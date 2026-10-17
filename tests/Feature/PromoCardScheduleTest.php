<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCardScheduleTest extends TestCase
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
            'title' => 'Zip Wallet', 'placement' => 'hero_side', 'image' => 'https://example.com/zip.jpg',
            'link_url' => '/shop', 'is_active' => true, 'position' => 0,
        ]);
    }

    public function test_promo_cards_keep_to_the_screens_admin_picked(): void
    {
        $this->promo();
        $this->promo(['title' => 'Long Wallet', 'show_on' => 'desktop']);
        $this->promo(['title' => 'Phone Deal', 'show_on' => 'mobile']);

        $html = $this->get('/')->assertOk()->getContent();

        // Desktop gets the first two that show there, phones the first two that show there
        $this->assertMatchesRegularExpression('/data-promo-card="both" class="\s*col-span-1 lg:col-span-3/', $html);
        $this->assertMatchesRegularExpression('/data-promo-card="desktop" class="hidden lg:flex col-span-1/', $html);
        $this->assertMatchesRegularExpression('/data-promo-card="mobile" class="lg:hidden col-span-1/', $html);
        $this->assertStringContainsString('lg:col-span-7 lg:row-span-2', $html);
        $this->assertStringNotContainsString('lg:aspect-[75/28]', $html);
    }

    public function test_slider_takes_full_width_on_desktop_when_cards_are_phone_only(): void
    {
        $this->promo(['show_on' => 'mobile']);

        $html = $this->get('/')->assertOk()->getContent();

        // Full width on desktop, but no taller than beside the cards
        $this->assertStringContainsString('col-span-2 lg:col-span-10 lg:aspect-[75/28]', $html);
        $this->assertMatchesRegularExpression('/data-promo-card="mobile" class="lg:hidden col-span-2 lg:col-span-3/', $html);
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

    public function test_ended_cards_leave_the_page(): void
    {
        $this->promo(['ends_at' => now()->subDay()]);

        $this->get('/')->assertOk()->assertDontSee('data-promo-card', false);
    }

    public function test_admin_saves_device_and_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Key Features', 'placement' => 'hero_side', 'image_url' => 'https://example.com/k.jpg',
            'show_on' => 'mobile', 'starts_at' => '2026-11-01T10:00', 'ends_at' => '2026-11-30T23:00', 'is_active' => '1',
        ])->assertRedirect(route('admin.banners.index'));

        $banner = Banner::firstWhere('title', 'Key Features');
        $this->assertSame('mobile', $banner->show_on);
        $this->assertSame('2026-11-01 10:00', $banner->starts_at->format('Y-m-d H:i'));
        $this->assertSame('scheduled', $banner->status());

        $this->actingAs($admin)->get(route('admin.banners.index'))->assertOk()
            ->assertSee('Promo cards')->assertSee('Phone only')->assertSee('data-banner-status="scheduled"', false);
        $this->actingAs($admin)->get(route('admin.banners.edit', $banner))->assertOk()
            ->assertSee('value="2026-11-01T10:00"', false);
    }

    public function test_end_must_follow_start_and_slides_show_everywhere(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Bad Dates', 'placement' => 'hero_side', 'starts_at' => '2026-11-10T10:00', 'ends_at' => '2026-11-01T10:00',
        ])->assertSessionHasErrors('ends_at');

        $this->actingAs($admin)->post(route('admin.banners.store'), [
            'title' => 'Slide', 'placement' => 'hero', 'show_on' => 'mobile', 'is_active' => '1',
        ]);
        $this->assertSame('both', Banner::firstWhere('title', 'Slide')->show_on);
    }
}
