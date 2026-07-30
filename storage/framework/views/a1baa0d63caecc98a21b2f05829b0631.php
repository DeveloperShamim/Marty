<?php $__env->startSection('title', 'Order Detail'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-sm text-gray-500 hover:text-primary">&larr; Back to orders</a>
      <h2 class="text-xl font-bold mt-1">Order <?php echo e($order->order_number); ?></h2>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <span class="px-2 py-1 text-xs rounded-full <?php echo e($order->statusBadge()); ?>"><?php echo e(ucfirst($order->status)); ?></span>
      <span class="px-2 py-1 text-xs rounded-full <?php echo e($order->paymentBadge()); ?>">Payment: <?php echo e(ucfirst($order->payment_status)); ?></span>
      <span class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-600">Placed <?php echo e($order->created_at->format('d M Y, g:i A')); ?></span>
      <a href="<?php echo e(route('admin.orders.invoice', $order)); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Invoice
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Items -->
      <div class="card">
        <h3 class="font-semibold p-5 border-b border-gray-200">Items</h3>
        <div class="divide-y divide-gray-100">
          <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-4">
              <div class="flex items-start sm:items-center gap-4">
                <img src="<?php echo e($item->imageUrl()); ?>" class="h-14 w-14 object-cover bg-gray-100 rounded-lg shrink-0" alt="<?php echo e($item->product_name); ?>">
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-900"><?php echo e($item->product_name); ?></p>
                  <div class="flex items-center gap-2 mt-1 flex-wrap text-sm">
                    <span class="text-gray-500"><?php echo e(money($item->unit_price)); ?> × <?php echo e($item->quantity); ?></span>
                    <?php if($item->variant): ?>
                      <span class="px-2 py-0.5 text-xs font-semibold rounded bg-amber-50 text-amber-800 border border-amber-200">
                        <?php echo e($item->variant); ?>

                      </span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-400">No variation</span>
                    <?php endif; ?>
                    <button type="button" onclick="document.getElementById('edit-variant-<?php echo e($item->id); ?>').classList.toggle('hidden')" class="text-xs text-primary hover:underline font-medium cursor-pointer inline-flex items-center gap-1">
                      ✏️ Edit Variation
                    </button>
                  </div>
                </div>
                <div class="font-semibold text-gray-900 shrink-0"><?php echo e(money($item->line_total)); ?></div>
              </div>

              <!-- Inline Edit Variation Form -->
              <form id="edit-variant-<?php echo e($item->id); ?>" method="POST" action="<?php echo e(route('admin.orders.items.update-variant', [$order, $item])); ?>" class="hidden mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-semibold text-gray-700">Update Item Variation (Size / Color / Spec)</label>
                  <button type="button" onclick="document.getElementById('edit-variant-<?php echo e($item->id); ?>').classList.add('hidden')" class="text-xs text-gray-400 hover:text-gray-600">✕ Close</button>
                </div>

                <?php if($item->product && $item->product->variants->isNotEmpty()): ?>
                  <?php
                    $variantsGrouped = $item->product->variants->groupBy('type');
                  ?>
                  <div class="text-xs text-gray-500 bg-white p-2 rounded border border-gray-200">
                    <span class="font-semibold text-gray-700">Available product options:</span>
                    <div class="flex flex-wrap gap-2 mt-1">
                      <?php $__currentLoopData = $variantsGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $vars): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-[11px] text-gray-700">
                          <strong><?php echo e($type); ?>:</strong> <?php echo e($vars->pluck('value')->join(', ')); ?>

                        </span>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="flex items-center gap-2">
                  <input type="text" name="variant" value="<?php echo e(old('variant', $item->variant)); ?>" placeholder="e.g. Size: L, Color: Black" class="inp text-xs py-1.5 px-2 flex-1" />
                  <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded bg-primary text-white hover:bg-primary/90 cursor-pointer">Save Variation</button>
                </div>
              </form>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="p-5 border-t border-gray-200 text-sm space-y-1">
          <div class="flex justify-between text-gray-600"><span>Subtotal</span><span><?php echo e(money($order->subtotal)); ?></span></div>
          <?php if($order->discount_amount > 0): ?>
            <div class="flex justify-between text-primary"><span>Coupon <?php if($order->coupon_code): ?>(<?php echo e($order->coupon_code); ?>)<?php endif; ?></span><span>−<?php echo e(money($order->discount_amount)); ?></span></div>
          <?php endif; ?>
          <div class="flex justify-between text-gray-600"><span>Shipping (<?php echo e(shipping_zone_label($order->shipping_zone)); ?>)</span><span><?php echo e(money($order->shipping_charge)); ?></span></div>
          <div class="flex justify-between text-gray-600"><span>Tax</span><span><?php echo e(money($order->tax)); ?></span></div>
          <div class="flex justify-between font-bold text-base pt-2"><span>Total</span><span><?php echo e(money($order->total)); ?></span></div>
        </div>
      </div>

      <!-- Payment verification -->
      <div class="card p-5">
        <h3 class="font-semibold mb-4">Payment — verify against your statement</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><p class="text-gray-400">Method</p><p class="font-medium"><?php echo e($order->paymentMethodLabel()); ?></p></div>
          <div><p class="text-gray-400">Amount</p><p class="font-medium"><?php echo e(money($order->total)); ?></p></div>
          <div><p class="text-gray-400">Sender number</p><p class="font-medium"><?php echo e($order->payment_sender_number ?? "\u{2014}"); ?></p></div>
          <div><p class="text-gray-400">Transaction ID</p><p class="font-medium"><?php echo e($order->payment_txn_id ?? "\u{2014}"); ?></p></div>
        </div>
        <?php if($order->payment_status === 'pending'): ?>
          <div class="flex gap-3 mt-5">
            <form method="POST" action="<?php echo e(route('admin.orders.verify', $order)); ?>"><?php echo csrf_field(); ?><button class="bg-primary hover:bg-primary/90 text-white text-sm font-medium px-5 py-2.5 rounded-lg">✓ Verify payment</button></form>
            <form method="POST" action="<?php echo e(route('admin.orders.reject', $order)); ?>"><?php echo csrf_field(); ?><button class="bg-red-50 text-red-600 border border-red-200 text-sm font-medium px-5 py-2.5 rounded-lg">✕ Reject</button></form>
          </div>
        <?php else: ?>
          <p class="mt-4 text-sm text-gray-500">Payment is <b class="<?php echo e($order->payment_status === 'verified' ? 'text-primary' : 'text-red-600'); ?>"><?php echo e($order->payment_status); ?></b>. Change it below if needed.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right -->
    <div class="space-y-6">
      <form method="POST" action="<?php echo e(route('admin.orders.update', $order)); ?>" class="card p-5 space-y-3">
        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
        <h3 class="font-semibold mb-1">Update order</h3>
        <div>
          <label class="lbl">Order status</label>
          <select name="status" class="inp">
            <?php $__currentLoopData = \App\Models\Order::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($s); ?>" <?php if($order->status === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label class="lbl">Payment status</label>
          <select name="payment_status" class="inp">
            <?php $__currentLoopData = \App\Models\Order::PAYMENT_STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($s); ?>" <?php if($order->payment_status === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div>
          <label class="lbl">Internal note</label>
          <textarea name="internal_note" rows="3" class="inp" placeholder="Add a note for staff…"><?php echo e($order->internal_note); ?></textarea>
        </div>
        <button class="w-full btn-primary">Save</button>
      </form>

      <div class="card p-5 text-sm">
        <h3 class="font-semibold mb-3">Customer</h3>
        <p class="font-medium"><?php echo e($order->customer_name); ?></p>
        <p class="text-gray-500"><?php echo e($order->customer_phone); ?></p>
        <?php if($order->customer_email): ?><p class="text-gray-500"><?php echo e($order->customer_email); ?></p><?php endif; ?>
        <p class="text-gray-500 mt-2"><?php echo e($order->shipping_address); ?>, <?php echo e($order->city); ?> <?php echo e($order->postal_code); ?></p>
      </div>

      <!-- Danger Zone -->
      <div class="card p-5 border-red-200 bg-red-50/40 text-sm">
        <h3 class="font-semibold text-red-700 mb-1">Danger Zone</h3>
        <p class="text-xs text-red-600 mb-3">Permanently delete this order record from the database.</p>
        <form method="POST" action="<?php echo e(route('admin.orders.destroy', $order)); ?>" onsubmit="return confirm('Are you SURE you want to permanently delete order <?php echo e($order->order_number); ?>? This action cannot be undone.')">
          <?php echo csrf_field(); ?>
          <?php echo method_field('DELETE'); ?>
          <button type="submit" class="w-full py-2 px-3 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition shadow-sm cursor-pointer">
            Delete Order <?php echo e($order->order_number); ?>

          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/orders/show.blade.php ENDPATH**/ ?>