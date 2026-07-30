<?php

namespace App\Providers;

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

        // Shared chrome data for every storefront view (header nav + cart drawer + brand).
        View::composer(['layouts.storefront', 'storefront.*'], function ($view) {
            $cart = app(CartService::class);

            $view->with([
                'siteName'      => site_name(),
                'navCategories' => Category::where('is_active', true)
                    ->withCount(['products' => fn ($q) => $q->published()])
                    ->orderByDesc('products_count')
                    ->orderBy('position')
                    ->get(),
                'hasFlashSale'  => Product::query()->published()->where('is_flash_sale', true)->exists(),
                'cartItems'     => $cart->items(),
                'cartCount'     => $cart->count(),
                'cartSubtotal'  => $cart->subtotal(),
            ]);
        });
    }
}
