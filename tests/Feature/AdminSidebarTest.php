<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    private function menuLabels(string $html): array
    {
        preg_match_all('/class="sb-item[^"]*"[^>]*data-label="([^"]+)"/', $html, $m);

        return array_map("html_entity_decode", $m[1]);
    }

    public function test_admin_sees_every_section_and_only_the_current_page_is_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('admin.products.index'))->assertOk()->getContent();

        $labels = $this->menuLabels($html);
        foreach (['Dashboard', 'Orders', 'Products', 'Coupons', 'Profit & Analytics', 'Staff & Roles', 'Store Settings'] as $label) {
            $this->assertContains($label, $labels);
        }
        $this->assertSame(1, substr_count($html, 'aria-current="page"'), 'Exactly one menu item should be marked current');
        $this->assertMatchesRegularExpression('/data-label="Products"[^>]*aria-current="page"/', $html);
    }

    public function test_order_manager_only_sees_order_pages(): void
    {
        $manager = User::factory()->create(['role' => 'order_manager']);
        $labels = $this->menuLabels($this->actingAs($manager)->get(route('admin.orders.index'))->assertOk()->getContent());

        $this->assertEqualsCanonicalizing(
            ['Orders', 'POS Register', 'Courier Scan', 'Abandoned Carts', 'Reviews', 'Customers', 'Blacklist', 'View store', 'Expand sidebar'],
            $labels
        );
    }

    public function test_groups_and_courier_scan_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\Order::create([
            'order_number' => 'ORD-RET', 'customer_name' => 'K', 'customer_phone' => '01711000000', 'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1, 'total' => 1,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'shipped',
            'courier_name' => 'steadfast', 'courier_tracking_code' => 'SF1', 'courier_status' => 'returning',
        ]);

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-group="Storefront".*Hero Banners.*Trust Strip.*Size Guide.*Media Library/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Marketing".*Coupons.*Flash Sale.*Abandoned Carts/s', $html);
        $this->assertStringContainsString('Product Barcodes', $html);
        $this->assertStringNotContainsString('data-label="My Account"', $html, 'Account is in the sidebar footer');
        $this->assertMatchesRegularExpression('/data-label="Courier Scan".*?title="1 courier updates need you"/s', $html);
    }
}
