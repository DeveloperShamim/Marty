<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Saves an order the same way wherever it comes from (the cart checkout or a landing page):
 * fraud score, stock checks and decrements (max 3 per product or size), order items with cost prices,
 * coupon usage, then the after-order jobs (courier history, server-side Purchase, cart recovery).
 *
 * Each item is an object shaped like a CartService line: product_id, sku_id, variant, name, price, qty,
 * line_total and the loaded product.
 */
class OrderPlacer
{
    public function __construct(private FraudDetectionService $fraudService) {}

    /**
     * @param  array  $customer  validated checkout fields (customer_*, shipping_*, city, payment_*, delivery_note)
     * @param  array  $totals    subtotal, discount, shipping, waived, reason, tax, total
     * @param  array  $extra     more order columns, e.g. ['landing_page_id' => 5]
     *
     * @throws \RuntimeException when an item can't be ordered (message is safe to show the customer)
     */
    public function place(array $customer, Collection $items, array $totals, ?Coupon $coupon, Request $request, array $extra = []): Order
    {
        $isCod = ($customer['payment_method'] ?? 'cod') === 'cod';
        $ipAddress = (string) $request->ip();
        $fraudAnalysis = $this->fraudService->analyzeOrder($customer, $totals['total'], $ipAddress);

        $order = DB::transaction(function () use ($customer, $items, $totals, $isCod, $coupon, $ipAddress, $fraudAnalysis, $extra) {
            $order = Order::create([
                'order_number'    => \App\Support\OrderNumber::generate(),
                'user_id'         => Auth::id(),
                'customer_name'   => $customer['customer_name'],
                'customer_phone'  => $customer['customer_phone'],
                'customer_email'  => $customer['customer_email'] ?? null,
                'shipping_address'=> $customer['shipping_address'],
                'city'            => (string) ($customer['city'] ?? ''),
                'postal_code'     => $customer['postal_code'] ?? null,
                'shipping_zone'   => $customer['shipping_zone'],
                'coupon_id'       => $coupon?->id,
                'coupon_code'     => $coupon?->code,
                'subtotal'        => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'shipping_charge' => $totals['shipping'],
                'shipping_waived' => $totals['waived'],
                'free_delivery_reason' => $totals['reason'],
                'tax'             => $totals['tax'],
                'total'           => $totals['total'],
                'payment_method'  => $customer['payment_method'],
                'payment_sender_number' => $isCod ? null : ($customer['payment_sender_number'] ?? null),
                'payment_txn_id'  => $isCod ? null : ($customer['payment_txn_id'] ?? null),
                // The customer's delivery note becomes the order's courier & invoice note.
                'internal_note'   => filled($customer['delivery_note'] ?? null) ? trim($customer['delivery_note']) : null,
                'utm_source'      => session('utm_source'),
                'payment_status'  => 'pending',
                'status'          => 'pending',
                'ip_address'      => $ipAddress,
                'fraud_score'     => $fraudAnalysis['score'] ?? 0,
                'fraud_flags'     => $fraudAnalysis['flags'] ?? [],
            ] + $extra);

            // Clear utm_source after order is placed
            session()->forget('utm_source');

            foreach ($items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                $sku = null;
                if (! empty($item->sku_id)) {
                    $sku = ProductSku::where('id', $item->sku_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();
                } elseif (! empty($item->variant) && $product) {
                    $sku = ProductSku::where('product_id', $product->id)
                        ->lockForUpdate()
                        ->get()
                        ->first(function ($s) use ($item) {
                            return $s->matchesVariantString($item->variant);
                        });
                }

                if (! $sku && $product && $product->skus()->exists()) {
                    throw new \RuntimeException("The selected option for {$item->name} is no longer available. Please remove it from your cart and choose again.");
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

                $itemCostPrice = $sku ? $sku->getEffectiveCostPrice() : (float) ($product?->cost_price ?: 0);

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item->product_id,
                    'product_sku_id' => $sku?->id,
                    'product_name'   => $item->name,
                    'image'          => $item->product->primaryImage()?->path,
                    'variant'        => $item->variant ?: $sku?->attributeLabel(),
                    'unit_price'     => $item->price,
                    'cost_price'     => $itemCostPrice,
                    'quantity'       => $item->qty,
                    'line_total'     => $item->line_total,
                ]);

                if ($product) {
                    $product->syncFlashSaleProgress();
                }
            }

            if ($coupon) {
                $coupon->incrementUsage();
            }

            return $order;
        });

        $this->markCartRecovered($order);

        session(['recent_order' => $order->order_number]);

        // Courier delivery history lookup after the response, so checkout isn't slowed down.
        \App\Jobs\CheckCustomerCourierHistory::dispatchAfterResponse($order->id);

        // Server-side Purchase (Meta Conversions API, GA4) after the response, when switched on in Integrations
        if (tracking_meta_capi_ready() || tracking_ga4_server_ready()) {
            \App\Jobs\SendServerPurchase::dispatchAfterResponse($order->id, ServerTracking::context($request));
        }

        return $order;
    }

    private function markCartRecovered(Order $order): void
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
}
