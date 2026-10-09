<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Setting::forgetCache(); // the settings bag is static and would carry over from an earlier test
    }

    private function product(Category $cat, string $name, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true,
        ], $attrs));
    }

    public function test_home_shows_four_short_category_rows_without_repeating_flash_or_best_sellers(): void
    {
        $cats = collect(range(1, 5))->map(fn ($i) => Category::create([
            'name' => "Cat $i", 'slug' => "cat-$i", 'is_active' => true, 'is_featured' => true, 'position' => $i,
        ]));
        // A timed flash sale is running, so the Flash deals section shows
        \App\Models\Setting::put('flash_sale_ends_at', now()->addDay()->toDateTimeString());
        // Cat 1: one flash product, one best seller and five others
        $this->product($cats[0], 'Flash One', ['is_flash_sale' => true]);
        $this->product($cats[0], 'Best One', ['is_best_seller' => true]);
        foreach (range(1, 5) as $i) $this->product($cats[0], "Plain $i");
        foreach ($cats->slice(1) as $c) $this->product($c, "Only {$c->name}");

        $res = $this->get('/')->assertOk();
        $rows = $res->viewData('featuredHomeCategories');

        $this->assertSame(['Cat 1', 'Cat 2', 'Cat 3', 'Cat 4'], $rows->pluck('name')->all(), 'Only the first 4 featured categories get a row');
        $row = $rows->first()->products->pluck('name');
        $this->assertCount(5, $row, 'All 5 not-yet-shown products slide in the row');
        $this->assertNotContains('Flash One', $row->all());
        $this->assertNotContains('Best One', $row->all());
        $this->assertCount(1, $rows[1]->products, 'A category with one product still gets its row');

        $html = $res->getContent();
        $this->assertStringNotContainsString('data-home-tab="trending"', $html);
        $this->assertStringContainsString('data-home-tab="new-arrivals"', $html);
    }

    public function test_category_rows_slide_then_just_for_you_then_vouchers_last(): void
    {
        $belts = Category::create(['name' => 'Belts', 'slug' => 'belts', 'is_active' => true]);
        $wallets = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'is_featured' => true, 'position' => 1]);
        $inCart = $this->product($belts, 'Belt In Cart');
        foreach (range(1, 3) as $i) $this->product($belts, "Belt $i");
        foreach (range(1, 12) as $i) $this->product($wallets, "Wallet $i", $i <= 8 ? ['is_best_seller' => true, 'is_new_arrival' => true] : []);
        $this->product($wallets, 'Sold Out Wallet', ['stock_quantity' => 0]);
        $this->postJson(route('cart.add'), ['product_id' => $inCart->id])->assertOk();
        \App\Models\Coupon::create(['code' => 'TEN', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $res = $this->get('/')->assertOk();
        $html = $res->getContent();
        $this->assertStringContainsString('data-auto-row', $html);

        $jfy = $res->viewData('justForYou')->pluck('name');
        $this->assertCount(\App\Http\Controllers\HomeController::JUST_FOR_YOU, $jfy);
        $this->assertNotContains('Belt In Cart', $jfy->all(), 'Not what is already in the cart');
        $this->assertNotContains('Sold Out Wallet', $jfy->all());
        $this->assertSame(3, $jfy->take(3)->filter(fn ($n) => str_starts_with($n, 'Belt '))->count(), 'Same category as the cart comes first');

        $this->assertGreaterThan(strpos($html, 'data-auto-row'), strpos($html, 'data-just-for-you'), 'Just for you comes after the category rows');
        $this->assertGreaterThan(strpos($html, 'data-just-for-you'), strpos($html, 'data-home-coupons'), 'Vouchers come last, after Just for you');
        $this->assertGreaterThan(strpos($html, 'Customer Feedback') ?: 0, strpos($html, 'data-just-for-you'));
    }

    public function test_our_picks_replaces_flash_deals_unless_a_timed_sale_is_running(): void
    {
        $cat = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true]);
        $this->product($cat, 'Picked Wallet', ['is_featured' => true]);
        $this->product($cat, 'Flash Wallet', ['is_flash_sale' => true]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-our-picks', $html);
        $this->assertStringNotContainsString('data-home-flash', $html);
        $this->assertStringNotContainsString('>Flash Sale</span>', $html, 'No Flash Sale badge on cards outside a sale');
        $this->assertStringNotContainsString('class="flash-pill', $html, 'No Flash Sale button in the header outside a sale');
        $this->assertStringNotContainsString('LIMITED TIME DROPS', $html);

        \App\Models\Setting::put('flash_sale_ends_at', now()->addDay()->toDateTimeString());
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-home-flash', $html);
        $this->assertStringContainsString('data-countdown-end', $html);
        $this->assertStringContainsString('>Flash Sale</span>', $html, 'Badge is back during the sale');
        $this->assertStringNotContainsString('data-our-picks', $html);
        $this->assertStringNotContainsString('LIMITED TIME DROPS', $html);

        \App\Models\Setting::put('flash_sale_ends_at', now()->subHour()->toDateTimeString());
        $this->assertStringContainsString('data-our-picks', $this->get('/')->assertOk()->getContent(), 'An ended sale goes back to Our picks');
    }

    public function test_brands_show_as_logos_only_when_there_is_more_than_one(): void
    {
        $cat = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true]);
        $one = Brand::create(['name' => 'Vant', 'slug' => 'vant', 'is_active' => true, 'is_featured' => true]);
        $this->product($cat, 'Bifold', ['brand_id' => $one->id]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('swiper brandsSwiper', $html, 'A single brand needs no brand row');
        $this->assertStringNotContainsString('Explore Vant', $html, 'Brands no longer get their own product section');

        $two = Brand::create(['name' => 'Hide Co', 'slug' => 'hide-co', 'is_active' => true]);
        $this->product($cat, 'Belt', ['brand_id' => $two->id]);
        Brand::create(['name' => 'Empty Co', 'slug' => 'empty-co', 'is_active' => true]);

        $res = $this->get('/')->assertOk();
        $this->assertStringContainsString('swiper brandsSwiper', $res->getContent());
        $this->assertSame(['Vant', 'Hide Co'], $res->viewData('featuredBrands')->pluck('name')->all(), 'Featured first, brands without products left out');
    }
}
