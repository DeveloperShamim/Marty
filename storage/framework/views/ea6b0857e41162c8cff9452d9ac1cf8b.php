<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">Orders</h2>
  </div>

  <!-- Filter tabs -->
  <div class="flex flex-wrap gap-2 text-sm">
    <?php
      $tabs = [
        'all' => 'All', 'pending_verification' => 'Pending verification',
        'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
      ];
    ?>
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $active = $status === $key || ($key === 'all' && !in_array($status, array_keys($tabs))); ?>
      <a href="<?php echo e(route('admin.orders.index', ['status' => $key])); ?>"
         class="px-3 py-1.5 rounded-lg <?php echo e($active ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600'); ?>">
        <?php echo e($label); ?>

        <?php if(isset($counts[$key])): ?>
          <span class="<?php echo e($active ? 'opacity-80' : ($key === 'pending_verification' ? 'bg-amber-100 text-amber-700 rounded px-1' : 'text-gray-400')); ?>"><?php echo e($counts[$key]); ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  <div class="card">
    <form method="GET" class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
      <input type="hidden" name="status" value="<?php echo e($status); ?>">
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search order # or customer…" class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
      <select name="method" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">All payment methods</option>
        <?php $__currentLoopData = ['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cod' => 'COD']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if($method === $k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
      <button class="btn-primary">Search</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500 bg-gray-50">
          <tr>
            <th class="px-5 py-3 font-medium">Order</th>
            <th class="px-5 py-3 font-medium">Customer</th>
            <th class="px-5 py-3 font-medium">Total</th>
            <th class="px-5 py-3 font-medium">Payment</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 font-medium">Date</th>
            <th class="px-5 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3"><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="font-medium text-primary"><?php echo e($order->order_number); ?></a><br><span class="text-gray-400 text-xs"><?php echo e($order->items_count); ?> item(s)</span></td>
              <td class="px-5 py-3"><?php echo e($order->customer_name); ?><br><span class="text-gray-400 text-xs"><?php echo e($order->customer_phone); ?></span></td>
              <td class="px-5 py-3 font-medium"><?php echo e(money($order->total)); ?></td>
              <td class="px-5 py-3"><?php echo e($order->paymentMethodLabel()); ?> · <span class="px-2 py-0.5 text-xs rounded-full <?php echo e($order->paymentBadge()); ?>"><?php echo e(ucfirst($order->payment_status)); ?></span></td>
              <td class="px-5 py-3"><span class="px-2 py-1 text-xs rounded-full <?php echo e($order->statusBadge()); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
              <td class="px-5 py-3 text-gray-500"><?php echo e($order->created_at->format('d M')); ?></td>
              <td class="px-5 py-3 text-right whitespace-nowrap space-x-1">
                <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600 hover:bg-gray-200">View</a>
                <?php if($order->payment_status === 'pending'): ?>
                  <form method="POST" action="<?php echo e(route('admin.orders.verify', $order)); ?>" class="inline"><?php echo csrf_field(); ?><button class="px-2 py-1 text-xs rounded bg-primary text-white">Verify</button></form>
                  <form method="POST" action="<?php echo e(route('admin.orders.reject', $order)); ?>" class="inline"><?php echo csrf_field(); ?><button class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200">Reject</button></form>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.orders.invoice', $order)); ?>" target="_blank" title="Print Invoice" class="px-2 py-1 text-xs rounded bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 inline-flex items-center gap-1">🖨️ Invoice</a>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-gray-200"><?php echo e($orders->links()); ?></div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/unilifeb/UnilifeBD/resources/views/admin/orders/index.blade.php ENDPATH**/ ?>