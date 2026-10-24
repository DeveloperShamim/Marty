<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Looks inside the Google Tag Manager container for the same Meta Pixel, GA4, Google Ads or TikTok ID that is
 * also set in Admin → Integrations. The shop already sends those tags itself, so a copy in Tag Manager makes every
 * event count twice (and its Meta events never match the server-side ones). The result is kept for a day.
 */
class GtmOverlapCheck
{
    public const GTM_URL = 'https://www.googletagmanager.com/gtm.js';

    /**
     * ['container' => 'GTM-…', 'duplicates' => [['tool' => 'Meta Pixel', 'id' => '…'], …], 'error' => ?string,
     *  'checked_at' => timestamp], or null when no container is set.
     */
    public function result(bool $fresh = false): ?array
    {
        $container = tracking_gtm_id();
        if (! $container) {
            return null;
        }

        $ids = $this->shopIds();
        $key = 'gtm-overlap:' . md5($container . '|' . json_encode($ids));
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addDay(), fn () => $this->check($container, $ids));
    }

    /** The tags the shop itself sends, by tool name. */
    private function shopIds(): array
    {
        return array_filter([
            'Meta Pixel'   => tracking_meta_pixel_id(),
            'GA4'          => tracking_ga4_id(),
            'Google Ads'   => tracking_google_ads_id(),
            'TikTok Pixel' => tracking_tiktok_pixel_id(),
        ]);
    }

    private function check(string $container, array $ids): array
    {
        $result = ['container' => $container, 'duplicates' => [], 'error' => null, 'checked_at' => now()->timestamp];

        try {
            $res = Http::timeout(8)->get(self::GTM_URL, ['id' => $container]);
            if (! $res->successful()) {
                $result['error'] = $res->status() === 404
                    ? 'Tag Manager has no published container with this ID.'
                    : 'Tag Manager did not answer (HTTP ' . $res->status() . ').';

                return $result;
            }
            $js = $res->body();
        } catch (\Throwable $e) {
            $result['error'] = 'Could not reach Tag Manager.';

            return $result;
        }

        foreach ($ids as $tool => $id) {
            if (stripos($js, (string) $id) !== false) {
                $result['duplicates'][] = ['tool' => $tool, 'id' => $id];
            }
        }

        return $result;
    }
}
