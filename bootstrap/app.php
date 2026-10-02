<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'area'  => \App\Http\Middleware\EnsureStaffArea::class,
            'testing.readonly' => \App\Http\Middleware\BlockMutationsInTestingMode::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TrackUtmSource::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Guests hitting an auth-protected storefront page go to the customer login.
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            // AJAX calls (cart, POS, chat, courier scan) need JSON errors, not a redirect to the home page.
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
