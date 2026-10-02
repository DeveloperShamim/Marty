<?php

namespace App\Http\Middleware;

use App\Support\StaffAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** route middleware "area:orders": the signed-in staff member's role must be allowed that area. */
class EnsureStaffArea
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();
        if (StaffAccess::allows($user, $area)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission for this.'], 403);
        }

        return redirect()->to($user ? StaffAccess::home($user) : route('admin.login'))
            ->with('error', 'Access denied: your role cannot open that page.');
    }
}
