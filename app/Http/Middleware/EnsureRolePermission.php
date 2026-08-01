<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRolePermission
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user || !$user->isStaff()) {
            return redirect()->route('admin.login');
        }

        // Super Admin can access everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Store Manager can access both Order Manager and Inventory Manager routes
        if ($user->role === 'store_manager' && (in_array('order_manager', $roles, true) || in_array('inventory_manager', $roles, true))) {
            return $next($request);
        }

        // Check if user's role is allowed for this route
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')->with('error', 'Access Denied: You do not have permission to access that section.');
    }
}
