<?php

namespace App\Services\Courier;

use App\Models\CustomerCourierCheck;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Customer delivery history across Bangladeshi couriers (Pathao, Steadfast, RedX, Paperfly...)
 * from BD Courier (bdcourier.com). Each lookup uses one search from the paid plan, so results
 * are saved per phone number and reused for FRESH_DAYS days.
 */
class BdCourierService
{
    private const BASE_URL = 'https://api.bdcourier.com';
    public const FRESH_DAYS = 7;

    public function isConfigured(): bool
    {
        return $this->keys() !== [];
    }

    /**
     * API keys in the order they are used: [['id', 'label', 'token', 'limit' (null = no limit)], ...].
     * Stored as JSON in the "bdcourier_keys" setting; a single older "bdcourier_api_token" still works.
     */
    public function keys(): array
    {
        $keys = json_decode((string) setting('bdcourier_keys', ''), true);
        if (! is_array($keys) || $keys === []) {
            $legacy = trim((string) setting('bdcourier_api_token'));

            return $legacy === '' ? [] : [['id' => 'default', 'label' => 'API key 1', 'token' => $legacy, 'limit' => null]];
        }

        return array_values(array_filter(array_map(fn ($k) => [
            'id'    => (string) ($k['id'] ?? ''),
            'label' => (string) ($k['label'] ?? ''),
            'token' => trim((string) ($k['token'] ?? '')),
            'limit' => isset($k['limit']) && $k['limit'] !== '' && $k['limit'] !== null ? max(0, (int) $k['limit']) : null,
        ], $keys), fn ($k) => $k['id'] !== '' && $k['token'] !== ''));
    }

    /** Today's usage per key id: ['key' => ['used' => n, 'blocked' => reason|null]]. Days follow the app timezone. */
    public function usageToday(): array
    {
        return \Illuminate\Support\Facades\DB::table('bdcourier_key_usage')->where('day', now()->toDateString())->get()
            ->mapWithKeys(fn ($r) => [$r->key_id => ['used' => (int) $r->used, 'blocked' => $r->blocked_reason]])->all();
    }

    private function recordUse(string $keyId, ?string $blockedReason = null): void
    {
        $day = now()->toDateString();
        $table = \Illuminate\Support\Facades\DB::table('bdcourier_key_usage');
        $table->insertOrIgnore(['key_id' => $keyId, 'day' => $day, 'used' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $query = $table->where('key_id', $keyId)->where('day', $day);
        $blockedReason === null
            ? $query->increment('used', 1, ['updated_at' => now()])
            : $query->update(['blocked_reason' => mb_substr($blockedReason, 0, 250), 'updated_at' => now()]);
    }

    /** Keys that may still be used today, in order. */
    private function availableKeys(): array
    {
        $usage = $this->usageToday();

        return array_values(array_filter($this->keys(), function ($k) use ($usage) {
            $u = $usage[$k['id']] ?? ['used' => 0, 'blocked' => null];

            return $u['blocked'] === null && ($k['limit'] === null || $u['used'] < $k['limit']);
        }));
    }

    /** 01XXXXXXXXX, or null if it isn't a Bangladeshi mobile number. */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) ? $digits : null;
    }

    public function saved(?string $phone): ?CustomerCourierCheck
    {
        $phone = self::normalizePhone($phone);

        return $phone ? CustomerCourierCheck::where('phone', $phone)->first() : null;
    }

    /**
     * Saved result if it is recent enough, otherwise a new lookup (one search).
     *
     * @return array{success: bool, check?: CustomerCourierCheck, cached?: bool, message?: string}
     */
    public function check(?string $phone, bool $force = false): array
    {
        $phone = self::normalizePhone($phone);
        if (! $phone) {
            return ['success' => false, 'message' => 'Not a valid Bangladeshi mobile number.'];
        }

        $saved = CustomerCourierCheck::where('phone', $phone)->first();
        if (! $force && $saved && $saved->checked_at->gt(now()->subDays(self::FRESH_DAYS))) {
            return ['success' => true, 'check' => $saved, 'cached' => true];
        }
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'BD Courier API token is not set in Integrations.'];
        }

        // Try each key in order; a key that hits its daily limit or is refused is skipped for today.
        $data = null;
        $body = [];
        $lastError = null;
        foreach ($this->availableKeys() as $key) {
            try {
                $response = Http::withToken($key['token'])
                    ->acceptJson()->asJson()->timeout(20)
                    ->post(self::BASE_URL . '/courier-check', ['phone' => $phone]);
                $body = $response->json() ?? [];
                $data = $body['data'] ?? null;
            } catch (\Throwable $e) {
                Log::warning('BD Courier lookup failed', ['key' => $key['label'], 'error' => $e->getMessage()]);
                $lastError = 'Could not reach BD Courier.';
                break; // the service is down; other keys won't help
            }

            if ($response->successful() && is_array($data)) {
                $this->recordUse($key['id']);
                break;
            }

            $lastError = 'BD Courier: ' . ($body['message'] ?? 'HTTP ' . $response->status());
            $data = null;
            if (in_array($response->status(), [401, 402, 403, 429], true) || str_contains(strtolower((string) ($body['message'] ?? '')), 'limit')) {
                // Out of searches, expired or invalid: don't try this key again today.
                $this->recordUse($key['id'], ($key['label'] ?: 'Key') . ': ' . $lastError);
                continue;
            }
            break;
        }

        if (! is_array($data)) {
            return ['success' => false, 'message' => $lastError ?? 'All BD Courier API keys have used today\'s searches. They reset tomorrow, or raise a limit in Integrations.'];
        }

        $summary = $data['summary'] ?? [];
        $couriers = collect($data)->except('summary')->filter(fn ($c) => is_array($c))->map(fn ($c, $key) => [
            'name'      => $c['name'] ?? ucfirst((string) $key),
            'total'     => (int) ($c['total_parcel'] ?? 0),
            'delivered' => (int) ($c['success_parcel'] ?? 0),
            'cancelled' => (int) ($c['cancelled_parcel'] ?? max(0, (int) ($c['total_parcel'] ?? 0) - (int) ($c['success_parcel'] ?? 0))),
        ])->values()->all();
        $reports = collect($body['reports'] ?? [])->filter(fn ($r) => is_array($r))->map(fn ($r) => [
            'courier' => $r['courierName'] ?? null,
            'name'    => $r['name'] ?? null,
            'details' => $r['details'] ?? null,
            'date'    => $r['created_at'] ?? null,
        ])->values()->all();

        $total = (int) ($summary['total_parcel'] ?? 0);
        $delivered = (int) ($summary['success_parcel'] ?? 0);

        $check = CustomerCourierCheck::updateOrCreate(['phone' => $phone], [
            'total_parcels' => $total,
            'delivered'     => $delivered,
            'cancelled'     => (int) ($summary['cancelled_parcel'] ?? max(0, $total - $delivered)),
            'success_ratio' => isset($summary['success_ratio']) ? (float) $summary['success_ratio'] : ($total ? round($delivered / $total * 100, 2) : null),
            'couriers'      => $couriers,
            'reports'       => $reports,
            'checked_at'    => now(),
        ]);

        return ['success' => true, 'check' => $check, 'cached' => false];
    }

    /** Plan name and searches left for one key (the first key if none given), for the Integrations page. */
    public function plan(?string $keyId = null): array
    {
        $key = collect($this->keys())->first(fn ($k) => $keyId === null || $k['id'] === $keyId);
        if (! $key) {
            return ['success' => false, 'message' => 'Add and save the API key first.'];
        }

        try {
            $response = Http::withToken($key['token'])->acceptJson()->timeout(15)->get(self::BASE_URL . '/my-plan');
            $d = $response->json('data');
            if (! $response->successful() || ! is_array($d)) {
                return ['success' => false, 'message' => $response->json('message') ?? 'HTTP ' . $response->status()];
            }

            return [
                'success'   => true,
                'plan'      => $d['plan_name'] ?? 'Free',
                'status'    => $d['status'] ?? null,
                'remaining' => (int) ($d['remaining_paid_calls'] ?? 0) + (int) ($d['remaining_free_calls'] ?? 0),
                'paid_left' => (int) ($d['remaining_paid_calls'] ?? 0),
                'free_left' => (int) ($d['remaining_free_calls'] ?? 0),
                'renews'    => $d['next_due_date'] ?? null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not reach BD Courier.'];
        }
    }
}
