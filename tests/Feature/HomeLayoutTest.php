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
        // Cat 1: one flash product, one best seller and five others
        $this->product($cats[0], 'Flash One', ['is_flash_sale' => true]);
        $this->product($cats[0], 'Best One', ['is_best_seller' => true]);
        foreach (range(1, 5) as $i) $this->product($cats[0], "Plain $i");
        foreach ($cats->slice(1) as $c) $this->product($c, "Only {$c->name}");

        $res = $this->get('/')->assertOk();
        $rows = $res->viewData('featuredHomeCategories');

        $this->assertSame(['Cat 1', 'Cat 2', 'Cat 3', 'Cat 4'], $rows->pluck('name')->all(), 'Only the first 4 featured categories get a row');
        $row = $rows->first()->products->pluck('name');
        $this->assertCount(4, $row);
        $this->assertNotContains('Flash One', $row->all());
        $this->assertNotContains('Best One', $row->all());

        $html = $res->getContent();
        $this->assertStringNotContainsString('data-home-tab="trending"', $html);
        $this->assertStringContainsString('data-home-tab="new-arrivals"', $html);
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
