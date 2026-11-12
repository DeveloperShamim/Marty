<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\HomeSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Homepage sections, theme style (font, corners) and the dashboard setup checklist. */
class StoreBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function shopWithCouponsAndCategories(): void
    {
        $cat = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'is_featured' => true, 'position' => 1]);
        Product::create(['category_id' => $cat->id, 'name' => 'Card Holder', 'slug' => 'card-holder', 'regular_price' => 900, 'stock_quantity' => 5, 'is_published' => true]);
        Coupon::create(['code' => 'TEN', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
    }

    public function test_owner_reorders_and_hides_homepage_sections(): void
    {
        $this->shopWithCouponsAndCategories();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.home-sections.index'))->assertOk()
            ->assertSee('Homepage sections')->assertSee('Customer reviews');

        // Coupons first, categories hidden
        $order = ['trust', 'coupons', 'curated', 'deals', 'brands', 'category_rows', 'reviews', 'just_for_you', 'categories'];
        $on = array_values(array_diff($order, ['categories']));
        $this->actingAs($admin)->put(route('admin.home-sections.update'), ['order' => $order, 'on' => $on])->assertRedirect();
        Setting::forgetCache();

        $this->assertSame(['coupons', 'curated', 'deals', 'brands', 'category_rows', 'reviews', 'just_for_you'], HomeSections::ordered());
        $this->assertFalse(HomeSections::isOn('categories'));

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-home-coupons', $html);
        $this->assertStringNotContainsString('id="catPrev"', $html, 'Hidden categories slider is gone');
        $this->assertLessThan(strpos($html, 'data-home-tab="new-arrivals"'), strpos($html, 'data-home-coupons'), 'Coupons now come before the curated tabs');
    }

    public function test_sections_default_to_the_old_brand_and_review_switches_and_reset_works(): void
    {
        Setting::put('show_home_reviews', '0');
        Setting::forgetCache();
        $this->assertFalse(HomeSections::isOn('reviews'));
        $this->assertTrue(HomeSections::isOn('coupons'));
        $this->assertSame('categories', HomeSections::ordered()[0]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.home-sections.update'), ['reset' => 1])->assertRedirect();
        Setting::forgetCache();
        $this->assertTrue(HomeSections::isOn('reviews'));
        $this->assertSame('1', setting('show_home_reviews'));
    }

    public function test_unknown_sections_are_refused(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.home-sections.update'), ['order' => ['hacker'], 'on' => []])
            ->assertSessionHasErrors('order.0');
    }

    public function test_font_and_corner_style_reach_the_storefront(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.settings.update-section', 'brand'), [
            'site_name' => 'Kick Zone', 'theme_font' => 'oswald', 'theme_corners' => 'sharp',
        ])->assertSessionHasNoErrors();
        Setting::forgetCache();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString("--font-display: 'Oswald', 'Hind Siliguri', sans-serif;", $html);
        $this->assertStringContainsString('--r-md: 4px;', $html);
        $this->assertStringContainsString('family=Oswald', $html);
        $this->assertStringNotContainsString('&#039;Oswald', $html, 'The CSS variables are not HTML-escaped');

        $this->actingAs($admin)->put(route('admin.settings.update-section', 'brand'), ['site_name' => 'X', 'theme_font' => 'comic-sans'])
            ->assertSessionHasErrors('theme_font');
    }

    public function test_logo_colours_need_a_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::put('logo', '');
        Setting::forgetCache();

        $this->actingAs($admin)->getJson(route('admin.settings.logo-colours'))->assertStatus(422)
            ->assertJsonPath('message', 'Upload a logo first, then try again.');
    }

    public function test_logo_colours_read_the_uploaded_logo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dir = storage_path('app/public/branding');
        @mkdir($dir, 0777, true);
        $img = imagecreatetruecolor(40, 40);
        imagefill($img, 0, 0, imagecolorallocate($img, 37, 99, 235)); // #2563EB
        imagepng($img, $dir . '/test-logo.png');
        Setting::put('logo', 'branding/test-logo.png');
        Setting::forgetCache();

        $res = $this->actingAs($admin)->getJson(route('admin.settings.logo-colours'))->assertOk();
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $res->json('primary'));
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $res->json('dark'));
        @unlink($dir . '/test-logo.png');
    }

    public function test_setup_checklist_shows_until_the_store_is_ready(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Setting::put('logo', 'uploads/brand/vant-logo.png'); // the logo that ships with the code
        Setting::forgetCache();

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();
        $this->assertStringContainsString('Finish setting up your store', $html);
        $this->assertStringContainsString('data-setup-step="brand" data-done="0"', $html);
        $this->assertStringContainsString('data-setup-step="products" data-done="0"', $html);

        // Staff without Settings access never see it
        $orders = User::factory()->create(['role' => 'store_manager']);
        $this->actingAs($orders)->get('/admin')->assertOk()->assertDontSee('Finish setting up your store');
    }

    public function test_cards_badge_is_off_unless_turned_on(): void
    {
        Setting::put('show_cards_in_footer', '0');
        Setting::forgetCache();
        $this->get('/')->assertOk()->assertDontSee('>Cards<', false);
    }
}
