<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'this_month');
        $channel = $request->input('channel', 'all');

        [$startDate, $endDate, $rangeLabel] = $this->resolveDateRange($range, $request);

        // Base Orders Query in Date Range with Indexes
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled']);

        if ($channel === 'online') {
            $ordersQuery->where('order_type', '!=', 'pos');
        } elseif ($channel === 'pos') {
            $ordersQuery->where('order_type', 'pos');
        }

        // 1. Overall Financials
        $totalOrdersCount = (clone $ordersQuery)->count();
        $deliveredOrVerified = (clone $ordersQuery)->where(function ($q) {
            $q->where('payment_status', 'verified')
              ->orWhereIn('status', ['delivered', 'shipped', 'processing']);
        });

        $grossRevenue = (float) (clone $deliveredOrVerified)->sum('total');
        $totalDiscounts = (float) (clone $deliveredOrVerified)->sum('discount_amount');
        $netRevenue = max(0, $grossRevenue);

        // AOV (Average Order Value)
        $aov = $totalOrdersCount > 0 ? ($grossRevenue / $totalOrdersCount) : 0;

        // 2. High-Performance COGS (Uses subquery: O(1) memory instead of loading all IDs into PHP)
        $cogs = (float) OrderItem::whereIn('order_id', (clone $deliveredOrVerified)->select('id'))
            ->sum(DB::raw('cost_price * quantity'));

        $grossProfit = $netRevenue - $cogs;

        // 3. Courier Returns & Losses (Type 2: Unpaid Delivery Loss)
        $returnedOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'returned');
        
        $returnedCount = (clone $returnedOrders)->count();
        $courierLoss = (float) (clone $returnedOrders)->sum('courier_loss_amount');
        $paidReturnsCount = (clone $returnedOrders)->where('return_type', 'paid_delivery')->count();
        $unpaidReturnsCount = (clone $returnedOrders)->where('return_type', 'unpaid_delivery')->count();

        // Net Profit (Factoring in Courier Loss)
        $netProfit = $grossProfit - $courierLoss;
        $profitMargin = $grossRevenue > 0 ? (($netProfit / $grossRevenue) * 100) : 0;

        // 4. Sales Channel Breakdown (Online vs POS)
        $onlineRevenue = (float) Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_type', '!=', 'pos')
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');
        $onlineOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_type', '!=', 'pos')
            ->whereNotIn('status', ['cancelled'])
            ->count();
        $onlineAov = $onlineOrdersCount > 0 ? ($onlineRevenue / $onlineOrdersCount) : 0;

        $posRevenue = (float) Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_type', 'pos')
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');
        $posOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('order_type', 'pos')
            ->whereNotIn('status', ['cancelled'])
            ->count();
        $posAov = $posOrdersCount > 0 ? ($posRevenue / $posOrdersCount) : 0;

        // 5. High-Speed Trend Series (Aggregated via single SQL query instead of N-loops)
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        $trendData = [];

        if ($daysDiff <= 31) {
            // Group by Day in 2 single queries
            $dailyStats = (clone $ordersQuery)
                ->select(
                    DB::raw('DATE(created_at) as date_key'),
                    DB::raw('SUM(total) as day_revenue'),
                    DB::raw('COUNT(*) as day_orders'),
                    DB::raw('SUM(courier_loss_amount) as day_loss')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy('date_key');

            $dailyCogs = OrderItem::whereIn('order_id', (clone $ordersQuery)->select('id'))
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    DB::raw('DATE(orders.created_at) as date_key'),
                    DB::raw('SUM(order_items.cost_price * order_items.quantity) as day_cogs')
                )
                ->groupBy(DB::raw('DATE(orders.created_at)'))
                ->get()
                ->keyBy('date_key');

            $period = Carbon::parse($startDate)->toPeriod($endDate);
            foreach ($period as $date) {
                $dayStr = $date->format('Y-m-d');
                $stat = $dailyStats->get($dayStr);
                $cogsRow = $dailyCogs->get($dayStr);

                $dayRev = $stat ? (float)$stat->day_revenue : 0;
                $dayCount = $stat ? (int)$stat->day_orders : 0;
                $dayC = $cogsRow ? (float)$cogsRow->day_cogs : 0;
                $dayL = $stat ? (float)$stat->day_loss : 0;
                $dayProfit = ($dayRev - $dayC) - $dayL;

                $trendData[] = [
                    'label'   => $date->format('d M'),
                    'revenue' => round($dayRev, 2),
                    'profit'  => round($dayProfit, 2),
                    'orders'  => $dayCount,
                    'aov'     => $dayCount > 0 ? round($dayRev / $dayCount, 2) : 0,
                ];
            }
        } else {
            // Group by Month in 2 single queries
            $monthlyStats = (clone $ordersQuery)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as date_key"),
                    DB::raw('SUM(total) as m_revenue'),
                    DB::raw('COUNT(*) as m_orders'),
                    DB::raw('SUM(courier_loss_amount) as m_loss')
                )
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->get()
                ->keyBy('date_key');

            $monthlyCogs = OrderItem::whereIn('order_id', (clone $ordersQuery)->select('id'))
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m') as date_key"),
                    DB::raw('SUM(order_items.cost_price * order_items.quantity) as m_cogs')
                )
                ->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m')"))
                ->get()
                ->keyBy('date_key');

            $startM = $startDate->copy()->startOfMonth();
            $endM = $endDate->copy()->endOfMonth();
            while ($startM->lte($endM)) {
                $monthKey = $startM->format('Y-m');
                $stat = $monthlyStats->get($monthKey);
                $cogsRow = $monthlyCogs->get($monthKey);

                $mRev = $stat ? (float)$stat->m_revenue : 0;
                $mCount = $stat ? (int)$stat->m_orders : 0;
                $mC = $cogsRow ? (float)$cogsRow->m_cogs : 0;
                $mL = $stat ? (float)$stat->m_loss : 0;
                $mProfit = ($mRev - $mC) - $mL;

                $trendData[] = [
                    'label'   => $startM->format('M Y'),
                    'revenue' => round($mRev, 2),
                    'profit'  => round($mProfit, 2),
                    'orders'  => $mCount,
                    'aov'     => $mCount > 0 ? round($mRev / $mCount, 2) : 0,
                ];

                $startM->addMonth();
            }
        }

        // 6. Top 10 Most Profitable Products (Subquery index lookup)
        $topProfitable = OrderItem::whereIn('order_id', (clone $deliveredOrVerified)->select('id'))
            ->select(
                'product_id',
                'product_name',
                'image',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('SUM(cost_price * quantity) as total_cost'),
                DB::raw('SUM(line_total - (cost_price * quantity)) as total_profit')
            )
            ->groupBy('product_id', 'product_name', 'image')
            ->orderByDesc('total_profit')
            ->take(10)
            ->get();

        return view('admin.analytics.index', compact(
            'range', 'channel', 'rangeLabel', 'startDate', 'endDate',
            'grossRevenue', 'netRevenue', 'totalDiscounts', 'cogs',
            'grossProfit', 'netProfit', 'profitMargin', 'aov', 'totalOrdersCount',
            'returnedCount', 'courierLoss', 'paidReturnsCount', 'unpaidReturnsCount',
            'onlineRevenue', 'onlineOrdersCount', 'onlineAov',
            'posRevenue', 'posOrdersCount', 'posAov',
            'trendData', 'topProfitable'
        ));
    }

    public function exportCsv(Request $request)
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request);

        // Stream cursor chunk to prevent PHP memory exhaustion on large exports
        $filename = "sales_profit_report_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order Number', 'Date', 'Channel', 'Customer Name', 'Customer Phone',
                'Payment Method', 'Payment Status', 'Fulfillment Status',
                'Subtotal', 'Discount', 'Shipping', 'Total Revenue',
                'COGS (Cost)', 'Gross Profit', 'Courier Loss', 'Net Profit'
            ]);

            Order::with('items')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotIn('status', ['cancelled'])
                ->latest('created_at')
                ->chunk(200, function ($orders) use ($file) {
                    foreach ($orders as $ord) {
                        $cogs = (float) $ord->items->sum(fn($it) => $it->cost_price * $it->quantity);
                        $grossProfit = (float) ($ord->total - $cogs);
                        $courierLoss = (float) ($ord->courier_loss_amount ?: 0);
                        $netProfit = $grossProfit - $courierLoss;

                        fputcsv($file, [
                            $ord->order_number,
                            $ord->created_at->format('Y-m-d H:i:s'),
                            strtoupper($ord->order_type ?: 'online'),
                            $ord->customer_name,
                            $ord->customer_phone,
                            strtoupper($ord->payment_method),
                            ucfirst($ord->payment_status),
                            ucfirst($ord->status),
                            $ord->subtotal,
                            $ord->discount_amount,
                            $ord->shipping_charge,
                            $ord->total,
                            $cogs,
                            $grossProfit,
                            $courierLoss,
                            $netProfit,
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resolveDateRange(string $range, Request $request): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Today (' . $start->format('d M Y') . ')';
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $label = 'Yesterday (' . $start->format('d M Y') . ')';
                break;
            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Last 7 Days (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;
            case 'last_30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $label = 'Last 30 Days (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $label = 'Last Month (' . $start->format('F Y') . ')';
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $label = 'Year ' . $now->year;
                break;
            case 'custom':
                $customStart = $request->input('start_date');
                $customEnd = $request->input('end_date');
                $start = $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->startOfMonth();
                $end = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfDay();
                $label = $start->format('d M Y') . ' - ' . $end->format('d M Y');
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $label = 'This Month (' . $start->format('F Y') . ')';
                break;
        }

        return [$start, $end, $label];
    }
}
