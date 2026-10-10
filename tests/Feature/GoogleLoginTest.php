<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function fakeGoogle(string $email, string $id = 'g-123', bool $verified = true): void
    {
        $googleUser = (new GoogleUser())->setRaw(['email_verified' => $verified])->map([
            'id' => $id, 'name' => 'Rahim Uddin', 'email' => $email, 'avatar' => 'https://lh3.googleusercontent.com/a/x',
        ]);
        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_redirect_uri_is_always_the_live_domain_callback(): void
    {
        Setting::put('google_redirect_uri', 'https://www.vantbd.com'); // an old, wrong value is ignored
        Setting::put('google_client_id', 'abc.apps.googleusercontent.com');
        Setting::put('google_client_secret', 'secret');
        $admin = User::factory()->create(['role' => 'admin']);
        $callback = url('/auth/google/callback');

        $this->actingAs($admin)->get(route('admin.integrations.index'))
            ->assertOk()
            ->assertSee('value="' . $callback . '"', false)
            ->assertSee('data-guide-open="googlelogin"', false);

        auth()->logout();
        $this->get(route('auth.google'))->assertRedirectContains('redirect_uri=' . urlencode($callback));
    }

    public function test_staff_signs_in_to_the_admin_panel_with_google(): void
    {
        $staff = User::factory()->create(['email' => 'manager@vantbd.com', 'role' => 'order_manager']);
        $this->fakeGoogle('Manager@vantbd.com');

        $this->withSession(['google_login_for' => 'admin'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(\App\Support\StaffAccess::home($staff));

        $this->assertAuthenticatedAs($staff->fresh());
        $this->assertSame('g-123', $staff->fresh()->google_id);
    }

    public function test_admin_google_login_refuses_customers_and_suspended_staff(): void
    {
        User::factory()->create(['email' => 'rahim@gmail.com', 'role' => 'customer']);
        $this->fakeGoogle('rahim@gmail.com');
        $this->withSession(['google_login_for' => 'admin'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        User::factory()->create(['email' => 'old@vantbd.com', 'role' => 'admin', 'is_suspended' => true]);
        $this->fakeGoogle('old@vantbd.com', 'g-999');
        $this->withSession(['google_login_for' => 'admin'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_google_button_shows_in_the_sign_in_popup_and_admin_login_once_set_up(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
        $this->get(route('home'))->assertDontSee('Continue with Google');

        Setting::put('google_client_id', 'abc.apps.googleusercontent.com');
        Setting::put('google_client_secret', 'secret');
        $this->get(route('home'))->assertSee('Continue with Google');
        $this->get(route('admin.login'))->assertSee(route('auth.google', ['for' => 'admin']), false);
    }

    public function test_without_keys_the_button_explains_what_is_missing(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('auth.google'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertSee('GOOGLE_CLIENT_ID');
    }

    public function test_with_keys_the_button_sends_the_customer_to_google(): void
    {
        Setting::put('google_client_id', 'abc.apps.googleusercontent.com');
        Setting::put('google_client_secret', 'secret');

        $this->get(route('auth.google'))->assertRedirectContains('accounts.google.com');
    }

    public function test_new_customer_is_created_and_logged_in(): void
    {
        $this->fakeGoogle('Rahim@Gmail.com');

        $this->get(route('auth.google.callback'))->assertRedirect(route('account'));

        $user = User::where('email', 'rahim@gmail.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertSame('g-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_customer_is_linked_and_returned_to_checkout(): void
    {
        $user = User::factory()->create(['email' => 'rahim@gmail.com', 'role' => 'customer']);
        $this->fakeGoogle('rahim@gmail.com');

        $this->withSession(['url.intended' => route('checkout.show')])
            ->get(route('auth.google.callback'))->assertRedirect(route('checkout.show'));

        $this->assertSame('g-123', $user->fresh()->google_id);
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_staff_suspended_and_unverified_accounts_are_refused(): void
    {
        User::factory()->create(['email' => 'admin@vantbd.com', 'role' => 'admin']);
        $this->fakeGoogle('admin@vantbd.com');
        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();

        User::factory()->create(['email' => 'bad@gmail.com', 'role' => 'customer', 'is_suspended' => true]);
        $this->fakeGoogle('bad@gmail.com', 'g-9');
        $this->get(route('auth.google.callback'))->assertSessionHas('error');
        $this->assertGuest();

        $this->fakeGoogle('new@example.com', 'g-7', verified: false);
        $this->get(route('auth.google.callback'))->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }
}
