<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Setting;
use App\Models\User;
use App\Services\CodAutoConfirm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderActivityTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'VB-300001', 'customer_name' => 'Karim', 'customer_phone' => '01711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending',
        ], $attrs));
    }

    public function test_staff_logs_calls_and_notes_and_sees_who_did_it(): void
    {
        $rina = User::factory()->create(['role' => 'order_manager', 'name' => 'Rina']);
        $order = $this->order();

        $this->actingAs($rina)->post(route('admin.orders.activities.store', $order), ['call_result' => 'no_answer'])
            ->assertSessionHasNoErrors();
        $this->actingAs($rina)->post(route('admin.orders.activities.store', $order), ['body' => 'Customer prefers delivery after 5 PM'])
            ->assertSessionHasNoErrors();
        $this->actingAs($rina)->post(route('admin.orders.activities.store', $order), [])->assertSessionHasErrors('body');

        $this->assertSame(['note', 'call'], $order->activities()->pluck('type')->all());
        $this->assertSame('Rina', $order->activities()->first()->staff_name);
        $this->assertSame('pending', $order->fresh()->status); // "No answer" changes nothing

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Calls &amp; staff notes', false)
            ->assertSee('Last call')->assertSee('No answer')
            ->assertSee('Customer prefers delivery after 5 PM')
            ->assertSee('Courier &amp; invoice note', false);
    }

    public function test_confirmed_call_confirms_the_order_and_records_who(): void
    {
        $karim = User::factory()->create(['role' => 'admin', 'name' => 'Karim Staff']);
        $order = $this->order();

        $this->actingAs($karim)->post(route('admin.orders.activities.store', $order), ['call_result' => 'confirmed', 'body' => 'Will pay cash'])
            ->assertSessionHas('status');

        $this->assertSame('confirmed', $order->fresh()->status);
        $status = OrderActivity::where('type', 'status')->firstOrFail();
        $this->assertSame('Karim Staff', $status->staff_name);
        $this->assertSame('Status: Pending → Confirmed', $status->body);

        $this->actingAs($karim)->get(route('admin.orders.show', $order))->assertSee('Confirmed by');
    }

    public function test_status_payment_and_auto_confirm_changes_are_logged(): void
    {
        $staff = User::factory()->create(['role' => 'admin', 'name' => 'Admin Rahim']);
        $order = $this->order(['order_number' => 'VB-300002']);

        $this->actingAs($staff)->patch(route('admin.orders.update', $order), ['status' => 'delivered', 'payment_status' => 'pending']);
        $this->assertSame(['Payment: Pending → Verified', 'Status: Pending → Delivered'],
            $order->activities()->pluck('body')->all());
        $this->assertSame(['Admin Rahim'], $order->activities()->pluck('staff_name')->unique()->values()->all());

        auth()->logout();
        Setting::put('cod_auto_confirm', '1');
        $repeat = $this->order(['order_number' => 'VB-300003']);
        CodAutoConfirm::apply($repeat, null);
        $log = $repeat->activities()->firstOrFail();
        $this->assertSame('System', $log->staff_name);
        $this->assertStringContainsString('auto-confirmed: 1 earlier order delivered in your shop', $log->body);
    }
}
