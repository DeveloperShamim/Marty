<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_and_save_api_integrations_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $pageResponse = $this->actingAs($admin)->get(route('admin.integrations.index'));
        $pageResponse->assertOk()->assertSee('API Integrations');

        $response = $this->actingAs($admin)->put(route('admin.integrations.update', 'couriers'), [
            'steadfast_enabled'    => '1',
            'steadfast_api_key'    => 'test_st_key',
            'steadfast_secret_key' => 'test_st_secret',
            'pathao_enabled'       => '1',
            'pathao_env'           => 'sandbox',
            'pathao_client_id'     => 'test_client_id',
            'pathao_client_secret' => 'test_client_secret',
            'pathao_username'      => 'test@pathao.com',
            'pathao_password'      => 'password',
            'pathao_store_id'      => '12345',
            'redx_enabled'         => '1',
            'redx_env'             => 'sandbox',
            'redx_api_token'       => 'test_redx_token',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1', Setting::get('steadfast_enabled'));
        $this->assertEquals('test_st_key', Setting::get('steadfast_api_key'));
        $this->assertEquals('1', Setting::get('pathao_enabled'));
        $this->assertEquals('test_redx_token', Setting::get('redx_api_token'));
    }

    public function test_order_courier_helpers_and_dispatch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::put('steadfast_enabled', '1');
        Setting::put('steadfast_api_key', 'test_key');
        Setting::put('steadfast_secret_key', 'test_secret');

        $order = Order::create([
            'order_number'      => 'ORD-2026-TEST',
            'customer_name'     => 'Test Customer',
            'customer_phone'    => '01711000000',
            'shipping_address'  => 'House 1, Road 2',
            'city'              => 'Dhaka',
            'subtotal'          => 500,
            'shipping_charge'   => 60,
            'total'             => 560,
            'payment_method'    => 'cod',
            'status'            => 'pending',
        ]);

        $this->assertFalse($order->isDispatchedToCourier());

        Http::fake([
            'https://portal.steadfast.com.bd/api/v1/create_order' => Http::response([
                'status'      => 200,
                'message'     => 'Success',
                'consignment' => ['consignment_id' => 'STD998877', 'tracking_code' => 'STD998877'],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.dispatch-courier', [$order, 'steadfast']));

        $response->assertRedirect();
        $order->refresh();

        $this->assertTrue($order->isDispatchedToCourier());
        $this->assertEquals('steadfast', $order->courier_name);
        $this->assertEquals('STD998877', $order->courier_tracking_code);
        $this->assertEquals('shipped', $order->status);
        $this->assertEquals('Steadfast Courier', $order->courierLabel());
        $this->assertStringContainsString('steadfast.com.bd/t/STD998877', $order->courierTrackingUrl());
    }
}
