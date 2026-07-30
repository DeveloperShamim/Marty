<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request, ?Category $category = null)
    {
        $query = Product::published()->with('images', 'category', 'brand');

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$term}%"))
                    ->orWhere('short_description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('min')) {
            $query->whereRaw('COALESCE(sale_price, regular_price) >= ?', [(float) $request->input('min')]);
        }
        if ($request->filled('max')) {
            $query->whereRaw('COALESCE(sale_price, regular_price) <= ?', [(float) $request->input('max')]);
        }

        // Flash Sale page: only flash products, ordered by admin flash-sale order
        $isFlashPage = $request->boolean('flash');
        if ($isFlashPage) {
            $query->where('is_flash_sale', true);
        } elseif ($request->boolean('on_sale')) {
            $query->where(function ($q) {
                $q->where('is_flash_sale', true)
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('sale_price')
                            ->whereColumn('sale_price', '<', 'regular_price');
                    });
            });
        }
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }
        if ($request->boolean('featured') || $request->input('filter') === 'featured') {
            $query->where('is_featured', true);
        }
        if ($request->boolean('new') || $request->input('filter') === 'new') {
            $query->where('is_new_arrival', true);
        }
        if ($request->boolean('best_seller') || $request->input('filter') === 'best_seller') {
            $query->where('is_best_seller', true);
        }

        if ($brandParam = trim((string) $request->input('brand'))) {
            $query->where(function ($q) use ($brandParam) {
                $q->where('brand', $brandParam)
                  ->orWhereHas('brand', fn ($bq) => $bq->where('slug', $brandParam)->orWhere('name', $brandParam));
            });
        }

        if ($request->filled('min_rating')) {
            $minStars = max(1, min(5, (int) $request->input('min_rating')));
            $query->whereRaw('ROUND(rating) >= ?', [$minStars]);
        }

        $sort = $request->input('sort');
        if ($isFlashPage && blank($sort)) {
            $query->orderBy('flash_sale_position')->orderBy('id');
        } else {
            match ($sort) {
                'price_low'  => $query->orderByRaw('COALESCE(sale_price, regular_price) asc'),
                'price_high' => $query->orderByRaw('COALESCE(sale_price, regular_price) desc'),
                'rating'     => $query->orderByDesc('rating'),
                'name'       => $query->orderBy('name'),
                'newest'     => $query->latest(),
                default      => $query->latest(),
            };
        }

        $products = $query->paginate(12)->withQueryString();

        $priceCeiling = (int) Product::published()
            ->selectRaw('MAX(COALESCE(sale_price, regular_price)) as max_price')
            ->value('max_price');
        $priceCeiling = max(5000, (int) (ceil(max($priceCeiling, 5000) / 50) * 50));

        // Legacy string brands list fallback
        $rawBrands = Product::published()
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->orderBy('brand')
            ->distinct()
            ->pluck('brand');

        $brandModels = Brand::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('position')
            ->get();

        return view('storefront.shop', [
            'products'         => $products,
            'activeCategory'   => $category,
            'categories'       => $categories,
            'allProductsCount' => Product::published()->count(),
            'brands'           => $rawBrands,
            'brandModels'      => $brandModels,
            'sort'             => $request->input('sort'),
            'minRating'        => $request->input('min_rating'),
            'q'                => $term,
            'priceCeiling'     => $priceCeiling,
        ]);
    }

    public function brandPage(Brand $brand)
    {
        abort_unless($brand->is_active, 404);

        $products = $brand->products()
            ->published()
            ->with('images', 'category', 'brand')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('position')
            ->get();

        return view('brand.show', [
            'brand'      => $brand,
            'products'   => $products,
            'categories' => $categories,
        ]);
    }
}
