<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Clear storefront navigation and home caches when catalog changes
        $clearCatalogCache = function () {
            \Illuminate\Support\Facades\Cache::forget('storefront_nav_categories');
            \Illuminate\Support\Facades\Cache::forget('storefront_nav_brands');
            \Illuminate\Support\Facades\Cache::forget('storefront_has_flash_sale');
            \Illuminate\Support\Facades\Cache::forget('storefront_home_data');
        };

        Category::saved($clearCatalogCache);
        Category::deleted($clearCatalogCache);
        Brand::saved($clearCatalogCache);
        Brand::deleted($clearCatalogCache);
        Product::saved($clearCatalogCache);
        Product::deleted($clearCatalogCache);
        \App\Models\Banner::saved($clearCatalogCache);
        \App\Models\Banner::deleted($clearCatalogCache);
        \App\Models\Coupon::saved($clearCatalogCache);
        \App\Models\Coupon::deleted($clearCatalogCache);
        \App\Models\Feature::saved($clearCatalogCache);
        \App\Models\Feature::deleted($clearCatalogCache);

        // Shared chrome data for storefront layout (single execution per page with caching)
        View::composer('layouts.storefront', function ($view) {
            $cart = app(CartService::class);

            $navCategories = \Illuminate\Support\Facades\Cache::remember('storefront_nav_categories', 3600, function () {
                return Category::where('is_active', true)
                    ->withCount(['products' => fn ($q) => $q->published()])
                    ->orderByDesc('products_count')
                    ->orderBy('position')
                    ->get();
            });

            $navBrands = \Illuminate\Support\Facades\Cache::remember('storefront_nav_brands', 3600, function () {
                return Brand::where('is_active', true)
                    ->withCount(['products' => fn ($q) => $q->published()])
                    ->orderByDesc('products_count')
                    ->orderBy('position')
                    ->get();
            });

            $hasFlashSale = \Illuminate\Support\Facades\Cache::remember('storefront_has_flash_sale', 1800, function () {
                return Product::query()->published()->where('is_flash_sale', true)->exists();
            });

            $view->with([
                'siteName'      => site_name(),
                'navCategories' => $navCategories,
                'navBrands'     => $navBrands,
                'hasFlashSale'  => $hasFlashSale,
                'cartItems'     => $cart->items(),
                'cartCount'     => $cart->count(),
                'cartSubtotal'  => $cart->subtotal(),
            ]);
        });
    }
}
