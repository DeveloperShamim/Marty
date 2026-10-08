<?php

namespace Tests\Feature;

use App\Models\AbandonedCart;
use App\Models\CustomerCourierCheck;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardActionCardsTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending',
        ], $attrs));
    }

    public function test_dashboard_lists_risky_orders_carts_returns_and_ad_spend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->order('ORD-FRAUD', ['customer_name' => 'Fraud Score Buyer', 'customer_phone' => '01811000001', 'fraud_score' => 60, 'fraud_flags' => ['Phone used on 4 orders today']]);
        $this->order('ORD-RETURNS', ['customer_name' => 'Many Returns Buyer', 'customer_phone' => '+8801911000002']);
        CustomerCourierCheck::create(['phone' => '01911000002', 'total_parcels' => 10, 'success_ratio' => 30, 'couriers' => [], 'reports' => [], 'checked_at' => now()]);
        $this->order('ORD-SAFE', ['customer_name' => 'Safe Buyer', 'customer_phone' => '01611000003']);
        $this->order('ORD-SHIPPED', ['customer_name' => 'Already Shipped', 'status' => 'shipped', 'fraud_score' => 80]);

        $this->order('ORD-RET', ['status' => 'returned', 'city' => 'Chattogram', 'courier_loss_amount' => 120]);
        $this->order('ORD-DEL', ['status' => 'delivered', 'city' => 'Chattogram', 'payment_status' => 'verified']);

        AbandonedCart::create(['customer_name' => 'Cart Rahim', 'customer_phone' => '01711222333', 'cart_data' => [], 'subtotal' => 900, 'total' => 900, 'recovery_token' => Str::random(40)]);
        Expense::create(['title' => 'Facebook boost', 'category' => 'marketing', 'amount' => 500, 'expense_date' => now()->toDateString()]);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        $this->assertStringContainsString('Risky orders to call', $html);
        $risky = \Illuminate\Support\Str::between($html, 'Risky orders to call', 'Delivery Success');
        $this->assertStringContainsString('Fraud Score Buyer', $risky);
        $this->assertStringContainsString('Phone used on 4 orders today', $risky);
        $this->assertStringContainsString('Many Returns Buyer', $risky);
        $this->assertStringContainsString('30% delivered', $risky);
        $this->assertStringNotContainsString('Safe Buyer', $risky);
        $this->assertStringNotContainsString('Already Shipped', $risky);

        $this->assertStringContainsString('Cart Rahim', $html);
        $this->assertStringContainsString('https://wa.me/8801711222333', $html);

        $this->assertStringContainsString('Return loss', $html);
        $this->assertStringContainsString('Chattogram', $html);
        $this->assertStringContainsString('1 of 2', $html);

        $this->assertStringContainsString('Ad spend vs sales', $html);
        $this->assertStringNotContainsString('Sales Report', $html, 'The six-month chart repeated Monthly Revenue');
        $this->assertStringNotContainsString('Fulfillment Overview', $html, 'Repeated the order rings');
    }
}
