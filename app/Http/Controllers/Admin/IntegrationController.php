<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IntegrationController extends Controller
{
    private const SECTIONS = [
        'couriers',
        'tracking',
        'google',
        'mail',
    ];

    public function index()
    {
        $dbSettings = Setting::pluck('value', 'key')->toArray();
        $keys = [
            'steadfast_enabled', 'steadfast_api_key', 'steadfast_secret_key',
            'pathao_enabled', 'pathao_env', 'pathao_client_id', 'pathao_client_secret', 'pathao_username', 'pathao_password', 'pathao_store_id',
            'redx_enabled', 'redx_env', 'redx_api_token', 'redx_default_area_id', 'courier_auto_sync',
            'bdcourier_api_token', 'bdcourier_auto_check',
            'tracking_gtm_id', 'tracking_ga4_id', 'tracking_meta_pixel_id', 'google_site_verification',
            'tracking_ga4_api_secret', 'tracking_google_ads_id', 'tracking_google_ads_label', 'tracking_meta_domain_verification',
            'tracking_meta_capi_enabled', 'tracking_meta_capi_token', 'tracking_meta_capi_test_code', 'tracking_tiktok_pixel_id',
            'tracking_custom_head', 'tracking_custom_body',
            'google_client_id', 'google_client_secret', 'google_redirect_uri',
            'otp_enabled', 'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $dbSettings[$key] ?? (string) setting($key, '');
        }

        $secret = courier_webhook_secret();
        $webhooks = collect(['steadfast', 'pathao', 'redx'])->mapWithKeys(fn ($p) => [
            $p => route('webhooks.courier', ['provider' => $p, 'token' => $secret]),
        ])->all();
        $lastSync = json_decode((string) setting('courier_last_sync', ''), true) ?: null;
        $bdCourier = app(\App\Services\Courier\BdCourierService::class);
        $usage = $bdCourier->usageToday();
        $bdKeys = array_map(fn ($k) => $k + ['used' => $usage[$k['id']]['used'] ?? 0, 'blocked' => $usage[$k['id']]['blocked'] ?? null], $bdCourier->keys());

        return view('admin.integrations.index', [
            'settings' => $settings,
            'webhooks' => $webhooks,
            'lastSync' => $lastSync,
            'bdKeys'   => $bdKeys,
        ]);
    }

    /** "Check connection" for BD Courier: plan and searches left. */
    public function bdCourierPlan(Request $request, \App\Services\Courier\BdCourierService $bdCourier)
    {
        return response()->json($bdCourier->plan($request->query('key')));
    }

    public function update(Request $request, string $section)
    {
        if (! in_array($section, self::SECTIONS, true)) {
            abort(404);
        }

        $data = $request->validate($this->rulesForSection($section));

        $this->persistSection($section, $data, $request);

        $label = match ($section) {
            'couriers' => 'Courier API credentials',
            'tracking' => 'Marketing & Analytics tracking codes',
            'google'   => 'Google Social Login credentials',
            'mail'     => 'Email Server & OTP settings',
            default    => 'Integration settings',
        };

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $label . ' saved successfully.',
                'section' => $section,
            ]);
        }

        return back()->with('status', $label . ' saved successfully.');
    }

    public function testMail(Request $request)
    {
        $to = $request->validate(['test_email' => ['required', 'email']])['test_email'];

        try {
            configure_mail_from_settings();
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from ' . site_name() . '. Your mail settings are working.',
                fn ($m) => $m->to($to)->subject('Test email — ' . site_name())
            );
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Send failed: ' . $e->getMessage()], 422);
            }

            return back()->withErrors(['test_email' => 'Send failed: ' . $e->getMessage()]);
        }

        $where = (setting('mail_mailer', 'log') === 'smtp') ? "sent to {$to}" : 'written to storage/logs/laravel.log';
        $message = "Test email {$where}.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    /** "Test Meta CAPI connection": sends one PageView with the saved pixel and token (and test code, if set). */
    public function testMeta(\App\Services\ServerTracking $tracking)
    {
        if (! tracking_meta_pixel_id() || trim((string) setting('tracking_meta_capi_token', '')) === '') {
            return response()->json(['ok' => false, 'message' => 'Save your Meta Pixel ID and Conversions API access token first.'], 422);
        }

        $result = $tracking->sendMeta([[
            'event_name'       => 'PageView',
            'event_time'       => time(),
            'event_id'         => 'test-' . \Illuminate\Support\Str::random(10),
            'action_source'    => 'website',
            'event_source_url' => url('/'),
            'user_data'        => [
                'client_ip_address' => request()->ip(),
                'client_user_agent' => (string) request()->userAgent(),
            ],
        ]]);

        $hint = trim((string) setting('tracking_meta_capi_test_code', '')) !== ''
            ? ' Check Events Manager → Test events.'
            : ' Add a test event code to see it under Events Manager → Test events.';

        return response()->json([
            'ok'      => $result['ok'],
            'message' => $result['ok'] ? 'Connected. ' . $result['message'] . $hint : 'Not connected: ' . $result['message'],
        ], $result['ok'] ? 200 : 422);
    }

    private function rulesForSection(string $section): array
    {
        return match ($section) {
            'couriers' => [
                'steadfast_enabled'    => ['nullable', 'boolean'],
                'steadfast_api_key'    => ['nullable', 'string', 'max:255'],
                'steadfast_secret_key' => ['nullable', 'string', 'max:255'],
                'pathao_enabled'       => ['nullable', 'boolean'],
                'pathao_env'           => ['nullable', 'in:sandbox,production'],
                'pathao_client_id'     => ['nullable', 'string', 'max:255'],
                'pathao_client_secret' => ['nullable', 'string', 'max:255'],
                'pathao_username'      => ['nullable', 'string', 'max:255'],
                'pathao_password'      => ['nullable', 'string', 'max:255'],
                'pathao_store_id'      => ['nullable', 'numeric'],
                'redx_enabled'         => ['nullable', 'boolean'],
                'redx_env'             => ['nullable', 'in:sandbox,production'],
                'redx_api_token'       => ['nullable', 'string', 'max:1000'],
                'redx_default_area_id' => ['nullable', 'integer', 'min:1'],
                'courier_auto_sync'    => ['nullable', 'boolean'],
                'bdcourier_keys'         => ['nullable', 'array', 'max:10'],
                'bdcourier_keys.*.id'    => ['nullable', 'string', 'max:40', 'alpha_dash'],
                'bdcourier_keys.*.label' => ['nullable', 'string', 'max:60'],
                'bdcourier_keys.*.token' => ['nullable', 'string', 'max:500'],
                'bdcourier_keys.*.limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
                'bdcourier_auto_check' => ['nullable', 'boolean'],
            ],
            'tracking' => [
                'tracking_gtm_id'          => ['nullable', 'string', 'max:20', 'regex:/^(|GTM-[A-Z0-9]+)$/i'],
                'tracking_ga4_id'          => ['nullable', 'string', 'max:20', 'regex:/^(|G-[A-Z0-9]+)$/i'],
                'tracking_meta_pixel_id'   => ['nullable', 'string', 'max:20', 'regex:/^(|\d+)$/'],
                'google_site_verification' => ['nullable', 'string', 'max:255'],
                'tracking_ga4_api_secret'  => ['nullable', 'string', 'max:100'],
                'tracking_google_ads_id'   => ['nullable', 'string', 'max:20', 'regex:/^(|AW-\d+)$/i'],
                'tracking_google_ads_label' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_\-]*$/'],
                'tracking_meta_domain_verification' => ['nullable', 'string', 'max:500'],
                'tracking_meta_capi_enabled' => ['nullable', 'boolean'],
                'tracking_meta_capi_token' => ['nullable', 'string', 'max:500'],
                'tracking_meta_capi_test_code' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9]*$/'],
                'tracking_tiktok_pixel_id' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9]*$/'],
                'tracking_custom_head'     => ['nullable', 'string', 'max:20000'],
                'tracking_custom_body'     => ['nullable', 'string', 'max:20000'],
            ],
            'google' => [
                'google_client_id'     => ['nullable', 'string', 'max:255'],
                'google_client_secret' => ['nullable', 'string', 'max:255'],
                'google_redirect_uri'  => ['nullable', 'string', 'max:255'],
            ],
            'mail' => [
                'otp_enabled'       => ['nullable', 'boolean'],
                'mail_mailer'       => ['required', 'in:log,smtp'],
                'mail_host'         => ['nullable', 'string', 'max:120'],
                'mail_port'         => ['nullable', 'numeric'],
                'mail_username'     => ['nullable', 'string', 'max:180'],
                'mail_password'     => ['nullable', 'string', 'max:180'],
                'mail_encryption'   => ['nullable', 'in:tls,ssl,none'],
                'mail_from_address' => ['nullable', 'email', 'max:120'],
                'mail_from_name'    => ['nullable', 'string', 'max:120'],
            ],
            default => throw ValidationException::withMessages(['section' => 'Unknown integration section.']),
        };
    }

    private function persistSection(string $section, array $data, Request $request): void
    {
        $keys = match ($section) {
            'couriers' => [
                'steadfast_api_key', 'steadfast_secret_key',
                'pathao_env', 'pathao_client_id', 'pathao_client_secret', 'pathao_username', 'pathao_password', 'pathao_store_id',
                'redx_env', 'redx_api_token', 'redx_default_area_id',
            ],
            'tracking' => [
                'tracking_gtm_id', 'tracking_ga4_id', 'tracking_meta_pixel_id', 'google_site_verification',
                'tracking_google_ads_id', 'tracking_google_ads_label', 'tracking_meta_domain_verification',
                'tracking_meta_capi_test_code', 'tracking_tiktok_pixel_id', 'tracking_custom_head', 'tracking_custom_body',
            ],
            'google'   => ['google_client_id', 'google_client_secret', 'google_redirect_uri'],
            'mail' => [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
            ],
            default => [],
        };

        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = (string) ($data[$key] ?? '');
            if (in_array($key, ['tracking_gtm_id', 'tracking_ga4_id', 'tracking_google_ads_id', 'tracking_tiktok_pixel_id'], true) && $value !== '') {
                $value = strtoupper($value);
            }
            Setting::put($key, $value);
        }

        if ($section === 'couriers') {
            Setting::put('steadfast_enabled', $request->boolean('steadfast_enabled') ? '1' : '0');
            Setting::put('pathao_enabled', $request->boolean('pathao_enabled') ? '1' : '0');
            Setting::put('redx_enabled', $request->boolean('redx_enabled') ? '1' : '0');
            Setting::put('courier_auto_sync', $request->boolean('courier_auto_sync') ? '1' : '0');
            Setting::put('bdcourier_auto_check', $request->boolean('bdcourier_auto_check') ? '1' : '0');

            // BD Courier keys, used top to bottom. The id stays the same so today's usage count carries over.
            $keys = [];
            foreach (array_values($data['bdcourier_keys'] ?? []) as $i => $row) {
                $token = trim((string) ($row['token'] ?? ''));
                if ($token === '') {
                    continue;
                }
                $keys[] = [
                    'id'    => ($row['id'] ?? '') !== '' ? $row['id'] : \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(12)),
                    'label' => trim((string) ($row['label'] ?? '')) ?: 'API key ' . (count($keys) + 1),
                    'token' => $token,
                    'limit' => isset($row['limit']) && $row['limit'] !== '' ? (int) $row['limit'] : null,
                ];
            }
            Setting::put('bdcourier_keys', json_encode($keys));
            Setting::put('bdcourier_api_token', ''); // replaced by the key list
        }

        if ($section === 'tracking') {
            Setting::put('tracking_meta_capi_enabled', $request->boolean('tracking_meta_capi_enabled') ? '1' : '0');
            // Secrets are only replaced when a new one is typed (the field is shown empty); "clear" removes them.
            foreach (['tracking_meta_capi_token', 'tracking_ga4_api_secret'] as $secret) {
                if ($request->boolean($secret . '_clear')) {
                    Setting::put($secret, '');
                } elseif ($request->filled($secret)) {
                    Setting::put($secret, trim((string) $request->input($secret)));
                }
            }
        }

        if ($section === 'mail') {
            Setting::put('otp_enabled', $request->boolean('otp_enabled') ? '1' : '0');
            if ($request->filled('mail_password')) {
                Setting::put('mail_password', (string) $request->input('mail_password'));
            }
        }
    }
}
