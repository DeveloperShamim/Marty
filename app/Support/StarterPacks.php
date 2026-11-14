<?php

namespace App\Support;

/**
 * Starting content for a new client's store, by type of business (php artisan store:new).
 * Each pack gives the categories, wording and style pack a fresh shop starts with, so a new client
 * never sees another shop's name, coupon or categories. Everything here is edited later in the admin.
 */
class StarterPacks
{
    /**
     * key => [label, style pack, tagline, what the shop sells (used in sentences), hero title, categories [name, icon]]
     */
    public const PACKS = [
        'fashion' => [
            'Fashion & clothing', 'minimal', 'Everyday fashion, delivered to your door',
            'clothing, shoes and accessories',
            "New season styles,\ndelivered fast",
            [['Men', '👔'], ['Women', '👗'], ['Kids', '🧒'], ['Shoes', '👟'], ['Bags', '👜'], ['Accessories', '🕶️']],
        ],
        'beauty' => [
            'Beauty & skincare', 'soft', 'Genuine beauty products you can trust',
            'skincare, makeup and personal care',
            "Glow every day with\ngenuine products",
            [['Skincare', '🧴'], ['Makeup', '💄'], ['Hair care', '💇'], ['Fragrance', '🌸'], ['Bath & body', '🛁'], ['Men\'s grooming', '🪒']],
        ],
        'electronics' => [
            'Gadgets & electronics', 'bold', 'Original gadgets with warranty',
            'gadgets and accessories',
            "Original gadgets,\nbest prices",
            [['Mobile accessories', '📱'], ['Audio', '🎧'], ['Smart watches', '⌚'], ['Computer accessories', '💻'], ['Chargers & power banks', '🔌'], ['Smart home', '🏠']],
        ],
        'grocery' => [
            'Grocery & food', 'fresh', 'Fresh groceries delivered to your home',
            'groceries and daily essentials',
            "Fresh groceries,\ndelivered today",
            [['Fruits & vegetables', '🥦'], ['Rice, dal & oil', '🍚'], ['Snacks', '🍪'], ['Drinks', '🧃'], ['Dairy & eggs', '🥚'], ['Household', '🧼']],
        ],
        'home' => [
            'Home & living', 'classic', 'Make your home feel like you',
            'home decor and kitchen goods',
            "Beautiful things\nfor your home",
            [['Home decor', '🖼️'], ['Kitchen & dining', '🍳'], ['Bedding', '🛏️'], ['Lighting', '💡'], ['Storage', '🧺'], ['Cleaning', '🧹']],
        ],
        'general' => [
            'General store', 'bold', 'Quality products, delivered to your door',
            'quality products',
            "Shop more,\npay on delivery",
            [['New arrivals', '✨'], ['Best sellers', '🔥'], ['Home', '🏠'], ['Fashion', '👕'], ['Gadgets', '📱'], ['Gifts', '🎁']],
        ],
    ];

    /** Trust strip under the hero: [title, subtitle, SVG path] */
    public const FEATURES = [
        ['Cash on Delivery', 'Pay when your parcel arrives',
            'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ['Nationwide Delivery', 'Tracked parcels to all 64 districts',
            'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
        ['Easy Returns', 'Tell us within 7 days of delivery',
            'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        ['Help When You Need It', 'Call or WhatsApp us',
            'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
    ];

    /** @return array<string, string> key => label */
    public static function options(): array
    {
        return array_map(fn ($p) => $p[0], self::PACKS);
    }

    /** "Kick Zone BD" → "KZB"; one word → its first 3 letters. Used as the order ID prefix. */
    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim(preg_replace('/[^A-Za-z0-9 ]/', ' ', $name))) ?: [];
        $words = array_values(array_filter($words));

        $prefix = count($words) > 1
            ? implode('', array_map(fn ($w) => $w[0], array_slice($words, 0, 3)))
            : substr($words[0] ?? 'ORD', 0, 3);

        return strtoupper($prefix) ?: 'ORD';
    }

    /**
     * The settings a new store starts with: the shop's own name everywhere, no other shop's logo,
     * numbers, links or email, and the pack's style.
     *
     * @return array<string, string>
     */
    public static function settings(string $type, string $name, string $phone = '', string $email = ''): array
    {
        [, $style, $tagline, $sells, $heroTitle] = self::PACKS[$type];
        [, $primary, $dark, $surface, $font, $corners] = StoreStyle::PACKS[$style];

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'example.com';

        return [
            'site_name' => $name,
            'tagline' => $tagline,
            'logo' => '',
            'favicon' => '',
            'footer_text' => "{$name} brings you {$sells}, delivered anywhere in Bangladesh with cash on delivery.",
            'contact_phone' => $phone,
            'whatsapp_number' => $phone,
            'order_hotline' => $phone,
            'contact_email' => $email,
            'contact_address' => '',
            'contact_intro' => 'Questions about a product or your order? Call or WhatsApp us and we will help.',
            'order_number_prefix' => self::initials($name),
            'messenger_url' => '', 'facebook_url' => '', 'instagram_url' => '', 'twitter_url' => '',
            'bkash_number' => '', 'nagad_number' => '', 'rocket_number' => '',
            'pay_cod_enabled' => '1', 'pay_bkash_enabled' => '0', 'pay_nagad_enabled' => '0', 'pay_rocket_enabled' => '0',
            'show_cards_in_footer' => '0',
            'size_guide_enabled' => '0',
            'default_meta_title' => "{$name} — {$tagline}",
            'default_meta_description' => "Shop {$sells} at {$name}. Cash on delivery and fast delivery across Bangladesh.",
            'default_meta_keywords' => "{$name}, {$sells}, online shop Bangladesh",
            'tracking_gtm_id' => '', 'tracking_ga4_id' => '', 'tracking_meta_pixel_id' => '',
            'header_promo_text' => "Cash on delivery all over Bangladesh\nUse code WELCOME10 for 10% off",
            'header_promo_link' => '/shop',
            'shop_subtitle' => ucfirst($sells),
            'home_categories_title' => 'Shop by Category',
            'home_categories_subtitle' => 'Find what you need, fast',
            'home_featured_title' => 'Best Sellers',
            'home_featured_brands_subtitle' => '',
            'hero_fallback_badge' => $name,
            'hero_fallback_title' => $heroTitle,
            'hero_fallback_subtitle' => ucfirst($sells) . ', delivered to your door with cash on delivery.',
            'show_featured_brands' => '0',
            'show_home_reviews' => '1',
            'home_sections' => '',
            'terms_content' => '', 'privacy_content' => '',
            'mail_from_address' => $email !== '' ? $email : "no-reply@{$host}",
            'mail_from_name' => $name,
            'theme_primary_color' => $primary,
            'theme_dark_color' => $dark,
            'theme_surface_color' => $surface,
            'theme_font' => $font,
            'theme_corners' => $corners,
        ];
    }
}
