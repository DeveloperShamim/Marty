<header class="site-header sticky top-0 z-40 bg-white border-b border-stone-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-5 h-14 sm:h-16 flex items-center gap-3 sm:gap-4 min-w-0">
    <button type="button" data-open-menu class="lg:hidden p-1 shrink-0 text-brand-600" aria-label="Menu">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <?php echo $__env->make('partials.brand', ['size' => 'sm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="ml-auto flex items-center gap-1 sm:gap-2 shrink-0">
      <button type="button" data-toggle-search class="p-2 text-ink hover:text-brand-600 rounded-lg" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
      </button>

      <?php echo $__env->make('storefront.partials.account-dropdown', ['lightHeader' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      <button type="button" data-open-cart class="relative p-2 text-ink hover:text-brand-600 rounded-lg" aria-label="Cart">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12L6 6Zm0 0-.7-3H3"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        <span data-cart-count class="cart-count absolute top-0.5 right-0.5 h-4 min-w-4 px-1 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center <?php echo e($cartCount ? '' : 'hidden'); ?>"><?php echo e($cartCount); ?></span>
      </button>
    </div>
  </div>

  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3">
    <form action="<?php echo e(route('shop')); ?>" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="<?php echo e(setting('search_placeholder', 'Search…')); ?>" class="flex-1 border border-stone-200 rounded-lg px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-600/30" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-brand-600 text-white px-5 rounded-lg font-semibold text-sm hover:bg-brand-700 transition">Search</button>
    </form>
  </div>
</header>
<?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/partials/header-compact.blade.php ENDPATH**/ ?>