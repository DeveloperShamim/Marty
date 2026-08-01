<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));

        // Realtime Active Visitors (Last 15 minutes)
        $activeRealtimeCount = VisitorLog::where('updated_at', '>=', Carbon::now()->subMinutes(15))->count();

        // KPI Counts
        $todayCount     = VisitorLog::whereDate('visit_date', Carbon::today())->count();
        $yesterdayCount = VisitorLog::whereDate('visit_date', Carbon::yesterday())->count();
        $thisWeekCount  = VisitorLog::where('visit_date', '>=', Carbon::now()->startOfWeek())->count();
        $thisMonthCount = VisitorLog::where('visit_date', '>=', Carbon::now()->startOfMonth())->count();
        $totalCount     = VisitorLog::count();

        // Device Breakdown (Basic Parsing)
        $mobileCount  = VisitorLog::where('user_agent', 'like', '%Mobile%')->orWhere('user_agent', 'like', '%Android%')->orWhere('user_agent', 'like', '%iPhone%')->count();
        $desktopCount = max(0, $totalCount - $mobileCount);

        // 14-Day Traffic Trend
        $trendDates = collect(range(13, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo)->toDateString();
            $count = VisitorLog::whereDate('visit_date', $date)->count();
            return [
                'date' => Carbon::parse($date)->format('M d'),
                'raw_date' => $date,
                'count' => $count,
                'is_today' => $date === Carbon::today()->toDateString(),
            ];
        });

        $maxTrendCount = max(1, $trendDates->max('count'));

        // Visitor logs query
        $query = VisitorLog::latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.visitors.index', compact(
            'activeRealtimeCount',
            'mobileCount',
            'desktopCount',
            'todayCount',
            'yesterdayCount',
            'thisWeekCount',
            'thisMonthCount',
            'totalCount',
            'trendDates',
            'maxTrendCount',
            'logs',
            'search'
        ));
    }
}
