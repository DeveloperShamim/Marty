<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Expense;
use App\Models\Feature;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Never put demo data (fake orders, reviews, staff with password "password") on a live store.
        // To seed demo data on a production server anyway, set SEED_DEMO=true.
        if (app()->environment('production') && ! filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOL)) {
            $this->seedProductionEssentials();

            return;
        }

        $this->seedUsers();
        $this->seedSettings();
        $this->seedAttributes();
        $this->seedCategories();
        $this->seedBrands();
        $this->seedFeatures();
        $this->seedCoupons();
        $this->call(BannerSeeder::class);
        $this->seedStaffActivityLogs();
    }

    private function seedUsers(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@vantbd.com'],
            [
                'name' => 'Vant Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '01775-075543',
                'email_verified_at' => now(),
            ]
        );

        // 2. Store Manager
        User::updateOrCreate(
            ['email' => 'manager@vantbd.com'],
            [
                'name' => 'Tanvir Alam (Store Manager)',
                'password' => Hash::make('password'),
                'role' => 'store_manager',
                'phone' => '+880 1711-222333',
                'email_verified_at' => now(),
            ]
        );

        // 3. Order Manager
        User::updateOrCreate(
            ['email' => 'orders@vantbd.com'],
            [
                'name' => 'Rafi Ahmed (Order Manager)',
                'password' => Hash::make('password'),
                'role' => 'order_manager',
                'phone' => '+880 1822-222222',
                'email_verified_at' => now(),
            ]
        );

        // 4. Inventory Manager
        User::updateOrCreate(
            ['email' => 'inventory@vantbd.com'],
            [
                'name' => 'Kalam Hossain (Inventory Manager)',
                'password' => Hash::make('password'),
                'role' => 'inventory_manager',
                'phone' => '+880 1933-333333',
                'email_verified_at' => now(),
            ]
        );

        // 5. Customer Account
        User::updateOrCreate(
            ['email' => 'customer@vantbd.com'],
            [
                'name' => 'Nusrat Jahan',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '01700-111111',
                'address' => 'House 12, Road 3, Green Model Town',
                'city' => 'Dhaka',
                'postal_code' => '1209',
                'email_verified_at' => now(),
            ]
        );
    }

    /** Store settings as seeded for the demo shop. */
    private function defaultSettings(): array
    {
        return [
            'site_name' => 'Vant Bangladesh',
            'tagline' => 'Genuine Leather. Timeless Style.',
            'logo' => '',     // no logo yet: the site name is shown instead
            'favicon' => '',
            'footer_text' => 'Vant Bangladesh brings you genuine leather wallets, belts, watches, shoes and desk accessories, made to last and delivered anywhere in Bangladesh with cash on delivery.',
            'contact_phone' => '01775-075543',
            'whatsapp_number' => '01775-075543',
            'order_hotline' => '01775-075543',
            'order_number_prefix' => 'VB',
            'messenger_url' => '',
            'size_guide_enabled' => '0',
            'contact_email' => 'help@vantbd.com',
            'contact_address' => 'Online store — warehouse: Green Model Town, Dhaka, Bangladesh',
            'contact_hours' => '9:00 AM – 6:00 PM',
            'contact_title' => 'Customer Support',
            'contact_intro' => 'Need help choosing a wallet, finding your belt or shoe size, or tracking an order? Call or WhatsApp the Vant team.',
            'search_placeholder' => 'Type your product...',
            'facebook_url' => '',
            'instagram_url' => '',
            'twitter_url' => '',
            'bkash_number' => '01521-311052',
            'bkash_account_type' => 'personal',
            'nagad_number' => '',
            'rocket_number' => '',
            'pay_cod_enabled' => '1',
            'pay_bkash_enabled' => '1',
            'pay_nagad_enabled' => '0',
            'pay_rocket_enabled' => '0',
            'show_cards_in_footer' => '1',
            'shipping_inside_dhaka' => '70',
            'shipping_outside_dhaka' => '130',
            'tax_percent' => '0',
            'shipping_inside_label' => 'Inside Dhaka',
            'shipping_outside_label' => 'Outside Dhaka',
            'currency_symbol' => '৳',
            'currency_code' => 'BDT',
            'default_meta_title' => 'Vant Bangladesh — Genuine Leather Wallets, Belts, Watches & Shoes',
            'default_meta_description' => 'Shop genuine leather wallets, belts, watches, shoes and desk mats at Vant Bangladesh. Cash on delivery and fast delivery across Bangladesh.',
            'default_meta_keywords' => 'Vant Bangladesh, vantbd, leather wallet Bangladesh, leather belt, card holder, men\'s watch, leather shoes, leather desk mat, mouse pad',
            'tracking_gtm_id' => '',
            'tracking_ga4_id' => '',
            'tracking_meta_pixel_id' => '',
            'otp_enabled' => '1',
            'header_promo_text' => "Cash on delivery all over Bangladesh\nUse code VANT10 for 10% OFF\nFree delivery when you pay with bKash",
            'header_promo_link' => '/shop',
            'ticker_label' => 'Hot Deals',
            'ticker_label_style' => 'dark',
            'ticker_show_countdown' => '1',
            'shop_subtitle' => 'Genuine leather wallets, belts, watches, shoes and desk accessories',
            'delivery_eta_text' => 'Estimated delivery within 1–3 business days',
            'home_categories_title' => 'Shop by Category',
            'home_categories_subtitle' => 'Genuine leather wallets, belts, watches, shoes and desk mats',
            'home_hot_deal_title' => 'Flash Sale Deals',
            'home_featured_title' => 'Best of Vant',
            'home_reviews_title' => 'What Our Customers Say',
            'home_view_more_label' => 'View All Products',
            'default_cta_text' => 'Add to Cart',
            'hero_fallback_badge' => 'Vant Bangladesh',
            'hero_fallback_title' => "Genuine Leather,\nMade to Last",
            'hero_fallback_subtitle' => 'Wallets, belts, watches, shoes and desk mats in real leather, delivered to your door with cash on delivery.',
            'show_featured_brands' => '0', // one house brand: no brand carousel
            'home_featured_brands_title' => 'Our Brands',
            'home_featured_brands_subtitle' => 'Quality leather goods you can trust',
            'terms_content' => '',
            'privacy_content' => '',
            'mail_mailer' => 'log',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@vantbd.com',
            'mail_from_name'    => 'Vant Bangladesh',
            // Leather theme: saddle brown, espresso, warm cream
            'theme_primary_color'  => '#8B5A2B',
            'theme_dark_color'     => '#2B1D14',
            'theme_surface_color'  => '#FAF7F2',
        ];
    }

    private function seedSettings(): void
    {
        foreach ($this->defaultSettings() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::forgetCache();
    }

    /**
     * Live site: an admin account, default settings and product option lists only.
     * No demo products, orders, reviews, customers or staff accounts with the password "password".
     * Existing settings and admins are never overwritten, so this is safe to run again.
     */
    private function seedProductionEssentials(): void
    {
        if (! User::where('role', 'admin')->exists()) {
            $email = env('ADMIN_EMAIL') ?: 'admin@vantbd.com';
            $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

            User::create([
                'name'              => 'Vant Admin',
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command?->warn("Admin account: {$email}" . (env('ADMIN_PASSWORD') ? ' (password from ADMIN_PASSWORD)' : "  password: {$password}  <- save it now, it is not shown again"));
        }

        // The defaults are Vant Bangladesh's real details. Only things not set up yet stay blank.
        $blank = [
            'messenger_url', 'facebook_url', 'instagram_url', 'twitter_url',
            'nagad_number', 'rocket_number', 'flash_sale_ends_at',
        ];
        $settings = array_merge($this->defaultSettings(), array_fill_keys($blank, ''), [
            'otp_enabled'    => '0', // needs a working mail server first
        ]);

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::forgetCache();

        $this->seedAttributes();

        // The real shop set-up (categories, Vant brand, trust strip, coupons, text banners) — only into
        // empty tables, so anything edited in admin is never overwritten. Still no demo products.
        if (Category::count() === 0) {
            $this->seedCategories();
        }
        if (Brand::count() === 0) {
            $this->seedBrands();
        }
        if (Feature::count() === 0) {
            $this->seedFeatures();
        }
        if (Coupon::count() === 0) {
            $this->seedCoupons();
        }
        if (\App\Models\Banner::count() === 0) {
            $this->call(BannerSeeder::class);
        }
    }

    private function seedCategories(): array
    {
        Category::query()->delete();

        $data = [
            ['Wallets & Card Holders', 'wallets', '👛', 'Genuine leather bifold, trifold and long wallets, plus slim card holders.', 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Belts', 'belts', '🪢', 'Formal and casual leather belts with solid metal buckles.', 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Watches', 'watches', '⌚', 'Classic leather-strap and stainless steel watches for men and women.', 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Shoes', 'shoes', '👞', 'Leather oxfords, monk straps, derbies, boots and sandals.', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Leather Mouse Pads', 'mouse-pads', '🖱️', 'Leather mouse pads and desk mats for a clean, premium workspace.', 'https://images.unsplash.com/photo-1629429407759-01cd3d7cfb38?auto=format&fit=crop&w=600&h=600&q=80'],
        ];

        $categories = [];
        foreach ($data as $index => [$name, $slug, $icon, $description, $image]) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'icon' => $icon,
                    'image' => $image,
                    'description' => $description,
                    'position' => $index,
                    'is_active' => true,
                    'is_featured' => true,
                    'meta_title' => "{$name} — Vant Bangladesh",
                    'meta_description' => "Shop genuine leather {$name} in Bangladesh at Vant. Cash on delivery and fast delivery.",
                ]
            );
        }

        return $categories;
    }

    private function seedBrands(): array
    {
        Brand::query()->delete();

        $vant = Brand::create([
            'name' => 'Vant',
            'slug' => 'vant',
            'logo' => null,
            'banner' => 'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
            'description' => 'Vant Bangladesh house brand: genuine leather goods made for everyday use.',
            'website' => 'https://vantbd.com',
            'position' => 0,
            'is_active' => true,
            'is_featured' => true,
            'meta_title' => 'Vant Leather Products — Vant Bangladesh',
            'meta_description' => 'Shop Vant genuine leather wallets, belts, watches, shoes and desk mats in Bangladesh.',
        ]);

        return ['Vant' => $vant];
    }

    private function seedFeatures(): void
    {
        Feature::query()->delete();

        $features = [
            ['Genuine Leather', 'Real leather, checked by hand',
                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 0],
            ['Cash on Delivery', 'Pay when your parcel arrives',
                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 1],
            ['Nationwide Delivery', 'Tracked parcels to all 64 districts',
                'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', 2],
            ['Help 9 AM – 6 PM', 'Call or WhatsApp 01775-075543',
                'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 3],
        ];

        foreach ($features as [$title, $subtitle, $icon, $position]) {
            Feature::create([
                'title' => $title,
                'subtitle' => $subtitle,
                'icon' => $icon,
                'position' => $position,
                'is_active' => true,
            ]);
        }
    }

    private function seedCoupons(): void
    {
        Coupon::query()->delete();

        Coupon::create([
            'code' => 'VANT10',
            'description' => '10% off orders of ৳1,500 or more',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 1500,
            'max_discount' => 500,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'LEATHER300',
            'description' => '৳300 off orders of ৳4,000 or more',
            'type' => 'fixed',
            'value' => 300,
            'min_order_amount' => 4000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(2),
            'is_active' => true,
        ]);
    }

    private function seedProducts(array $categories, array $brands): void
    {
        Product::query()->delete();

        $productsData = [
            // ================= WALLETS & CARD HOLDERS =================
            [
                'name' => 'Vant Classic Bifold Leather Wallet',
                'slug' => 'vant-classic-bifold-wallet',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 1450,
                'sale_price' => 1190,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black', 'Tan'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Slim Leather Card Holder',
                'slug' => 'vant-slim-card-holder',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 750,
                'sale_price' => 650,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Long Zip Leather Wallet',
                'slug' => 'vant-long-zip-wallet',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 1850,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Trifold Leather Wallet',
                'slug' => 'vant-trifold-wallet',
                'brand' => 'Vant',
                'category' => 'wallets',
                'regular_price' => 1350,
                'sale_price' => 1150,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black', 'Tan'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1606503825008-909a67e63c3d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            // ================= BELTS =================
            [
                'name' => 'Vant Formal Leather Belt (Pin Buckle)',
                'slug' => 'vant-formal-leather-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1650,
                'sale_price' => 1390,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Casual Tan Leather Belt',
                'slug' => 'vant-casual-tan-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1550,
                'sale_price' => 1290,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Reversible Black & Brown Belt',
                'slug' => 'vant-reversible-belt',
                'brand' => 'Vant',
                'category' => 'belts',
                'regular_price' => 1850,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Waist Size',
                'options' => ['32', '34', '36', '38', '40'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1666723043169-22e29545675c?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            // ================= WATCHES =================
            [
                'name' => 'Vant Classic Leather Strap Watch',
                'slug' => 'vant-classic-leather-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 3450,
                'sale_price' => 2990,
                'unit' => 'Piece',
                'variant_type' => 'Strap Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Rose Dial Ladies Watch',
                'slug' => 'vant-rose-dial-ladies-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 3250,
                'sale_price' => 2850,
                'unit' => 'Piece',
                'variant_type' => 'Strap Color',
                'options' => ['Brown', 'Tan'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Chronograph Steel Watch',
                'slug' => 'vant-chronograph-steel-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 5450,
                'sale_price' => 4790,
                'unit' => 'Piece',
                'variant_type' => 'Dial Color',
                'options' => ['Black', 'White'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1539874754764-5a96559165b0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Diver Stainless Steel Watch',
                'slug' => 'vant-diver-steel-watch',
                'brand' => 'Vant',
                'category' => 'watches',
                'regular_price' => 4950,
                'sale_price' => 4450,
                'unit' => 'Piece',
                'variant_type' => 'Dial Color',
                'options' => ['Black', 'Silver'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            // ================= SHOES =================
            [
                'name' => 'Vant Leather Oxford Shoes',
                'slug' => 'vant-leather-oxford-shoes',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 4250,
                'sale_price' => 3690,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Double Monk Strap Shoes',
                'slug' => 'vant-double-monk-strap',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 4650,
                'sale_price' => 3990,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Classic Derby Shoes',
                'slug' => 'vant-classic-derby-shoes',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 3950,
                'sale_price' => 3450,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Brogue Leather Boots',
                'slug' => 'vant-brogue-leather-boots',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 5450,
                'sale_price' => 4790,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1638609348722-aa2a3a67db26?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1531310197839-ccf54634509e?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Leather Chelsea Boots',
                'slug' => 'vant-chelsea-boots',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 5250,
                'sale_price' => 4590,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1531310197839-ccf54634509e?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1638609348722-aa2a3a67db26?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Leather Comfort Sandals',
                'slug' => 'vant-leather-sandals',
                'brand' => 'Vant',
                'category' => 'shoes',
                'regular_price' => 1850,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['39', '40', '41', '42', '43', '44'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1603487742131-4160ec999306?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            // ================= LEATHER MOUSE PADS =================
            [
                'name' => 'Vant Leather Desk Mat (80 x 40 cm)',
                'slug' => 'vant-leather-desk-mat',
                'brand' => 'Vant',
                'category' => 'mouse-pads',
                'regular_price' => 1650,
                'sale_price' => 1390,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black', 'Tan'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1629429407759-01cd3d7cfb38?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1593062096033-9a26b09da705?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Vant Leather Mouse Pad (30 x 25 cm)',
                'slug' => 'vant-leather-mouse-pad',
                'brand' => 'Vant',
                'category' => 'mouse-pads',
                'regular_price' => 650,
                'sale_price' => 550,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown', 'Black'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1616400619175-5beda3a17896?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1629429407759-01cd3d7cfb38?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
        ];

        $ratings = [4.7, 4.8, 4.9, 5.0];

        foreach ($productsData as $pData) {
            $category = $categories[$pData['category']] ?? null;
            $brandObj = $brands[$pData['brand']] ?? null;

            if (! $category) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $pData['slug']],
                [
                    'category_id' => $category->id,
                    'brand_id'    => $brandObj?->id,
                    'name' => $pData['name'],
                    'sku' => 'VANT-' . strtoupper(Str::substr(md5($pData['slug']), 0, 6)),
                    'brand' => $pData['brand'],
                    'short_description' => "Genuine leather {$pData['name']}: neat stitching, solid finish, made for everyday use.",
                    'description' => "The {$pData['name']} is part of the Vant Bangladesh leather collection. Every piece is checked by hand before it ships. Order with cash on delivery and get it anywhere in Bangladesh.",
                    'regular_price' => $pData['regular_price'],
                    'sale_price' => $pData['sale_price'],
                    'cost_price' => round(($pData['sale_price'] ?: $pData['regular_price']) * 0.65, 2),
                    'barcode' => 'PRD-' . strtoupper(Str::substr(md5($pData['slug']), 0, 8)),
                    'stock_quantity' => random_int(25, 80),
                    'unit' => $pData['unit'],
                    'is_published' => true,
                    'is_featured' => $pData['is_featured'],
                    'is_new_arrival' => $pData['is_new'],
                    'is_best_seller' => $pData['is_best_seller'],
                    'is_flash_sale' => false,
                    'flash_sale_position' => 0,
                    'flash_sale_progress' => 50,
                    'rating' => $ratings[array_rand($ratings)],
                    'reviews_count' => random_int(14, 52),
                    'meta_title' => "Buy {$pData['name']} Online in Bangladesh — Vant",
                    'meta_description' => "Order the {$pData['name']} at Vant Bangladesh. Genuine leather, cash on delivery and fast delivery across Bangladesh.",
                ]
            );

            // Add product images
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($pData['images'] as $p => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $imgUrl,
                    'alt'        => "{$pData['name']} - View " . ($p + 1),
                    'color'      => null,
                    'is_primary' => $p === 0,
                    'position'   => $p,
                ]);
            }

            // Add Product Variants
            ProductVariant::where('product_id', $product->id)->delete();
            $vPos = 0;

            $attributesMap = [
                $pData['variant_type'] => $pData['options'],
            ];

            foreach ($attributesMap as $attType => $attVals) {
                foreach ($attVals as $val) {
                    ProductVariant::create([
                        'product_id'  => $product->id,
                        'type'        => $attType,
                        'value'       => $val,
                        'price_delta' => 0,
                        'stock'       => random_int(10, 30),
                        'position'    => $vPos++,
                    ]);
                }
            }

            // Add Product SKUs matrix
            \App\Models\ProductSku::where('product_id', $product->id)->delete();
            $catPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3)) ?: 'PRD';
            $productSkuBase = ! empty($product->sku) ? $product->sku : $catPrefix;
            $baseReg = (float) $product->regular_price;
            $baseSale = (float) $product->sale_price;

            $attrKeys = array_keys($attributesMap);
            $cartesianSeeder = function ($keys, $index = 0, $current = []) use (&$cartesianSeeder, $attributesMap) {
                if ($index === count($keys)) return [$current];
                $key = $keys[$index];
                $res = [];
                foreach ($attributesMap[$key] as $val) {
                    $res = array_merge($res, $cartesianSeeder($keys, $index + 1, array_merge($current, [$key => $val])));
                }
                return $res;
            };

            $combos = $cartesianSeeder($attrKeys);

            foreach ($combos as $comboIdx => $combo) {
                $skuSuffix = '';
                foreach ($combo as $k => $v) {
                    $skuSuffix .= preg_replace('/[^A-Za-z0-9]/', '', explode(' ', $v)[0]);
                }
                $priceAdj = 0; // every colour and size costs the same
                $skuReg = $baseReg > 0 ? ($baseReg + $priceAdj) : null;
                $skuSale = $baseSale > 0 ? ($baseSale + $priceAdj) : null;

                \App\Models\ProductSku::create([
                    'product_id'       => $product->id,
                    'sku'              => "{$productSkuBase}-{$skuSuffix}",
                    'barcode'          => 'SKU-' . strtoupper(Str::random(8)),
                    'cost_price'       => round(($skuSale ?: $skuReg) * 0.65, 2),
                    'attributes'       => $combo,
                    'price_adjustment' => $priceAdj,
                    'regular_price'    => $skuReg,
                    'sale_price'       => $skuSale,
                    'stock_quantity'   => random_int(10, 40),
                    'is_active'        => true,
                ]);
            }

            $product->syncTotalStock();
        }

        // Set flash sale products
        $firstPerCategory = Product::orderBy('id')->get()->groupBy('category_id')->map->first()->values();
        $flashProducts = $firstPerCategory->concat(Product::whereNotIn('id', $firstPerCategory->pluck('id'))->orderBy('id')->get())->take(6);
        foreach ($flashProducts as $pos => $fp) {
            $fp->update([
                'is_flash_sale' => true,
                'flash_sale_position' => $pos,
                'flash_sale_progress' => random_int(55, 92),
            ]);
        }
    }

    private function seedReviews(): void
    {
        ProductReview::query()->delete();

        $reviews = [
            ['Tanvir Ahmed', 'tanvir@example.com', 5, 'Super premium quality and 100% authentic! Delivered within 2 days in Dhaka.'],
            ['Sabrina Akter', 'sabrina@example.com', 5, 'Loved the packaging and build quality. Highly recommended store!'],
            ['Mahmudul Hasan', 'mahmud@example.com', 5, 'Great item! The leather and stitching are top-notch.'],
            ['Farhana Yeasmin', 'farhana@example.com', 5, 'Elegant design and smooth order process. Will buy again!'],
            ['Asif Chowdhury', 'asif@example.com', 5, 'Real leather, smells and feels premium. Cash on delivery was easy. 10/10 service.'],
        ];

        $products = Product::take(12)->get();
        foreach ($products as $product) {
            foreach (array_rand($reviews, 2) as $rIdx) {
                [$author, $email, $rating, $comment] = $reviews[$rIdx];
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => null,
                    'author_name' => $author,
                    'author_email' => $email,
                    'rating' => $rating,
                    'title' => 'Verified Purchase Review',
                    'body' => $comment,
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            }
        }
    }

    private function seedOrders(): void
    {
        OrderItem::query()->delete();
        Order::query()->delete();

        $products = Product::with(['images', 'skus'])->get();
        if ($products->isEmpty()) {
            return;
        }

        $insideFee = (float) setting('shipping_inside_dhaka', 70);
        $outsideFee = (float) setting('shipping_outside_dhaka', 130);
        $customerUser = User::where('email', 'customer@vantbd.com')->first();
        $adminUser = User::where('role', 'admin')->first();

        $customerPool = [
            ['Nusrat Jahan', '01711-223344', 'nusrat@gmail.com', 'House 24, Road 7, Dhanmondi', 'Dhaka', 'inside_dhaka'],
            ['Tanvir Ahmed', '01822-334455', 'tanvir@gmail.com', 'Flat 5A, GEC Circle', 'Chattogram', 'outside_dhaka'],
            ['Mim Islam', '01933-445566', 'customer@vantbd.com', 'House 8, Sector 11, Uttara', 'Dhaka', 'inside_dhaka'],
            ['Sakib Hasan', '01644-556677', 'sakib@gmail.com', 'Zindabazar Main Road', 'Sylhet', 'outside_dhaka'],
            ['Farhana Akter', '01755-667788', 'farhana@yahoo.com', 'College Road', 'Rajshahi', 'outside_dhaka'],
            ['Kazi Mahmud', '01866-778899', 'mahmud@gmail.com', 'Shibbari More', 'Khulna', 'outside_dhaka'],
            ['Shuvo Roy', '01977-889900', 'shuvo@gmail.com', 'Chawkbazar', 'Barishal', 'outside_dhaka'],
            ['Tania Sultana', '01588-990011', 'tania@gmail.com', 'CDA Avenue', 'Chattogram', 'outside_dhaka'],
        ];

        // --- A. SEED ONLINE E-COMMERCE ORDERS (Past 25 days) ---
        $onlineScenarios = [
            // Status, PaymentMethod, PaymentStatus, DaysAgo, ReturnType, CourierLoss, CourierName, TrackingCode
            ['delivered', 'bkash', 'verified', 24, null, 0, 'steadfast', 'ST-881201'],
            ['delivered', 'nagad', 'verified', 22, null, 0, 'pathao', 'PT-442190'],
            ['delivered', 'cod', 'verified', 20, null, 0, 'steadfast', 'ST-881345'],
            ['delivered', 'rocket', 'verified', 18, null, 0, 'steadfast', 'ST-881456'],
            ['delivered', 'cod', 'verified', 15, null, 0, 'pathao', 'PT-442301'],
            ['delivered', 'bkash', 'verified', 12, null, 0, 'redx', 'RX-990123'],
            ['delivered', 'nagad', 'verified', 10, null, 0, 'steadfast', 'ST-881789'],
            ['delivered', 'cod', 'verified', 8, null, 0, 'pathao', 'PT-442567'],
            ['shipped', 'cod', 'pending', 4, null, 0, 'steadfast', 'ST-882001'],
            ['shipped', 'bkash', 'verified', 3, null, 0, 'pathao', 'PT-442890'],
            ['shipped', 'cod', 'pending', 2, null, 0, 'steadfast', 'ST-882100'],
            ['processing', 'nagad', 'verified', 1, null, 0, null, null],
            ['confirmed', 'bkash', 'pending', 1, null, 0, null, null],
            ['pending', 'cod', 'pending', 0, null, 0, null, null],

            // RETURN TYPE 1: Buyer Paid Delivery Charge at doorstep (Store Loss = ৳0)
            ['returned', 'cod', 'rejected', 6, 'paid_delivery', 0, 'steadfast', 'ST-881667'],
            ['returned', 'bkash', 'rejected', 9, 'paid_delivery', 0, 'pathao', 'PT-442444'],

            // RETURN TYPE 2: Failed Delivery / Customer Ghosted (Store Loss = the order's delivery charge)
            ['returned', 'cod', 'rejected', 7, 'unpaid_delivery', null, 'steadfast', 'ST-881555'],
            ['returned', 'cod', 'rejected', 14, 'unpaid_delivery', null, 'pathao', 'PT-442222'],
        ];

        foreach ($onlineScenarios as $idx => [$status, $method, $payStatus, $daysAgo, $returnType, $courierLoss, $courier, $tracking]) {
            $cust = $customerPool[$idx % count($customerPool)];
            [$cName, $cPhone, $cEmail, $cAddr, $cCity, $cZone] = $cust;

            $shippingFee = $cZone === 'inside_dhaka' ? $insideFee : $outsideFee;
            $courierLoss = $returnType === 'unpaid_delivery' ? $shippingFee : 0;
            $orderDate = now()->subDays($daysAgo)->subHours(random_int(1, 8));

            $order = Order::create([
                'user_id'               => $cEmail === 'customer@vantbd.com' ? $customerUser?->id : null,
                'order_number'          => \App\Support\OrderNumber::generate(),
                'order_type'            => 'online',
                'customer_name'         => $cName,
                'customer_phone'        => $cPhone,
                'customer_email'        => $cEmail,
                'shipping_address'      => $cAddr,
                'city'                  => $cCity,
                'postal_code'           => (string) random_int(1000, 9999),
                'shipping_zone'         => $cZone,
                'payment_method'        => $method,
                'payment_status'        => $payStatus,
                'status'                => $status,
                'payment_sender_number' => $method === 'cod' ? null : $cPhone,
                'payment_txn_id'        => $method === 'cod' ? null : strtoupper(Str::random(10)),
                'shipping_charge'       => $shippingFee,
                'courier_name'          => $courier,
                'courier_tracking_code' => $tracking,
                'courier_sent_at'       => $courier ? $orderDate->copy()->addHours(3) : null,
                'courier_returned_at'   => $returnType ? $orderDate->copy()->addDays(3) : null,
                'return_type'           => $returnType,
                'courier_loss_amount'   => $courierLoss,
                'return_reason'         => $returnType === 'paid_delivery' ? 'Doorstep refusal (delivery charge paid)' : ($returnType ? 'Customer phone unreachable / failed delivery' : null),
                'return_restocked'      => (bool) $returnType,
                'stock_restored'        => (bool) $returnType,
                'created_at'            => $orderDate,
                'updated_at'            => $orderDate,
            ]);

            $subtotal = 0;
            $pickedProducts = $products->random(random_int(1, 2));

            foreach ($pickedProducts as $prod) {
                $qty = random_int(1, 2);
                $sku = $prod->skus->first();
                $unitPrice = $sku ? ($sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice()) : (float)($prod->sale_price ?: $prod->regular_price);
                $costPrice = $sku ? (float)$sku->getEffectiveCostPrice() : (float)($prod->cost_price ?: round($unitPrice * 0.65, 2));
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $prod->id,
                    'product_sku_id' => $sku?->id,
                    'product_name'   => $prod->name,
                    'image'          => $prod->primaryImage()?->path,
                    'variant'        => $sku?->attributeLabel() ?: 'Standard',
                    'unit_price'     => $unitPrice,
                    'cost_price'     => $costPrice,
                    'quantity'       => $qty,
                    'line_total'     => $lineTotal,
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total'    => $subtotal + $shippingFee,
            ]);
        }

        // --- B. SEED POS CASH REGISTER SALES (Past 14 days) ---
        $posDays = [13, 11, 10, 8, 7, 5, 4, 3, 1, 0];
        $posPayMethods = ['cash', 'cash', 'bkash', 'card', 'cash', 'nagad', 'cash', 'cash', 'card', 'cash'];

        foreach ($posDays as $pIdx => $daysAgo) {
            $orderDate = now()->subDays($daysAgo)->subHours(random_int(2, 6));
            $method = $posPayMethods[$pIdx];
            $orderNum = \App\Support\OrderNumber::generate(pos: true);

            $order = Order::create([
                'user_id'            => $adminUser?->id,
                'order_number'       => $orderNum,
                'order_type'         => 'pos',
                'customer_name'      => $pIdx % 3 === 0 ? 'Tanvir Ahmed' : 'Walk-in Customer',
                'customer_phone'     => $pIdx % 3 === 0 ? '01711-223344' : 'N/A',
                'shipping_address'   => 'POS Counter Sale',
                'city'               => 'In-Store',
                'shipping_zone'      => 'inside_dhaka',
                'payment_method'     => $method,
                'payment_status'     => 'verified',
                'status'             => 'delivered',
                'shipping_charge'    => 0,
                'discount_amount'    => $pIdx % 4 === 0 ? 100 : 0,
                'internal_note'      => 'In-store POS counter sale',
                'created_at'         => $orderDate,
                'updated_at'         => $orderDate,
            ]);

            $subtotal = 0;
            $pickedProducts = $products->random(random_int(1, 2));

            foreach ($pickedProducts as $prod) {
                $qty = random_int(1, 2);
                $sku = $prod->skus->first();
                $unitPrice = $sku ? ($sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice()) : (float)($prod->sale_price ?: $prod->regular_price);
                $costPrice = $sku ? (float)$sku->getEffectiveCostPrice() : (float)($prod->cost_price ?: round($unitPrice * 0.65, 2));
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $prod->id,
                    'product_sku_id' => $sku?->id,
                    'product_name'   => $prod->name,
                    'image'          => $prod->primaryImage()?->path,
                    'variant'        => $sku?->attributeLabel() ?: 'Standard',
                    'unit_price'     => $unitPrice,
                    'cost_price'     => $costPrice,
                    'quantity'       => $qty,
                    'line_total'     => $lineTotal,
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate,
                ]);
            }

            $discount = (float) $order->discount_amount;
            $netTotal = max(0, $subtotal - $discount);
            $cashTendered = $method === 'cash' ? ceil($netTotal / 500) * 500 : $netTotal;
            $changeAmount = max(0, $cashTendered - $netTotal);

            $order->update([
                'subtotal'          => $subtotal,
                'total'             => $netTotal,
                'pos_cash_tendered' => $cashTendered,
                'pos_change_amount' => $changeAmount,
            ]);
        }
    }

    private function seedStaffActivityLogs(): void
    {
        $superAdmin = User::where('role', 'admin')->first();
        $storeManager = User::where('role', 'store_manager')->first();
        $orderManager = User::where('role', 'order_manager')->first();

        $logs = [
            [
                'user_id'     => $superAdmin?->id,
                'staff_name'  => $superAdmin?->name ?? 'Vant Admin',
                'staff_role'  => 'admin',
                'action'      => 'System Initialization',
                'description' => 'Configured the Vant Bangladesh leather catalog, categories and payment methods.',
                'ip_address'  => '127.0.0.1',
                'created_at'  => now()->subDays(3),
            ],
            [
                'user_id'     => $storeManager?->id,
                'staff_name'  => $storeManager?->name ?? 'Tanvir Alam',
                'staff_role'  => 'store_manager',
                'action'      => 'Created Product Catalog',
                'description' => 'Seeded authentic smartwatches, Nike & Adidas shoes, audio earbuds, and accessories.',
                'ip_address'  => '103.45.12.89',
                'created_at'  => now()->subDays(2),
            ],
            [
                'user_id'     => $orderManager?->id,
                'staff_name'  => $orderManager?->name ?? 'Rafi Ahmed',
                'staff_role'  => 'order_manager',
                'action'      => 'Verified Order Payment',
                'description' => 'Verified bKash transaction for Order #MARTY-260910-NYJD.',
                'ip_address'  => '103.112.44.12',
                'created_at'  => now()->subDays(1),
            ],
        ];

        foreach ($logs as $log) {
            \App\Models\StaffActivityLog::create($log);
        }
    }

    private function seedExpenses(): void
    {
        Expense::query()->delete();

        $admin = User::where('role', 'admin')->first();
        $expenses = [
            [
                'title'        => 'Facebook Ad Campaign - Smartwatch & Gadgets Boost',
                'category'     => 'marketing',
                'amount'       => 2500.00,
                'expense_date' => now()->subDays(5)->format('Y-m-d'),
                'notes'        => 'Targeted boost for Apple Watch and Earbuds campaign',
                'created_by'   => $admin?->id,
            ],
            [
                'title'        => 'Facebook Ad Campaign - Flash Sale Promo',
                'category'     => 'marketing',
                'amount'       => 1800.00,
                'expense_date' => now()->subDays(2)->format('Y-m-d'),
                'notes'        => 'Weekend conversions boost',
                'created_by'   => $admin?->id,
            ],
            [
                'title'        => 'Custom Poly Packaging Bags & Bubble Wraps',
                'category'     => 'packaging',
                'amount'       => 1200.00,
                'expense_date' => now()->subDays(8)->format('Y-m-d'),
                'notes'        => 'Purchased 500 pcs branded packaging',
                'created_by'   => $admin?->id,
            ],
            [
                'title'        => 'Local Sourcing & Transportation',
                'category'     => 'sourcing_travel',
                'amount'       => 950.00,
                'expense_date' => now()->subDays(12)->format('Y-m-d'),
                'notes'        => 'Courier pickup and warehouse delivery transport',
                'created_by'   => $admin?->id,
            ],
        ];

        foreach ($expenses as $exp) {
            Expense::create($exp);
        }
    }


    private function seedAttributes(): void
    {
        $presets = [
            'Shoe Size' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44', 'EU 45'],
            'Case Size' => ['41mm', '42mm', '43mm', '45mm', '46mm', '47mm', '49mm'],
            'Color'     => ['Midnight', 'Starlight', 'Silver', 'Space Gray', 'Triple White', 'Core Black', 'Panda', 'Navy Blue'],
            'Wattage'   => ['20W', '35W', '65W', '100W', '140W'],
            'Material'  => ['Genuine Leather', 'Canvas', 'Breathable Mesh', 'Titanium', 'Silicone'],
        ];

        foreach ($presets as $typeName => $vals) {
            $type = \App\Models\ProductAttributeType::updateOrCreate(
                ['slug' => Str::slug($typeName)],
                ['name' => $typeName, 'is_active' => true]
            );

            foreach ($vals as $pos => $v) {
                \App\Models\ProductAttributeValue::firstOrCreate([
                    'product_attribute_type_id' => $type->id,
                    'value'                     => $v,
                ], [
                    'position' => $pos,
                ]);
            }
        }
    }
}
