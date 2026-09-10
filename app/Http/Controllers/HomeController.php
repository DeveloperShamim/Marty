<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Product;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $withImages = fn ($q) => $q->published()->with('images', 'category', 'variants', 'skus');

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('position')
            ->get();

        $featuredBrands = Brand::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $banners = fn (string $placement) => Banner::active()
            ->placement($placement)
            ->orderBy('position')
            ->orderBy('id');

        $featuredQuery = Product::query()->tap($withImages)->where('is_featured', true)->latest();
        if ((clone $featuredQuery)->count() === 0) {
            $featuredQuery = Product::query()->tap($withImages)->latest();
        }
        $totalFeatured = (clone $featuredQuery)->count();
        $trending = $featuredQuery->take(8)->get();

        $newArrivalsQuery = Product::query()->tap($withImages)->where('is_new_arrival', true)->latest();
        if ((clone $newArrivalsQuery)->count() === 0) {
            $newArrivalsQuery = Product::query()->tap($withImages)->latest();
        }

        $featuredHomeCategories = Category::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('position')
            ->with(['products' => function ($q) {
                $q->published()->with('images', 'category', 'variants', 'skus')->latest()->take(8);
            }])
            ->get();

        $featuredHomeBrands = Brand::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('position')
            ->with(['products' => function ($q) {
                $q->published()->with('images', 'category', 'brand', 'variants', 'skus')->latest()->take(8);
            }])
            ->get();

        $onSaleProducts = Product::query()
            ->tap($withImages)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'regular_price')
            ->latest()
            ->take(12)
            ->get();

        return view('storefront.home', [
            'heroBanners'            => $banners('hero')->get(),
            'features'               => Feature::where('is_active', true)->orderBy('position')->get(),
            'categories'             => $categories,
            'featuredHomeCategories' => $featuredHomeCategories,
            'featuredHomeBrands'     => $featuredHomeBrands,
            'coupons'                => Coupon::query()
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->get()
                ->filter(fn (Coupon $c) => $c->isCurrentlyActive())
                ->values()
                ->take(4),
            'flashProducts'          => Product::query()->tap($withImages)->where('is_flash_sale', true)->orderBy('flash_sale_position')->orderBy('id')->get(),
            'onSaleProducts'         => $onSaleProducts,
            'trending'               => $trending,
            'hasMoreProducts'        => $totalFeatured > 8,
            'initialLoadedCount'     => min(8, $totalFeatured),
            'totalFeaturedCount'     => $totalFeatured,
            'bestSellers'            => Product::query()->tap($withImages)->where('is_best_seller', true)->latest()->take(12)->get(),
            'newArrivals'            => $newArrivalsQuery->take(12)->get(),
            'featuredBrands'         => $featuredBrands,
        ]);
    }

    public function loadMore(Request $request)
    {
        $page = max(1, (int) $request->input('page', 2));
        $limit = max(1, (int) $request->input('limit', 8));
        $offset = ($page - 1) * $limit;

        $withImages = fn ($q) => $q->published()->with('images', 'category', 'variants', 'skus');

        $query = Product::query()->tap($withImages)->where('is_featured', true)->latest();
        if ((clone $query)->count() === 0) {
            $query = Product::query()->tap($withImages)->latest();
        }

        $total = (clone $query)->count();
        $products = $query->skip($offset)->take($limit)->get();

        $html = '';
        foreach ($products as $product) {
            $html .= view('storefront.partials.product-card', ['product' => $product])->render();
        }

        $currentLoaded = min($offset + $products->count(), $total);

        return response()->json([
            'html'         => $html,
            'has_more'     => $currentLoaded < $total,
            'loaded_count' => $currentLoaded,
            'total_count'  => $total,
        ]);
    }
}
