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
        // Camera: admin barcode scanner. Microphone: voice notes in live chat.
        $headers->set('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=(), payment=()');

        if ($request->isSecure() && app()->environment('production')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000'); // always use HTTPS for a year
        }

        return $response;
    }
}
