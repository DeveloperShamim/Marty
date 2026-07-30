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
        'stock_quantity'   => 'integer',
        'is_active'        => 'boolean',
    ];

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
