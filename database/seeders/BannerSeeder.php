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
                'title' => "Next-Gen Smartwatches &\nTech Essentials",
                'subtitle' => 'Explore the latest Apple Watch, Samsung Galaxy Watch, Amazfit, and high-performance audio gadgets with official warranty and nationwide delivery.',
                'badge' => 'PREMIUM TECH & SMARTWATCHES',
                'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=1920&q=85',
                'link_url' => '/category/watches',
                'button_text' => 'Shop Smartwatches',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => "Exclusive Footwear Drop\nTrending Sneakers & Shoes",
                'subtitle' => 'Step up your style with iconic Nike, Adidas, and handcrafted leather footwear. Authentic quality, multiple sizes, and hassle-free exchanges.',
                'badge' => 'USE CODE: MARTY10 FOR 10% OFF',
                'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1920&q=85',
                'link_url' => '/category/shoes',
                'button_text' => 'Shop Footwear Deals',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Smartwatches & Chronographs',
                'subtitle' => 'Apple Watch, Galaxy Watch & Amazfit.',
                'badge' => 'SMARTWATCHES',
                'image' => 'https://adminapi.applegadgetsbd.com/storage/media/large/Apple-Watch-Series-10-Aluminum-Silver-7765.jpg',
                'link_url' => '/category/watches',
                'button_text' => 'Explore Watches',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Sneakers & Leather Shoes',
                'subtitle' => 'Nike, Adidas & handcrafted boots.',
                'badge' => 'FOOTWEAR',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/shoes',
                'button_text' => 'View Shoes',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'TWS Earbuds & Audio',
                'subtitle' => 'AirPods Pro, Soundcore & JBL speakers.',
                'badge' => 'AUDIO GADGETS',
                'image' => 'https://adminapi.applegadgetsbd.com/storage/media/large/AirPods-Pro-(2nd-generation)-USB‐C1a-9576.png',
                'link_url' => '/category/audio-gadgets',
                'button_text' => 'Shop Audio',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Tote Bags & Accessories',
                'subtitle' => 'The Patchee luxury bags & leather wallets.',
                'badge' => 'LIFESTYLE',
                'image' => 'https://cdn.shopify.com/s/files/1/0916/6736/6162/files/Patchee-Logo-2025_4.png?v=1747217855&width=800',
                'link_url' => '/category/accessories',
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
