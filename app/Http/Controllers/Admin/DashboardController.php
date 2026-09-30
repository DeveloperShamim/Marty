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
        $user = auth()->user();
        if ($user && $user->role === 'order_manager') {
            return redirect()->route('admin.orders.index');
        }

        $request = $request ?? request();

        // Scope to count sales revenue from valid, non-cancelled, non-returned orders
        $validOrders = fn ($query) => $query->whereNotIn('status', ['cancelled', 'returned'])
            ->where('payment_status', '!=', 'rejected');

        $revenue = (float) Order::query()
            ->tap($validOrders)
            ->sum(DB::raw('subtotal - discount_amount'));

        $verifiedOrdersCount = Order::query()
            ->tap($validOrders)
            ->count();

        // Today's revenue
        $todayRevenue = (float) Order::whereDate('created_at', Carbon::today())
            ->tap($validOrders)
            ->sum(DB::raw('subtotal - discount_amount'));

        // Yesterday's revenue for comparison
        $yesterdayRevenue = (float) Order::whereDate('created_at', Carbon::yesterday())
            ->tap($validOrders)
            ->sum(DB::raw('subtotal - discount_amount'));

        // This Month's revenue
        $thisMonthRevenue = (float) Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->tap($validOrders)
            ->sum(DB::raw('subtotal - discount_amount'));

        // Average Order Value (AOV)
        $avgOrderValue = $verifiedOrdersCount > 0 ? ($revenue / $verifiedOrdersCount) : 0;

        // Top 5 Revenue-Generating Products (strictly excluding cancelled and returned orders)
        $topProducts = OrderItem::query()
            ->select(
                'product_id',
                'product_name',
                'image',
                DB::raw('SUM(quantity) as total_units'),
                DB::raw('SUM(line_total) as total_revenue')
            )
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'returned'])
                    ->where('payment_status', '!=', 'rejected');
            })
            ->groupBy('product_id', 'product_name', 'image')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // Attach Product model to get current stock and live status
        $productIds = $topProducts->pluck('product_id')->filter()->toArray();
        $productNames = $topProducts->pluck('product_name')->filter()->toArray();

        $liveProductsById = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $liveProductsByName = Product::whereIn('name', $productNames)->get()->keyBy('name');

        $topProducts->transform(function ($item) use ($liveProductsById, $liveProductsByName) {
            $item->product = ($item->product_id ? $liveProductsById->get($item->product_id) : null)
                ?? $liveProductsByName->get($item->product_name);
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
                ->tap($validOrders)
                ->sum(DB::raw('subtotal - discount_amount'));

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

        // Inventory / Live Stock Analytics
        $totalStockUnits = (int) Product::sum('stock_quantity');
        $lowStockProducts = Product::where('stock_quantity', '<=', 3)->latest()->take(5)->get();
        $lowStockCount = Product::where('stock_quantity', '<=', 3)->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $activeOrdersCount = Order::whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->count();
        $returnedOrdersCount = Order::where('status', 'returned')->count();
        $deliveredOrdersCount = Order::where('status', 'delivered')->count();

        return view('admin.dashboard', [
            'ordersCount'          => $activeOrdersCount,
            'totalSalesOrdersCount'=> $verifiedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'returnedOrdersCount'  => $returnedOrdersCount,
            'deliveredCount'       => $deliveredOrdersCount,
            'pendingCount'         => Order::where('payment_status', 'pending')->whereNotIn('status', ['cancelled', 'returned'])->count(),
            'revenue'             => $revenue,
            'todayRevenue'        => $todayRevenue,
            'yesterdayRevenue'    => $yesterdayRevenue,
            'thisMonthRevenue'    => $thisMonthRevenue,
            'avgOrderValue'       => $avgOrderValue,
            'productsCount'       => Product::count(),
            'totalStockUnits'     => $totalStockUnits,
            'lowStockProducts'    => $lowStockProducts,
            'lowStockCount'       => $lowStockCount,
            'outOfStockCount'     => $outOfStockCount,
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
            'todayOrdersCount'    => Order::whereDate('created_at', Carbon::today())->tap($validOrders)->count(),
            'yesterdayOrdersCount'=> Order::whereDate('created_at', Carbon::yesterday())->tap($validOrders)->count(),
        ]);
    }

    public function clearCache()
    {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        return redirect()->back()->with('status', 'Application cache cleared successfully!');
    }
}
