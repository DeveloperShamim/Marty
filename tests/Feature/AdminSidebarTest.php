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
        foreach (['Dashboard', 'Reports', 'Orders', 'Products', 'Offers', 'Staff & roles', 'Store settings'] as $label) {
            $this->assertContains($label, $labels);
        }
        // One current item in the sidebar and one in the phone bottom bar
        $this->assertSame(2, substr_count($html, 'aria-current="page"'), 'Exactly one item per menu should be marked current');
        $this->assertMatchesRegularExpression('/href="[^"]+\/admin\/products"[^>]*\s+aria-current="page" data-bar-current/', $html);
        $this->assertMatchesRegularExpression('/data-label="Products"[^>]*aria-current="page"/', $html);
    }

    public function test_order_manager_only_sees_order_pages(): void
    {
        $manager = User::factory()->create(['role' => 'order_manager']);
        $labels = $this->menuLabels($this->actingAs($manager)->get(route('admin.orders.index'))->assertOk()->getContent());

        $this->assertEqualsCanonicalizing(
            ['Orders', 'Point of sale', 'Courier scan', 'Abandoned carts', 'Reviews', 'Customers', 'View store', 'Log out'],
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

        $this->assertMatchesRegularExpression('/data-group="Sales".*Orders.*Courier scan.*Abandoned carts.*Point of sale.*data-group="Catalog"/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Catalog".*Products.*Inventory.*Catalog setup.*Media library.*data-group="Customers"/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Customers".*Customers.*Reviews.*data-group="Marketing"/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Marketing".*Offers.*Landing pages.*data-group="Online store"/s', $html);
        $this->assertMatchesRegularExpression('/data-group="Online store".*Homepage.*Pages &amp; menu/s', $html);
        // Admin-only pages sit together in the bottom card, and the top group has no heading.
        $this->assertMatchesRegularExpression('/data-group="Settings">\s*<p[^>]*>Settings<\/p>.*Store settings.*Integrations.*Staff &amp; roles.*View store/s', $html);
        $this->assertStringNotContainsString('class="sb-label">Home<', $html);
        $this->assertStringNotContainsString('>Support<', $html);
        $this->assertStringNotContainsString('data-label="My Account"', $html, 'Account is in the sidebar footer');
        $this->assertMatchesRegularExpression('/data-label="Courier scan".*?title="1 courier updates need you"/s', $html);
    }

    public function test_merged_pages_share_one_menu_entry_and_show_tabs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $html = $this->actingAs($admin)->get(route('admin.brands.index'))->assertOk()->getContent();

        $this->assertCount(19, array_diff($this->menuLabels($html), ['View store', 'Log out']));
        $this->assertMatchesRegularExpression('/data-label="Catalog setup"[^>]*aria-current="page"/', $html);
        $this->assertMatchesRegularExpression('/data-page-tabs.*Categories.*aria-current="true"[^>]*>Brands<.*Variations/s', $html);
        // Searching the menu for a tab's name still finds its entry.
        $this->assertMatchesRegularExpression('/data-label="Catalog setup" data-search="[^"]*brands/', $html);

        // Phone bottom bar: four everyday pages and "More", which is highlighted for pages not on the bar.
        $this->assertSame(['Home', 'Orders', 'Scan', 'Products', 'More'], $this->bottomBar($html));
        $this->assertMatchesRegularExpression('/data-bar-more[^>]*>\s*<span[^>]*style="background: var\(--brand-dark\);"/', $html);

        $this->assertStringNotContainsString('data-page-tabs', $this->actingAs($admin)->get(route('admin.landing-pages.index'))->getContent());
    }

    public function test_tabs_only_list_pages_the_role_may_open(): void
    {
        // Inventory managers may open Size guide but not Pages; Catalog setup shows it as a fourth tab.
        $stock = User::factory()->create(['role' => 'inventory_manager']);
        $html = $this->actingAs($stock)->get(route('admin.size-guide.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-label="Catalog setup"[^>]*aria-current="page"/', $html);
        $this->assertMatchesRegularExpression('/data-page-tabs.*Categories.*Brands.*Variations.*aria-current="true"[^>]*>Size guide</s', $html);

        // Trust strip is a homepage block, so it is no longer theirs, and no "Homepage" entry shows.
        $this->actingAs($stock)->get(route('admin.features.index'))->assertRedirect();
        $this->assertSame(['Dashboard', 'Products', 'Inventory', 'Catalog setup', 'Media library', 'Reviews', 'View store', 'Log out'], $this->menuLabels($html));

        // A store manager sees only the tabs of a merged entry they may open.
        $store = User::factory()->create(['role' => 'store_manager']);
        $html = $this->actingAs($store)->get(route('admin.pages.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-page-tabs.*aria-current="true"[^>]*>Pages<.*Menu bar/s', $html);
        $this->assertStringNotContainsString('data-label="Staff &amp; roles"', $html);
    }

    private function bottomBar(string $html): array
    {
        preg_match('/<nav id="bottomNav".*?<\/nav>/s', $html, $bar);
        preg_match_all('/<span(?: class="[^"]*")?>([^<]+)<\/span>\s*<\/(?:a|button)>/', $bar[0] ?? '', $labels);

        return array_map('trim', $labels[1]);
    }

    public function test_bottom_bar_follows_the_role_and_steps_aside_on_pos_and_the_product_form(): void
    {
        $orders = User::factory()->create(['role' => 'order_manager']);
        $this->assertSame(['Orders', 'Scan', 'POS', 'Customers', 'More'],
            $this->bottomBar($this->actingAs($orders)->get(route('admin.orders.index'))->getContent()));

        $stock = User::factory()->create(['role' => 'inventory_manager']);
        $this->assertSame(['Home', 'Products', 'Inventory', 'Barcodes', 'More'],
            $this->bottomBar($this->actingAs($stock)->get(route('admin.inventory.index'))->getContent()));

        $store = User::factory()->create(['role' => 'store_manager']);
        $this->assertSame(['Home', 'Orders', 'Products', 'POS', 'More'],
            $this->bottomBar($this->actingAs($store)->get(route('admin.orders.index'))->getContent()));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertStringNotContainsString('id="bottomNav"', $this->actingAs($admin)->get(route('admin.pos.index'))->getContent());
        $this->assertStringNotContainsString('id="bottomNav"', $this->actingAs($admin)->get(route('admin.products.create'))->getContent());
        $this->assertStringNotContainsString('id="mobileRail"', $this->actingAs($admin)->get(route('admin.dashboard'))->getContent());
    }

    public function test_logo_and_admin_home_lead_each_role_to_a_page_it_can_open(): void
    {
        $orders = User::factory()->create(['role' => 'order_manager']);
        $html = $this->actingAs($orders)->get(route('admin.orders.index'))->getContent();
        $this->assertMatchesRegularExpression('/<a href="[^"]+\/admin\/orders" class="flex items-center gap-2.5[^"]*" aria-label="[^"]+ admin home"/', $html);

        // Opening /admin goes straight to their home page, without an "access denied" message.
        $this->actingAs($orders)->get(route('admin.dashboard'))->assertRedirect(route('admin.orders.index'))->assertSessionMissing('error');
        $this->actingAs($orders)->get(route('admin.analytics.index'))->assertSessionHas('error');

        $pos = $this->actingAs($orders)->get(route('admin.pos.index'))->getContent();
        $this->assertStringNotContainsString(route('admin.barcodes.index'), $pos);
        $this->assertStringNotContainsString('href="'.route('admin.dashboard').'"', $pos);
    }
}
