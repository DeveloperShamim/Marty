<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isStaff()) {
            $msg = (Auth::check() && Auth::user()->is_suspended)
                ? 'Your staff account has been suspended. Please contact the administrator.'
                : 'Please sign in with a staff account.';

            if (Auth::check() && Auth::user()->is_suspended) {
                Auth::logout();
            }

            return redirect()->route('admin.login')->withErrors([
                'email' => $msg,
            ]);
        }

        return $next($request);
    }
}
