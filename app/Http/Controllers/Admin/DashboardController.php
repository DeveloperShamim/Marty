<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\CustomerCourierCheck;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Courier\BdCourierService;
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

        // Top 12 revenue products (strictly excluding cancelled and returned orders), with units
        // sold in the last 30 days against the 30 days before for the trend badge
        $trendFrom = now()->subDays(30);
        $trendPrevFrom = now()->subDays(60);
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', ['cancelled', 'returned'])
            ->where(function ($q) {
                $q->where('orders.status', 'delivered')
                  ->orWhere('orders.payment_status', 'verified');
            })
            ->select('order_items.product_id', 'order_items.product_name')
            ->selectRaw('MAX(order_items.image) as image')
            ->selectRaw('SUM(order_items.quantity) as total_units')
            ->selectRaw('SUM(order_items.line_total) as total_revenue')
            ->selectRaw('SUM(CASE WHEN orders.created_at >= ? THEN order_items.quantity ELSE 0 END) as recent_units', [$trendFrom])
            ->selectRaw('SUM(CASE WHEN orders.created_at >= ? AND orders.created_at < ? THEN order_items.quantity ELSE 0 END) as previous_units', [$trendPrevFrom, $trendFrom])
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_revenue')
            ->take(12)
            ->get();

        // Attach Product model to get current stock and live status
        $productIds = $topProducts->pluck('product_id')->filter()->toArray();
        $productNames = $topProducts->pluck('product_name')->filter()->toArray();

        $liveProductsById = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $liveProductsByName = Product::whereIn('name', $productNames)->get()->keyBy('name');

        $topProducts->transform(function ($item) use ($liveProductsById, $liveProductsByName) {
            $item->product = ($item->product_id ? $liveProductsById->get($item->product_id) : null)
                ?? $liveProductsByName->get($item->product_name);
            $recent = (int) $item->recent_units;
            $previous = (int) $item->previous_units;
            // null = no sales in either window, 'new' = sold now but not before, otherwise a % change
            $item->trend = $previous > 0 ? (int) round(($recent - $previous) / $previous * 100) : ($recent > 0 ? 'new' : null);
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

        // Risky orders to call: not shipped yet, flagged by the order fraud score or the customer's courier history.
        $unshipped = Order::whereIn('status', ['pending', 'confirmed', 'processing'])->latest()->take(60)->get();
        $courierChecks = CustomerCourierCheck::whereIn('phone', $unshipped->map(fn ($o) => BdCourierService::normalizePhone($o->customer_phone))->filter()->unique())
            ->get()->keyBy('phone');
        $rank = ['low' => 0, 'new' => 0, 'medium' => 1, 'high' => 2];
        $riskyOrders = $unshipped->map(function ($order) use ($courierChecks, $rank) {
            $check = $courierChecks->get(BdCourierService::normalizePhone($order->customer_phone));
            $fraud = $order->fraudRiskLevel();
            $courier = $check?->riskLevel() ?? 'new';
            $order->risk = max($rank[$fraud] ?? 0, $rank[$courier] ?? 0);
            $order->riskReason = match (true) {
                $courier === 'high' || ($courier === 'medium' && $rank[$fraud] < 2) => $check->riskLabel() . ' (' . round($check->success_ratio) . '% delivered)',
                $fraud !== 'low' => $order->fraud_flags[0] ?? 'Fraud score ' . (int) $order->fraud_score,
                default => '',
            };
            return $order;
        })->filter(fn ($o) => $o->risk > 0)->sortByDesc('risk')->values();

        // Ad spend this month (Marketing & Facebook Ads expenses).
        $adSpend = (float) Expense::where('category', 'marketing')
            ->whereBetween('expense_date', [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()])
            ->sum('amount');
        $thisMonthOrders = Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->tap($validOrders)->count();

        // Carts to call back: abandoned in the last 3 days with a phone number.
        $callbackCarts = AbandonedCart::abandoned()->whereNotNull('customer_phone')->where('customer_phone', '!=', '')
            ->where('created_at', '>=', Carbon::now()->subDays(3));

        // Store performance: all orders, units sold on verified orders and distinct customers.
        $itemsSold = (int) OrderItem::whereHas('order', $validOrders)->sum('quantity');
        $customersCount = (int) Order::distinct()->count('customer_phone');

        // Headline tiles. Today counts every order placed today (cash on delivery is only "sold" once delivered,
        // so placed orders are what a COD store watches day to day); the month and its profit count realized sales.
        $placed = fn ($query) => $query->whereNotIn('status', ['cancelled']);
        $todayPlaced = Order::whereDate('created_at', Carbon::today())->tap($placed);
        $yesterdayPlaced = Order::whereDate('created_at', Carbon::yesterday())->tap($placed);
        $monthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = (clone $monthStart)->subMonth();
        $lastMonthRevenue = (float) Order::whereBetween('created_at', [$lastMonthStart, (clone $monthStart)->subSecond()])
            ->tap($validOrders)->sum(DB::raw('subtotal - discount_amount'));
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, (clone $monthStart)->subSecond()])->tap($validOrders)->count();
        $inMonth = fn ($q) => $q->where('created_at', '>=', $monthStart);
        $monthCogs = (float) OrderItem::whereHas('order', fn ($q) => $q->tap($validOrders)->tap($inMonth))
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->sum(DB::raw('COALESCE(NULLIF(order_items.cost_price, 0), products.cost_price, 0) * order_items.quantity'));
        $monthCourierLoss = (float) Order::where('status', 'returned')->tap($inMonth)->sum('courier_loss_amount');
        $monthFreeDelivery = (float) Order::query()->tap($validOrders)->tap($inMonth)->sum('shipping_waived');
        $monthExpenses = (float) Expense::whereBetween('expense_date', [$monthStart->toDateString(), Carbon::now()->endOfMonth()->toDateString()])->sum('amount');

        return view('admin.dashboard', [
            'ordersCount'          => $activeOrdersCount,
            'totalSalesOrdersCount'=> $verifiedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'returnedOrdersCount'  => $returnedOrdersCount,
            'deliveredCount'       => $deliveredOrdersCount,
            'pendingCount'         => Order::awaitingPayment()->count(),
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
            'pendingOrders'       => Order::awaitingPayment()->latest()->take(6)->get(),
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
            'shippedCount'        => $countOf('shipped'),
            'todayOrdersCount'    => Order::whereDate('created_at', Carbon::today())->tap($validOrders)->count(),
            'yesterdayOrdersCount'=> Order::whereDate('created_at', Carbon::yesterday())->tap($validOrders)->count(),
            'riskyOrders'         => $riskyOrders->take(5),
            'riskyCount'          => $riskyOrders->count(),
            'adSpend'             => $adSpend,
            'thisMonthOrders'     => $thisMonthOrders,
            'callbackCarts'       => (clone $callbackCarts)->latest()->take(5)->get(),
            'callbackCount'       => $callbackCarts->count(),
            'todayPlacedValue'    => (float) (clone $todayPlaced)->sum('total'),
            'todayPlacedCount'    => (clone $todayPlaced)->count(),
            'yesterdayPlacedValue'=> (float) (clone $yesterdayPlaced)->sum('total'),
            'yesterdayPlacedCount'=> (clone $yesterdayPlaced)->count(),
            'lastMonthRevenue'    => $lastMonthRevenue,
            'lastMonthOrders'     => $lastMonthOrders,
            'monthCogs'           => $monthCogs,
            'monthExpenses'       => $monthExpenses,
            'monthProfit'         => $thisMonthRevenue - $monthCogs - $monthCourierLoss - $monthFreeDelivery - $monthExpenses,
            'itemsSold'           => $itemsSold,
            'customersCount'      => $customersCount,
            'allOrdersCount'      => (int) $statusCounts->sum(),
        ]);
    }

    public function clearCache()
    {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        return redirect()->back()->with('status', 'Application cache cleared successfully!');
    }
}
