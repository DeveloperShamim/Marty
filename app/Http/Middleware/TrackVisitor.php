<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     * Passes the request immediately so storefront has 0ms added delay.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * Runs completely in the background without affecting storefront page load time.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only track successful GET requests for real web pages
        if (
            !$request->isMethod('GET') ||
            $request->ajax() ||
            $request->wantsJson() ||
            $response->getStatusCode() >= 400 ||
            $request->is('admin*', 'api*', 'livewire*', 'broadcasting*', 'up', '_debugbar*', 'sanctum*')
        ) {
            return;
        }

        $userAgent = (string) ($request->userAgent() ?? '');

        // Ignore common search bots and scrapers to save DB resources
        if ($this->isBot($userAgent)) {
            return;
        }

        try {
            $ip = (string) ($request->ip() ?? '127.0.0.1');
            $date = today()->toDateString();
            $path = '/' . ltrim((string) $request->path(), '/');
            if ($path === '//') $path = '/';
            $path = substr($path, 0, 500);

            // Clean Referer
            $referer = null;
            $rawReferer = $request->header('referer');
            if ($rawReferer) {
                $host = parse_url($rawReferer, PHP_URL_HOST);
                if ($host && !str_contains($host, $request->getHost())) {
                    $referer = substr($host, 0, 500);
                }
            }

            // Clean UTM Source
            $utmSource = $request->query('utm_source') ? substr((string) $request->query('utm_source'), 0, 100) : null;
            $utmMedium = $request->query('utm_medium') ? substr((string) $request->query('utm_medium'), 0, 100) : null;
            $utmCampaign = $request->query('utm_campaign') ? substr((string) $request->query('utm_campaign'), 0, 100) : null;

            // Device Type & Browser
            $deviceType = $this->detectDevice($userAgent);
            $browser = $this->detectBrowser($userAgent);
            $userId = auth()->id();

            // Single fast upsert query (1 IP per day per unique key)
            DB::table('visitor_logs')->upsert(
                [
                    'ip_address'   => $ip,
                    'visit_date'   => $date,
                    'user_agent'   => substr($userAgent, 0, 255),
                    'page_url'     => $path,
                    'referer'      => $referer,
                    'utm_source'   => $utmSource,
                    'utm_medium'   => $utmMedium,
                    'utm_campaign' => $utmCampaign,
                    'device_type'  => $deviceType,
                    'browser'      => $browser,
                    'user_id'      => $userId,
                    'is_bot'       => 0,
                    'hits'         => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                ['ip_address', 'visit_date'],
                [
                    'updated_at'  => now(),
                    'page_url'    => $path,
                    'user_agent'  => substr($userAgent, 0, 255),
                    'device_type' => $deviceType,
                    'browser'     => $browser,
                    'hits'        => DB::raw('visitor_logs.hits + 1'),
                ]
            );
        } catch (\Throwable $e) {
            // Silently ignore to never break the application
        }
    }

    private function isBot(string $userAgent): bool
    {
        if ($userAgent === '') return false;
        $bots = [
            'googlebot', 'bingbot', 'yandex', 'baiduspider', 'ahrefs',
            'semrush', 'petalbot', 'slurp', 'duckduckbot', 'curl', 'python',
            'postman', 'headlesschrome', 'lighthouse', 'bytespider'
        ];
        $lower = strtolower($userAgent);
        foreach ($bots as $bot) {
            if (str_contains($lower, $bot)) {
                return true;
            }
        }
        return false;
    }

    private function detectDevice(string $userAgent): string
    {
        $lower = strtolower($userAgent);
        if (str_contains($lower, 'ipad') || str_contains($lower, 'tablet')) {
            return 'tablet';
        }
        if (str_contains($lower, 'mobile') || str_contains($lower, 'android') || str_contains($lower, 'iphone')) {
            return 'mobile';
        }
        return 'desktop';
    }

    private function detectBrowser(string $userAgent): string
    {
        $lower = strtolower($userAgent);
        if (str_contains($lower, 'edg')) return 'Edge';
        if (str_contains($lower, 'opera') || str_contains($lower, 'opr')) return 'Opera';
        if (str_contains($lower, 'chrome') && !str_contains($lower, 'edg')) return 'Chrome';
        if (str_contains($lower, 'safari') && !str_contains($lower, 'chrome')) return 'Safari';
        if (str_contains($lower, 'firefox')) return 'Firefox';
        return 'Other';
    }
}
