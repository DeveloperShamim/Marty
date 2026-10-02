<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::query()->delete();

        // Text banners (no image): the storefront draws them in the brand colours.
        // Upload designed banner images later in Admin → Storefront → Hero Banners.
        $banners = [
            // ================= HERO MAIN SLIDER =================
            [
                'title'       => 'Genuine Leather, Made to Last',
                'subtitle'    => 'Wallets, belts, watches, shoes and desk mats. Cash on delivery all over Bangladesh.',
                'badge'       => 'VANT BANGLADESH',
                'image'       => null,
                'link_url'    => '/shop',
                'button_text' => 'Shop Now',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 0,
                'is_active'   => true,
            ],
            [
                'title'       => 'Leather Wallets from ৳650',
                'subtitle'    => 'Bifold, trifold, long zip wallets and slim card holders in brown, black and tan.',
                'badge'       => 'BEST SELLERS',
                'image'       => null,
                'link_url'    => '/category/wallets',
                'button_text' => 'Shop Wallets',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 1,
                'is_active'   => true,
            ],
            [
                'title'       => 'Pay with bKash, Get Free Delivery',
                'subtitle'    => 'Pay online at checkout and we deliver for free. Use code VANT10 for 10% off.',
                'badge'       => 'OFFER',
                'image'       => null,
                'link_url'    => '/shop',
                'button_text' => 'Start Shopping',
                'placement'   => 'hero',
                'style'       => 'brand',
                'position'    => 2,
                'is_active'   => true,
            ],

            // ================= SIDE PROMO CARDS =================
            [
                'title'       => 'Leather Belts from ৳1,290',
                'subtitle'    => 'Formal and casual, sizes 32 to 40.',
                'badge'       => 'BELTS',
                'image'       => null,
                'link_url'    => '/category/belts',
                'button_text' => 'Shop Belts',
                'placement'   => 'hero_side',
                'style'       => 'accent',
                'position'    => 0,
                'is_active'   => true,
            ],
            [
                'title'       => 'Leather Desk Mats & Mouse Pads',
                'subtitle'    => 'A premium leather finish for your workspace.',
                'badge'       => 'NEW',
                'image'       => null,
                'link_url'    => '/category/mouse-pads',
                'button_text' => 'Shop Desk Mats',
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
