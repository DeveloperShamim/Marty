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
            [
                'title' => "Premium Shoes,\nWatches & Leather Goods",
                'subtitle' => 'Explore authentic footwear, luxury watches, genuine leather belts and accessories.',
                'badge' => 'LUXURY COLLECTION 2026',
                'image' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/shop',
                'button_text' => 'Explore Collection',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => "Exclusive Offers on\nLuxury Brands",
                'subtitle' => 'Special discounts on Nike, Casio, Seiko, Apex, Fossil & genuine leather accessories.',
                'badge' => 'USE COUPON: SOLE10',
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=1200&q=80',
                'link_url' => '/shop?flash=1',
                'button_text' => 'Shop Deals',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => "Footwear & Shoes",
                'subtitle' => 'Sneakers, formal leather & boots.',
                'badge' => 'FOOTWEAR SELECTION',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/category/shoes-footwear',
                'button_text' => 'Shop Shoes',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Watches & Timepieces',
                'subtitle' => 'Luxury analog & chronographs.',
                'badge' => 'TIMEPIECES',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/category/watches-timepieces',
                'button_text' => 'Explore Watches',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Leather Belts & Wallets',
                'subtitle' => '100% Genuine leather accessories.',
                'badge' => 'LEATHER ESSENTIALS',
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/category/leather-belts-wallets',
                'button_text' => 'Shop Belts & Wallets',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Leather Bags & Travel',
                'subtitle' => 'Duffels, briefcases & backpacks.',
                'badge' => 'TRAVEL & WORK',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
                'link_url' => '/category/leather-bags',
                'button_text' => 'Shop Bags',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
