<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Product;

use App\Models\ProductReview;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** How many featured categories get a product row on the homepage, and how many products each row holds. */
    public const HOME_CATEGORY_ROWS = 4;
    /** A category row slides through up to 8 products; it is topped up to 4 with already-shown ones if short. */
    public const HOME_ROW_SIZE = 8;
    public const HOME_ROW_MIN = 4;
    public const JUST_FOR_YOU = 12;

    public function index()
    {
        $withImages = fn ($q) => $q->published()->with('images', 'category', 'brand', 'variants', 'skus');

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderByDesc('products_count')
            ->orderBy('position')
            ->get();

        // Brands show as a logo row that links to each brand's page; featured brands lead it
        $featuredBrands = Brand::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderByDesc('is_featured')
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->filter(fn (Brand $b) => $b->products_count > 0)
            ->values();

        $banners = fn (string $placement) => Banner::active()
            ->placement($placement)
            ->orderBy('position')
            ->orderBy('id');

        $bestSellersQuery = Product::query()->tap($withImages)->where('is_best_seller', true)->latest();
        if ((clone $bestSellersQuery)->count() === 0) {
            $bestSellersQuery = Product::query()->tap($withImages)->latest();
        }
        $bestSellers = $bestSellersQuery->take(12)->get();

        $newArrivalsQuery = Product::query()->tap($withImages)->where('is_new_arrival', true)->latest();
        if ((clone $newArrivalsQuery)->count() === 0) {
            $newArrivalsQuery = Product::query()->tap($withImages)->latest();
        }
        $newArrivals = $newArrivalsQuery->take(12)->get();

        $flashProducts = Product::query()->tap($withImages)
            ->where('is_flash_sale', true)
            ->orderBy('flash_sale_position')
            ->orderBy('id')
            ->get();

        $homeReviews = ProductReview::approved()
            ->with('product')
            ->latest()
            ->take(6) // 3 + 3 on desktop, 2 + 2 + 2 on tablets
            ->get();

        // Featured categories get one sliding row each. A product already shown in Flash deals or
        // Best sellers is skipped, so the page doesn't repeat itself; a small catalogue tops a row
        // back up to 4 rather than leave it half empty.
        $shown = $flashProducts->take(8)->pluck('id')->merge($bestSellers->take(8)->pluck('id'));
        $featuredHomeCategories = Category::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('position')
            ->take(self::HOME_CATEGORY_ROWS)
            ->get()
            ->each(function (Category $cat) use (&$shown) {
                $products = $cat->products()->published()->with('images', 'category', 'brand', 'variants', 'skus')
                    ->latest()->take(16)->get();
                $fresh = $products->reject(fn ($p) => $shown->contains($p->id));
                $row = ($fresh->count() >= self::HOME_ROW_MIN
                    ? $fresh->take(self::HOME_ROW_SIZE)
                    : $fresh->concat($products->filter(fn ($p) => $shown->contains($p->id)))->take(self::HOME_ROW_MIN))->values();
                $shown = $shown->merge($row->pluck('id'));
                $cat->setRelation('products', $row);
            })
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty())
            ->values();

        $justForYou = $this->justForYou($withImages, $shown->merge($newArrivals->take(8)->pluck('id')));

        return view('storefront.home', [
            'heroBanners'            => $banners('hero')->get(),
            'heroSideBanners'        => $banners('hero_side')->get(),
            'features'               => Feature::where('is_active', true)->orderBy('position')->get(),
            'categories'             => $categories,
            'featuredHomeCategories' => $featuredHomeCategories,
            'coupons'                => Coupon::query()
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->get()
                ->filter(fn (Coupon $c) => $c->isCurrentlyActive())
                ->values()
                ->take(4),
            'flashProducts'          => $flashProducts,
            'bestSellers'            => $bestSellers,
            'newArrivals'            => $newArrivals,
            'featuredBrands'         => $featuredBrands,
            'homeReviews'            => $homeReviews,
            'justForYou'             => $justForYou,
        ]);
    }

    /**
     * "Just for you" at the bottom of the homepage: in-stock products from the categories of what is in the
     * shopper's cart first, then anything not already on the page, in a fresh order each visit. A small
     * catalogue repeats products from higher up rather than leave the section short.
     */
    private function justForYou(\Closure $withImages, \Illuminate\Support\Collection $onPage): \Illuminate\Support\Collection
    {
        $cartIds = app(\App\Services\CartService::class)->productIds();
        $categoryIds = $cartIds->isEmpty() ? collect() : Product::whereIn('id', $cartIds)->pluck('category_id')->filter()->unique();
        $base = fn () => Product::query()->tap($withImages)->where('stock_quantity', '>', 0)->whereNotIn('id', $cartIds)->inRandomOrder();

        $picks = $categoryIds->isEmpty() ? collect()
            : $base()->whereNotIn('id', $onPage)->whereIn('category_id', $categoryIds)->take(self::JUST_FOR_YOU)->get();
        foreach ([$onPage, collect()] as $skip) {
            if ($picks->count() >= self::JUST_FOR_YOU) {
                break;
            }
            $picks = $picks->concat($base()->whereNotIn('id', $skip->merge($picks->pluck('id')))
                ->take(self::JUST_FOR_YOU - $picks->count())->get());
        }

        return $picks->values();
    }

    public function loadMore(Request $request)
    {
        $page = max(1, (int) $request->input('page', 2));
        $limit = max(1, (int) $request->input('limit', 8));
        $offset = ($page - 1) * $limit;

        $withImages = fn ($q) => $q->published()->with('images', 'category', 'variants', 'skus');

        $query = Product::query()->tap($withImages)->where('is_featured', true)->latest();
        if ((clone $query)->count() === 0) {
            $query = Product::query()->tap($withImages)->latest();
        }

        $total = (clone $query)->count();
        $products = $query->skip($offset)->take($limit)->get();

        $html = '';
        foreach ($products as $product) {
            $html .= view('storefront.partials.product-card', ['product' => $product])->render();
        }

        $currentLoaded = min($offset + $products->count(), $total);

        return response()->json([
            'html'         => $html,
            'has_more'     => $currentLoaded < $total,
            'loaded_count' => $currentLoaded,
            'total_count'  => $total,
        ]);
    }
}
