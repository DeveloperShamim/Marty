<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\FlashSaleController as AdminFlashSaleController;
use App\Http\Controllers\Admin\IntegrationController as AdminIntegrationController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/load-more-products', [HomeController::class, 'loadMore'])->name('home.load-more');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{category}', [ShopController::class, 'index'])->name('shop.category');
Route::get('/brand/{brand:slug}', [ShopController::class, 'brandPage'])->name('shop.brand');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::post('/product/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('product.reviews.store');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/recover/{token}', [\App\Http\Controllers\CartRecoveryController::class, 'recover'])->name('cart.recover');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/sync-contact', [CheckoutController::class, 'syncContact'])->name('checkout.sync-contact');
Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
Route::post('/checkout/coupon/remove', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
Route::get('/order/{order}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

// Order tracking (public)
Route::get('/track', [TrackOrderController::class, 'show'])->name('track');
Route::post('/track', [TrackOrderController::class, 'find'])->name('track.find');

// Legal & pages
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Customer authentication & account
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Google 1-Click Social Auth
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/forgot-password', [PasswordResetController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Email OTP verification (accessible mid-flow)
Route::get('/verify', [OtpController::class, 'show'])->name('verify');
Route::post('/verify', [OtpController::class, 'verify'])->name('verify.store');
Route::post('/verify/resend', [OtpController::class, 'resend'])->name('verify.resend');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/account/orders/{order}', [AccountController::class, 'showOrder'])->name('account.orders.show');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest (login)
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected (testing.readonly blocks save/delete/verify while TESTING_MODE=true)
    Route::middleware(['admin', 'testing.readonly'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');

        // Orders + verification workflow
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::patch('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
        Route::patch('orders/{order}/items/{item}', [AdminOrderController::class, 'updateItemVariant'])->name('orders.items.update-variant');
        Route::post('orders/{order}/verify', [AdminOrderController::class, 'verify'])->name('orders.verify');
        Route::post('orders/{order}/reject', [AdminOrderController::class, 'reject'])->name('orders.reject');
        Route::post('orders/{order}/courier/{provider}', [AdminOrderController::class, 'dispatchCourier'])->name('orders.dispatch-courier');

        // Abandoned Carts Recovery
        Route::get('abandoned-carts', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
        Route::post('abandoned-carts/{cart}/send-reminder', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'sendReminder'])->name('abandoned-carts.send-reminder');
        Route::post('abandoned-carts/{cart}/mark-recovered', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'markRecovered'])->name('abandoned-carts.mark-recovered');
        Route::delete('abandoned-carts/{cart}', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'destroy'])->name('abandoned-carts.destroy');

        // Fraud Blacklist
        Route::get('blacklist', [\App\Http\Controllers\Admin\BlacklistController::class, 'index'])->name('blacklist.index');
        Route::post('blacklist', [\App\Http\Controllers\Admin\BlacklistController::class, 'store'])->name('blacklist.store');
        Route::delete('blacklist/{blacklist}', [\App\Http\Controllers\Admin\BlacklistController::class, 'destroy'])->name('blacklist.destroy');

        // Catalog & Inventory
        Route::get('inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/update-stock', [AdminInventoryController::class, 'updateStock'])->name('inventory.update-stock');
        Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::patch('categories/{category}/toggle-featured', [AdminCategoryController::class, 'toggleFeatured'])->name('categories.toggle-featured');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::patch('brands/{brand}/toggle-featured', [AdminBrandController::class, 'toggleFeatured'])->name('brands.toggle-featured');
        Route::resource('brands', AdminBrandController::class)->except('show');
        Route::patch('banners/{banner}/toggle', [AdminBannerController::class, 'toggle'])->name('banners.toggle');
        Route::resource('banners', AdminBannerController::class)->except('show');
        Route::resource('features', AdminFeatureController::class)->except('show');
        Route::resource('coupons', AdminCouponController::class)->except('show');

        // Flash sale
        Route::get('flash-sale', [AdminFlashSaleController::class, 'index'])->name('flash-sale.index');
        Route::put('flash-sale/ends-at', [AdminFlashSaleController::class, 'updateEndsAt'])->name('flash-sale.ends-at');
        Route::put('flash-sale/reorder', [AdminFlashSaleController::class, 'reorder'])->name('flash-sale.reorder');
        Route::put('flash-sale/{product}/progress', [AdminFlashSaleController::class, 'updateProgress'])->name('flash-sale.progress');
        Route::post('flash-sale/{product}', [AdminFlashSaleController::class, 'add'])->name('flash-sale.add');
        Route::delete('flash-sale/{product}', [AdminFlashSaleController::class, 'remove'])->name('flash-sale.remove');

        // Reviews
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // People
        Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{phone}', [AdminCustomerController::class, 'show'])->name('customers.show');

        // API Integrations
        Route::get('integrations', [AdminIntegrationController::class, 'index'])->name('integrations.index');
        Route::put('integrations/{section}', [AdminIntegrationController::class, 'update'])->name('integrations.update');
        Route::post('integrations/test-mail', [AdminIntegrationController::class, 'testMail'])->name('integrations.test-mail');

        // System
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{section}', [SettingController::class, 'updateSection'])->name('settings.update-section');
        Route::post('settings/test-mail', [SettingController::class, 'testMail'])->name('settings.test-mail');
    });
});
