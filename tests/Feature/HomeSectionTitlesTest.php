<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The Brands and Customer reviews section titles live in Homepage → Sections, not Store settings. */
class HomeSectionTitlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_manager_sets_section_titles_on_the_sections_page(): void
    {
        $store = User::factory()->create(['role' => 'store_manager']);

        $this->actingAs($store)->get(route('admin.home-sections.index'))
            ->assertOk()->assertSee('Section titles')->assertSee('name="home_reviews_title"', false);

        $this->actingAs($store)->from(route('admin.home-sections.index'))->put(route('admin.home-sections.titles'), [
            'home_featured_brands_title' => 'Our brands',
            'home_featured_brands_subtitle' => 'Picked with care',
            'home_reviews_title' => 'Happy buyers',
            'home_reviews_subtitle' => 'Real reviews',
        ])->assertRedirect(route('admin.home-sections.index'))->assertSessionHas('status');

        $this->assertSame('Our brands', setting('home_featured_brands_title'));
        $this->assertSame('Happy buyers', setting('home_reviews_title'));
    }

    public function test_store_settings_no_longer_has_the_titles_and_others_cannot_save_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()->assertDontSee('name="home_reviews_title"', false)->assertSee('Open sections');

        $orders = User::factory()->create(['role' => 'order_manager']);
        $this->actingAs($orders)->put(route('admin.home-sections.titles'), ['home_reviews_title' => 'x'])->assertRedirect();
        $this->assertNotSame('x', setting('home_reviews_title'));
    }
}
