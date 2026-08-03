<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Services\FraudDetectionService;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
        private FraudDetectionService $fraudService,
    ) {}

    public function show()
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        $coupon   = $this->coupons->summary($subtotal);
        $zone     = old('shipping_zone', 'inside_dhaka');
        $totals   = $this->orderTotals($subtotal, $coupon['discount'], $zone);

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
        ]);
    }

    public function syncContact(Request $request)
    {
        $validated = $request->validate([
            'customer_name'  => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email', 'max:120'],
        ]);

        $items = $this->cart->items();
        if ($items->isNotEmpty()) {
            $subtotal = $this->cart->subtotal();
            $coupon   = $this->coupons->summary($subtotal);
            $zone     = $request->input('shipping_zone', 'inside_dhaka');
            $totals   = $this->orderTotals($subtotal, $coupon['discount'], $zone);

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

        $validated = $request->validate([
            'customer_name'   => ['required', 'string', 'max:120'],
            'customer_phone'  => ['required', 'string', 'max:40'],
            'customer_email'  => ['nullable', 'email', 'max:120'],
            'shipping_address'=> ['required', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:80'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'shipping_zone'   => ['required', Rule::in(['inside_dhaka', 'outside_dhaka'])],
            'payment_method'  => ['required', Rule::in($this->availablePaymentMethods())],
            'payment_sender_number' => ['nullable', 'string', 'max:40', Rule::requiredIf(fn () => $request->payment_method !== 'cod')],
            'payment_txn_id'  => ['nullable', 'string', 'max:60', Rule::requiredIf(fn () => $request->payment_method !== 'cod')],
        ]);

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

        $totals = $this->orderTotals($subtotal, $discount, $validated['shipping_zone']);
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

        $isCod = $validated['payment_method'] === 'cod';
        $ipAddress = (string) $request->ip();

        $fraudAnalysis = $this->fraudService->analyzeOrder($validated, $total, $ipAddress);

        try {
            $order = DB::transaction(function () use ($validated, $items, $subtotal, $discount, $shipping, $tax, $total, $isCod, $coupon, $ipAddress, $fraudAnalysis) {
                $order = Order::create([
                    'order_number'    => $this->generateOrderNumber(),
                    'user_id'         => Auth::id(),
                    'customer_name'   => $validated['customer_name'],
                    'customer_phone'  => $validated['customer_phone'],
                    'customer_email'  => $validated['customer_email'] ?? null,
                    'shipping_address'=> $validated['shipping_address'],
                    'city'            => $validated['city'],
                    'postal_code'     => $validated['postal_code'] ?? null,
                    'shipping_zone'   => $validated['shipping_zone'],
                    'coupon_id'       => $coupon?->id,
                    'coupon_code'     => $coupon?->code,
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discount,
                    'shipping_charge' => $shipping,
                    'tax'             => $tax,
                    'total'           => $total,
                    'payment_method'  => $validated['payment_method'],
                    'payment_sender_number' => $isCod ? null : ($validated['payment_sender_number'] ?? null),
                    'payment_txn_id'  => $isCod ? null : ($validated['payment_txn_id'] ?? null),
                    'utm_source'      => session('utm_source'),
                    'payment_status'  => $isCod ? 'verified' : 'pending',
                    'status'          => $isCod ? 'confirmed' : 'pending',
                    'ip_address'      => $ipAddress,
                    'fraud_score'     => $fraudAnalysis['score'] ?? 0,
                    'fraud_flags'     => $fraudAnalysis['flags'] ?? [],
                ]);

                // Clear utm_source after order is placed
                session()->forget('utm_source');

                foreach ($items as $item) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                    $sku = null;
                    if (! empty($item->sku_id)) {
                        $sku = ProductSku::where('id', $item->sku_id)->lockForUpdate()->first();
                    } elseif (! empty($item->variant) && $product) {
                        $sku = ProductSku::where('product_id', $product->id)
                            ->lockForUpdate()
                            ->get()
                            ->first(function ($s) use ($item) {
                                return $s->matchesVariantString($item->variant);
                            });
                    }

                    if ($sku) {
                        $maxAllowed = min(3, (int) $sku->stock_quantity);
                        if (! $sku->is_active || $item->qty > $maxAllowed) {
                            throw new \RuntimeException("Cannot order {$item->qty} of {$item->name} ({$sku->attributeLabel()}). Max 3 allowed per variant.");
                        }
                        ProductSku::where('id', $sku->id)->decrement('stock_quantity', $item->qty);
                        if ($product) {
                            $product->syncTotalStock();
                        }
                    } else {
                        $maxAllowed = min(3, (int) ($product->stock_quantity ?? 0));
                        if (! $product || $item->qty > $maxAllowed) {
                            throw new \RuntimeException("Cannot order {$item->qty} of {$item->name}. Max 3 allowed per product.");
                        }
                        Product::where('id', $product->id)->decrement('stock_quantity', $item->qty);
                    }

                    OrderItem::create([
                        'order_id'       => $order->id,
                        'product_id'     => $item->product_id,
                        'product_sku_id' => $sku?->id,
                        'product_name'   => $item->name,
                        'image'          => $item->product->primaryImage()?->path,
                        'variant'        => $item->variant ?: $sku?->attributeLabel(),
                        'unit_price'     => $item->price,
                        'quantity'       => $item->qty,
                        'line_total'     => $item->line_total,
                    ]);
                }

                if ($coupon) {
                    $coupon->incrementUsage();
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['cart' => $e->getMessage()]);
        }

        $this->cart->clear();
        $this->coupons->remove();
        $this->markCartRecoveredOnOrderPlaced($order);
        $this->linkConversationOnOrderPlaced($order);

        session(['recent_order' => $order->order_number]);

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

    private function orderTotals(float $subtotal, float $discount, string $shippingZone): array
    {
        $taxable  = max(0, $subtotal - $discount);
        $shipping = $shippingZone === 'inside_dhaka'
            ? (float) setting('shipping_inside_dhaka', 60)
            : (float) setting('shipping_outside_dhaka', 120);
        $tax      = round($taxable * (float) setting('tax_percent', 0) / 100, 2);
        $total    = $taxable + $shipping + $tax;

        return compact('shipping', 'tax', 'total');
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

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function syncDraftAbandonedCart($items, float $subtotal, float $total, ?array $customerData = null): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $sessionId = session()->getId();
        $user = Auth::user();

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
            'customer_phone' => $customerData['customer_phone'] ?? $user?->phone ?? $cart?->customer_phone,
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

    private function markCartRecoveredOnOrderPlaced(Order $order): void
    {
        $sessionId = session()->getId();
        $userId = Auth::id();
        $phone = trim((string) $order->customer_phone);
        $email = trim((string) $order->customer_email);

        \App\Models\AbandonedCart::where('status', '!=', 'recovered')
            ->where(function ($q) use ($sessionId, $userId, $phone, $email) {
                $q->where('session_id', $sessionId);
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
                if ($phone !== '') {
                    $q->orWhere('customer_phone', $phone);
                }
                if ($email !== '') {
                    $q->orWhere('customer_email', $email);
                }
            })
            ->update([
                'status'       => 'recovered',
                'recovered_at' => now(),
            ]);
    }

    private function linkConversationOnOrderPlaced(Order $order): void
    {
        $guestToken = request()->cookie('guest_chat_token');
        $query = \App\Models\Conversation::query();

        if ($guestToken) {
            $query->where('guest_token', $guestToken);
        } elseif ($order->user_id) {
            $query->where('user_id', $order->user_id);
        } else {
            return;
        }

        $conversation = $query->latest()->first();

        if ($conversation) {
            $updateData = [];

            if ($order->user_id && !$conversation->user_id) {
                $updateData['user_id'] = $order->user_id;
            }
            if ($order->customer_phone && !$conversation->customer_phone) {
                $updateData['customer_phone'] = $order->customer_phone;
            }
            if ($order->customer_email && !$conversation->customer_email) {
                $updateData['customer_email'] = $order->customer_email;
            }
            if ($order->customer_name && ($conversation->customer_name === 'Guest Visitor' || empty($conversation->customer_name))) {
                $updateData['customer_name'] = $order->customer_name;
            }

            if (!empty($updateData)) {
                $conversation->update($updateData);
            }
        }
    }
}
