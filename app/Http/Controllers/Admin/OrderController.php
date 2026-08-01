<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Courier\PathaoService;
use App\Services\Courier\RedxService;
use App\Services\Courier\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->latest();

        $status = $request->input('status', 'all');
        if ($status === 'pending_verification') {
            $query->where('payment_status', 'pending');
        } elseif (in_array($status, Order::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($method = $request->input('method')) {
            if (in_array($method, Order::PAYMENT_METHODS, true)) {
                $query->where('payment_method', $method);
            }
        }

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%");
            });
        }

        $orders = $query->withCount('items')->paginate(15)->withQueryString();

        $counts = [
            'all'                  => Order::count(),
            'pending_verification' => Order::where('payment_status', 'pending')->count(),
            'confirmed'            => Order::where('status', 'confirmed')->count(),
            'shipped'              => Order::where('status', 'shipped')->count(),
            'delivered'            => Order::where('status', 'delivered')->count(),
            'cancelled'            => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'status', 'counts') + ['method' => $request->input('method'), 'q' => $term]);
    }

    public function show(Order $order, SteadfastService $steadfast, PathaoService $pathao, RedxService $redx)
    {
        $order->load(['items.product.variants']);

        $couriers = [
            'steadfast' => ['name' => 'Steadfast Courier', 'configured' => $steadfast->isConfigured()],
            'pathao'    => ['name' => 'Pathao Courier', 'configured' => $pathao->isConfigured()],
            'redx'      => ['name' => 'RedX Courier', 'configured' => $redx->isConfigured()],
        ];

        return view('admin.orders.show', compact('order', 'couriers'));
    }

    public function invoice(Order $order)
    {
        $order->load('items');

        return view('admin.orders.invoice', compact('order'));
    }

    /** Verify the manual payment (accept the order). */
    public function verify(Order $order)
    {
        $order->update([
            'payment_status' => 'verified',
            'status'         => $order->status === 'pending' ? 'confirmed' : $order->status,
        ]);

        \App\Services\ActivityLogger::log('Verified Order Payment', "Verified payment for order #{$order->order_number} ({$order->customer_name})");

        return back()->with('status', "Payment verified for {$order->order_number}.");
    }

    /** Reject the manual payment. */
    public function reject(Order $order)
    {
        if ($order->shouldRestoreStockOnCancel()) {
            $order->restoreStock();
            $order->releaseCoupon();
        }

        $order->update([
            'payment_status' => 'rejected',
            'status'         => 'cancelled',
        ]);

        \App\Services\ActivityLogger::log('Rejected Order Payment', "Rejected payment for order #{$order->order_number} ({$order->customer_name})");

        return back()->with('status', "Payment rejected for {$order->order_number}.");
    }

    /** Update fulfilment status, payment status and internal note from the detail page. */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status'         => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
            'internal_note'  => ['nullable', 'string', 'max:2000'],
        ]);

        $wasNotCancelled = $order->status !== 'cancelled';
        $becomingCancelled = $data['status'] === 'cancelled' && $wasNotCancelled;

        if ($becomingCancelled) {
            $order->restoreStock();
            $order->releaseCoupon();
        }

        $order->update($data);

        return back()->with('status', "Order {$order->order_number} updated.");
    }

    /** Delete an order from system. */
    public function destroy(Order $order)
    {
        $orderNumber = $order->order_number;

        if ($order->shouldRestoreStockOnCancel()) {
            $order->restoreStock();
            $order->releaseCoupon();
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')->with('status', "Order {$orderNumber} deleted successfully.");
    }

    /** Update variation for an order item. */
    public function updateItemVariant(Request $request, Order $order, OrderItem $item)
    {
        $data = $request->validate([
            'variant' => ['nullable', 'string', 'max:255'],
        ]);

        $newVariant = trim((string) $data['variant']) !== '' ? trim((string) $data['variant']) : null;

        $item->update([
            'variant' => $newVariant,
        ]);

        return back()->with('status', "Variation updated for '{$item->product_name}'.");
    }

    /** Dispatch order to selected courier provider (Steadfast, Pathao, RedX). */
    public function dispatchCourier(
        Request $request,
        Order $order,
        string $provider,
        SteadfastService $steadfast,
        PathaoService $pathao,
        RedxService $redx
    ) {
        $provider = strtolower(trim($provider));

        $result = match ($provider) {
            'steadfast' => $steadfast->createOrder($order),
            'pathao'    => $pathao->createOrder($order),
            'redx'      => $redx->createOrder($order),
            default     => ['success' => false, 'message' => 'Invalid courier provider specified.'],
        };

        if ($result['success']) {
            $order->update([
                'courier_name'          => $provider,
                'courier_tracking_code' => $result['tracking_code'],
                'courier_status'        => 'in_transit',
                'courier_sent_at'       => now(),
                'status'                => in_array($order->status, ['pending', 'confirmed', 'processing'], true) ? 'shipped' : $order->status,
            ]);

            return back()->with('status', "Order {$order->order_number} successfully dispatched to {$order->courierLabel()}! Tracking Code: {$result['tracking_code']}");
        }

        return back()->with('error', $result['message']);
    }
}
