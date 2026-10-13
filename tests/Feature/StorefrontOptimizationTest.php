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

        // Styles come from the compiled Tailwind build with the theme colours as CSS variables, not the play CDN
        $response->assertSee('theme/css/tw-store.css', false);
        $response->assertSee('--tw-brand-600:', false);
        $response->assertDontSee('cdn.tailwindcss.com', false);
        $this->assertFileExists(public_path('theme/css/tw-store.css'));

        // Does not render artificial preloader delay
        $response->assertDontSee('id="preloader"', false);

        // Renders WhatsApp floating widget
        $response->assertSee('aria-label="Chat on WhatsApp"', false);
    }

    public function test_admin_uses_the_compiled_tailwind_build(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('theme/css/tw-admin.css', false);
        $response->assertSee('--tw-brand-600:', false);
        $response->assertDontSee('cdn.tailwindcss.com', false);
        $this->assertFileExists(public_path('theme/css/tw-admin.css'));

        $this->get(route('admin.login'))->assertDontSee('cdn.tailwindcss.com', false);
    }
}
