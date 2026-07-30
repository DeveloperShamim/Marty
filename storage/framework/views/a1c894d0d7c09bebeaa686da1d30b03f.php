<?php
  $site = site_name();
  $facebook = setting('facebook_url');
  $instagram = setting('instagram_url');
  $twitter = setting('twitter_url');
  $footerText = trim((string) setting('footer_text', ''));
  $phone = trim((string) setting('contact_phone', ''));

  $paymentBadges = [];
  if ((string) setting('pay_cod_enabled', '1') === '1') {
      $paymentBadges[] = 'COD';
  }
  if ((string) setting('pay_bkash_enabled', '1') === '1' && setting('bkash_number')) {
      $paymentBadges[] = 'bKash';
  }
  if ((string) setting('pay_nagad_enabled', '1') === '1' && setting('nagad_number')) {
      $paymentBadges[] = 'Nagad';
  }
  if ((string) setting('pay_rocket_enabled', '1') === '1' && setting('rocket_number')) {
      $paymentBadges[] = 'Rocket';
  }
  if ((string) setting('show_cards_in_footer', '0') === '1') {
      $paymentBadges[] = 'Card';
  }
?>

<?php
  $site = site_name();
  $facebook = setting('facebook_url');
  $instagram = setting('instagram_url');
  $twitter = setting('twitter_url');
  $footerText = trim((string) setting('footer_text', ''));
  $phone = trim((string) setting('contact_phone', ''));

  $paymentBadges = [];
  if ((string) setting('pay_cod_enabled', '1') === '1') {
      $paymentBadges[] = 'COD';
  }
  if ((string) setting('pay_bkash_enabled', '1') === '1' && setting('bkash_number')) {
      $paymentBadges[] = 'bKash';
  }
  if ((string) setting('pay_nagad_enabled', '1') === '1' && setting('nagad_number')) {
      $paymentBadges[] = 'Nagad';
  }
  if ((string) setting('pay_rocket_enabled', '1') === '1' && setting('rocket_number')) {
      $paymentBadges[] = 'Rocket';
  }
  if ((string) setting('show_cards_in_footer', '0') === '1') {
      $paymentBadges[] = 'Card';
  }
?>

<footer class="bg-slate-900 text-slate-100 mt-14 border-t border-slate-800">
  <!-- Value Proposition / Trust Bar -->
  <div class="border-b border-slate-800 py-6 bg-slate-950/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-5 grid grid-cols-2 md:grid-cols-4 gap-6 text-center md:text-left">
      <div class="flex items-center justify-center md:justify-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-brand-500/15 border border-brand-500/30 text-brand-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
          <h5 class="font-bold text-sm text-white">Fast Home Delivery</h5>
          <p class="text-xs text-slate-400">Quick &amp; reliable shipping</p>
        </div>
      </div>

      <div class="flex items-center justify-center md:justify-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <h5 class="font-bold text-sm text-white">100% Authentic</h5>
          <p class="text-xs text-slate-400">Guaranteed quality items</p>
        </div>
      </div>

      <div class="flex items-center justify-center md:justify-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div>
          <h5 class="font-bold text-sm text-white">Secure Payments</h5>
          <p class="text-xs text-slate-400">COD &amp; Mobile Banking</p>
        </div>
      </div>

      <div class="flex items-center justify-center md:justify-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-400 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
          <h5 class="font-bold text-sm text-white">Dedicated Support</h5>
          <p class="text-xs text-slate-400">Friendly customer care</p>
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-5 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
    <div class="col-span-2 md:col-span-1">
      <p class="text-2xl font-extrabold mb-2 text-white tracking-tight"><?php echo e($site); ?></p>
      <?php if($footerText !== ''): ?>
        <p class="text-slate-400 leading-relaxed text-sm"><?php echo e($footerText); ?></p>
      <?php endif; ?>
      <?php if($phone !== ''): ?>
        <a href="tel:<?php echo e(preg_replace('/\s+/', '', $phone)); ?>" class="inline-flex items-center gap-2 mt-4 bg-brand-500 hover:bg-brand-600 text-white font-bold px-4 py-2.5 rounded-xl shadow-md transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          <?php echo e($phone); ?>

        </a>
      <?php endif; ?>
      <?php if($facebook || $instagram || $twitter): ?>
        <div class="flex gap-2 mt-4">
          <?php if($facebook): ?>
            <a href="<?php echo e($facebook); ?>" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-slate-800 hover:bg-brand-500 border border-slate-700/60 text-slate-300 hover:text-white flex items-center justify-center transition-all" aria-label="Facebook">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>
            </a>
          <?php endif; ?>
          <?php if($instagram): ?>
            <a href="<?php echo e($instagram); ?>" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-slate-800 hover:bg-brand-500 border border-slate-700/60 text-slate-300 hover:text-white flex items-center justify-center transition-all" aria-label="Instagram">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg>
            </a>
          <?php endif; ?>
          <?php if($twitter): ?>
            <a href="<?php echo e($twitter); ?>" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-slate-800 hover:bg-brand-500 border border-slate-700/60 text-slate-300 hover:text-white flex items-center justify-center transition-all" aria-label="X (Twitter)">
              <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.59l-5.16-6.74L5.2 22H1.94l8.03-9.17L1.5 2h6.75l4.66 6.18L18.244 2Zm-1.16 18.1h1.83L7.05 3.79H5.09L17.084 20.1Z"/></svg>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <h4 class="font-bold mb-3.5 text-white tracking-wide">Shop Links</h4>
      <ul class="space-y-2.5 text-slate-300">
        <li><a href="<?php echo e(route('home')); ?>" class="hover:text-brand-400 transition-colors">Home</a></li>
        <li><a href="<?php echo e(route('shop')); ?>" class="hover:text-brand-400 transition-colors">Shop</a></li>
        <?php if($hasFlashSale ?? false): ?>
          <li><a href="<?php echo e(route('shop', ['flash' => 1])); ?>" class="hover:text-brand-400 transition-colors">Deals &amp; Offers</a></li>
        <?php endif; ?>
        <li><a href="<?php echo e(route('contact')); ?>" class="hover:text-brand-400 transition-colors">Contact</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-bold mb-3.5 text-white tracking-wide">Customer Service</h4>
      <ul class="space-y-2.5 text-slate-300">
        <li><a href="<?php echo e(route('contact')); ?>" class="hover:text-brand-400 transition-colors">Contact Us</a></li>
        <li><a href="<?php echo e(route('track')); ?>" class="hover:text-brand-400 transition-colors">Track Order</a></li>
        <li><a href="<?php echo e(route('login')); ?>" class="hover:text-brand-400 transition-colors">My Account</a></li>
        <li><a href="<?php echo e(route('terms')); ?>" class="hover:text-brand-400 transition-colors">Terms of Service</a></li>
        <li><a href="<?php echo e(route('privacy')); ?>" class="hover:text-brand-400 transition-colors">Privacy Policy</a></li>
      </ul>
    </div>

    <?php if(count($paymentBadges)): ?>
      <div>
        <h4 class="font-bold mb-3.5 text-white tracking-wide">We Accept</h4>
        <div class="flex flex-wrap gap-2 text-xs font-semibold">
          <?php $__currentLoopData = $paymentBadges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($badge === 'bKash'): ?>
              <span class="inline-flex items-center gap-1.5 bg-[#e2136e]/15 text-[#f672a7] border border-[#e2136e]/30 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-[#e2136e]"></span>
                bKash
              </span>
            <?php elseif($badge === 'Nagad'): ?>
              <span class="inline-flex items-center gap-1.5 bg-[#f7941d]/15 text-[#ffb75e] border border-[#f7941d]/30 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-[#f7941d]"></span>
                Nagad
              </span>
            <?php elseif($badge === 'Rocket'): ?>
              <span class="inline-flex items-center gap-1.5 bg-[#8c3494]/15 text-[#d484dc] border border-[#8c3494]/30 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-[#8c3494]"></span>
                Rocket
              </span>
            <?php elseif($badge === 'COD'): ?>
              <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Cash on Delivery
              </span>
            <?php elseif($badge === 'Card'): ?>
              <span class="inline-flex items-center gap-1.5 bg-blue-500/15 text-blue-300 border border-blue-500/30 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                Cards
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 bg-slate-800 text-slate-200 border border-slate-700 px-2.5 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-brand-400"></span>
                <?php echo e($badge); ?>

              </span>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="border-t border-slate-800/80 py-4 max-w-7xl mx-auto px-4 sm:px-5 flex flex-wrap justify-between gap-2 text-xs text-slate-400">
    <span>© <?php echo e(date('Y')); ?> <?php echo e($site); ?>. All rights reserved.</span>
  </div>
  <?php if(testing_mode()): ?>

  <div class="ws-dev-credit">

    <span>Developed by <strong>WaveSeller</strong></span>

  </div>

  <?php endif; ?>
</footer>

<?php /**PATH /home/unilifeb/UnilifeBD/resources/views/storefront/partials/footer.blade.php ENDPATH**/ ?>