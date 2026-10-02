<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
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

        // Scope to count realized sales and profit strictly from delivered orders or verified payment (POS/prepaid)
        $validOrders = fn ($query) => $query->whereNotIn('status', ['cancelled', 'returned'])
            ->where(function ($q) {
                $q->where('status', 'delivered')
                  ->orWhere('payment_status', 'verified');
            });

        $revenue = (float) Order::query()
            ->tap($validOrders)
            ->sum(DB::raw('subtotal - discount_amount'));

        // Cost of Goods Sold (Buying Cost) for delivered / verified orders
        $totalCogs = (float) OrderItem::whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'returned'])
                    ->where(function ($q) {
                        $q->where('status', 'delivered')
                          ->orWhere('payment_status', 'verified');
                    });
            })
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->sum(DB::raw('COALESCE(NULLIF(order_items.cost_price, 0), products.cost_price, 0) * order_items.quantity'));

        $courierLoss = (float) Order::where('status', 'returned')->sum('courier_loss_amount');
        // Delivery fees the shop paid for free-delivery orders.
        $freeDeliveryCost = (float) Order::query()->tap($validOrders)->sum('shipping_waived');
        $profitBeforeExpenses = $revenue - $totalCogs - $courierLoss - $freeDeliveryCost;
        // All logged expenses (ads, packaging, rent…), so Net Profit is what the shop actually keeps.
        $totalExpenses = (float) Expense::sum('amount');
        // Can be negative: a loss must show as a loss, not as zero.
        $netProfit = $profitBeforeExpenses - $totalExpenses;
        $profitMargin = $revenue > 0 ? (($netProfit / $revenue) * 100) : 0;

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
                    ->where(function ($q) {
                        $q->where('status', 'delivered')
                          ->orWhere('payment_status', 'verified');
                    });
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

        // Years that have orders (min/max only — never load every order into memory)
        $firstOrderAt = Order::min('created_at');
        $lastOrderAt = Order::max('created_at');
        $dbYears = $firstOrderAt
            ? range((int) Carbon::parse($firstOrderAt)->format('Y'), (int) Carbon::parse($lastOrderAt)->format('Y'))
            : [];

        $defaultYears = range($currentYear - 5, $currentYear);
        $availableYears = array_unique(array_merge($dbYears, $defaultYears, [$selectedYear]));
        rsort($availableYears);

        if ($selectedYear === $currentYear) {
            $endMonth = Carbon::now()->startOfMonth();
        } else {
            $endMonth = Carbon::createFromDate($selectedYear, 12, 1)->startOfMonth();
        }

        // Build rolling 12-month series in ONE grouped query (was 12 queries)
        $windowStart = (clone $endMonth)->subMonths(11)->startOfMonth();
        $monthKey = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $monthlyTotals = Order::query()
            ->tap($validOrders)
            ->whereBetween('created_at', [$windowStart, (clone $endMonth)->endOfMonth()])
            ->selectRaw("{$monthKey} as month_key, SUM(subtotal - discount_amount) as total")
            ->groupBy(DB::raw($monthKey))
            ->pluck('total', 'month_key');

        $monthlySeries = collect(range(11, 0))->map(function ($monthsAgo) use ($endMonth, $monthlyTotals) {
            $monthDate = (clone $endMonth)->subMonths($monthsAgo);
            $year = (int) $monthDate->format('Y');
            $monthNumber = (int) $monthDate->format('m');

            $total = (float) ($monthlyTotals[$monthDate->format('Y-m')] ?? 0);

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

        // All status counts in one query
        $statusCounts = Order::query()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $countOf = fn (string ...$statuses) => (int) collect($statuses)->sum(fn ($st) => $statusCounts[$st] ?? 0);

        $activeOrdersCount = $countOf('pending', 'confirmed', 'processing', 'shipped');
        $cancelledOrdersCount = $countOf('cancelled');
        $returnedOrdersCount = $countOf('returned');
        $deliveredOrdersCount = $countOf('delivered');

        return view('admin.dashboard', [
            'ordersCount'          => $activeOrdersCount,
            'totalSalesOrdersCount'=> $verifiedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'returnedOrdersCount'  => $returnedOrdersCount,
            'deliveredCount'       => $deliveredOrdersCount,
            'pendingCount'         => Order::needsReview()->count(),
            'revenue'              => $revenue,
            'totalCogs'            => $totalCogs,
            'netProfit'            => $netProfit,
            'profitBeforeExpenses' => $profitBeforeExpenses,
            'totalExpenses'        => $totalExpenses,
            'profitMargin'         => $profitMargin,
            'todayRevenue'         => $todayRevenue,
            'yesterdayRevenue'    => $yesterdayRevenue,
            'thisMonthRevenue'    => $thisMonthRevenue,
            'avgOrderValue'       => $avgOrderValue,
            'productsCount'       => Product::count(),
            'totalStockUnits'     => $totalStockUnits,
            'lowStockProducts'    => $lowStockProducts,
            'lowStockCount'       => $lowStockCount,
            'outOfStockCount'     => $outOfStockCount,
            'pendingOrders'       => Order::needsReview()->latest()->take(6)->get(),
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
            'shippedCount'        => $countOf('shipped'),
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
