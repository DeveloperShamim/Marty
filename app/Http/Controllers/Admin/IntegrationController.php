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
        return view('admin.integrations.index', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
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
            ],
            'tracking' => [
                'tracking_gtm_id'        => ['nullable', 'string', 'max:20', 'regex:/^(|GTM-[A-Z0-9]+)$/i'],
                'tracking_ga4_id'        => ['nullable', 'string', 'max:20', 'regex:/^(|G-[A-Z0-9]+)$/i'],
                'tracking_meta_pixel_id' => ['nullable', 'string', 'max:20', 'regex:/^(|\d+)$/'],
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
                'redx_env', 'redx_api_token',
            ],
            'tracking' => ['tracking_gtm_id', 'tracking_ga4_id', 'tracking_meta_pixel_id'],
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
            if (in_array($key, ['tracking_gtm_id', 'tracking_ga4_id'], true) && $value !== '') {
                $value = strtoupper($value);
            }
            Setting::put($key, $value);
        }

        if ($section === 'couriers') {
            Setting::put('steadfast_enabled', $request->boolean('steadfast_enabled') ? '1' : '0');
            Setting::put('pathao_enabled', $request->boolean('pathao_enabled') ? '1' : '0');
            Setting::put('redx_enabled', $request->boolean('redx_enabled') ? '1' : '0');
        }

        if ($section === 'mail') {
            Setting::put('otp_enabled', $request->boolean('otp_enabled') ? '1' : '0');
            if ($request->filled('mail_password')) {
                Setting::put('mail_password', (string) $request->input('mail_password'));
            }
        }
    }
}
