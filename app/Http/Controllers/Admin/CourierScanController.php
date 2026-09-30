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

        // Orders ready for dispatch (confirmed, processing, pending)
        $awaitingDispatch = Order::whereIn('status', ['confirmed', 'processing', 'pending'])
            ->latest()
            ->take(12)
            ->get();

        $courierOptions = [
            'steadfast' => 'Steadfast Courier',
            'pathao'    => 'Pathao Courier',
            'redx'      => 'RedX Courier',
            'paperfly'  => 'Paperfly',
            'in_house'  => 'In-House Rider',
        ];

        // Determine last used pickup courier (today's latest or most recent in history)
        $lastCourier = $dispatchedToday->first()?->courier_name;
        if (! $lastCourier) {
            $lastCourier = Order::whereNotNull('courier_name')
                ->where('courier_name', '!=', '')
                ->latest('courier_sent_at')
                ->value('courier_name');
        }
        $lastCourier = strtolower((string) ($lastCourier ?: 'steadfast'));

        return view('admin.courier-scan.index', compact('dispatchedToday', 'returnedToday', 'awaitingDispatch', 'courierOptions', 'lastCourier'));
    }

    /**
     * Resilient order resolution for barcode guns and keyboard input.
     * Supports:
     * - Full order numbers: ORD-260930-DZS5
     * - Order numbers with hashes or scanner asterisks: #ORD-260930-DZS5, *ORD-260930-DZS5*
     * - Number only: 260930-DZS5
     * - Numeric Order ID: 1, #1
     * - Courier Tracking Codes: CID12345, 20A316MOG0DI
     * - Customer Phone: 017XXXXXXXX
     */
    protected function resolveOrder(string $rawCode): ?Order
    {
        $raw = trim($rawCode);
        if ($raw === '') {
            return null;
        }

        // Clean barcode scanner wrappers (*, #, quotes, whitespaces)
        $clean = trim($raw, " *#'\"\t\n\r\0\x0B");

        // 1. Direct match on order_number or courier_tracking_code
        $order = Order::with('items')
            ->where('order_number', $clean)
            ->orWhere('courier_tracking_code', $clean)
            ->first();

        // 2. Case-insensitive order_number
        if (!$order) {
            $order = Order::with('items')
                ->whereRaw('LOWER(order_number) = ?', [strtolower($clean)])
                ->orWhereRaw('LOWER(courier_tracking_code) = ?', [strtolower($clean)])
                ->first();
        }

        // 3. Try prepending 'ORD-' if not present
        if (!$order && !str_starts_with(strtoupper($clean), 'ORD-')) {
            $order = Order::with('items')
                ->where('order_number', 'ORD-' . $clean)
                ->orWhereRaw('LOWER(order_number) = ?', ['ord-' . strtolower($clean)])
                ->first();
        }

        // 4. Try matching by numeric ID (e.g. typing "1" for Order #1)
        if (!$order && is_numeric($clean)) {
            $order = Order::with('items')->find((int) $clean);
        }

        // 5. Try matching by customer phone (10+ digits)
        if (!$order && strlen(preg_replace('/[^0-9]/', '', $clean)) >= 10) {
            $phone = preg_replace('/[^0-9]/', '', $clean);
            $order = Order::with('items')
                ->where('customer_phone', 'like', "%{$phone}%")
                ->latest()
                ->first();
        }

        return $order;
    }

    public function dispatchScan(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        $courierName = trim((string) $request->input('courier_name', 'steadfast'));

        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Please scan or enter a barcode.']);
        }

        $order = $this->resolveOrder($code);

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found for code: {$code}"]);
        }

        // Safety checks
        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => "⚠️ Order #{$order->order_number} is CANCELLED! Cancelled orders cannot be dispatched."
            ]);
        }

        if ($order->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => "ℹ️ Order #{$order->order_number} has already been DELIVERED to customer."
            ]);
        }

        if ($order->status === 'shipped' && $order->courier_sent_at && $order->courier_sent_at->isToday()) {
            return response()->json([
                'success' => false,
                'already_dispatched' => true,
                'message' => "⚠️ Alert: Order #{$order->order_number} was ALREADY scanned for dispatch today at " . $order->courier_sent_at->format('h:i A') . "!"
            ]);
        }

        // Mark as shipped & record courier details
        $order->update([
            'status'          => 'shipped',
            'courier_name'    => $courierName,
            'courier_sent_at' => now(),
            'scanned_by'      => auth()->id(),
        ]);

        \App\Services\ActivityLogger::log(
            'Courier Dispatch Scan',
            "Dispatched order #{$order->order_number} via " . ucfirst($courierName)
        );

        return response()->json([
            'success' => true,
            'message' => "✓ Dispatched: #{$order->order_number} to " . ucfirst($courierName),
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
            return response()->json(['success' => false, 'message' => 'Please enter or scan a barcode.']);
        }

        $order = $this->resolveOrder($code);

        if (!$order) {
            return response()->json(['success' => false, 'message' => "Order not found for code: {$code}"]);
        }

        return response()->json([
            'success' => true,
            'order'   => [
                'id'               => $order->id,
                'order_number'     => $order->order_number,
                'customer_name'    => $order->customer_name,
                'phone'            => $order->customer_phone,
                'shipping_charge'  => (float) ($order->shipping_charge ?: 130),
                'total'            => (float) $order->total,
                'current_status'   => ucfirst($order->status),
                'courier_name'     => $order->courierLabel(),
                'items_count'      => $order->items->count(),
                'items_summary'    => $order->items->pluck('product_name')->filter()->implode(', ') ?: 'Order Items',
                'already_returned' => $order->status === 'returned',
                'return_restocked' => (bool) $order->return_restocked,
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

        $defaultReason = $validated['return_type'] === 'paid_delivery'
            ? 'Customer returned (delivery charge paid)'
            : 'Failed delivery / Unreachable phone';
        $reason = !empty($validated['return_reason']) ? $validated['return_reason'] : $defaultReason;

        \Illuminate\Support\Facades\DB::transaction(function () use ($order, $validated, $courierLoss, $reason) {
            // Restock inventory if checked and not already restocked
            if (!empty($validated['restock']) && ! $order->return_restocked) {
                $order->restoreStock();
                $order->return_restocked = true;
            }

            $order->status = 'returned';
            $order->courier_returned_at = now();
            $order->return_type = $validated['return_type'];
            $order->courier_loss_amount = $courierLoss;
            $order->return_reason = $reason;
            $order->scanned_by = auth()->id();
            $order->save();
        });

        $order->refresh();

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
                'returned_at'         => $order->courier_returned_at ? $order->courier_returned_at->format('h:i A') : now()->format('h:i A'),
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
