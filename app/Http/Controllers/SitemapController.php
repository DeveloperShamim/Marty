<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => route('shop'), 'priority' => '0.9', 'changefreq' => 'daily'];

        foreach (Category::where('is_active', true)->get() as $category) {
            $urls[] = [
                'loc'        => route('shop.category', $category),
                'priority'   => '0.8',
                'changefreq' => 'weekly',
                'lastmod'    => $category->updated_at?->toAtomString(),
            ];
        }

        foreach (Product::published()->get() as $product) {
            $urls[] = [
                'loc'        => route('product.show', $product),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
                'lastmod'    => $product->updated_at?->toAtomString(),
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
