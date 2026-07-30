<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    private const SECTIONS = [
        'brand',
        'homepage',
        'payments',
        'shipping',
        'mail',
        'seo',
        'tracking',
        'legal',
    ];

    public function edit()
    {
        return view('admin.settings', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function updateSection(Request $request, string $section)
    {
        if (! in_array($section, self::SECTIONS, true)) {
            abort(404);
        }

        $data = $request->validate($this->rulesForSection($section));

        $this->persistSection($section, $data, $request);

        $payload = [
            'message' => $this->sectionLabel($section) . ' saved.',
            'section' => $section,
        ];

        if ($section === 'brand') {
            $payload['logo_url'] = logo_url();
            $payload['favicon_url'] = favicon_url();
            $payload['has_logo'] = has_custom_logo();
            $payload['has_favicon'] = (bool) ($request->boolean('remove_favicon') ? false : setting('favicon'));
        }

        if ($section === 'tracking') {
            $payload['tracking_active'] = tracking_any_enabled();
            $payload['tracking_labels'] = array_values(array_filter([
                tracking_gtm_id() ? 'GTM' : null,
                tracking_ga4_id() ? 'GA4' : null,
                tracking_meta_pixel_id() ? 'Meta Pixel' : null,
            ]));
        }

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('status', $payload['message']);
    }

    /** Send a test email to the admin using the current mail settings. */
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

        $where = (setting('mail_mailer', 'log') === 'smtp') ? "sent to {$to}" : 'written to storage/logs/laravel.log (log mailer)';
        $message = "Test email {$where}.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    private function rulesForSection(string $section): array
    {
        return match ($section) {
            'brand' => [
                'site_name'       => ['required', 'string', 'max:120'],
                'tagline'         => ['nullable', 'string', 'max:200'],
                'footer_text'     => ['nullable', 'string', 'max:400'],
                'contact_phone'   => ['nullable', 'string', 'max:60'],
                'contact_email'   => ['nullable', 'email', 'max:120'],
                'contact_address' => ['nullable', 'string', 'max:255'],
                'contact_hours'   => ['nullable', 'string', 'max:120'],
                'contact_title'   => ['nullable', 'string', 'max:120'],
                'contact_intro'   => ['nullable', 'string', 'max:255'],
                'facebook_url'    => ['nullable', 'string', 'max:255'],
                'instagram_url'   => ['nullable', 'string', 'max:255'],
                'twitter_url'     => ['nullable', 'string', 'max:255'],
                'search_placeholder' => ['nullable', 'string', 'max:120'],
                'logo_file'       => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
                'favicon_file'    => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:1024'],
                'remove_logo'     => ['nullable', 'boolean'],
                'remove_favicon'  => ['nullable', 'boolean'],
            ],
            'homepage' => [
                'header_promo_text'   => ['nullable', 'string', 'max:200'],
                'header_promo_link'   => ['nullable', 'string', 'max:255'],
                'shop_subtitle'       => ['nullable', 'string', 'max:255'],
                'delivery_eta_text'   => ['nullable', 'string', 'max:120'],
                'home_categories_title' => ['nullable', 'string', 'max:80'],
                'home_hot_deal_title'   => ['nullable', 'string', 'max:80'],
                'home_featured_title'   => ['nullable', 'string', 'max:80'],
                'home_reviews_title'    => ['nullable', 'string', 'max:80'],
                'home_view_more_label'  => ['nullable', 'string', 'max:40'],
                'default_cta_text'      => ['nullable', 'string', 'max:40'],
                'hero_fallback_badge'   => ['nullable', 'string', 'max:60'],
                'hero_fallback_title'   => ['nullable', 'string', 'max:120'],
                'hero_fallback_subtitle'=> ['nullable', 'string', 'max:200'],
            ],
            'payments' => [
                'bkash_number'  => ['nullable', 'string', 'max:40'],
                'nagad_number'  => ['nullable', 'string', 'max:40'],
                'rocket_number' => ['nullable', 'string', 'max:40'],
                'pay_cod_enabled' => ['nullable', 'boolean'],
                'pay_bkash_enabled' => ['nullable', 'boolean'],
                'pay_nagad_enabled' => ['nullable', 'boolean'],
                'pay_rocket_enabled' => ['nullable', 'boolean'],
                'show_cards_in_footer' => ['nullable', 'boolean'],
            ],
            'shipping' => [
                'shipping_inside_dhaka'  => ['required', 'numeric', 'min:0'],
                'shipping_outside_dhaka' => ['required', 'numeric', 'min:0'],
                'tax_percent'            => ['required', 'numeric', 'min:0', 'max:100'],
                'shipping_inside_label'  => ['nullable', 'string', 'max:80'],
                'shipping_outside_label' => ['nullable', 'string', 'max:80'],
                'currency_symbol'        => ['nullable', 'string', 'max:8'],
                'currency_code'          => ['nullable', 'string', 'max:8'],
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
            'seo' => [
                'default_meta_title'       => ['nullable', 'string', 'max:180'],
                'default_meta_description' => ['nullable', 'string', 'max:400'],
                'default_meta_keywords'    => ['nullable', 'string', 'max:400'],
            ],
            'tracking' => [
                'tracking_gtm_id'        => ['nullable', 'string', 'max:20', 'regex:/^(|GTM-[A-Z0-9]+)$/i'],
                'tracking_ga4_id'        => ['nullable', 'string', 'max:20', 'regex:/^(|G-[A-Z0-9]+)$/i'],
                'tracking_meta_pixel_id' => ['nullable', 'string', 'max:20', 'regex:/^(|\d+)$/'],
            ],
            'legal' => [
                'terms_content'   => ['nullable', 'string', 'max:20000'],
                'privacy_content' => ['nullable', 'string', 'max:20000'],
            ],
            default => throw ValidationException::withMessages(['section' => 'Unknown settings section.']),
        };
    }

    private function persistSection(string $section, array $data, Request $request): void
    {
        $keys = match ($section) {
            'brand' => [
                'site_name', 'tagline', 'footer_text',
                'contact_phone', 'contact_email', 'contact_address',
                'contact_hours', 'contact_title', 'contact_intro',
                'facebook_url', 'instagram_url', 'twitter_url',
                'search_placeholder',
            ],
            'homepage' => [
                'header_promo_text', 'header_promo_link',
                'shop_subtitle', 'delivery_eta_text',
                'home_categories_title', 'home_hot_deal_title', 'home_featured_title',
                'home_reviews_title',
                'home_view_more_label', 'default_cta_text',
                'hero_fallback_badge', 'hero_fallback_title', 'hero_fallback_subtitle',
            ],
            'payments' => ['bkash_number', 'nagad_number', 'rocket_number'],
            'shipping' => [
                'shipping_inside_dhaka', 'shipping_outside_dhaka', 'tax_percent',
                'shipping_inside_label', 'shipping_outside_label',
                'currency_symbol', 'currency_code',
            ],
            'mail' => [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
            ],
            'seo' => ['default_meta_title', 'default_meta_description', 'default_meta_keywords'],
            'tracking' => ['tracking_gtm_id', 'tracking_ga4_id', 'tracking_meta_pixel_id'],
            'legal' => ['terms_content', 'privacy_content'],
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

        if ($section === 'mail') {
            Setting::put('otp_enabled', $request->boolean('otp_enabled') ? '1' : '0');
            if ($request->filled('mail_password')) {
                Setting::put('mail_password', (string) $request->input('mail_password'));
            }
        }

        if ($section === 'payments') {
                        Setting::put('pay_cod_enabled', $request->boolean('pay_cod_enabled') ? '1' : '0');
            Setting::put('pay_bkash_enabled', $request->boolean('pay_bkash_enabled') ? '1' : '0');
            Setting::put('pay_nagad_enabled', $request->boolean('pay_nagad_enabled') ? '1' : '0');
            Setting::put('pay_rocket_enabled', $request->boolean('pay_rocket_enabled') ? '1' : '0');
Setting::put('show_cards_in_footer', $request->boolean('show_cards_in_footer') ? '1' : '0');
        }

        if ($section === 'brand') {
            $this->handleImage($request, 'logo_file', 'logo', 'remove_logo');
            $this->handleImage($request, 'favicon_file', 'favicon', 'remove_favicon');
        }
    }

    private function sectionLabel(string $section): string
    {
        return match ($section) {
            'brand' => 'Brand & identity',
            'homepage' => 'Homepage',
            'payments' => 'Payments',
            'shipping' => 'Shipping & tax',
            'mail' => 'Email & OTP',
            'seo' => 'SEO defaults',
            'tracking' => 'Marketing & analytics',
            'legal' => 'Legal pages',
            default => 'Settings',
        };
    }

    private function handleImage(Request $request, string $fileField, string $settingKey, string $removeField): void
    {
        if ($request->boolean($removeField)) {
            $this->deleteStored(Setting::get($settingKey));
            Setting::put($settingKey, '');

            return;
        }

        if ($request->hasFile($fileField)) {
            $this->deleteStored(Setting::get($settingKey));
            $path = $request->file($fileField)->store('branding', 'public');
            Setting::put($settingKey, $path);
        }
    }

    private function deleteStored(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
