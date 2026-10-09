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
Route::get('/cart/recommendations', [CartController::class, 'recommendations'])->name('cart.recommendations');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/recover/{token}', [\App\Http\Controllers\CartRecoveryController::class, 'recover'])->name('cart.recover');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::post('/checkout/sync-contact', [CheckoutController::class, 'syncContact'])->name('checkout.sync-contact');
Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->middleware('throttle:coupon')->name('checkout.coupon.apply');
Route::post('/checkout/coupon/remove', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
Route::get('/order/{order}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

// Order tracking (public)
Route::get('/track', [TrackOrderController::class, 'show'])->name('track');
Route::post('/track', [TrackOrderController::class, 'find'])->middleware('throttle:track')->name('track.find');

// Courier status callbacks (Steadfast / Pathao / RedX). No CSRF: authenticated by the secret.
Route::post('/webhooks/courier/{provider}/{token?}', [\App\Http\Controllers\CourierWebhookController::class, 'handle'])
    ->whereIn('provider', ['steadfast', 'pathao', 'redx'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->middleware('throttle:120,1')
    ->name('webhooks.courier');

// Legal & pages
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page');

// Live Support Chat (Storefront Customer)
Route::get('/chat/conversation', [\App\Http\Controllers\ChatController::class, 'getConversation'])->name('chat.conversation');
Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->middleware('throttle:chat')->name('chat.send');
Route::post('/chat/send-attachment', [\App\Http\Controllers\ChatController::class, 'sendAttachment'])->middleware('throttle:chat-upload')->name('chat.send-attachment');
Route::post('/chat/send-voice', [\App\Http\Controllers\ChatController::class, 'sendVoiceNote'])->middleware('throttle:chat-upload')->name('chat.send-voice');
Route::get('/chat/poll', [\App\Http\Controllers\ChatController::class, 'pollMessages'])->name('chat.poll');

// SEO & Google Search Console
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/google{code}.html', function (string $code) {
    return response("google-site-verification: google{$code}.html", 200)
        ->header('Content-Type', 'text/html; charset=UTF-8');
})->where('code', '[a-zA-Z0-9_-]+');

/*
|--------------------------------------------------------------------------
| Customer authentication & account
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:login')->name('register.store');

    // Google 1-Click Social Auth
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/forgot-password', [PasswordResetController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->middleware('throttle:otp')->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:otp')->name('password.update');
});

// Email OTP verification (accessible mid-flow)
Route::get('/verify', [OtpController::class, 'show'])->name('verify');
Route::post('/verify', [OtpController::class, 'verify'])->middleware('throttle:otp')->name('verify.store');
Route::post('/verify/resend', [OtpController::class, 'resend'])->middleware('throttle:otp')->name('verify.resend');

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
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected (testing.readonly blocks save/delete/verify while TESTING_MODE=true)
    Route::middleware(['admin', 'testing.readonly'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->middleware('area:dashboard')->name('dashboard');
        Route::post('cache/clear', [DashboardController::class, 'clearCache'])->middleware('area:cache')->name('cache.clear');

        // POS (Point of Sale) & Cash Register
        Route::get('pos', [\App\Http\Controllers\Admin\PosController::class, 'index'])->middleware('area:pos')->name('pos.index');
        Route::get('pos/search', [\App\Http\Controllers\Admin\PosController::class, 'searchProducts'])->middleware('area:pos')->name('pos.search');
        Route::get('pos/scan', [\App\Http\Controllers\Admin\PosController::class, 'scanBarcode'])->middleware('area:pos')->name('pos.scan');
        Route::get('pos/customer', [\App\Http\Controllers\Admin\PosController::class, 'customerLookup'])->middleware('area:pos')->name('pos.customer');
        Route::post('pos/order', [\App\Http\Controllers\Admin\PosController::class, 'storeOrder'])->middleware('area:pos')->name('pos.order');
        Route::get('pos/receipt/{order}', [\App\Http\Controllers\Admin\PosController::class, 'receipt'])->middleware('area:pos')->name('pos.receipt');

        // Barcode Generator & Print Labels
        Route::get('barcodes', [\App\Http\Controllers\Admin\BarcodeController::class, 'index'])->middleware('area:barcodes')->name('barcodes.index');
        Route::post('barcodes/print', [\App\Http\Controllers\Admin\BarcodeController::class, 'print'])->middleware('area:barcodes')->name('barcodes.print');

        // Courier In/Out Scan Station & Returns
        Route::get('courier-scan', [\App\Http\Controllers\Admin\CourierScanController::class, 'index'])->middleware('area:courier-scan')->name('courier-scan.index');
        Route::post('courier-scan/dispatch', [\App\Http\Controllers\Admin\CourierScanController::class, 'dispatchScan'])->middleware('area:courier-scan')->name('courier-scan.dispatch');
        Route::get('courier-scan/return-lookup', [\App\Http\Controllers\Admin\CourierScanController::class, 'returnScanLookup'])->middleware('area:courier-scan')->name('courier-scan.return-lookup');
        Route::post('courier-scan/return-confirm', [\App\Http\Controllers\Admin\CourierScanController::class, 'returnScanConfirm'])->middleware('area:courier-scan')->name('courier-scan.return-confirm');
        Route::post('courier-scan/sync', [\App\Http\Controllers\Admin\CourierScanController::class, 'syncStatuses'])->middleware('area:courier-scan')->name('courier-scan.sync');
        Route::get('courier-scan/manifest', [\App\Http\Controllers\Admin\CourierScanController::class, 'printManifest'])->middleware('area:courier-scan')->name('courier-scan.manifest');

        // Sales, Profit & AOV Analytics
        Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->middleware('area:analytics')->name('analytics.index');
        Route::get('analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportCsv'])->middleware('area:analytics')->name('analytics.export');

        // Expense & Marketing Ad Spend Tracking
        Route::get('expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'index'])->middleware('area:expenses')->name('expenses.index');
        Route::post('expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'store'])->middleware('area:expenses')->name('expenses.store');
        Route::put('expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'update'])->middleware('area:expenses')->name('expenses.update');
        Route::delete('expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroy'])->middleware('area:expenses')->name('expenses.destroy');
        Route::get('expenses/export', [\App\Http\Controllers\Admin\ExpenseController::class, 'exportCsv'])->middleware('area:expenses')->name('expenses.export');

        // Order & Customer Management Routes (Order Managers & Admins)
        Route::group([], function () { // access per route: middleware('area:…'), see App\Support\StaffAccess
            Route::get('orders', [AdminOrderController::class, 'index'])->middleware('area:orders')->name('orders.index');
            Route::get('orders/feed', [AdminOrderController::class, 'feed'])->middleware('area:orders')->name('orders.feed');
            Route::get('orders/labels', [AdminOrderController::class, 'labels'])->middleware('area:orders')->name('orders.labels');
            Route::get('orders/invoices', [AdminOrderController::class, 'invoices'])->middleware('area:orders')->name('orders.invoices');
            Route::post('orders/prints', [AdminOrderController::class, 'recordPrints'])->middleware('area:orders')->name('orders.prints.record');
            Route::get('orders/prints', [AdminOrderController::class, 'printStatus'])->middleware('area:orders')->name('orders.prints.status');
            Route::post('orders/bulk', [AdminOrderController::class, 'bulk'])->middleware('area:orders')->name('orders.bulk');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->middleware('area:orders')->name('orders.show');
            Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->middleware('area:orders')->name('orders.invoice');
            Route::patch('orders/{order}', [AdminOrderController::class, 'update'])->middleware('area:orders')->name('orders.update');
            Route::patch('orders/{order}/customer', [AdminOrderController::class, 'updateCustomer'])->middleware('area:orders')->name('orders.update-customer');
            Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->middleware('area:orders')->name('orders.destroy');
            Route::patch('orders/{order}/items/{item}', [AdminOrderController::class, 'updateItemVariant'])->middleware('area:orders')->name('orders.items.update-variant');
            Route::post('orders/{order}/verify', [AdminOrderController::class, 'verify'])->middleware('area:orders')->name('orders.verify');
            Route::post('orders/{order}/switch-to-cod', [AdminOrderController::class, 'switchToCod'])->middleware('area:orders')->name('orders.switch-to-cod');
            Route::post('orders/{order}/reject', [AdminOrderController::class, 'reject'])->middleware('area:orders')->name('orders.reject');
            Route::post('orders/{order}/activities', [AdminOrderController::class, 'storeActivity'])->middleware('area:orders')->name('orders.activities.store');
            Route::post('orders/{order}/courier-history', [AdminOrderController::class, 'courierHistory'])->middleware('area:orders')->name('orders.courier-history');
            Route::post('orders/{order}/courier-status', [AdminOrderController::class, 'refreshCourierStatus'])->middleware('area:orders')->name('orders.courier-status');
            Route::post('orders/{order}/courier/{provider}', [AdminOrderController::class, 'dispatchCourier'])->middleware('area:orders')->name('orders.dispatch-courier');

            // Abandoned Carts Recovery
            Route::get('abandoned-carts', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'index'])->middleware('area:abandoned-carts')->name('abandoned-carts.index');
            Route::post('abandoned-carts/prune-recovered', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'pruneRecovered'])->middleware('area:abandoned-carts')->name('abandoned-carts.prune-recovered');
            Route::post('abandoned-carts/prune-old', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'pruneOld'])->middleware('area:abandoned-carts')->name('abandoned-carts.prune-old');
            Route::post('abandoned-carts/{cart}/send-reminder', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'sendReminder'])->middleware('area:abandoned-carts')->name('abandoned-carts.send-reminder');
            Route::post('abandoned-carts/{cart}/mark-recovered', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'markRecovered'])->middleware('area:abandoned-carts')->name('abandoned-carts.mark-recovered');
            Route::delete('abandoned-carts/{cart}', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'destroy'])->middleware('area:abandoned-carts')->name('abandoned-carts.destroy');

            // Fraud Blacklist & Customers
            Route::get('blacklist', [\App\Http\Controllers\Admin\BlacklistController::class, 'index'])->middleware('area:blacklist')->name('blacklist.index');
            Route::post('blacklist', [\App\Http\Controllers\Admin\BlacklistController::class, 'store'])->middleware('area:blacklist')->name('blacklist.store');
            Route::delete('blacklist/{blacklist}', [\App\Http\Controllers\Admin\BlacklistController::class, 'destroy'])->middleware('area:blacklist')->name('blacklist.destroy');
            Route::get('customers/export', [AdminCustomerController::class, 'export'])->middleware('area:customers')->name('customers.export');
            Route::get('customers', [AdminCustomerController::class, 'index'])->middleware('area:customers')->name('customers.index');
            Route::get('customers/{phone}', [AdminCustomerController::class, 'show'])->middleware('area:customers')->name('customers.show');
            Route::post('customers/{phone}/toggle-blacklist', [AdminCustomerController::class, 'toggleBlacklist'])->middleware('area:customers')->name('customers.toggle-blacklist');
            Route::post('customers/{phone}/segment-tag', [AdminCustomerController::class, 'updateSegmentTag'])->middleware('area:customers')->name('customers.update-segment-tag');

            // Reviews
            Route::get('reviews', [AdminReviewController::class, 'index'])->middleware('area:reviews')->name('reviews.index');
            Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->middleware('area:reviews')->name('reviews.approve');
            Route::post('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->middleware('area:reviews')->name('reviews.reject');
            Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->middleware('area:reviews')->name('reviews.destroy');


        });

        // Catalog & Inventory Routes (Inventory Managers, Store Managers & Admins)
        Route::group([], function () { // access per route: middleware('area:…'), see App\Support\StaffAccess
            Route::get('variations', [\App\Http\Controllers\Admin\VariationController::class, 'index'])->middleware('area:variations')->name('variations.index');
            Route::post('variations/types', [\App\Http\Controllers\Admin\VariationController::class, 'storeType'])->middleware('area:variations')->name('variations.types.store');
            Route::patch('variations/types/{type}', [\App\Http\Controllers\Admin\VariationController::class, 'updateType'])->middleware('area:variations')->name('variations.types.update');
            Route::delete('variations/types/{type}', [\App\Http\Controllers\Admin\VariationController::class, 'destroyType'])->middleware('area:variations')->name('variations.types.destroy');
            Route::post('variations/types/{type}/values', [\App\Http\Controllers\Admin\VariationController::class, 'storeValue'])->middleware('area:variations')->name('variations.values.store');
            Route::patch('variations/values/{value}', [\App\Http\Controllers\Admin\VariationController::class, 'updateValue'])->middleware('area:variations')->name('variations.values.update');
            Route::delete('variations/values/{value}', [\App\Http\Controllers\Admin\VariationController::class, 'destroyValue'])->middleware('area:variations')->name('variations.values.destroy');

            Route::get('inventory', [AdminInventoryController::class, 'index'])->middleware('area:inventory')->name('inventory.index');
            Route::post('inventory/update-stock', [AdminInventoryController::class, 'updateStock'])->middleware('area:inventory')->name('inventory.update-stock');
            Route::post('inventory/add-stock', [AdminInventoryController::class, 'addStock'])->middleware('area:inventory')->name('inventory.add-stock');
            Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->middleware('area:products')->name('products.images.destroy');
            Route::get('products/export', [AdminProductController::class, 'export'])->middleware('area:products')->name('products.export');
            Route::get('products/sample-csv', [AdminProductController::class, 'sampleCsv'])->middleware('area:products')->name('products.sample-csv');
            Route::post('products/import', [AdminProductController::class, 'import'])->middleware('area:products')->name('products.import');
            Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->middleware('area:products')->name('products.bulk-delete');
            Route::post('products/upload-description-media', [AdminProductController::class, 'uploadDescriptionMedia'])->middleware('area:products')->name('products.upload-description-media');
            Route::resource('products', AdminProductController::class)->except('show')->middleware('area:products');
            Route::patch('categories/{category}/toggle-featured', [AdminCategoryController::class, 'toggleFeatured'])->middleware('area:categories')->name('categories.toggle-featured');
            Route::resource('categories', AdminCategoryController::class)->except('show')->middleware('area:categories');
            Route::patch('brands/{brand}/toggle-featured', [AdminBrandController::class, 'toggleFeatured'])->middleware('area:brands')->name('brands.toggle-featured');
            Route::resource('brands', AdminBrandController::class)->except('show')->middleware('area:brands');
            Route::patch('banners/{banner}/toggle', [AdminBannerController::class, 'toggle'])->middleware('area:banners')->name('banners.toggle');
            Route::resource('banners', AdminBannerController::class)->except('show')->middleware('area:banners');
            Route::resource('features', AdminFeatureController::class)->except('show')->middleware('area:features');
            Route::resource('coupons', AdminCouponController::class)->except('show')->middleware('area:coupons');

            // Media Library
            Route::get('media', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->middleware('area:media')->name('media.index');
            Route::post('media/upload', [\App\Http\Controllers\Admin\MediaController::class, 'upload'])->middleware('area:media')->name('media.upload');
            Route::post('media/optimize', [\App\Http\Controllers\Admin\MediaController::class, 'optimizeSingle'])->middleware('area:media')->name('media.optimize');
            Route::post('media/bulk-optimize', [\App\Http\Controllers\Admin\MediaController::class, 'bulkOptimize'])->middleware('area:media')->name('media.bulk-optimize');
            Route::post('media/quality', [\App\Http\Controllers\Admin\MediaController::class, 'saveQuality'])->middleware('area:media')->name('media.quality');
            Route::post('media/metadata', [\App\Http\Controllers\Admin\MediaController::class, 'updateMetadata'])->middleware('area:media')->name('media.metadata');
            Route::delete('media/destroy', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->middleware('area:media')->name('media.destroy');
            Route::post('media/bulk-delete', [\App\Http\Controllers\Admin\MediaController::class, 'bulkDelete'])->middleware('area:media')->name('media.bulk-delete');

            // Flash sale
            Route::get('flash-sale', [AdminFlashSaleController::class, 'index'])->middleware('area:flash-sale')->name('flash-sale.index');
            Route::put('flash-sale/ends-at', [AdminFlashSaleController::class, 'updateEndsAt'])->middleware('area:flash-sale')->name('flash-sale.ends-at');
            Route::put('flash-sale/reorder', [AdminFlashSaleController::class, 'reorder'])->middleware('area:flash-sale')->name('flash-sale.reorder');
            Route::put('flash-sale/{product}/progress', [AdminFlashSaleController::class, 'updateProgress'])->middleware('area:flash-sale')->name('flash-sale.progress');
            Route::post('flash-sale/{product}', [AdminFlashSaleController::class, 'add'])->middleware('area:flash-sale')->name('flash-sale.add');
            Route::delete('flash-sale/{product}', [AdminFlashSaleController::class, 'remove'])->middleware('area:flash-sale')->name('flash-sale.remove');

            // Free delivery offer
            Route::get('free-delivery', [\App\Http\Controllers\Admin\FreeDeliveryController::class, 'index'])->middleware('area:free-delivery')->name('free-delivery.index');
            Route::put('free-delivery', [\App\Http\Controllers\Admin\FreeDeliveryController::class, 'update'])->middleware('area:free-delivery')->name('free-delivery.update');
            Route::put('free-delivery/{product}', [\App\Http\Controllers\Admin\FreeDeliveryController::class, 'toggle'])->middleware('area:free-delivery')->name('free-delivery.toggle');

            // News ticker (top headline bar)
            Route::get('news-ticker', [\App\Http\Controllers\Admin\NewsTickerController::class, 'index'])->middleware('area:news-ticker')->name('news-ticker.index');
            Route::put('news-ticker', [\App\Http\Controllers\Admin\NewsTickerController::class, 'update'])->middleware('area:news-ticker')->name('news-ticker.update');

            // Size Guide
            Route::get('size-guide', [\App\Http\Controllers\Admin\SizeGuideController::class, 'index'])->middleware('area:size-guide')->name('size-guide.index');
            Route::put('size-guide', [\App\Http\Controllers\Admin\SizeGuideController::class, 'update'])->middleware('area:size-guide')->name('size-guide.update');
        });

        // Super Admin Only Routes (Staff, Audit Logs, Settings, Integrations)
        Route::group([], function () { // access per route: middleware('area:…'), see App\Support\StaffAccess
            Route::get('staff', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->middleware('area:staff')->name('staff.index');
            Route::post('staff', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->middleware('area:staff')->name('staff.store');
            Route::patch('staff/{staff}/toggle', [\App\Http\Controllers\Admin\StaffController::class, 'toggleStatus'])->middleware('area:staff')->name('staff.toggle');
            Route::delete('staff/{staff}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->middleware('area:staff')->name('staff.destroy');
            Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->middleware('area:activity-logs')->name('activity-logs.index');
            Route::delete('activity-logs/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clearLogs'])->middleware('area:activity-logs')->name('activity-logs.clear');
            
            // API Integrations
            Route::get('integrations', [AdminIntegrationController::class, 'index'])->middleware('area:integrations')->name('integrations.index');
            Route::get('integrations/bdcourier-plan', [AdminIntegrationController::class, 'bdCourierPlan'])->middleware('area:integrations')->name('integrations.bdcourier-plan');
            Route::put('integrations/{section}', [AdminIntegrationController::class, 'update'])->middleware('area:integrations')->name('integrations.update');
            Route::post('integrations/test-mail', [AdminIntegrationController::class, 'testMail'])->middleware('area:integrations')->name('integrations.test-mail');

            // System Settings
            Route::get('settings', [SettingController::class, 'edit'])->middleware('area:settings')->name('settings.edit');
            Route::put('settings/{section}', [SettingController::class, 'updateSection'])->middleware('area:settings')->name('settings.update-section');
            Route::post('settings/test-mail', [SettingController::class, 'testMail'])->middleware('area:settings')->name('settings.test-mail');
        });

        // Admin Profile & Security Credentials
        Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::put('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
