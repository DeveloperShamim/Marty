<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'regular_price'   => 'decimal:2',
        'sale_price'      => 'decimal:2',
        'rating'          => 'decimal:2',
        'is_published'    => 'boolean',
        'is_featured'     => 'boolean',
        'is_new_arrival'  => 'boolean',
        'is_best_seller'  => 'boolean',
        'is_flash_sale'   => 'boolean',
        'specifications'  => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class)->orderBy('id');
    }

    public function activeSkus(): HasMany
    {
        return $this->hasMany(ProductSku::class)->where('is_active', true)->orderBy('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getSoldUnitsAttribute(): int
    {
        return (int) OrderItem::where('product_id', $this->id)
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->sum('quantity');
    }

    /**
     * Calculate live Flash Sale progress (% claimed/sold).
     * High-converting Smart Scaling: Starts at a realistic organic baseline (74%–82%)
     * and dynamically fills up to 100% as actual customer orders are placed.
     */
    public function calculatedFlashSaleProgress(): int
    {
        $currentStock = $this->relationLoaded('skus') && $this->skus->isNotEmpty()
            ? (int) $this->skus->where('is_active', true)->sum('stock_quantity')
            : (int) $this->stock_quantity;

        // Completely sold out always hits 100%
        if ($currentStock <= 0) {
            return 100;
        }

        // Natural starting baseline between 74% and 82% per product, or admin custom progress if >= 50
        $baseline = ($this->flash_sale_progress && $this->flash_sale_progress >= 50)
            ? (int) $this->flash_sale_progress
            : (74 + ($this->id % 9));

        $baseline = max(60, min(92, $baseline));

        $soldUnits = $this->sold_units;

        if ($soldUnits <= 0) {
            return $baseline;
        }

        $totalPool = $soldUnits + $currentStock;
        $soldRatio = $totalPool > 0 ? ($soldUnits / $totalPool) : 1;
        $remainingRoom = 100 - $baseline;

        $progress = (int) round($baseline + ($soldRatio * $remainingRoom));

        return min(99, max($baseline, $progress));
    }

    public function syncFlashSaleProgress(): int
    {
        $progress = $this->calculatedFlashSaleProgress();
        $this->updateQuietly(['flash_sale_progress' => $progress]);
        return $progress;
    }

    public function syncTotalStock(): void
    {
        if ($this->skus()->exists()) {
            $totalStock = (int) $this->activeSkus()->sum('stock_quantity');
            $this->update(['stock_quantity' => $totalStock]);
        }
        $this->syncFlashSaleProgress();
    }

    public function isLowStock(int $threshold = 3): bool
    {
        if ($this->skus()->exists()) {
            return $this->skus()->where('stock_quantity', '<=', $threshold)->exists();
        }
        return (int) $this->stock_quantity <= $threshold;
    }

    public function isOutOfStock(): bool
    {
        if ($this->relationLoaded('skus') ? $this->skus->isNotEmpty() : $this->skus()->exists()) {
            $activeSkus = $this->relationLoaded('skus')
                ? $this->skus->where('is_active', true)
                : $this->activeSkus()->get();
            if ($activeSkus->isNotEmpty()) {
                return (int) $activeSkus->sum('stock_quantity') <= 0;
            }
        }

        return (int) $this->stock_quantity <= 0;
    }

    public function isInStock(): bool
    {
        return ! $this->isOutOfStock();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->approved();
    }

    /** Recompute rating + reviews_count from approved customer reviews. */
    public function recalculateRatingFromReviews(): void
    {
        $approved = $this->reviews()->approved();
        $count = (clone $approved)->count();
        $avg = $count > 0 ? round((float) (clone $approved)->avg('rating'), 2) : 0;

        $this->forceFill([
            'rating'         => $avg,
            'reviews_count'  => $count,
        ])->saveQuietly();
    }

    /* --------- scopes --------- */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /* --------- pricing helpers --------- */
    public function getPriceAttribute(): float
    {
        return (float) ($this->sale_price ?? $this->regular_price);
    }

    public function getOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->regular_price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->on_sale || (float) $this->regular_price <= 0) {
            return 0;
        }
        return (int) round(100 - ($this->price / (float) $this->regular_price * 100));
    }

    /* --------- image helpers --------- */
    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function imageUrl(): string
    {
        return image_url($this->primaryImage()?->path, $this->slug);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Normalized specification rows: [['label' => '...', 'value' => '...'], ...]
     */
    public function specificationRows(): array
    {
        $rows = collect($this->specifications ?? [])
            ->map(function ($row) {
                if (is_string($row)) {
                    $row = trim($row);
                    if ($row === '') {
                        return null;
                    }
                    if (str_contains($row, ':')) {
                        [$label, $value] = array_map('trim', explode(':', $row, 2));

                        return ['label' => $label, 'value' => $value];
                    }

                    return ['label' => '', 'value' => $row];
                }

                $label = trim((string) ($row['label'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));
                if ($label === '' && $value === '') {
                    return null;
                }

                return ['label' => $label, 'value' => $value];
            })
            ->filter()
            ->values()
            ->all();

        return $rows;
    }

    /** Bullet lines for cards / PDP sidebar (e.g. "Model: Ryzen 5"). */
    public function specificationBullets(int $limit = 6): array
    {
        return collect($this->specificationRows())
            ->map(function (array $row) {
                if ($row['label'] !== '' && $row['value'] !== '') {
                    return $row['label'] . ': ' . $row['value'];
                }

                return $row['label'] !== '' ? $row['label'] : $row['value'];
            })
            ->filter()
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Smart Auto-calculated Flash Sale progress percentage based on completed orders & current stock.
     */
    public function getFlashSaleProgressAttribute($value): int
    {
        $currentStock = (int) $this->stock_quantity;

        // Calculate total valid units sold from orders
        $unitsSold = (int) \App\Models\OrderItem::where('product_id', $this->id)
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'failed', 'refunded']);
            })
            ->sum('quantity');

        // Admin baseline floor override (if set, defaults to 0 if not set)
        $baseline = is_null($value) ? 0 : (int) $value;

        if ($unitsSold <= 0) {
            return min(99, $baseline);
        }

        $totalUnits = $unitsSold + max(1, $currentStock);
        $realRatio = $totalUnits > 0 ? ($unitsSold / $totalUnits) : 0;

        // Scale real sales on top of baseline remaining range
        $addedProgress = (int) round($realRatio * max(10, 100 - $baseline));

        return min(99, $baseline + $addedProgress);
    }
}
