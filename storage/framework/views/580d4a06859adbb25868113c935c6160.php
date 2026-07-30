<?php $__env->startSection('title', 'Inventory & Variant Stock'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
  <!-- Top Stat Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="card p-4">
      <p class="text-xs font-semibold uppercase text-slate-400">Total Variants / SKUs</p>
      <p class="text-2xl font-extrabold text-slate-800 mt-1"><?php echo e(number_format($totalSkusCount)); ?></p>
      <p class="text-xs text-slate-400 mt-1">Tracked across catalog</p>
    </div>

    <div class="card p-4 border-amber-200 bg-amber-50/30">
      <p class="text-xs font-semibold uppercase text-amber-600">Low Stock (≤ 3)</p>
      <p class="text-2xl font-extrabold text-amber-700 mt-1"><?php echo e(number_format($lowStockCount)); ?></p>
      <p class="text-xs text-amber-600/80 mt-1">Requires reorder</p>
    </div>

    <div class="card p-4 border-red-200 bg-red-50/30">
      <p class="text-xs font-semibold uppercase text-red-600">Out of Stock</p>
      <p class="text-2xl font-extrabold text-red-700 mt-1"><?php echo e(number_format($outOfStockCount)); ?></p>
      <p class="text-xs text-red-600/80 mt-1">Unavailable for buyers</p>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div class="card p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Inventory Stock Management</h2>
        <p class="text-xs text-slate-400 mt-0.5">Manage stock for shoe sizes, leather colors, and product variants.</p>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium self-start sm:self-auto">
        <a href="<?php echo e(route('admin.inventory.index', ['filter' => 'all', 'q' => $q])); ?>" class="px-3 py-1.5 rounded-lg transition <?php echo e($filter === 'all' ? 'bg-white shadow-xs text-slate-900 font-bold' : 'text-slate-500 hover:text-slate-800'); ?>">All Items</a>
        <a href="<?php echo e(route('admin.inventory.index', ['filter' => 'low_stock', 'q' => $q])); ?>" class="px-3 py-1.5 rounded-lg transition <?php echo e($filter === 'low_stock' ? 'bg-amber-500 text-white font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'); ?>">
          Low Stock <?php if($lowStockCount > 0): ?><span class="ml-1 bg-amber-700 text-white text-[10px] px-1.5 py-0.5 rounded-full"><?php echo e($lowStockCount); ?></span><?php endif; ?>
        </a>
        <a href="<?php echo e(route('admin.inventory.index', ['filter' => 'out_of_stock', 'q' => $q])); ?>" class="px-3 py-1.5 rounded-lg transition <?php echo e($filter === 'out_of_stock' ? 'bg-red-600 text-white font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800'); ?>">
          Out of Stock <?php if($outOfStockCount > 0): ?><span class="ml-1 bg-red-800 text-white text-[10px] px-1.5 py-0.5 rounded-full"><?php echo e($outOfStockCount); ?></span><?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Search Form -->
    <form method="GET" action="<?php echo e(route('admin.inventory.index')); ?>" class="flex items-center gap-2">
      <input type="hidden" name="filter" value="<?php echo e($filter); ?>" />
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search by product name, SKU, or variant (e.g. Size 40, Black)..." class="inp text-sm py-2 px-3 flex-1" />
      <button type="submit" class="btn-primary py-2 px-4 text-xs">Search</button>
      <?php if($q): ?>
        <a href="<?php echo e(route('admin.inventory.index', ['filter' => $filter])); ?>" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Inventory Stock Table -->
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Variant Option / Attributes</th>
            <th class="py-3 px-4">SKU Code</th>
            <th class="py-3 px-4">Current Stock</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Update Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if($product->skus->isNotEmpty()): ?>
              <?php $__currentLoopData = $product->skus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $stock = (int) $sku->stock_quantity;
                  $isLow = $stock <= 3 && $stock > 0;
                  $isOut = $stock <= 0;
                ?>
                <tr class="hover:bg-slate-50/60 transition-colors">
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <img src="<?php echo e($product->imageUrl()); ?>" class="h-10 w-10 object-cover bg-slate-100 rounded-lg shrink-0" alt="">
                      <div>
                        <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="font-semibold text-slate-900 hover:text-primary transition"><?php echo e($product->name); ?></a>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 px-4 text-xs text-slate-500"><?php echo e($product->category?->name ?? '—'); ?></td>
                  <td class="py-3 px-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                      <?php echo e($sku->attributeLabel()); ?>

                    </span>
                  </td>
                  <td class="py-3 px-4 font-mono text-xs text-slate-500"><?php echo e($sku->sku ?: ($product->sku ?: '—')); ?></td>
                  <td class="py-3 px-4 font-bold text-slate-900"><?php echo e(number_format($stock)); ?></td>
                  <td class="py-3 px-4">
                    <?php if($isOut): ?>
                      <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-100 text-red-700 border border-red-200">Out of Stock</span>
                    <?php elseif($isLow): ?>
                      <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-amber-100 text-amber-800 border border-amber-200">Low Stock (<?php echo e($stock); ?>)</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">In Stock</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <form method="POST" action="<?php echo e(route('admin.inventory.update-stock')); ?>" class="inline-flex items-center gap-2">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="sku_id" value="<?php echo e($sku->id); ?>" />
                      <input type="number" name="stock_quantity" value="<?php echo e($stock); ?>" min="0" class="inp text-xs py-1 px-2 w-20 text-center rounded-lg" required />
                      <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition cursor-pointer">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
              <?php
                $stock = (int) $product->stock_quantity;
                $isLow = $stock <= 3 && $stock > 0;
                $isOut = $stock <= 0;
              ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img src="<?php echo e($product->imageUrl()); ?>" class="h-10 w-10 object-cover bg-slate-100 rounded-lg shrink-0" alt="">
                    <div>
                      <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="font-semibold text-slate-900 hover:text-primary transition"><?php echo e($product->name); ?></a>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 text-xs text-slate-500"><?php echo e($product->category?->name ?? '—'); ?></td>
                <td class="py-3 px-4 text-xs text-slate-400 italic">Standard Product</td>
                <td class="py-3 px-4 font-mono text-xs text-slate-500"><?php echo e($product->sku ?: '—'); ?></td>
                <td class="py-3 px-4 font-bold text-slate-900"><?php echo e(number_format($stock)); ?></td>
                <td class="py-3 px-4">
                  <?php if($isOut): ?>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-100 text-red-700 border border-red-200">Out of Stock</span>
                  <?php elseif($isLow): ?>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-amber-100 text-amber-800 border border-amber-200">Low Stock (<?php echo e($stock); ?>)</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">In Stock</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 text-right">
                  <form method="POST" action="<?php echo e(route('admin.inventory.update-stock')); ?>" class="inline-flex items-center gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>" />
                    <input type="number" name="stock_quantity" value="<?php echo e($stock); ?>" min="0" class="inp text-xs py-1 px-2 w-20 text-center rounded-lg" required />
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition cursor-pointer">Save</button>
                  </form>
                </td>
              </tr>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                No inventory items found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if($products->hasPages()): ?>
      <div class="p-4 border-t border-slate-200">
        <?php echo e($products->links()); ?>

      </div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/inventory/index.blade.php ENDPATH**/ ?>