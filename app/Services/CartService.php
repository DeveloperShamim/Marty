<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart. The session only stores product id, quantity
 * and the chosen variant label — all prices/names/images are resolved from the
 * database at read time so totals are always server-authoritative.
 */
class CartService
{
    private const KEY = 'cart';

    private function raw(): array
    {
        return session(self::KEY, []);
    }

    private function persist(array $lines): void
    {
        session([self::KEY => $lines]);
    }

    private function lineKey(int $productId, ?string $variant, ?int $skuId = null): string
    {
        return $productId . '|' . ($variant ?? '') . '|' . ($skuId ?? '');
    }

    public function add(int $productId, int $qty = 1, ?string $variant = null, ?int $skuId = null): void
    {
        $qty   = max(1, $qty);
        $lines = $this->raw();
        $key   = $this->lineKey($productId, $variant, $skuId);

        if (isset($lines[$key])) {
            $lines[$key]['qty'] += $qty;
        } else {
            $lines[$key] = [
                'product_id' => $productId,
                'qty'        => $qty,
                'variant'    => $variant,
                'sku_id'     => $skuId,
            ];
        }

        $this->persist($lines);
    }

    public function update(string $key, int $qty): void
    {
        $lines = $this->raw();
        if (! isset($lines[$key])) {
            return;
        }
        if ($qty <= 0) {
            unset($lines[$key]);
        } else {
            $lines[$key]['qty'] = $qty;
        }
        $this->persist($lines);
    }

    public function remove(string $key): void
    {
        $lines = $this->raw();
        unset($lines[$key]);
        $this->persist($lines);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Resolve the cart into a collection of display-ready line items.
     * Silently drops lines whose product was deleted / unpublished.
     */
    public function items(): Collection
    {
        $lines = $this->raw();
        if (empty($lines)) {
            return collect();
        }

        $products = Product::with(['images', 'skus', 'variants'])
            ->whereIn('id', collect($lines)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return collect($lines)->map(function ($line, $key) use ($products) {
            $product = $products->get($line['product_id']);
            if (! $product || ! $product->is_published) {
                return null;
            }

            $skuId = $line['sku_id'] ?? null;
            $sku = null;
            $variantObj = null;

            if ($skuId) {
                $sku = $product->skus->firstWhere('id', $skuId);
            } elseif (! empty($line['variant'])) {
                $variantText = (string) $line['variant'];
                if ($product->skus->isNotEmpty()) {
                    $sku = $product->skus->first(function ($s) use ($variantText) {
                        return $s->matchesVariantString($variantText);
                    });
                }
                if (! $sku && $product->variants->isNotEmpty()) {
                    $variantObj = $product->variants->first(function ($v) use ($variantText) {
                        return str_contains(mb_strtolower($variantText), mb_strtolower($v->value))
                            || mb_strtolower(trim($variantText)) === mb_strtolower(trim($v->value));
                    });
                }
            }

            $priceAdjustment = 0.0;
            $maxStock = (int) $product->stock_quantity;

            if ($sku) {
                $priceAdjustment = (float) $sku->price_adjustment;
                $maxStock = (int) $sku->stock_quantity;
            } elseif ($variantObj) {
                $priceAdjustment = (float) $variantObj->price_delta;
                $maxStock = (int) $variantObj->stock;
            }

            $basePrice = (float) $product->price;
            $price = $basePrice <= 0 ? max(0, $priceAdjustment) : max(0, $basePrice + $priceAdjustment);

            return (object) [
                'key'            => $key,
                'product'        => $product,
                'product_id'     => $product->id,
                'sku_id'         => $sku?->id,
                'sku'            => $sku,
                'name'           => $product->name,
                'slug'           => $product->slug,
                'image'          => $product->imageUrl(),
                'variant'        => $line['variant'] ?: $sku?->attributeLabel(),
                'price'          => $price,
                'unit_price'     => $price,
                'qty'            => (int) $line['qty'],
                'max_stock'      => $maxStock,
                'line_total'     => $price * (int) $line['qty'],
                'is_out_of_stock'=> $maxStock < (int) $line['qty'],
            ];
        })->filter()->values();
    }

    public function count(): int
    {
        return (int) $this->items()->sum('qty');
    }

    public function qtyInCart(int $productId, ?string $variant = null, ?int $skuId = null): int
    {
        $key = $this->lineKey($productId, $variant, $skuId);
        $line = $this->raw()[$key] ?? null;

        return $line ? (int) $line['qty'] : 0;
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    public function toArray(): array
    {
        return [
            'count'    => $this->count(),
            'subtotal' => $this->subtotal(),
        ];
    }
}
