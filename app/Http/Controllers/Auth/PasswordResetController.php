<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function showRequest()
    {
        return view('storefront.auth.forgot');
    }

    public function sendCode(Request $request, OtpService $otp)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->input('email'))->first();
        $email = $user?->email ?? strtolower(trim((string) $request->input('email')));

        // Same answer whether or not the account exists, so the form can't be used to find out who shops here.
        $code = $user ? $otp->send($user->email, 'reset', $user->name) : null;
        $request->session()->put('otp_email', $email);
        $request->session()->put('otp_purpose', 'reset');

        return redirect()->route('verify')
            ->with('status', 'If an account uses that email, we sent it a password reset code.')
            ->with('dev_otp', app()->environment('local') ? $code : null);
    }

    public function showReset(Request $request)
    {
        if (! $request->session()->has('reset_verified')) {
            return redirect()->route('password.request');
        }

        return view('storefront.auth.reset', [
            'email' => $request->session()->get('reset_verified'),
        ]);
    }

    public function reset(Request $request)
    {
        $email = $request->session()->get('reset_verified');
        if (! $email) {
            return redirect()->route('password.request');
        }

        $request->validate(['password' => ['required', 'confirmed', Password::min(8)]]);

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['password' => $request->input('password')]); // hashed by cast
        // Old remember-me cookies stop working; other signed-in browsers are signed out by AuthenticateSession.
        $user->setRememberToken(\Illuminate\Support\Str::random(60));
        $user->save();
        $request->session()->forget('reset_verified');

        Auth::login($user);

        return redirect()->route('account')->with('status', 'Your password has been updated.');
    }
}
