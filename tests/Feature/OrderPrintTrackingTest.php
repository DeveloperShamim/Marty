<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPrintTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Rahim']);
    }

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
        ], $attrs));
    }

    private function record(array $numbers, string $type = 'invoice', ?string $format = 'half')
    {
        return $this->actingAs($this->admin)->postJson(route('admin.orders.prints.record'), array_filter([
            'orders' => $numbers, 'type' => $type, 'format' => $format,
        ]));
    }

    public function test_printing_an_invoice_is_recorded_and_moves_confirmed_orders_to_processing(): void
    {
        $confirmed = $this->order('ORD-A');
        $unverified = $this->order('ORD-B', ['status' => 'pending']);
        $shipped = $this->order('ORD-C', ['status' => 'shipped']);

        $this->record(['ORD-A', 'ORD-B', 'ORD-C'])->assertOk()->assertJson(['recorded' => 3, 'moved_to_processing' => ['ORD-A']]);

        $this->assertSame('processing', $confirmed->fresh()->status);
        $this->assertSame('pending', $unverified->fresh()->status, 'Unverified orders are not moved to packing');
        $this->assertSame('shipped', $shipped->fresh()->status);
        $this->assertSame(3, \App\Models\OrderPrint::where('type', 'invoice')->where('user_id', $this->admin->id)->count());
    }

    public function test_printing_a_label_is_recorded_without_changing_status(): void
    {
        $order = $this->order('ORD-L');

        $this->record(['ORD-L'], 'label', null)->assertOk()->assertJson(['moved_to_processing' => []]);

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(['label'], $order->prints()->pluck('type')->all());
    }

    public function test_print_status_lists_already_printed_orders_per_type(): void
    {
        $this->order('ORD-1');
        $this->order('ORD-2');
        $this->record(['ORD-1']);
        $this->record(['ORD-1']);

        $this->actingAs($this->admin)->getJson(route('admin.orders.prints.status', ['orders' => ['ORD-1', 'ORD-2']]))
            ->assertOk()
            ->assertJsonCount(1, 'printed.invoice')
            ->assertJsonPath('printed.invoice.0.order_number', 'ORD-1')
            ->assertJsonPath('printed.invoice.0.times', 2)
            ->assertJsonCount(0, 'printed.label');
    }

    public function test_invoice_page_warns_when_the_order_was_already_printed(): void
    {
        $order = $this->order('ORD-W');
        $url = route('admin.orders.invoice', $order);

        $this->actingAs($this->admin)->get($url)->assertOk()->assertDontSee('already printed');

        $this->record(['ORD-W']);

        $this->actingAs($this->admin)->get($url)->assertOk()
            ->assertSee('This order was already printed')
            ->assertSee('Rahim, today');
    }

    public function test_not_printed_tab_is_the_packing_queue(): void
    {
        $this->order('ORD-NEW');
        $this->order('ORD-DONE');
        $this->order('ORD-UNVERIFIED', ['status' => 'pending']);
        $this->record(['ORD-DONE']);

        $html = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'not_printed']))->assertOk()->getContent();

        $this->assertStringContainsString('ORD-NEW', $html);
        $this->assertStringNotContainsString('>ORD-DONE', $html);
        $this->assertStringNotContainsString('ORD-UNVERIFIED', $html);

        $all = $this->actingAs($this->admin)->get(route('admin.orders.index'))->getContent();
        $this->assertStringContainsString('The invoice for ORD-DONE was already printed', $all, 'Reprint warning on the list');
    }
}
