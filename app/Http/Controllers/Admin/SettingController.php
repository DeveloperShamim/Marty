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
        'couriers',
        'mail',
        'invoice',
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
                'theme_primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
                'theme_dark_color'    => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
                'theme_surface_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
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
                'show_featured_brands'         => ['nullable', 'boolean'],
                'home_featured_brands_title'   => ['nullable', 'string', 'max:80'],
                'home_featured_brands_subtitle'=> ['nullable', 'string', 'max:200'],
                'home_view_more_label'  => ['nullable', 'string', 'max:40'],
                'default_cta_text'      => ['nullable', 'string', 'max:40'],
                'hero_fallback_badge'   => ['nullable', 'string', 'max:60'],
                'hero_fallback_title'   => ['nullable', 'string', 'max:120'],
                'hero_fallback_subtitle'=> ['nullable', 'string', 'max:200'],
            ],
            'payments' => [
                'bkash_number'  => ['nullable', 'string', 'max:40'],
                'bkash_type'    => ['nullable', 'in:personal,merchant'],
                'nagad_number'  => ['nullable', 'string', 'max:40'],
                'nagad_type'    => ['nullable', 'in:personal,merchant'],
                'rocket_number' => ['nullable', 'string', 'max:40'],
                'rocket_type'   => ['nullable', 'in:personal,merchant'],
                'pay_cod_enabled' => ['nullable', 'boolean'],
                'pay_bkash_enabled' => ['nullable', 'boolean'],
                'pay_nagad_enabled' => ['nullable', 'boolean'],
                'pay_rocket_enabled' => ['nullable', 'boolean'],
                'show_cards_in_footer' => ['nullable', 'boolean'],
                'free_shipping_on_online_payment' => ['nullable', 'boolean'],
                'pay_cod_icon_file' => ['nullable', 'image', 'max:2048'],
                'pay_bkash_icon_file' => ['nullable', 'image', 'max:2048'],
                'pay_nagad_icon_file' => ['nullable', 'image', 'max:2048'],
                'pay_rocket_icon_file' => ['nullable', 'image', 'max:2048'],
                'remove_pay_cod_icon' => ['nullable', 'boolean'],
                'remove_pay_bkash_icon' => ['nullable', 'boolean'],
                'remove_pay_nagad_icon' => ['nullable', 'boolean'],
                'remove_pay_rocket_icon' => ['nullable', 'boolean'],
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
            'invoice' => [
                'invoice_company_name' => ['nullable', 'string', 'max:120'],
                'order_number_prefix'  => ['nullable', 'string', 'max:20'],
                'invoice_phone'        => ['nullable', 'string', 'max:60'],
                'invoice_vat_number'   => ['nullable', 'string', 'max:60'],
                'invoice_address'      => ['nullable', 'string', 'max:500'],
                'invoice_terms'        => ['nullable', 'string', 'max:2000'],
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
                'theme_primary_color', 'theme_dark_color', 'theme_surface_color',
            ],
            'homepage' => [
                'header_promo_text', 'header_promo_link',
                'shop_subtitle', 'delivery_eta_text',
                'home_categories_title', 'home_hot_deal_title', 'home_featured_title',
                'home_reviews_title',
                'home_featured_brands_title', 'home_featured_brands_subtitle',
                'home_view_more_label', 'default_cta_text',
                'hero_fallback_badge', 'hero_fallback_title', 'hero_fallback_subtitle',
            ],
            'payments' => ['bkash_number', 'nagad_number', 'rocket_number', 'bkash_type', 'nagad_type', 'rocket_type'],
            'shipping' => [
                'shipping_inside_dhaka', 'shipping_outside_dhaka', 'tax_percent',
                'shipping_inside_label', 'shipping_outside_label',
                'currency_symbol', 'currency_code',
            ],
            'couriers' => [
                'steadfast_api_key', 'steadfast_secret_key',
                'pathao_env', 'pathao_client_id', 'pathao_client_secret', 'pathao_username', 'pathao_password', 'pathao_store_id',
                'redx_env', 'redx_api_token',
            ],
            'mail' => [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
            ],
            'invoice' => [
                'invoice_company_name', 'order_number_prefix', 'invoice_phone',
                'invoice_vat_number', 'invoice_address', 'invoice_terms',
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

        if ($section === 'homepage') {
            Setting::put('show_featured_brands', $request->boolean('show_featured_brands') ? '1' : '0');
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

        if ($section === 'payments') {
            Setting::put('pay_cod_enabled', $request->boolean('pay_cod_enabled') ? '1' : '0');
            Setting::put('pay_bkash_enabled', $request->boolean('pay_bkash_enabled') ? '1' : '0');
            Setting::put('pay_nagad_enabled', $request->boolean('pay_nagad_enabled') ? '1' : '0');
            Setting::put('pay_rocket_enabled', $request->boolean('pay_rocket_enabled') ? '1' : '0');
            Setting::put('show_cards_in_footer', $request->boolean('show_cards_in_footer') ? '1' : '0');
            Setting::put('free_shipping_on_online_payment', $request->boolean('free_shipping_on_online_payment') ? '1' : '0');

            $this->handleImage($request, 'pay_cod_icon_file', 'pay_cod_icon', 'remove_pay_cod_icon');
            $this->handleImage($request, 'pay_bkash_icon_file', 'pay_bkash_icon', 'remove_pay_bkash_icon');
            $this->handleImage($request, 'pay_nagad_icon_file', 'pay_nagad_icon', 'remove_pay_nagad_icon');
            $this->handleImage($request, 'pay_rocket_icon_file', 'pay_rocket_icon', 'remove_pay_rocket_icon');
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
            'couriers' => 'Courier APIs',
            'mail' => 'Email & OTP',
            'invoice' => 'Invoice format',
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

            if ($settingKey === 'logo') {
                $fullPath = storage_path('app/public/' . $path);
                $extractedColor = extract_dominant_color_from_image($fullPath);
                if ($extractedColor) {
                    Setting::put('brand_primary_color', $extractedColor);
                }
            }
        }
    }

    private function deleteStored(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
