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
                'title' => "100% Chemical-Free &\nFarm Fresh Organic Food",
                'subtitle' => 'Shop authentic Sundarban raw honey, wood-milled cold-pressed mustard oil, pure cow ghee, organic grains, spices & imported dates delivered fast to your door.',
                'badge' => '100% PURE & FARM FRESH',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1920&q=80',
                'link_url' => '/shop',
                'button_text' => 'Shop Fresh Organic',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => "Special Offer on\nOrganic Health Bundles",
                'subtitle' => 'Get 10% OFF on your organic pantry order with coupon code PURE10.',
                'badge' => 'USE COUPON: PURE10',
                'image' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=1920&q=80',
                'link_url' => '/shop?flash=1',
                'button_text' => 'Shop Organic Deals',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Sundarban Raw Honey',
                'subtitle' => '100% Unfiltered, natural forest honey.',
                'badge' => 'PURE HONEY',
                'image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/raw-honey',
                'button_text' => 'Shop Honey',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Cold-Pressed Oils & Ghee',
                'subtitle' => 'Kather ghani mustard oil & pure Bilona cow ghee.',
                'badge' => 'PURE OILS & GHEE',
                'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/pure-oils',
                'button_text' => 'Shop Oils & Ghee',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Premium Ajwa Dates & Nuts',
                'subtitle' => 'Imported Saudi dates, almonds, cashews & walnuts.',
                'badge' => 'DATES & NUTS',
                'image' => 'https://images.unsplash.com/photo-1596560548464-f010549b84d7?auto=format&fit=crop&w=800&h=450&q=80',
                'link_url' => '/category/premium-dates',
                'button_text' => 'Shop Dates & Nuts',
                'placement' => 'hero_side',
                'style' => 'accent',
                'position' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Organic Grains & Superfoods',
                'subtitle' => 'Kataribogh rice, chia seeds & unadulterated spices.',
                'badge' => 'PANTRY ESSENTIALS',
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&h=800&q=80',
                'link_url' => '/category/organic-grains',
                'button_text' => 'Shop Pantry',
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
