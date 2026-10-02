<?php

namespace Tests\Feature;

use App\Jobs\CheckCustomerCourierHistory;
use App\Models\Blacklist;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\BdCourierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CodAutoConfirmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::put('bdcourier_api_token', 'TOKEN123');
        Setting::put('cod_auto_confirm', '1');
    }

    private function fakeBdCourier(int $total, int $delivered, array $reports = []): void
    {
        Http::fake(['api.bdcourier.com/courier-check' => Http::response([
            'status' => 'success',
            'data' => ['summary' => ['total_parcel' => $total, 'success_parcel' => $delivered, 'cancelled_parcel' => $total - $delivered,
                'success_ratio' => $total ? round($delivered / $total * 100, 2) : 0]],
            'reports' => $reports,
        ])]);
    }

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending', 'fraud_score' => 10, 'fraud_flags' => [],
        ], $attrs));
    }

    private function runCheck(Order $order): Order
    {
        (new CheckCustomerCourierHistory($order->id))->handle(app(BdCourierService::class));

        return $order->fresh();
    }

    public function test_good_courier_history_confirms_the_order_but_payment_stays_pending(): void
    {
        $this->fakeBdCourier(20, 18);

        $order = $this->runCheck($this->order('VB-100001'));

        $this->assertSame('confirmed', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('90% courier delivery success (18 of 20 parcels)', $order->auto_confirmed_reason);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Auto-confirmed: 90% courier delivery success', false)
            ->assertSee('Customer delivery history');
    }

    public function test_new_risky_or_reported_customers_stay_pending(): void
    {
        $history = ['01711000000' => [0, 0, []], '01811000000' => [10, 6, []], '01911000000' => [20, 19, [['name' => 'Shop X', 'details' => 'Fake order']]]];
        Http::fake(function ($request) use ($history) {
            [$total, $delivered, $reports] = $history[$request['phone']];

            return Http::response(['status' => 'success', 'reports' => $reports, 'data' => ['summary' => [
                'total_parcel' => $total, 'success_parcel' => $delivered, 'cancelled_parcel' => $total - $delivered,
                'success_ratio' => $total ? round($delivered / $total * 100, 2) : 0]]]);
        });

        foreach (['VB-100002' => '01711000000', 'VB-100003' => '01811000000', 'VB-100004' => '01911000000'] as $number => $phone) {
            $order = $this->runCheck($this->order($number, ['customer_phone' => $phone]));
            $this->assertSame('pending', $order->status, $phone);
            $this->assertNull($order->auto_confirmed_reason);
        }
        $this->assertSame([0, 10, 20], \App\Models\CustomerCourierCheck::orderBy('phone')->pluck('total_parcels')->all());
    }

    public function test_repeat_customer_with_no_returns_is_confirmed_without_a_courier_search(): void
    {
        Http::fake();
        $this->order('VB-100010', ['status' => 'delivered', 'payment_status' => 'verified']);
        $this->order('VB-100011', ['status' => 'delivered', 'payment_status' => 'verified']);

        $order = $this->runCheck($this->order('VB-100012'));

        Http::assertNothingSent(); // trusted repeat customers skip the BD Courier search
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('2 earlier orders delivered in your shop', $order->auto_confirmed_reason);
    }

    public function test_switch_off_blacklist_fraud_score_and_prepaid_orders_are_left_alone(): void
    {
        $this->fakeBdCourier(20, 20);

        Setting::put('cod_auto_confirm', '0');
        $this->assertSame('pending', $this->runCheck($this->order('VB-100020'))->status);
        Setting::put('cod_auto_confirm', '1');

        Blacklist::create(['type' => 'phone', 'value' => '01611000000', 'reason' => 'test']);
        $this->assertSame('pending', $this->runCheck($this->order('VB-100021', ['customer_phone' => '01611000000']))->status);

        $this->assertSame('pending', $this->runCheck($this->order('VB-100022', ['fraud_score' => 40]))->status);

        $prepaid = $this->runCheck($this->order('VB-100023', ['payment_method' => 'bkash']));
        $this->assertSame('pending', $prepaid->status);
        $this->assertNull($prepaid->auto_confirmed_reason);
    }

    public function test_admin_turns_it_on_in_payment_settings(): void
    {
        Setting::put('cod_auto_confirm', '0');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Auto-confirm safe COD orders');
        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'payments'), ['cod_auto_confirm' => '1', 'pay_cod_enabled' => '1'])
            ->assertOk();

        $this->assertSame('1', setting('cod_auto_confirm'));
    }
}
