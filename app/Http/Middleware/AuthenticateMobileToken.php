<?php

namespace App\Http\Middleware;

use App\Models\MobileApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Authenticates staff from the admin mobile app via "Authorization: Bearer <token>". */
class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $plain = $request->bearerToken();
        $token = $plain ? MobileApiToken::findByPlain($plain) : null;
        $user = $token?->user;

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->isStaff()) {
            $token->delete();

            return response()->json(['message' => $user->is_suspended
                ? 'Your staff account has been suspended. Please contact the administrator.'
                : 'This account does not have staff permissions.'], 403);
        }

        if ($permission === 'orders' && ! $user->isOrderManager()) {
            return response()->json(['message' => 'Access Denied: You do not have permission to manage orders.'], 403);
        }

        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinutes(5))) {
            $token->forceFill(['last_used_at' => now()])->save();
        }

        Auth::setUser($user);
        $request->attributes->set('mobile_token', $token);

        return $next($request);
    }
}
