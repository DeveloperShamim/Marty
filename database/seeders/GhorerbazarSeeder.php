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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Ghorer Bazar storefront: brand, colours, menu, homepage layout, categories, brands, products and banners.
 *
 *   php artisan db:seed --class=GhorerbazarSeeder
 *
 * Replaces the catalogue (categories, brands, products, hero banners); orders, customers and staff are not touched.
 * The catalogue in database/data/ghorerbazar-products.json is copied from ghorerbazar.com: product names, prices,
 * old prices, labels, categories, brands, banners and the photo of each. Photos are downloaded from the Ghorer Bazar
 * CDN into public/uploads/ghorerbazar/cdn/ (once; a copy already there is reused). When the server cannot reach the
 * CDN, the picture links straight to it instead.
 */
class GhorerbazarSeeder extends Seeder
{
    private const IMG = 'uploads/ghorerbazar';

    private string $cdn = 'https://cdn.ghorerbazar.com';

    /** Set to false after the first failed download, so an offline server doesn't wait on every picture. */
    private bool $cdnReachable = true;

    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/ghorerbazar-products.json')), true);
        $this->cdn = rtrim($data['cdn'] ?? $this->cdn, '/');

        $this->seedSettings($data);
        $categories = $this->seedCategories($data['categories']);
        $brands = $this->seedBrands($data['brands']);
        $this->seedProducts($data['products'], $categories, $brands);
        $this->seedBanners($data['banners']);
        $this->seedHeaderMenu($categories);
        Setting::forgetCache();
    }

    private function seedSettings(array $data): void
    {
        $settings = [
            'site_name'                  => 'Ghorer Bazar',
            'tagline'                    => 'Safe food, delivered home',
            'logo'                       => $this->picture($data['logo'] ?? '', self::IMG . '/logo.svg'),
            'favicon'                    => self::IMG . '/favicon.svg',
            'footer_text'                => 'Ghorer Bazar is an e-commerce platform dedicated to providing safe and reliable food to every home.',
            'order_number_prefix'        => 'GB',
            'search_placeholder'         => 'Search in...',
            // Brand colours: orange, the deep green menu bar, and the warm off-white page
            'theme_primary_color'        => '#F48721',
            'theme_dark_color'           => '#041F1E',
            'theme_surface_color'        => '#F9F8F6',
            'header_menu_style'          => 'dark',
            'theme_font'                 => 'open-sans', // Open Sans, as on ghorerbazar.com
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
            // Their footer and contact details
            'contact_phone'              => '+8809642922922',
            'contact_email'              => 'contact@ghorerbazar.com',
            'contact_address'            => 'Rampura, Dhaka, Bangladesh',
            'whatsapp_number'            => '8809642922922',
            'footer_copyright'           => 'Copyright © ' . date('Y') . ' GhorerBazar',
            'category_sort'              => 'position', // categories in ghorerbazar.com's order, not by size
            'home_category_rows'         => json_encode(array_map(fn ($row) => [
                'title' => $row['title'],
                'cats'  => $row['cats'],
                'only'  => array_map(fn ($name) => Str::slug($name), $row['only'] ?? []),
            ], $data['home_rows'] ?? [])),
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
                ['key' => 'just_for_you', 'on' => true],
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
        $featured = collect(json_decode((string) Setting::where('key', 'home_category_rows')->value('value'), true) ?: [])
            ->pluck('cats')->flatten()->unique()->all();
        foreach ($rows as $i => $c) {
            $out[$c['slug']] = Category::create([
                'name'             => $c['name'],
                'slug'             => $c['slug'],
                'icon'             => null,
                'image'            => $this->picture($c['image'] ?? '', self::IMG . "/categories/{$c['slug']}.svg"),
                'description'      => $c['desc'],
                'position'         => $i,
                'is_active'        => true,
                // "Featured on home": one product row each on the homepage
                'is_featured'      => in_array($c['slug'], $featured, true),
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
                'logo'        => $this->picture($b['logo'] ?? '', self::IMG . "/brands/{$b['slug']}.svg"),
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
        $count = count($rows);
        foreach ($rows as $i => $p) {
            $slug = Str::slug($p['name']);
            $category = $categories[$p['cat']];
            $brand = $brands[$p['brand']];
            $old = $p['old'] ?? null;
            $label = $p['label'] ?? '';

            $product = Product::create([
                'category_id'       => $category->id,
                'brand_id'          => $brand->id,
                'brand'             => $brand->name,
                'name'              => $p['name'],
                'slug'              => $slug,
                'sku'               => 'GB-' . strtoupper(Str::substr(md5($slug), 0, 6)),
                'short_description' => "{$p['name']}: pure, natural and quality checked by Ghorer Bazar.",
                'description'       => "<p>{$p['name']} from Ghorer Bazar. {$category->description}</p><p>Every batch is checked before it is packed. Order with cash on delivery anywhere in Bangladesh.</p>",
                'regular_price'     => $old ?: $p['price'],
                'sale_price'        => $old ? $p['price'] : null,
                'cost_price'        => round($p['price'] * 0.7, 2),
                'barcode'           => 'GB-' . strtoupper(Str::substr(md5($slug), 0, 8)),
                'stock_quantity'    => 60,
                'unit'              => 'Piece',
                'is_published'      => true,
                'is_featured'       => (bool) ($p['top'] ?? false),      // Top Selling Products
                'is_new_arrival'    => $label === 'New Arrival',
                'is_best_seller'    => $label === 'Best Selling' || ! empty($p['top']),
                'is_flash_sale'     => false,
                'rating'            => 4.8,
                'reviews_count'     => 0,
                'meta_title'        => "Buy {$p['name']} Online — Ghorer Bazar",
                'meta_description'  => "Order {$p['name']} from Ghorer Bazar with cash on delivery all over Bangladesh.",
            ]);
            // "Latest first" lists keep the JSON order: the first row is the newest
            $product->forceFill(['created_at' => now()->subMinutes($i + 1), 'updated_at' => now()])->saveQuietly();

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $this->picture('productImages/' . $p['img'], self::IMG . "/products/{$slug}.svg"),
                'alt'        => $p['name'],
                'is_primary' => true,
                'position'   => 0,
            ]);
        }
    }

    /**
     * A picture from the Ghorer Bazar CDN: the local copy in public/uploads/ghorerbazar/cdn/ (downloaded now if missing),
     * or the CDN link itself when it can't be downloaded. $fallback is used when there is no CDN path at all.
     */
    private function picture(string $cdnPath, string $fallback): string
    {
        $cdnPath = ltrim($cdnPath, '/');
        if ($cdnPath === '') {
            return $fallback;
        }
        $local = self::IMG . '/cdn/' . str_replace('/', '-', $cdnPath);
        if (is_file(public_path($local))) {
            return $local;
        }
        $url = $this->cdn . '/' . str_replace(' ', '%20', $cdnPath);
        if ($this->cdnReachable && ! app()->runningUnitTests()) {
            try {
                $res = Http::timeout(15)->connectTimeout(5)->get($url);
                if ($res->successful() && str_starts_with((string) $res->header('Content-Type'), 'image/')) {
                    File::ensureDirectoryExists(dirname(public_path($local)));
                    File::put(public_path($local), $res->body());

                    return $local;
                }
            } catch (\Throwable) {
                // fall through to the link
            }
            $this->cdnReachable = false;
        }

        return $url;
    }

    private function seedBanners(array $rows): void
    {
        Banner::query()->delete();
        foreach ($rows as $i => $b) {
            Banner::create([
                'title'     => $b['title'],
                'image'     => $this->picture($b['image'], self::IMG . '/banners/hero-1.svg'),
                'link_url'  => $b['link'],
                'placement' => $b['placement'],
                'position'  => $i,
                'is_active' => true,
                // The side card stands beside the slider on desktop; phones show the slider alone
                'show_on'   => $b['placement'] === 'hero_side' ? 'desktop' : 'both',
            ]);
        }

        // The trust strip is off on this layout, but keep sensible wording if it is switched back on
        Feature::where('title', 'Genuine Leather')->update(['title' => '100% Natural', 'subtitle' => 'Pure, quality-checked food']);
    }

    /** Their menu bar: Offer Zone, then the categories in the order ghorerbazar.com lists them. */
    private function seedHeaderMenu(array $categories): void
    {
        $order = ['honey', 'oil-ghee', 'dates', 'spices', 'nuts-seeds', 'beverage', 'rice', 'flours-lentils', 'organic', 'functional-food'];
        $items = [['type' => 'link', 'label' => 'Offer Zone', 'url' => '/shop?on_sale=1']];
        foreach ($order as $slug) {
            if (isset($categories[$slug])) {
                $items[] = ['type' => 'category', 'ref' => $categories[$slug]->id] + ($slug === 'organic' ? ['label' => 'Certified'] : []);
            }
        }
        Setting::updateOrCreate(['key' => 'header_menu'], ['value' => json_encode($items)]);
    }
}
