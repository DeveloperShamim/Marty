<?php $__env->startSection('title', 'Dashboard & Revenue Analytics'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
  <!-- Revenue & Stat Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Orders</p>
      <p class="mt-1 text-2xl font-extrabold text-slate-800"><?php echo e(number_format($ordersCount)); ?></p>
      <p class="text-[11px] text-slate-400 mt-1">Excludes <?php echo e(number_format($cancelledOrdersCount ?? 0)); ?> cancelled</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Verified Revenue</p>
      <p class="mt-1 text-2xl font-extrabold text-emerald-600"><?php echo e(money($revenue)); ?></p>
      <p class="text-[11px] text-emerald-600/80 mt-1">Excludes cancelled orders</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Today's Revenue</p>
      <p class="mt-1 text-2xl font-extrabold text-primary"><?php echo e(money($todayRevenue)); ?></p>
      <p class="text-[11px] text-gray-400 mt-1">
        Yesterday: <span class="font-medium text-slate-600"><?php echo e(money($yesterdayRevenue)); ?></span>
      </p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">This Month</p>
      <p class="mt-1 text-2xl font-extrabold text-indigo-600"><?php echo e(money($thisMonthRevenue)); ?></p>
      <p class="text-[11px] text-indigo-600/80 mt-1"><?php echo e(date('F Y')); ?></p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Avg Order Value (AOV)</p>
      <p class="mt-1 text-2xl font-extrabold text-sky-600"><?php echo e(money($avgOrderValue)); ?></p>
      <p class="text-[11px] text-sky-600/80 mt-1">Per verified order</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending Verification</p>
      <p class="mt-1 text-2xl font-extrabold text-amber-600"><?php echo e(number_format($pendingCount)); ?></p>
      <p class="text-[11px] text-amber-600/80 mt-1">Needs payment check</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Monthly Revenue Performance Chart (Last 12 Months ending at Current Month) -->
    <div class="card p-5 lg:col-span-2 flex flex-col justify-between">
      <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="font-bold text-slate-800">Monthly Revenue Performance</h2>
            <p class="text-xs text-slate-400 mt-0.5">12-Month rolling trend: <strong><?php echo e($firstMonthLabel); ?> – <?php echo e($lastMonthLabel); ?></strong></p>
          </div>

          <!-- Year Selector Form -->
          <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-2">
            <label for="year" class="text-xs font-semibold text-slate-500">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-sm cursor-pointer">
              <?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($yr); ?>" <?php echo e($selectedYear == $yr ? 'selected' : ''); ?>><?php echo e($yr); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </form>
        </div>
      </div>

      <!-- 12-Month Bar Chart (Last bar = Current Month) -->
      <div class="mt-6 flex items-end gap-2 h-48 px-1">
        <?php $__currentLoopData = $monthlySeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
            <div class="w-full rounded-t transition-all <?php echo e($point['is_current'] ? 'bg-primary ring-2 ring-primary/40 shadow-sm' : 'bg-primary/75 hover:bg-primary'); ?>" style="height: <?php echo e(max(4, (int) round($point['value'] / $seriesMax * 100))); ?>%"></div>
            
            <!-- Hover Tooltip -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 bg-slate-900 text-white text-[10px] py-1 px-2.5 rounded shadow pointer-events-none z-10 whitespace-nowrap">
              <?php echo e($point['full_label']); ?>: <?php echo e(money($point['value'])); ?>

            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <!-- Month Labels -->
      <div class="mt-3 grid grid-cols-12 text-center text-[10px] font-semibold text-gray-400 border-t pt-2">
        <?php $__currentLoopData = $monthlySeries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <span class="<?php echo e($point['is_current'] ? 'text-primary font-extrabold underline decoration-2 underline-offset-4' : ($point['value'] > 0 ? 'text-slate-700 font-bold' : '')); ?>">
            <?php echo e($point['label']); ?>

          </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>

    <!-- Pending Payment Verification Sidebar -->
    <div class="card p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-slate-800">Pending Verification</h2>
        <a href="<?php echo e(route('admin.orders.index', ['status' => 'pending_verification'])); ?>" class="text-xs font-semibold text-primary hover:underline">View all</a>
      </div>
      <div class="space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="flex items-center gap-3 p-2 bg-amber-50/50 rounded-xl border border-amber-100">
            <div class="flex-1 min-w-0">
              <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="text-sm font-bold text-primary hover:underline"><?php echo e($order->order_number); ?></a>
              <p class="text-xs text-gray-500 truncate"><?php echo e($order->customer_name); ?> &middot; <?php echo e($order->paymentMethodLabel()); ?></p>
            </div>
            <span class="text-sm font-extrabold text-slate-800"><?php echo e(money($order->total)); ?></span>
            <form method="POST" action="<?php echo e(route('admin.orders.verify', $order)); ?>">
              <?php echo csrf_field(); ?>
              <button class="px-2.5 py-1 text-xs font-bold rounded-lg bg-primary hover:bg-primary-dark text-white transition">Verify</button>
            </form>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="text-center py-8">
            <p class="text-2xl mb-1">🎉</p>
            <p class="text-sm text-gray-400">No pending payment verifications</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Top Revenue-Generating Products Leaderboard -->
  <div class="card p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-bold text-slate-800">🏆 Top Revenue-Generating Products</h2>
        <p class="text-xs text-slate-400">Best-selling items ranked by total revenue earned</p>
      </div>
      <a href="<?php echo e(route('admin.products.index')); ?>" class="text-xs font-semibold text-primary hover:underline">View all products</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 rounded-xl">
          <tr>
            <th class="px-4 py-3 font-semibold rounded-l-xl"># Rank</th>
            <th class="px-4 py-3 font-semibold">Product Name</th>
            <th class="px-4 py-3 font-semibold text-center">Units Sold</th>
            <th class="px-4 py-3 font-semibold text-right">Total Revenue (৳)</th>
            <th class="px-4 py-3 font-semibold text-center rounded-r-xl">Stock Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="px-4 py-3 font-bold text-slate-400">
                <?php if($index === 0): ?> 🥇 #1 <?php elseif($index === 1): ?> 🥈 #2 <?php elseif($index === 2): ?> 🥉 #3 <?php else: ?> #<?php echo e($index + 1); ?> <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <?php if($item->image): ?>
                    <img src="<?php echo e($item->image); ?>" alt="<?php echo e($item->product_name); ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 shrink-0" />
                  <?php else: ?>
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 font-bold text-xs shrink-0">
                      <?php echo e(substr($item->product_name, 0, 1)); ?>

                    </div>
                  <?php endif; ?>
                  <div>
                    <span class="font-bold text-slate-800 line-clamp-1"><?php echo e($item->product_name); ?></span>
                    <?php if($item->product): ?>
                      <span class="text-xs text-slate-400">Price: <?php echo e(money($item->product->price)); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                  <?php echo e(number_format($item->total_units)); ?> units
                </span>
              </td>
              <td class="px-4 py-3 text-right font-extrabold text-emerald-600 text-base">
                <?php echo e(money($item->total_revenue)); ?>

              </td>
              <td class="px-4 py-3 text-center">
                <?php if($item->product): ?>
                  <?php if($item->product->stock_quantity <= 0): ?>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">Out of Stock</span>
                  <?php elseif($item->product->stock_quantity <= 5): ?>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700">Low Stock (<?php echo e($item->product->stock_quantity); ?>)</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">In Stock (<?php echo e($item->product->stock_quantity); ?>)</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-xs text-slate-400">N/A</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="5" class="text-center py-6 text-slate-400 text-sm">
                No revenue data recorded yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Orders Table -->
  <div class="card">
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
      <h2 class="font-bold text-slate-800">Recent Orders</h2>
      <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-xs font-semibold text-primary hover:underline">All orders</a>
    </div>
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
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3"><a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="font-bold text-primary hover:underline"><?php echo e($order->order_number); ?></a></td>
              <td class="px-5 py-3 font-medium text-slate-700"><?php echo e($order->customer_name); ?></td>
              <td class="px-5 py-3 font-bold text-slate-900"><?php echo e(money($order->total)); ?></td>
              <td class="px-5 py-3"><?php echo e($order->paymentMethodLabel()); ?> · <span class="px-2 py-0.5 text-xs rounded-full <?php echo e($order->paymentBadge()); ?>"><?php echo e(ucfirst($order->payment_status)); ?></span></td>
              <td class="px-5 py-3"><span class="px-2 py-1 text-xs rounded-full <?php echo e($order->statusBadge()); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
              <td class="px-5 py-3 text-gray-500 text-xs"><?php echo e($order->created_at->format('d M, Y')); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>