<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function cat(string $name, ?Category $parent = null): Category
    {
        $cat = Category::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'is_active' => true, 'position' => 1, 'parent_id' => $parent?->id]);
        Product::create(['category_id' => $cat->id, 'name' => $name . ' One', 'slug' => \Illuminate\Support\Str::slug($name) . '-one',
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);

        return $cat;
    }

    private function save(User $admin, array $menu)
    {
        return $this->actingAs($admin)->put(route('admin.header-menu.update'), ['menu' => json_encode($menu)]);
    }

    public function test_default_menu_is_home_all_products_categories_and_brands(): void
    {
        $this->cat('Wallets');
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-nav-cats', $html);
        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('shop'), '#') . '"[^>]*>\s*All Products#', $html);
    }

    public function test_admin_builds_a_menu_with_dropdowns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $shoes = $this->cat('Shoes');
        $this->cat('Sneakers', $shoes);
        $belts = $this->cat('Belts');
        $brand = Brand::create(['name' => 'Vant', 'slug' => 'vant', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.header-menu.index'))->assertOk()->assertSee('Menu items');

        $this->save($admin, [
            ['type' => 'home'],
            ['type' => 'category', 'ref' => $shoes->id, 'label' => 'Men Shoes'],
            ['type' => 'link', 'label' => 'Offers', 'url' => '/shop?on_sale=1', 'children' => [
                ['type' => 'category', 'ref' => $belts->id],
                ['type' => 'brand', 'ref' => $brand->id, 'label' => 'Vant picks'],
                ['type' => 'categories'],                                   // not allowed in a dropdown
            ]],
            ['type' => 'link', 'label' => 'Bad', 'url' => 'javascript:alert(1)'], // unsafe link dropped
            ['type' => 'category', 'ref' => 999],                           // missing category dropped
        ])->assertSessionHasNoErrors();

        $saved = json_decode(Setting::get('header_menu'), true);
        $this->assertSame(['home', 'category', 'link'], array_column($saved, 'type'));
        $this->assertCount(2, $saved[2]['children']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-nav-cats', $html, 'The automatic categories row is gone');
        $this->assertStringContainsString('Men Shoes', $html);
        $this->assertStringContainsString('All Shoes', $html, 'A category item opens its sub-categories');
        $this->assertStringContainsString(route('shop.category', 'sneakers'), $html);
        $this->assertStringContainsString('href="/shop?on_sale=1"', $html);
        $this->assertStringContainsString('Vant picks', $html);
        $this->assertStringContainsString('data-mobile-menu-links', $html, 'The phone menu follows the same list');
        $this->assertStringNotContainsString('javascript:alert', $html);
    }

    public function test_reset_goes_back_to_the_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->save($admin, [['type' => 'home']]);
        $this->actingAs($admin)->put(route('admin.header-menu.update'), ['reset' => 1]);

        $this->assertSame('', (string) Setting::get('header_menu'));
        $this->assertSame(\App\Support\HeaderMenu::defaults(), \App\Support\HeaderMenu::saved());
    }

    public function test_only_store_managers_and_admins_can_edit_it(): void
    {
        $staff = User::factory()->create(['role' => 'order_manager']);
        $this->actingAs($staff)->get(route('admin.header-menu.index'))->assertRedirect();
        $this->save($staff, [['type' => 'home']]);
        $this->assertNull(Setting::get('header_menu'));
    }
}
