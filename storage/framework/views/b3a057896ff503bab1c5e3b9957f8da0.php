<?php
    $name = $siteName ?? site_name();
    $href = $href ?? route('home');
    $light = $light ?? false;
    $size = $size ?? 'md';
    $class = $class ?? '';
    $iconOnly = $iconOnly ?? false;
    $custom = has_custom_logo();

    $textClass = match ($size) {
        'sm' => 'text-lg sm:text-xl',
        'lg' => 'text-2xl sm:text-3xl',
        default => 'text-xl sm:text-2xl',
    };
    $customClass = match ($size) {
        'sm' => 'h-8 w-auto max-w-[140px]',
        'lg' => 'h-11 w-auto max-w-[200px]',
        default => 'h-9 sm:h-10 w-auto max-w-[180px]',
    };
?>

<a href="<?php echo e($href); ?>" class="flex items-center gap-2 shrink-0 min-w-0 <?php echo e($class); ?>" aria-label="<?php echo e($name); ?>">
  <?php if($custom): ?>
    <img src="<?php echo e(logo_url()); ?>" alt="<?php echo e($name); ?>" class="<?php echo e($customClass); ?> object-contain" />
  <?php else: ?>
    <span class="h-10 w-10 rounded-lg <?php echo e($light ? 'bg-white text-brand-700' : 'bg-brand-600 text-white'); ?> flex items-center justify-center shrink-0">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M6 7h13l-1.2 8H7.5L6 7Zm0 0-.8-3H3"/>
        <path d="M10 11v2M14 11v2"/>
        <path d="M12 4c0 2-1.5 3-1.5 3S9 6 9 4a1.5 1.5 0 0 1 3 0z"/>
      </svg>
    </span>
    <?php if (! ($iconOnly)): ?>
      <span class="leading-tight">
        <span class="block <?php echo e($textClass); ?> font-extrabold tracking-tight <?php echo e($light ? 'text-white' : 'text-brand-700'); ?>"><?php echo e($name); ?></span>
        <?php if(setting('tagline') && $size !== 'sm'): ?>
          <span class="hidden sm:block text-[10px] font-semibold tracking-[0.18em] <?php echo e($light ? 'text-white/70' : 'text-stone-400'); ?> uppercase"><?php echo e(setting('tagline')); ?></span>
        <?php endif; ?>
      </span>
    <?php endif; ?>
  <?php endif; ?>
</a>
<?php /**PATH /home/unilifeb/UnilifeBD/resources/views/partials/brand.blade.php ENDPATH**/ ?>