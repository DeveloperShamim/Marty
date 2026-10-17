<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandLogoTest extends TestCase
{
    use RefreshDatabase;

    private function runBrandMigration(): void
    {
        (require database_path('migrations/2026_10_16_120000_set_vant_logo_and_favicon.php'))->up();
        Setting::forgetCache();
    }

    public function test_blank_logo_and_favicon_get_the_vant_brand_files(): void
    {
        Setting::put('logo', '');
        Setting::forget('favicon');

        $this->runBrandMigration();

        $this->assertSame('uploads/brand/vant-logo.png', Setting::get('logo'));
        $this->assertSame('uploads/brand/vant-mark.png', Setting::get('favicon'));
        $this->assertTrue(has_custom_logo());
        $this->assertStringEndsWith('/uploads/brand/vant-logo.png', logo_url());
        $this->assertStringEndsWith('/uploads/brand/vant-mark.png', favicon_url());
        $this->assertFileExists(public_path('uploads/brand/vant-logo.png'));
        $this->assertFileExists(public_path('uploads/brand/vant-mark.png'));
    }

    public function test_a_logo_uploaded_in_admin_is_kept(): void
    {
        Setting::put('logo', 'branding/my-own-logo.png');
        Setting::put('favicon', 'branding/my-own-icon.png');

        $this->runBrandMigration();

        $this->assertSame('branding/my-own-logo.png', Setting::get('logo'));
        $this->assertSame('branding/my-own-icon.png', Setting::get('favicon'));
    }

    public function test_storefront_shows_the_logo_image_and_favicon(): void
    {
        $this->runBrandMigration();

        $this->get('/')
            ->assertOk()
            ->assertSee('uploads/brand/vant-logo.png', false)
            ->assertSee('uploads/brand/vant-mark.png', false);
    }
}
