<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end money check: real checkout, POS, courier scan out / scan in,
 * then compare Dashboard and Analytics against hand-calculated figures.
 *
 * Product A sells 1000 (cost 600), Product B sells 500 (cost 200).
 * Shipping: inside Dhaka 60, outside 120 (defaults).
 *
 *  #  Order                                   Counts as sale?  Revenue  COGS   Courier loss
 *  1  COD 2xA, scanned out, delivered         yes              2000     1200   0
 *  2  COD 1xB with 10% coupon, delivered      yes              450      200    0
 *  3  bKash prepaid 1xA, verified, shipped    yes (paid)       1000     600    0
 *  4  COD 1xA, scanned out, returned UNPAID   no               -        -      60  (inside Dhaka)
 *  5  COD 1xB outside Dhaka, returned PAID    no               -        -      0   (customer paid delivery)
 *  6  COD 1xA, cancelled                      no               -        -      0
 *  7  COD 1xB, still pending                  no               -        -      0
 *  8  POS cash 1xB                            yes              500      200    0
 *  9  bKash prepaid 1xB, verified, returned   no (refunded)    -        -      60  (unpaid return, inside Dhaka)
 *                                                    Totals:   3950     2200   120
 *  Net profit = 3950 - 2200 - 120 = 1630
 */
class SalesProfitCourierScenarioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $a;
    private Product $b;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        Setting::put('bkash_number', '01700000000');
        Coupon::create(['code' => 'TEN', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $this->a = Product::create(['category_id' => $category->id, 'name' => 'Product A', 'slug' => 'a', 'regular_price' => 1000, 'cost_price' => 600, 'stock_quantity' => 20, 'is_published' => true]);
        $this->b = Product::create(['category_id' => $category->id, 'name' => 'Product B', 'slug' => 'b', 'regular_price' => 500, 'cost_price' => 200, 'stock_quantity' => 20, 'is_published' => true]);
    }

    /** Place an order through the real storefront cart + checkout. */
    private function checkout(Product $product, int $qty, array $extra = [], ?string $coupon = null): Order
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => $qty])->assertOk();
        if ($coupon) {
            $this->post(route('checkout.coupon.apply'), ['code' => $coupon])->assertSessionHasNoErrors();
        }

        $this->post(route('checkout.store'), array_merge([
            'customer_name' => 'Customer', 'customer_phone' => '01712345678',
            'shipping_address' => 'House 1', 'city' => 'Dhaka',
            'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
        ], $extra))->assertSessionHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    private function prepaid(): array
    {
        return ['payment_method' => 'bkash', 'payment_sender_number' => '01811111111', 'payment_txn_id' => 'TXN' . uniqid()];
    }

    private function scanOut(Order $order): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.courier-scan.dispatch'), ['code' => $order->order_number, 'courier_name' => 'steadfast'])
            ->assertJson(['success' => true]);
    }

    private function scanReturn(Order $order, string $type): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.courier-scan.return-confirm'), ['order_id' => $order->id, 'return_type' => $type, 'restock' => true])
            ->assertJson(['success' => true]);
    }

    private function setStatus(Order $order, string $status, string $payment = 'pending'): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.orders.update', $order), ['status' => $status, 'payment_status' => $payment])
            ->assertSessionHasNoErrors();
    }

    private function placeAllOrders(): void
    {
        // 1. COD 2xA scanned out then delivered
        $o1 = $this->checkout($this->a, 2);
        $this->scanOut($o1);
        $this->setStatus($o1, 'delivered');

        // 2. COD 1xB with 10% coupon, delivered
        $o2 = $this->checkout($this->b, 1, [], 'TEN');
        $this->setStatus($o2, 'delivered');

        // 3. bKash prepaid 1xA, payment verified, scanned out (in transit)
        $o3 = $this->checkout($this->a, 1, $this->prepaid());
        $this->actingAs($this->admin)->post(route('admin.orders.verify', $o3));
        $this->scanOut($o3);

        // 4. COD 1xA scanned out, came back: customer did not pay delivery
        $o4 = $this->checkout($this->a, 1);
        $this->scanOut($o4);
        $this->scanReturn($o4, 'unpaid_delivery');

        // 5. COD 1xB outside Dhaka scanned out, came back: customer paid delivery
        $o5 = $this->checkout($this->b, 1, ['shipping_zone' => 'outside_dhaka']);
        $this->scanOut($o5);
        $this->scanReturn($o5, 'paid_delivery');

        // 6. COD 1xA cancelled
        $o6 = $this->checkout($this->a, 1);
        $this->setStatus($o6, 'cancelled');

        // 7. COD 1xB still pending
        $this->checkout($this->b, 1);

        // 8. POS cash sale 1xB
        $this->actingAs($this->admin)->postJson(route('admin.pos.order'), [
            'items' => [['product_id' => $this->b->id, 'quantity' => 1, 'price' => 500]],
            'payment_method' => 'cash',
        ])->assertJson(['success' => true]);

        // 9. bKash prepaid 1xB verified, scanned out, returned (unpaid delivery)
        $o9 = $this->checkout($this->b, 1, $this->prepaid());
        $this->actingAs($this->admin)->post(route('admin.orders.verify', $o9));
        $this->scanOut($o9);
        $this->scanReturn($o9, 'unpaid_delivery');
    }

    public function test_orders_scans_and_returns_record_the_right_values(): void
    {
        $this->placeAllOrders();
        $orders = Order::orderBy('id')->get()->values();

        // Courier scan out recorded the courier and time
        $this->assertSame('steadfast', $orders[0]->courier_name);
        $this->assertNotNull($orders[0]->courier_sent_at);

        // Coupon discount on order 2
        $this->assertEquals(50, (float) $orders[1]->discount_amount);

        // Return delivery charges
        $this->assertSame('unpaid_delivery', $orders[3]->return_type);
        $this->assertEquals(60, (float) $orders[3]->courier_loss_amount);
        $this->assertSame('paid_delivery', $orders[4]->return_type);
        $this->assertEquals(0, (float) $orders[4]->courier_loss_amount);
        $this->assertEquals(60, (float) $orders[8]->courier_loss_amount);

        // Stock: A sold 2(o1)+1(o3) = 3 → 17 (o4 returned+restocked, o6 cancelled)
        //        B sold 1(o2)+1(o7 pending)+1(POS) = 3 → 17 (o5, o9 returned+restocked)
        $this->assertSame(17, (int) $this->a->fresh()->stock_quantity);
        $this->assertSame(17, (int) $this->b->fresh()->stock_quantity);
    }

    public function test_dashboard_totals_match_hand_calculation(): void
    {
        $this->placeAllOrders();

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('revenue', fn ($v) => abs($v - 3950) < 0.01)
            ->assertViewHas('totalCogs', fn ($v) => abs($v - 2200) < 0.01)
            ->assertViewHas('netProfit', fn ($v) => abs($v - 1630) < 0.01)
            ->assertViewHas('totalSalesOrdersCount', 4)
            ->assertViewHas('returnedOrdersCount', 3)
            ->assertViewHas('cancelledOrdersCount', 1);
    }

    public function test_analytics_totals_match_hand_calculation(): void
    {
        $this->placeAllOrders();

        $this->actingAs($this->admin)->get(route('admin.analytics.index', ['range' => 'this_month']))
            ->assertOk()
            ->assertViewHas('grossRevenue', fn ($v) => abs($v - 3950) < 0.01)
            ->assertViewHas('cogs', fn ($v) => abs($v - 2200) < 0.01)
            ->assertViewHas('courierLoss', fn ($v) => abs($v - 120) < 0.01)
            ->assertViewHas('netProfit', fn ($v) => abs($v - 1630) < 0.01)
            ->assertViewHas('totalOrdersCount', 4)
            ->assertViewHas('returnedCount', 3)
            ->assertViewHas('paidReturnsCount', 1)
            ->assertViewHas('unpaidReturnsCount', 2)
            ->assertViewHas('posRevenue', fn ($v) => abs($v - 500) < 0.01)
            ->assertViewHas('onlineRevenue', fn ($v) => abs($v - 3450) < 0.01);
    }

    public function test_redispatching_a_returned_parcel_takes_stock_out_and_clears_the_old_loss(): void
    {
        $order = $this->checkout($this->a, 1);            // A: 20 -> 19
        $this->scanOut($order);
        $this->scanReturn($order, 'unpaid_delivery');      // restocked -> 20, loss 60
        $this->assertSame(20, (int) $this->a->fresh()->stock_quantity);

        $this->scanOut($order);                            // customer wants it after all
        $order->refresh();

        $this->assertSame('shipped', $order->status);
        $this->assertSame(19, (int) $this->a->fresh()->stock_quantity);
        $this->assertEquals(0, (float) $order->courier_loss_amount);
        $this->assertNull($order->return_type);

        // Coming back a second time restocks again and records a fresh loss.
        $this->scanReturn($order, 'unpaid_delivery');
        $this->assertSame(20, (int) $this->a->fresh()->stock_quantity);
        $this->assertEquals(60, (float) $order->fresh()->courier_loss_amount);
    }

    public function test_return_scan_is_refused_for_orders_that_never_shipped(): void
    {
        $pending = $this->checkout($this->a, 1);
        $cancelled = $this->checkout($this->b, 1);
        $this->setStatus($cancelled, 'cancelled');

        foreach ([$pending, $cancelled] as $order) {
            $this->actingAs($this->admin)
                ->postJson(route('admin.courier-scan.return-confirm'), ['order_id' => $order->id, 'return_type' => 'unpaid_delivery', 'restock' => true])
                ->assertStatus(422)->assertJson(['success' => false]);
            $this->assertEquals(0, (float) $order->fresh()->courier_loss_amount);
        }
    }

    public function test_dashboard_and_analytics_use_the_same_cost_when_order_has_none_saved(): void
    {
        $order = $this->checkout($this->a, 1);
        $order->items()->update(['cost_price' => 0]);      // e.g. cost was not set when it sold
        $this->setStatus($order, 'delivered');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertViewHas('totalCogs', fn ($v) => abs($v - 600) < 0.01);
        $this->actingAs($this->admin)->get(route('admin.analytics.index', ['range' => 'this_month']))
            ->assertViewHas('cogs', fn ($v) => abs($v - 600) < 0.01);
    }

    public function test_dashboard_shows_a_loss_instead_of_zero(): void
    {
        // One unpaid return and nothing sold: the shop lost the delivery charge.
        $order = $this->checkout($this->a, 1);
        $this->scanOut($order);
        $this->scanReturn($order, 'unpaid_delivery');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertViewHas('netProfit', fn ($v) => abs($v - (-60)) < 0.01);
    }
}
