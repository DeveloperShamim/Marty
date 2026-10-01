<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $storeName = Setting::get('site_name', 'MARTY STORE');
        $storePhone = Setting::get('site_phone', '+880 1700-000000');
        $storeAddress = Setting::get('site_address', 'Dhaka, Bangladesh');

        return view('admin.pos.index', compact('categories', 'storeName', 'storePhone', 'storeAddress'));
    }

    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $categoryId = $request->input('category_id');

        $query = Product::with(['images', 'skus' => function ($sq) {
            $sq->where('is_active', true);
        }])
        ->where('is_published', true);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        $products = $query->take(30)->get()->map(function ($p) {
            $effectivePrice = (float) ($p->sale_price ?: $p->regular_price);
            $hasSkus = $p->skus->isNotEmpty();

            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku ?: '',
                'barcode'        => $p->getBarcode(),
                'price'          => $effectivePrice,
                'cost_price'     => (float) ($p->cost_price ?: 0),
                'stock'          => (int) $p->stock_quantity,
                'image'          => $p->imageUrl(),
                'has_skus'       => $hasSkus,
                'skus'           => $p->skus->map(function ($s) use ($p) {
                    return [
                        'id'         => $s->id,
                        'sku'        => $s->sku ?: '',
                        'barcode'    => $s->getBarcode(),
                        'name'       => $s->attributeLabel(),
                        'price'      => $s->getCalculatedSalePrice() ?: $s->getCalculatedRegularPrice(),
                        'cost_price' => (float) $s->getEffectiveCostPrice(),
                        'stock'      => (int) $s->stock_quantity,
                    ];
                }),
            ];
        });

        return response()->json(['products' => $products]);
    }

    public function scanBarcode(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            return response()->json(['found' => false]);
        }

        // 1. Check in Product SKUs
        $sku = ProductSku::with(['product.images'])
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code);
            })
            ->first();

        if ($sku && $sku->product) {
            $p = $sku->product;
            return response()->json([
                'found' => true,
                'item'  => [
                    'product_id'     => $p->id,
                    'product_sku_id' => $sku->id,
                    'name'           => $p->name . ' (' . $sku->attributeLabel() . ')',
                    'barcode'        => $sku->getBarcode(),
                    'sku'            => $sku->sku ?: $p->sku,
                    'variant'        => $sku->attributeLabel(),
                    'price'          => $sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice(),
                    'cost_price'     => (float) $sku->getEffectiveCostPrice(),
                    'stock'          => (int) $sku->stock_quantity,
                    'image'          => $p->imageUrl(),
                ],
            ]);
        }

        // 2. Check in Products
        $product = Product::with(['images', 'skus'])
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code)
                  ->orWhere('id', $code);
            })
            ->first();

        if ($product) {
            if ($product->skus->isNotEmpty()) {
                // Return flag to open variant modal
                return response()->json([
                    'found'      => true,
                    'needs_sku'  => true,
                    'product'    => [
                        'id'    => $product->id,
                        'name'  => $product->name,
                        'skus'  => $product->skus->map(fn($s) => [
                            'id'         => $s->id,
                            'name'       => $s->attributeLabel(),
                            'price'      => $s->getCalculatedSalePrice() ?: $s->getCalculatedRegularPrice(),
                            'cost_price' => (float) $s->getEffectiveCostPrice(),
                            'stock'      => (int) $s->stock_quantity,
                        ]),
                    ],
                ]);
            }

            return response()->json([
                'found' => true,
                'item'  => [
                    'product_id'     => $product->id,
                    'product_sku_id' => null,
                    'name'           => $product->name,
                    'barcode'        => $product->getBarcode(),
                    'sku'            => $product->sku ?: '',
                    'variant'        => null,
                    'price'          => (float) ($product->sale_price ?: $product->regular_price),
                    'cost_price'     => (float) ($product->cost_price ?: 0),
                    'stock'          => (int) $product->stock_quantity,
                    'image'          => $product->imageUrl(),
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function customerLookup(Request $request)
    {
        $phone = trim((string) $request->input('phone', ''));
        if (strlen($phone) < 4) {
            return response()->json(['found' => false]);
        }

        $customer = Order::where('customer_phone', 'like', "%{$phone}%")
            ->latest()
            ->first();

        if ($customer) {
            return response()->json([
                'found' => true,
                'customer' => [
                    'name'    => $customer->customer_name,
                    'phone'   => $customer->customer_phone,
                    'address' => $customer->shipping_address,
                ],
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.product_sku_id'=> ['nullable', 'exists:product_skus,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.price'         => ['required', 'numeric', 'min:0'],
            'customer_name'         => ['nullable', 'string', 'max:100'],
            'customer_phone'        => ['nullable', 'string', 'max:30'],
            'discount'              => ['nullable', 'numeric', 'min:0'],
            'shipping_charge'       => ['nullable', 'numeric', 'min:0'],
            'payment_method'        => ['required', 'string', 'in:cash,bkash,nagad,rocket,card,split'],
            'cash_tendered'         => ['nullable', 'numeric', 'min:0'],
            'note'                  => ['nullable', 'string', 'max:255'],
        ]);

        try {
            return DB::transaction(fn () => $this->createPosOrder($validated));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function createPosOrder(array $validated)
    {
        $subtotal = 0;
        $itemsData = [];
        $stockMoves = [];

        foreach ($validated['items'] as $it) {
            $qty = (int) $it['quantity'];
            $price = (float) $it['price'];
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $prod = Product::with('images')->lockForUpdate()->find($it['product_id']);
            $sku = null;
            if (! empty($it['product_sku_id'])) {
                $sku = ProductSku::where('product_id', $prod->id)->lockForUpdate()->find($it['product_sku_id']);
                if (! $sku) {
                    throw new \RuntimeException("The selected option does not belong to \"{$prod->name}\".");
                }
            } elseif ($prod->skus()->exists()) {
                throw new \RuntimeException("Please choose an option (size/colour) for \"{$prod->name}\".");
            }

            // Several cart lines can hit the same SKU/product, so count what this sale already took.
            $stockKey = $sku ? "sku:{$sku->id}" : "product:{$prod->id}";
            $stockMoves[$stockKey] = ($stockMoves[$stockKey] ?? 0) + $qty;
            $available = (int) ($sku ? $sku->stock_quantity : $prod->stock_quantity);
            if ($stockMoves[$stockKey] > $available) {
                $label = $sku ? "{$prod->name} ({$sku->attributeLabel()})" : $prod->name;
                throw new \RuntimeException("Only {$available} of \"{$label}\" in stock.");
            }

            $costPrice = $sku ? (float)$sku->getEffectiveCostPrice() : (float)($prod->cost_price ?: 0);

            $itemsData[] = [
                'product_id'     => $prod->id,
                'product_sku_id' => $sku?->id,
                'product_name'   => $prod->name,
                'variant'        => $sku?->attributeLabel(),
                'image'          => $prod->primaryImage()?->path,
                'unit_price'     => $price,
                'cost_price'     => $costPrice,
                'quantity'       => $qty,
                'line_total'     => $lineTotal,
            ];

            // Immediate stock deduction for POS sale; product total follows its SKUs.
            if ($sku) {
                $sku->decrement('stock_quantity', $qty);
                $prod->syncTotalStock();
            } else {
                $prod->decrement('stock_quantity', $qty);
            }
        }

        $discount = (float) ($validated['discount'] ?? 0);
        $shipping = (float) ($validated['shipping_charge'] ?? 0);
        $total = max(0, $subtotal - $discount + $shipping);

        $cashTendered = (float) ($validated['cash_tendered'] ?? $total);
        $changeAmount = max(0, $cashTendered - $total);

        $orderNumber = 'POS-' . date('ymd') . '-' . strtoupper(Str::random(4));

        $order = Order::create([
            'order_number'       => $orderNumber,
            'order_type'         => 'pos',
            'user_id'            => auth()->id(),
            'customer_name'      => ($validated['customer_name'] ?? null) ?: 'Walk-in Customer',
            'customer_phone'     => ($validated['customer_phone'] ?? null) ?: 'N/A',
            'customer_email'     => null,
            'shipping_address'   => 'POS Counter Sale',
            'city'               => 'In-Store',
            'shipping_zone'      => 'inside_dhaka',
            'subtotal'           => $subtotal,
            'discount_amount'    => $discount,
            'shipping_charge'    => $shipping,
            'tax'                => 0,
            'total'              => $total,
            'pos_cash_tendered'  => $cashTendered,
            'pos_change_amount'  => $changeAmount,
            'payment_method'     => $validated['payment_method'],
            'payment_status'     => 'verified',
            'status'             => 'delivered',
            'internal_note'      => $validated['note'] ?? 'POS Cash Register Sale',
        ]);

        foreach ($itemsData as $itemRow) {
            $itemRow['order_id'] = $order->id;
            OrderItem::create($itemRow);
        }

        return response()->json([
            'success'      => true,
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'total'        => $order->total,
            'change'       => $order->pos_change_amount,
            'receipt_url'  => route('admin.pos.receipt', $order),
        ]);
    }

    public function receipt(Order $order)
    {
        $order->load(['items.product', 'items.sku', 'user']);
        $storeName = Setting::get('site_name', 'MARTY STORE');
        $storePhone = Setting::get('site_phone', '+880 1700-000000');
        $storeAddress = Setting::get('site_address', 'Dhaka, Bangladesh');

        return view('admin.pos.receipt', compact('order', 'storeName', 'storePhone', 'storeAddress'));
    }
}
