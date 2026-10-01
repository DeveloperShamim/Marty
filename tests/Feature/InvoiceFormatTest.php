<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceFormatTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'ORD-INV-1', 'customer_name' => 'Rahim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka',
            'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
        ], $attrs));
        $order->items()->create(['product_name' => 'Runner', 'variant' => 'Size: 42', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        return $order;
    }

    private function invoice(Order $order, ?string $format = null)
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return $this->actingAs($admin)->get(route('admin.orders.invoice', array_filter(['order' => $order, 'format' => $format])))->assertOk();
    }

    public function test_full_page_is_the_default_and_shows_the_cod_amount_due(): void
    {
        $html = $this->invoice($this->order())->getContent();

        $this->assertStringContainsString('class="fmt-a4"', $html);
        $this->assertStringContainsString('size: A4', $html);
        $this->assertStringContainsString('Amount due (cash on delivery)', $html);
        $this->assertStringContainsString('jsbarcode-value="ORD-INV-1"', $html);
    }

    public function test_half_page_prints_a_customer_and_an_office_copy_on_one_sheet(): void
    {
        $html = $this->invoice($this->order(), 'half')->getContent();

        $this->assertStringContainsString('Customer copy', $html);
        $this->assertStringContainsString('Office copy', $html);
        $this->assertSame(2, substr_count($html, '<article class="inv inv--half'));
    }

    public function test_thermal_receipts_use_roll_width(): void
    {
        $order = $this->order(['payment_method' => 'bkash', 'payment_status' => 'verified']);

        $this->assertStringContainsString('size: 80mm', $this->invoice($order, 'thermal')->getContent());
        $html = $this->invoice($order, 'thermal58')->getContent();
        $this->assertStringContainsString('size: 58mm', $html);
        $this->assertStringNotContainsString('DUE (COD)', $html, 'A verified prepaid order has nothing to collect');
    }

    public function test_unknown_format_falls_back_to_full_page(): void
    {
        $this->assertStringContainsString('class="fmt-a4"', $this->invoice($this->order(), 'poster')->getContent());
    }

    public function test_several_orders_print_together_two_per_sheet_on_half_page(): void
    {
        $numbers = [];
        foreach (range(1, 4) as $i) {
            $numbers[] = $this->order(['order_number' => "ORD-BULK-{$i}"])->order_number;
        }
        $admin = User::factory()->create(['role' => 'admin']);

        $html = $this->actingAs($admin)->get(route('admin.orders.invoices', ['orders' => $numbers, 'format' => 'half']))
            ->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '<div class="paper">'), '4 orders on half page should use 2 A4 sheets');
        $this->assertSame(4, substr_count($html, '<article class="inv inv--half'));
        $this->assertStringNotContainsString('Office copy', $html, 'Bulk half pages hold different orders, not copies');
        foreach ($numbers as $n) {
            $this->assertStringContainsString('#' . $n, $html);
        }

        $thermal = $this->actingAs($admin)->get(route('admin.orders.invoices', ['orders' => $numbers, 'format' => 'thermal']))->getContent();
        $this->assertSame(4, substr_count($thermal, 'data-receipt style='));
    }

    public function test_bulk_print_needs_at_least_one_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.orders.invoices'))->assertSessionHasErrors('orders');
    }
}
