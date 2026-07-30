<?php
  $title = 'Order Confirmed';
  $trackingItems = $order->items->map(fn ($item) => [
    'item_id' => (string) $item->product_id,
    'item_name' => $item->product_name,
    'item_variant' => $item->variant,
    'price' => (float) $item->unit_price,
    'quantity' => (int) $item->quantity,
  ])->values();
?>

<?php if(tracking_any_enabled()): ?>
<?php $__env->startPush('tracking-head'); ?>
<script>
(function () {
  var items = <?php echo json_encode($trackingItems, 15, 512) ?>;
  var purchase = {
    transaction_id: <?php echo json_encode($order->order_number, 15, 512) ?>,
    value: <?php echo e((float) $order->total); ?>,
    currency: <?php echo json_encode(setting('currency_code', 'BDT'), 512) ?>,
    tax: <?php echo e((float) $order->tax); ?>,
    shipping: <?php echo e((float) $order->shipping_charge); ?>,
    coupon: <?php echo json_encode($order->coupon_code, 15, 512) ?>,
    items: items
  };

  <?php if(tracking_gtm_id() || tracking_ga4_id()): ?>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    event: 'purchase',
    ecommerce: {
      transaction_id: purchase.transaction_id,
      value: purchase.value,
      currency: purchase.currency,
      tax: purchase.tax,
      shipping: purchase.shipping,
      coupon: purchase.coupon || undefined,
      items: items
    }
  });
  <?php endif; ?>

  <?php if(tracking_ga4_id()): ?>
  if (typeof gtag === 'function') {
    gtag('event', 'purchase', {
      transaction_id: purchase.transaction_id,
      value: purchase.value,
      currency: purchase.currency,
      tax: purchase.tax,
      shipping: purchase.shipping,
      coupon: purchase.coupon || undefined,
      items: items
    });
  }
  <?php endif; ?>

  <?php if(tracking_meta_pixel_id()): ?>
  if (typeof fbq === 'function') {
    fbq('track', 'Purchase', {
      value: purchase.value,
      currency: purchase.currency,
      content_type: 'product'
    });
  }
  <?php endif; ?>
})();
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
<section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-14">
  <div class="text-center">
    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-emerald-600">
      <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="mt-5 font-display text-2xl sm:text-3xl font-extrabold">Thank you for your order!</h1>
    <p class="mt-2 text-slate-500">Your order <b class="text-ink"><?php echo e($order->order_number); ?></b> has been placed and is now <b class="text-amber-600">pending verification</b>.</p>
    <?php if($order->isMobileBanking()): ?>
      <p class="mt-1 text-sm text-slate-500">We'll verify your <?php echo e($order->paymentMethodLabel()); ?> payment shortly.</p>
    <?php endif; ?>
  </div>

  <div class="mt-10 rounded-2xl border border-slate-100 bg-white shadow-soft overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-display font-extrabold">Order summary</h2>
      <span class="text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold"><?php echo e($order->status); ?></span>
    </div>
    <div class="divide-y divide-slate-100">
      <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-center gap-4 p-4">
          <img src="<?php echo e($item->imageUrl()); ?>" class="h-14 w-12 object-cover bg-slate-100 rounded-lg" alt="<?php echo e($item->product_name); ?>" />
          <div class="flex-1"><p class="font-medium text-sm"><?php echo e($item->product_name); ?></p><p class="text-xs text-slate-400"><?php echo e($item->variant); ?> &middot; <?php echo e(money($item->unit_price)); ?> × <?php echo e($item->quantity); ?></p></div>
          <div class="font-medium text-sm"><?php echo e(money($item->line_total)); ?></div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="p-5 border-t border-slate-100 text-sm space-y-1.5">
      <div class="flex justify-between text-slate-600"><span>Subtotal</span><span><?php echo e(money($order->subtotal)); ?></span></div>
      <?php if($order->discount_amount > 0): ?>
        <div class="flex justify-between text-brand-600"><span>Coupon <?php if($order->coupon_code): ?>(<?php echo e($order->coupon_code); ?>)<?php endif; ?></span><span>−<?php echo e(money($order->discount_amount)); ?></span></div>
      <?php endif; ?>
      <div class="flex justify-between text-slate-600"><span>Shipping (<?php echo e(shipping_zone_label($order->shipping_zone)); ?>)</span><span><?php echo e(money($order->shipping_charge)); ?></span></div>
      <div class="flex justify-between text-slate-600"><span>Tax</span><span><?php echo e(money($order->tax)); ?></span></div>
      <div class="flex justify-between font-extrabold text-base pt-2 border-t border-slate-100 mt-2"><span>Total</span><span class="text-brand-600"><?php echo e(money($order->total)); ?></span></div>
    </div>
  </div>

  <div class="mt-8 grid sm:grid-cols-2 gap-4 text-sm">
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
      <h3 class="font-semibold text-xs text-slate-400 mb-2">Delivery to</h3>
      <p class="font-semibold"><?php echo e($order->customer_name); ?></p>
      <p class="text-slate-500"><?php echo e($order->customer_phone); ?></p>
      <p class="text-slate-500 mt-1"><?php echo e($order->shipping_address); ?>, <?php echo e($order->city); ?> <?php echo e($order->postal_code); ?></p>
    </div>
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-soft">
      <h3 class="font-semibold text-xs text-slate-400 mb-2">Payment</h3>
      <p class="font-semibold"><?php echo e($order->paymentMethodLabel()); ?></p>
      <?php if($order->isMobileBanking()): ?>
        <p class="text-slate-500">Sender: <?php echo e($order->payment_sender_number); ?></p>
        <p class="text-slate-500">Txn: <?php echo e($order->payment_txn_id); ?></p>
      <?php endif; ?>
      <p class="text-slate-500 mt-1">Status: <span class="font-semibold text-amber-600"><?php echo e(ucfirst($order->payment_status)); ?></span></p>
    </div>
  </div>

  <div class="mt-8 text-center">
    <a href="<?php echo e(route('shop')); ?>" class="inline-flex rounded-full bg-brand-600 text-white text-sm font-semibold px-6 py-3 hover:bg-brand-700">Continue shopping</a>
  </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/order-confirmation.blade.php ENDPATH**/ ?>