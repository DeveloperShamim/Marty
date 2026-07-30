

<?php
    if ($activeCategory) {
        $rawTitle = $activeCategory->meta_title ?: null;
        $title = $activeCategory->name;
        $metaDescription = $activeCategory->meta_description;
        $metaKeywords = $activeCategory->meta_keywords;
    } else {
        $title = $q ? 'Search: ' . $q : 'Shop';
    }
    $total = $products->total();
    $maxPrice = max(1000, (int) ($priceCeiling ?? 100000));
    $minVal = request()->filled('min') ? max(0, (int) request('min')) : 0;
    $maxVal = request()->filled('max') ? min($maxPrice, max(0, (int) request('max'))) : $maxPrice;
    $brands = $brands ?? collect();
    $activeBrand = request('brand');
    $formAction = $activeCategory ? route('shop.category', $activeCategory) : route('shop');
?>

<?php $__env->startSection('content'); ?>
<main class="max-w-[1440px] mx-auto px-4 sm:px-5 py-5 sm:py-6">
  <div class="flex flex-col lg:flex-row gap-5 lg:gap-6">

    
    <aside id="filterPanel" class="filter-panel fixed inset-y-0 left-0 z-50 w-[300px] max-w-[90%] -translate-x-full overflow-y-auto bg-white p-4 shadow-2xl lg:static lg:z-auto lg:w-[270px] lg:shrink-0 lg:translate-x-0 lg:overflow-visible lg:p-0 lg:shadow-none lg:bg-transparent">
      <form method="GET" action="<?php echo e($formAction); ?>" id="shopFilters" class="bg-white rounded-md border border-gray-200 p-4 space-y-5">
        <?php if($minRating): ?>
          <input type="hidden" name="min_rating" id="minRatingField" value="<?php echo e($minRating); ?>">
        <?php endif; ?>
        <div class="flex items-center justify-between lg:hidden border-b border-gray-100 pb-3 -mt-1">
          <span class="font-extrabold text-ink">Filters</span>
          <button type="button" data-close-filter class="grid h-8 w-8 place-items-center rounded-md bg-gray-100 text-gray-500" aria-label="Close">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
          </button>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Search Products</h3>
          <div class="flex overflow-hidden rounded border border-gray-200 focus-within:border-brand-600">
            <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="<?php echo e(setting('search_placeholder', 'Search…')); ?>" class="flex-1 min-w-0 px-3 py-2 text-sm focus:outline-none" />
            <button type="submit" class="bg-brand-600 text-white px-3 hover:bg-brand-700" aria-label="Search">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </button>
          </div>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Categories</h3>
          <ul class="space-y-1.5 text-sm">
            <li>
              <a href="<?php echo e(route('shop', request()->except(['page']))); ?>" class="flex items-center gap-2.5 text-gray-700 hover:text-brand-600 <?php echo e(!$activeCategory ? 'text-brand-600 font-semibold' : ''); ?>">
                <span class="shop-box <?php echo e(!$activeCategory ? 'is-checked' : ''); ?>" aria-hidden="true"></span>
                <span class="flex-1 truncate">All Products</span>
                <span class="text-gray-400 text-xs">(<?php echo e($allProductsCount ?? $categories->sum('products_count')); ?>)</span>
              </a>
            </li>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li>
                <a href="<?php echo e(route('shop.category', ['category' => $cat] + request()->except(['page']))); ?>"
                   class="flex items-center gap-2.5 text-gray-700 hover:text-brand-600 <?php echo e($activeCategory?->id === $cat->id ? 'text-brand-600 font-semibold' : ''); ?>">
                  <span class="shop-box <?php echo e($activeCategory?->id === $cat->id ? 'is-checked' : ''); ?>" aria-hidden="true"></span>
                  <span class="flex-1 truncate"><?php echo e($cat->name); ?></span>
                  <span class="text-gray-400 text-xs">(<?php echo e($cat->products_count); ?>)</span>
                </a>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Price Range</h3>
          <div class="flex items-center gap-2">
            <div class="flex items-center flex-1 border border-gray-200 rounded overflow-hidden">
              <span class="pl-2 text-xs text-gray-400"><?php echo e(currency_symbol()); ?></span>
              <input type="number" name="min" min="0" max="<?php echo e($maxPrice); ?>" value="<?php echo e(request()->filled('min') ? $minVal : ''); ?>" placeholder="0" class="w-full min-w-0 px-1.5 py-2 text-sm focus:outline-none" />
            </div>
            <span class="text-gray-400 shrink-0">–</span>
            <div class="flex items-center flex-1 border border-gray-200 rounded overflow-hidden">
              <span class="pl-2 text-xs text-gray-400"><?php echo e(currency_symbol()); ?></span>
              <input type="number" name="max" min="0" max="<?php echo e($maxPrice); ?>" value="<?php echo e(request()->filled('max') ? $maxVal : ''); ?>" placeholder="<?php echo e(number_format($maxPrice)); ?>" class="w-full min-w-0 px-1.5 py-2 text-sm focus:outline-none" />
            </div>
          </div>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Average Rating</h3>
          <ul class="space-y-1.5 text-sm">
            <?php $__currentLoopData = [5, 4, 3, 2, 1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stars): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $isActive = (string) ($minRating ?? '') === (string) $stars; ?>
              <li>
                <a href="<?php echo e(request()->fullUrlWithQuery(['min_rating' => $stars, 'page' => null])); ?>"
                   class="flex items-center gap-2.5 text-gray-700 hover:text-brand-600 <?php echo e($isActive ? 'text-brand-600 font-semibold' : ''); ?>">
                  <span class="shop-box <?php echo e($isActive ? 'is-checked' : ''); ?>" aria-hidden="true"></span>
                  <span class="text-amber-400 tracking-tight leading-none"><?php echo e(str_repeat("\u{2605}", $stars)); ?></span>
                  <span class="text-gray-300 tracking-tight leading-none"><?php echo e(str_repeat("\u{2605}", 5 - $stars)); ?></span>
                  <span class="text-xs text-gray-500">&amp; Up</span>
                </a>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($minRating): ?>
              <li>
                <a href="<?php echo e(request()->fullUrlWithQuery(['min_rating' => null, 'page' => null])); ?>" class="text-xs font-semibold text-brand-600 hover:text-brand-700 pl-6">Clear rating</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Product Tags</h3>
          <?php if($brands->isNotEmpty()): ?>
            <ul class="space-y-1.5 max-h-36 overflow-y-auto text-sm">
              <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                  <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                    <input type="radio" name="brand" value="<?php echo e($brand); ?>" class="shop-check" <?php if($activeBrand === $brand): echo 'checked'; endif; ?>>
                    <span class="truncate"><?php echo e($brand); ?></span>
                  </label>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <p class="text-xs text-gray-400">No tags yet</p>
          <?php endif; ?>
        </div>

        
        <div>
          <h3 class="text-sm font-bold text-ink mb-2">Product Collections &amp; Status</h3>
          <ul class="space-y-1.5 text-sm">
            <li>
              <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                <input type="checkbox" name="featured" value="1" class="shop-check" <?php if(request('featured') || request('filter') === 'featured'): echo 'checked'; endif; ?>>
                <span>Featured</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                <input type="checkbox" name="new" value="1" class="shop-check" <?php if(request('new') || request('filter') === 'new'): echo 'checked'; endif; ?>>
                <span>New Arrival</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                <input type="checkbox" name="best_seller" value="1" class="shop-check" <?php if(request('best_seller') || request('filter') === 'best_seller'): echo 'checked'; endif; ?>>
                <span>Best Seller</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                <input type="checkbox" name="on_sale" value="1" class="shop-check" <?php if(request('on_sale') || request('flash')): echo 'checked'; endif; ?>>
                <span>On Sale</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-2.5 cursor-pointer text-gray-700 hover:text-brand-600">
                <input type="checkbox" name="in_stock" value="1" class="shop-check" <?php if(request('in_stock')): echo 'checked'; endif; ?>>
                <span>In Stock</span>
              </label>
            </li>
          </ul>
        </div>

        <div class="pt-1 space-y-2">
          <button type="submit" class="w-full rounded bg-brand-600 text-white text-sm font-bold py-2.5 hover:bg-brand-700 transition">
            Apply Filters
          </button>
          <a href="<?php echo e($activeCategory ? route('shop.category', $activeCategory) : route('shop')); ?>" class="block text-center text-sm font-semibold text-accent-600 hover:text-accent-500">
            Reset
          </a>
        </div>
      </form>
    </aside>

    
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-3 mb-4 lg:hidden">
        <button type="button" data-open-filter class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-ink shadow-sm">
          <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 6h18M6 12h12M10 18h4"/></svg>
          Filters
        </button>
        <p class="text-sm text-gray-500"><span class="font-semibold text-ink"><?php echo e($total); ?></span> products</p>
      </div>

      <div class="bg-white rounded-md border border-gray-100 px-4 py-3 mb-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-base sm:text-lg font-extrabold text-accent-500 truncate">
            <?php if($activeCategory): ?>
              Category: <?php echo e($activeCategory->name); ?>

            <?php elseif($q): ?>
              Search: <?php echo e($q); ?>

            <?php elseif(request('best_seller') || request('filter') === 'best_seller'): ?>
              Best Selling Products
            <?php elseif(request('featured') || request('filter') === 'featured'): ?>
              Featured Products
            <?php elseif(request('new') || request('filter') === 'new'): ?>
              New Arrivals
            <?php elseif(request('on_sale') || request('flash')): ?>
              Products On Sale
            <?php else: ?>
              All Products
            <?php endif; ?>
          </h1>
          <?php if(request('new') || request('filter') === 'new'): ?>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">Our new arrivals</p>
          <?php elseif(request('best_seller') || request('filter') === 'best_seller'): ?>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">Explore customer top picks</p>
          <?php elseif(request('featured') || request('filter') === 'featured'): ?>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">Featured top quality products</p>
          <?php elseif(request('on_sale') || request('flash')): ?>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">Special offers &amp; discount deals</p>
          <?php elseif(! $activeCategory && ! $q && setting('shop_subtitle')): ?>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2"><?php echo e(setting('shop_subtitle')); ?></p>
          <?php endif; ?>
        </div>
        <form method="GET" action="<?php echo e($formAction); ?>" class="shrink-0 hidden sm:block">
          <?php $__currentLoopData = request()->except(['sort', 'page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_scalar($val) && $val !== ''): ?>
              <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($val); ?>">
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <select name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded text-sm px-3 py-1.5 focus:outline-none focus:border-brand-600">
            <option value="">Sort: Popular</option>
            <option value="newest" <?php if($sort==='newest'): echo 'selected'; endif; ?>>Newest</option>
            <option value="price_low" <?php if($sort==='price_low'): echo 'selected'; endif; ?>>Price: Low to High</option>
            <option value="price_high" <?php if($sort==='price_high'): echo 'selected'; endif; ?>>Price: High to Low</option>
            <option value="rating" <?php if($sort==='rating'): echo 'selected'; endif; ?>>Top Rated</option>
          </select>
        </form>
      </div>

      <?php if($products->isEmpty()): ?>
        <div class="bg-white rounded-md border border-dashed border-gray-200 p-12 sm:p-16 text-center text-gray-500">
          <p class="font-semibold text-ink">No products found.</p>
          <a href="<?php echo e(route('shop')); ?>" class="mt-3 inline-flex text-brand-600 font-semibold text-sm">Browse all products</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $__env->make('storefront.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($products->hasPages()): ?>
          <nav class="mt-8 flex items-center justify-center gap-1.5">
            <?php if($products->onFirstPage()): ?>
              <span class="px-3 py-2 border border-gray-200 rounded bg-white text-gray-300 text-sm">Prev</span>
            <?php else: ?>
              <a href="<?php echo e($products->previousPageUrl()); ?>" class="px-3 py-2 border border-gray-200 rounded bg-white text-sm hover:border-brand-600">Prev</a>
            <?php endif; ?>
            <?php $__currentLoopData = $products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <a href="<?php echo e($url); ?>" class="min-w-9 h-9 grid place-items-center rounded text-sm <?php echo e($page == $products->currentPage() ? 'bg-brand-600 text-white font-semibold' : 'border border-gray-200 bg-white hover:border-brand-600'); ?>"><?php echo e($page); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($products->hasMorePages()): ?>
              <a href="<?php echo e($products->nextPageUrl()); ?>" class="px-3 py-2 border border-gray-200 rounded bg-white text-sm hover:border-brand-600">Next</a>
            <?php else: ?>
              <span class="px-3 py-2 border border-gray-200 rounded bg-white text-gray-300 text-sm">Next</span>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/shop.blade.php ENDPATH**/ ?>