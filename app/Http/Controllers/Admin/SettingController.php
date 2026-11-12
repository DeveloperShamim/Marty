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
        'backups',
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
    /**
     * "Use colours from my logo": the logo's strongest colour as the accent, and a deep shade of it
     * for headings and the top bar. Only fills the colour fields; the owner still presses Save.
     */
    public function logoColours()
    {
        $logo = ltrim((string) setting('logo', ''), '/');
        $path = match (true) {
            $logo === '', str_starts_with($logo, 'http') => null,
            str_starts_with($logo, 'uploads/') => public_path($logo),
            default => storage_path('app/public/' . $logo),
        };
        $primary = $path ? extract_dominant_color_from_image($path) : null;

        if (! $primary) {
            return response()->json([
                'message' => $logo === ''
                    ? 'Upload a logo first, then try again.'
                    : 'Could not find a clear colour in the logo (SVG and very pale logos are skipped). Pick a colour by hand.',
            ], 422);
        }

        return response()->json(['primary' => strtoupper($primary), 'dark' => self::deepShade($primary)]);
    }

    /** The same hue at about 16% lightness, e.g. #8B5A2B → #3D2813. */
    private static function deepShade(string $hex): string
    {
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split(ltrim($hex, '#'), 2));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $d = $max - $min;
        $h = 0.0;
        if ($d > 0) {
            $h = match ($max) {
                $r => fmod(($g - $b) / $d + 6, 6),
                $g => ($b - $r) / $d + 2,
                default => ($r - $g) / $d + 4,
            } / 6;
        }
        $s = $d > 0 ? min(0.55, $d / (1 - abs($max + $min - 1))) : 0;
        $l = 0.16;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h * 6, 2) - 1));
        $m = $l - $c / 2;
        [$r1, $g1, $b1] = match ((int) floor($h * 6) % 6) {
            0 => [$c, $x, 0], 1 => [$x, $c, 0], 2 => [0, $c, $x],
            3 => [0, $x, $c], 4 => [$x, 0, $c], default => [$c, 0, $x],
        };

        return sprintf('#%02X%02X%02X', round(($r1 + $m) * 255), round(($g1 + $m) * 255), round(($b1 + $m) * 255));
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
                'whatsapp_number' => ['nullable', 'string', 'max:60'],
                'contact_email'   => ['nullable', 'email', 'max:120'],
                'contact_address' => ['nullable', 'string', 'max:255'],
                'contact_hours'   => ['nullable', 'string', 'max:120'],
                'contact_title'   => ['nullable', 'string', 'max:120'],
                'contact_intro'   => ['nullable', 'string', 'max:255'],
                'facebook_url'    => ['nullable', 'string', 'max:255'],
                'messenger_url'   => ['nullable', 'string', 'max:255'],
                'instagram_url'   => ['nullable', 'string', 'max:255'],
                'twitter_url'     => ['nullable', 'string', 'max:255'],
                'search_placeholder' => ['nullable', 'string', 'max:120'],
                'theme_primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
                'theme_dark_color'    => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
                'theme_surface_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}){1,2}$/'],
                'theme_font'          => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Support\StoreStyle::FONTS))],
                'theme_corners'       => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Support\StoreStyle::CORNERS))],
                'logo_file'       => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
                'favicon_file'    => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:1024'],
                'remove_logo'     => ['nullable', 'boolean'],
                'remove_favicon'  => ['nullable', 'boolean'],
            ],
            'homepage' => [
                'shop_subtitle'       => ['nullable', 'string', 'max:255'],
                'delivery_eta_text'   => ['nullable', 'string', 'max:120'],
                'home_categories_title' => ['nullable', 'string', 'max:80'],
                'home_categories_subtitle' => ['nullable', 'string', 'max:160'],
                'home_hot_deal_title'   => ['nullable', 'string', 'max:80'],
                'home_featured_title'   => ['nullable', 'string', 'max:80'],
                'home_reviews_title'    => ['nullable', 'string', 'max:80'],
                'home_reviews_subtitle' => ['nullable', 'string', 'max:200'],
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
                'nagad_number'  => ['nullable', 'string', 'max:40'],
                'rocket_number' => ['nullable', 'string', 'max:40'],
                'bkash_account_type'  => ['nullable', 'in:personal,merchant,agent'],
                'nagad_account_type'  => ['nullable', 'in:personal,merchant,agent'],
                'rocket_account_type' => ['nullable', 'in:personal,merchant,agent'],
                'order_hotline' => ['nullable', 'string', 'max:40'],
                'order_number_prefix' => ['nullable', 'string', 'max:6', 'regex:/^[A-Za-z0-9]*$/'],
                'pay_cod_enabled' => ['nullable', 'boolean'],
                'cod_auto_confirm' => ['nullable', 'boolean'],
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
                'shipping_inside_time'   => ['nullable', 'string', 'max:40'],
                'shipping_outside_time'  => ['nullable', 'string', 'max:40'],
                'pdp_authentic_label'    => ['nullable', 'string', 'max:40'],
                'pdp_quality_badge'      => ['nullable', 'string', 'max:40'],
                'pdp_return_text'        => ['nullable', 'string', 'max:40'],
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
            'backups' => [
                'backup_frequency' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Console\Commands\BackupDatabase::FREQUENCIES))],
                'backup_time'      => ['required', 'date_format:H:i'],
                'backup_keep'      => ['required', 'integer', 'min:1', 'max:90'],
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
                'contact_phone', 'whatsapp_number', 'contact_email', 'contact_address',
                'contact_hours', 'contact_title', 'contact_intro',
                'facebook_url', 'messenger_url', 'instagram_url', 'twitter_url',
                'search_placeholder',
                'theme_primary_color', 'theme_dark_color', 'theme_surface_color',
                'theme_font', 'theme_corners',
            ],
            'homepage' => [
                'shop_subtitle', 'delivery_eta_text',
                'home_categories_title', 'home_categories_subtitle', 'home_hot_deal_title', 'home_featured_title',
                'home_reviews_title', 'home_reviews_subtitle',
                'home_featured_brands_title', 'home_featured_brands_subtitle',
                'home_view_more_label', 'default_cta_text',
                'hero_fallback_badge', 'hero_fallback_title', 'hero_fallback_subtitle',
            ],
            'payments' => [
                'bkash_number', 'nagad_number', 'rocket_number',
                'bkash_account_type', 'nagad_account_type', 'rocket_account_type', 'order_hotline', 'order_number_prefix',
            ],
            'shipping' => [
                'shipping_inside_dhaka', 'shipping_outside_dhaka', 'tax_percent',
                'shipping_inside_label', 'shipping_outside_label',
                'shipping_inside_time', 'shipping_outside_time',
                'pdp_authentic_label', 'pdp_quality_badge', 'pdp_return_text',
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
            'backups' => ['backup_frequency', 'backup_time', 'backup_keep'],
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

        if ($section === 'payments') {
            Setting::put('pay_cod_enabled', $request->boolean('pay_cod_enabled') ? '1' : '0');
            Setting::put('cod_auto_confirm', $request->boolean('cod_auto_confirm') ? '1' : '0');
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
            'couriers' => 'Courier APIs',
            'mail' => 'Email & OTP',
            'invoice' => 'Invoice format',
            'seo' => 'SEO defaults',
            'tracking' => 'Marketing & analytics',
            'legal' => 'Legal pages',
            'backups' => 'Backup schedule',
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
