<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand'])
            ->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category_id')) {
            $query->where('category_id', $category);
        }

        $products = $query->paginate(25)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.barcodes.index', compact('products', 'categories'));
    }

    public function print(Request $request)
    {
        $selected = $request->input('items', []); // format: [ ['id' => 1, 'type' => 'product', 'qty' => 5], ... ]
        $sheetFormat = $request->input('format', 'thermal_50x30'); // 'thermal_50x30', 'thermal_40x25', 'a4_3col', 'a4_4col'
        $showStoreName = $request->boolean('show_store_name', true);
        $showPrice = $request->boolean('show_price', true);
        $storeName = \App\Models\Setting::get('site_name', 'MARTY STORE');

        $labels = [];

        foreach ($selected as $item) {
            if (empty($item['selected'])) {
                continue;
            }

            $id = (int) ($item['id'] ?? 0);
            $qty = max(1, min(200, (int) ($item['qty'] ?? 1)));
            $type = $item['type'] ?? 'product';

            if ($type === 'sku') {
                $sku = ProductSku::with('product')->find($id);
                if ($sku) {
                    $barcode = $sku->getBarcode();
                    $name = ($sku->product?->name ?? 'Product') . ' - ' . $sku->attributeLabel();
                    $price = $sku->getCalculatedSalePrice() ?: $sku->getCalculatedRegularPrice();

                    for ($i = 0; $i < $qty; $i++) {
                        $labels[] = [
                            'barcode' => $barcode,
                            'name'    => $name,
                            'price'   => $price,
                            'sku'     => $sku->sku ?: $barcode,
                        ];
                    }
                }
            } else {
                $product = Product::find($id);
                if ($product) {
                    $barcode = $product->getBarcode();
                    $name = $product->name;
                    $price = (float) ($product->sale_price ?: $product->regular_price);

                    for ($i = 0; $i < $qty; $i++) {
                        $labels[] = [
                            'barcode' => $barcode,
                            'name'    => $name,
                            'price'   => $price,
                            'sku'     => $product->sku ?: $barcode,
                        ];
                    }
                }
            }
        }

        if (empty($labels)) {
            return redirect()->back()->with('error', 'Please select at least one product sticker to print.');
        }

        return view('admin.barcodes.print', compact('labels', 'sheetFormat', 'showStoreName', 'showPrice', 'storeName'));
    }
}
