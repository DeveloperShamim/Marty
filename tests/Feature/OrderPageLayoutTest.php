<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPageLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'VB-200001', 'customer_name' => 'Karim', 'customer_phone' => '01711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending',
        ], $attrs));
    }

    public function test_pending_cod_order_shows_next_step_at_the_top_and_no_empty_payment_boxes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('admin.orders.show', $this->order()))->assertOk()->getContent();

        $this->assertStringContainsString('Next step: confirm this order', $html);
        $this->assertLessThan(strpos($html, 'Items ('), strpos($html, 'Next step'));
        $this->assertStringContainsString('Cash on delivery: collect at the door', $html);
        $this->assertStringNotContainsString('Transaction ID', $html);
    }

    public function test_pending_bkash_order_asks_to_check_the_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['payment_method' => 'bkash', 'status' => 'confirmed', 'payment_sender_number' => '01811000000', 'payment_txn_id' => 'TX123']);

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Next step: check the payment')->assertSee('TX123')->assertSee('Transaction ID');

        $order->update(['payment_status' => 'verified']);
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertDontSee('Next step');
    }
}
