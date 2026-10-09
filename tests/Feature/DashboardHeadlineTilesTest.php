<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHeadlineTilesTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, array $attrs, $createdAt): Order
    {
        $order = Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending',
        ], $attrs));
        $order->forceFill(['created_at' => $createdAt])->save();

        return $order;
    }

    public function test_tiles_show_today_this_month_and_this_months_profit(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(10)->setTime(15, 0));
        $admin = User::factory()->create(['role' => 'admin']);

        // Today: a new cash on delivery order counts as placed even though nothing is collected yet; cancelled ones don't
        $this->order('ORD-TODAY', [], now());
        $this->order('ORD-CANCEL', ['status' => 'cancelled', 'total' => 9999], now());
        $this->order('ORD-YDAY', ['total' => 500], now()->subDay());
        // This month's realized sale, and one from last month
        $this->order('ORD-MONTH', ['status' => 'delivered', 'subtotal' => 2000, 'total' => 2060], now()->subDays(3));
        $this->order('ORD-LAST', ['status' => 'delivered', 'subtotal' => 700, 'total' => 760], now()->subMonth());
        Expense::create(['title' => 'Boost', 'category' => 'marketing', 'amount' => 300, 'expense_date' => now()->toDateString()]);
        Expense::create(['title' => 'Old boost', 'category' => 'marketing', 'amount' => 900, 'expense_date' => now()->subMonth()->toDateString()]);

        $data = $this->actingAs($admin)->get('/admin')->assertOk()->original->getData();

        $this->assertSame(1, $data['todayPlacedCount']);
        $this->assertEqualsWithDelta(1060, $data['todayPlacedValue'], 0.01);
        $this->assertEqualsWithDelta(500, $data['yesterdayPlacedValue'], 0.01);
        $this->assertEqualsWithDelta(2000, $data['thisMonthRevenue'], 0.01);
        $this->assertEqualsWithDelta(700, $data['lastMonthRevenue'], 0.01);
        // 2000 sales - 300 of this month's expenses (no product cost recorded); last month's expense is left out
        $this->assertEqualsWithDelta(1700, $data['monthProfit'], 0.01);

        $html = $this->get('/admin')->getContent();
        $this->assertStringContainsString('Net Profit this month', $html);
        $this->assertStringContainsString('85% kept', $html);
        $this->assertStringNotContainsString('Total Sales', $html);
        $this->assertStringNotContainsString('Avg. order', $html);
    }
}
