<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSku extends Model
{
    protected $table = 'product_skus';
    protected $guarded = [];

    protected $casts = [
        'attributes'       => 'array',
        'price_adjustment' => 'decimal:2',
        'regular_price'    => 'decimal:2',
        'sale_price'       => 'decimal:2',
        'stock_quantity'   => 'integer',
        'is_active'        => 'boolean',
    ];

    public function getCalculatedRegularPrice(): float
    {
        if ($this->regular_price !== null && (float) $this->regular_price > 0) {
            return (float) $this->regular_price;
        }
        $baseReg = (float) ($this->product->regular_price ?? $this->product->price ?? 0);
        return max(0, $baseReg + (float) $this->price_adjustment);
    }

    public function getCalculatedSalePrice(): float
    {
        if ($this->sale_price !== null && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }
        $baseSale = (float) ($this->product->sale_price ?? $this->product->price ?? 0);
        return max(0, $baseSale + (float) $this->price_adjustment);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAttributesData(): array
    {
        $raw = $this->getAttributeValue('attributes');
        if (is_string($raw)) {
            return json_decode($raw, true) ?: [];
        }
        return is_array($raw) ? $raw : [];
    }

    public function attributeLabel(): string
    {
        $attrs = $this->getAttributesData();
        if (empty($attrs)) {
            return 'Standard';
        }
        $parts = [];
        foreach ($attrs as $k => $v) {
            $parts[] = "{$k}: {$v}";
        }
        return implode(' / ', $parts);
    }

    public function isAvailable(int $qty = 1): bool
    {
        return $this->is_active && $this->stock_quantity >= $qty;
    }

    public function matchesVariantString(?string $variantStr): bool
    {
        if (empty($variantStr)) {
            return false;
        }

        $attrs = $this->getAttributesData();
        if (empty($attrs)) {
            return false;
        }

        foreach ($attrs as $k => $v) {
            $valStr = (string) $v;
            if ($valStr !== '' && ! str_contains(mb_strtolower($variantStr), mb_strtolower($valStr))) {
                return false;
            }
        }

        return true;
    }
}
