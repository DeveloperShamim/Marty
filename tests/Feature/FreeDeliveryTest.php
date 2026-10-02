<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        foreach ([
            'shipping_inside_dhaka' => '60', 'shipping_outside_dhaka' => '120', 'tax_percent' => '0',
            'bkash_number' => '01700000000', 'free_delivery_online' => '1', 'free_delivery_online_min' => '500',
            'free_delivery_online_zones' => 'both', 'free_delivery_over_amount' => '0',
        ] as $k => $v) {
            Setting::put($k, $v);
        }
    }

    private function product(string $name, float $price, float $cost, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => $price, 'cost_price' => $cost, 'stock_quantity' => 10, 'is_published' => true,
        ], $attrs));
    }

    private function order(Product $product, array $payload = [], int $qty = 1): Order
    {
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => $qty])->assertOk();
        $this->post(route('checkout.store'), array_merge([
            'customer_name' => 'Rahim', 'customer_phone' => '01712345678', 'shipping_address' => 'House 1',
            'city' => 'Dhaka', 'shipping_zone' => 'outside_dhaka', 'payment_method' => 'cod',
        ], $payload))->assertSessionHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    private function bkash(): array
    {
        return ['payment_method' => 'bkash', 'payment_sender_number' => '01811111111', 'payment_txn_id' => 'TX123'];
    }

    public function test_paying_online_over_the_minimum_ships_free(): void
    {
        $order = $this->order($this->product('Watch', 1000, 600), $this->bkash());

        $this->assertEquals(0, (float) $order->shipping_charge);
        $this->assertEquals(120, (float) $order->shipping_waived);
        $this->assertSame('online_payment', $order->free_delivery_reason);
        $this->assertEquals(1000, (float) $order->total);
        $this->assertSame('FREE', $order->deliveryDisplay());
    }

    public function test_online_offer_respects_minimum_zone_and_switch(): void
    {
        // Under the minimum
        $small = $this->order($this->product('Strap', 300, 100), $this->bkash());
        $this->assertEquals(120, (float) $small->shipping_charge);
        $this->assertNull($small->free_delivery_reason);

        // Wrong zone
        Setting::put('free_delivery_online_zones', 'inside_dhaka');
        $outside = $this->order($this->product('Watch A', 1000, 600), $this->bkash());
        $this->assertEquals(120, (float) $outside->shipping_charge);

        // Cash on delivery never gets the online offer
        Setting::put('free_delivery_online_zones', 'both');
        $cod = $this->order($this->product('Watch B', 1000, 600));
        $this->assertEquals(120, (float) $cod->shipping_charge);

        // Offer switched off
        Setting::put('free_delivery_online', '0');
        $off = $this->order($this->product('Watch C', 1000, 600), $this->bkash());
        $this->assertEquals(120, (float) $off->shipping_charge);
    }

    public function test_free_delivery_product_and_big_orders_ship_free_even_with_cod(): void
    {
        $byProduct = $this->order($this->product('Free Shoe', 200, 100, ['free_delivery' => true]));
        $this->assertSame('product', $byProduct->free_delivery_reason);
        $this->assertEquals(0, (float) $byProduct->shipping_charge);
        $this->assertEquals(120, (float) $byProduct->shipping_waived);

        Setting::put('free_delivery_over_amount', '3000');
        $big = $this->order($this->product('Jacket', 1600, 900), [], 2);
        $this->assertSame('order_total', $big->free_delivery_reason);
        $this->assertEquals(3200, (float) $big->total);
    }

    public function test_profit_subtracts_the_delivery_fee_the_shop_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($this->product('Watch', 1000, 600), $this->bkash());
        $order->update(['payment_status' => 'verified', 'status' => 'delivered']);

        // 1000 sales − 600 cost − 120 courier fee the shop paid = 280
        $this->actingAs($admin)->get(route('admin.analytics.index', ['range' => 'this_month']))
            ->assertOk()
            ->assertViewHas('freeDeliveryCost', fn ($v) => abs($v - 120) < 0.01)
            ->assertViewHas('freeDeliveryOrders', 1)
            ->assertViewHas('netProfit', fn ($v) => abs($v - 280) < 0.01);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertViewHas('netProfit', fn ($v) => abs($v - 280) < 0.01);

        $csv = $this->actingAs($admin)->get(route('admin.analytics.export', ['range' => 'this_month']))->streamedContent();
        $this->assertStringContainsString('Free Delivery Cost', $csv);
        $this->assertMatchesRegularExpression('/,120(\.00)?,280(\.00)?\s*$/m', $csv);
    }

    public function test_returned_free_delivery_parcel_loses_the_real_fee_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($this->product('Watch', 1000, 600), $this->bkash());
        $order->update(['payment_status' => 'verified', 'status' => 'shipped']);

        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'returned', 'payment_status' => 'verified']);
        $order->refresh();
        $this->assertEquals(120, (float) $order->courier_loss_amount); // the real fee, not the ৳130 guess

        $this->actingAs($admin)->get(route('admin.analytics.index', ['range' => 'this_month']))
            ->assertViewHas('freeDeliveryCost', fn ($v) => $v == 0)
            ->assertViewHas('netProfit', fn ($v) => abs($v + 120) < 0.01); // counted once, as courier loss
    }

    public function test_switching_an_unpaid_online_order_to_cod_adds_delivery_back(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order($this->product('Watch', 1000, 600), $this->bkash());

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertSee('switch to cash on delivery');
        $this->actingAs($admin)->post(route('admin.orders.switch-to-cod', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertEquals(120, (float) $order->shipping_charge);
        $this->assertEquals(0, (float) $order->shipping_waived);
        $this->assertEquals(1120, (float) $order->total);
        $this->assertEquals(1120, $order->amountToCollect());

        // Free-delivery products keep their free delivery
        $productOrder = $this->order($this->product('Free Shoe', 600, 300, ['free_delivery' => true]), $this->bkash());
        $this->actingAs($admin)->post(route('admin.orders.switch-to-cod', $productOrder));
        $this->assertEquals(0, (float) $productOrder->fresh()->shipping_charge);

        // Verified payments can't be switched
        $paid = $this->order($this->product('Watch D', 1000, 600), $this->bkash());
        $paid->update(['payment_status' => 'verified']);
        $this->actingAs($admin)->post(route('admin.orders.switch-to-cod', $paid));
        $this->assertSame('bkash', $paid->fresh()->payment_method);
    }

    public function test_store_manager_runs_the_offer_and_inventory_manager_cannot(): void
    {
        $product = $this->product('Watch', 1000, 600);
        $manager = User::factory()->create(['role' => 'store_manager']);

        $this->actingAs($manager)->get(route('admin.free-delivery.index'))->assertOk()->assertSee('Free Delivery');
        $this->actingAs($manager)->put(route('admin.free-delivery.update'), [
            'free_delivery_online' => '1', 'free_delivery_online_min' => '800',
            'free_delivery_online_zones' => 'inside_dhaka', 'free_delivery_over_amount' => '5000',
        ])->assertRedirect();
        $this->assertSame('800', Setting::get('free_delivery_online_min'));
        $this->assertSame('inside_dhaka', Setting::get('free_delivery_online_zones'));

        $this->actingAs($manager)->put(route('admin.free-delivery.toggle', $product), ['free_delivery' => '1'])->assertRedirect();
        $this->assertTrue($product->fresh()->free_delivery);

        $stock = User::factory()->create(['role' => 'inventory_manager']);
        $this->actingAs($stock)->get(route('admin.free-delivery.index'))->assertRedirect();
        $this->actingAs($stock)->put(route('admin.free-delivery.toggle', $product), ['free_delivery' => '0']);
        $this->assertTrue($product->fresh()->free_delivery);
        $this->actingAs($stock)->get(route('admin.products.edit', $product))->assertOk()->assertDontSee('Orders with this product ship free');
    }

    public function test_storefront_shows_the_offer(): void
    {
        $product = $this->product('Free Shoe', 900, 300, ['free_delivery' => true]);
        $this->get(route('product.show', $product))->assertOk()->assertSee('Free delivery on this product');

        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();
        $this->get(route('checkout.show'))->assertOk()->assertSee('freeDeliveryNote', false)->assertSee('FREE');
    }
}
