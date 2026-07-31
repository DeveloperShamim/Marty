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
            return redirect()->route('login')->with('error', 'Google login failed or was cancelled. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $user->avatar ?: $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name'              => $googleUser->getName() ?: 'Google User',
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'password'          => Hash::make(Str::random(24)),
                'role'              => 'customer',
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('account')->with('status', 'Successfully logged in with Google!');
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
