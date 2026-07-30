<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->is_published, 404);

        $product->load('images', 'variants', 'skus', 'category');

        $related = Product::published()
            ->with('images')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(5)
            ->get();

        $sizes   = $product->variants->where('type', 'Size')->values();
        $colors  = $product->variants->where('type', 'Color')->values();
        $weights = $product->variants->where('type', 'Weight')->values();

        if ($colors->isEmpty() || $sizes->isEmpty()) {
            $skuColors = collect();
            $skuSizes = collect();
            $skuWeights = collect();

            foreach ($product->skus as $sku) {
                $attrs = $sku->getAttributesData();
                foreach ($attrs as $k => $v) {
                    $kLower = strtolower(trim((string)$k));
                    if (str_contains($kLower, 'col')) {
                        $skuColors->push((object)['type' => 'Color', 'value' => (string)$v]);
                    } elseif (str_contains($kLower, 'size')) {
                        $skuSizes->push((object)['type' => 'Size', 'value' => (string)$v]);
                    } else {
                        $skuWeights->push((object)['type' => $k, 'value' => (string)$v]);
                    }
                }
            }

            if ($colors->isEmpty()) {
                $colors = $skuColors->unique('value')->values();
            }
            if ($sizes->isEmpty()) {
                $sizes = $skuSizes->unique('value')->values();
            }
            if ($weights->isEmpty()) {
                $weights = $skuWeights->unique('value')->values();
            }
        }

        $reviews = $product->approvedReviews()
            ->with('user')
            ->paginate(8, ['*'], 'reviews_page')
            ->withQueryString();

        $canReview = true;
        if ($user = auth()->user()) {
            $canReview = ! ProductReview::query()
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->whereIn('status', [ProductReview::STATUS_PENDING, ProductReview::STATUS_APPROVED])
                ->exists();
        }

        return view('storefront.product', compact(
            'product',
            'related',
            'sizes',
            'colors',
            'weights',
            'reviews',
            'canReview'
        ));
    }
}
