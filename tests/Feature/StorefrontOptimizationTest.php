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

    public function test_storefront_home_renders_successfully_with_nav_data(): void
    {
        Category::create([
            'name'      => 'Organic Veggies',
            'slug'      => 'organic-veggies',
            'is_active' => true,
            'position'  => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Organic Veggies');
    }

    public function test_storefront_renders_styles_and_whatsapp_widget(): void
    {
        Cache::flush();
        \App\Models\Setting::put('whatsapp_number', '01711111111');

        $response = $this->get('/');
        $response->assertStatus(200);

        // Ensures Tailwind CSS engine is loaded for full styling
        $response->assertSee('cdn.tailwindcss.com');

        // Does not render artificial preloader delay
        $response->assertDontSee('id="preloader"', false);

        // Renders WhatsApp floating widget
        $response->assertSee('aria-label="WhatsApp Support"', false);
    }
}
