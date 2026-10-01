<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelLabelTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-' . strtoupper(uniqid()), 'customer_name' => 'Rahim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1, Road 2', 'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka',
            'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
        ], $attrs));
    }

    public function test_label_has_scannable_order_number_and_cash_to_collect(): void
    {
        $cod = $this->order(['order_number' => 'ORD-COD-1']);
        $paid = $this->order(['order_number' => 'ORD-PAID-1', 'payment_method' => 'bkash', 'payment_status' => 'verified']);
        $admin = User::factory()->create(['role' => 'admin']);

        $html = $this->actingAs($admin)
            ->get(route('admin.orders.labels', ['orders' => [$cod->order_number, $paid->order_number]]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('jsbarcode-value="ORD-COD-1"', $html);
        $this->assertStringContainsString('jsbarcode-value="ORD-PAID-1"', $html);
        $this->assertStringContainsString('Collect (COD)', $html);
        $this->assertStringContainsString('1,060', $html);
        $this->assertSame(1, substr_count($html, 'Prepaid'));
    }

    public function test_the_printed_code_is_found_by_the_courier_scanner(): void
    {
        $order = $this->order([]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->getJson(route('admin.courier-scan.return-lookup', ['code' => $order->order_number]))
            ->assertOk()->assertJsonPath('order.order_number', $order->order_number);
    }

    public function test_ready_labels_skip_unconfirmed_finished_and_pos_orders(): void
    {
        $this->order(['order_number' => 'ORD-READY']);
        $this->order(['order_number' => 'ORD-PACKING', 'status' => 'processing']);
        $this->order(['order_number' => 'ORD-UNVERIFIED', 'status' => 'pending']);
        $this->order(['order_number' => 'ORD-SENT', 'status' => 'shipped']);
        $this->order(['order_number' => 'POS-1', 'status' => 'processing', 'order_type' => 'pos']);
        $admin = User::factory()->create(['role' => 'admin']);

        $html = $this->actingAs($admin)->get(route('admin.orders.labels', ['ready' => 1, 'size' => 'a4']))->assertOk()->getContent();

        preg_match_all('/jsbarcode-value="([^"]+)"/', $html, $m);
        $this->assertEqualsCanonicalizing(['ORD-READY', 'ORD-PACKING'], $m[1]);
    }

    public function test_customer_text_on_label_is_escaped(): void
    {
        $order = $this->order(['customer_name' => '<script>alert(1)</script>']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.orders.labels', ['orders' => [$order->order_number]]))
            ->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }
}
