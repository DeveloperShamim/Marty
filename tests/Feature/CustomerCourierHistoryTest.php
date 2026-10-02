<?php

namespace Tests\Feature;

use App\Jobs\CheckCustomerCourierHistory;
use App\Models\CustomerCourierCheck;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\BdCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerCourierHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        Setting::put('bdcourier_api_token', 'TOKEN123');
    }

    /** Response in the shape the official BD Courier WordPress plugin reads. */
    private function fakeBdCourier(int $total = 10, int $delivered = 4, array $reports = []): void
    {
        Http::fake(['api.bdcourier.com/courier-check' => Http::response([
            'status' => 'success',
            'data' => [
                'pathao'    => ['name' => 'Pathao', 'logo' => 'x.png', 'total_parcel' => $total - 2, 'success_parcel' => $delivered - 1, 'cancelled_parcel' => ($total - 2) - ($delivered - 1)],
                'steadfast' => ['name' => 'Steadfast', 'total_parcel' => 2, 'success_parcel' => 1, 'cancelled_parcel' => 1],
                'redx'      => ['name' => 'RedX', 'total_parcel' => 0, 'success_parcel' => 0, 'cancelled_parcel' => 0],
                'summary'   => ['total_parcel' => $total, 'success_parcel' => $delivered, 'cancelled_parcel' => $total - $delivered, 'success_ratio' => $total ? round($delivered / $total * 100, 2) : 0],
            ],
            'reports' => $reports,
        ])]);
    }

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '+880 1711-000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending', 'fraud_score' => 10, 'fraud_flags' => [],
        ], $attrs));
    }

    public function test_lookup_is_saved_per_phone_and_reused_for_a_week(): void
    {
        $this->fakeBdCourier(10, 4);
        $service = app(BdCourierService::class);

        $first = $service->check('+880 1711-000000');
        $again = $service->check('01711000000');

        $this->assertTrue($first['success']);
        $this->assertTrue($again['cached']);
        Http::assertSentCount(1);
        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer TOKEN123') && $r['phone'] === '01711000000');

        $check = CustomerCourierCheck::firstOrFail();
        $this->assertSame([10, 4, 6], [$check->total_parcels, $check->delivered, $check->cancelled]);
        $this->assertSame(40.0, $check->success_ratio);
        $this->assertSame('high', $check->riskLevel());
        $this->assertSame(['Pathao', 'Steadfast', 'RedX'], array_column($check->couriers, 'name'));

        $this->travel(8)->days();
        $service->check('01711000000');
        Http::assertSentCount(2);
    }

    public function test_new_cod_order_is_checked_and_its_risk_raised(): void
    {
        $this->fakeBdCourier(10, 3, [['courierName' => 'Steadfast', 'name' => 'Shop X', 'details' => 'Fake order, phone off', 'created_at' => '2026-09-01']]);
        $order = $this->order('ORD-1');

        (new CheckCustomerCourierHistory($order->id))->handle(app(BdCourierService::class));

        $order->refresh();
        $this->assertSame(70, $order->fraud_score); // 10 + 30 (fraud report) + 30 (30% delivered)
        $this->assertCount(2, $order->fraud_flags);
        $this->assertSame('high', $order->fraudRiskLevel());

        // Running again must not add the points twice.
        (new CheckCustomerCourierHistory($order->id))->handle(app(BdCourierService::class));
        $this->assertSame(70, $order->fresh()->fraud_score);
    }

    public function test_searches_are_not_spent_on_prepaid_trusted_or_disabled_orders(): void
    {
        Http::fake();
        $prepaid = $this->order('ORD-BK', ['payment_method' => 'bkash']);
        $this->order('OLD-1', ['customer_phone' => '01811000000', 'status' => 'delivered']);
        $this->order('OLD-2', ['customer_phone' => '01811000000', 'status' => 'delivered']);
        $trusted = $this->order('ORD-T', ['customer_phone' => '01811000000']);

        foreach ([$prepaid, $trusted] as $o) {
            (new CheckCustomerCourierHistory($o->id))->handle(app(BdCourierService::class));
        }
        Setting::put('bdcourier_auto_check', '0');
        $cod = $this->order('ORD-C', ['customer_phone' => '01911000000']);
        (new CheckCustomerCourierHistory($cod->id))->handle(app(BdCourierService::class));

        Http::assertNothingSent();
    }

    public function test_order_page_shows_history_and_check_now_refreshes_it(): void
    {
        $this->fakeBdCourier(20, 18);
        $order = $this->order('ORD-P');
        $this->order('ORD-OLD', ['status' => 'returned']);

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Customer delivery history')->assertSee('Not checked yet')->assertSee('1 earlier order');

        $this->actingAs($this->admin)->post(route('admin.orders.courier-history', $order))->assertSessionHas('status');

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Reliable customer')->assertSee('90%')->assertSee('Steadfast');
    }

    public function test_new_order_popup_includes_the_history(): void
    {
        $this->fakeBdCourier(10, 9);
        $old = $this->order('ORD-A');
        $new = $this->order('ORD-B');
        app(BdCourierService::class)->check($new->customer_phone);

        $this->actingAs($this->admin)->getJson(route('admin.orders.feed', ['after' => $old->id]))
            ->assertJsonPath('orders.0.history.delivered', 9)
            ->assertJsonPath('orders.0.history.level', 'low');
    }

    public function test_bad_token_and_plan_check(): void
    {
        Http::fake([
            'api.bdcourier.com/courier-check' => Http::response(['status' => 'error', 'message' => 'Invalid API key.'], 401),
            'api.bdcourier.com/my-plan' => Http::response(['data' => ['plan_name' => 'Starter', 'status' => 'active', 'remaining_paid_calls' => 47, 'remaining_free_calls' => 0]]),
        ]);

        $this->assertSame('BD Courier: Invalid API key.', app(BdCourierService::class)->check('01711000000')['message']);
        $this->assertDatabaseCount('customer_courier_checks', 0);

        $this->actingAs($this->admin)->getJson(route('admin.integrations.bdcourier-plan'))
            ->assertOk()->assertJson(['success' => true, 'plan' => 'Starter', 'remaining' => 47]);
    }
}
