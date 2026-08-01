<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUtmSource
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('utm_source')) {
            $source = substr(trim($request->query('utm_source')), 0, 50);
            if ($source) {
                // Store in session for up to 30 days (or until checkout clears it)
                session(['utm_source' => $source]);
            }
        }

        return $next($request);
    }
}
