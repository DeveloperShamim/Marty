<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One address for the whole store: visitors and search engines that open the other half of the
 * www / no-www pair, or plain http on an https store, get a permanent redirect to APP_URL's address.
 * Two addresses serving the same pages is what Search Console reports as
 * "Duplicate, Google chose different canonical than user".
 */
class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $main = parse_url((string) config('app.url'));
        $mainHost = strtolower($main['host'] ?? '');
        // Only a real domain (not localhost or an IP address) has a www twin worth redirecting
        if (! str_contains($mainHost, '.') || filter_var($mainHost, FILTER_VALIDATE_IP)) {
            return $next($request);
        }

        $host = strtolower($request->getHost());
        $twin = str_starts_with($mainHost, 'www.') ? substr($mainHost, 4) : 'www.' . $mainHost;
        $wrongHost = $host === $twin;

        // Behind a proxy (Cloudflare and the like) the visitor's scheme arrives in X-Forwarded-Proto
        $secure = $request->isSecure() || strtolower((string) $request->header('X-Forwarded-Proto')) === 'https';
        $wrongScheme = ($main['scheme'] ?? '') === 'https' && ! $secure && $host === $mainHost;

        if ($wrongHost || $wrongScheme) {
            return redirect()->away(($main['scheme'] ?? 'https') . '://' . $mainHost . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
