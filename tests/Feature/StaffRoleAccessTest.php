<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\StaffAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** A GET page per area, to check the server rule for every role. */
    private const PAGES = [
        'dashboard' => '/admin', 'analytics' => '/admin/analytics', 'expenses' => '/admin/expenses',
        'orders' => '/admin/orders', 'courier-scan' => '/admin/courier-scan', 'pos' => '/admin/pos',
        'abandoned-carts' => '/admin/abandoned-carts', 'customers' => '/admin/customers', 'blacklist' => '/admin/blacklist',
        'reviews' => '/admin/reviews', 'products' => '/admin/products', 'inventory' => '/admin/inventory',
        'categories' => '/admin/categories', 'brands' => '/admin/brands', 'variations' => '/admin/variations',
        'barcodes' => '/admin/barcodes', 'media' => '/admin/media', 'size-guide' => '/admin/size-guide',
        'features' => '/admin/features', 'banners' => '/admin/banners', 'coupons' => '/admin/coupons',
        'flash-sale' => '/admin/flash-sale', 'staff' => '/admin/staff', 'activity-logs' => '/admin/activity-logs',
        'settings' => '/admin/settings', 'integrations' => '/admin/integrations',
    ];

    private const EXPECTED = [
        'store_manager' => ['dashboard', 'analytics', 'expenses', 'orders', 'courier-scan', 'pos', 'abandoned-carts', 'customers',
            'blacklist', 'reviews', 'products', 'inventory', 'categories', 'brands', 'variations', 'barcodes', 'media',
            'size-guide', 'features', 'banners', 'coupons', 'flash-sale'],
        'order_manager' => ['orders', 'courier-scan', 'pos', 'abandoned-carts', 'customers', 'blacklist', 'reviews'],
        'inventory_manager' => ['dashboard', 'reviews', 'products', 'inventory', 'categories', 'brands', 'variations',
            'barcodes', 'media', 'size-guide', 'features'],
    ];

    public function test_every_role_can_open_exactly_its_pages(): void
    {
        foreach (self::EXPECTED as $role => $allowed) {
            $user = User::factory()->create(['role' => $role]);
            foreach (self::PAGES as $area => $url) {
                $response = $this->actingAs($user)->get($url);
                if (in_array($area, $allowed, true)) {
                    $this->assertSame(200, $response->status(), "{$role} should open {$area} ({$url})");
                } else {
                    $this->assertTrue($response->isRedirect(), "{$role} must NOT open {$area} ({$url})");
                    $this->assertNotSame(url($url), $response->headers->get('Location'));
                }
            }
        }
    }

    public function test_admin_opens_everything(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (self::PAGES as $area => $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_sidebar_only_links_to_pages_the_role_can_open(): void
    {
        foreach (array_keys(self::EXPECTED) as $role) {
            $user = User::factory()->create(['role' => $role]);
            $home = $this->actingAs($user)->get(StaffAccess::home($user))->assertOk()->getContent();
            preg_match_all('/class="sb-item[^"]*"[^>]*data-label="[^"]+"|<a href="([^"]+)"\s+class="sb-item/', $home, $m);
            preg_match_all('/<a href="(https?:\/\/[^"]+\/admin[^"]*)"\s*\n?\s*class="sb-item/', $home, $links);
            $this->assertNotEmpty($links[1], "{$role}: sidebar links found");
            foreach ($links[1] as $link) {
                $this->actingAs($user)->get($link)->assertOk();
            }
        }
    }

    public function test_inventory_manager_dashboard_hides_money_and_orders(): void
    {
        $stock = User::factory()->create(['role' => 'inventory_manager']);
        $html = $this->actingAs($stock)->get('/admin')->assertOk()->getContent();

        $this->assertStringNotContainsString('Net Profit', $html);
        $this->assertStringNotContainsString("Today's Sales", $html);
        $this->assertStringNotContainsString('Recent Orders', $html);
        $this->assertStringContainsString('Stock health', $html);
        $this->assertStringNotContainsString('orderAlertsConfig', $html, 'No new-order popup');
        $this->assertStringNotContainsString(route('admin.cache.clear'), $html);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin')->assertSee('Net Profit');
    }

    public function test_order_manager_lands_on_orders_and_ajax_refusals_are_json(): void
    {
        $orders = User::factory()->create(['role' => 'order_manager', 'password' => bcrypt('secret-pass')]);

        $this->post(route('admin.login.attempt'), ['email' => $orders->email, 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.orders.index'));
        $this->actingAs($orders)->get('/admin')->assertRedirect(route('admin.orders.index'));
        $this->actingAs($orders)->postJson(route('admin.expenses.store'), [])->assertStatus(403);
    }

    public function test_store_owner_is_protected_and_staff_passwords_need_8_characters(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $second = User::factory()->create(['role' => 'admin']);

        $this->actingAs($second)->patch(route('admin.staff.toggle', $owner))->assertSessionHasErrors('staff');
        $this->actingAs($second)->delete(route('admin.staff.destroy', $owner))->assertSessionHasErrors('staff');
        $this->assertFalse($owner->fresh()->is_suspended);

        // The owner can manage the other admin.
        $this->actingAs($owner)->patch(route('admin.staff.toggle', $second))->assertSessionHasNoErrors();
        $this->assertTrue($second->fresh()->is_suspended);

        $this->actingAs($owner)->post(route('admin.staff.store'), [
            'name' => 'New', 'email' => 'new@shop.test', 'role' => 'order_manager', 'password' => 'short1',
        ])->assertSessionHasErrors('password');
    }
}
