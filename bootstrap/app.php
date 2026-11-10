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

        $middleware->web(prepend: [
            \App\Http\Middleware\CanonicalHost::class,
        ]);
        $middleware->web(append: [
            // Signs a browser out when the account's password changed elsewhere (password change or reset)
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\TrackUtmSource::class,
        ]);

        // Only the store's own address may appear in links the app builds (Host header can't be spoofed)
        $middleware->trustHosts(at: fn () => array_values(array_filter([parse_url((string) config('app.url'), PHP_URL_HOST)])), subdomains: true);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Ad and analytics cookies are set by their own scripts in the browser, so they are never encrypted;
        // the server reads them to match purchases sent from the server (Meta Conversions API, GA4).
        $middleware->encryptCookies(except: ['_fbp', '_fbc', '_ga', '_ttp']);

        // Guests hitting an auth-protected storefront page go to the customer login.
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            // AJAX calls (cart, POS, chat, courier scan) need JSON errors, not a redirect to the home page.
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
