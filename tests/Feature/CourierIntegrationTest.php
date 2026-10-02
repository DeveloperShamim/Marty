<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\CourierStatusUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        foreach ([
            'steadfast_enabled' => '1', 'steadfast_api_key' => 'k', 'steadfast_secret_key' => 's',
            'pathao_enabled' => '1', 'pathao_client_id' => 'c', 'pathao_client_secret' => 'cs', 'pathao_username' => 'u',
            'pathao_password' => 'p', 'pathao_store_id' => '7', 'redx_enabled' => '1', 'redx_api_token' => 't',
        ] as $k => $v) {
            Setting::put($k, $v);
        }
    }

    private function order(string $number, array $attrs = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '+8801711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1, Mirpur 10', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
        ], $attrs));
        $order->items()->create(['product_name' => 'Runner', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        return $order;
    }

    /* ---------- Booking ---------- */

    public function test_steadfast_booking_uses_the_live_api_and_collects_cash_only_for_cod(): void
    {
        Http::fake(['portal.packzy.com/api/v1/create_order' => Http::response(['status' => 200, 'consignment' => ['tracking_code' => 'SF123']])]);
        $cod = $this->order('ORD-COD');
        $bkashUnchecked = $this->order('ORD-BK', ['payment_method' => 'bkash']);

        $this->actingAs($this->admin)->post(route('admin.orders.dispatch-courier', [$cod, 'steadfast']))->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.orders.dispatch-courier', [$bkashUnchecked, 'steadfast']));

        $sent = Http::recorded()->map(fn ($pair) => $pair[0]);
        $this->assertSame([1060, 0], $sent->map(fn (HttpRequest $r) => (int) $r['cod_amount'])->all(), 'A prepaid order must never be collected again');
        $this->assertSame('01711000000', $sent->first()['recipient_phone']);
        $this->assertSame('SF123', $cod->fresh()->courier_tracking_code);
        $this->assertSame('shipped', $cod->fresh()->status);
    }

    public function test_an_order_cannot_be_booked_twice(): void
    {
        Http::fake();
        $order = $this->order('ORD-1', ['courier_name' => 'steadfast', 'courier_tracking_code' => 'SF1', 'status' => 'shipped']);

        $this->actingAs($this->admin)->post(route('admin.orders.dispatch-courier', [$order, 'pathao']))
            ->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_redx_refuses_to_guess_the_delivery_area(): void
    {
        Http::fake(['*/areas' => Http::response(['areas' => [['id' => 5, 'name' => 'Uttara', 'post_code' => 1230]]]), '*' => Http::response([], 500)]);
        $order = $this->order('ORD-RX', ['shipping_address' => 'House 1, Somewhere']);

        $this->actingAs($this->admin)->post(route('admin.orders.dispatch-courier', [$order, 'redx']))->assertSessionHas('error');
        Http::assertNotSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/parcel'));
        $this->assertNull($order->fresh()->courier_tracking_code);
    }

    public function test_pathao_logs_in_once_and_reuses_the_token(): void
    {
        Http::fake([
            '*/issue-token' => Http::response(['access_token' => 'TOKEN', 'expires_in' => 432000]),
            '*/orders/PA1/info' => Http::response(['data' => ['order_status' => 'In_Transit']]),
        ]);
        $order = $this->order('ORD-PA', ['courier_name' => 'pathao', 'courier_tracking_code' => 'PA1', 'status' => 'shipped']);
        $updater = app(CourierStatusUpdater::class);

        $updater->refresh($order);
        $updater->refresh($order);

        Http::assertSentCount(3); // one login + two status checks
        $this->assertSame('in_transit', $order->fresh()->courier_status);
    }

    /* ---------- Status sync ---------- */

    public function test_sync_marks_delivered_parcels_delivered_and_flags_returns(): void
    {
        Http::fake([
            'portal.packzy.com/api/v1/status_by_invoice/ORD-D' => Http::response(['status' => 200, 'delivery_status' => 'delivered']),
            'portal.packzy.com/api/v1/status_by_invoice/ORD-R' => Http::response(['status' => 200, 'delivery_status' => 'cancelled']),
            '*/parcel/info/RX9' => Http::response(['parcel' => ['status' => 'agent-hold']]),
        ]);
        $shipped = ['status' => 'shipped', 'courier_sent_at' => now()->subDay()];
        $delivered = $this->order('ORD-D', $shipped + ['courier_name' => 'steadfast', 'courier_tracking_code' => 'SFD']);
        $returning = $this->order('ORD-R', $shipped + ['courier_name' => 'steadfast', 'courier_tracking_code' => 'SFR']);
        $held = $this->order('ORD-H', $shipped + ['courier_name' => 'redx', 'courier_tracking_code' => 'RX9']);
        $manual = $this->order('ORD-M', $shipped + ['courier_name' => 'in_house']);

        $this->artisan('couriers:sync')->assertSuccessful();

        $delivered->refresh();
        $this->assertSame('delivered', $delivered->status);
        $this->assertSame('verified', $delivered->payment_status, 'Cash collected by the courier');
        $this->assertSame('shipped', $returning->fresh()->status, 'A return still has to be scanned in');
        $this->assertSame('returning', $returning->fresh()->courier_status);
        $this->assertSame('hold', $held->fresh()->courier_status);
        $this->assertNull($manual->fresh()->courier_synced_at);

        $this->actingAs($this->admin)->get(route('admin.courier-scan.index'))
            ->assertOk()->assertSee('ORD-R')->assertSee('Returning to you')->assertSee('On hold')->assertSee('2 need you');
    }

    public function test_refresh_button_on_the_order_page(): void
    {
        Http::fake(['*/status_by_invoice/ORD-1' => Http::response(['status' => 200, 'delivery_status' => 'delivered_approval_pending'])]);
        $order = $this->order('ORD-1', ['status' => 'shipped', 'courier_name' => 'steadfast', 'courier_tracking_code' => 'SF1']);

        $this->actingAs($this->admin)->post(route('admin.orders.courier-status', $order))->assertSessionHas('status');

        $this->assertSame('delivered', $order->fresh()->status);
    }

    /* ---------- Webhooks ---------- */

    public function test_webhooks_need_the_secret(): void
    {
        $order = $this->order('ORD-W', ['status' => 'shipped', 'courier_name' => 'steadfast', 'courier_tracking_code' => 'SFW']);

        $this->postJson('/webhooks/courier/steadfast/wrong', ['invoice' => 'ORD-W', 'status' => 'delivered'])->assertStatus(401);
        $this->postJson('/webhooks/courier/steadfast', ['invoice' => 'ORD-W', 'status' => 'delivered'])->assertStatus(401);

        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_steadfast_webhook_with_bearer_token_delivers_the_order(): void
    {
        $order = $this->order('ORD-W', ['status' => 'shipped', 'courier_name' => 'steadfast', 'courier_tracking_code' => 'SFW']);

        $this->withToken(courier_webhook_secret())->postJson('/webhooks/courier/steadfast', [
            'notification_type' => 'delivery_status', 'consignment_id' => 999, 'invoice' => 'ORD-W',
            'status' => 'Delivered', 'tracking_message' => 'Delivered to customer',
        ])->assertOk()->assertJson(['status' => 'success']);

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('Delivered to customer', $order->courier_status_message);
    }

    public function test_redx_and_pathao_webhooks(): void
    {
        $rx = $this->order('ORD-RX', ['status' => 'shipped', 'courier_name' => 'redx', 'courier_tracking_code' => '21A427TU4BN3R']);
        $pa = $this->order('ORD-PA', ['status' => 'shipped', 'courier_name' => 'pathao', 'courier_tracking_code' => 'DL121224VS8TTJ']);
        $secret = courier_webhook_secret();

        $this->postJson("/webhooks/courier/redx/{$secret}", ['tracking_number' => '21A427TU4BN3R', 'status' => 'agent-returning', 'message_en' => 'Customer refused'])
            ->assertOk();
        $this->postJson("/webhooks/courier/pathao/{$secret}", ['consignment_id' => 'DL121224VS8TTJ', 'merchant_order_id' => 'ORD-PA', 'event' => 'order.delivered'])
            ->assertStatus(202)->assertHeader('X-Pathao-Merchant-Webhook-Integration-Secret');
        $this->postJson("/webhooks/courier/redx/{$secret}", ['tracking_number' => 'NOPE', 'status' => 'delivered'])->assertOk();

        $this->assertSame('returning', $rx->fresh()->courier_status);
        $this->assertSame('shipped', $rx->fresh()->status);
        $this->assertSame('delivered', $pa->fresh()->status);
    }

    public function test_status_vocabulary(): void
    {
        foreach ([
            'delivered' => 'delivered', 'Delivered' => 'delivered', 'partial_delivered_approval_pending' => 'partial_delivered',
            'cancelled' => 'returning', 'hold' => 'hold', 'In_Transit' => 'in_transit', 'Pickup_Cancelled' => 'cancelled',
            'order.delivered' => 'delivered', 'Return' => 'returning', 'Delivery_Failed' => 'hold',
            'delivery-in-progress' => 'in_transit', 'agent-returning' => 'returning', 'returned' => 'returned', 'something new' => 'unknown',
        ] as $raw => $expected) {
            $this->assertSame($expected, CourierStatusUpdater::normalize($raw), $raw);
        }
    }
}
