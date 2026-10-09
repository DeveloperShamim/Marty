<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category', 'is_active' => true, 'position' => 1]
        );

        return Product::create(array_merge([
            'category_id'    => $category->id,
            'name'           => 'Test Shoe',
            'slug'           => 'test-shoe-' . uniqid(),
            'regular_price'  => 1000,
            'stock_quantity' => 10,
            'is_published'   => true,
        ], $overrides));
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'    => 'Test Customer',
            'customer_phone'   => '01712345678',
            'shipping_address' => 'House 1, Road 2',
            'city'             => 'Dhaka',
            'shipping_zone'    => 'inside_dhaka',
            'payment_method'   => 'cod',
        ], $overrides);
    }

    /* ---------------- Cart ---------------- */

    public function test_customer_can_add_product_to_cart(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 2])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_cannot_add_unpublished_product(): void
    {
        $product = $this->makeProduct(['is_published' => false]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertNotFound();
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $product = $this->makeProduct(['stock_quantity' => 0]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_cart_enforces_max_three_per_product(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 3])->assertOk();
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertStatus(422);
    }

    public function test_ajax_validation_errors_come_back_as_json_not_a_redirect(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 4])
            ->assertStatus(422)
            ->assertJsonValidationErrors('qty');
    }

    public function test_product_with_skus_requires_an_option(): void
    {
        $product = $this->makeProduct();
        ProductSku::create([
            'product_id'     => $product->id,
            'attributes'     => ['Size' => '40'],
            'stock_quantity' => 5,
        ]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_sku_product_accepts_a_real_option_and_rejects_made_up_ones(): void
    {
        $product = $this->makeProduct();
        $sku = ProductSku::create([
            'product_id'     => $product->id,
            'attributes'     => ['Size' => '40'],
            'stock_quantity' => 5,
        ]);
        $otherSku = ProductSku::create([
            'product_id'     => $this->makeProduct()->id,
            'attributes'     => ['Size' => '41'],
            'stock_quantity' => 5,
        ]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'variant' => 'Size: 99'])->assertStatus(422);
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'sku_id' => $otherSku->id])->assertStatus(422);

        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'sku_id' => $sku->id])->assertOk();
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertEquals($sku->id, Order::with('items')->first()->items->first()->product_sku_id);
        $this->assertEquals(4, $sku->fresh()->stock_quantity);
    }

    /* ---------------- Checkout ---------------- */

    public function test_checkout_creates_order_with_correct_total_and_reduces_stock(): void
    {
        $product = $this->makeProduct(['regular_price' => 1000, 'stock_quantity' => 10]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::with('items')->first();
        $this->assertNotNull($order, 'Order was not created');
        $response->assertRedirect(route('order.confirmation', $order->order_number));

        // 2 x 1000 + 60 inside-Dhaka shipping (default)
        $this->assertEquals(2000, (float) $order->subtotal);
        $this->assertEquals(60, (float) $order->shipping_charge);
        $this->assertEquals(2060, (float) $order->total);
        $this->assertCount(1, $order->items);
        $this->assertEquals(8, $product->fresh()->stock_quantity);

        // Cart is emptied after ordering
        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('cart.index'));
    }

    public function test_delivery_note_from_checkout_becomes_the_courier_note(): void
    {
        $product = $this->makeProduct(['regular_price' => 1000, 'stock_quantity' => 10]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 1])->assertOk();
        $this->get(route('checkout.show'))->assertSee('name="delivery_note"', false);

        $this->post(route('checkout.store'), $this->checkoutPayload(['delivery_note' => '  Deliver after 5pm  ']));

        $this->assertSame('Deliver after 5pm', Order::first()->internal_note);
    }

    public function test_checkout_rejects_invalid_phone_number(): void
    {
        $product = $this->makeProduct();
        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();

        $this->post(route('checkout.store'), $this->checkoutPayload(['customer_phone' => '12345']))
            ->assertSessionHasErrors('customer_phone');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_if_stock_ran_out_after_adding_to_cart(): void
    {
        $product = $this->makeProduct(['stock_quantity' => 2]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

        $product->update(['stock_quantity' => 1]);

        $this->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    /* ---------------- Coupons ---------------- */

    public function test_valid_coupon_is_applied_to_order_total(): void
    {
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $product = $this->makeProduct(['regular_price' => 1000]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();

        $this->post(route('checkout.coupon.apply'), ['code' => 'save10'])
            ->assertSessionHasNoErrors();

        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::first();
        $this->assertEquals(100, (float) $order->discount_amount);
        $this->assertEquals(960, (float) $order->total); // 1000 - 100 + 60
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_unknown_expired_and_used_up_coupons_are_rejected(): void
    {
        Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 100, 'is_active' => true, 'expires_at' => now()->subDay()]);
        Coupon::create(['code' => 'FULL', 'type' => 'fixed', 'value' => 100, 'is_active' => true, 'max_uses' => 1, 'used_count' => 1]);
        Coupon::create(['code' => 'BIGORDER', 'type' => 'fixed', 'value' => 100, 'is_active' => true, 'min_order_amount' => 5000]);

        $product = $this->makeProduct(['regular_price' => 1000]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();

        foreach (['NOPE', 'OLD', 'FULL', 'BIGORDER'] as $code) {
            $this->post(route('checkout.coupon.apply'), ['code' => $code])
                ->assertSessionHasErrors('coupon');
        }
    }

    /* ---------------- Order tracking ---------------- */

    private function makeOrder(): Order
    {
        return Order::create([
            'order_number'     => 'ORD-TRACK-1',
            'customer_name'    => 'Tracker',
            'customer_phone'   => '01712345678',
            'shipping_address' => 'Somewhere',
            'city'             => 'Dhaka',
            'subtotal'         => 1000,
            'shipping_charge'  => 60,
            'total'            => 1060,
            'payment_method'   => 'cod',
            'status'           => 'pending',
        ]);
    }

    public function test_track_order_requires_matching_phone(): void
    {
        $this->makeOrder();

        $this->post(route('track.find'), ['order_number' => 'ORD-TRACK-1', 'phone' => '01800000000'])
            ->assertSessionHasErrors('order_number');

        $this->post(route('track.find'), ['order_number' => 'ORD-TRACK-1', 'phone' => '+8801712345678'])
            ->assertOk()
            ->assertSee('ORD-TRACK-1');
    }

    public function test_order_confirmation_page_is_not_viewable_by_strangers(): void
    {
        $order = $this->makeOrder();

        $this->get(route('order.confirmation', $order->order_number))
            ->assertRedirect(route('track'));
    }

    public function test_track_order_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('track.find'), ['order_number' => 'X', 'phone' => '01700000000']);
        }

        $this->post(route('track.find'), ['order_number' => 'X', 'phone' => '01700000000'])
            ->assertStatus(429);
    }

    /* ---------------- Admin access ---------------- */

    public function test_guests_and_customers_cannot_open_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
    }

    public function test_staff_roles_only_reach_their_own_sections(): void
    {
        $orderManager = User::factory()->create(['role' => 'order_manager']);
        $this->actingAs($orderManager)->get(route('admin.orders.index'))->assertOk();
        // Refused pages send staff to their own home page (orders for an order manager).
        $this->actingAs($orderManager)->get(route('admin.products.index'))->assertRedirect(route('admin.orders.index'));
        $this->actingAs($orderManager)->get(route('admin.staff.index'))->assertRedirect(route('admin.orders.index'));

        $inventoryManager = User::factory()->create(['role' => 'inventory_manager']);
        $this->actingAs($inventoryManager)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($inventoryManager)->get(route('admin.orders.index'))->assertRedirect(route('admin.dashboard'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk();
    }
}
