<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Basic browser security headers for every response. */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');               // don't guess file types (uploaded files)
        $headers->set('X-Frame-Options', 'SAMEORIGIN');                    // no clickjacking inside other sites' frames
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Camera: admin barcode scanner.
        $headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');

        // Content Security Policy, the parts that can't break the store's inline scripts or tracking tags:
        // no <base> tag hijacking, no Flash/plugin objects, no framing by other sites, http assets upgraded.
        if (! $headers->has('Content-Security-Policy')) {
            $headers->set('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'"
                . ($request->isSecure() ? '; upgrade-insecure-requests' : ''));
        }

        if ($request->isSecure() && app()->environment('production')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000'); // always use HTTPS for a year
        }

        return $response;
    }
}
