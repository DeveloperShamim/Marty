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
    public function redirectToGoogle()
    {
        $this->setupGoogleConfig();

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
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

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('login')->with('error', 'Google login failed or was cancelled. Please try again.');
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        // Google only returns verified addresses for normal accounts, but never link by an unverified one.
        $emailVerified = filter_var($googleUser->user['email_verified'] ?? true, FILTER_VALIDATE_BOOL);
        if ($email === '' || ! $emailVerified) {
            return redirect()->route('login')->with('error', 'Your Google account has no verified email address. Please sign up with email instead.');
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        // Staff sign in through the admin login (with its own throttling), never through the shop's Google button.
        if ($user && ($user->isStaff() || $user->isAdmin())) {
            return redirect()->route('login')->with('error', 'This is a staff account. Please sign in from the admin login page.');
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

    private function setupGoogleConfig(): void
    {
        $clientId = setting('google_client_id') ?: config('services.google.client_id');
        $clientSecret = setting('google_client_secret') ?: config('services.google.client_secret');
        $redirect = setting('google_redirect_uri') ?: config('services.google.redirect');

        if ($clientId) {
            config(['services.google.client_id' => $clientId]);
        }
        if ($clientSecret) {
            config(['services.google.client_secret' => $clientSecret]);
        }
        if ($redirect) {
            config(['services.google.redirect' => $redirect]);
        }
    }
}
