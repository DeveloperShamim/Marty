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
}
