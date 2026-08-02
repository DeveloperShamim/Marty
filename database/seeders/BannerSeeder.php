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
                'subtitle' => 'Explore authentic footwear, luxury watches, genuine leather belts and accessories from Nike, Adidas, Apex, Casio, Seiko, Picard, Fossil, Ray-Ban & Woodland.',
                'badge' => 'LUXURY COLLECTION 2026',
                'image' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1920&q=80',
                'link_url' => '/shop',
                'button_text' => 'Explore Collection',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => "Exclusive Offers on\nLuxury Brands",
                'subtitle' => 'Special discounts on Nike, Adidas, Casio, Seiko, Apex, Picard, Fossil, Ray-Ban & Woodland items.',
                'badge' => 'USE COUPON: UNI10',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1920&q=80',
                'link_url' => '/shop?flash=1',
                'button_text' => 'Shop Deals',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Sneakers & Running Shoes',
                'subtitle' => 'Nike & Adidas athletic sneakers & running shoes.',
                'badge' => 'SPORTS & RUNNING',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/sneakers-running-shoes',
                'button_text' => 'Shop Sneakers',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Luxury & Sport Watches',
                'subtitle' => 'Casio & Seiko solar & chronograph timepieces.',
                'badge' => 'TIMEPIECES',
                'image' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/watches-timepieces',
                'button_text' => 'Explore Watches',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Leather Belts & Wallets',
                'subtitle' => '100% Genuine leather by Apex, Picard & Fossil.',
                'badge' => 'LEATHER ESSENTIALS',
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/leather-belts-wallets',
                'button_text' => 'Shop Belts & Wallets',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Bags & Backpacks',
                'subtitle' => 'Picard, Fossil & Apex laptop duffels & backpacks.',
                'badge' => 'TRAVEL & WORK',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/bags-backpacks',
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
