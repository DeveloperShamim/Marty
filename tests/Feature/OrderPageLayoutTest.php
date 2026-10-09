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

    public function test_status_and_payment_show_once_and_the_change_card_is_always_open(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('admin.orders.show', $this->order()))->assertOk()->getContent();

        $this->assertStringNotContainsString('Payment Pending', $html, 'The header shows one status pill');
        $this->assertStringNotContainsString('Waiting for you', $html, 'Repeated the next-step box');
        $this->assertMatchesRegularExpression('/<section class="card[^"]*" id="change-status">/', $html, 'Status & payment is always open');
        $this->assertLessThan(strpos($html, 'id="courier"'), strpos($html, 'id="change-status"'), 'Status & payment tops the right column');
        $this->assertStringNotContainsString('data-next-status', $html, 'Orders awaiting review are confirmed from the review box');
        $this->assertStringContainsString('Connect courier history', $html);
        $this->assertStringNotContainsString('to see this customer', $html);
    }

    public function test_courier_box_says_when_staff_marked_the_order_delivered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['status' => 'delivered', 'payment_status' => 'verified', 'courier_name' => 'steadfast',
            'courier_tracking_code' => 'SF9', 'courier_sent_at' => now()->subDays(2), 'courier_status' => 'in_transit',
            'courier_status_message' => 'At sorting hub']);

        $html = $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->getContent();

        $this->assertStringContainsString('Marked delivered by staff.', $html);
        $this->assertStringNotContainsString('At sorting hub', $html);
    }

    public function test_next_step_button_moves_the_order_along(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->order(['status' => 'shipped', 'payment_method' => 'cod', 'payment_status' => 'pending', 'internal_note' => 'Fragile']);

        $html = $this->actingAs($admin)->get(route('admin.orders.show', $order))->getContent();
        $this->assertStringContainsString('Mark as delivered', $html);
        $this->assertStringContainsString('Also marks the cash as collected.', $html);

        preg_match('/<form[^>]*data-next-status>(.*?)<\/form>/s', $html, $form);
        preg_match_all('/name="(status|payment_status|internal_note)" value="([^"]*)"/', $form[1], $f);
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), array_combine($f[1], $f[2]))->assertRedirect();

        $order->refresh();
        $this->assertSame(['delivered', 'verified', 'Fragile'], [$order->status, $order->payment_status, $order->internal_note]);
        $this->assertStringNotContainsString('data-next-status', $this->actingAs($admin)->get(route('admin.orders.show', $order))->getContent());
    }
}
