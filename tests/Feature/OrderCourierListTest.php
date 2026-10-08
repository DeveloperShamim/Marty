<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCourierListTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'shipped',
            'courier_name' => 'steadfast', 'courier_sent_at' => now()->subDay(),
        ], $attrs));
    }

    public function test_order_list_shows_courier_status_and_filters_by_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order('ORD-HOLD', ['courier_tracking_code' => 'SF1', 'courier_status' => 'hold',
            'courier_status_message' => 'Customer not reachable', 'courier_synced_at' => now()->subHours(3)]);
        $this->order('ORD-MOVE', ['courier_tracking_code' => 'SF2', 'courier_status' => 'in_transit', 'courier_synced_at' => now()->subHours(3)]);
        $this->order('ORD-NEW', ['courier_tracking_code' => 'SF3']);
        $this->order('ORD-HOME', ['status' => 'confirmed', 'courier_name' => null, 'courier_sent_at' => null]);

        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()
            ->assertSee('Courier tracking')
            ->assertSee('every night at 9:00 PM')
            ->assertSee('On hold')
            ->assertSee('Customer not reachable')
            ->assertSee('Checked 3 hours ago')
            ->assertSee('Waiting for first check');

        $this->actingAs($admin)->get(route('admin.orders.index', ['courier' => 'attention']))->assertOk()
            ->assertSee('ORD-HOLD')->assertDontSee('ORD-MOVE')->assertDontSee('ORD-HOME');

        $this->actingAs($admin)->get(route('admin.orders.index', ['courier' => 'in_transit']))->assertOk()
            ->assertSee('ORD-MOVE')->assertSee('ORD-NEW')->assertDontSee('ORD-HOLD');

        $this->actingAs($admin)->get(route('admin.orders.index', ['courier' => 'unchecked']))->assertOk()
            ->assertSee('ORD-NEW')->assertDontSee('ORD-MOVE');
    }
}
