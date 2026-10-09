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
        // One current item in the sidebar and one in the phone icon rail
        $this->assertSame(2, substr_count($html, 'aria-current="page"'), 'Exactly one item per menu should be marked current');
        $this->assertMatchesRegularExpression('/data-rail-current[^>]*aria-label="Products"/', $html);
        $this->assertMatchesRegularExpression('/data-label="Products"[^>]*aria-current="page"/', $html);
    }

    public function test_order_manager_only_sees_order_pages(): void
    {
        $manager = User::factory()->create(['role' => 'order_manager']);
        $labels = $this->menuLabels($this->actingAs($manager)->get(route('admin.orders.index'))->assertOk()->getContent());

        $this->assertEqualsCanonicalizing(
            ['Orders', 'POS Register', 'Courier Scan', 'Abandoned Carts', 'Reviews', 'Customers', 'View store', 'Log out'],
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

        $this->assertMatchesRegularExpression('/data-group="Store content".*Homepage.*Size Guide.*Media Library/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Marketing".*Coupons.*Promotions.*Abandoned Carts/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Catalog".*Products.*Inventory.*Catalog setup.*Reviews/s', $html);
        $this->assertStringNotContainsString('data-label="My Account"', $html, 'Account is in the sidebar footer');
        $this->assertMatchesRegularExpression('/data-label="Courier Scan".*?title="1 courier updates need you"/s', $html);
    }

    public function test_merged_pages_share_one_menu_entry_and_show_tabs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('admin.brands.index'))->assertOk()->getContent();

        $this->assertCount(20, array_diff($this->menuLabels($html), ['View store', 'Log out']));
        $this->assertMatchesRegularExpression('/data-label="Catalog setup"[^>]*aria-current="page"/', $html);
        $this->assertMatchesRegularExpression('/data-page-tabs.*Categories.*aria-current="true"[^>]*>Brands<.*Variations/s', $html);
        // Searching the menu for a tab's name still finds its entry.
        $this->assertMatchesRegularExpression('/data-label="Catalog setup" data-search="[^"]*brands/', $html);

        // Phone icon strip: the everyday pages, the current page and a "More" button.
        preg_match('/<nav id="mobileRail".*?<\/nav>/s', $html, $rail);
        preg_match_all('/aria-label="([^"]+)" title=/', $rail[0], $icons);
        $this->assertSame(['Dashboard', 'Orders', 'Courier Scan', 'POS Register', 'Products', 'Inventory', 'Catalog setup', 'More pages'], $icons[1]);

        $this->assertStringNotContainsString('data-page-tabs', $this->actingAs($admin)->get(route('admin.coupons.index'))->getContent());
    }

    public function test_tabs_only_list_pages_the_role_may_open(): void
    {
        $stock = User::factory()->create(['role' => 'inventory_manager']);
        $html = $this->actingAs($stock)->get(route('admin.features.index'))->assertOk()->getContent();

        // Only Trust strip is theirs, so there is nothing to switch to.
        $this->assertStringNotContainsString('data-page-tabs', $html);
        $this->assertStringNotContainsString('Hero banners', $html);
        $this->assertMatchesRegularExpression('/href="[^"]+\/admin\/features"\s+class="sb-item[^"]*"[^>]*data-label="Homepage"/', $html);
    }
}
