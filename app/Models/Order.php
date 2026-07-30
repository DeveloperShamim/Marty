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
}
