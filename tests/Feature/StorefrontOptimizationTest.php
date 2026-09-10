<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_home_loads_and_populates_cache(): void
    {
        Cache::flush();

        $this->assertFalse(Cache::has('storefront_nav_categories'));

        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('storefront_nav_categories'));
        $this->assertTrue(Cache::has('storefront_nav_brands'));
        $this->assertTrue(Cache::has('storefront_has_flash_sale'));
    }

    public function test_category_save_invalidates_storefront_cache(): void
    {
        Cache::put('storefront_nav_categories', 'cached_nav', 60);

        Category::create([
            'name'      => 'Test Organic Category',
            'slug'      => 'test-organic-category',
            'is_active' => true,
            'position'  => 1,
        ]);

        $this->assertFalse(Cache::has('storefront_nav_categories'));
    }

    public function test_storefront_renders_vite_assets_and_whatsapp_widget(): void
    {
        Cache::flush();
        \App\Models\Setting::put('whatsapp_number', '01711111111');

        $response = $this->get('/');
        $response->assertStatus(200);

        // Does not load the heavy Tailwind Play CDN
        $response->assertDontSee('cdn.tailwindcss.com');

        // Does not render artificial preloader
        $response->assertDontSee('id="preloader"', false);

        // Renders WhatsApp floating widget
        $response->assertSee('aria-label="WhatsApp Support"', false);
    }
}
