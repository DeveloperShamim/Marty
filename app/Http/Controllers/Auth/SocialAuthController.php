<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $this->setupGoogleConfig();
        // Staff start from the admin login page; the callback then signs them in to the admin panel.
        $request->session()->put('google_login_for', $request->query('for') === 'admin' ? 'admin' : 'shop');

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            if ($request->query('for') === 'admin') {
                return redirect()->route('admin.login')->withErrors(['email' => 'Google sign-in is not set up yet. Add the Client ID and secret in Integrations → Google login.']);
            }

            return redirect()->route('login')->with(
                'status',
                'Google 1-Click login requires GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET configured in Admin -> API Integrations or your .env file.'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $this->setupGoogleConfig();
        $forAdmin = $request->session()->pull('google_login_for') === 'admin';
        $fail = fn (string $message) => $forAdmin
            ? redirect()->route('admin.login')->withErrors(['email' => $message])
            : redirect()->route('login')->with('error', $message);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            report($e);

            return $fail('Google login failed or was cancelled. Please try again.');
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        // Google only returns verified addresses for normal accounts, but never link by an unverified one.
        $emailVerified = filter_var($googleUser->user['email_verified'] ?? true, FILTER_VALIDATE_BOOL);
        if ($email === '' || ! $emailVerified) {
            return $fail('Your Google account has no verified email address. Please sign in with your email and password instead.');
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($forAdmin) {
            return $this->signInStaff($request, $user, $googleUser, $email, $fail);
        }

        // Staff sign in through the admin login page, never through the shop's Google button.
        if ($user && ($user->isStaff() || $user->isAdmin())) {
            return redirect()->route('login')->with('error', 'This is a staff account. Please use Continue with Google on the admin login page.');
        }
        if ($user && $user->is_suspended) {
            return redirect()->route('login')->with('error', 'This account has been suspended. Please contact support.');
        }

        // forceFill: email_verified_at is not mass-assignable on User, and Google has verified the address.
        if ($user) {
            $user->forceFill([
                'google_id'         => $googleUser->getId(),
                'avatar'            => $user->avatar ?: $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = (new User())->forceFill([
                'name'              => $googleUser->getName() ?: 'Google User',
                'email'             => $email,
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'customer',
                'email_verified_at' => now(),
            ]);
            $user->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        // Back to where they were (e.g. checkout), otherwise the account page.
        return redirect()->intended(route('account'))->with('status', 'Successfully logged in with Google!');
    }

    /** Staff whose account email matches the Google account go straight to the admin panel. */
    private function signInStaff(Request $request, ?User $user, $googleUser, string $email, \Closure $fail)
    {
        if (! $user || ! $user->isStaff()) {
            return $fail($user && $user->is_suspended
                ? 'Your staff account has been suspended. Please contact the administrator.'
                : "No staff account uses {$email}. Sign in with your password, or ask the owner to set this email on your staff account.");
        }

        $user->forceFill(['google_id' => $googleUser->getId(), 'avatar' => $user->avatar ?: $googleUser->getAvatar()])->save();

        Auth::login($user, true);
        $request->session()->regenerate();
        \App\Services\ActivityLogger::log('Staff Login', 'Logged into Admin Panel with Google');

        return redirect()->intended(\App\Support\StaffAccess::home($user));
    }

    private function setupGoogleConfig(): void
    {
        $clientId = setting('google_client_id') ?: config('services.google.client_id');
        $clientSecret = setting('google_client_secret') ?: config('services.google.client_secret');

        if ($clientId) {
            config(['services.google.client_id' => $clientId]);
        }
        if ($clientSecret) {
            config(['services.google.client_secret' => $clientSecret]);
        }
        // Always the live domain's callback, so it matches what the admin page tells you to give Google.
        config(['services.google.redirect' => google_callback_url()]);
    }
}
