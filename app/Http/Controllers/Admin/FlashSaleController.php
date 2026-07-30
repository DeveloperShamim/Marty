<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $flashProducts = Product::with('images', 'category')
            ->where('is_flash_sale', true)
            ->orderBy('flash_sale_position')
            ->orderBy('id')
            ->get();

        $available = Product::with('images', 'category')
            ->published()
            ->where('is_flash_sale', false)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('brand', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.flash-sale.index', [
            'flashProducts' => $flashProducts,
            'available'     => $available,
            'q'             => $request->input('q'),
            'endsAt'        => setting('flash_sale_ends_at'),
        ]);
    }

    public function updateEndsAt(Request $request)
    {
        $data = $request->validate([
            'flash_sale_ends_at' => ['nullable', 'date'],
        ]);

        $raw = (string) ($data['flash_sale_ends_at'] ?? '');
        if ($raw !== '') {
            $raw = str_replace('T', ' ', $raw);
            if (strlen($raw) === 16) {
                $raw .= ':00';
            }
        }

        Setting::put('flash_sale_ends_at', $raw);
        Setting::forgetCache();

        return back()->with('status', 'Flash sale end time saved.');
    }

    public function add(Product $product)
    {
        $nextPos = (int) Product::where('is_flash_sale', true)->max('flash_sale_position') + 1;

        $product->update([
            'is_flash_sale'        => true,
            'flash_sale_position'  => $nextPos,
            'flash_sale_progress'  => $product->flash_sale_progress ?: 50,
        ]);

        return back()->with('status', "“{$product->name}” added to Flash Sale.");
    }

    public function remove(Product $product)
    {
        $product->update([
            'is_flash_sale'       => false,
            'flash_sale_position' => 0,
        ]);

        return back()->with('status', "“{$product->name}” removed from Flash Sale.");
    }

    public function updateProgress(Request $request, Product $product)
    {
        abort_unless($product->is_flash_sale, 404);

        $data = $request->validate([
            'flash_sale_progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $product->update([
            'flash_sale_progress' => $data['flash_sale_progress'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Progress saved.',
                'flash_sale_progress' => (int) $product->flash_sale_progress,
            ]);
        }

        return back()->with('status', "Progress for “{$product->name}” updated.");
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order'   => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'exists:products,id'],
        ]);

        foreach (array_values($data['order']) as $i => $id) {
            Product::where('id', $id)->where('is_flash_sale', true)->update([
                'flash_sale_position' => $i,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Flash sale order updated.']);
        }

        return back()->with('status', 'Flash sale order updated.');
    }
}
