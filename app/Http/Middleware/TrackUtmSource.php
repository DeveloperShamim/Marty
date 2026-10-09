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

        // Facebook ad click id: lets the server-side Purchase be matched to the ad when the _fbc cookie is missing
        $clickId = (string) $request->query('fbclid', '');
        if ($clickId !== '' && preg_match('/^[A-Za-z0-9_\-]{10,500}$/', $clickId)) {
            session(['fbclid' => $clickId]);
        }

        return $next($request);
    }
}
