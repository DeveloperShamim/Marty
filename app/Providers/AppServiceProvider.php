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

        // Shared chrome data for storefront views (header nav + cart drawer + brand).
        // Memoized in request memory so queries run at most once per request without serialization issues.
        View::composer(['layouts.storefront', 'storefront.*'], function ($view) {
            static $chromeData = null;

            if ($chromeData === null) {
                $chromeData = [
                    'siteName'      => site_name(),
                    'navCategories' => Category::where('is_active', true)
                        ->withCount(['products' => fn ($q) => $q->published()])
                        ->orderByDesc('products_count')
                        ->orderBy('position')
                        ->get(),
                    'navBrands'     => Brand::where('is_active', true)
                        ->withCount(['products' => fn ($q) => $q->published()])
                        ->orderByDesc('products_count')
                        ->orderBy('position')
                        ->get(),
                    'hasFlashSale'  => Product::query()->published()->where('is_flash_sale', true)->exists(),
                ];
            }

            $cart = app(CartService::class);

            $view->with(array_merge($chromeData, [
                'cartItems'    => $cart->items(),
                'cartCount'    => $cart->count(),
                'cartSubtotal' => $cart->subtotal(),
            ]));
        });
    }
}
