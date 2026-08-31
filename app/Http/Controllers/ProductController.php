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

        $normalizeLabel = function (string $type, $options): string {
            return ucfirst(trim($type));
        };

        // Primary source of truth for storefront variation buttons: Product SKUs
        if ($product->skus && $product->skus->isNotEmpty()) {
            $skuAttrGroups = [];
            foreach ($product->skus as $sku) {
                if (! $sku->is_active) {
                    continue;
                }
                foreach ($sku->getAttributesData() as $attrKey => $attrVal) {
                    $kTrim = trim((string) $attrKey);
                    $vTrim = trim((string) $attrVal);
                    if ($kTrim !== '' && $vTrim !== '') {
                        $skuAttrGroups[$kTrim][] = $vTrim;
                    }
                }
            }
            foreach ($skuAttrGroups as $attrKey => $vals) {
                $opts = collect($vals)->unique()->values();
                $label = $normalizeLabel($attrKey, $opts);
                $variantGroups->put($label, (object) [
                    'type'    => $label,
                    'options' => $opts,
                ]);
            }
        }

        // Fallback: ProductVariants table if no SKUs exist
        if ($variantGroups->isEmpty() && $product->variants && $product->variants->isNotEmpty()) {
            foreach ($product->variants->groupBy('type') as $type => $items) {
                $opts = $items->pluck('value')->unique()->values();
                $label = $normalizeLabel($type, $opts);
                $variantGroups->put($label, (object) [
                    'type'    => $label,
                    'options' => $opts,
                ]);
            }
        }

        $sizes   = $variantGroups->has('Size') ? $variantGroups->get('Size')->options->map(fn($v) => (object)['type'=>'Size','value'=>$v]) : collect();
        $colors  = $variantGroups->has('Color') ? $variantGroups->get('Color')->options->map(fn($v) => (object)['type'=>'Color','value'=>$v]) : collect();
        $weights = $variantGroups->has('Weight') ? $variantGroups->get('Weight')->options->map(fn($v) => (object)['type'=>'Weight','value'=>$v]) : collect();

        $reviews = $product->approvedReviews()
            ->with('user')
            ->paginate(8, ['*'], 'reviews_page')
            ->withQueryString();

        $hasPurchased = false;
        $alreadyReviewed = false;

        if ($user = auth()->user()) {
            $hasPurchased = \App\Models\OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where(function ($w) use ($user) {
                        $w->where('user_id', $user->id);
                        if ($user->email) {
                            $w->orWhere('customer_email', $user->email);
                        }
                        if ($user->phone) {
                            $w->orWhere('customer_phone', $user->phone);
                        }
                    })->whereNotIn('status', ['cancelled']);
                })
                ->exists();

            $alreadyReviewed = ProductReview::query()
                ->where('product_id', $product->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                    if ($user->email) {
                        $q->orWhere('author_email', $user->email);
                    }
                })
                ->whereIn('status', [ProductReview::STATUS_PENDING, ProductReview::STATUS_APPROVED])
                ->exists();
        }

        $canReview = $hasPurchased && ! $alreadyReviewed;

        return view('storefront.product', compact(
            'product',
            'related',
            'variantGroups',
            'sizes',
            'colors',
            'weights',
            'reviews',
            'hasPurchased',
            'alreadyReviewed',
            'canReview'
        ));
    }
}
