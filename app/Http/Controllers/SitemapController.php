<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('shop'), 'priority' => '0.9', 'changefreq' => 'daily'];

        // Categories
        foreach (Category::where('is_active', true)->get() as $category) {
            $urls[] = [
                'loc'        => route('shop.category', $category),
                'priority'   => '0.8',
                'changefreq' => 'weekly',
                'lastmod'    => $category->updated_at?->toAtomString(),
            ];
        }

        // Brands
        foreach (Brand::where('is_active', true)->get() as $brand) {
            if ($brand->slug) {
                $urls[] = [
                    'loc'        => route('shop.brand', $brand),
                    'priority'   => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod'    => $brand->updated_at?->toAtomString(),
                ];
            }
        }

        // Products
        foreach (Product::published()->get() as $product) {
            $urls[] = [
                'loc'        => route('product.show', $product),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
                'lastmod'    => $product->updated_at?->toAtomString(),
            ];
        }

        // Static & Service Pages
        $staticPages = [
            ['route' => 'contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'track',   'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'terms',   'priority' => '0.5', 'changefreq' => 'yearly'],
            ['route' => 'privacy', 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc'        => route($page['route']),
                'priority'   => $page['priority'],
                'changefreq' => $page['changefreq'],
            ];
        }

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
