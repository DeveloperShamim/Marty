<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));
        $deviceFilter = trim((string) $request->input('device'));

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $realtimeThreshold = Carbon::now()->subMinutes(15);

        // 1. Realtime Active Visitors (Last 15 minutes)
        $activeRealtimeCount = DB::table('visitor_logs')
            ->where('updated_at', '>=', $realtimeThreshold)
            ->count();

        // 2. High Performance Single Query KPI Stats
        $stats = DB::table('visitor_logs')
            ->selectRaw("
                COUNT(CASE WHEN visit_date = ? THEN 1 END) as today_count,
                COUNT(CASE WHEN visit_date = ? THEN 1 END) as yesterday_count,
                COUNT(CASE WHEN visit_date >= ? THEN 1 END) as week_count,
                COUNT(CASE WHEN visit_date >= ? THEN 1 END) as month_count,
                COUNT(CASE WHEN device_type = 'mobile' OR user_agent LIKE '%Mobile%' OR user_agent LIKE '%Android%' OR user_agent LIKE '%iPhone%' THEN 1 END) as mobile_count,
                COUNT(*) as total_count
            ", [$today, $yesterday, $startOfWeek, $startOfMonth])
            ->first();

        $todayCount = (int) ($stats->today_count ?? 0);
        $yesterdayCount = (int) ($stats->yesterday_count ?? 0);
        $thisWeekCount = (int) ($stats->week_count ?? 0);
        $thisMonthCount = (int) ($stats->month_count ?? 0);
        $totalCount = (int) ($stats->total_count ?? 0);
        $mobileCount = (int) ($stats->mobile_count ?? 0);
        $desktopCount = max(0, $totalCount - $mobileCount);

        // 3. Ultra Fast Single Query 14-Day Traffic Trend
        $fourteenDaysAgo = Carbon::today()->subDays(13)->toDateString();
        $rawTrends = DB::table('visitor_logs')
            ->where('visit_date', '>=', $fourteenDaysAgo)
            ->groupBy('visit_date')
            ->selectRaw('visit_date, COUNT(*) as count')
            ->pluck('count', 'visit_date')
            ->all();

        $trendDates = collect(range(13, 0))->map(function ($daysAgo) use ($rawTrends) {
            $date = Carbon::today()->subDays($daysAgo)->toDateString();
            $count = (int) ($rawTrends[$date] ?? 0);
            return [
                'date' => Carbon::parse($date)->format('M d'),
                'raw_date' => $date,
                'count' => $count,
                'is_today' => $date === Carbon::today()->toDateString(),
            ];
        });

        $maxTrendCount = max(1, $trendDates->max('count'));

        // 4. Top Visited Landing Pages (Last 7 Days)
        $topPages = DB::table('visitor_logs')
            ->whereNotNull('page_url')
            ->where('page_url', '!=', '')
            ->where('visit_date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->groupBy('page_url')
            ->selectRaw('page_url, COUNT(*) as visits')
            ->orderByDesc('visits')
            ->limit(5)
            ->get();

        // 5. Top Referrers / Traffic Sources (Last 7 Days)
        $topReferrers = DB::table('visitor_logs')
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->where('visit_date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->groupBy('referer')
            ->selectRaw('referer, COUNT(*) as visits')
            ->orderByDesc('visits')
            ->limit(5)
            ->get();

        // 6. Paginated Visitor logs query
        $query = VisitorLog::latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('page_url', 'like', "%{$search}%")
                  ->orWhere('referer', 'like', "%{$search}%");
            });
        }

        if ($deviceFilter && in_array($deviceFilter, ['mobile', 'desktop', 'tablet'])) {
            $query->where('device_type', $deviceFilter);
        }

        $logs = $query->paginate(25)->withQueryString();

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
            'topPages',
            'topReferrers',
            'logs',
            'search',
            'deviceFilter'
        ));
    }

    /**
     * Prune logs older than a given number of days to keep database lightweight.
     */
    public function prune(Request $request): RedirectResponse
    {
        $days = max(7, min(365, (int) $request->input('days', 60)));
        $cutoffDate = Carbon::today()->subDays($days)->toDateString();

        $deletedCount = VisitorLog::where('visit_date', '<', $cutoffDate)->delete();

        return redirect()->route('admin.visitors.index')
            ->with('status', "Cleaned {$deletedCount} visitor logs older than {$days} days.");
    }
}
