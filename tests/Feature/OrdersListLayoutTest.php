<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersListLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $number, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
        ], $attrs));
    }

    public function test_list_shows_one_plain_payment_line_and_no_repeated_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order('ORD-COD');
        $this->order('ORD-BKASH', ['payment_method' => 'bkash']);
        $this->order('ORD-PAID', ['payment_method' => 'nagad', 'payment_status' => 'verified']);
        $this->order('ORD-POS', ['order_type' => 'pos', 'status' => 'delivered', 'payment_status' => 'verified', 'customer_phone' => 'N/A']);

        $html = $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->getContent();

        // The status tabs carry the counts; the pipeline card and the Review orders button repeated them
        $this->assertStringNotContainsString('Order pipeline', $html);
        $this->assertStringNotContainsString('Review orders', $html);
        $this->assertStringContainsString('Needs review', $html);

        $this->assertStringContainsString('Collect on delivery', $html, 'Unpaid COD is normal, not a warning');
        $this->assertStringContainsString('Payment to verify', $html);
        $this->assertStringContainsString('>Paid<', $html);
        $this->assertStringContainsString('In-store sale', $html, 'POS sales never go to a courier');
        $this->assertStringNotContainsString('tel:N/A', $html);
        $this->assertStringContainsString('Print label', $html);
        $this->assertStringContainsString('Print invoice', $html);
    }
}
