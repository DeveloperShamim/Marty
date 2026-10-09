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

    public function test_pending_confirmed_and_processing_tabs_come_first_with_their_own_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->order('ORD-P1', ['status' => 'pending']);
        $this->order('ORD-P2', ['status' => 'pending']);
        $this->order('ORD-C1');
        $this->order('ORD-R1', ['status' => 'processing']);
        $this->order('ORD-BK', ['status' => 'processing', 'payment_method' => 'bkash']);

        $res = $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk();
        $counts = $res->viewData('counts');
        $this->assertSame([2, 1, 2], [$counts['pending'], $counts['confirmed'], $counts['processing']]);

        // Same names and order as the dashboard's Orders to ship board, straight after All
        preg_match('/aria-label="Order status">(.*?)<\/nav>/s', $res->getContent(), $nav);
        preg_match_all('/>\s*([A-Z][a-z]+(?: [a-z]+)?)\s*</', $nav[1], $labels);
        $this->assertSame(['All', 'Pending', 'Confirmed', 'Processing', 'Needs review', 'Not printed', 'Shipped'], array_slice($labels[1], 0, 7));

        // The dashboard's Pending link now lands on a lit Pending tab showing only pending orders
        $html = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'pending']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*Pending/', $html);
        $this->assertStringContainsString('ORD-P1', $html);
        $this->assertStringNotContainsString('ORD-C1', $html);
    }
}
