<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\DashboardController;
use App\Http\Controllers\Api\Mobile\OrderController;
use Illuminate\Support\Facades\Route;

/*
| Admin mobile app API (Flutter). Staff sign in with their admin-panel email/password
| and send the returned token as "Authorization: Bearer <token>".
*/
Route::prefix('mobile/v1')->name('api.mobile.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware('mobile.token')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('mobile.token:orders')->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('couriers', [OrderController::class, 'couriers'])->name('couriers');
        Route::get('scan', [OrderController::class, 'scan'])->name('scan');

        Route::middleware('testing.readonly')->group(function () {
            Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::post('orders/{order}/verify', [OrderController::class, 'verify'])->name('orders.verify');
            Route::post('orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
            Route::post('orders/{order}/courier/{provider}', [OrderController::class, 'dispatchCourier'])->name('orders.dispatch-courier');
            Route::post('orders/{order}/tracking', [OrderController::class, 'attachTracking'])->name('orders.tracking');
        });
    });
});
