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

        $variantGroups = collect();

        if ($product->variants && $product->variants->isNotEmpty()) {
            foreach ($product->variants->groupBy('type') as $type => $items) {
                $variantGroups->put($type, (object) [
                    'type'    => $type,
                    'options' => $items->pluck('value')->unique()->values(),
                ]);
            }
        }

        if ($product->skus && $product->skus->isNotEmpty()) {
            $skuAttrGroups = [];
            foreach ($product->skus as $sku) {
                foreach ($sku->getAttributesData() as $attrKey => $attrVal) {
                    $kTrim = trim((string) $attrKey);
                    $vTrim = trim((string) $attrVal);
                    if ($kTrim !== '' && $vTrim !== '') {
                        $skuAttrGroups[$kTrim][] = $vTrim;
                    }
                }
            }
            foreach ($skuAttrGroups as $attrKey => $vals) {
                if (! $variantGroups->has($attrKey)) {
                    $variantGroups->put($attrKey, (object) [
                        'type'    => $attrKey,
                        'options' => collect($vals)->unique()->values(),
                    ]);
                }
            }
        }

        $sizes   = $variantGroups->has('Size') ? $variantGroups->get('Size')->options->map(fn($v) => (object)['type'=>'Size','value'=>$v]) : collect();
        $colors  = $variantGroups->has('Color') ? $variantGroups->get('Color')->options->map(fn($v) => (object)['type'=>'Color','value'=>$v]) : collect();
        $weights = $variantGroups->has('Weight') ? $variantGroups->get('Weight')->options->map(fn($v) => (object)['type'=>'Weight','value'=>$v]) : collect();

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
            'variantGroups',
            'sizes',
            'colors',
            'weights',
            'reviews',
            'canReview'
        ));
    }
}
