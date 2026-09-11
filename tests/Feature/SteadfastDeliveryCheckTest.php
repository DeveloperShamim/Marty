<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\Courier\SteadfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SteadfastDeliveryCheckTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(): Order
    {
        return Order::create([
            'order_number'     => 'ORD-TEST-1234',
            'customer_name'    => 'Test Customer',
            'customer_phone'   => '01711223344',
            'shipping_address' => 'House 1, Road 2',
            'city'             => 'Dhaka',
            'shipping_zone'    => 'inside_dhaka',
            'subtotal'         => 1000,
            'shipping_charge'  => 60,
            'total'            => 1060,
            'payment_method'   => 'cod',
            'payment_status'   => 'pending',
            'status'           => 'pending',
        ]);
    }

    public function test_steadfast_delivery_check_returns_unconfigured_when_keys_missing(): void
    {
        Setting::put('steadfast_enabled', '0');
        Setting::put('steadfast_api_key', '');
        Setting::put('steadfast_secret_key', '');

        $service = app(SteadfastService::class);
        $result = $service->checkDeliveryHistory('01711223344');

        $this->assertFalse($result['configured']);
        $this->assertEquals('unconfigured', $result['risk_level']);
    }

    public function test_steadfast_delivery_check_calculates_rates_correctly(): void
    {
        Setting::put('steadfast_enabled', '1');
        Setting::put('steadfast_api_key', 'test_key');
        Setting::put('steadfast_secret_key', 'test_secret');

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/fraud_check/*' => Http::response([
                'status'              => 200,
                'Total_parcels'       => 20,
                'total_delivered'     => 18,
                'total_cancelled'     => 2,
                'total_fraud_reports' => [],
            ], 200),
        ]);

        $service = app(SteadfastService::class);
        $result = $service->checkDeliveryHistory('01711223344', true);

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['success']);
        $this->assertEquals(20, $result['total']);
        $this->assertEquals(18, $result['delivered']);
        $this->assertEquals(2, $result['cancelled']);
        $this->assertEquals(90.0, $result['rate']);
        $this->assertEquals('low', $result['risk_level']);
    }

    public function test_admin_order_show_displays_steadfast_delivery_check(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrder();

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Steadfast Courier Delivery History');
    }
}
