<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardToShipTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, string $status, int $hoursAgo): void
    {
        $o = Order::create([
            'order_number' => $number, 'customer_name' => 'Buyer ' . $number, 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => $status,
        ]);
        $o->forceFill(['created_at' => now()->subHours($hoursAgo)])->save();
    }

    public function test_board_shows_each_step_with_the_oldest_orders_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach ([1, 30, 5, 50, 2] as $i => $h) {
            $this->order('ORD-P' . $i, 'pending', $h);
        }
        $this->order('ORD-C1', 'confirmed', 3);
        $this->order('ORD-S1', 'shipped', 3);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();
        $board = Str::betweenFirst($html, 'data-to-ship', '</section>');

        $this->assertStringContainsString('6 orders not with the courier yet', $board);
        $pending = Str::betweenFirst($board, 'data-ship-col="pending"', 'data-ship-col="confirmed"');
        // Oldest three of five, oldest first, then a link to the rest
        $this->assertStringNotContainsString('ORD-P0', $pending);
        $this->assertStringNotContainsString('ORD-P4', $pending);
        $this->assertLessThan(strpos($pending, 'ORD-P1'), strpos($pending, 'ORD-P3'));
        $this->assertLessThan(strpos($pending, 'ORD-P2'), strpos($pending, 'ORD-P1'));
        $this->assertStringContainsString('+2 more', $pending);
        $this->assertStringContainsString('bg-amber-50', $pending, 'Orders older than a day stand out');
        $this->assertStringContainsString('ORD-C1', $board);
        $this->assertStringNotContainsString('ORD-S1', $board, 'Shipped orders are with the courier');
        $this->assertStringNotContainsString('Order Breakdown', $html);
    }
}
