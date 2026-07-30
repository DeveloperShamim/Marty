<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && $redirect !== '') {
            // Only allow same-app relative paths or current-app absolute URLs.
            if (str_starts_with($redirect, '/') || str_starts_with($redirect, url('/'))) {
                $request->session()->put('url.intended', $redirect);
            }
        }

        return view('storefront.auth.login');
    }

    public function login(Request $request, OtpService $otp)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        // Admins who use the storefront login go straight to the panel.
        if ($user->isAdmin()) {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        // Unverified customers must confirm their email via OTP first.
        if (otp_enabled() && ! $user->hasVerifiedEmail()) {
            Auth::logout();
            $code = $otp->send($user->email, 'register', $user->name);
            $request->session()->put('otp_email', $user->email);
            $request->session()->put('otp_purpose', 'register');

            return redirect()->route('verify')
                ->with('status', 'Please verify your email to continue.')
                ->with('dev_otp', app()->environment('local') ? $code : null);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been signed out.');
    }
}
