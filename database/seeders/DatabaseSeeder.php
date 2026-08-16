<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
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
        $this->seedConversations();
        $this->seedVisitorLogs();
        $this->seedStaffActivityLogs();
    }

    private function seedUsers(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@solebd.com'],
            [
                'name' => 'SoleBd Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+880 1700-000000',
                'email_verified_at' => now(),
            ]
        );

        // 2. Store Manager
        User::updateOrCreate(
            ['email' => 'store.manager@solebd.com'],
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
            ['email' => 'order.manager@solebd.com'],
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
            ['email' => 'inventory.manager@solebd.com'],
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
            ['email' => 'customer@solebd.com'],
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

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => 'SoleBd',
            'tagline' => '100% Chemical-Free Organic Food & Grocery Store',
            'logo' => '',
            'favicon' => '',
            'footer_text' => 'SoleBd delivers 100% authentic, lab-tested, chemical-free organic groceries, raw Sundarban honey, cold-pressed oils, pure deshi ghee, and farm-fresh produce across Bangladesh.',
            'contact_phone' => '+880 1700-000000',
            'contact_email' => 'support@solebd.com',
            'contact_address' => 'Level 4, Jamuna Future Park, Kuril, Dhaka 1229, Bangladesh',
            'contact_hours' => 'Saturday–Thursday, 10:00 AM – 9:00 PM',
            'contact_title' => 'Customer Support',
            'contact_intro' => 'Need help with an order, organic food specifications, or delivery? Our SoleBd customer care team is happy to assist.',
            'search_placeholder' => 'Search raw honey, mustard oil, deshi ghee, organic chia seeds, dates, nuts...',
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
            'default_meta_title' => 'SoleBd — 100% Chemical-Free Organic Food & Grocery in Bangladesh',
            'default_meta_description' => 'Shop authentic raw Sundarban honey, cold-pressed mustard oil, pure cow ghee, organic spices, nuts & fresh produce online at best prices in Bangladesh.',
            'default_meta_keywords' => 'SoleBd, Raw Honey, Mustard Oil, Cow Ghee, Ajwa Dates, Chia Seeds, Organic Food Bangladesh',
            'tracking_gtm_id' => '',
            'tracking_ga4_id' => '',
            'tracking_meta_pixel_id' => '',
            'otp_enabled' => '1',
            'header_promo_text' => 'Fresh Farm Harvest — use coupon <b class="text-amber-300">PURE10</b> for <b class="text-amber-300">10% OFF</b>',
            'header_promo_link' => '/shop?flash=1',
            'shop_subtitle' => 'Our latest 100% chemical-free organic food & fresh pantry arrivals',
            'flash_sale_ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'delivery_eta_text' => 'Estimated delivery within 1–3 business days',
            'home_categories_title' => 'Explore Organic Product Categories',
            'home_hot_deal_title' => 'Organic Special Discount Items',
            'home_featured_title' => 'Featured Organic Collection',
            'home_reviews_title' => 'Customer Feedback',
            'home_view_more_label' => 'View All Products',
            'default_cta_text' => 'Add to Cart',
            'hero_fallback_badge' => 'SoleBd Organic Store',
            'hero_fallback_title' => "100% Farm Fresh &\nChemical-Free Organic Food",
            'hero_fallback_subtitle' => 'Authentic Sundarban raw honey, cold-pressed mustard oil, pure cow ghee & organic pantry items delivered to your door.',
            'show_featured_brands' => '1',
            'home_featured_brands_title' => 'Featured Organic Brands',
            'home_featured_brands_subtitle' => 'Shop authentic organic products directly from trusted brands',
            'terms_content' => '',
            'privacy_content' => '',
            'mail_mailer' => 'log',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@solebd.com',
            'mail_from_name'    => 'SoleBd',
            'theme_primary_color'  => '#16A34A',
            'theme_dark_color'     => '#1C1917',
            'theme_surface_color'  => '#FAFAF5',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::forgetCache();
    }

    private function seedCategories(): array
    {
        $data = [
            ['Raw Honey', 'raw-honey', '🍯', '100% pure Sundarban raw honey, mustard flower honey & date molasses.', 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Pure Oils', 'pure-oils', '🛢️', 'Wood-milled mustard oil, virgin coconut oil & Bilona cow milk ghee.', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Organic Grains', 'organic-grains', '🌾', 'Aromatic Kataribogh rice, organic chia seeds, oats & unpolished pulses.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Pure Spices', 'pure-spices', '🌶️', 'High curcumin turmeric, unadulterated red chili, cumin & rock salt.', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Premium Dates', 'premium-dates', '🥜', 'Imported Madinah Ajwa dates, Medjool dates, almonds & roasted cashews.', 'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Herbal Tea', 'herbal-tea', '🍵', 'Organic cold-pressed black seed oil, Tulsi herbal tea & moringa powder.', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&h=600&q=80'],
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
                    'meta_title' => "{$name} — PureHarvest Organic",
                    'meta_description' => "Shop 100% authentic {$name} in Bangladesh with fast home delivery.",
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
                'name' => 'PureHarvest Organic',
                'slug' => 'pureharvest-organic',
                'logo' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => '100% Chemical-free organic farm direct pantry essentials and cold-pressed oils.',
                'website' => 'https://pureharvestbd.com',
                'is_featured' => true,
                'position' => 1,
            ],
            [
                'name' => 'Khaas Food',
                'slug' => 'khaas-food',
                'logo' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Purity guaranteed natural food products, Bilona cow milk ghee, and organic spices.',
                'website' => 'https://khaasfood.com',
                'is_featured' => true,
                'position' => 2,
            ],
            [
                'name' => 'BioFresh Organic',
                'slug' => 'biofresh-organic',
                'logo' => 'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Premium imported Saudi Ajwa dates, roasted almonds, cashews, and organic seeds.',
                'website' => 'https://biofreshbd.com',
                'is_featured' => true,
                'position' => 3,
            ],
            [
                'name' => 'Sundarban Honey Co.',
                'slug' => 'sundarban-honey-co',
                'logo' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Raw, unfiltered wild forest honey collected directly from Sundarbans honeycombs.',
                'website' => 'https://sundarbanhoney.com',
                'is_featured' => true,
                'position' => 4,
            ],
            [
                'name' => 'Naturals BD',
                'slug' => 'naturals-bd',
                'logo' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Pure cold-pressed black seed oil, organic chia seeds, moringa powder, and herbal teas.',
                'website' => 'https://naturalsbd.com',
                'is_featured' => true,
                'position' => 5,
            ],
            [
                'name' => 'GreenValley Produce',
                'slug' => 'greenvalley-produce',
                'logo' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Farm-fresh organic produce, Dinajpur Kataribogh rice, and chemical-free pulses.',
                'website' => 'https://greenvalley.com',
                'is_featured' => true,
                'position' => 6,
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
                'meta_title' => "Buy {$b['name']} Products Online in Bangladesh — Unilife",
                'meta_description' => "Shop 100% authentic {$b['name']} products at best prices in Bangladesh with fast home delivery.",
            ]);
            $brands[$b['name']] = $brand;
        }

        return $brands;
    }

    private function seedFeatures(): void
    {
        Feature::query()->delete();

        $features = [
            ['Express Delivery', 'Reliable 1–3 days home delivery across Bangladesh.', '🚚', 0],
            ['100% Authentic', 'Guaranteed genuine products from authorized brands.', '🛡️', 1],
            ['Easy Exchange & Returns', '7-day hassle-free size & product replacement.', '🔁', 2],
            ['Genuine Quality', '100% chemical-free, unadulterated & farm-direct organic food.', '✨', 3],
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
            'code' => 'UNI10',
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

        // 72 Products: Exactly 8 products per brand across all 9 brands.
        // Each product has 3 specific square images (1:1 ratio - w=800, h=800).
        $productsData = [
            // ================= 1. SUNDARBAN HONEY CO. =================
            [
                'name' => 'Sundarban Raw Wildflower Honey (সুন্দরবন খাঁটি মধু)',
                'slug' => 'sundarban-raw-wildflower-honey',
                'brand' => 'Sundarban Honey Co.',
                'category' => 'raw-honey',
                'regular_price' => 850,
                'sale_price' => 750,
                'unit' => 'Jar',
                'variant_type' => 'Weight',
                'options' => ['500g', '1kg'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1471943311424-646960669fbc?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Pure Mustard Flower Honey (খাঁটি সরিষা ফুল মধু)',
                'slug' => 'pure-mustard-flower-honey',
                'brand' => 'Sundarban Honey Co.',
                'category' => 'raw-honey',
                'regular_price' => 650,
                'sale_price' => 580,
                'unit' => 'Jar',
                'variant_type' => 'Weight',
                'options' => ['500g', '1kg'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1471943311424-646960669fbc?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 2. PUREHARVEST ORGANIC =================
            [
                'name' => 'Wood-Milled Cold-Pressed Mustard Oil (কাটের ঘানি খাঁটি সরিষার তেল)',
                'slug' => 'wood-milled-cold-pressed-mustard-oil',
                'brand' => 'PureHarvest Organic',
                'category' => 'pure-oils',
                'regular_price' => 420,
                'sale_price' => 380,
                'unit' => 'Bottle',
                'variant_type' => 'Volume',
                'options' => ['1 Liter', '2 Liters', '5 Liters'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'High Curcumin Organic Turmeric Powder (খাঁটি হলুদ গুঁড়া)',
                'slug' => 'high-curcumin-organic-turmeric-powder',
                'brand' => 'PureHarvest Organic',
                'category' => 'pure-spices',
                'regular_price' => 260,
                'sale_price' => 220,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['250g', '500g'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Natural Date Palm Molasses (খাঁটি ঝোলা খেজুরে গুড়)',
                'slug' => 'natural-date-palm-molasses',
                'brand' => 'PureHarvest Organic',
                'category' => 'raw-honey',
                'regular_price' => 620,
                'sale_price' => 550,
                'unit' => 'Container',
                'variant_type' => 'Weight',
                'options' => ['1kg', '2kg'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 3. KHAAS FOOD =================
            [
                'name' => 'Handcrafted Cow Milk Ghee (হাতে তৈরি খাঁটি গাওয়া ঘি)',
                'slug' => 'handcrafted-cow-milk-ghee',
                'brand' => 'Khaas Food',
                'category' => 'pure-oils',
                'regular_price' => 1650,
                'sale_price' => 1450,
                'unit' => 'Jar',
                'variant_type' => 'Weight',
                'options' => ['500g', '1kg'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Unadulterated Red Chili Powder (খাঁটি লাল মরিচ গুঁড়া)',
                'slug' => 'unadulterated-red-chili-powder',
                'brand' => 'Khaas Food',
                'category' => 'pure-spices',
                'regular_price' => 280,
                'sale_price' => 240,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['250g', '500g'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 4. BIOFRESH ORGANIC =================
            [
                'name' => 'Saudi Madinah Ajwa Dates (সৌদি আজওয়া খেজুর)',
                'slug' => 'saudi-madinah-ajwa-dates',
                'brand' => 'BioFresh Organic',
                'category' => 'premium-dates',
                'regular_price' => 1400,
                'sale_price' => 1200,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['500g', '1kg'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Premium Mixed Dry Fruits & Roasted Nuts (মিক্সড ড্রাই ফ্রুটস)',
                'slug' => 'premium-mixed-dry-fruits-nuts',
                'brand' => 'BioFresh Organic',
                'category' => 'premium-dates',
                'regular_price' => 1150,
                'sale_price' => 980,
                'unit' => 'Jar',
                'variant_type' => 'Weight',
                'options' => ['500g', '1kg'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 5. NATURALS BD =================
            [
                'name' => 'Cold-Pressed Black Seed Oil - Kalonji (খাঁটি কালোজিরা তেল)',
                'slug' => 'cold-pressed-black-seed-oil',
                'brand' => 'Naturals BD',
                'category' => 'herbal-tea',
                'regular_price' => 520,
                'sale_price' => 450,
                'unit' => 'Bottle',
                'variant_type' => 'Volume',
                'options' => ['100ml', '250ml'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Raw Organic Chia Seeds (খাঁটি অর্গানিক চিয়া সিড)',
                'slug' => 'raw-organic-chia-seeds',
                'brand' => 'Naturals BD',
                'category' => 'organic-grains',
                'regular_price' => 550,
                'sale_price' => 480,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['250g', '500g'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Organic Moringa Leaf Powder - Sajna Pata (সজনে পাতা গুঁড়া)',
                'slug' => 'organic-moringa-leaf-powder',
                'brand' => 'Naturals BD',
                'category' => 'herbal-tea',
                'regular_price' => 400,
                'sale_price' => 350,
                'unit' => 'Jar',
                'variant_type' => 'Weight',
                'options' => ['200g', '500g'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 6. GREENVALLEY PRODUCE =================
            [
                'name' => 'Dinajpur Kataribogh Aromatic Rice (সুগন্ধি কাটারীভোগ চাল)',
                'slug' => 'dinajpur-kataribogh-aromatic-rice',
                'brand' => 'GreenValley Produce',
                'category' => 'organic-grains',
                'regular_price' => 720,
                'sale_price' => 650,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['5kg', '10kg'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Farm-Fresh Organic Green Vegetables Basket (অর্গানিক সবজি বাস্কেট)',
                'slug' => 'farm-fresh-organic-green-vegetables-basket',
                'brand' => 'GreenValley Produce',
                'category' => 'organic-grains',
                'regular_price' => 600,
                'sale_price' => 490,
                'unit' => 'Pack',
                'variant_type' => 'Weight',
                'options' => ['3kg Combo Basket', '5kg Family Basket'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
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
                    'sku' => 'UNI-' . strtoupper(Str::substr(md5($pData['slug']), 0, 6)),
                    'brand' => $pData['brand'],
                    'short_description' => "Authentic 100% genuine {$pData['name']} by {$pData['brand']}. Built with premium craftsmanship and original quality guarantee.",
                    'description' => "Elevate your wardrobe with {$pData['name']} from {$pData['brand']}. Engineered with high-precision craftsmanship and durable, luxury materials. Backed by our 100% authenticity guarantee and express 1-3 days delivery across Bangladesh.",
                    'regular_price' => $pData['regular_price'],
                    'sale_price' => $pData['sale_price'],
                    'stock_quantity' => random_int(20, 65),
                    'unit' => $pData['unit'],
                    'is_published' => true,
                    'is_featured' => $pData['is_featured'],
                    'is_new_arrival' => $pData['is_new'],
                    'is_best_seller' => $pData['is_best_seller'],
                    'is_flash_sale' => false,
                    'flash_sale_position' => 0,
                    'flash_sale_progress' => 50,
                    'rating' => $ratings[array_rand($ratings)],
                    'reviews_count' => random_int(12, 48),
                    'meta_title' => "Buy {$pData['name']} Online — Unilife Bangladesh",
                    'meta_description' => "Order 100% authentic {$pData['name']} by {$pData['brand']} at best price in Bangladesh with fast home delivery and warranty.",
                ]
            );

            // Add 3 product images (all 1:1 square ratio)
            ProductImage::where('product_id', $product->id)->delete();
            $colorList = ($pData['variant_type'] === 'Color') ? $pData['options'] : ['Black', 'Brown', 'Navy'];
            foreach ($pData['images'] as $p => $imgUrl) {
                $imgColor = $colorList[$p % count($colorList)] ?? null;
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $imgUrl,
                    'alt'        => $imgColor ? "{$pData['name']} - {$imgColor}" : "{$pData['name']} View " . ($p + 1),
                    'color'      => $imgColor,
                    'is_primary' => $p === 0,
                    'position'   => $p,
                ]);
            }

            // Add Product Variants
            ProductVariant::where('product_id', $product->id)->delete();
            foreach ($pData['options'] as $vPos => $optionVal) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'type' => $pData['variant_type'],
                    'value' => $optionVal,
                    'price_delta' => 0,
                    'stock' => random_int(5, 20),
                    'position' => $vPos,
                ]);
            }

            // Add Product SKUs matrix
            \App\Models\ProductSku::where('product_id', $product->id)->delete();
            $catPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3)) ?: 'PRD';
            $productSkuBase = ! empty($product->sku) ? $product->sku : $catPrefix;

            $samplePackaging = ['Glass Jar', 'Plastic Bottle'];
            $baseReg = (float) $product->regular_price;
            $baseSale = (float) $product->sale_price;

            if ($pData['variant_type'] === 'Weight') {
                foreach ($pData['options'] as $sIdx => $sOpt) {
                    foreach ($samplePackaging as $pIdx => $pOpt) {
                        $cleanSize = preg_replace('/[^A-Za-z0-9]/', '', $sOpt);
                        $cleanPack = preg_replace('/[^A-Za-z0-9]/', '', explode(' ', $pOpt)[0]);
                        $priceAdj = ($sIdx * 150) + ($pIdx * 50);

                        $skuReg = $baseReg > 0 ? ($baseReg + $priceAdj) : null;
                        $skuSale = $baseSale > 0 ? ($baseSale + $priceAdj) : null;

                        \App\Models\ProductSku::create([
                            'product_id'       => $product->id,
                            'sku'              => "{$productSkuBase}-{$cleanSize}{$cleanPack}",
                            'attributes'       => [
                                'Weight'    => $sOpt,
                                'Packaging' => $pOpt,
                            ],
                            'price_adjustment' => $priceAdj,
                            'regular_price'    => $skuReg,
                            'sale_price'       => $skuSale,
                            'stock_quantity'   => random_int(8, 25),
                            'is_active'        => true,
                        ]);
                    }
                }
            } else {
                foreach ($pData['options'] as $idx => $opt) {
                    $cleanVal = preg_replace('/[^A-Za-z0-9]/', '', $opt);
                    $priceAdj = ($idx % 2 === 1) ? 120 : 0;

                    $skuReg = $baseReg > 0 ? ($baseReg + $priceAdj) : null;
                    $skuSale = $baseSale > 0 ? ($baseSale + $priceAdj) : null;

                    \App\Models\ProductSku::create([
                        'product_id'       => $product->id,
                        'sku'              => "{$productSkuBase}-{$cleanVal}",
                        'attributes'       => [$pData['variant_type'] => $opt],
                        'price_adjustment' => $priceAdj,
                        'regular_price'    => $skuReg,
                        'sale_price'       => $skuSale,
                        'stock_quantity'   => random_int(10, 30),
                        'is_active'        => true,
                    ]);
                }
            }

            $product->syncTotalStock();
        }

        // Set 6 flash sale products
        $flashProducts = Product::take(6)->get();
        foreach ($flashProducts as $pos => $fp) {
            $fp->update([
                'is_flash_sale' => true,
                'flash_sale_position' => $pos,
                'flash_sale_progress' => random_int(55, 88),
            ]);
        }
    }

    private function seedReviews(): void
    {
        ProductReview::query()->delete();

        $reviews = [
            ['Tanvir Ahmed', 'tanvir@example.com', 5, 'Super premium quality and 100% authentic! Delivered within 2 days in Dhaka.'],
            ['Sabrina Akter', 'sabrina@example.com', 5, 'Loved the packaging and build quality. Highly recommended store!'],
            ['Mahmudul Hasan', 'mahmud@example.com', 5, 'Great item! Leather quality and finish is top-notch.'],
            ['Farhana Yeasmin', 'farhana@example.com', 5, 'Elegant design and smooth order process. Will buy again!'],
            ['Asif Chowdhury', 'asif@example.com', 5, 'Completely genuine product with official tags. 10/10 service.'],
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
        Order::query()->delete();
        OrderItem::query()->delete();

        $products = Product::with('images', 'variants')->get();
        if ($products->isEmpty()) {
            return;
        }

        $customers = [
            ['Nusrat Jahan', '01700-111111', 'customer@solebd.com', 'House 24, Road 7, Dhanmondi', 'Dhaka', 'inside_dhaka'],
            ['Rafi Ahmed', '01822-222222', 'rafi@example.com', 'Flat 5A, GEC Circle', 'Chattogram', 'outside_dhaka'],
            ['Mim Islam', '01933-333333', 'mim@example.com', 'House 8, Uttara Sector 11', 'Dhaka', 'inside_dhaka'],
            ['Sakib Hasan', '01644-444444', 'sakib@example.com', 'Zindabazar Main Road', 'Sylhet', 'outside_dhaka'],
        ];

        $scenarios = [
            ['pending', 'bkash', 'pending', 60, ['Duplicate TrxID', 'Invalid TrxID Format']],
            ['confirmed', 'nagad', 'verified', 30, ['Multiple recent orders']],
            ['shipped', 'cod', 'verified', 0, []],
            ['delivered', 'rocket', 'verified', 10, ['First time buyer']],
        ];

        $insideFee = (float) setting('shipping_inside_dhaka', 70);
        $outsideFee = (float) setting('shipping_outside_dhaka', 130);
        $customerUser = User::where('email', 'customer@solebd.com')->first();

        foreach ($scenarios as $index => [$status, $method, $paymentStatus, $fraudScore, $fraudFlags]) {
            [$name, $phone, $email, $address, $city, $zone] = $customers[$index];
            $order = new Order([
                'user_id' => $email === 'customer@solebd.com' ? $customerUser?->id : null,
                'order_number' => 'UNI-' . now()->subDays($index)->format('ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name' => $name,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'shipping_address' => $address,
                'city' => $city,
                'postal_code' => (string) random_int(1000, 9999),
                'shipping_zone' => $zone,
                'payment_method' => $method,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'payment_sender_number' => $method === 'cod' ? null : $phone,
                'payment_txn_id' => $method === 'cod' ? null : strtoupper(Str::random(10)),
                'shipping_charge' => $zone === 'inside_dhaka' ? $insideFee : $outsideFee,
                'fraud_score' => $fraudScore,
                'fraud_flags' => $fraudFlags,
            ]);
            $order->created_at = now()->subDays($index)->subHours(random_int(1, 10));
            $order->updated_at = $order->created_at;
            $order->save();

            $subtotal = 0;
            foreach ($products->random(random_int(1, 2)) as $product) {
                $quantity = random_int(1, 2);
                $unitPrice = (float) ($product->sale_price ?: $product->regular_price);
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;
                $variant = $product->variants->first();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'image' => $product->primaryImage()?->path,
                    'variant' => $variant ? "{$variant->type}: {$variant->value}" : 'Standard',
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'tax' => 0,
                'total' => $subtotal + $order->shipping_charge,
            ]);
        }
    }

    private function seedVisitorLogs(): void
    {
        for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
            $date = \Illuminate\Support\Carbon::today()->subDays($daysAgo)->toDateString();
            $visitorCount = random_int(12, 48);

            for ($i = 0; $i < $visitorCount; $i++) {
                $ip = '103.' . random_int(10, 99) . '.' . random_int(100, 255) . '.' . random_int(1, 254);
                $isMobile = (bool) random_int(0, 1);
                $ua = $isMobile
                    ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'
                    : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

                \App\Models\VisitorLog::updateOrCreate(
                    ['ip_address' => $ip, 'visit_date' => $date],
                    ['user_agent' => $ua, 'created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)]
                );
            }
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
                'staff_name'  => $superAdmin?->name ?? 'Unilife Super Admin',
                'staff_role'  => 'admin',
                'action'      => 'System Initialization',
                'description' => 'Configured site settings, 9 core brand profiles, 6 categories, and payment gateways.',
                'ip_address'  => '127.0.0.1',
                'created_at'  => now()->subDays(3),
            ],
            [
                'user_id'     => $storeManager?->id,
                'staff_name'  => $storeManager?->name ?? 'Tanvir Alam',
                'staff_role'  => 'store_manager',
                'action'      => 'Created Product Catalog',
                'description' => "Seeded 72 authentic products across 9 brands (Nike, Adidas, Apex, Casio, Seiko, Picard, Fossil, Ray-Ban, Woodland) with square 1:1 media.",
                'ip_address'  => '103.45.12.89',
                'created_at'  => now()->subDays(2),
            ],
            [
                'user_id'     => $orderManager?->id,
                'staff_name'  => $orderManager?->name ?? 'Rafi Ahmed',
                'staff_role'  => 'order_manager',
                'action'      => 'Verified Order Payment',
                'description' => 'Verified bKash transaction for Order #UNI-260731-NYJD.',
                'ip_address'  => '103.112.44.12',
                'created_at'  => now()->subDays(1),
            ],
        ];

        foreach ($logs as $log) {
            \App\Models\StaffActivityLog::create($log);
        }
    }

    private function seedConversations(): void
    {
        $customer = User::where('role', 'customer')->first();
        if (! $customer) return;

        $conv = \App\Models\Conversation::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'customer_name'      => $customer->name,
                'customer_phone'     => $customer->phone ?? '01700-111111',
                'customer_email'     => $customer->email ?? 'customer@solebd.com',
                'status'             => 'open',
                'unread_admin_count' => 1,
                'last_message_at'    => now(),
            ]
        );

        \App\Models\ConversationMessage::updateOrCreate(
            ['conversation_id' => $conv->id, 'message' => 'Hello! 👋 I have a question about my order delivery.'],
            [
                'sender_type' => 'customer',
                'sender_id'   => $customer->id,
                'type'        => 'text',
                'is_read'     => true,
                'created_at'  => now()->subMinutes(15),
            ]
        );

        \App\Models\ConversationMessage::updateOrCreate(
            ['conversation_id' => $conv->id, 'message' => 'Welcome to SoleBd Live Support! We are happy to help.'],
            [
                'sender_type' => 'admin',
                'sender_id'   => 1,
                'type'        => 'text',
                'is_read'     => true,
                'created_at'  => now()->subMinutes(10),
            ]
        );
    }

    private function seedAttributes(): void
    {
        $presets = [
            'Weight'    => ['250g', '500g', '1kg', '2kg', '5kg'],
            'Volume'    => ['250ml', '500ml', '1 Liter', '2 Liters', '5 Liters'],
            'Packaging' => ['Glass Jar', 'Plastic Bottle', 'Pouch', 'Tin Container', 'Carton Box'],
            'Flavor'    => ['Original', 'Raw Honey', 'Black Seed Infused', 'Spicy'],
            'Size'      => ['S', 'M', 'L', 'XL', 'EU 40', 'EU 41', 'EU 42'],
            'Color'     => ['Black', 'Brown', 'Natural Gold', 'White'],
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
