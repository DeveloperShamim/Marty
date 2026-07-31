<?php

namespace Tests\Feature;

use App\Models\Blacklist;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraudDetectionTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleProduct(): Product
    {
        $category = Category::create(['name' => 'General', 'slug' => 'general']);

        return Product::create([
            'category_id'    => $category->id,
            'name'           => 'Sample Product',
            'slug'           => 'sample-product',
            'regular_price'  => 500,
            'stock_quantity' => 10,
            'is_published'   => true,
        ]);
    }

    public function test_blacklisted_phone_is_blocked_at_checkout(): void
    {
        Blacklist::add('phone', '01700000000', 'Prank caller');
        $product = $this->createSampleProduct();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1]);

        $response = $this->post(route('checkout.store'), [
            'customer_name'    => 'Fraud Customer',
            'customer_phone'   => '01700000000',
            'customer_email'   => 'fraud@example.com',
            'shipping_address' => 'House 1, Road 2',
            'city'             => 'Dhaka',
            'shipping_zone'    => 'inside_dhaka',
            'payment_method'   => 'cod',
        ]);

        $response->assertSessionHasErrors('customer_phone');
        $this->assertDatabaseMissing('orders', ['customer_phone' => '01700000000']);
    }

    public function test_duplicate_mobile_banking_trxid_flags_risk_score(): void
    {
        \App\Models\Setting::put('pay_bkash_enabled', '1');
        \App\Models\Setting::put('bkash_number', '01700000000');

        $product = $this->createSampleProduct();

        Order::create([
            'order_number'    => 'ORD-EXISTING-123',
            'customer_name'   => 'First Customer',
            'customer_phone'  => '01711111111',
            'shipping_address'=> 'Address 1',
            'city'            => 'Dhaka',
            'shipping_zone'   => 'inside_dhaka',
            'subtotal'        => 500,
            'shipping_charge' => 60,
            'total'           => 560,
            'payment_method'  => 'bkash',
            'payment_sender_number' => '01711111111',
            'payment_txn_id'  => '9B7A6C5D4E',
            'payment_status'  => 'verified',
            'status'          => 'confirmed',
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1]);

        $response = $this->post(route('checkout.store'), [
            'customer_name'    => 'Second Customer',
            'customer_phone'   => '01722222222',
            'shipping_address' => 'Address 2',
            'city'             => 'Dhaka',
            'shipping_zone'    => 'inside_dhaka',
            'payment_method'   => 'bkash',
            'payment_sender_number' => '01722222222',
            'payment_txn_id'  => '9B7A6C5D4E',
        ]);

        $response->assertRedirect();

        $order = Order::where('customer_phone', '01722222222')->first();
        $this->assertNotNull($order);
        $this->assertGreaterThanOrEqual(40, $order->fraud_score);
        $this->assertNotEmpty($order->fraud_flags);
    }

    public function test_admin_can_manage_blacklist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.blacklist.store'), [
            'type'   => 'phone',
            'value'  => '01899999999',
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blacklists', [
            'type'  => 'phone',
            'value' => '01899999999',
        ]);
    }
}
