<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="flex gap-3 py-4 border-b border-slate-100">
    <img src="<?php echo e($item->image); ?>" alt="<?php echo e($item->name); ?>" class="h-16 w-16 rounded-xl object-cover bg-slate-100" loading="lazy">
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold text-ink truncate"><?php echo e($item->name); ?></p>
      <p class="text-xs text-slate-500 mt-0.5"><?php echo e($item->variant ?: $item->product->unit); ?></p>
      <div class="mt-2 flex items-center justify-between">
        <div class="inline-flex items-center rounded-lg border border-slate-200">
          <button class="px-2.5 py-1 text-slate-500 hover:text-brand-700" data-cart-dec data-key="<?php echo e($item->key); ?>" data-qty="<?php echo e($item->qty); ?>">&minus;</button>
          <span class="w-7 text-center text-sm font-semibold"><?php echo e($item->qty); ?></span>
          <button class="px-2.5 py-1 text-slate-500 hover:text-brand-700" data-cart-inc data-key="<?php echo e($item->key); ?>" data-qty="<?php echo e($item->qty); ?>">+</button>
        </div>
        <span class="text-sm font-bold text-ink"><?php echo e(money($item->line_total)); ?></span>
      </div>
    </div>
    <button class="text-slate-300 hover:text-red-500 self-start" data-cart-remove data-key="<?php echo e($item->key); ?>" aria-label="Remove">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
    </button>
  </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/partials/cart-drawer-items.blade.php ENDPATH**/ ?>