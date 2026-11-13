<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Page width (Admin → Store Settings → Theme): Standard keeps the original layout, Wide widens it with 5 products per row. */
class StoreWidthTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
        $cat = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'is_featured' => true, 'position' => 1]);
        $this->product = Product::create(['category_id' => $cat->id, 'name' => 'Card Holder', 'slug' => 'card-holder', 'regular_price' => 900, 'stock_quantity' => 5, 'is_published' => true]);
        Product::create(['category_id' => $cat->id, 'name' => 'Bifold', 'slug' => 'bifold', 'regular_price' => 1200, 'stock_quantity' => 5, 'is_published' => true]);
    }

    private function bodyClass(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();
        $this->assertSame(1, preg_match('#<body class="([^"]*)"#', $html, $m));

        return $m[1];
    }

    public function test_standard_is_the_default_and_adds_nothing(): void
    {
        $this->assertStringNotContainsString('store-wide', $this->bodyClass('/'));
        $this->assertStringNotContainsString('slidesPerView: 8', $this->get('/')->getContent());
    }

    public function test_admin_picks_wide_and_the_storefront_widens(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()
            ->assertSee('Page width')->assertSee('Wide (1,440px)');

        $this->actingAs($admin)->put(route('admin.settings.update-section', 'brand'), ['site_name' => 'Vant', 'theme_width' => 'wide'])
            ->assertSessionHasNoErrors();
        Setting::forgetCache();
        $this->assertSame('wide', setting('theme_width'));

        foreach (['/', route('shop'), route('product.show', $this->product)] as $url) {
            $this->assertStringContainsString('store-wide', $this->bodyClass($url), $url);
        }
        $this->assertStringContainsString('slidesPerView: 8', $this->get('/')->getContent(), 'One more category in the slider');
    }

    public function test_checkout_stays_narrow_in_wide_mode(): void
    {
        Setting::put('theme_width', 'wide');
        Setting::forgetCache();

        $html = $this->get(route('checkout.show'))->getContent();
        if (preg_match('#<body class="([^"]*)"#', $html, $m)) {
            $this->assertStringNotContainsString('store-wide', $m[1]);
        } else {
            $this->assertStringNotContainsString('store-wide', $html);
        }
    }

    public function test_unknown_width_is_refused(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.settings.update-section', 'brand'), ['site_name' => 'Vant', 'theme_width' => 'huge'])
            ->assertSessionHasErrors('theme_width');
    }

    public function test_product_grids_carry_the_width_marker(): void
    {
        $this->get(route('shop'))->assertOk()->assertSee('pgrid shop-grid', false);
        $this->get(route('product.show', $this->product))->assertOk()->assertSee('prow-item', false);
    }

    /** Wide keeps the homepage banner at the height it has on Standard, and admin says slides are trimmed. */
    public function test_wide_holds_the_homepage_banner_height(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-hero-main', $html);
        $this->assertStringContainsString('body.store-wide [data-hero-main] { height: 430px; aspect-ratio: auto; }', $html);
    }
}
