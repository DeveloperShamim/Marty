<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(?Request $request = null)
    {
        $request = $request ?? request();

        // Scope to ignore cancelled orders across all financial & order analytics
        $validOrders = fn ($query) => $query->where('status', '!=', 'cancelled');

        $revenue = (float) Order::where('payment_status', 'verified')
            ->tap($validOrders)
            ->sum('total');

        $verifiedOrdersCount = Order::where('payment_status', 'verified')
            ->tap($validOrders)
            ->count();

        // Today's revenue
        $todayRevenue = (float) Order::whereDate('created_at', Carbon::today())
            ->where('payment_status', 'verified')
            ->tap($validOrders)
            ->sum('total');

        // Yesterday's revenue for comparison
        $yesterdayRevenue = (float) Order::whereDate('created_at', Carbon::yesterday())
            ->where('payment_status', 'verified')
            ->tap($validOrders)
            ->sum('total');

        // This Month's revenue
        $thisMonthRevenue = (float) Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('payment_status', 'verified')
            ->tap($validOrders)
            ->sum('total');

        // Average Order Value (AOV)
        $avgOrderValue = $verifiedOrdersCount > 0 ? ($revenue / $verifiedOrdersCount) : 0;

        // Top 5 Revenue-Generating Products (excluding cancelled orders)
        $topProductsQuery = OrderItem::query()
            ->select(
                'product_id',
                'product_name',
                'image',
                DB::raw('SUM(quantity) as total_units'),
                DB::raw('SUM(line_total) as total_revenue')
            )
            ->groupBy('product_id', 'product_name', 'image')
            ->orderByDesc('total_revenue')
            ->take(5);

        $topProducts = (clone $topProductsQuery)
            ->whereHas('order', function ($query) {
                $query->where('payment_status', 'verified')->where('status', '!=', 'cancelled');
            })
            ->get();

        if ($topProducts->isEmpty()) {
            $topProducts = $topProductsQuery->get();
        }

        // Attach Product model to get current stock and live status
        $productIds = $topProducts->pluck('product_id')->filter();
        $liveProducts = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $topProducts->transform(function ($item) use ($liveProducts) {
            $item->product = $liveProducts->get($item->product_id);
            return $item;
        });

        // Determine End Date for the 12-month rolling window
        $currentYear = (int) date('Y');
        $selectedYear = (int) $request->input('year', $currentYear);

        // Collect years from database + default past 5 years range
        $dbYears = Order::all()
            ->map(fn($o) => (int) $o->created_at->format('Y'))
            ->unique()
            ->filter()
            ->toArray();

        $defaultYears = range($currentYear - 5, $currentYear);
        $availableYears = array_unique(array_merge($dbYears, $defaultYears, [$selectedYear]));
        rsort($availableYears);

        if ($selectedYear === $currentYear) {
            $endMonth = Carbon::now()->startOfMonth();
        } else {
            $endMonth = Carbon::createFromDate($selectedYear, 12, 1)->startOfMonth();
        }

        // Build rolling 12-month series (excluding cancelled orders)
        $monthlySeries = collect(range(11, 0))->map(function ($monthsAgo) use ($endMonth, $validOrders) {
            $monthDate = (clone $endMonth)->subMonths($monthsAgo);
            $year = (int) $monthDate->format('Y');
            $monthNumber = (int) $monthDate->format('m');

            $total = (float) Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $monthNumber)
                ->where('payment_status', 'verified')
                ->tap($validOrders)
                ->sum('total');

            return [
                'label'      => $monthDate->format('M'),
                'full_label' => $monthDate->format('F Y'),
                'year'       => $year,
                'month'      => $monthNumber,
                'value'      => $total,
                'is_current' => $monthDate->isCurrentMonth(),
            ];
        });

        $peakMonth = $monthlySeries->sortByDesc('value')->first() ?? ['full_label' => 'N/A', 'value' => 0];
        $totalSeriesRevenue = (float) $monthlySeries->sum('value');

        return view('admin.dashboard', [
            'ordersCount'         => Order::where('status', '!=', 'cancelled')->count(),
            'cancelledOrdersCount'=> Order::where('status', 'cancelled')->count(),
            'pendingCount'        => Order::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count(),
            'revenue'             => $revenue,
            'todayRevenue'        => $todayRevenue,
            'yesterdayRevenue'    => $yesterdayRevenue,
            'thisMonthRevenue'    => $thisMonthRevenue,
            'avgOrderValue'       => $avgOrderValue,
            'productsCount'       => Product::count(),
            'pendingOrders'       => Order::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->latest()->take(6)->get(),
            'recentOrders'        => Order::latest()->take(8)->get(),
            'topProducts'         => $topProducts,
            'selectedYear'        => $selectedYear,
            'availableYears'      => $availableYears,
            'monthlySeries'       => $monthlySeries,
            'seriesMax'           => max(1, $monthlySeries->max('value')),
            'firstMonthLabel'     => $monthlySeries->first()['full_label'] ?? '',
            'lastMonthLabel'      => $monthlySeries->last()['full_label'] ?? '',
            'peakMonth'           => $peakMonth,
            'totalSeriesRevenue'  => $totalSeriesRevenue,
            'dispatchedCount'     => Order::whereNotNull('courier_name')->count(),
            'shippedCount'        => Order::where('status', 'shipped')->count(),
            'deliveredCount'      => Order::where('status', 'delivered')->count(),
            'visitorsToday'       => \App\Models\VisitorLog::whereDate('visit_date', Carbon::today())->count(),
            'visitorsYesterday'   => \App\Models\VisitorLog::whereDate('visit_date', Carbon::yesterday())->count(),
        ]);
    }

    public function clearCache()
    {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        return redirect()->back()->with('status', 'Application cache cleared successfully!');
    }
}
