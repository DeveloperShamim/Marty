<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\StarterPacks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Turn a fresh copy of the platform into a new client's blank store:
 *   php artisan store:new --name="Kick Zone" --type=fashion --phone=01XXXXXXXXX --admin-email=owner@kickzone.com
 *
 * Replaces the shop's name, wording, theme, categories, trust strip, coupons and banners with the
 * starter pack for that type of business. Only runs on a store with no products and no orders,
 * so it can never wipe a shop that is already selling.
 */
class NewStore extends Command
{
    protected $signature = 'store:new
        {--name= : The shop name, e.g. "Kick Zone"}
        {--type= : Type of business: fashion, beauty, electronics, grocery, home or general}
        {--phone= : Shop phone / WhatsApp number}
        {--email= : Shop email shown to customers}
        {--admin-email= : Create an admin account with this email (a password is printed once)}';

    protected $description = 'Set up a fresh copy as a new client store (name, theme and starter categories)';

    public function handle(): int
    {
        if (Order::exists() || Product::exists()) {
            $this->error('This store already has products or orders. store:new only sets up a fresh, empty store.');

            return self::FAILURE;
        }

        $name = trim((string) ($this->option('name') ?: $this->ask('Shop name')));
        if ($name === '') {
            $this->error('A shop name is needed: --name="Your Shop"');

            return self::FAILURE;
        }

        $options = StarterPacks::options();
        $type = (string) ($this->option('type') ?: $this->choice('Type of business', $options, 'general'));
        if (! isset(StarterPacks::PACKS[$type])) {
            $type = array_search($type, $options, true) ?: '';
        }
        if (! isset(StarterPacks::PACKS[$type])) {
            $this->error('Unknown type. Use one of: ' . implode(', ', array_keys($options)));

            return self::FAILURE;
        }

        $phone = trim((string) $this->option('phone'));
        $email = trim((string) $this->option('email'));
        $adminEmail = trim((string) $this->option('admin-email'));
        if ($adminEmail !== '' && ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error("{$adminEmail} is not a valid email address.");

            return self::FAILURE;
        }

        if (! $this->confirm("Set this copy up as \"{$name}\" ({$options[$type]})? Its categories, coupons, banners and trust strip are replaced.", true)) {
            return self::FAILURE;
        }

        $password = null;
        DB::transaction(function () use ($type, $name, $phone, $email, $adminEmail, &$password) {
            foreach (StarterPacks::settings($type, $name, $phone, $email) as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            Brand::query()->delete();
            Coupon::query()->delete();
            Banner::query()->delete();
            Feature::query()->delete();
            Category::query()->delete();

            foreach (StarterPacks::PACKS[$type][5] as $i => [$catName, $icon]) {
                Category::create([
                    'name' => $catName,
                    'slug' => Str::slug($catName) ?: 'category-' . ($i + 1),
                    'icon' => $icon,
                    'position' => $i,
                    'is_active' => true,
                    'is_featured' => true,
                    'meta_title' => "{$catName} — {$name}",
                ]);
            }

            foreach (StarterPacks::FEATURES as $i => [$title, $subtitle, $path]) {
                Feature::create(['title' => $title, 'subtitle' => $subtitle, 'icon' => $path, 'position' => $i, 'is_active' => true]);
            }

            Coupon::create([
                'code' => 'WELCOME10',
                'description' => '10% off orders of ৳1,000 or more',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 1000,
                'max_discount' => 300,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ]);

            // One text banner in the brand colours until the owner uploads a photo banner
            Banner::create([
                'title' => str_replace("\n", ' ', StarterPacks::PACKS[$type][4]),
                'subtitle' => ucfirst(StarterPacks::PACKS[$type][3]) . '. Cash on delivery all over Bangladesh.',
                'badge' => mb_strtoupper($name),
                'image' => null,
                'link_url' => '/shop',
                'button_text' => 'Shop now',
                'placement' => 'hero',
                'style' => 'brand',
                'position' => 0,
                'is_active' => true,
            ]);

            if ($adminEmail !== '' && ! User::where('email', $adminEmail)->exists()) {
                $password = Str::password(16, symbols: false);
                User::create([
                    'name' => "{$name} Admin",
                    'email' => $adminEmail,
                    'password' => Hash::make($password),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            }
        });
        Setting::forgetCache();

        $this->info("\"{$name}\" is ready as a {$options[$type]} store.");
        if ($password !== null) {
            $this->warn("Admin login: {$adminEmail}  password: {$password}  <- save it now, it is not shown again");
        }
        $this->line('Next: log in to /admin. The Dashboard lists what is left (logo, payments, first product, banner photo, courier, email).');

        return self::SUCCESS;
    }
}
