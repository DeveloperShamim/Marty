<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTextSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_trust_points_default_to_neutral_text_and_can_be_changed(): void
    {
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Runner', 'slug' => 'runner',
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);

        $this->get(route('product.show', $product))->assertOk()->assertSee('100% Authentic')->assertDontSee('Genuine leather');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'homepage'), ['trust_point_1' => 'Genuine leather'])->assertOk();

        $this->get(route('product.show', $product))->assertSee('Genuine leather');
    }

    public function test_banner_text_shows_on_the_picture_only_when_switched_on(): void
    {
        Banner::create(['title' => 'Step Into Comfort', 'button_text' => 'Shop Now', 'image' => 'banners/hero.jpg', 'placement' => 'hero', 'is_active' => true]);

        $this->get(route('home'))->assertOk()->assertDontSee('Step Into Comfort</h2>', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'homepage'), ['hero_text_on_image' => '1'])->assertOk();
        $this->assertSame('1', setting('hero_text_on_image'));

        $this->get(route('home'))->assertSee('Step Into Comfort</h2>', false)->assertSee('Shop Now');
    }
}
