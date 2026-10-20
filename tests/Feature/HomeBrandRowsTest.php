<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeBrandRowsTest extends TestCase
{
    use RefreshDatabase;

    private function brandWithProducts(string $name, int $count, bool $onHome): Brand
    {
        $brand = Brand::create(['name' => $name, 'slug' => strtolower($name), 'is_active' => true, 'show_on_home' => $onHome]);
        $category = Category::firstOrCreate(['slug' => 'shoes'], ['name' => 'Shoes', 'is_active' => true, 'position' => 1]);
        for ($i = 1; $i <= $count; $i++) {
            Product::create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => "$name Runner $i", 'slug' => strtolower($name) . "-runner-$i",
                'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);
        }

        return $brand;
    }

    public function test_only_brands_switched_on_get_a_homepage_row(): void
    {
        $adidas = $this->brandWithProducts('Adidas', 5, true);
        $this->brandWithProducts('Puma', 5, false);
        Brand::create(['name' => 'Empty', 'slug' => 'empty', 'is_active' => true, 'show_on_home' => true]);

        $response = $this->get(route('home'))->assertOk();
        $rows = $response->viewData('homeBrandRows');

        $this->assertSame(['Adidas'], $rows->pluck('name')->all()); // switched off and empty brands are left out
        $response->assertSee('See all<br>Adidas', false)->assertSee(route('shop.brand', $adidas), false)->assertDontSee('See all<br>Puma', false);
    }

    public function test_admin_switches_it_on_in_the_brand_form(): void
    {
        $brand = $this->brandWithProducts('Adidas', 1, false);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.brands.edit', $brand))->assertOk()->assertSee("Show this brand's products on the homepage", false);
        $this->actingAs($admin)->put(route('admin.brands.update', $brand), ['name' => 'Adidas', 'is_active' => '1', 'show_on_home' => '1'])->assertSessionHasNoErrors();

        $this->assertTrue($brand->fresh()->show_on_home);
    }
}
