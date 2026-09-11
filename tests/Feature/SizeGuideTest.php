<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_size_guide_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.size-guide.index'));

        $response->assertOk();
        $response->assertSee('Size Guide Management');
    }

    public function test_admin_can_update_size_guide_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('admin.size-guide.update'), [
            'size_guide_enabled'      => '1',
            'size_guide_default_unit' => 'in',
            'size_guide_custom_tip'   => 'Fits half size smaller.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('1', Setting::get('size_guide_enabled'));
        $this->assertEquals('in', Setting::get('size_guide_default_unit'));
        $this->assertEquals('Fits half size smaller.', Setting::get('size_guide_custom_tip'));
    }

    public function test_storefront_product_shows_size_guide_when_enabled(): void
    {
        Setting::put('size_guide_enabled', '1');

        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Air Running Shoes',
            'slug'           => 'air-running-shoes',
            'regular_price'  => 2000,
            'sale_price'     => 1500,
            'stock_quantity' => 10,
            'is_published'   => true,
        ]);

        ProductSku::create([
            'product_id'     => $product->id,
            'sku'            => 'SHOE-42',
            'attributes'     => ['Size' => '42'],
            'is_active'      => true,
            'stock_quantity' => 5,
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('data-open-size-guide', false);
        $response->assertSee('Size Guide');
    }

    public function test_storefront_product_hides_size_guide_when_disabled(): void
    {
        Setting::put('size_guide_enabled', '0');

        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Air Running Shoes',
            'slug'           => 'air-running-shoes',
            'regular_price'  => 2000,
            'sale_price'     => 1500,
            'stock_quantity' => 10,
            'is_published'   => true,
        ]);

        ProductSku::create([
            'product_id'     => $product->id,
            'sku'            => 'SHOE-42',
            'attributes'     => ['Size' => '42'],
            'is_active'      => true,
            'stock_quantity' => 5,
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee('data-open-size-guide', false);
    }
}
