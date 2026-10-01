<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPrint;
use App\Services\Courier\PathaoService;
use App\Services\Courier\RedxService;
use App\Services\Courier\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->latest();

        $status = $request->input('status', 'all');
        if ($status === 'pending_verification') {
            $query->where('payment_status', 'pending');
        } elseif ($status === 'not_printed') {
            $query->notPrinted();
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

        $orders = $query->withCount('items')
            ->with(['prints' => fn ($q) => $q->with('user')->latest('id')])
            ->paginate(15)->withQueryString();

        $counts = [
            'all'                  => Order::count(),
            'pending_verification' => Order::where('payment_status', 'pending')->count(),
            'not_printed'          => Order::notPrinted()->count(),
            'confirmed'            => Order::where('status', 'confirmed')->count(),
            'processing'           => Order::where('status', 'processing')->count(),
            'shipped'              => Order::where('status', 'shipped')->count(),
            'delivered'            => Order::where('status', 'delivered')->count(),
            'returned'             => Order::where('status', 'returned')->count(),
            'cancelled'            => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'status', 'counts') + ['method' => $request->input('method'), 'q' => $term]);
    }

    public function show(Order $order, SteadfastService $steadfast, PathaoService $pathao, RedxService $redx, Request $request)
    {
        $order->load(['items.product.variants', 'prints.user']);

        $couriers = [
            'steadfast' => ['name' => 'Steadfast Courier', 'configured' => $steadfast->isConfigured()],
            'pathao'    => ['name' => 'Pathao Courier', 'configured' => $pathao->isConfigured()],
            'redx'      => ['name' => 'RedX Courier', 'configured' => $redx->isConfigured()],
        ];

        $forceRefresh = $request->boolean('refresh_courier');
        $steadfastDeliveryCheck = $steadfast->checkDeliveryHistory($order->customer_phone, $forceRefresh);

        return view('admin.orders.show', compact('order', 'couriers', 'steadfastDeliveryCheck'));
    }

    /** Invoice formats: full A4 page, half page (two copies on one A4), and 80mm / 58mm thermal receipts. */
    public const INVOICE_FORMATS = [
        'a4'        => 'Full page (A4)',
        'half'      => 'Half page (2 per A4)',
        'thermal'   => 'Thermal 80mm',
        'thermal58' => 'Thermal 58mm',
    ];

    public function invoice(Request $request, Order $order)
    {
        return $this->renderInvoices($request, collect([$order->load(['items', 'prints.user'])]));
    }

    /**
     * Several invoices in one print job (?orders[]=ORD-...). On half page, two different
     * orders share each A4 sheet, so 4 orders use 2 sheets.
     */
    public function invoices(Request $request)
    {
        $validated = $request->validate([
            'orders'   => ['required', 'array', 'min:1', 'max:100'],
            'orders.*' => ['string', 'max:64'],
        ]);

        $numbers = array_values(array_unique($validated['orders']));
        $orders = Order::with(['items', 'prints.user'])->whereIn('order_number', $numbers)->get()
            ->sortBy(fn ($o) => array_search($o->order_number, $numbers))->values();
        abort_if($orders->isEmpty(), 404);

        return $this->renderInvoices($request, $orders);
    }

    /**
     * Called by the invoice / label pages when the print dialog opens. Printing an invoice
     * also moves confirmed orders to "processing" (being packed).
     */
    public function recordPrints(Request $request)
    {
        $validated = $request->validate([
            'orders'   => ['required', 'array', 'min:1', 'max:100'],
            'orders.*' => ['string', 'max:64'],
            'type'     => ['required', Rule::in(OrderPrint::TYPES)],
            'format'   => ['nullable', Rule::in(array_keys(self::INVOICE_FORMATS))],
        ]);

        $orders = Order::whereIn('order_number', array_unique($validated['orders']))->get();
        $moved = [];

        DB::transaction(function () use ($orders, $validated, $request, &$moved) {
            foreach ($orders as $order) {
                $order->prints()->create([
                    'user_id' => $request->user()?->id,
                    'type'    => $validated['type'],
                    'format'  => $validated['type'] === 'invoice' ? ($validated['format'] ?? 'a4') : null,
                ]);
                if ($validated['type'] === 'invoice' && $order->status === 'confirmed') {
                    $order->update(['status' => 'processing']);
                    $moved[] = $order->order_number;
                }
            }
        });

        if ($orders->isNotEmpty()) {
            $what = $validated['type'] === 'invoice' ? 'invoice' : 'parcel label';
            \App\Services\ActivityLogger::log(
                'Printed ' . ucfirst($what) . 's',
                'Printed ' . $what . ' for ' . $orders->pluck('order_number')->implode(', ')
                    . ($moved ? '; moved to processing: ' . implode(', ', $moved) : '')
            );
        }

        return response()->json(['success' => true, 'recorded' => $orders->count(), 'moved_to_processing' => $moved]);
    }

    /** Which of these orders were already printed, per type (for the warning before printing again). */
    public function printStatus(Request $request)
    {
        $validated = $request->validate([
            'orders'   => ['required', 'array', 'min:1', 'max:100'],
            'orders.*' => ['string', 'max:64'],
        ]);

        $orders = Order::whereIn('order_number', $validated['orders'])->whereHas('prints')
            ->with(['prints' => fn ($q) => $q->with('user')->latest('id')])->get();

        $printed = [];
        foreach (OrderPrint::TYPES as $type) {
            $printed[$type] = $orders->map(function ($o) use ($type) {
                $prints = $o->prints->where('type', $type);

                return $prints->isEmpty() ? null : [
                    'order_number' => $o->order_number,
                    'times'        => $prints->count(),
                    'last'         => $prints->first()->summary(),
                ];
            })->filter()->values();
        }

        return response()->json(['printed' => $printed]);
    }

    private function renderInvoices(Request $request, \Illuminate\Support\Collection $orders)
    {
        $format = array_key_exists((string) $request->query('format'), self::INVOICE_FORMATS)
            ? $request->query('format')
            : 'a4';
        $formats = self::INVOICE_FORMATS;

        return view('admin.orders.invoice', compact('orders', 'format', 'formats'));
    }

    /**
     * Printable parcel labels with a scannable order barcode for the courier scan station.
     * ?orders[]=ORD-... prints those orders; ?ready=1 prints every confirmed/processing delivery order.
     */
    public function labels(Request $request)
    {
        $validated = $request->validate([
            'orders'   => ['array', 'max:200'],
            'orders.*' => ['string', 'max:64'],
            'ready'    => ['nullable', 'boolean'],
            'size'     => ['nullable', 'in:thermal,a4'],
        ]);

        $query = Order::with(['items', 'prints.user']);
        if (! empty($validated['orders'])) {
            $numbers = array_values($validated['orders']);
            $query->whereIn('order_number', $numbers);
            $orders = $query->get()->sortBy(fn ($o) => array_search($o->order_number, $numbers))->values();
        } elseif ($request->boolean('ready')) {
            $orders = $query->whereIn('status', ['confirmed', 'processing'])
                ->where(fn ($q) => $q->whereNull('order_type')->orWhere('order_type', '!=', 'pos'))
                ->oldest()
                ->limit(200)
                ->get();
        } else {
            $orders = collect();
        }

        $size = $validated['size'] ?? 'thermal';

        return view('admin.orders.labels', compact('orders', 'size'));
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

        $becomingCancelled = $data['status'] === 'cancelled' && $order->status !== 'cancelled';

        if ($becomingCancelled) {
            // A returned order already handled its stock (restocked, or kept out as damaged).
            if ($order->status !== 'returned') {
                $order->restoreStock();
            }
            $order->releaseCoupon();
        }

        // Reactivating a cancelled/returned order takes its stock (and coupon use) back out.
        $reactivating = in_array($order->status, ['cancelled', 'returned'], true)
            && in_array($data['status'], Order::ACTIVE_STATUSES, true);
        if ($reactivating) {
            $order->prepareReactivation();
        }

        // Synchronize returning behavior with Courier Scan Station
        $becomingReturned = $data['status'] === 'returned' && $order->status !== 'returned';
        if ($becomingReturned) {
            if (!$order->return_restocked) {
                $order->restoreStock();
                $data['return_restocked'] = true;
            }
            if (!$order->courier_returned_at) {
                $data['courier_returned_at'] = now();
            }
            if (!$order->return_type) {
                $data['return_type'] = 'unpaid_delivery';
                $data['courier_loss_amount'] = (float) ($order->shipping_charge > 0 ? $order->shipping_charge : 130);
            }
        }

        // Synchronize shipping behavior with Courier Scan Station
        $becomingShipped = $data['status'] === 'shipped' && $order->status !== 'shipped';
        if ($becomingShipped && !$order->courier_sent_at) {
            $data['courier_sent_at'] = now();
            if (empty($order->courier_name)) {
                $data['courier_name'] = 'in_house';
            }
        }

        if ($data['status'] === 'delivered' && $order->payment_method === 'cod' && $data['payment_status'] === 'pending') {
            $data['payment_status'] = 'verified';
        }

        if (in_array($data['status'], ['returned', 'cancelled'], true) && $data['payment_status'] === 'pending') {
            $data['payment_status'] = 'rejected';
        }

        $order->update($data);

        return back()->with('status', "Order {$order->order_number} updated.");
    }

    /** Update customer contact & delivery details for an order. */
    public function updateCustomer(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_name'    => ['required', 'string', 'max:150'],
            'customer_phone'   => ['required', 'string', 'max:30'],
            'customer_email'   => ['nullable', 'email', 'max:150'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'city'             => ['required', 'string', 'max:100'],
            'postal_code'      => ['nullable', 'string', 'max:20'],
        ]);

        $order->update($data);

        \App\Services\ActivityLogger::log(
            'Updated Customer Details',
            "Updated customer details for order #{$order->order_number} ({$order->customer_name})"
        );

        return back()->with('status', "Customer details for order {$order->order_number} updated successfully.");
    }

    /** Delete an order from system. */
    public function destroy(Order $order)
    {
        $orderNumber = $order->order_number;

        // Only put stock back for orders whose goods never left the shop.
        if (in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
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
