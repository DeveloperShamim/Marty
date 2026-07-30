<?php $__env->startSection('title', 'Reviews'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
  <div>
    <h2 class="text-xl font-bold">Product reviews</h2>
    <p class="text-sm text-gray-500 mt-1">Approve or reject customer reviews. Approved reviews update product star ratings.</p>
  </div>

  <div class="flex flex-wrap gap-2">
    <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route('admin.reviews.index', array_filter(['status' => $key, 'q' => $q]))); ?>"
         class="px-3 py-1.5 rounded-full text-sm font-semibold <?php echo e($status === $key ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-brand-50'); ?>">
        <?php echo e($label); ?>

        <span class="ml-1 opacity-80"><?php echo e($counts[$key] ?? 0); ?></span>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <form method="GET" class="flex flex-wrap gap-2">
    <input type="hidden" name="status" value="<?php echo e($status); ?>" />
    <input name="q" value="<?php echo e($q); ?>" placeholder="Search author, product, text…" class="inp max-w-sm" />
    <button class="btn-primary">Search</button>
  </form>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-gray-500 bg-gray-50">
        <tr>
          <th class="px-5 py-3 font-medium">Product</th>
          <th class="px-5 py-3 font-medium">Review</th>
          <th class="px-5 py-3 font-medium">Rating</th>
          <th class="px-5 py-3 font-medium">Status</th>
          <th class="px-5 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr class="hover:bg-gray-50 align-top">
            <td class="px-5 py-4">
              <?php if($review->product): ?>
                <div class="flex items-center gap-3 min-w-[180px]">
                  <img src="<?php echo e($review->product->imageUrl()); ?>" class="h-11 w-11 rounded-lg object-cover bg-gray-100" alt="">
                  <div>
                    <a href="<?php echo e(route('admin.products.edit', $review->product)); ?>" class="font-medium hover:text-brand-700"><?php echo e($review->product->name); ?></a>
                    <p class="text-xs text-gray-400"><?php echo e($review->created_at?->format('d M Y, H:i')); ?></p>
                  </div>
                </div>
              <?php else: ?>
                <span class="text-gray-400">Deleted product</span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-4 max-w-md">
              <p class="font-semibold text-ink"><?php echo e($review->author_name); ?>

                <?php if($review->is_verified_purchase): ?>
                  <span class="ml-1 text-[10px] font-bold uppercase tracking-wide text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded">Verified</span>
                <?php endif; ?>
              </p>
              <?php if($review->author_email): ?><p class="text-xs text-gray-400"><?php echo e($review->author_email); ?></p><?php endif; ?>
              <?php if($review->title): ?><p class="mt-1 font-medium"><?php echo e($review->title); ?></p><?php endif; ?>
              <p class="mt-1 text-gray-600 whitespace-pre-line"><?php echo e($review->body); ?></p>
            </td>
            <td class="px-5 py-4 whitespace-nowrap">
              <span class="text-amber-500 tracking-tight"><?php echo e(str_repeat("\u{2605}", $review->rating)); ?><?php echo e(str_repeat("\u{2606}", 5 - $review->rating)); ?></span>
            </td>
            <td class="px-5 py-4">
              <?php if($review->status === 'approved'): ?>
                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Approved</span>
              <?php elseif($review->status === 'rejected'): ?>
                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-600">Rejected</span>
              <?php else: ?>
                <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700">Pending</span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-4 text-right whitespace-nowrap">
              <?php if($review->status !== 'approved'): ?>
                <form method="POST" action="<?php echo e(route('admin.reviews.approve', $review)); ?>" class="inline"><?php echo csrf_field(); ?>
                  <button class="px-2 py-1 text-xs rounded bg-brand-50 text-brand-700 border border-brand-200">Approve</button>
                </form>
              <?php endif; ?>
              <?php if($review->status !== 'rejected'): ?>
                <form method="POST" action="<?php echo e(route('admin.reviews.reject', $review)); ?>" class="inline"><?php echo csrf_field(); ?>
                  <button class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Reject</button>
                </form>
              <?php endif; ?>
              <form method="POST" action="<?php echo e(route('admin.reviews.destroy', $review)); ?>" class="inline" onsubmit="return confirm('Delete this review permanently?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No reviews in this filter.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($reviews->hasPages()): ?>
    <div><?php echo e($reviews->links()); ?></div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/reviews/index.blade.php ENDPATH**/ ?>