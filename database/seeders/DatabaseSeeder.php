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
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@freshkart.test'],
            [
                'name' => 'Unilife Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+880 1700-000000',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@freshkart.test'],
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
            'site_name' => 'Unilife',
            'tagline' => 'Shoes · Watches · Leather Goods',
            'logo' => '',
            'favicon' => '',
            'footer_text' => 'Explore authentic footwear, luxury watches, genuine leather belts, wallets, and bags delivered fast across Bangladesh with warranty & exchange guarantee.',
            'contact_phone' => '+880 1700-000000',
            'contact_email' => 'support@unilifebd.com',
            'contact_address' => 'Level 4, Jamuna Future Park, Kuril, Dhaka 1229, Bangladesh',
            'contact_hours' => 'Saturday–Thursday, 10:00 AM – 9:00 PM',
            'contact_title' => 'Customer Support',
            'contact_intro' => 'Need help with an order, sizing, or warranty? Our Unilife customer care team is happy to assist.',
            'search_placeholder' => 'Search shoes, watches, leather belts, wallets...',
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
            'default_meta_title' => 'Unilife — Premium Shoes, Watches & Leather Goods in Bangladesh',
            'default_meta_description' => 'Shop 100% authentic sneakers, formal shoes, luxury watches, and genuine leather belts & wallets at best prices in Bangladesh.',
            'default_meta_keywords' => 'Unilife, shoes, watches, leather belts, wallets, leather bags, Nike, Casio, Seiko, Apex, Bata, Bangladesh',
            'tracking_gtm_id' => '',
            'tracking_ga4_id' => '',
            'tracking_meta_pixel_id' => '',
            'otp_enabled' => '1',
            'header_promo_text' => 'New Season Sale — use coupon <b class="text-amber-300">UNI10</b> for <b class="text-amber-300">10% OFF</b>',
            'header_promo_link' => '/shop?flash=1',
            'shop_subtitle' => 'Our latest footwear, watches & leather arrivals',
            'flash_sale_ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'delivery_eta_text' => 'Estimated delivery within 1–3 business days',
            'home_categories_title' => 'Explore Product Categories',
            'home_hot_deal_title' => 'Products On Special Discount',
            'home_featured_title' => 'Featured Collection',
            'home_reviews_title' => 'Customer Feedback',
            'home_view_more_label' => 'View All Products',
            'default_cta_text' => 'Add to Cart',
            'hero_fallback_badge' => 'Unilife Store',
            'hero_fallback_title' => "Premium Shoes,\nWatches & Leather",
            'hero_fallback_subtitle' => 'Authentic footwear, luxury watches, genuine leather belts & bags delivered to your door.',
            'terms_content' => '',
            'privacy_content' => '',
            'mail_mailer' => 'log',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'no-reply@unilifebd.com',
            'mail_from_name'    => 'Unilife',
            // Theme colours (3-colour engine)
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
            ['Shoes & Footwear', 'shoes-footwear', '👟', 'Authentic sneakers, formal leather shoes, boots & sandals.'],
            ['Watches & Timepieces', 'watches-timepieces', '⌚', 'Luxury analog watches, automatic chronographs & smartwatches.'],
            ['Leather Belts & Wallets', 'leather-belts-wallets', '💼', '100% genuine leather belts, bifold wallets & cardholders.'],
            ['Leather Bags & Backpacks', 'leather-bags', '🎒', 'Premium leather travel duffels, laptop bags & backpacks.'],
            ['Fashion Accessories', 'fashion-accessories', '🕶️', 'Luxury sunglasses, designer perfumes & lifestyle accessories.'],
        ];

        $categories = [];
        foreach ($data as $index => [$name, $slug, $icon, $description]) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'icon' => $icon,
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

        $brandList = [
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'logo' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1200&q=80',
                'description' => 'World leader in athletic footwear, apparel, and sports innovation.',
                'website' => 'https://www.nike.com',
                'is_featured' => true,
                'position' => 1,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'logo' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Global sportswear brand known for iconic performance and street culture shoes.',
                'website' => 'https://www.adidas.com',
                'is_featured' => true,
                'position' => 2,
            ],
            [
                'name' => 'Apex',
                'slug' => 'apex',
                'logo' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Premier footwear & genuine leather brand crafted for comfort and elegance.',
                'website' => 'https://www.apexfootwear.com',
                'is_featured' => true,
                'position' => 3,
            ],
            [
                'name' => 'Casio',
                'slug' => 'casio',
                'logo' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Legendary Japanese timepieces featuring G-Shock and Edifice chronographs.',
                'website' => 'https://www.casio.com',
                'is_featured' => true,
                'position' => 4,
            ],
            [
                'name' => 'Seiko',
                'slug' => 'seiko',
                'logo' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Pioneer of quartz technology and fine automatic mechanical watches.',
                'website' => 'https://www.seikowatches.com',
                'is_featured' => true,
                'position' => 5,
            ],
            [
                'name' => 'Fossil',
                'slug' => 'fossil',
                'logo' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80',
                'description' => 'American vintage-inspired fashion watches and authentic leather accessories.',
                'website' => 'https://www.fossil.com',
                'is_featured' => true,
                'position' => 6,
            ],
            [
                'name' => 'Ray-Ban',
                'slug' => 'ray-ban',
                'logo' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Iconic eyewear manufacturer famous for Aviator and Wayfarer sunglasses.',
                'website' => 'https://www.ray-ban.com',
                'is_featured' => true,
                'position' => 7,
            ],
            [
                'name' => 'Clarks',
                'slug' => 'clarks',
                'logo' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=1200&q=80',
                'description' => 'British shoemaker crafting premium leather boots, dress shoes, and desert boots.',
                'website' => 'https://www.clarks.com',
                'is_featured' => false,
                'position' => 8,
            ],
            [
                'name' => 'Woodland',
                'slug' => 'woodland',
                'logo' => 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1460353581641-37babbab0fa6?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Rugged outdoor footwear, hiking boots, and heavy-duty leather gear.',
                'website' => 'https://www.woodlandworldwide.com',
                'is_featured' => false,
                'position' => 9,
            ],
            [
                'name' => 'Curren',
                'slug' => 'curren',
                'logo' => 'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Affordable luxury chronograph watches with sleek metallic and leather designs.',
                'website' => 'https://www.currenwatches.com',
                'is_featured' => false,
                'position' => 10,
            ],
            [
                'name' => 'Naviforce',
                'slug' => 'naviforce',
                'logo' => 'https://images.unsplash.com/photo-1526045612212-70caf35c14df?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Military and sports inspired dual-display watches built for action.',
                'website' => 'https://www.naviforce.com',
                'is_featured' => false,
                'position' => 11,
            ],
            [
                'name' => 'Wildhorn',
                'slug' => 'wildhorn',
                'logo' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Handcrafted full-grain leather wallets, travel duffels, and belts.',
                'website' => 'https://www.wildhorn.in',
                'is_featured' => false,
                'position' => 12,
            ],
            [
                'name' => 'Bata',
                'slug' => 'bata',
                'logo' => 'https://images.unsplash.com/photo-1562183241-b937e95585b6?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Trusted global footwear manufacturer providing everyday comfort shoes & accessories.',
                'website' => 'https://www.bata.com.bd',
                'is_featured' => false,
                'position' => 13,
            ],
            [
                'name' => 'Picard',
                'slug' => 'picard',
                'logo' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=1200&q=80',
                'description' => 'German leather goods manufacturer specializing in luxury bags, wallets & cardholders.',
                'website' => 'https://www.picard-lederwaren.de',
                'is_featured' => true,
                'position' => 14,
            ],
            [
                'name' => 'Hugo Boss',
                'slug' => 'hugo-boss',
                'logo' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=1200&q=80',
                'description' => 'German luxury fashion house offering premium tailoring, fragrances, and accessories.',
                'website' => 'https://www.hugoboss.com',
                'is_featured' => false,
                'position' => 15,
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
                'meta_title' => "Buy {$b['name']} Products Online in Bangladesh",
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
            ['Genuine Quality', '100% genuine leather, premium Movement & durable soles.', '✨', 3],
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

        // Sample Unsplash High Quality Product Images
        $sampleImages = [
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80',
        ];

        $catalog = [
            'shoes-footwear' => [
                ['Nike Air Zoom Pegasus 40', 'nike-air-zoom-pegasus-40', 'Nike', 12500, 10990, 'Pair', 'Size', ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)', 'EU 44 (US 11)'], true, true, true],
                ['Adidas Ultraboost Light Running', 'adidas-ultraboost-light', 'Adidas', 14900, 12990, 'Pair', 'Size', ['EU 40 (US 7)', 'EU 41 (US 8)', 'EU 42 (US 9)', 'EU 43 (US 10)'], true, false, true],
                ['Apex Genuine Leather Oxford', 'apex-genuine-leather-oxford', 'Apex', 5490, 4890, 'Pair', 'Size', ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43'], true, true, false],
                ['Clarks Leather Derby Shoes', 'clarks-leather-derby-shoes', 'Clarks', 9900, 8490, 'Pair', 'Size', ['EU 40', 'EU 41', 'EU 42', 'EU 43'], true, true, true],
                ['Woodland Leather Trekking Boots', 'woodland-leather-trekking-boots', 'Woodland', 8990, 7490, 'Pair', 'Size', ['EU 40', 'EU 41', 'EU 42', 'EU 43'], false, true, true],
            ],
            'watches-timepieces' => [
                ['Curren Luxury Chronograph Watch', 'curren-luxury-chronograph-watch', 'Curren', 3490, 2890, 'Piece', 'Color', ['Black Dial / Steel Strap', 'Silver / Blue Dial', 'Gold / Black Leather Strap'], true, true, true],
                ['Casio Edifice Solar Chronograph', 'casio-edifice-solar-chronograph', 'Casio', 11900, 9990, 'Piece', 'Color', ['Black Steel', 'Silver Metallic'], true, false, true],
                ['Seiko 5 Automatic Stainless Watch', 'seiko-5-automatic-stainless-watch', 'Seiko', 16500, 14200, 'Piece', 'Color', ['Dark Blue Dial', 'Black Dial'], true, true, false],
                ['Naviforce Dual Time Leather Watch', 'naviforce-dual-time-leather-watch', 'Naviforce', 2990, 2490, 'Piece', 'Color', ['Brown Leather / Black Dial', 'Black Leather / Gold Dial'], false, true, true],
                ['Fossil Genuine Leather Chrono', 'fossil-genuine-leather-chrono', 'Fossil', 12900, 10800, 'Piece', 'Color', ['Tan Brown Leather', 'Dark Brown Leather'], false, false, true],
            ],
            'leather-belts-wallets' => [
                ['Apex Genuine Leather Formal Belt', 'apex-genuine-leather-formal-belt', 'Apex', 1890, 1490, 'Piece', 'Size', ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'], true, true, true],
                ['Wildhorn Full-Grain Bifold Wallet', 'wildhorn-full-grain-bifold-wallet', 'Wildhorn', 1490, 1190, 'Piece', 'Color', ['Classic Black', 'Vintage Brown', 'Dark Tan'], true, false, true],
                ['Bata Reversible Leather Dress Belt', 'bata-reversible-leather-dress-belt', 'Bata', 1690, 1390, 'Piece', 'Size', ['Waist 32-34', 'Waist 36-38', 'Waist 40-42'], false, true, false],
                ['Picard Full-Grain Leather Cardholder', 'picard-full-grain-leather-cardholder', 'Picard', 2290, 1890, 'Piece', 'Color', ['Black', 'Dark Brown'], true, false, true],
                ['Apex RFID Blocking Leather Wallet', 'apex-rfid-blocking-leather-wallet', 'Apex', 1790, 1390, 'Piece', 'Color', ['Black Leather', 'Chocolate Brown'], false, true, true],
            ],
            'leather-bags' => [
                ['Picard Genuine Leather Laptop Briefcase', 'picard-genuine-leather-laptop-briefcase', 'Picard', 9900, 8490, 'Piece', 'Color', ['Tan Brown', 'Dark Chocolate'], true, true, true],
                ['Wildhorn Vintage Leather Travel Duffel', 'wildhorn-vintage-leather-travel-duffel', 'Wildhorn', 7990, 6590, 'Piece', 'Color', ['Vintage Brown', 'Matte Black'], true, false, true],
                ['Apex Executive Leather Backpack', 'apex-executive-leather-backpack', 'Apex', 6890, 5790, 'Piece', 'Color', ['Black Leather', 'Cognac Brown'], false, true, true],
            ],
            'fashion-accessories' => [
                ['Ray-Ban Aviator Polarized Sunglasses', 'rayban-aviator-polarized-sunglasses', 'Ray-Ban', 9900, 8500, 'Piece', 'Color', ['Gold Frame / Green Lens', 'Black Frame / Dark Lens'], true, true, true],
                ['Hugo Boss Boss Bottled EDP 100ml', 'hugo-boss-bottled-edp-100ml', 'Hugo Boss', 8500, 7200, 'Piece', 'Volume', ['100 ml'], false, true, true],
            ],
        ];

        $ratings = [4.6, 4.7, 4.8, 4.9];
        $imgIndex = 0;

        foreach ($catalog as $categorySlug => $items) {
            $category = $categories[$categorySlug];

            foreach ($items as $item) {
                [$name, $slug, $brandName, $regularPrice, $salePrice, $unit, $variantType, $options, $featured, $newArrival, $bestSeller] = $item;
                $brandObj = $brands[$brandName] ?? null;

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'brand_id'    => $brandObj?->id,
                        'name' => $name,
                        'sku' => 'UNI-' . strtoupper(Str::substr(md5($slug), 0, 6)),
                        'brand' => $brandName,
                        'short_description' => "Authentic {$name} by {$brandName}. Premium materials, ergonomic build, and elegant craftsmanship.",
                        'description' => "Elevate your lifestyle with {$name} from {$brandName}. Designed with high-precision craftsmanship and durable materials. Backed by our 100% authenticity guarantee and fast delivery across Bangladesh.",
                        'regular_price' => $regularPrice,
                        'sale_price' => $salePrice,
                        'stock_quantity' => random_int(15, 60),
                        'unit' => $unit,
                        'is_published' => true,
                        'is_featured' => $featured,
                        'is_new_arrival' => $newArrival,
                        'is_best_seller' => $bestSeller,
                        'is_flash_sale' => false,
                        'flash_sale_position' => 0,
                        'flash_sale_progress' => 50,
                        'rating' => $ratings[array_rand($ratings)],
                        'reviews_count' => random_int(10, 42),
                        'meta_title' => "Buy {$name} Online — Unilife Bangladesh",
                        'meta_description' => "Order {$name} by {$brandName} at best price in Bangladesh with home delivery and warranty.",
                    ]
                );

                // Add 3 product images
                ProductImage::where('product_id', $product->id)->delete();
                for ($p = 0; $p < 3; $p++) {
                    $imgUrl = $sampleImages[($imgIndex + $p) % count($sampleImages)];
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $imgUrl,
                        'alt' => "{$name} view " . ($p + 1),
                        'is_primary' => $p === 0,
                        'position' => $p,
                    ]);
                }
                $imgIndex++;

                // Add variants
                ProductVariant::where('product_id', $product->id)->delete();
                foreach ($options as $vPos => $optionVal) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'type' => $variantType,
                        'value' => $optionVal,
                        'price_delta' => 0,
                        'stock' => random_int(5, 20),
                        'position' => $vPos,
                    ]);
                }

                // Add Product SKUs for variation matrix & stock management
                \App\Models\ProductSku::where('product_id', $product->id)->delete();
                $catPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3)) ?: 'PRD';
                $productSkuBase = ! empty($product->sku) ? $product->sku : $catPrefix;

                $sampleColors = ['Black', 'Brown'];
                if ($variantType === 'Color') {
                    foreach ($options as $opt) {
                        $cleanVal = preg_replace('/[^A-Za-z0-9]/', '', explode(' ', trim($opt))[0]);
                        \App\Models\ProductSku::create([
                            'product_id' => $product->id,
                            'sku' => "{$productSkuBase}-{$cleanVal}",
                            'attributes' => ['Color' => $opt],
                            'price_adjustment' => 0,
                            'stock_quantity' => random_int(8, 25),
                            'is_active' => true,
                        ]);
                    }
                } elseif ($variantType === 'Size') {
                    foreach ($sampleColors as $c) {
                        foreach ($options as $sOpt) {
                            $cleanColor = preg_replace('/[^A-Za-z0-9]/', '', $c);
                            $cleanSize = preg_replace('/[^A-Za-z0-9]/', '', $sOpt);
                            \App\Models\ProductSku::create([
                                'product_id' => $product->id,
                                'sku' => "{$productSkuBase}-{$cleanColor}{$cleanSize}",
                                'attributes' => [
                                    'Color' => $c,
                                    'Size' => $sOpt,
                                ],
                                'price_adjustment' => 0,
                                'stock_quantity' => random_int(5, 15),
                                'is_active' => true,
                            ]);
                        }
                    }
                } else {
                    foreach ($options as $opt) {
                        $cleanVal = preg_replace('/[^A-Za-z0-9]/', '', $opt);
                        \App\Models\ProductSku::create([
                            'product_id' => $product->id,
                            'sku' => "{$productSkuBase}-{$cleanVal}",
                            'attributes' => [$variantType => $opt],
                            'price_adjustment' => 0,
                            'stock_quantity' => random_int(10, 30),
                            'is_active' => true,
                        ]);
                    }
                }

                $product->syncTotalStock();
            }
        }

        // Set 4 flash sale products
        $flashProducts = Product::take(4)->get();
        foreach ($flashProducts as $pos => $fp) {
            $fp->update([
                'is_flash_sale' => true,
                'flash_sale_position' => $pos,
                'flash_sale_progress' => random_int(45, 85),
            ]);
        }
    }

    private function seedReviews(): void
    {
        ProductReview::query()->delete();

        $reviews = [
            ['Tanvir Ahmed', 'tanvir@example.com', 5, 'Super premium quality and 100% authentic! Delivered within 2 days in Dhaka.'],
            ['Sabrina Akter', 'sabrina@example.com', 5, 'Loved the packaging and build quality. Highly recommended store!'],
            ['Mahmudul Hasan', 'mahmud@example.com', 4, 'Great item! Leather quality and finish is top-notch.'],
            ['Farhana Yeasmin', 'farhana@example.com', 5, 'Elegant design and smooth order process. Will buy again!'],
        ];

        $products = Product::take(6)->get();
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
            ['Nusrat Jahan', '01700-111111', 'customer@freshkart.test', 'House 24, Road 7, Dhanmondi', 'Dhaka', 'inside_dhaka'],
            ['Rafi Ahmed', '01822-222222', 'rafi@example.com', 'Flat 5A, GEC Circle', 'Chattogram', 'outside_dhaka'],
            ['Mim Islam', '01933-333333', 'mim@example.com', 'House 8, Uttara Sector 11', 'Dhaka', 'inside_dhaka'],
            ['Sakib Hasan', '01644-444444', 'sakib@example.com', 'Zindabazar Main Road', 'Sylhet', 'outside_dhaka'],
        ];

        $scenarios = [
            ['pending', 'bkash', 'pending'],
            ['confirmed', 'nagad', 'verified'],
            ['shipped', 'cod', 'verified'],
            ['delivered', 'rocket', 'verified'],
        ];

        $insideFee = (float) setting('shipping_inside_dhaka', 70);
        $outsideFee = (float) setting('shipping_outside_dhaka', 130);
        $customerUser = User::where('email', 'customer@freshkart.test')->first();

        foreach ($scenarios as $index => [$status, $method, $paymentStatus]) {
            [$name, $phone, $email, $address, $city, $zone] = $customers[$index];
            $order = new Order([
                'user_id' => $email === 'customer@freshkart.test' ? $customerUser?->id : null,
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
}
