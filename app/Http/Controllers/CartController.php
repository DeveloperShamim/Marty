<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('storefront.cart', [
            'items'    => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:3'],
            'variant'    => ['nullable', 'string', 'max:120'],
            'sku_id'     => ['nullable', 'exists:product_skus,id'],
        ]);

        $product = Product::published()->findOrFail($data['product_id']);
        $qty     = min(3, max(1, (int) ($data['qty'] ?? 1)));
        $variant = isset($data['variant']) && trim((string)$data['variant']) !== '' ? trim((string)$data['variant']) : null;
        $skuId   = ! empty($data['sku_id']) ? (int) $data['sku_id'] : null;

        // If no explicit sku_id provided, attempt lookup from variant text if product has SKUs or variants
        $sku = null;
        $variantObj = null;
        if ($skuId) {
            $sku = $product->skus()->where('is_active', true)->find($skuId);
        } elseif ($variant) {
            if ($product->skus()->exists()) {
                $sku = $product->skus()->where('is_active', true)->get()->first(function ($s) use ($variant) {
                    return $s->matchesVariantString($variant);
                });
                if ($sku) {
                    $skuId = $sku->id;
                }
            }
            if (! $sku && $product->variants()->exists()) {
                $variantObj = $product->variants()->get()->first(function ($v) use ($variant) {
                    return str_contains(mb_strtolower($variant), mb_strtolower($v->value))
                        || mb_strtolower(trim($variant)) === mb_strtolower(trim($v->value));
                });
            }
        }

        $hasOptions = $product->skus()->exists() || $product->variants()->exists();
        if ($hasOptions && empty($sku) && empty($variantObj) && empty($variant)) {
            $message = "Please select a product option before adding to cart.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['cart' => $message]);
        }

        $availableStock = (int) $product->stock_quantity;
        if ($sku) {
            $availableStock = (int) $sku->stock_quantity;
        } elseif ($variantObj) {
            $availableStock = (int) $variantObj->stock;
        }
        $maxAllowed = min(3, $availableStock);
        $inCart = $this->cart->qtyInCart($product->id, $variant, $skuId);

        if ($availableStock < 1) {
            $label = $sku ? "{$product->name} ({$sku->attributeLabel()})" : $product->name;
            $message = "\"{$label}\" is out of stock.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['cart' => $message]);
        }

        if ($inCart + $qty > $maxAllowed) {
            $label = $sku ? "{$product->name} ({$sku->attributeLabel()})" : $product->name;
            if ($availableStock < 3) {
                $message = "Only {$availableStock} of \"{$label}\" available in stock.";
            } else {
                $message = "Maximum 3 items allowed per product variant.";
            }
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['cart' => $message]);
        }

        $this->cart->add($product->id, $qty, $variant, $skuId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'message' => "Added \"{$product->name}\" to cart",
                'cart'    => $this->cart->toArray(),
                'drawer'  => view('storefront.partials.cart-drawer-items', [
                    'items'    => $this->cart->items(),
                    'subtotal' => $this->cart->subtotal(),
                ])->render(),
            ]);
        }

        return back()->with('status', "Added \"{$product->name}\" to cart");
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $lines = session('cart', []);
        $line  = $lines[$data['key']] ?? null;
        if ($line && (int) $data['qty'] > 0) {
            $product = Product::published()->find($line['product_id']);
            if ($product) {
                $sku = ! empty($line['sku_id']) ? $product->skus()->find($line['sku_id']) : null;
                $maxStock = $sku ? (int) $sku->stock_quantity : (int) $product->stock_quantity;
                $maxAllowed = min(3, $maxStock);

                if ((int) $data['qty'] > $maxAllowed) {
                    $label = $sku ? "{$product->name} ({$sku->attributeLabel()})" : $product->name;
                    $message = $maxStock < 3 ? "Only {$maxStock} of \"{$label}\" available in stock." : "Maximum 3 items allowed per product variant.";
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['ok' => false, 'message' => $message], 422);
                    }

                    return back()->withErrors(['cart' => $message]);
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['ok' => false, 'message' => $message], 422);
                    }

                    return back()->withErrors(['cart' => $message]);
                }
            }
        }

        $this->cart->update($data['key'], (int) $data['qty']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'     => true,
                'cart'   => $this->cart->toArray(),
                'drawer' => view('storefront.partials.cart-drawer-items', [
                    'items'    => $this->cart->items(),
                    'subtotal' => $this->cart->subtotal(),
                ])->render(),
            ]);
        }

        return back();
    }

    public function remove(Request $request)
    {
        $data = $request->validate(['key' => ['required', 'string']]);
        $this->cart->remove($data['key']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'     => true,
                'cart'   => $this->cart->toArray(),
                'drawer' => view('storefront.partials.cart-drawer-items', [
                    'items'    => $this->cart->items(),
                    'subtotal' => $this->cart->subtotal(),
                ])->render(),
            ]);
        }

        return back();
    }
}
