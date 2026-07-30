<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85%] -translate-x-full flex-col bg-white shadow-2xl lg:hidden transition-transform duration-300">
  <div class="flex items-center justify-between p-5 bg-brand-600 text-white shrink-0">
    <?php echo $__env->make('partials.brand', ['light' => true, 'size' => 'sm'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button type="button" data-close-menu class="p-2 hover:bg-white/10 rounded-lg" aria-label="Close">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
  </div>

  <div class="border-b border-stone-100 bg-stone-50 p-5">
    <?php if(auth()->guard()->check()): ?>
      <a href="<?php echo e(route('account')); ?>" class="flex items-center gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-600 text-white font-bold">
          <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

        </span>
        <span class="min-w-0">
          <span class="block truncate font-bold text-ink"><?php echo e(auth()->user()->name); ?></span>
          <span class="block truncate text-xs text-stone-500"><?php echo e(auth()->user()->email); ?></span>
        </span>
      </a>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="<?php echo e(route('account')); ?>" class="rounded-xl bg-white px-3 py-2.5 text-center text-sm font-semibold text-ink ring-1 ring-stone-200 hover:bg-brand-50">My Account</a>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit" class="w-full rounded-xl bg-brand-600 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">Log out</button>
        </form>
      </div>
    <?php else: ?>
      <p class="font-bold text-ink">Welcome to <?php echo e(site_name()); ?></p>
      <p class="mt-1 text-sm text-stone-500">Sign in to continue</p>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="<?php echo e(route('login')); ?>" class="rounded-xl bg-brand-600 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">Sign in</a>
        <a href="<?php echo e(route('register')); ?>" class="rounded-xl bg-white px-3 py-2.5 text-center text-sm font-semibold text-ink ring-1 ring-stone-200 hover:bg-brand-50">Register</a>
      </div>
    <?php endif; ?>
  </div>

  <nav class="flex-1 overflow-y-auto px-4 py-3 text-sm font-medium">
    <a href="<?php echo e(route('home')); ?>" class="block py-2.5 hover:text-brand-600">Home</a>
    <a href="<?php echo e(route('shop')); ?>" class="block py-2.5 hover:text-brand-600">Shop</a>
    <?php if($hasFlashSale ?? false): ?>
      <a href="<?php echo e(route('shop', ['flash' => 1])); ?>" class="block py-2.5 text-brand-600 hover:text-brand-700">Deals &amp; Offers</a>
    <?php endif; ?>
    <a href="<?php echo e(route('contact')); ?>" class="block py-2.5 hover:text-brand-600">Help &amp; Support</a>
    <a href="<?php echo e(route('login')); ?>" class="block py-2.5 hover:text-brand-600 sm:hidden">Account</a>

    <?php if($navCategories->isNotEmpty()): ?>
      <p class="mt-4 mb-2 text-[11px] font-bold uppercase tracking-wider text-stone-400">Categories</p>
      <?php $__currentLoopData = $navCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('shop.category', $cat)); ?>" class="block py-2.5 hover:text-brand-600"><?php echo e($cat->name); ?></a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <div class="mt-6 pt-4 border-t border-stone-100">
      <a href="<?php echo e(route('track')); ?>" class="flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 text-sm shadow-sm transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/></svg>
        Track Order
      </a>
    </div>
  </nav>
</aside>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/storefront/partials/mobile-menu.blade.php ENDPATH**/ ?>