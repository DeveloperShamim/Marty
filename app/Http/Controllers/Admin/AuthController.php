<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->isStaff()) {
                $errorMsg = $user->is_suspended
                    ? 'Your staff account has been suspended. Please contact the administrator.'
                    : 'This account does not have staff permissions.';

                Auth::logout();

                return back()->withErrors(['email' => $errorMsg])->onlyInput('email');
            }

            $request->session()->regenerate();
            \App\Services\ActivityLogger::log('Staff Login', "Logged into Admin Panel");

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
