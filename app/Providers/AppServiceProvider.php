<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
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
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Paginator::useTailwind();

        // Rate Limiters for Authentication & Security
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower(trim((string) $request->input('email', ''))) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('admin-login', function (Request $request) {
            $key = strtolower(trim((string) $request->input('email', ''))) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Public storefront endpoints. Limits are per IP and kept generous because
        // mobile carriers often put many customers behind one shared IP.
        RateLimiter::for('track', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('coupon', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('chat-upload', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

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
                    // Flash links and the header button only while a timed sale is running
                    'hasFlashSale'  => flash_sale_running() && Product::query()->published()->where('is_flash_sale', true)->exists(),
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
