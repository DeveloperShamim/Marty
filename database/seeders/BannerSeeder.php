<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::query()->delete();

        $banners = [
            // ================= HERO MAIN SLIDER SLIDES =================
            [
                'title'       => 'The New iPhone Era — iPhone 18 Pro Series & iPhone Duo',
                'subtitle'    => 'Pre-order the most anticipated flagship smartphones with official warranty and EMI facilities.',
                'badge'       => 'NEW LAUNCH',
                'image'       => 'uploads/banners/hero_slider_iphone_18_pro.jpg',
                'link_url'    => '/shop',
                'button_text' => 'Pre-order Now',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 0,
                'is_active'   => true,
            ],
            [
                'title'       => 'The Mini Reinvented — Mac Mini M6 & M5 Pro',
                'subtitle'    => 'Compact power redesigned for extreme performance. Up to 36 months EMI facility available.',
                'badge'       => 'PRE-ORDER ONGOING',
                'image'       => 'uploads/banners/hero_slider_mac_mini_m6.jpg',
                'link_url'    => '/shop',
                'button_text' => 'Pre-order Now',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Samsung Galaxy S26 Ultra',
                'subtitle'    => 'Next level Galaxy AI, titanium craftsmanship and professional-grade quad camera setup.',
                'badge'       => 'SPECIAL OFFER',
                'image'       => 'uploads/banners/hero_slider_samsung_s26.jpg',
                'link_url'    => '/shop',
                'button_text' => 'Explore Galaxy',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 2,
                'is_active'   => true,
            ],

            // ================= PROMO SIDE / BOTTOM CARDS =================
            [
                'title'       => 'MacBook Neo Only @ 85,999!',
                'subtitle'    => 'Unbelievable lightness, all-day battery life, and vivid Liquid Retina display.',
                'badge'       => 'HOT DEAL',
                'image'       => 'uploads/banners/hero_promo_macbook_neo.jpg',
                'link_url'    => '/shop',
                'button_text' => 'Shop Now',
                'placement'   => 'hero_side',
                'style'       => 'accent',
                'position'    => 0,
                'is_active'   => true,
            ],
            [
                'title'       => 'Pro Sound Only @ 21,999 TK — AirPods Pro (2nd Gen) USB-C',
                'subtitle'    => 'Active Noise Cancellation, Adaptive Audio, and Personalized Spatial Audio.',
                'badge'       => 'PRO SOUND',
                'image'       => 'uploads/banners/hero_promo_airpods_pro.png',
                'link_url'    => '/product/apple-airpods-pro-2nd-gen-usbc',
                'button_text' => 'Buy Now',
                'placement'   => 'hero_side',
                'style'       => 'accent',
                'position'    => 1,
                'is_active'   => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
