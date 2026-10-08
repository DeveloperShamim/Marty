<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\VisitorLog;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $verified = fn () => Order::where('payment_status', 'verified')->where('status', '!=', 'cancelled');

        return response()->json([
            'revenue' => [
                'total'      => (float) $verified()->sum('total'),
                'today'      => (float) $verified()->whereDate('created_at', Carbon::today())->sum('total'),
                'yesterday'  => (float) $verified()->whereDate('created_at', Carbon::yesterday())->sum('total'),
                'this_month' => (float) $verified()->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
            ],
            'orders' => [
                'today'                => Order::whereDate('created_at', Carbon::today())->count(),
                'active'               => Order::where('status', '!=', 'cancelled')->count(),
                'pending_verification' => Order::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count(),
                'pending'              => Order::where('status', 'pending')->count(),
                'confirmed'            => Order::where('status', 'confirmed')->count(),
                'processing'           => Order::where('status', 'processing')->count(),
                'shipped'              => Order::where('status', 'shipped')->count(),
                'delivered'            => Order::where('status', 'delivered')->count(),
                'cancelled'            => Order::where('status', 'cancelled')->count(),
                'dispatched'           => Order::whereNotNull('courier_name')->count(),
            ],
            'inventory' => [
                'products'     => Product::count(),
                'low_stock'    => Product::where('stock_quantity', '<=', 3)->count(),
                'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            ],
            'visitors' => [
                'today'     => VisitorLog::whereDate('visit_date', Carbon::today())->count(),
                'yesterday' => VisitorLog::whereDate('visit_date', Carbon::yesterday())->count(),
            ],
            'recent_orders' => OrderResource::collection(Order::latest()->take(8)->get()),
        ]);
    }
}
