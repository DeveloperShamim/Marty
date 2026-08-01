<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VisitorLog;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only track GET requests that are not AJAX and not API/Admin routes
        if ($request->isMethod('GET') && !$request->ajax() && !$request->is('admin*') && !$request->is('api*')) {
            try {
                $ip = $request->ip();
                $date = today()->toDateString();
                
                // Refresh updated_at on every page load for realtime tracking
                $existing = DB::table('visitor_logs')->where('ip_address', $ip)->where('visit_date', $date)->first();
                
                if ($existing) {
                    DB::table('visitor_logs')->where('id', $existing->id)->update([
                        'updated_at' => now(),
                        'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                    ]);
                } else {
                    DB::table('visitor_logs')->insert([
                        'ip_address' => $ip,
                        'visit_date' => $date,
                        'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Silently fail if tracking errors out, so it doesn't break the storefront
            }
        }

        return $next($request);
    }
}
