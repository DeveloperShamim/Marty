<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoBannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function promo(array $attrs = []): Banner
    {
        return Banner::create($attrs + [
            'title' => 'Zip Wallet', 'placement' => 'promo_row', 'image' => 'https://example.com/zip.jpg',
            'link_url' => '/shop', 'is_active' => true, 'position' => 0,
        ]);
    }

    public function test_promo_banners_show_in_their_own_row(): void
    {
        $this->promo();
        $this->promo(['title' => 'Long Wallet', 'show_on' => 'desktop']);
        $this->promo(['title' => 'Phone Deal', 'show_on' => 'mobile']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-promo-banners', $html);
        $this->assertMatchesRegularExpression('/hidden sm:block" data-promo-banner="desktop"/', $html);
        $this->assertMatchesRegularExpression('/sm:hidden" data-promo-banner="mobile"/', $html);
        $this->assertStringContainsString('data-promo-banner="both"', $html);
    }

    public function test_dates_decide_when_a_banner_shows(): void
    {
        $this->promo(['title' => 'Eid Offer', 'starts_at' => now()->addDay()]);
        $this->promo(['title' => 'Old Offer', 'ends_at' => now()->subMinute()]);
        $this->promo(['title' => 'Running Offer', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $this->promo(['title' => 'Switched Off', 'is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Running Offer', $html);
        $this->assertStringNotContainsString('Eid Offer', $html);
        $this->assertStringNotContainsString('Old Offer', $html);
        $this->assertStringNotContainsString('Switched Off', $html);
    }

    public function test_no_row_without_live_banners(): void
    {
        $this->promo(['ends_at' => now()->subDay()]);

        $this->get('/')->assertOk()->assertDontSee('data-promo-banners', false);
    }

    public function test_admin_saves_device_and_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Key Features', 'placement' => 'promo_row', 'image_url' => 'https://example.com/k.jpg',
            'show_on' => 'mobile', 'starts_at' => '2026-11-01T10:00', 'ends_at' => '2026-11-30T23:00', 'is_active' => '1',
        ])->assertRedirect(route('admin.banners.index'));

        $banner = Banner::firstWhere('title', 'Key Features');
        $this->assertSame('mobile', $banner->show_on);
        $this->assertSame('2026-11-01 10:00', $banner->starts_at->format('Y-m-d H:i'));
        $this->assertSame('scheduled', $banner->status());

        $this->actingAs($admin)->get(route('admin.banners.index'))->assertOk()
            ->assertSee('Promo banners')->assertSee('Phone only')->assertSee('data-banner-status="scheduled"', false);
        $this->actingAs($admin)->get(route('admin.banners.edit', $banner))->assertOk()
            ->assertSee('value="2026-11-01T10:00"', false);
    }

    public function test_end_must_follow_start_and_other_placements_show_everywhere(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Bad Dates', 'placement' => 'promo_row', 'starts_at' => '2026-11-10T10:00', 'ends_at' => '2026-11-01T10:00',
        ])->assertSessionHasErrors('ends_at');

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Slide', 'placement' => 'hero', 'show_on' => 'mobile', 'is_active' => '1',
        ]);
        $this->assertSame('both', Banner::firstWhere('title', 'Slide')->show_on);
    }
}
