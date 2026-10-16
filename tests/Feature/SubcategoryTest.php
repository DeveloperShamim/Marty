<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function cat(string $name, ?Category $parent = null): Category
    {
        return Category::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'is_active' => true,
            'position' => 1, 'parent_id' => $parent?->id]);
    }

    private function product(string $name, Category $cat): Product
    {
        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);
    }

    public function test_admin_can_put_a_category_under_a_main_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bags = $this->cat('Bags');

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Tote Bags', 'parent_id' => $bags->id, 'is_active' => 1])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSame($bags->id, Category::where('name', 'Tote Bags')->value('parent_id'));
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk()->assertSee('Sub-category of Bags');
        $this->actingAs($admin)->get(route('admin.categories.create'))->assertOk()->assertSee('Parent category');
    }

    public function test_only_one_level_is_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bags = $this->cat('Bags');
        $totes = $this->cat('Tote Bags', $bags);
        $wallets = $this->cat('Wallets');

        // A sub-category can't be a parent
        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Mini Totes', 'parent_id' => $totes->id])
            ->assertSessionHasErrors('parent_id');
        // A main category that has sub-categories can't move under another
        $this->actingAs($admin)->put(route('admin.categories.update', $bags), ['name' => 'Bags', 'parent_id' => $wallets->id])
            ->assertSessionHasErrors('parent_id');
        // Nor inside itself
        $this->actingAs($admin)->put(route('admin.categories.update', $wallets), ['name' => 'Wallets', 'parent_id' => $wallets->id])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_main_category_page_lists_its_sub_category_products(): void
    {
        $bags = $this->cat('Bags');
        $totes = $this->cat('Tote Bags', $bags);
        $this->product('Canvas Tote', $totes);
        $this->product('Leather Duffel', $bags);

        $this->get(route('shop.category', $bags))->assertOk()
            ->assertSee('Canvas Tote')->assertSee('Leather Duffel')->assertSee('data-subcategory-chips', false);
        $this->get(route('shop.category', $totes))->assertOk()
            ->assertSee('Canvas Tote')->assertDontSee('Leather Duffel');
    }

    public function test_menus_show_sub_categories_under_their_main_category(): void
    {
        $bags = $this->cat('Bags');
        $totes = $this->cat('Tote Bags', $bags);
        $this->product('Canvas Tote', $totes);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('data-nav-sub-menu', $html);
        $this->assertStringContainsString('data-mobile-subcats', $html);
        $this->assertStringContainsString(route('shop.category', $totes), $html);
        $this->assertStringContainsString('All Bags', $html);
    }
}
