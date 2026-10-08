<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OrderResource;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\Courier\PathaoService;
use App\Services\Courier\RedxService;
use App\Services\Courier\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->latest()->withCount('items');

        $status = $request->input('status', 'all');
        if ($status === 'pending_verification') {
            $query->where('payment_status', 'pending')->where('status', '!=', 'cancelled');
        } elseif (in_array($status, Order::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%")
                    ->orWhere('courier_tracking_code', 'like', "%{$term}%");
            });
        }

        return OrderResource::collection($query->paginate(20)->withQueryString());
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load('items'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status'         => ['sometimes', 'required', Rule::in(Order::STATUSES)],
            'payment_status' => ['sometimes', 'required', Rule::in(Order::PAYMENT_STATUSES)],
            'internal_note'  => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        if (($data['status'] ?? null) === 'cancelled' && $order->shouldRestoreStockOnCancel()) {
            $order->restoreStock();
            $order->releaseCoupon();
        }

        $order->update($data);
        ActivityLogger::log('Updated Order', "Updated order #{$order->order_number} from mobile app");

        return new OrderResource($order->load('items'));
    }

    public function verify(Order $order)
    {
        $order->update([
            'payment_status' => 'verified',
            'status'         => $order->status === 'pending' ? 'confirmed' : $order->status,
        ]);

        ActivityLogger::log('Verified Order Payment', "Verified payment for order #{$order->order_number} ({$order->customer_name})");

        return new OrderResource($order->load('items'));
    }

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

        ActivityLogger::log('Rejected Order Payment', "Rejected payment for order #{$order->order_number} ({$order->customer_name})");

        return new OrderResource($order->load('items'));
    }

    /** Couriers the admin has enabled in Settings → Integrations. */
    public function couriers(SteadfastService $steadfast, PathaoService $pathao, RedxService $redx)
    {
        return response()->json(['data' => [
            ['id' => 'steadfast', 'name' => 'Steadfast Courier', 'configured' => $steadfast->isConfigured()],
            ['id' => 'pathao', 'name' => 'Pathao Courier', 'configured' => $pathao->isConfigured()],
            ['id' => 'redx', 'name' => 'RedX Courier', 'configured' => $redx->isConfigured()],
        ]]);
    }

    /** Book the parcel through the courier's API (same as the web admin's dispatch button). */
    public function dispatchCourier(Order $order, string $provider, SteadfastService $steadfast, PathaoService $pathao, RedxService $redx)
    {
        $provider = strtolower(trim($provider));

        $result = match ($provider) {
            'steadfast' => $steadfast->createOrder($order),
            'pathao'    => $pathao->createOrder($order),
            'redx'      => $redx->createOrder($order),
            default     => ['success' => false, 'message' => 'Invalid courier provider specified.'],
        };

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $order->markDispatched($provider, $result['tracking_code']);
        ActivityLogger::log('Dispatched Order', "Dispatched order #{$order->order_number} to {$order->courierLabel()} from mobile app");

        return (new OrderResource($order->load('items')))->additional([
            'message' => "Dispatched to {$order->courierLabel()}. Tracking Code: {$result['tracking_code']}",
        ]);
    }

    /** Attach a tracking code scanned from a courier label (parcel booked outside the website). */
    public function attachTracking(Request $request, Order $order)
    {
        $data = $request->validate([
            'courier_name'  => ['required', 'string', 'max:50'],
            'tracking_code' => ['required', 'string', 'max:100'],
        ]);

        $trackingCode = trim($data['tracking_code']);
        $taken = Order::where('courier_tracking_code', $trackingCode)->whereKeyNot($order->id)->value('order_number');
        if ($taken) {
            return response()->json(['message' => "This tracking code is already attached to order {$taken}."], 422);
        }

        $order->markDispatched(strtolower(trim($data['courier_name'])), $trackingCode);
        ActivityLogger::log('Attached Courier Tracking', "Attached tracking {$trackingCode} to order #{$order->order_number} from mobile app");

        return new OrderResource($order->load('items'));
    }

    /** Find an order from a scanned invoice barcode/QR or courier label. */
    public function scan(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:500']]);

        $candidates = self::scanCandidates($data['code']);

        $order = Order::whereIn('order_number', $candidates)
            ->orWhereIn('courier_tracking_code', $candidates)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'No order found for the scanned code.', 'code' => trim($data['code'])], 404);
        }

        return new OrderResource($order->load('items'));
    }

    /**
     * A scan can be a bare code ("ORD-2026-0001", "SFR123…") or a URL such as a courier
     * tracking link or the storefront order page, so also try its last path segment and
     * common query parameters.
     */
    private static function scanCandidates(string $raw): array
    {
        $code = trim($raw);
        $candidates = [$code];

        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $segments = array_values(array_filter(explode('/', (string) parse_url($code, PHP_URL_PATH))));
            if ($segments) {
                $candidates[] = rawurldecode(end($segments));
            }
            parse_str((string) parse_url($code, PHP_URL_QUERY), $query);
            foreach (['tracking_id', 'tracking_code', 'order', 'order_number', 'invoice'] as $key) {
                if (! empty($query[$key]) && is_string($query[$key])) {
                    $candidates[] = $query[$key];
                }
            }
        }

        return array_values(array_unique(array_merge($candidates, array_map('strtoupper', $candidates))));
    }
}
