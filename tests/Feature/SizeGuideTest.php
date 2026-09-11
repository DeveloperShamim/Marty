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

    public function test_admin_can_customize_size_charts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $customShoes = [
            ['eu' => '40', 'us_m' => '7.5', 'us_w' => '9.0', 'uk' => '6.5', 'cm' => '25.0'],
            ['eu' => '46', 'us_m' => '12.0', 'us_w' => '13.5', 'uk' => '11.0', 'cm' => '30.0'],
        ];

        $response = $this->actingAs($admin)->put(route('admin.size-guide.update'), [
            'size_guide_enabled' => '1',
            'shoes_data'         => json_encode($customShoes),
        ]);

        $response->assertRedirect();
        $savedShoes = json_decode(Setting::get('size_guide_shoes_data'), true);
        $this->assertCount(2, $savedShoes);
        $this->assertEquals('46', $savedShoes[1]['eu']);
    }

    public function test_admin_can_reset_size_charts_to_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Setting::put('size_guide_shoes_data', json_encode([['eu' => '99', 'us_m' => '99', 'us_w' => '99', 'uk' => '99', 'cm' => '99']]));

        $response = $this->actingAs($admin)->put(route('admin.size-guide.update'), [
            'reset_defaults' => '1',
        ]);

        $response->assertRedirect();
        $this->assertNull(Setting::get('size_guide_shoes_data'));
    }

    public function test_storefront_modal_renders_custom_sizes(): void
    {
        Setting::put('size_guide_enabled', '1');
        $customShoes = [
            ['eu' => '99-CUSTOM', 'us_m' => '15.0', 'us_w' => '16.5', 'uk' => '14.0', 'cm' => '33.0'],
        ];
        Setting::put('size_guide_shoes_data', json_encode($customShoes));

        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::create([
            'category_id'    => $category->id,
            'name'           => 'Air Custom Shoes',
            'slug'           => 'air-custom-shoes',
            'regular_price'  => 2000,
            'sale_price'     => 1500,
            'stock_quantity' => 10,
            'is_published'   => true,
        ]);

        ProductSku::create([
            'product_id'     => $product->id,
            'sku'            => 'CUSTOM-99',
            'attributes'     => ['Size' => '99-CUSTOM'],
            'is_active'      => true,
            'stock_quantity' => 5,
        ]);

        $response = $this->get(route('product.show', $product->slug));
        $response->assertOk();
        $response->assertSee('99-CUSTOM');
    }
}


