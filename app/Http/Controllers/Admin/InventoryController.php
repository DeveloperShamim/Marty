<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['skus', 'category'])->latest();

        $filter = $request->input('filter', 'all'); // 'all', 'low_stock', 'out_of_stock'

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhereHas('skus', function ($sq) use ($term) {
                        $sq->where('sku', 'like', "%{$term}%")
                            ->orWhere('attributes', 'like', "%{$term}%");
                    });
            });
        }

        if ($filter === 'low_stock') {
            $query->where(function ($q) {
                $q->where('stock_quantity', '<=', 3)
                    ->orWhereHas('skus', fn ($sq) => $sq->where('stock_quantity', '<=', 3));
            });
        } elseif ($filter === 'out_of_stock') {
            $query->where(function ($q) {
                $q->where('stock_quantity', '<=', 0)
                    ->orWhereHas('skus', fn ($sq) => $sq->where('stock_quantity', '<=', 0));
            });
        }

        $products = $query->paginate(20)->withQueryString();

        $lowStockCount = ProductSku::where('stock_quantity', '<=', 3)->count()
            + Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 3)->count();

        $outOfStockCount = ProductSku::where('stock_quantity', '<=', 0)->count()
            + Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 0)->count();

        $totalSkusCount = ProductSku::count()
            + Product::whereDoesntHave('skus')->count();

        return view('admin.inventory.index', compact(
            'products',
            'filter',
            'lowStockCount',
            'outOfStockCount',
            'totalSkusCount'
        ) + ['q' => $term]);
    }

    public function updateStock(Request $request)
    {
        $data = $request->validate([
            'sku_id'         => ['nullable', 'exists:product_skus,id'],
            'product_id'     => ['required_without:sku_id', 'exists:products,id'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! empty($data['sku_id'])) {
            $sku = ProductSku::findOrFail($data['sku_id']);
            $update = ['stock_quantity' => (int) $data['stock_quantity']];
            if (isset($data['cost_price'])) {
                $update['cost_price'] = (float) $data['cost_price'];
            }
            $sku->update($update);
            $sku->product->syncTotalStock();

            if (isset($data['cost_price'])) {
                // Also update parent average cost
                $product = $sku->product->fresh(['skus']);
                $totalStock = (int) $product->skus->sum('stock_quantity');
                if ($totalStock > 0) {
                    $totalVal = (float) $product->skus->sum(fn ($s) => $s->stock_quantity * (float) ($s->cost_price ?: 0));
                    $product->update(['cost_price' => round($totalVal / $totalStock, 2)]);
                }
            }

            $msg = "Stock updated to {$sku->stock_quantity} for {$sku->product->name} ({$sku->attributeLabel()}).";
        } else {
            $product = Product::findOrFail($data['product_id']);
            $update = ['stock_quantity' => (int) $data['stock_quantity']];
            if (isset($data['cost_price'])) {
                $update['cost_price'] = (float) $data['cost_price'];
            }
            $product->update($update);

            $msg = "Stock updated to {$product->stock_quantity} for {$product->name}.";
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return back()->with('status', $msg);
    }

    /**
     * Restock inventory with Weighted Average Cost (AVCO) calculation.
     * When new stock arrives at a different buying cost, it recalculates
     * the product's moving weighted average cost and updates cost_price.
     */
    public function addStock(Request $request)
    {
        $data = $request->validate([
            'sku_id'         => ['nullable', 'exists:product_skus,id'],
            'product_id'     => ['required_without:sku_id', 'exists:products,id'],
            'added_quantity' => ['required', 'integer', 'min:1'],
            'unit_cost'      => ['required', 'numeric', 'min:0'],
        ]);

        $addedQty = (int) $data['added_quantity'];
        $batchCost = (float) $data['unit_cost'];

        if (! empty($data['sku_id'])) {
            $sku = ProductSku::with('product')->findOrFail($data['sku_id']);
            $product = $sku->product;
            $currentStock = max(0, (int) $sku->stock_quantity);
            $currentCost = (float) ($sku->cost_price ?: $product->cost_price ?: 0);
            $newStock = $currentStock + $addedQty;

            // Moving Weighted Average Cost formula:
            // ((current_stock * current_cost) + (added_qty * batch_cost)) / (current_stock + added_qty)
            if ($currentStock > 0 && $currentCost > 0) {
                $newAvgCost = round((($currentStock * $currentCost) + ($addedQty * $batchCost)) / $newStock, 2);
            } else {
                $newAvgCost = round($batchCost, 2);
            }

            $sku->update([
                'stock_quantity' => $newStock,
                'cost_price'     => $newAvgCost,
            ]);

            $product->syncTotalStock();

            // Update parent product weighted average cost across all variant inventory
            $product->refresh();
            $totalActiveStock = (int) $product->skus()->sum('stock_quantity');
            if ($totalActiveStock > 0) {
                $totalValuation = (float) $product->skus()->get()->sum(function ($s) {
                    $c = (float) ($s->cost_price ?: 0);
                    return $s->stock_quantity * $c;
                });
                $parentAvgCost = round($totalValuation / $totalActiveStock, 2);
            } else {
                $parentAvgCost = $newAvgCost;
            }

            $product->update(['cost_price' => $parentAvgCost]);

            $msg = "Added +{$addedQty} units to {$product->name} ({$sku->attributeLabel()}). New Stock: {$newStock}, Average Buying Cost: ৳" . number_format($newAvgCost, 2) . " (Catalog Avg: ৳" . number_format($parentAvgCost, 2) . ").";
        } else {
            $product = Product::findOrFail($data['product_id']);
            $currentStock = max(0, (int) $product->stock_quantity);
            $currentCost = (float) ($product->cost_price ?: 0);
            $newStock = $currentStock + $addedQty;

            // Moving Weighted Average Cost formula:
            if ($currentStock > 0 && $currentCost > 0) {
                $newAvgCost = round((($currentStock * $currentCost) + ($addedQty * $batchCost)) / $newStock, 2);
            } else {
                $newAvgCost = round($batchCost, 2);
            }

            $product->update([
                'stock_quantity' => $newStock,
                'cost_price'     => $newAvgCost,
            ]);

            $msg = "Added +{$addedQty} units to {$product->name}. New Stock: {$newStock}, Average Buying Cost: ৳" . number_format($newAvgCost, 2) . ".";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok'           => true,
                'message'      => $msg,
                'new_stock'    => $newStock,
                'new_avg_cost' => $newAvgCost,
            ]);
        }

        return back()->with('status', $msg);
    }
}
