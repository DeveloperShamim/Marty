<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaunchReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_creates_only_the_essentials(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Setting::put('bkash_number', '01712345678'); // already configured by the owner

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, Product::count());
        $this->assertSame(0, Order::count());
        $this->assertSame(0, ProductReview::count());
        $this->assertSame(1, User::count());

        $admin = User::firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertFalse(Hash::check('password', $admin->password), 'No default password on a live store');

        $this->assertSame('01712345678', setting('bkash_number'), 'Existing settings are never overwritten');
        $this->assertSame('', (string) setting('nagad_number'), 'No sample payment numbers');
        $this->assertSame('0', (string) setting('otp_enabled'), 'Sign-up codes need a mail server first');
        $this->assertGreaterThan(0, \App\Models\ProductAttributeType::count());

        // Running it again changes nothing.
        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        $this->assertSame(1, User::count());
    }

    public function test_pages_send_security_headers(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_privacy_policy_lists_courier_checks_chat_and_unfinished_checkouts(): void
    {
        $this->get(route('privacy'))->assertOk()
            ->assertSee('BD Courier')
            ->assertSee('Live chat messages')
            ->assertSee('saved even if you don')
            ->assertSee('deleted automatically after 60 days')
            ->assertDontSee('contractually obligated');

        Setting::put('tracking_meta_pixel_id', '123456789');
        $this->get(route('privacy'))->assertSee('Meta (Facebook) Pixel');
    }

    public function test_launch_check_fails_on_demo_passwords(): void
    {
        User::factory()->create(['role' => 'admin', 'password' => Hash::make('password')]);

        $this->artisan('app:launch-check')->assertFailed();
    }
}
