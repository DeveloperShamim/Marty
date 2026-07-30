<?php
  $accountUser = auth()->user();
  $lightHeader = $lightHeader ?? false;
?>
<div class="relative" data-account-menu>
  <button type="button" data-account-toggle class="flex items-center gap-1.5 p-1.5 sm:p-2 text-sm font-semibold rounded-xl transition <?php echo e($lightHeader ? 'text-white hover:text-white/80' : 'text-slate-600 hover:text-brand-600 hover:bg-brand-50'); ?>" aria-label="Account menu" aria-expanded="false" aria-haspopup="true">
    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
    <span class="hidden lg:inline"><?php echo e(auth()->check() ? 'My Account' : 'Login / Sign up'); ?></span>
  </button>

  <div data-account-dropdown class="hidden absolute right-0 top-full mt-2 w-72 max-w-[calc(100vw-1.5rem)] rounded-2xl border border-slate-100 bg-white shadow-soft z-50 overflow-hidden" role="menu">
    <?php if(auth()->guard()->check()): ?>
      <div class="px-4 py-3.5 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-full bg-brand-600 text-white font-display font-bold text-sm shrink-0">
            <?php echo e(strtoupper(substr($accountUser->name, 0, 1))); ?>

          </span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-ink truncate"><?php echo e($accountUser->name); ?></p>
            <p class="text-xs text-slate-500 truncate"><?php echo e($accountUser->email); ?></p>
          </div>
        </div>
      </div>
      <div class="p-1.5">
        <a href="<?php echo e(route('account')); ?>" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
          My account
        </a>
        <a href="<?php echo e(route('account')); ?>#orders" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5h11M9 12h11M9 19h11M4 5h.01M4 12h.01M4 19h.01"/></svg>
          My orders
        </a>
        <a href="<?php echo e(route('track')); ?>" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
          Track order
        </a>
      </div>
      <div class="border-t border-slate-100 p-1.5">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
          <?php echo csrf_field(); ?>
          <button type="submit" role="menuitem" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-red-600 hover:bg-red-50">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Log out
          </button>
        </form>
      </div>
    <?php else: ?>
      <div class="px-4 py-3.5 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
        <p class="text-sm font-semibold text-ink">Welcome to <?php echo e(site_name()); ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Sign in to continue</p>
      </div>
      <div class="p-3 space-y-2">
        <a href="<?php echo e(route('login')); ?>" role="menuitem" class="flex items-center justify-center rounded-xl bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Sign in</a>
        <a href="<?php echo e(route('register')); ?>" role="menuitem" class="flex items-center justify-center rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-ink ring-1 ring-slate-200 hover:bg-brand-50">Create account</a>
      </div>
      <div class="border-t border-slate-100 p-1.5">
        <a href="<?php echo e(route('track')); ?>" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
          Track order
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/storefront/partials/account-dropdown.blade.php ENDPATH**/ ?>