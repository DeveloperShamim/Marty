<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewOrderAlertTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function order(string $number, array $attrs = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending', 'order_type' => 'online',
        ], $attrs));
        $order->items()->create(['product_name' => 'Runner', 'variant' => 'EU 42', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        return $order;
    }

    public function test_first_poll_only_sets_the_baseline(): void
    {
        $old = $this->order('ORD-OLD');

        $this->actingAs($this->admin)->getJson(route('admin.orders.feed'))
            ->assertOk()->assertJson(['latest_id' => $old->id, 'orders' => [], 'needs_review' => 1]);
    }

    public function test_new_orders_after_the_baseline_are_returned_without_pos_sales(): void
    {
        $old = $this->order('ORD-OLD');
        $new = $this->order('ORD-NEW', ['payment_method' => 'bkash', 'payment_txn_id' => 'TX99', 'payment_sender_number' => '01811000000']);
        $this->order('POS-1', ['order_type' => 'pos', 'status' => 'delivered', 'payment_status' => 'verified', 'payment_method' => 'cash']);

        $this->actingAs($this->admin)->getJson(route('admin.orders.feed', ['after' => $old->id]))
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.number', 'ORD-NEW')
            ->assertJsonPath('orders.0.txn', 'TX99')
            ->assertJsonPath('orders.0.items', '1 × Runner (EU 42)')
            ->assertJsonPath('orders.0.accept_label', 'Verify payment')
            ->assertJsonPath('orders.0.urls.accept', route('admin.orders.verify', $new));
    }

    public function test_confirming_a_cod_order_keeps_cash_to_collect(): void
    {
        $order = $this->order('ORD-COD');

        $this->actingAs($this->admin)->postJson(route('admin.orders.verify', $order))
            ->assertOk()->assertJson(['success' => true, 'status' => 'confirmed', 'payment_status' => 'pending']);

        $order->refresh();
        $this->assertSame(1060.0, $order->amountToCollect(), 'The courier must still collect the cash');
        $this->assertFalse($order->isAwaitingReview());
        $this->assertSame(0, Order::needsReview()->count());
    }

    public function test_verifying_a_bkash_order_marks_it_paid(): void
    {
        $order = $this->order('ORD-BK', ['payment_method' => 'bkash']);

        $this->actingAs($this->admin)->postJson(route('admin.orders.verify', $order))->assertOk();

        $this->assertSame('verified', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_needs_review_tab_lists_new_orders_and_unverified_prepaid_payments_only(): void
    {
        $this->order('ORD-NEWCOD');
        $this->order('ORD-ACCEPTEDCOD', ['status' => 'confirmed']);
        $this->order('ORD-BKASH-UNCHECKED', ['status' => 'confirmed', 'payment_method' => 'bkash']);

        $html = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'pending_verification']))->assertOk()->getContent();

        $this->assertStringContainsString('ORD-NEWCOD', $html);
        $this->assertStringContainsString('ORD-BKASH-UNCHECKED', $html);
        $this->assertStringNotContainsString('ORD-ACCEPTEDCOD', $html);
    }

    public function test_only_staff_who_handle_orders_get_the_alerts(): void
    {
        $this->order('ORD-1');
        $inventory = User::factory()->create(['role' => 'inventory_manager']);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('orderAlertsConfig', false);
        $this->actingAs($inventory)->get(route('admin.dashboard'))->assertDontSee('orderAlertsConfig', false);
        $this->actingAs($inventory)->getJson(route('admin.orders.feed'))->assertForbidden();
    }
}
