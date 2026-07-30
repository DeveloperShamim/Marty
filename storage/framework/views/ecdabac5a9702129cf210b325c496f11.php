<?php
  $promoText = trim((string) setting('header_promo_text', ''));
  $promoLink = trim((string) setting('header_promo_link', ''));
  $navCats = ($navCategories ?? collect());
  $topCats = $navCats->take(5);
  $otherCats = $navCats->skip(5);
?>

<?php if($promoText !== ''): ?>
  <div class="bg-ink text-white text-xs sm:text-sm text-center py-2 px-4">
    <?php if($promoLink !== ''): ?>
      <a href="<?php echo e($promoLink); ?>" class="hover:text-brand-300"><?php echo strip_tags($promoText, '<b><strong><span>'); ?></a>
    <?php else: ?>
      <?php echo strip_tags($promoText, '<b><strong><span>'); ?>

    <?php endif; ?>
  </div>
<?php endif; ?>

<header class="site-header sticky top-0 z-40">
  <div class="bg-white border-b border-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-5 py-3 flex items-center gap-3 sm:gap-4">
      <button type="button" data-open-menu class="lg:hidden text-brand-700 p-1 shrink-0" aria-label="Menu">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <?php echo $__env->make('partials.brand', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      <form action="<?php echo e(route('shop')); ?>" method="GET" class="hidden md:flex flex-1 max-w-xl mx-auto">
        <div class="flex w-full rounded-md border border-stone-200 overflow-hidden bg-white focus-within:ring-2 focus-within:ring-brand-600/25">
          <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(setting('search_placeholder', 'Search Products...')); ?>" class="flex-1 px-3 py-2.5 text-sm focus:outline-none text-ink" autocomplete="off" />
          <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-4 flex items-center" aria-label="Search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
          </button>
        </div>
      </form>

      <div class="ml-auto flex items-center gap-1 sm:gap-3 shrink-0">
        <button type="button" data-toggle-search class="md:hidden p-2 text-ink hover:text-brand-600" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
        </button>

        <?php echo $__env->make('storefront.partials.account-dropdown', ['lightHeader' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <button type="button" data-open-cart class="relative flex items-center gap-1.5 p-2 text-ink hover:text-brand-600" aria-label="Cart">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6h15l-1.5 9H7.5L6 6Zm0 0-.7-3H3"/><circle cx="9" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
          <span data-cart-count class="cart-count absolute -top-0.5 -right-0.5 h-4 min-w-4 px-1 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center <?php echo e($cartCount ? '' : 'hidden'); ?>"><?php echo e($cartCount); ?></span>
        </button>
      </div>
    </div>
  </div>

  <div class="hidden lg:block bg-white border-b border-stone-200/80 text-stone-700 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-5 flex items-center justify-between gap-2 h-11 text-sm font-semibold">
      <nav class="flex flex-1 items-center justify-between gap-1 py-1">
        <a href="<?php echo e(route('home')); ?>" class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors <?php echo e(request()->routeIs('home') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50'); ?>">Home</a>
        <a href="<?php echo e(route('shop')); ?>" class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors <?php echo e(request()->routeIs('shop*') && ! request('flash') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50'); ?>">Shop</a>
        <?php if($hasFlashSale ?? false): ?>
          <a href="<?php echo e(route('shop', ['flash' => 1])); ?>" class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors <?php echo e(request('flash') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50'); ?>">Deals &amp; Offers</a>
        <?php endif; ?>
        <?php $__currentLoopData = $topCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('shop.category', $cat)); ?>" class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors <?php echo e(optional($activeCategory ?? null)->id === $cat->id ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50'); ?>"><?php echo e($cat->name); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($otherCats->isNotEmpty()): ?>
          <div class="relative group/catdropdown" id="moreCatContainer">
            <button type="button" 
                    onclick="event.stopPropagation(); document.getElementById('moreCatDropdownMenu').classList.toggle('hidden');" 
                    class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors text-stone-700 hover:text-brand-600 hover:bg-stone-50 inline-flex items-center gap-1 cursor-pointer">
              <span>More Categories</span>
              <svg class="w-3.5 h-3.5 transition-transform group-hover/catdropdown:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>
            <div id="moreCatDropdownMenu" class="absolute left-1/2 -translate-x-1/2 top-full pt-1.5 hidden group-hover/catdropdown:block z-50 min-w-[240px] max-w-sm">
              <div class="bg-white rounded-xl shadow-2xl border border-stone-200 p-2 space-y-1 max-h-80 overflow-y-auto">
                <?php $__currentLoopData = $otherCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <a href="<?php echo e(route('shop.category', $cat)); ?>" class="flex items-center justify-between gap-2 px-3 py-2 text-xs font-semibold text-stone-700 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                    <span class="truncate"><?php if($cat->icon): ?><span class="mr-1.5"><?php echo e($cat->icon); ?></span><?php endif; ?><?php echo e($cat->name); ?></span>
                    <?php if(isset($cat->products_count) && $cat->products_count > 0): ?>
                      <span class="text-[10px] text-stone-400 bg-stone-100 px-1.5 py-0.5 rounded-full font-mono"><?php echo e($cat->products_count); ?></span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <a href="<?php echo e(route('contact')); ?>" class="px-2.5 py-1.5 whitespace-nowrap rounded-lg transition-colors <?php echo e(request()->routeIs('contact') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50'); ?>">Help &amp; Support</a>
      </nav>
      <a href="<?php echo e(route('track')); ?>" class="ml-4 shrink-0 bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold px-3.5 sm:px-4 py-1.5 rounded-lg shadow-sm transition">TRACK ORDER</a>
    </div>
  </div>

  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3 md:hidden">
    <form action="<?php echo e(route('shop')); ?>" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(setting('search_placeholder', 'Search Products...')); ?>" class="flex-1 border border-stone-200 rounded-md px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-600/30" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-brand-600 text-white px-5 rounded-md font-semibold text-sm hover:bg-brand-700 transition">Search</button>
    </form>
  </div>
</header>

<script>
document.addEventListener('click', function(e) {
  const container = document.getElementById('moreCatContainer');
  const menu = document.getElementById('moreCatDropdownMenu');
  if (menu && container && !container.contains(e.target)) {
    menu.classList.add('hidden');
  }
});
</script>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/storefront/partials/header.blade.php ENDPATH**/ ?>