<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Ghorer Bazar storefront: brand, colours, menu, homepage layout, categories, brands, products and banners.
 *
 *   php artisan db:seed --class=GhorerbazarSeeder
 *
 * Replaces the catalogue (categories, brands, products, hero banners); orders, customers and staff are not touched.
 * The product list lives in database/data/ghorerbazar-products.json. Pictures are drawn placeholders in
 * public/uploads/ghorerbazar/ until the real product photos are uploaded in Admin → Products.
 */
class GhorerbazarSeeder extends Seeder
{
    private const IMG = 'uploads/ghorerbazar';

    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/ghorerbazar-products.json')), true);

        $this->seedSettings();
        $categories = $this->seedCategories($data['categories']);
        $brands = $this->seedBrands($data['brands']);
        $this->seedProducts($data['products'], $categories, $brands);
        $this->seedBanners();
        $this->seedHeaderMenu();
        Setting::forgetCache();
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name'                  => 'Ghorer Bazar',
            'tagline'                    => 'Safe food, delivered home',
            'logo'                       => self::IMG . '/logo.svg',
            'favicon'                    => self::IMG . '/favicon.svg',
            'footer_text'                => 'Ghorer Bazar brings pure, safe and natural food to your door: honey, ghee, oil, dates, spices, nuts and more, with cash on delivery all over Bangladesh.',
            'order_number_prefix'        => 'GB',
            'search_placeholder'         => 'Search in...',
            // Brand colours: orange, the deep green menu bar, and the warm off-white page
            'theme_primary_color'        => '#F68822',
            'theme_dark_color'           => '#051F20',
            'theme_surface_color'        => '#FAF9F5',
            'header_menu_style'          => 'dark',
            'theme_font'                 => 'hind', // Hind Siliguri, as on ghorerbazar.com
            // No scrolling ticker above the header
            'header_promo_text'          => '',
            'home_categories_title'      => 'Featured Categories',
            'home_picks_title'           => 'Top Selling Products',
            'home_featured_brands_title' => 'Our Brands',
            'show_featured_brands'       => '1',
            'home_view_more_label'       => 'View all items',
            'default_cta_text'           => 'Add To Cart',
            'shop_subtitle'              => 'Pure honey, ghee, oil, dates, spices, nuts and more',
            'hero_fallback_badge'        => 'Ghorer Bazar',
            'hero_fallback_title'        => 'Nourishing Better Lives',
            'hero_fallback_subtitle'     => 'Pure, safe and natural food delivered to your door.',
            'default_meta_title'         => 'Ghorer Bazar — Pure Honey, Ghee, Oil, Dates & Organic Food in Bangladesh',
            'default_meta_description'   => 'Order pure honey, gawa ghee, mustard oil, dates, spices, nuts and organic food from Ghorer Bazar. Cash on delivery all over Bangladesh.',
            'default_meta_keywords'      => 'Ghorer Bazar, honey, gawa ghee, mustard oil, dates, organic food Bangladesh',
            'contact_title'              => 'Customer Support',
            'contact_intro'              => 'Need help choosing honey, ghee, oil or dates, or tracking an order? Call or WhatsApp the Ghorer Bazar team.',
            'pdp_quality_badge'          => '100% natural',
            'pdp_authentic_label'        => 'Quality checked',
            // Homepage, as on ghorerbazar.com: banner, categories, top selling, brands, then one row per category
            'home_sections'              => json_encode([
                ['key' => 'trust', 'on' => false],
                ['key' => 'categories', 'on' => true],
                ['key' => 'deals', 'on' => true],
                ['key' => 'brands', 'on' => true],
                ['key' => 'category_rows', 'on' => true],
                ['key' => 'curated', 'on' => false],
                ['key' => 'reviews', 'on' => false],
                ['key' => 'just_for_you', 'on' => false],
                ['key' => 'coupons', 'on' => false],
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::forgetCache();
    }

    /** @return array<string, Category> */
    private function seedCategories(array $rows): array
    {
        // The old catalogue goes; products first so nothing is left pointing at a removed category
        ProductSku::query()->delete();
        ProductVariant::query()->delete();
        ProductImage::query()->delete();
        Product::query()->delete();
        Category::query()->delete();

        $out = [];
        foreach ($rows as $i => $c) {
            $out[$c['slug']] = Category::create([
                'name'             => $c['name'],
                'slug'             => $c['slug'],
                'icon'             => null,
                'image'            => self::IMG . "/categories/{$c['slug']}.svg",
                'description'      => $c['desc'],
                'position'         => $i,
                'is_active'        => true,
                // "Featured on home": one product row each on the homepage
                'is_featured'      => (bool) $c['featured'],
                'meta_title'       => "{$c['name']} — Ghorer Bazar",
                'meta_description' => "Buy {$c['name']} online from Ghorer Bazar. {$c['desc']} Cash on delivery all over Bangladesh.",
            ]);
        }

        return $out;
    }

    /** @return array<string, Brand> */
    private function seedBrands(array $rows): array
    {
        Brand::query()->delete();
        $out = [];
        foreach ($rows as $i => $b) {
            $out[$b['slug']] = Brand::create([
                'name'        => $b['name'],
                'slug'        => $b['slug'],
                'logo'        => self::IMG . "/brands/{$b['slug']}.svg",
                'description' => "{$b['name']} products at Ghorer Bazar.",
                'position'    => $i,
                'is_active'   => true,
                'is_featured' => true,
            ]);
        }

        return $out;
    }

    private function seedProducts(array $rows, array $categories, array $brands): void
    {
        foreach (array_reverse($rows) as $i => $p) {
            $slug = Str::slug($p['name']);
            $category = $categories[$p['cat']];
            $brand = $brands[$p['brand']];
            $old = $p['old'] ?? null;

            $product = Product::create([
                'category_id'       => $category->id,
                'brand_id'          => $brand->id,
                'brand'             => $brand->name,
                'name'              => $p['name'],
                'slug'              => $slug,
                'sku'               => 'GB-' . strtoupper(Str::substr(md5($slug), 0, 6)),
                'short_description' => "{$p['name']}: pure, natural and quality checked by Ghorer Bazar.",
                'description'       => "{$p['name']} from Ghorer Bazar. {$category->description} Every batch is checked before it is packed. Order with cash on delivery anywhere in Bangladesh.",
                'regular_price'     => $old ?: $p['price'],
                'sale_price'        => $old ? $p['price'] : null,
                'cost_price'        => round($p['price'] * 0.7, 2),
                'barcode'           => 'GB-' . strtoupper(Str::substr(md5($slug), 0, 8)),
                'stock_quantity'    => 60,
                'unit'              => 'Piece',
                'is_published'      => true,
                'is_featured'       => (bool) $p['top'],      // Top Selling Products
                'is_new_arrival'    => false,
                'is_best_seller'    => (bool) $p['top'],
                'is_flash_sale'     => false,
                'rating'            => 4.8,
                'reviews_count'     => 0,
                'meta_title'        => "Buy {$p['name']} Online — Ghorer Bazar",
                'meta_description'  => "Order {$p['name']} from Ghorer Bazar with cash on delivery all over Bangladesh.",
            ]);
            // Keep the JSON order on "latest first" lists (rows are created in reverse with rising timestamps)
            $product->forceFill(['created_at' => now()->subMinutes(count($rows) - $i), 'updated_at' => now()])->saveQuietly();

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => self::IMG . "/products/{$slug}.svg",
                'alt'        => $p['name'],
                'is_primary' => true,
                'position'   => 0,
            ]);
        }
    }

    private function seedBanners(): void
    {
        Banner::query()->delete();
        $rows = [
            ['hero', 'hero-1', 'Nourishing Better Lives', '/shop'],
            ['hero', 'hero-2', 'Sundarban Honey', '/category/honey'],
            ['hero', 'hero-3', 'Premium Dates', '/category/dates'],
            ['hero_side', 'side-honey', 'African Organic Wild Honey', '/product/african-organic-wild-honey-1kg'],
        ];
        foreach ($rows as $i => [$placement, $file, $title, $link]) {
            Banner::create([
                'title'     => $title,
                'image'     => self::IMG . "/banners/{$file}.svg",
                'link_url'  => $link,
                'placement' => $placement,
                'position'  => $i,
                'is_active' => true,
                // The side card stands beside the slider on desktop; phones show the slider alone
                'show_on'   => $placement === 'hero_side' ? 'desktop' : 'both',
            ]);
        }

        // The trust strip is off on this layout, but keep sensible wording if it is switched back on
        Feature::where('title', 'Genuine Leather')->update(['title' => '100% Natural', 'subtitle' => 'Pure, quality-checked food']);
    }

    private function seedHeaderMenu(): void
    {
        Setting::updateOrCreate(['key' => 'header_menu'], ['value' => json_encode([
            ['type' => 'link', 'label' => 'Offer Zone', 'url' => '/shop?on_sale=1'],
            ['type' => 'categories'],
        ])]);
    }
}
