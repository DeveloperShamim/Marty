<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\FreeDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Services\OrderPlacer;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
        private OrderPlacer $orders,
    ) {}

    public function show()
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        $coupon   = $this->coupons->summary($subtotal);
        // No delivery zone is picked until the shopper chooses one
        $zone     = old('shipping_zone', '');
        $hasFreeProduct = $this->hasFreeDeliveryProduct($items);
        $totals   = $this->orderTotals($subtotal, $coupon['discount'], $zone, old('payment_method', 'cod'), $hasFreeProduct);

        $this->syncDraftAbandonedCart($items, $subtotal, $totals['total']);

        return view('storefront.checkout', [
            'items'       => $items,
            'subtotal'    => $subtotal,
            'shipInside'  => (float) setting('shipping_inside_dhaka', 60),
            'shipOutside' => (float) setting('shipping_outside_dhaka', 120),
            'taxPercent'  => (float) setting('tax_percent', 0),
            'user'        => Auth::user(),
            'couponCode'  => $coupon['code'],
            'discount'    => $coupon['discount'],
            'totals'      => $totals,
            'freeDelivery'   => FreeDelivery::config(),
            'hasFreeProduct' => $hasFreeProduct,
        ]);
    }

    public static function normalizeBdPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        $clean = preg_replace('/[^\d+]/', '', trim($phone));
        if (str_starts_with($clean, '+880')) {
            $clean = '0' . substr($clean, 4);
        } elseif (str_starts_with($clean, '880')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '00880')) {
            $clean = '0' . substr($clean, 5);
        }

        if (preg_match('/^01[3-9]\d{8}$/', $clean)) {
            return $clean;
        }

        return null;
    }

    public function syncContact(Request $request)
    {
        $validated = $request->validate([
            'customer_name'  => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:120'],
        ]);

        if (!empty($validated['customer_phone'])) {
            $normalized = self::normalizeBdPhone($validated['customer_phone']);
            if ($normalized) {
                $validated['customer_phone'] = $normalized;
            }
        }

        $items = $this->cart->items();
        if ($items->isNotEmpty()) {
            $subtotal = $this->cart->subtotal();
            $coupon   = $this->coupons->summary($subtotal);
            $zone     = (string) $request->input('shipping_zone', '');
            $totals   = $this->orderTotals($subtotal, $coupon['discount'], $zone, null, $this->hasFreeDeliveryProduct($items));

            $this->syncDraftAbandonedCart($items, $subtotal, $totals['total'], $validated);
        }

        return response()->json(['ok' => true]);
    }

    public function applyCoupon(Request $request)
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $request->validate(['code' => ['required', 'string', 'max:40']]);

        $result = $this->coupons->apply($request->input('code'), $this->cart->subtotal());

        return $result['ok']
            ? redirect()->route('checkout.show')->with('status', $result['message'])
            : redirect()->route('checkout.show')->withInput()->withErrors(['coupon' => $result['message']]);
    }

    public function removeCoupon()
    {
        $this->coupons->remove();

        return redirect()->route('checkout.show')->with('status', 'Coupon removed.');
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        if ($request->filled('customer_phone')) {
            $cleaned = preg_replace('/[\s\-\(\)]/', '', (string) $request->input('customer_phone'));
            $request->merge(['customer_phone' => $cleaned]);
        }
        if ($request->filled('payment_sender_number')) {
            $cleanedSender = preg_replace('/[\s\-\(\)]/', '', (string) $request->input('payment_sender_number'));
            $request->merge(['payment_sender_number' => $cleanedSender]);
        }

        $validated = $request->validate([
            'customer_name'   => ['required', 'string', 'max:120'],
            'customer_phone'  => [
                'required',
                'string',
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
            ],
            'customer_email'  => ['nullable', 'email', 'max:120'],
            'shipping_address'=> ['required', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:80'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'shipping_zone'   => ['required', Rule::in(['inside_dhaka', 'outside_dhaka'])],
            'payment_method'  => ['required', Rule::in($this->availablePaymentMethods())],
            'payment_sender_number' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => $request->payment_method !== 'cod'),
                'regex:/^(?:\+?88|0088)?01[3-9]\d{8}$/',
            ],
            'payment_txn_id'  => ['nullable', 'string', 'max:60', Rule::requiredIf(fn () => $request->payment_method !== 'cod')],
            'delivery_note'   => ['nullable', 'string', 'max:500'],
        ], [
            'customer_phone.required' => 'Please enter your mobile phone number.',
            'customer_phone.regex'    => 'Please enter a valid 11-digit Bangladeshi mobile number (e.g. 01712345678 or 018XXXXXXXX).',
            'payment_sender_number.required' => 'Please provide the mobile banking sender number.',
            'payment_sender_number.regex'    => 'Please enter a valid 11-digit mobile banking phone number (e.g. 017XXXXXXXX).',
        ]);

        $validated['customer_phone'] = self::normalizeBdPhone($validated['customer_phone']) ?? $validated['customer_phone'];
        if (!empty($validated['payment_sender_number'])) {
            $validated['payment_sender_number'] = self::normalizeBdPhone($validated['payment_sender_number']) ?? $validated['payment_sender_number'];
        }

        $subtotal = (float) $items->sum('line_total');
        $coupon   = $this->coupons->coupon();
        $discount = 0.0;

        if ($coupon) {
            $error = $coupon->validateForSubtotal($subtotal);
            if ($error) {
                $this->coupons->remove();

                return back()->withInput()->withErrors(['coupon' => $error]);
            }
            $discount = $coupon->calculateDiscount($subtotal);
        }

        $totals = $this->orderTotals($subtotal, $discount, $validated['shipping_zone'], $validated['payment_method'], $this->hasFreeDeliveryProduct($items));
        $shipping = $totals['shipping'];
        $tax      = $totals['tax'];
        $total    = $totals['total'];

        foreach ($items as $item) {
            $maxStock = isset($item->max_stock) ? (int) $item->max_stock : (int) $item->product->stock_quantity;
            $maxAllowed = min(3, $maxStock);
            if ($item->qty > $maxAllowed) {
                $msg = $maxStock < 3 ? "\"{$item->name}\" only has {$maxStock} in stock." : "Maximum 3 items allowed per product variant for \"{$item->name}\".";
                return back()
                    ->withInput()
                    ->withErrors(['cart' => $msg]);
            }
        }

        try {
            $order = $this->orders->place($validated, $items, [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping' => $shipping,
                'waived'   => $totals['waived'],
                'reason'   => $totals['reason'],
                'tax'      => $tax,
                'total'    => $total,
            ], $coupon, $request);
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['cart' => $e->getMessage()]);
        }

        $this->cart->clear();
        $this->coupons->remove();

        return redirect()->route('order.confirmation', $order->order_number);
    }

    public function confirmation(Order $order)
    {
        if (session('recent_order') !== $order->order_number) {
            return redirect()
                ->route('track')
                ->with('status', 'Use Track Order with your order number and phone to view order details.');
        }

        $order->load('items');

        return view('storefront.order-confirmation', compact('order'));
    }

    private function orderTotals(float $subtotal, float $discount, string $shippingZone, ?string $paymentMethod = null, bool $hasFreeProduct = false): array
    {
        $taxable  = max(0, $subtotal - $discount);
        // Delivery is added once a zone is chosen
        $delivery = $shippingZone === ''
            ? ['shipping' => 0.0, 'waived' => 0.0, 'reason' => null]
            : FreeDelivery::quote($taxable, $shippingZone, $paymentMethod, $hasFreeProduct);
        $shipping = $delivery['shipping'];
        $waived   = $delivery['waived'];
        $reason   = $delivery['reason'];
        $tax      = round($taxable * (float) setting('tax_percent', 0) / 100, 2);
        $total    = $taxable + $shipping + $tax;

        return compact('shipping', 'waived', 'reason', 'tax', 'total');
    }

    private function hasFreeDeliveryProduct($items): bool
    {
        return $items->contains(fn ($item) => (bool) ($item->product?->free_delivery));
    }

    /** COD is always available; mobile banking only when a merchant number is configured. */
    /** Enabled methods only; mobile banking also requires a merchant number. */
    private function availablePaymentMethods(): array
    {
        $methods = [];

        if ((string) setting('pay_cod_enabled', '1') === '1') {
            $methods[] = 'cod';
        }
        if ((string) setting('pay_bkash_enabled', '1') === '1' && setting('bkash_number')) {
            $methods[] = 'bkash';
        }
        if ((string) setting('pay_nagad_enabled', '1') === '1' && setting('nagad_number')) {
            $methods[] = 'nagad';
        }
        if ((string) setting('pay_rocket_enabled', '1') === '1' && setting('rocket_number')) {
            $methods[] = 'rocket';
        }

        return $methods;
    }

    private function syncDraftAbandonedCart($items, float $subtotal, float $total, ?array $customerData = null): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $sessionId = session()->getId();
        $user = Auth::user();

        // Feature: Abandoned cart is ONLY recorded and counted if the customer gives a mobile phone number
        $phone = trim((string) ($customerData['customer_phone'] ?? $user?->phone ?? ''));
        if (empty($phone)) {
            return;
        }

        $cartData = $items->mapWithKeys(function ($item) {
            $key = $item->key ?? ($item->product_id . '|' . ($item->variant ?? '') . '|' . ($item->sku_id ?? ''));
            return [
                $key => [
                    'product_id' => (int) $item->product_id,
                    'qty'        => (int) $item->qty,
                    'variant'    => $item->variant ?? null,
                    'sku_id'     => $item->sku_id ?? null,
                ]
            ];
        })->toArray();

        $cart = \App\Models\AbandonedCart::where('session_id', $sessionId)
            ->where('status', '!=', 'recovered')
            ->first();

        if (! $cart && $user) {
            $cart = \App\Models\AbandonedCart::where('user_id', $user->id)
                ->where('status', '!=', 'recovered')
                ->first();
        }

        $payload = [
            'session_id'     => $sessionId,
            'user_id'        => $user?->id,
            'customer_name'  => $customerData['customer_name'] ?? $user?->name ?? $cart?->customer_name,
            'customer_phone' => $phone,
            'customer_email' => $customerData['customer_email'] ?? $user?->email ?? $cart?->customer_email,
            'cart_data'      => $cartData,
            'subtotal'       => $subtotal,
            'total'          => $total,
            'status'         => $cart?->status === 'reminder_sent' ? 'reminder_sent' : 'abandoned',
        ];

        if ($cart) {
            $cart->update($payload);
        } else {
            $payload['recovery_token'] = \App\Models\AbandonedCart::generateToken();
            \App\Models\AbandonedCart::create($payload);
        }
    }
}
