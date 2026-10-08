<?php

namespace Tests\Feature;

use App\Models\MobileApiToken;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number'     => 'ORD-2026-0001',
            'customer_name'    => 'Test Customer',
            'customer_phone'   => '01711000000',
            'shipping_address' => 'House 1, Road 2',
            'city'             => 'Dhaka',
            'subtotal'         => 500,
            'shipping_charge'  => 60,
            'total'            => 560,
            'payment_method'   => 'cod',
            'status'           => 'pending',
        ], $overrides));
    }

    private function tokenFor(User $user): string
    {
        return MobileApiToken::issue($user, 'test');
    }

    public function test_staff_can_log_in_and_customers_cannot(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123', 'role' => 'admin']);
        User::factory()->create(['email' => 'buyer@example.com', 'password' => 'secret123', 'role' => 'customer']);

        $this->postJson('/api/mobile/v1/login', ['email' => 'admin@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'role', 'can_manage_orders']]);

        $this->postJson('/api/mobile/v1/login', ['email' => 'admin@example.com', 'password' => 'wrong'])
            ->assertStatus(422);

        $this->postJson('/api/mobile/v1/login', ['email' => 'buyer@example.com', 'password' => 'secret123'])
            ->assertForbidden();
    }

    public function test_requests_without_valid_token_are_rejected(): void
    {
        $this->getJson('/api/mobile/v1/dashboard')->assertUnauthorized();
        $this->withToken('nope')->getJson('/api/mobile/v1/orders')->assertUnauthorized();
    }

    public function test_logout_revokes_token(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'admin']));

        $this->withToken($token)->postJson('/api/mobile/v1/logout')->assertOk();
        $this->assertSame(0, MobileApiToken::count());
    }

    public function test_inventory_manager_cannot_manage_orders(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'inventory_manager']));

        $this->withToken($token)->getJson('/api/mobile/v1/dashboard')->assertOk();
        $this->withToken($token)->getJson('/api/mobile/v1/orders')->assertForbidden();
    }

    public function test_dashboard_and_order_listing(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'order_manager']));
        $this->makeOrder(['payment_status' => 'verified', 'status' => 'confirmed']);
        $this->makeOrder(['order_number' => 'ORD-2026-0002']);

        $this->withToken($token)->getJson('/api/mobile/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('revenue.total', 560)
            ->assertJsonPath('orders.pending_verification', 1);

        $this->withToken($token)->getJson('/api/mobile/v1/orders?status=pending_verification')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_number', 'ORD-2026-0002');

        $this->withToken($token)->getJson('/api/mobile/v1/orders/ORD-2026-0001')
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');
    }

    public function test_update_verify_and_reject(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'admin']));
        $order = $this->makeOrder();

        $this->withToken($token)->postJson("/api/mobile/v1/orders/{$order->order_number}/verify")
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'verified')
            ->assertJsonPath('data.status', 'confirmed');

        $this->withToken($token)->patchJson("/api/mobile/v1/orders/{$order->order_number}", ['status' => 'processing', 'internal_note' => 'Packed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'processing')
            ->assertJsonPath('data.internal_note', 'Packed');

        $this->withToken($token)->patchJson("/api/mobile/v1/orders/{$order->order_number}", ['status' => 'bogus'])
            ->assertUnprocessable();

        $this->withToken($token)->postJson("/api/mobile/v1/orders/{$order->order_number}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_dispatch_to_courier(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'admin']));
        Setting::put('steadfast_enabled', '1');
        Setting::put('steadfast_api_key', 'k');
        Setting::put('steadfast_secret_key', 's');
        $order = $this->makeOrder(['status' => 'confirmed']);

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status'      => 200,
                'consignment' => ['tracking_code' => 'SFR-TRACK-1'],
            ]),
        ]);

        $this->withToken($token)->postJson("/api/mobile/v1/orders/{$order->order_number}/courier/steadfast")
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped')
            ->assertJsonPath('data.courier.tracking_code', 'SFR-TRACK-1');
    }

    public function test_scan_finds_order_by_number_tracking_code_or_url(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'admin']));
        $this->makeOrder(['courier_name' => 'steadfast', 'courier_tracking_code' => 'SFR123']);

        $this->withToken($token)->getJson('/api/mobile/v1/scan?code=ORD-2026-0001')
            ->assertOk()->assertJsonPath('data.order_number', 'ORD-2026-0001');

        $this->withToken($token)->getJson('/api/mobile/v1/scan?code=SFR123')
            ->assertOk()->assertJsonPath('data.order_number', 'ORD-2026-0001');

        $this->withToken($token)->getJson('/api/mobile/v1/scan?code='.urlencode('https://steadfast.com.bd/t/SFR123'))
            ->assertOk()->assertJsonPath('data.order_number', 'ORD-2026-0001');

        $this->withToken($token)->getJson('/api/mobile/v1/scan?code=UNKNOWN')
            ->assertNotFound();
    }

    public function test_attach_scanned_tracking_code(): void
    {
        $token = $this->tokenFor(User::factory()->create(['role' => 'admin']));
        $order = $this->makeOrder(['status' => 'processing']);
        $this->makeOrder(['order_number' => 'ORD-2026-0002', 'courier_name' => 'redx', 'courier_tracking_code' => 'RX-1']);

        $this->withToken($token)->postJson("/api/mobile/v1/orders/{$order->order_number}/tracking", ['courier_name' => 'Pathao', 'tracking_code' => 'PT-9'])
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped')
            ->assertJsonPath('data.courier.name', 'pathao')
            ->assertJsonPath('data.courier.tracking_code', 'PT-9');

        $this->withToken($token)->postJson("/api/mobile/v1/orders/{$order->order_number}/tracking", ['courier_name' => 'redx', 'tracking_code' => 'RX-1'])
            ->assertUnprocessable();
    }
}
