<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_orders_follow_the_stock_and_return_rules(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, Order::count());

        foreach (Order::where('status', 'returned')->get() as $order) {
            $this->assertTrue($order->stock_restored, "{$order->order_number}: restocked return must be marked stock_restored");
            $expectedLoss = $order->return_type === 'unpaid_delivery' ? (float) $order->shipping_charge : 0.0;
            $this->assertEquals($expectedLoss, (float) $order->courier_loss_amount, "{$order->order_number}: wrong courier loss");
        }

        // Cash-on-delivery parcels still with the courier are not paid yet.
        $this->assertSame(0, Order::where('status', 'shipped')->where('payment_method', 'cod')->where('payment_status', 'verified')->count());
    }

    public function test_dashboard_and_analytics_agree_on_seeded_data(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->original->getData();
        $analytics = $this->actingAs($admin)->get(route('admin.analytics.index', ['range' => 'last_30_days']))->assertOk()->original->getData();

        // All seeded orders are within the last 30 days, so both pages must report the same totals.
        $this->assertEqualsWithDelta($dashboard['revenue'], $analytics['grossRevenue'], 0.01);
        $this->assertEqualsWithDelta($dashboard['totalCogs'], $analytics['cogs'], 0.01);
        $this->assertEqualsWithDelta($dashboard['profitBeforeExpenses'], $analytics['netProfit'], 0.01);
        // Dashboard Net Profit is after every logged expense.
        $this->assertEqualsWithDelta($dashboard['profitBeforeExpenses'] - \App\Models\Expense::sum('amount'), $dashboard['netProfit'], 0.01);
    }
}
