<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CourierScanController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Today's dispatched orders
        $dispatchedToday = Order::whereDate('courier_sent_at', $today)
            ->where('status', 'shipped')
            ->latest('courier_sent_at')
            ->get();

        // Today's returned orders
        $returnedToday = Order::whereDate('courier_returned_at', $today)
            ->where('status', 'returned')
            ->latest('courier_returned_at')
            ->get();

        $courierOptions = [
            'steadfast' => 'Steadfast Courier',
            'pathao'    => 'Pathao Courier',
            'redx'      => 'RedX Courier',
            'paperfly'  => 'Paperfly',
            'in_house'  => 'In-House Rider',
        ];

        return view('admin.courier-scan.index', compact('dispatchedToday', 'returnedToday', 'courierOptions'));
    }

    public function dispatchScan(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        $courierName = trim((string) $request->input('courier_name', 'steadfast'));

        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please scan or enter a barcode.']);
        }

        // Match by order_number or courier_tracking_code
        $order = Order::where('order_number', $code)
            ->orWhere('courier_tracking_code', $code)
            ->first();

        if (!$order) {
            // Also check if prefix is missing
            $order = Order::where('order_number', 'ORD-' . $code)->first();
        }

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found for code: {$code}"]);
        }

        // Safety checks
        if ($order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => "Order {$order->order_number} is CANCELLED! Do not dispatch."]);
        }

        if ($order->status === 'shipped' && $order->courier_sent_at && $order->courier_sent_at->isToday()) {
            return response()->json([
                'success' => false,
                'already_dispatched' => true,
                'message' => "⚠️ Alert: {$order->order_number} was ALREADY scanned for dispatch at " . $order->courier_sent_at->format('h:i A') . "!"
            ]);
        }

        // Mark as shipped & record courier details
        $order->update([
            'status'          => 'shipped',
            'courier_name'    => $courierName,
            'courier_sent_at' => now(),
            'scanned_by'      => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "✓ Dispatched: {$order->order_number}",
            'order'   => [
                'id'            => $order->id,
                'order_number'  => $order->order_number,
                'customer_name' => $order->customer_name,
                'phone'         => $order->customer_phone,
                'city'          => $order->city,
                'total'         => (float) $order->total,
                'payment_method'=> strtoupper($order->payment_method),
                'dispatched_at' => $order->courier_sent_at->format('h:i A'),
                'courier'       => ucfirst($order->courier_name),
            ],
        ]);
    }

    public function returnScanLookup(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please enter a code.']);
        }

        $order = Order::with('items.product')
            ->where('order_number', $code)
            ->orWhere('courier_tracking_code', $code)
            ->first();

        if (!$order) {
            $order = Order::with('items.product')->where('order_number', 'ORD-' . $code)->first();
        }

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found for code: {$code}"]);
        }

        return response()->json([
            'success' => true,
            'order'   => [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'customer_name'   => $order->customer_name,
                'phone'           => $order->customer_phone,
                'shipping_charge' => (float) ($order->shipping_charge ?: 130),
                'total'           => (float) $order->total,
                'current_status'  => ucfirst($order->status),
                'courier_name'    => $order->courierLabel(),
                'items_count'     => $order->items->count(),
                'items_summary'   => $order->items->pluck('product_name')->implode(', '),
                'already_returned'=> $order->status === 'returned',
            ],
        ]);
    }

    public function returnScanConfirm(Request $request)
    {
        $validated = $request->validate([
            'order_id'      => ['required', 'exists:orders,id'],
            'return_type'   => ['required', 'in:paid_delivery,unpaid_delivery'],
            'restock'       => ['required', 'boolean'],
            'return_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $courierLoss = 0;
        if ($validated['return_type'] === 'unpaid_delivery') {
            // Business bears courier delivery cost
            $courierLoss = (float) ($order->shipping_charge > 0 ? $order->shipping_charge : 130);
        }

        // Restock inventory if checked and not already restocked
        if ($validated['restock'] && ! $order->return_restocked) {
            $order->restoreStock();
            $order->return_restocked = true;
        }

        $order->status = 'returned';
        $order->courier_returned_at = now();
        $order->return_type = $validated['return_type'];
        $order->courier_loss_amount = $courierLoss;
        $order->return_reason = $validated['return_reason'] ?: ($validated['return_type'] === 'paid_delivery' ? 'Customer returned (delivery charge paid)' : 'Failed delivery / Unreachable phone');
        $order->scanned_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Order {$order->order_number} marked as RETURNED.",
            'order'   => [
                'id'                  => $order->id,
                'order_number'        => $order->order_number,
                'customer_name'       => $order->customer_name,
                'return_type'         => $order->return_type,
                'courier_loss_amount' => (float) $order->courier_loss_amount,
                'return_restocked'    => (bool) $order->return_restocked,
                'returned_at'         => $order->courier_returned_at->format('h:i A'),
            ],
        ]);
    }

    public function printManifest(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $courier = $request->input('courier', 'all');

        $query = Order::with('items')
            ->whereDate('courier_sent_at', $date)
            ->where('status', 'shipped')
            ->orderBy('courier_sent_at', 'asc');

        if ($courier !== 'all') {
            $query->where('courier_name', $courier);
        }

        $orders = $query->get();
        $storeName = Setting::get('site_name', 'MARTY STORE');
        $storePhone = Setting::get('site_phone', '+880 1700-000000');

        return view('admin.courier-scan.manifest', compact('orders', 'date', 'courier', 'storeName', 'storePhone'));
    }
}
