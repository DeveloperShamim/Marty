<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\OrderNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);

        return Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true]);
    }

    private function checkout(): Order
    {
        $this->postJson(route('cart.add'), ['product_id' => $this->product()->id])->assertOk();
        $this->post(route('checkout.store'), [
            'customer_name' => 'Rahim', 'customer_phone' => '01712345678', 'shipping_address' => 'House 1',
            'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors();

        return Order::latest('id')->firstOrFail();
    }

    public function test_shop_and_pos_orders_get_prefix_plus_six_digits(): void
    {
        $this->assertMatchesRegularExpression('/^VB-\d{6}$/', $this->checkout()->order_number);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->postJson(route('admin.pos.order'), [
            'items' => [['product_id' => Product::first()->id, 'quantity' => 1, 'price' => 1000]],
            'payment_method' => 'cash',
        ])->assertJson(['success' => true]);
        $this->assertMatchesRegularExpression('/^VB-P-\d{6}$/', Order::latest('id')->value('order_number'));

        Setting::put('order_number_prefix', 'vant');
        $this->assertMatchesRegularExpression('/^VANT-\d{6}$/', OrderNumber::generate());
    }

    public function test_track_order_and_courier_scan_accept_just_the_digits(): void
    {
        $order = $this->checkout();
        $digits = substr($order->order_number, -6);

        $this->post(route('track.find'), ['order_number' => $digits, 'phone' => '01712345678'])
            ->assertOk()->assertSee($order->order_number);
        $this->post(route('track.find'), ['order_number' => strtolower($order->order_number), 'phone' => '01712345678'])
            ->assertOk()->assertSee($order->order_number);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertSame([$order->order_number], array_values(array_intersect(OrderNumber::candidates($digits), [$order->order_number])));
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Order ID prefix');
    }
}
