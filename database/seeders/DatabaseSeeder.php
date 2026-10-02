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
        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $this->seedFeatures();
        $this->seedCoupons();
        $this->call(BannerSeeder::class);
        $this->seedProducts($categories, $brands);
        $this->seedReviews();
        $this->seedOrders();
        $this->seedExpenses();
        $this->seedStaffActivityLogs();
    }

    private function seedUsers(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@marty.com'],
            [
                'name' => 'Marty Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+880 1700-000000',
                'email_verified_at' => now(),
            ]
        );

        // 2. Store Manager
        User::updateOrCreate(
            ['email' => 'manager@marty.com'],
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
            ['email' => 'orders@marty.com'],
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
            ['email' => 'inventory@marty.com'],
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
            ['email' => 'customer@marty.com'],
            [
                'name' => 'Nusrat Jahan',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '01700-111111',
                'address' => 'House 24, Road 7, Dhanmondi',
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
            'site_name' => 'Marty',
            'tagline' => 'Smartwatches, Trending Shoes & Premium Gadgets',
            'logo' => 'uploads/logo.webp',
            'favicon' => 'uploads/favicon.webp',
            'footer_text' => 'Marty is your premier destination for 100% authentic smartwatches, trending sneakers, handcrafted leather shoes, and cutting-edge tech gadgets in Bangladesh with fast nationwide delivery.',
            'contact_phone' => '+880 1700-000000',
            'whatsapp_number' => '+880 1700-000000',
            'messenger_url' => 'https://m.me/martybd',
            'size_guide_enabled' => '0',
            'contact_email' => 'support@marty.com',
            'contact_address' => 'Level 5, Bashundhara City Shopping Mall, Panthapath, Dhaka 1205, Bangladesh',
            'contact_hours' => 'Saturday–Thursday, 10:00 AM – 9:00 PM',
            'contact_title' => 'Customer Support',
            'contact_intro' => 'Need help choosing a smartwatch, sizing shoes, or tracking an order? Our Marty customer care team is happy to assist.',
            'search_placeholder' => 'Search smartwatches, sneakers, loafers, earbuds, power banks...',
            'facebook_url' => 'https://facebook.com/',
            'instagram_url' => 'https://instagram.com/',
            'twitter_url' => 'https://twitter.com/',
            'bkash_number' => '01700-000000',
            'nagad_number' => '01800-000000',
            'rocket_number' => '01900-000000',
            'pay_cod_enabled' => '1',
            'pay_bkash_enabled' => '1',
            'pay_nagad_enabled' => '1',
            'pay_rocket_enabled' => '1',
            'show_cards_in_footer' => '1',
            'shipping_inside_dhaka' => '70',
            'shipping_outside_dhaka' => '130',
            'tax_percent' => '0',
            'shipping_inside_label' => 'Inside Dhaka',
            'shipping_outside_label' => 'Outside Dhaka',
            'currency_symbol' => '৳',
            'currency_code' => 'BDT',
            'default_meta_title' => 'Marty — Smartwatches, Shoes & Smart Gadgets Store in Bangladesh',
            'default_meta_description' => 'Buy 100% authentic Apple Watches, Nike & Adidas shoes, TWS earbuds, and smart gadgets in Bangladesh with warranty and fast delivery.',
            'default_meta_keywords' => 'Marty, Smartwatches, Nike Shoes, Adidas Sneakers, Apple Watch, AirPods, TWS Earbuds, Gadgets Bangladesh',
            'tracking_gtm_id' => '',
            'tracking_ga4_id' => '',
            'tracking_meta_pixel_id' => '',
            'otp_enabled' => '1',
            'header_promo_text' => 'New Season Drops — Use code <b class="text-amber-300">MARTY10</b> for <b class="text-amber-300">10% OFF</b>',
            'header_promo_link' => '/shop?flash=1',
            'shop_subtitle' => 'Our latest smartwatches, trending shoes, audio gear & smart tech arrivals',
            'flash_sale_ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'delivery_eta_text' => 'Estimated delivery within 1–3 business days',
            'home_categories_title' => 'Explore Product Categories',
            'home_hot_deal_title' => 'Flash Sale Deals',
            'home_featured_title' => 'Featured Trending Collection',
            'home_reviews_title' => 'Customer Reviews & Feedback',
            'home_view_more_label' => 'View All Products',
            'default_cta_text' => 'Add to Cart',
            'hero_fallback_badge' => 'Marty Lifestyle & Tech Store',
            'hero_fallback_title' => "100% Authentic Smartwatches,\nShoes & Tech Essentials",
            'hero_fallback_subtitle' => 'Official Apple & Samsung smartwatches, Nike & Adidas sneakers, and smart gadgets delivered to your door with genuine warranty.',
            'show_featured_brands' => '1',
            'home_featured_brands_title' => 'Featured Top Brands',
            'home_featured_brands_subtitle' => 'Shop authentic products directly from globally trusted brands',
            'terms_content' => '',
            'privacy_content' => '',
            'mail_mailer' => 'log',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@marty.com',
            'mail_from_name'    => 'Marty',
            'theme_primary_color'  => '#2563EB',
            'theme_dark_color'     => '#0F172A',
            'theme_surface_color'  => '#F8FAFC',
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
            $email = env('ADMIN_EMAIL') ?: 'admin@' . (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'example.com');
            $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

            User::create([
                'name'              => 'Store Admin',
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command?->warn("Admin account: {$email}" . (env('ADMIN_PASSWORD') ? ' (password from ADMIN_PASSWORD)' : "  password: {$password}  <- save it now, it is not shown again"));
        }

        // Sample contact, payment and social details must not go live; the shop owner fills them in.
        $blank = [
            'contact_phone', 'whatsapp_number', 'messenger_url', 'contact_email', 'contact_address',
            'facebook_url', 'instagram_url', 'twitter_url', 'bkash_number', 'nagad_number', 'rocket_number',
            'header_promo_text', 'header_promo_link', 'flash_sale_ends_at', 'mail_from_address',
        ];
        $settings = array_merge($this->defaultSettings(), array_fill_keys($blank, ''), [
            'site_name'      => config('app.name') ?: 'My Store',
            'mail_from_name' => config('app.name') ?: 'My Store',
            'otp_enabled'    => '0', // needs a working mail server first
        ]);

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::forgetCache();

        $this->seedAttributes();
    }

    private function seedCategories(): array
    {
        Category::query()->delete();

        $data = [
            [
                'Smartwatches & Watches',
                'watches',
                '⌚',
                'Apple Watch, Samsung Galaxy Watch, Amazfit & classic chronograph timepieces.',
                'https://adminapi.applegadgetsbd.com/storage/media/large/Apple-Watch-Series-10-Aluminum-Silver-7765.jpg',
            ],
            [
                'Shoes & Footwear',
                'shoes',
                '👟',
                'Trending sneakers, running shoes, loafers, and handcrafted leather footwear.',
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&h=600&q=80',
            ],
            [
                'Audio & Earbuds',
                'audio-gadgets',
                '🎧',
                'High-fidelity TWS earbuds, noise-canceling headphones & portable Bluetooth speakers.',
                'https://adminapi.applegadgetsbd.com/storage/media/large/AirPods-Pro-(2nd-generation)-USB‐C1a-9576.png',
            ],
            [
                'Smart Gadgets & Power',
                'gadgets',
                '⚡',
                'MagSafe wireless chargers, GaN fast adapters, and high-capacity power banks.',
                'https://adminapi.applegadgetsbd.com/storage/media/thumb/Maxco-MW11-Geometry-Series-3-in-1-Magsafe-Wireless-Charger-3-6253.jpg',
            ],
            [
                'Bags & Accessories',
                'accessories',
                '👜',
                'The Patchee luxury tote bags, crossbody side bags & genuine leather wallets.',
                'https://cdn.shopify.com/s/files/1/0916/6736/6162/files/Patchee-Logo-2025_4.png?v=1747217855&width=600',
            ],
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
                    'meta_title' => "{$name} — Marty Online Store",
                    'meta_description' => "Shop 100% authentic {$name} in Bangladesh with warranty and fast delivery.",
                ]
            );
        }

        return $categories;
    }

    private function seedBrands(): array
    {
        Brand::query()->delete();

        $brandList = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'logo' => 'https://adminapi.applegadgetsbd.com/storage/media/large/logo-3717.png',
                'banner' => 'https://images.unsplash.com/photo-1510519138161-5844a492711f?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Official Apple Watch, AirPods Pro, MagSafe accessories and premium tech devices.',
                'website' => 'https://apple.com',
                'is_featured' => true,
                'position' => 1,
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'logo' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Galaxy Watch, Galaxy Buds, and smart wearables with advanced health monitoring.',
                'website' => 'https://samsung.com',
                'is_featured' => true,
                'position' => 2,
            ],
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'logo' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'World-renowned footwear brand delivering iconic Air Force 1, Pegasus, and running shoes.',
                'website' => 'https://nike.com',
                'is_featured' => true,
                'position' => 3,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'logo' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Trending lifestyle sneakers and high-performance athletic footwear including Ultraboost and Samba.',
                'website' => 'https://adidas.com',
                'is_featured' => true,
                'position' => 4,
            ],
            [
                'name' => 'Anker',
                'slug' => 'anker',
                'logo' => 'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Global leader in smart charging technology, GaN adapters, and Soundcore audio gear.',
                'website' => 'https://anker.com',
                'is_featured' => true,
                'position' => 5,
            ],
            [
                'name' => 'The Patchee',
                'slug' => 'the-patchee',
                'logo' => 'https://thepatchee.com/cdn/shop/files/The_Patchee-Logo-05.png',
                'banner' => 'https://thepatchee.com/cdn/shop/files/Patchee-Logo-2025_4.png',
                'description' => 'Chic, premium tote bags, crossbody side bags, and handcrafted leather accessories.',
                'website' => 'https://thepatchee.com',
                'is_featured' => true,
                'position' => 6,
            ],
            [
                'name' => 'Amazfit',
                'slug' => 'amazfit',
                'logo' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'High-precision GPS fitness smartwatches with ultra-long battery life and AMOLED displays.',
                'website' => 'https://amazfit.com',
                'is_featured' => true,
                'position' => 7,
            ],
            [
                'name' => 'JBL',
                'slug' => 'jbl',
                'logo' => 'https://adminapi.applegadgetsbd.com/storage/media/thumb/JBL-GO-4-Portable-Waterproof-Speaker-Blue-3987.jpg',
                'banner' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Legendary pro sound, waterproof portable Bluetooth speakers, and high-bass wireless audio.',
                'website' => 'https://jbl.com',
                'is_featured' => true,
                'position' => 8,
            ],
        ];

        $brands = [];
        foreach ($brandList as $b) {
            $brand = Brand::create([
                'name' => $b['name'],
                'slug' => $b['slug'],
                'logo' => $b['logo'],
                'banner' => $b['banner'],
                'description' => $b['description'],
                'website' => $b['website'],
                'position' => $b['position'],
                'is_active' => true,
                'is_featured' => $b['is_featured'],
                'meta_title' => "Buy {$b['name']} Products Online in Bangladesh — Marty",
                'meta_description' => "Shop 100% authentic {$b['name']} products at best prices in Bangladesh with warranty and fast delivery.",
            ]);
            $brands[$b['name']] = $brand;
        }

        return $brands;
    }

    private function seedFeatures(): void
    {
        Feature::query()->delete();

        $features = [
            [
                'Nationwide Delivery',
                'Fast, tracked shipping in Bangladesh',
                'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
                0,
            ],
            [
                '100% Genuine Products',
                'Direct source & genuine warranty',
                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                1,
            ],
            [
                'Flexible Payments',
                'Cash on delivery & mobile banking',
                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                2,
            ],
            [
                'Dedicated Support',
                'Sat–Thu: 10:00 AM – 9:00 PM',
                'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
                3,
            ],
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
            'code' => 'MARTY10',
            'description' => '10% Off New Season Collection',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 2000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'LUXE500',
            'description' => '৳500 Flat Discount on Orders ৳5000+',
            'type' => 'fixed',
            'value' => 500,
            'min_order_amount' => 5000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonths(2),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'FREESHIP',
            'description' => 'Free Shipping Special Offer',
            'type' => 'fixed',
            'value' => 130,
            'min_order_amount' => 3000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addWeeks(3),
            'is_active' => true,
        ]);
    }

    private function seedProducts(array $categories, array $brands): void
    {
        Product::query()->delete();

        $productsData = [
            // ================= 1. WATCHES & SMARTWATCHES =================
            [
                'name' => 'Apple Watch Series 10 (GPS) - Aluminum Case with Sport Band',
                'slug' => 'apple-watch-series-10-aluminum',
                'brand' => 'Apple',
                'category' => 'watches',
                'regular_price' => 52000,
                'sale_price' => 48500,
                'unit' => 'Piece',
                'variant_type' => 'Case Size',
                'options' => ['42mm', '46mm'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://adminapi.applegadgetsbd.com/storage/media/large/Apple-Watch-Series-10-Aluminum-Silver-7765.jpg',
                    'https://adminapi.applegadgetsbd.com/storage/media/medium/Apple-Watch-Series-12d-5661.png',
                ],
            ],
            [
                'name' => 'Apple Watch Ultra 2 (GPS + Cellular) - Titanium Case',
                'slug' => 'apple-watch-ultra-2-titanium',
                'brand' => 'Apple',
                'category' => 'watches',
                'regular_price' => 98000,
                'sale_price' => 92500,
                'unit' => 'Piece',
                'variant_type' => 'Band Color',
                'options' => ['Orange Ocean Band', 'Midnight Trail Loop', 'Blue Ocean Band'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://adminapi.applegadgetsbd.com/storage/media/medium/Apple-Watch-Ultra-4jjj-4058.png',
                    'https://adminapi.applegadgetsbd.com/storage/media/large/Apple-Watch-Series-10-Aluminum-Silver-7765.jpg',
                ],
            ],
            [
                'name' => 'Samsung Galaxy Watch 6 Classic - Rotating Bezel Smartwatch',
                'slug' => 'samsung-galaxy-watch-6-classic',
                'brand' => 'Samsung',
                'category' => 'watches',
                'regular_price' => 38000,
                'sale_price' => 34500,
                'unit' => 'Piece',
                'variant_type' => 'Case Size',
                'options' => ['43mm', '47mm'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Amazfit Balance Smartwatch - AMOLED GPS Health & Fitness Watch',
                'slug' => 'amazfit-balance-smartwatch',
                'brand' => 'Amazfit',
                'category' => 'watches',
                'regular_price' => 24500,
                'sale_price' => 21900,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Midnight Black', 'Sunset Grey'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 2. SHOES & FOOTWEAR =================
            [
                'name' => "Nike Air Force 1 '07 Classic Triple White Sneakers",
                'slug' => 'nike-air-force-1-07-triple-white',
                'brand' => 'Nike',
                'category' => 'shoes',
                'regular_price' => 14500,
                'sale_price' => 12800,
                'unit' => 'Pair',
                'variant_type' => 'Shoe Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Air Zoom Pegasus 40 Road Running Shoes',
                'slug' => 'nike-air-zoom-pegasus-40',
                'brand' => 'Nike',
                'category' => 'shoes',
                'regular_price' => 15800,
                'sale_price' => 13900,
                'unit' => 'Pair',
                'variant_type' => 'Shoe Size',
                'options' => ['EU 41', 'EU 42', 'EU 43', 'EU 44'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Ultraboost Light Running Shoes - Core Black',
                'slug' => 'adidas-ultraboost-light-core-black',
                'brand' => 'Adidas',
                'category' => 'shoes',
                'regular_price' => 18500,
                'sale_price' => 16200,
                'unit' => 'Pair',
                'variant_type' => 'Shoe Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Samba OG Classic Leather Sneakers - White & Black',
                'slug' => 'adidas-samba-og-classic',
                'brand' => 'Adidas',
                'category' => 'shoes',
                'regular_price' => 13500,
                'sale_price' => 11900,
                'unit' => 'Pair',
                'variant_type' => 'Shoe Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Handcrafted Premium Leather Chelsea Boots - Cognac Tan',
                'slug' => 'handcrafted-premium-leather-chelsea-boots',
                'brand' => 'The Patchee',
                'category' => 'shoes',
                'regular_price' => 8500,
                'sale_price' => 7400,
                'unit' => 'Pair',
                'variant_type' => 'Shoe Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1638247025967-b4e38f787b76?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 3. AUDIO & EARBUDS =================
            [
                'name' => 'Apple AirPods Pro (2nd Generation) with MagSafe Case (USB-C)',
                'slug' => 'apple-airpods-pro-2nd-gen-usbc',
                'brand' => 'Apple',
                'category' => 'audio-gadgets',
                'regular_price' => 31000,
                'sale_price' => 28500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Glossy White'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://adminapi.applegadgetsbd.com/storage/media/large/AirPods-Pro-(2nd-generation)-USB‐C1a-9576.png',
                    'https://adminapi.applegadgetsbd.com/storage/media/medium/Apple-AirPods-5-2202.png',
                ],
            ],
            [
                'name' => 'Anker Soundcore Liberty 4 NC True Wireless Noise Canceling Earbuds',
                'slug' => 'anker-soundcore-liberty-4-nc',
                'brand' => 'Anker',
                'category' => 'audio-gadgets',
                'regular_price' => 9500,
                'sale_price' => 8400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Velvet Black', 'Clear White', 'Navy Blue'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Samsung Galaxy Buds2 Pro - 24-bit Hi-Fi Sound & ANC',
                'slug' => 'samsung-galaxy-buds2-pro',
                'brand' => 'Samsung',
                'category' => 'audio-gadgets',
                'regular_price' => 19500,
                'sale_price' => 17400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Graphite', 'White', 'Bora Purple'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'JBL Flip 6 Portable Waterproof Bluetooth Speaker',
                'slug' => 'jbl-flip-6-portable-bluetooth-speaker',
                'brand' => 'JBL',
                'category' => 'audio-gadgets',
                'regular_price' => 13500,
                'sale_price' => 11800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Midnight Black', 'Ocean Blue', 'Squad Camo'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://adminapi.applegadgetsbd.com/storage/media/thumb/JBL-GO-4-Portable-Waterproof-Speaker-Blue-3987.jpg',
                    'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 4. SMART GADGETS & POWER =================
            [
                'name' => 'Anker 737 Power Bank (PowerCore 24K 140W Fast Charging)',
                'slug' => 'anker-737-power-bank-24k-140w',
                'brand' => 'Anker',
                'category' => 'gadgets',
                'regular_price' => 15500,
                'sale_price' => 13800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Space Gray'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1609592424109-dd9892f1b177?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Maxco MW11 Geometry 3-in-1 MagSafe Fast Wireless Charger',
                'slug' => 'maxco-mw11-3in1-magsafe-charger',
                'brand' => 'Apple',
                'category' => 'gadgets',
                'regular_price' => 4500,
                'sale_price' => 3800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Matte Black', 'Arctic White'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://adminapi.applegadgetsbd.com/storage/media/thumb/Maxco-MW11-Geometry-Series-3-in-1-Magsafe-Wireless-Charger-3-6253.jpg',
                    'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apple 20W USB-C Fast Power Adapter (Original)',
                'slug' => 'apple-20w-usbc-power-adapter',
                'brand' => 'Apple',
                'category' => 'gadgets',
                'regular_price' => 3200,
                'sale_price' => 2650,
                'unit' => 'Piece',
                'variant_type' => 'Plug Type',
                'options' => ['UK 3-Pin', 'US 2-Pin'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1609592424109-dd9892f1b177?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apple iPhone 18 Pro Max — Titanium Edition',
                'slug' => 'apple-iphone-18-pro-max',
                'brand' => 'Apple',
                'category' => 'gadgets',
                'regular_price' => 189000,
                'sale_price' => 175000,
                'unit' => 'Piece',
                'variant_type' => 'Storage',
                'options' => ['256GB', '512GB', '1TB'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'uploads/banners/hero_slider_iphone_18_pro.jpg',
                ],
            ],
            [
                'name' => 'Apple Mac Mini M6 & M5 Pro — The Mini Reinvented',
                'slug' => 'apple-mac-mini-m6-pro',
                'brand' => 'Apple',
                'category' => 'gadgets',
                'regular_price' => 98000,
                'sale_price' => 89500,
                'unit' => 'Piece',
                'variant_type' => 'Configuration',
                'options' => ['M6 Chip (16GB/512GB)', 'M5 Pro (24GB/1TB)'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'uploads/banners/hero_slider_mac_mini_m6.jpg',
                ],
            ],
            [
                'name' => 'Samsung Galaxy S26 Ultra 5G — Galaxy AI Flagship',
                'slug' => 'samsung-galaxy-s26-ultra',
                'brand' => 'Samsung',
                'category' => 'gadgets',
                'regular_price' => 165000,
                'sale_price' => 155000,
                'unit' => 'Piece',
                'variant_type' => 'Storage',
                'options' => ['12GB / 256GB', '12GB / 512GB'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'uploads/banners/hero_slider_samsung_s26.jpg',
                ],
            ],
            [
                'name' => 'Apple MacBook Neo — Liquid Retina 13-inch',
                'slug' => 'apple-macbook-neo',
                'brand' => 'Apple',
                'category' => 'gadgets',
                'regular_price' => 92000,
                'sale_price' => 85999,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Silver', 'Space Gray', 'Midnight Blue', 'Starlight Gold'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'uploads/banners/hero_promo_macbook_neo.jpg',
                ],
            ],

            // ================= 5. BAGS & ACCESSORIES =================
            [
                'name' => 'The Patchee Elegant Canvas & Leather Work Tote Bag',
                'slug' => 'the-patchee-elegant-canvas-leather-tote',
                'brand' => 'The Patchee',
                'category' => 'accessories',
                'regular_price' => 4200,
                'sale_price' => 3650,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Classic Tan', 'Midnight Black', 'Espresso Brown'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://thepatchee.com/cdn/shop/files/Patchee-Logo-2025_4.png',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'The Patchee Handcrafted Genuine Leather Crossbody Side Bag',
                'slug' => 'the-patchee-leather-crossbody-bag',
                'brand' => 'The Patchee',
                'category' => 'accessories',
                'regular_price' => 3800,
                'sale_price' => 3200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Vintage Brown', 'Matte Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://thepatchee.com/cdn/shop/files/Patchee-Logo-2025_4.png',
                ],
            ],
            [
                'name' => 'The Patchee Slim Bifold RFID Protected Leather Wallet',
                'slug' => 'the-patchee-slim-bifold-rfid-wallet',
                'brand' => 'The Patchee',
                'category' => 'accessories',
                'regular_price' => 1950,
                'sale_price' => 1650,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Dark Brown', 'Pitch Black'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://thepatchee.com/cdn/shop/files/Patchee-Logo-2025_4.png',
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
                    'sku' => 'MARTY-' . strtoupper(Str::substr(md5($pData['slug']), 0, 6)),
                    'brand' => $pData['brand'],
                    'short_description' => "100% authentic, verified {$pData['name']} by {$pData['brand']}. Guaranteed genuine quality with warranty.",
                    'description' => "Experience the original {$pData['name']} by {$pData['brand']}. Crafted with premium grade materials and certified authenticity. Backed by fast nationwide delivery and dedicated customer support across Bangladesh.",
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
                    'meta_title' => "Buy {$pData['name']} Online in Bangladesh — Marty",
                    'meta_description' => "Order authentic {$pData['name']} by {$pData['brand']} at best price in Bangladesh with fast home delivery and warranty.",
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
                $priceAdj = $comboIdx * 150;
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
        $flashProducts = Product::take(6)->get();
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
            ['Mahmudul Hasan', 'mahmud@example.com', 5, 'Great item! Leather and build finish is top-notch.'],
            ['Farhana Yeasmin', 'farhana@example.com', 5, 'Elegant design and smooth order process. Will buy again!'],
            ['Asif Chowdhury', 'asif@example.com', 5, 'Completely genuine product with official serial tags. 10/10 service.'],
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
        $customerUser = User::where('email', 'customer@marty.com')->first();
        $adminUser = User::where('role', 'admin')->first();

        $customerPool = [
            ['Nusrat Jahan', '01711-223344', 'nusrat@gmail.com', 'House 24, Road 7, Dhanmondi', 'Dhaka', 'inside_dhaka'],
            ['Tanvir Ahmed', '01822-334455', 'tanvir@gmail.com', 'Flat 5A, GEC Circle', 'Chattogram', 'outside_dhaka'],
            ['Mim Islam', '01933-445566', 'customer@marty.com', 'House 8, Sector 11, Uttara', 'Dhaka', 'inside_dhaka'],
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
                'user_id'               => $cEmail === 'customer@marty.com' ? $customerUser?->id : null,
                'order_number'          => 'MARTY-' . $orderDate->format('ymd') . '-' . strtoupper(Str::random(4)),
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
            $orderNum = 'POS-' . $orderDate->format('ymd') . '-' . strtoupper(Str::random(4));

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
                'staff_name'  => $superAdmin?->name ?? 'Marty Super Admin',
                'staff_role'  => 'admin',
                'action'      => 'System Initialization',
                'description' => 'Configured Marty multi-category catalog, 8 brand profiles, categories, and payment gateways.',
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
