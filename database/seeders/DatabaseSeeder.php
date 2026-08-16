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
            'tagline' => 'Shoes · Watches · Leather Goods · Eyewear',
            'logo' => '',
            'favicon' => '',
            'footer_text' => 'Explore authentic footwear, luxury watches, genuine leather belts, wallets, bags, and premium eyewear delivered fast across Bangladesh.',
            'contact_phone' => '+880 1700-000000',
            'contact_email' => 'support@solebd.com',
            'contact_address' => 'Level 4, Jamuna Future Park, Kuril, Dhaka 1229, Bangladesh',
            'contact_hours' => 'Saturday–Thursday, 10:00 AM – 9:00 PM',
            'contact_title' => 'Customer Support',
            'contact_intro' => 'Need help with an order, sizing, or warranty? Our SoleBd customer care team is happy to assist.',
            'search_placeholder' => 'Search shoes, watches, leather belts, bags, sunglasses...',
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
            'default_meta_title' => 'SoleBd — Premium Shoes, Watches, Leather Goods & Eyewear in Bangladesh',
            'default_meta_description' => 'Shop 100% authentic sneakers, formal shoes, luxury watches, genuine leather belts & bags, and designer sunglasses at best prices in Bangladesh.',
            'default_meta_keywords' => 'SoleBd, Nike, Adidas, Apex, Casio, Seiko, Picard, Fossil, Ray-Ban, Woodland, Bangladesh',
            'tracking_gtm_id' => '',
            'tracking_ga4_id' => '',
            'tracking_meta_pixel_id' => '',
            'otp_enabled' => '1',
            'header_promo_text' => 'New Season Sale — use coupon <b class="text-amber-300">UNI10</b> for <b class="text-amber-300">10% OFF</b>',
            'header_promo_link' => '/shop?flash=1',
            'shop_subtitle' => 'Our latest footwear, timepieces, leather goods & eyewear arrivals',
            'flash_sale_ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'delivery_eta_text' => 'Estimated delivery within 1–3 business days',
            'home_categories_title' => 'Explore Product Categories',
            'home_hot_deal_title' => 'Products On Special Discount',
            'home_featured_title' => 'Featured Collection',
            'home_reviews_title' => 'Customer Feedback',
            'home_view_more_label' => 'View All Products',
            'default_cta_text' => 'Add to Cart',
            'hero_fallback_badge' => 'SoleBd Store',
            'hero_fallback_title' => "Premium Shoes,\nWatches & Leather",
            'hero_fallback_subtitle' => 'Authentic footwear, luxury watches, genuine leather belts & bags delivered to your door.',
            'show_featured_brands' => '1',
            'home_featured_brands_title' => 'Featured Brands',
            'home_featured_brands_subtitle' => 'Shop authentic products directly from leading brands',
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
            'theme_primary_color'  => '#E8751B',
            'theme_dark_color'     => '#1C1917',
            'theme_surface_color'  => '#FFF8F3',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::forgetCache();
    }

    private function seedCategories(): array
    {
        $data = [
            ['Sneakers & Running Shoes', 'sneakers-running-shoes', '👟', 'Authentic athletic sneakers, training & running shoes.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Formal & Dress Footwear', 'formal-dress-footwear', '👞', 'Handcrafted oxford, derby, monk strap & loafer leather shoes.', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Luxury & Sport Watches', 'watches-timepieces', '⌚', 'Luxury chronographs, solar, automatic & digital timepieces.', 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Leather Belts & Wallets', 'leather-belts-wallets', '💼', '100% genuine full-grain leather belts, bifold wallets & cardholders.', 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Bags & Backpacks', 'bags-backpacks', '🎒', 'Premium leather travel duffels, laptop briefcases & executive backpacks.', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&h=600&q=80'],
            ['Eyewear & Accessories', 'eyewear-accessories', '🕶️', 'Iconic designer sunglasses, polarized eyewear & lifestyle accessories.', 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=600&h=600&q=80'],
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
                    'meta_title' => "{$name} — Unilife Store",
                    'meta_description' => "Shop high quality {$name} in Bangladesh with fast home delivery.",
                ]
            );
        }

        return $categories;
    }

    private function seedBrands(): array
    {
        Brand::query()->delete();

        // Exactly 9 Brands with 1:1 Square Logos and 1:1 Square Banners
        $brandList = [
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'logo' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'World leader in athletic footwear, iconic sneakers, and sports innovation.',
                'website' => 'https://www.nike.com',
                'is_featured' => true,
                'position' => 1,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'logo' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Global sportswear brand famous for iconic performance running and lifestyle sneakers.',
                'website' => 'https://www.adidas.com',
                'is_featured' => true,
                'position' => 2,
            ],
            [
                'name' => 'Apex',
                'slug' => 'apex',
                'logo' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Premier footwear & genuine leather brand crafted for elegance, comfort, and durability.',
                'website' => 'https://www.apexfootwear.com',
                'is_featured' => true,
                'position' => 3,
            ],
            [
                'name' => 'Casio',
                'slug' => 'casio',
                'logo' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Legendary Japanese timepieces featuring G-Shock, Edifice chronographs, and Vintage series.',
                'website' => 'https://www.casio.com',
                'is_featured' => true,
                'position' => 4,
            ],
            [
                'name' => 'Seiko',
                'slug' => 'seiko',
                'logo' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Pioneer of quartz technology and fine automatic mechanical timepieces.',
                'website' => 'https://www.seikowatches.com',
                'is_featured' => true,
                'position' => 5,
            ],
            [
                'name' => 'Picard',
                'slug' => 'picard',
                'logo' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'German leather goods manufacturer specializing in handcrafted bags, wallets, and travel duffels.',
                'website' => 'https://www.picard-lederwaren.de',
                'is_featured' => true,
                'position' => 6,
            ],
            [
                'name' => 'Fossil',
                'slug' => 'fossil',
                'logo' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'American vintage-inspired fashion timepieces and authentic leather accessories.',
                'website' => 'https://www.fossil.com',
                'is_featured' => true,
                'position' => 7,
            ],
            [
                'name' => 'Ray-Ban',
                'slug' => 'ray-ban',
                'logo' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Iconic eyewear brand world-renowned for Aviator, Wayfarer, and Clubmaster sunglasses.',
                'website' => 'https://www.ray-ban.com',
                'is_featured' => true,
                'position' => 8,
            ],
            [
                'name' => 'Woodland',
                'slug' => 'woodland',
                'logo' => 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=400&h=400&q=80',
                'banner' => 'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
                'description' => 'Rugged outdoor footwear, trekking boots, and heavy-duty leather gear.',
                'website' => 'https://www.woodlandworldwide.com',
                'is_featured' => true,
                'position' => 9,
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
            ['Genuine Quality', '100% genuine leather, precision movement & durable soles.', '✨', 3],
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
            // ================= 1. NIKE (8 Products) =================
            [
                'name' => 'Nike Air Zoom Pegasus 40',
                'slug' => 'nike-air-zoom-pegasus-40',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 14500,
                'sale_price' => 12990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)', 'EU 44 (US 11)'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => "Nike Air Force 1 '07 Low",
                'slug' => 'nike-air-force-1-07-low',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 13900,
                'sale_price' => 11990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Air Max 270 React',
                'slug' => 'nike-air-max-270-react',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 15900,
                'sale_price' => 13800,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)', 'EU 44 (US 11)'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1543508282-6c195a940428?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Vaporfly 3 Carbon Racing',
                'slug' => 'nike-vaporfly-3-carbon-racing',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 22000,
                'sale_price' => 19500,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)', 'EU 44 (US 11)'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1512374382149-233c42b6a83b?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Court Vision Low Next Nature',
                'slug' => 'nike-court-vision-low-nn',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 9800,
                'sale_price' => 8490,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Revolution 7 Cushion Running',
                'slug' => 'nike-revolution-7-cushion-running',
                'brand' => 'Nike',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 8900,
                'sale_price' => 7490,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1579338559194-a162d19bf842?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Utility Elite Training Backpack',
                'slug' => 'nike-utility-elite-training-backpack',
                'brand' => 'Nike',
                'category' => 'bags-backpacks',
                'regular_price' => 7800,
                'sale_price' => 6490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Matte Black', 'Obsidian Navy'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1581605405669-fcdf81165afa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Nike Heritage Crossbody & Hip Pack',
                'slug' => 'nike-heritage-crossbody-pack',
                'brand' => 'Nike',
                'category' => 'eyewear-accessories',
                'regular_price' => 3200,
                'sale_price' => 2690,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Black / White Swoosh', 'Army Green'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 2. ADIDAS (8 Products) =================
            [
                'name' => 'Adidas Ultraboost Light Running Shoes',
                'slug' => 'adidas-ultraboost-light-running',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 16500,
                'sale_price' => 14200,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas NMD_R1 Primeblue Sport Sneakers',
                'slug' => 'adidas-nmd-r1-primeblue',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 14900,
                'sale_price' => 12800,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Stan Smith Sustainable Classic',
                'slug' => 'adidas-stan-smith-sustainable',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 10900,
                'sale_price' => 8990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Forum Low Retro Basketball Shoes',
                'slug' => 'adidas-forum-low-retro',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 12500,
                'sale_price' => 10490,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Terrex Swift R3 Gore-Tex Hiking',
                'slug' => 'adidas-terrex-swift-r3-hiking',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 17900,
                'sale_price' => 15500,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)', 'EU 44 (US 11)'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Gazelle Vintage Suede Sneakers',
                'slug' => 'adidas-gazelle-vintage-suede',
                'brand' => 'Adidas',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 11900,
                'sale_price' => 9990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Linear Performance Training Backpack',
                'slug' => 'adidas-linear-performance-backpack',
                'brand' => 'Adidas',
                'category' => 'bags-backpacks',
                'regular_price' => 5900,
                'sale_price' => 4890,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Black / White 3-Stripes', 'Legend Ink Navy'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1581605405669-fcdf81165afa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Adidas Trefoil Canvas Waist & Shoulder Pack',
                'slug' => 'adidas-trefoil-canvas-waistpack',
                'brand' => 'Adidas',
                'category' => 'eyewear-accessories',
                'regular_price' => 3400,
                'sale_price' => 2790,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Core Black', 'Collegiate Navy'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 3. APEX (8 Products) =================
            [
                'name' => 'Apex Genuine Leather Oxford Formal',
                'slug' => 'apex-genuine-leather-oxford-formal',
                'brand' => 'Apex',
                'category' => 'formal-dress-footwear',
                'regular_price' => 6490,
                'sale_price' => 5490,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Classic Monk Strap Leather Shoes',
                'slug' => 'apex-classic-monk-strap-leather',
                'brand' => 'Apex',
                'category' => 'formal-dress-footwear',
                'regular_price' => 6990,
                'sale_price' => 5890,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Executive Leather Derby Shoes',
                'slug' => 'apex-executive-leather-derby',
                'brand' => 'Apex',
                'category' => 'formal-dress-footwear',
                'regular_price' => 5990,
                'sale_price' => 4990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Premium Calfskin Leather Loafers',
                'slug' => 'apex-premium-calfskin-loafers',
                'brand' => 'Apex',
                'category' => 'formal-dress-footwear',
                'regular_price' => 5490,
                'sale_price' => 4590,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1560343090-f0409e92791a?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Formal Reversible Genuine Leather Belt',
                'slug' => 'apex-formal-reversible-leather-belt',
                'brand' => 'Apex',
                'category' => 'leather-belts-wallets',
                'regular_price' => 1990,
                'sale_price' => 1590,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex RFID Protection Leather Bifold Wallet',
                'slug' => 'apex-rfid-protection-leather-wallet',
                'brand' => 'Apex',
                'category' => 'leather-belts-wallets',
                'regular_price' => 1790,
                'sale_price' => 1390,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Classic Black', 'Vintage Tan Brown'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Full-Grain Leather Pocket Cardholder',
                'slug' => 'apex-full-grain-leather-cardholder',
                'brand' => 'Apex',
                'category' => 'leather-belts-wallets',
                'regular_price' => 1290,
                'sale_price' => 990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Dark Brown', 'Jet Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Apex Executive Genuine Leather Briefcase',
                'slug' => 'apex-executive-genuine-leather-briefcase',
                'brand' => 'Apex',
                'category' => 'bags-backpacks',
                'regular_price' => 8490,
                'sale_price' => 6990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Rich Cognac Brown', 'Dark Chocolate'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 4. CASIO (8 Products) =================
            [
                'name' => 'Casio Edifice Solar Powered Chronograph',
                'slug' => 'casio-edifice-solar-powered-chronograph',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 13500,
                'sale_price' => 11490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Metallic Silver / Black Dial', 'Full Black Steel'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio G-Shock Mudmaster Tough Solar',
                'slug' => 'casio-gshock-mudmaster-tough-solar',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 18900,
                'sale_price' => 16500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Tactical Black', 'Army Olive Green'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio Vintage Digital Gold Stainless Watch',
                'slug' => 'casio-vintage-digital-gold-watch',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 5500,
                'sale_price' => 4490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Gold Metallic', 'Silver Chrome'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio Enticer Steel Blue Dial Chronograph',
                'slug' => 'casio-enticer-steel-blue-dial',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 8900,
                'sale_price' => 7490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Sunray Blue Dial', 'Midnight Black Dial'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio Edifice Leather Strap Tachymeter Watch',
                'slug' => 'casio-edifice-leather-strap-tachymeter',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 11900,
                'sale_price' => 9800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown Leather / Silver Case', 'Black Leather / Gold Case'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio G-Shock Carbon Core Guard Sport',
                'slug' => 'casio-gshock-carbon-core-guard',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 14500,
                'sale_price' => 12400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Matte Black / Red Accent', 'All Black Stealth'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio Sheen Elegant Crystal Dial Watch',
                'slug' => 'casio-sheen-elegant-crystal-dial',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 9900,
                'sale_price' => 8400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Rose Gold Stainless', 'Silver / Pearl Dial'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Casio Pro Trek Triple Sensor Outdoor Watch',
                'slug' => 'casio-pro-trek-triple-sensor',
                'brand' => 'Casio',
                'category' => 'watches-timepieces',
                'regular_price' => 21000,
                'sale_price' => 18200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Titanium Silver', 'Resin Olive'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 5. SEIKO (8 Products) =================
            [
                'name' => 'Seiko 5 Sports Automatic Stainless Steel Watch',
                'slug' => 'seiko-5-sports-automatic-stainless',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 24500,
                'sale_price' => 21900,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Navy Blue Sunburst Dial', 'Matte Black Dial'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Prospex Diver Solar Chronograph',
                'slug' => 'seiko-prospex-diver-solar',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 38000,
                'sale_price' => 34500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Pepsi Bezel Blue/Red', 'Stealth Black Diver'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Presage Cocktail Time Automatic',
                'slug' => 'seiko-presage-cocktail-time-auto',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 42000,
                'sale_price' => 37900,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Ice Blue Radial Dial', 'Champagne Gold Dial'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Chronograph Blue Dial Tachymeter',
                'slug' => 'seiko-chronograph-blue-dial',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 19500,
                'sale_price' => 16800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Sunray Blue Stainless', 'Deep Black Steel'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko 5 Mechanical NATO Strap Watch',
                'slug' => 'seiko-5-mechanical-nato-strap',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 18900,
                'sale_price' => 15990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Olive Green Nylon Strap', 'Beige Khaki Strap'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Essentials Two-Tone Stainless Watch',
                'slug' => 'seiko-essentials-twotone-stainless',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 16900,
                'sale_price' => 14400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Silver & Gold Two-Tone', 'Classic All Silver'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Premier Kinetic Perpetual Calendar',
                'slug' => 'seiko-premier-kinetic-perpetual',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 48000,
                'sale_price' => 43000,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['White Roman Dial / Steel', 'Black Dial / Black Leather'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Seiko Solar Leather Dress Timepiece',
                'slug' => 'seiko-solar-leather-dress-watch',
                'brand' => 'Seiko',
                'category' => 'watches-timepieces',
                'regular_price' => 15500,
                'sale_price' => 13200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown Calfskin / White Dial', 'Black Calfskin / Silver Dial'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 6. PICARD (8 Products) =================
            [
                'name' => 'Picard Genuine Leather Laptop Briefcase',
                'slug' => 'picard-genuine-leather-laptop-briefcase',
                'brand' => 'Picard',
                'category' => 'bags-backpacks',
                'regular_price' => 11900,
                'sale_price' => 9800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Tan Brown Leather', 'Dark Chocolate Brown'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Heritage Vintage Leather Travel Duffel',
                'slug' => 'picard-heritage-vintage-leather-travel-duffel',
                'brand' => 'Picard',
                'category' => 'bags-backpacks',
                'regular_price' => 13500,
                'sale_price' => 11200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Vintage Chestnut', 'Matte Black'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Urban Executive Leather Backpack',
                'slug' => 'picard-urban-executive-leather-backpack',
                'brand' => 'Picard',
                'category' => 'bags-backpacks',
                'regular_price' => 9900,
                'sale_price' => 8400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Black Leather', 'Cognac Brown'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Full-Grain Bifold Leather Wallet',
                'slug' => 'picard-full-grain-bifold-leather-wallet',
                'brand' => 'Picard',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2800,
                'sale_price' => 2290,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Obsidian Black', 'Rustic Saddle Brown'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Ziparound Premium Leather Organizer',
                'slug' => 'picard-ziparound-leather-organizer',
                'brand' => 'Picard',
                'category' => 'leather-belts-wallets',
                'regular_price' => 3400,
                'sale_price' => 2850,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Deep Burgundy', 'Classic Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Handcrafted Leather Messenger Bag',
                'slug' => 'picard-handcrafted-leather-messenger-bag',
                'brand' => 'Picard',
                'category' => 'bags-backpacks',
                'regular_price' => 8900,
                'sale_price' => 7490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Tobacco Brown', 'Charcoal Leather'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Heritage Italian Cut Leather Belt',
                'slug' => 'picard-heritage-italian-leather-belt',
                'brand' => 'Picard',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2490,
                'sale_price' => 1990,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Picard Slim Profile Leather Passport Wallet',
                'slug' => 'picard-slim-leather-passport-wallet',
                'brand' => 'Picard',
                'category' => 'leather-belts-wallets',
                'regular_price' => 1890,
                'sale_price' => 1490,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Cognac Leather', 'Jet Black'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 7. FOSSIL (8 Products) =================
            [
                'name' => 'Fossil Genuine Leather Chronograph Watch',
                'slug' => 'fossil-genuine-leather-chronograph',
                'brand' => 'Fossil',
                'category' => 'watches-timepieces',
                'regular_price' => 15900,
                'sale_price' => 13500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Tan Brown Leather', 'Dark Brown Leather'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Minimalist Slim Leather Timepiece',
                'slug' => 'fossil-minimalist-slim-leather',
                'brand' => 'Fossil',
                'category' => 'watches-timepieces',
                'regular_price' => 11900,
                'sale_price' => 9900,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Saddle Brown Leather', 'Smoke Steel Mesh'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Derrick RFID Real Leather Bifold Wallet',
                'slug' => 'fossil-derrick-rfid-leather-wallet',
                'brand' => 'Fossil',
                'category' => 'leather-belts-wallets',
                'regular_price' => 3800,
                'sale_price' => 3150,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Dark Brown Leather', 'Jet Black'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Estate Genuine Leather Messenger Bag',
                'slug' => 'fossil-estate-genuine-leather-messenger',
                'brand' => 'Fossil',
                'category' => 'bags-backpacks',
                'regular_price' => 12500,
                'sale_price' => 10400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Cognac Vintage Leather', 'Dark Chocolate'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Defender Full-Grain Leather Backpack',
                'slug' => 'fossil-defender-fullgrain-backpack',
                'brand' => 'Fossil',
                'category' => 'bags-backpacks',
                'regular_price' => 14500,
                'sale_price' => 12200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Vintage Brown', 'Matte Black'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Reversible Cut-To-Fit Leather Belt',
                'slug' => 'fossil-reversible-cut-to-fit-belt',
                'brand' => 'Fossil',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2800,
                'sale_price' => 2250,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Haskell Leather Single Zip Briefcase',
                'slug' => 'fossil-haskell-leather-briefcase',
                'brand' => 'Fossil',
                'category' => 'bags-backpacks',
                'regular_price' => 11900,
                'sale_price' => 9900,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Cognac Leather', 'Black Leather'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Fossil Ingram Leather Front Pocket Card Case',
                'slug' => 'fossil-ingram-leather-card-case',
                'brand' => 'Fossil',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2200,
                'sale_price' => 1750,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Brown Leather', 'Black Leather'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 8. RAY-BAN (8 Products) =================
            [
                'name' => 'Ray-Ban Aviator Classic Polarized Sunglasses',
                'slug' => 'rayban-aviator-classic-polarized',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 11500,
                'sale_price' => 9800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Gold Frame / G-15 Green Lens', 'Black Frame / Dark Grey Lens'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Wayfarer Original Classic Sunglasses',
                'slug' => 'rayban-wayfarer-original-classic',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 10900,
                'sale_price' => 9200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Glossy Black Frame / Green Lens', 'Tortoise Frame / Brown Lens'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Clubmaster Classic Retro Glasses',
                'slug' => 'rayban-clubmaster-classic-retro',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 11900,
                'sale_price' => 9990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Black & Gold Frame', 'Mock Tortoise & Arista Gold'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Erika Round Oversized Sunglasses',
                'slug' => 'rayban-erika-round-oversized',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 9800,
                'sale_price' => 8400,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Rubberized Black Frame', 'Rubberized Brown Frame'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Hexagonal Flat Lenses Gold',
                'slug' => 'rayban-hexagonal-flat-lenses-gold',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 12500,
                'sale_price' => 10500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Legend Gold / G-15 Green', 'Silver / Crystal Gradient Blue'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Justin Classic Matte Black Frame',
                'slug' => 'rayban-justin-classic-matte-black',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 9500,
                'sale_price' => 7990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Matte Black / Grey Gradient', 'Matte Tortoise / Brown Gradient'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Caravan Metal Square Sunglasses',
                'slug' => 'rayban-caravan-metal-square',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 10800,
                'sale_price' => 8990,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Arista Gold Frame', 'Gunmetal Steel Frame'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Ray-Ban Round Metal Classic Gold Glasses',
                'slug' => 'rayban-round-metal-classic-gold',
                'brand' => 'Ray-Ban',
                'category' => 'eyewear-accessories',
                'regular_price' => 11200,
                'sale_price' => 9500,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Gold Frame / Green Lens', 'Black Frame / Dark Lens'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],

            // ================= 9. WOODLAND (8 Products) =================
            [
                'name' => 'Woodland Heavy-Duty Leather Trekking Boots',
                'slug' => 'woodland-heavy-duty-leather-trekking',
                'brand' => 'Woodland',
                'category' => 'formal-dress-footwear',
                'regular_price' => 11900,
                'sale_price' => 9800,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Casual Nubuck Leather Outdoor Shoes',
                'slug' => 'woodland-casual-nubuck-outdoor-shoes',
                'brand' => 'Woodland',
                'category' => 'sneakers-running-shoes',
                'regular_price' => 9500,
                'sale_price' => 7990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => true,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Tough Genuine Leather Formal Shoes',
                'slug' => 'woodland-tough-leather-formal-shoes',
                'brand' => 'Woodland',
                'category' => 'formal-dress-footwear',
                'regular_price' => 8490,
                'sale_price' => 6990,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Rugged Full-Grain Leather Belt',
                'slug' => 'woodland-rugged-fullgrain-leather-belt',
                'brand' => 'Woodland',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2490,
                'sale_price' => 1990,
                'unit' => 'Piece',
                'variant_type' => 'Size',
                'options' => ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'],
                'is_featured' => true,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Heavy Canvas & Leather Duffel Bag',
                'slug' => 'woodland-heavy-canvas-leather-duffel',
                'brand' => 'Woodland',
                'category' => 'bags-backpacks',
                'regular_price' => 9900,
                'sale_price' => 8200,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Olive Khaki', 'Rustic Brown'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Expedition Outdoor Leather Backpack',
                'slug' => 'woodland-expedition-leather-backpack',
                'brand' => 'Woodland',
                'category' => 'bags-backpacks',
                'regular_price' => 10500,
                'sale_price' => 8800,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Tan Leather', 'Camouflage Brown'],
                'is_featured' => false,
                'is_new' => false,
                'is_best_seller' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Bifold Genuine Leather Coin Wallet',
                'slug' => 'woodland-bifold-leather-coin-wallet',
                'brand' => 'Woodland',
                'category' => 'leather-belts-wallets',
                'regular_price' => 2200,
                'sale_price' => 1790,
                'unit' => 'Piece',
                'variant_type' => 'Color',
                'options' => ['Vintage Camel Brown', 'Dark Charcoal'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                ],
            ],
            [
                'name' => 'Woodland Outdoor Leather Casual Slip-On Loafers',
                'slug' => 'woodland-outdoor-leather-casual-loafers',
                'brand' => 'Woodland',
                'category' => 'formal-dress-footwear',
                'regular_price' => 7900,
                'sale_price' => 6490,
                'unit' => 'Pair',
                'variant_type' => 'Size',
                'options' => ['EU 40', 'EU 41', 'EU 42', 'EU 43'],
                'is_featured' => false,
                'is_new' => true,
                'is_best_seller' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1560343090-f0409e92791a?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=800&h=800&q=80',
                    'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=800&h=800&q=80',
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

            $sampleColors = ['Black', 'Brown'];
            if ($pData['variant_type'] === 'Color') {
                foreach ($pData['options'] as $idx => $opt) {
                    $cleanVal = preg_replace('/[^A-Za-z0-9]/', '', explode(' ', trim($opt))[0]);
                    $priceAdj = ($idx % 2 === 1) ? 100 : 0;
                    \App\Models\ProductSku::create([
                        'product_id' => $product->id,
                        'sku' => "{$productSkuBase}-{$cleanVal}",
                        'attributes' => ['Color' => $opt],
                        'price_adjustment' => $priceAdj,
                        'stock_quantity' => random_int(8, 25),
                        'is_active' => true,
                    ]);
                }
            } elseif ($pData['variant_type'] === 'Size') {
                foreach ($sampleColors as $cIdx => $c) {
                    foreach ($pData['options'] as $sIdx => $sOpt) {
                        $cleanColor = preg_replace('/[^A-Za-z0-9]/', '', $c);
                        $cleanSize = preg_replace('/[^A-Za-z0-9]/', '', $sOpt);
                        $priceAdj = ($sIdx * 50) + ($cIdx * 100);
                        \App\Models\ProductSku::create([
                            'product_id' => $product->id,
                            'sku' => "{$productSkuBase}-{$cleanColor}{$cleanSize}",
                            'attributes' => [
                                'Color' => $c,
                                'Size' => $sOpt,
                            ],
                            'price_adjustment' => $priceAdj,
                            'stock_quantity' => random_int(5, 15),
                            'is_active' => true,
                        ]);
                    }
                }
            } else {
                foreach ($pData['options'] as $idx => $opt) {
                    $cleanVal = preg_replace('/[^A-Za-z0-9]/', '', $opt);
                    $priceAdj = ($idx % 2 === 1) ? 150 : 0;
                    \App\Models\ProductSku::create([
                        'product_id' => $product->id,
                        'sku' => "{$productSkuBase}-{$cleanVal}",
                        'attributes' => [$pData['variant_type'] => $opt],
                        'price_adjustment' => $priceAdj,
                        'stock_quantity' => random_int(10, 30),
                        'is_active' => true,
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
}
