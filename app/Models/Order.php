<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'fraud_score'     => 'integer',
        'fraud_flags'     => 'array',
        'courier_sent_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    public const PAYMENT_STATUSES = ['pending', 'verified', 'rejected'];
    public const PAYMENT_METHODS = ['cod', 'bkash', 'nagad', 'rocket'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cod'    => 'Cash on Delivery',
            'bkash'  => 'bKash',
            'nagad'  => 'Nagad',
            'rocket' => 'Rocket',
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
        $this->loadMissing('items');

        foreach ($this->items as $item) {
            if ($item->product_sku_id) {
                ProductSku::where('id', $item->product_sku_id)->increment('stock_quantity', $item->quantity);
            }
            if ($item->product_id) {
                Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
            }
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
        return ! in_array($this->status, ['cancelled'], true);
    }

    public function isDispatchedToCourier(): bool
    {
        return ! empty($this->courier_name) && ! empty($this->courier_tracking_code);
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
