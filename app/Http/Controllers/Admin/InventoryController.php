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
        ]);

        if (! empty($data['sku_id'])) {
            $sku = ProductSku::findOrFail($data['sku_id']);
            $sku->update(['stock_quantity' => (int) $data['stock_quantity']]);
            $sku->product->syncTotalStock();

            $msg = "Stock updated to {$sku->stock_quantity} for {$sku->product->name} ({$sku->attributeLabel()}).";
        } else {
            $product = Product::findOrFail($data['product_id']);
            $product->update(['stock_quantity' => (int) $data['stock_quantity']]);

            $msg = "Stock updated to {$product->stock_quantity} for {$product->name}.";
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return back()->with('status', $msg);
    }
}
