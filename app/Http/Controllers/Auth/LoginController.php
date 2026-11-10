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
            if (self::isSafeRedirect($redirect)) {
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

    /**
     * A path on this site ("/checkout"), or a full URL whose host is exactly this site's host.
     * "//evil.com", "/\\evil.com" and "https://vantbd.com.evil.com" are refused (they leave the site).
     */
    public static function isSafeRedirect(string $target): bool
    {
        if (preg_match('/[\x00-\x1F\x7F\\\\]/', $target)) {
            return false;
        }
        if (str_starts_with($target, '/')) {
            return ! str_starts_with($target, '//');
        }

        $parts = parse_url($target);
        $site = parse_url(url('/'));

        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && isset($parts['host'], $site['host'])
            && strtolower($parts['host']) === strtolower($site['host'])
            && ! isset($parts['user'])
            && ($parts['port'] ?? null) === ($site['port'] ?? null);
    }
}
