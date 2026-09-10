<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCustomerUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_order_customer_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::create([
            'order_number'      => 'ORD-TEST-CUST',
            'customer_name'     => 'Original Name',
            'customer_phone'    => '01700000000',
            'customer_email'    => 'original@example.com',
            'shipping_address'  => 'Old Address, House 1',
            'city'              => 'Dhaka',
            'postal_code'       => '1205',
            'subtotal'          => 1000,
            'shipping_charge'   => 60,
            'total'             => 1060,
            'payment_method'    => 'cod',
            'status'            => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update-customer', $order), [
            'customer_name'    => 'Updated Name',
            'customer_phone'   => '01899999999',
            'customer_email'   => 'updated@example.com',
            'shipping_address' => 'New Address, Flat 4B',
            'city'             => 'Chittagong',
            'postal_code'      => '4000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $order->refresh();
        $this->assertEquals('Updated Name', $order->customer_name);
        $this->assertEquals('01899999999', $order->customer_phone);
        $this->assertEquals('updated@example.com', $order->customer_email);
        $this->assertEquals('New Address, Flat 4B', $order->shipping_address);
        $this->assertEquals('Chittagong', $order->city);
        $this->assertEquals('4000', $order->postal_code);
    }

    public function test_customer_details_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Order::create([
            'order_number'      => 'ORD-TEST-VAL',
            'customer_name'     => 'Original Name',
            'customer_phone'    => '01700000000',
            'shipping_address'  => 'Old Address',
            'city'              => 'Dhaka',
            'subtotal'          => 500,
            'shipping_charge'   => 60,
            'total'             => 560,
            'payment_method'    => 'cod',
            'status'            => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.update-customer', $order), [
            'customer_name'    => '',
            'customer_phone'   => '',
            'shipping_address' => '',
            'city'             => '',
        ]);

        $response->assertSessionHasErrors(['customer_name', 'customer_phone', 'shipping_address', 'city']);
    }
}
