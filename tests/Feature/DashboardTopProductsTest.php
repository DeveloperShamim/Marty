<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTopProductsTest extends TestCase
{
    use RefreshDatabase;

    private function sale(string $number, string $product, int $qty, int $daysAgo, string $status = 'delivered'): void
    {
        $order = Order::create([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000 * $qty, 'shipping_charge' => 60, 'total' => 1000 * $qty + 60,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => $status,
        ]);
        $order->forceFill(['created_at' => now()->subDays($daysAgo)])->save();
        OrderItem::create(['order_id' => $order->id, 'product_name' => $product, 'unit_price' => 1000, 'quantity' => $qty, 'line_total' => 1000 * $qty]);
    }

    public function test_top_products_show_units_sold_and_a_30_day_trend(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->sale('ORD-1', 'Rising Wallet', 3, 5);
        $this->sale('ORD-2', 'Rising Wallet', 2, 40);
        $this->sale('ORD-3', 'Falling Belt', 1, 5);
        $this->sale('ORD-4', 'Falling Belt', 4, 45);
        $this->sale('ORD-5', 'Fresh Watch', 2, 3);
        $this->sale('ORD-6', 'Cancelled Bag', 9, 3, 'cancelled');

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();
        $section = Str::between($html, 'data-top-products', '</section>');

        $this->assertStringContainsString('Top products', $section);
        $this->assertMatchesRegularExpression('/Rising Wallet.*?5 sold.*?\+50%/s', $section);
        $this->assertMatchesRegularExpression('/Falling Belt.*?5 sold.*?-75%/s', $section);
        $this->assertMatchesRegularExpression('/Fresh Watch.*?2 sold.*?New/s', $section);
        $this->assertStringNotContainsString('Cancelled Bag', $section);
    }
}
