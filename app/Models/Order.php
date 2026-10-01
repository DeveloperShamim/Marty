<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subtotal'            => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'shipping_charge'     => 'decimal:2',
        'tax'                 => 'decimal:2',
        'total'               => 'decimal:2',
        'pos_cash_tendered'   => 'decimal:2',
        'pos_change_amount'   => 'decimal:2',
        'courier_loss_amount' => 'decimal:2',
        'fraud_score'         => 'integer',
        'fraud_flags'         => 'array',
        'courier_sent_at'     => 'datetime',
        'courier_returned_at' => 'datetime',
        'return_restocked'    => 'boolean',
        'stock_restored'      => 'boolean',
    ];

    /** Statuses where the order's items are still owed to (or with) the customer. */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
    public const PAYMENT_STATUSES = ['pending', 'verified', 'rejected'];
    public const PAYMENT_METHODS = ['cod', 'bkash', 'nagad', 'rocket', 'cash', 'card', 'split'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function secureTrackingToken(): string
    {
        return substr(hash_hmac('sha256', $this->id . '|' . $this->customer_phone . '|' . $this->created_at, config('app.key')), 0, 20);
    }

    public function isPos(): bool
    {
        return $this->order_type === 'pos';
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cod'    => 'Cash on Delivery',
            'bkash'  => 'bKash',
            'nagad'  => 'Nagad',
            'rocket' => 'Rocket',
            'cash'   => 'Cash (POS)',
            'card'   => 'Card (POS)',
            'split'  => 'Split / Multi-Pay',
            default  => ucfirst($this->payment_method),
        };
    }

    public function isMobileBanking(): bool
    {
        return in_array($this->payment_method, ['bkash', 'nagad', 'rocket'], true);
    }

    /** Tailwind badge classes for the fulfilment status. */
    public function statusBadge(): string
    {
        return match ($this->status) {
            'pending'    => 'bg-gray-100 text-gray-600',
            'confirmed'  => 'bg-blue-100 text-blue-700',
            'processing' => 'bg-indigo-100 text-indigo-700',
            'shipped'    => 'bg-blue-100 text-blue-700',
            'delivered'  => 'bg-green-100 text-green-700',
            'cancelled'  => 'bg-red-100 text-red-700',
            'returned'   => 'bg-amber-100 text-amber-800 border border-amber-300',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    /** Tailwind badge classes for the payment status. */
    public function paymentBadge(): string
    {
        return match ($this->payment_status) {
            'pending'  => 'bg-amber-100 text-amber-700',
            'verified' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }

    public function utmSourceIcon(): string
    {
        $source = strtolower($this->utm_source ?? '');
        
        if (str_contains($source, 'facebook') || str_contains($source, 'fb') || str_contains($source, 'meta')) {
            return '🟦';
        }
        if (str_contains($source, 'google')) {
            return '🔍';
        }
        if (str_contains($source, 'instagram') || str_contains($source, 'ig')) {
            return '📷';
        }
        if (str_contains($source, 'tiktok')) {
            return '📱';
        }
        if (str_contains($source, 'youtube')) {
            return '▶️';
        }
        if ($source) {
            return '🔗';
        }
        
        return '🌐'; // Direct or unknown
    }

    /** Return reserved stock to product SKUs & products (e.g. when an order is cancelled or refunded). */
    public function restoreStock(): void
    {
        // Idempotent: stock is only ever put back once until it is reserved again.
        if ($this->stock_restored) {
            return;
        }

        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            if ($item->product_sku_id) {
                ProductSku::where('id', $item->product_sku_id)->increment('stock_quantity', $item->quantity);
                $item->product?->syncTotalStock();
            } elseif ($item->product_id) {
                Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
            }
        }

        $this->forceFill(['stock_restored' => true])->saveQuietly();
    }

    /** Take stock out again when a cancelled/returned order is reactivated. */
    public function reserveStock(): void
    {
        if (! $this->stock_restored) {
            return;
        }

        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            if ($item->product_sku_id && ($sku = ProductSku::find($item->product_sku_id))) {
                $sku->update(['stock_quantity' => max(0, (int) $sku->stock_quantity - $item->quantity)]);
                $item->product?->syncTotalStock();
            } elseif ($item->product_id && ($product = Product::find($item->product_id))) {
                $product->update(['stock_quantity' => max(0, (int) $product->stock_quantity - $item->quantity)]);
            }
        }

        $this->forceFill(['stock_restored' => false])->saveQuietly();
    }

    /**
     * Put a cancelled/returned order back into play: take its stock out again,
     * re-count its coupon use and forget the old return (and its courier loss).
     * Attributes are filled but not saved; the caller saves.
     */
    public function prepareReactivation(): void
    {
        if (! in_array($this->status, ['cancelled', 'returned'], true)) {
            return;
        }

        $this->reserveStock();

        if ($this->status === 'cancelled' && $this->coupon_id) {
            $this->coupon?->incrementUsage();
        }

        if ($this->status === 'returned') {
            $this->forceFill([
                'return_restocked'    => false,
                'return_type'         => null,
                'return_reason'       => null,
                'courier_returned_at' => null,
                'courier_loss_amount' => 0,
            ]);
        }
    }

    /** Release a coupon use when an order is cancelled/rejected. */
    public function releaseCoupon(): void
    {
        if ($this->coupon_id) {
            $this->coupon?->decrementUsage();
        }
    }

    public function shouldRestoreStockOnCancel(): bool
    {
        // A return already decided whether its stock went back (it may be damaged).
        return ! in_array($this->status, ['cancelled', 'returned'], true);
    }

    public function isDispatchedToCourier(): bool
    {
        return ! empty($this->courier_sent_at) || ! empty($this->courier_tracking_code) || ($this->status === 'shipped' && ! empty($this->courier_name));
    }

    public function courierLabel(): string
    {
        return match (strtolower((string) $this->courier_name)) {
            'steadfast' => 'Steadfast Courier',
            'pathao'    => 'Pathao Courier',
            'redx'      => 'RedX Courier',
            default     => ucfirst((string) $this->courier_name),
        };
    }

    public function courierTrackingUrl(): ?string
    {
        if (! $this->courier_tracking_code) {
            return null;
        }

        return match (strtolower((string) $this->courier_name)) {
            'steadfast' => 'https://steadfast.com.bd/t/' . rawurlencode($this->courier_tracking_code),
            'pathao'    => 'https://pathao.com/courier-tracking/?tracking_id=' . rawurlencode($this->courier_tracking_code),
            'redx'      => 'https://redx.com.bd/track-parcel/' . rawurlencode($this->courier_tracking_code),
            default     => null,
        };
    }

    public function fraudRiskLevel(): string
    {
        $score = (int) $this->fraud_score;
        if ($score >= 55) {
            return 'high';
        }
        if ($score >= 25) {
            return 'medium';
        }

        return 'low';
    }

    public function fraudBadgeClass(): string
    {
        return match ($this->fraudRiskLevel()) {
            'high'   => 'bg-rose-100 text-rose-800 border border-rose-300 font-extrabold',
            'medium' => 'bg-amber-100 text-amber-800 border border-amber-300 font-extrabold',
            default  => 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-extrabold',
        };
    }
}
