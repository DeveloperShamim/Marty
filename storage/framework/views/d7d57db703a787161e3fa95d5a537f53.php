<?php
  $title = 'Checkout';
  $shippingZone = old('shipping_zone', 'inside_dhaka');
  $taxable = max(0, $subtotal - $discount);
?>

<?php $__env->startSection('body_class', 'bg-slate-50 text-ink antialiased'); ?>

<?php $__env->startSection('checkout_header'); ?>
  <header class="bg-white border-b border-slate-100">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-16 lg:h-20 flex items-center justify-between">
      <?php echo $__env->make('partials.brand', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <div class="flex items-center gap-2 text-sm text-slate-500"><svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7z"/><path stroke-linecap="round" d="m9 12 2 2 4-4"/></svg> Secure Checkout</div>
    </div>
  </header>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 pt-8">
    <ol class="flex items-center gap-3 text-sm font-medium">
      <li class="flex items-center gap-2 text-brand-700"><span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-white text-xs">1</span> <a href="<?php echo e(route('cart.index')); ?>" class="hover:underline">Cart</a></li>
      <li class="h-px w-8 bg-brand-300"></li>
      <li class="flex items-center gap-2 text-brand-700"><span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-white text-xs">2</span> Delivery</li>
      <li class="h-px w-8 bg-slate-200"></li>
      <li class="flex items-center gap-2 text-slate-400"><span class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-slate-500 text-xs">3</span> Payment</li>
    </ol>
  </div>

  <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
    <?php if($errors->any()): ?>
      <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="list-disc list-inside mt-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
      </div>
    <?php endif; ?>

    <div class="lg:grid lg:grid-cols-[1fr_380px] lg:gap-8 lg:items-start">
      <form method="POST" action="<?php echo e(route('checkout.store')); ?>" id="checkoutForm" class="space-y-6">
        <?php echo csrf_field(); ?>

        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Contact</h2>
          <div class="mt-4 grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Full name</label><input name="customer_name" value="<?php echo e(old('customer_name', $user?->name)); ?>" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Phone</label><input name="customer_phone" value="<?php echo e(old('customer_phone', $user?->phone)); ?>" required placeholder="01XXX-XXXXXX" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Email (optional)</label><input type="email" name="customer_email" value="<?php echo e(old('customer_email', $user?->email)); ?>" placeholder="you@example.com" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Delivery address</h2>
          <div class="mt-4 grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Address</label><input name="shipping_address" value="<?php echo e(old('shipping_address', $user?->address)); ?>" required placeholder="House, road, area" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div><label class="block text-sm font-medium mb-1.5">City</label><input name="city" value="<?php echo e(old('city', $user?->city)); ?>" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div><label class="block text-sm font-medium mb-1.5">Postal code</label><input name="postal_code" value="<?php echo e(old('postal_code', $user?->postal_code)); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium mb-1.5">Delivery zone</label>
              <select name="shipping_zone" id="shippingZone" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300">
                <option value="inside_dhaka" data-fee="<?php echo e($shipInside); ?>" <?php if($shippingZone === 'inside_dhaka'): echo 'selected'; endif; ?>><?php echo e(shipping_zone_label('inside_dhaka')); ?> (+<?php echo e(money($shipInside)); ?>)</option>
                <option value="outside_dhaka" data-fee="<?php echo e($shipOutside); ?>" <?php if($shippingZone === 'outside_dhaka'): echo 'selected'; endif; ?>><?php echo e(shipping_zone_label('outside_dhaka')); ?> (+<?php echo e(money($shipOutside)); ?>)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Payment method</h2>
          <?php
            $method = old('payment_method', 'cod');
            $bkash = setting('bkash_number');
            $nagad = setting('nagad_number');
            $rocket = setting('rocket_number');
            $showCod = (string) setting('pay_cod_enabled', '1') === '1';
            $showBkash = (string) setting('pay_bkash_enabled', '1') === '1' && (bool) $bkash;
            $showNagad = (string) setting('pay_nagad_enabled', '1') === '1' && (bool) $nagad;
            $showRocket = (string) setting('pay_rocket_enabled', '1') === '1' && (bool) $rocket;
            $payNumbers = [
              'bkash' => $bkash,
              'nagad' => $nagad,
              'rocket' => $rocket,
            ];
          ?>
          <div class="mt-4 space-y-3">
            <?php if($showCod): ?>
            <label class="pay-opt flex items-center gap-3 rounded-xl border-2 px-4 py-3.5 cursor-pointer <?php echo e($method==='cod' ? 'border-brand-500 bg-brand-50/60' : 'border-slate-200 hover:border-brand-300'); ?>">
              <input type="radio" name="payment_method" value="cod" <?php if($method==='cod'): echo 'checked'; endif; ?> class="pay-radio text-brand-600 focus:ring-brand-500" data-manual="0" data-pay-number="" />
              <span class="font-medium">Cash on Delivery</span>
              <svg class="ml-auto h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
            </label>
            <?php endif; ?>
            <?php if($showBkash): ?>
            <label class="pay-opt flex items-center gap-3 rounded-xl border px-4 py-3.5 cursor-pointer hover:border-brand-300 <?php echo e($method==='bkash' ? 'border-2 border-brand-500 bg-brand-50/60' : 'border-slate-200'); ?>">
              <input type="radio" name="payment_method" value="bkash" <?php if($method==='bkash'): echo 'checked'; endif; ?> class="pay-radio text-brand-600 focus:ring-brand-500" data-manual="1" data-pay-number="<?php echo e($bkash); ?>" />
              <span class="font-medium">bKash</span>
              <span class="ml-auto rounded-md bg-pink-100 text-pink-600 text-xs font-bold px-2 py-1"><?php echo e($bkash); ?></span>
            </label>
            <?php endif; ?>
            <?php if($showNagad): ?>
            <label class="pay-opt flex items-center gap-3 rounded-xl border px-4 py-3.5 cursor-pointer hover:border-brand-300 <?php echo e($method==='nagad' ? 'border-2 border-brand-500 bg-brand-50/60' : 'border-slate-200'); ?>">
              <input type="radio" name="payment_method" value="nagad" <?php if($method==='nagad'): echo 'checked'; endif; ?> class="pay-radio text-brand-600 focus:ring-brand-500" data-manual="1" data-pay-number="<?php echo e($nagad); ?>" />
              <span class="font-medium">Nagad</span>
              <span class="ml-auto rounded-md bg-orange-100 text-orange-600 text-xs font-bold px-2 py-1"><?php echo e($nagad); ?></span>
            </label>
            <?php endif; ?>
            <?php if($showRocket): ?>
            <label class="pay-opt flex items-center gap-3 rounded-xl border px-4 py-3.5 cursor-pointer hover:border-brand-300 <?php echo e($method==='rocket' ? 'border-2 border-brand-500 bg-brand-50/60' : 'border-slate-200'); ?>">
              <input type="radio" name="payment_method" value="rocket" <?php if($method==='rocket'): echo 'checked'; endif; ?> class="pay-radio text-brand-600 focus:ring-brand-500" data-manual="1" data-pay-number="<?php echo e($rocket); ?>" />
              <span class="font-medium">Rocket</span>
              <span class="ml-auto rounded-md bg-purple-100 text-purple-600 text-xs font-bold px-2 py-1"><?php echo e($rocket); ?></span>
            </label>
            <?php endif; ?>
          </div>

          <div id="manualFields" class="mt-4 grid sm:grid-cols-2 gap-4 <?php echo e($method==='cod' ? 'hidden' : ''); ?>">
            <div class="sm:col-span-2 text-xs text-slate-500">Send <b id="manualPayAmount"><?php echo e(money($totals['total'])); ?></b> to <b id="manualPayNumber"><?php echo e($payNumbers[$method] ?? "\u{2014}"); ?></b>, then enter your payment details below.</div>
            <div><label class="block text-sm font-medium mb-1.5">Your sender number</label><input name="payment_sender_number" value="<?php echo e(old('payment_sender_number')); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div><label class="block text-sm font-medium mb-1.5">Transaction ID</label><input name="payment_txn_id" value="<?php echo e(old('payment_txn_id')); ?>" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
          </div>
        </div>

        <button type="submit" class="btn-shine w-full rounded-full bg-brand-600 text-white font-bold py-4 hover:bg-brand-700 transition">Place Order · <span id="placeTotal"><?php echo e(money($totals['total'])); ?></span></button>
        <p class="text-center text-xs text-slate-400">By placing your order you agree to our <a href="<?php echo e(route('terms')); ?>" class="underline hover:text-brand-600">Terms</a> &amp; <a href="<?php echo e(route('privacy')); ?>" class="underline hover:text-brand-600">Privacy Policy</a>.</p>
      </form>

      <aside class="mt-8 lg:mt-0 space-y-4">
        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Coupon code</h2>
          <?php if($couponCode): ?>
            <div class="mt-3 flex items-center justify-between gap-3 bg-brand-50 border border-brand-200 rounded-xl px-4 py-3">
              <div>
                <p class="font-mono font-bold text-brand-700"><?php echo e($couponCode); ?></p>
                <p class="text-xs text-brand-600">You save <?php echo e(money($discount)); ?></p>
              </div>
              <form method="POST" action="<?php echo e(route('checkout.coupon.remove')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600">Remove</button>
              </form>
            </div>
          <?php else: ?>
            <form method="POST" action="<?php echo e(route('checkout.coupon.apply')); ?>" class="mt-3 flex gap-2">
              <?php echo csrf_field(); ?>
              <input name="code" value="<?php echo e(old('code')); ?>" placeholder="Promo code" required class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" />
              <button type="submit" class="rounded-xl bg-brand-600 text-white text-sm font-semibold px-4 hover:bg-brand-700 transition">Apply</button>
            </form>
            <?php $__errorArgs = ['coupon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <?php endif; ?>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100 lg:sticky lg:top-8">
          <h2 class="font-display text-lg font-extrabold">Your order</h2>
          <div class="mt-5 space-y-4">
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="space-y-3 rounded-2xl border border-slate-100 p-4">
                <div class="flex gap-3 items-start">
                  <div class="relative"><img src="<?php echo e($item->image); ?>" loading="lazy" decoding="async" class="h-14 w-14 rounded-xl object-cover" alt="<?php echo e($item->name); ?>" /><span class="absolute -top-2 -right-2 grid h-5 w-5 place-items-center rounded-full bg-brand-600 text-white text-[10px] font-bold"><?php echo e($item->qty); ?></span></div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <p class="text-sm font-semibold truncate"><?php echo e($item->name); ?></p>
                        <p class="text-xs text-slate-500"><?php echo e($item->variant ?: $item->product->unit); ?></p>
                      </div>
                      <span class="text-sm font-bold"><?php echo e(money($item->line_total)); ?></span>
                    </div>
                    <div class="mt-3">
                      <form method="POST" action="<?php echo e(route('cart.update')); ?>" class="inline-flex w-max items-center rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden h-8">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="key" value="<?php echo e($item->key); ?>">
                        <button type="submit" name="qty" value="<?php echo e(max(0, $item->qty - 1)); ?>" class="flex-none w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                          </svg>
                        </button>
                        <div class="flex-none w-8 h-8 flex items-center justify-center border-x border-gray-200 font-semibold text-gray-800"><?php echo e($item->qty); ?></div>
                        <button type="submit" name="qty" value="<?php echo e($item->qty + 1); ?>" class="flex-none w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <dl class="mt-5 space-y-2.5 text-sm border-t border-slate-100 pt-5">
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold"><?php echo e(money($subtotal)); ?></dd></div>
            <?php if($discount > 0): ?>
              <div class="flex justify-between text-brand-600"><dt>Discount (<?php echo e($couponCode); ?>)</dt><dd class="font-semibold" id="sumDiscount">−<?php echo e(money($discount)); ?></dd></div>
            <?php endif; ?>
            <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd class="font-semibold text-brand-600" id="sumShipping"><?php echo e(money($totals['shipping'])); ?></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Tax (<?php echo e(rtrim(rtrim(number_format($taxPercent, 2), '0'), '.')); ?>%)</dt><dd class="font-semibold" id="sumTax"><?php echo e(money($totals['tax'])); ?></dd></div>
          </dl>
          <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between"><span class="font-semibold">Total</span><span class="font-display text-2xl font-extrabold text-brand-700" id="sumTotal"><?php echo e(money($totals['total'])); ?></span></div>
          <?php if(trim((string) setting('delivery_eta_text', '')) !== ''): ?>
            <div class="mt-5 flex items-center gap-2.5 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-800"><svg class="h-5 w-5 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg> <?php echo e(setting('delivery_eta_text')); ?></div>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
  var subtotal = <?php echo e($subtotal); ?>;
  var discount = <?php echo e($discount); ?>;
  var taxPct = <?php echo e($taxPercent); ?>;
  var zone = document.getElementById('shippingZone');
  var currencySymbol = <?php echo json_encode(currency_symbol(), 15, 512) ?>;
  var money = function (n) {
    return currencySymbol + ' ' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  };

  function recalc() {
    var fee = parseFloat(zone.options[zone.selectedIndex].dataset.fee) || 0;
    var taxable = Math.max(0, subtotal - discount);
    var tax = Math.round(taxable * taxPct / 100);
    var total = taxable + fee + tax;

    document.getElementById('sumShipping').textContent = money(fee);
    document.getElementById('sumTax').textContent = money(tax);
    document.getElementById('sumTotal').textContent = money(total);
    document.getElementById('placeTotal').textContent = money(total);

    var manualAmt = document.getElementById('manualPayAmount');
    if (manualAmt) manualAmt.textContent = money(total);
  }

  if (zone) zone.addEventListener('change', recalc);

  document.querySelectorAll('.pay-radio').forEach(function (r) {
    r.addEventListener('change', function () {
      document.querySelectorAll('.pay-opt').forEach(function (o) {
        o.classList.remove('border-2', 'border-brand-500', 'bg-brand-50/60');
        o.classList.add('border-slate-200');
      });
      var label = r.closest('.pay-opt');
      label.classList.add('border-2', 'border-brand-500', 'bg-brand-50/60');
      label.classList.remove('border-slate-200');
      document.getElementById('manualFields').classList.toggle('hidden', r.dataset.manual === '0');
      var numEl = document.getElementById('manualPayNumber');
      if (numEl) numEl.textContent = r.dataset.payNumber || "\u{2014}";
    });
  });

  recalc();

  // Auto-paste and auto-apply coupon code from URL or sessionStorage
  const urlParams = new URLSearchParams(window.location.search);
  let autoCoupon = urlParams.get('coupon');
  if (!autoCoupon) {
    try {
      autoCoupon = sessionStorage.getItem('auto_apply_coupon');
    } catch(e) {}
  }
  if (autoCoupon) {
    try {
      sessionStorage.removeItem('auto_apply_coupon');
    } catch(e) {}
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput && !codeInput.value) {
      codeInput.value = autoCoupon;
      const couponForm = codeInput.closest('form');
      if (couponForm) {
        if (window.showToast) {
          window.showToast('Coupon ' + autoCoupon + ' applied!', 'success');
        }
        setTimeout(function() {
          couponForm.submit();
        }, 300);
      }
    }
  }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.storefront', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/checkout.blade.php ENDPATH**/ ?>