<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — <?php echo e(site_name()); ?></title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="icon" href="<?php echo e(favicon_url()); ?>" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: {
      colors: {
        primary: { DEFAULT: '#0f766e' },
        brand: { 600: '#0f766e', 700: '#0B4F4A' },
        accent: { 500: '#B8892E' },
        ink: '#1F2A28',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'ui-sans-serif', 'system-ui'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
    } } };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>body { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; }</style>
</head>
<body class="bg-[#F5F8F7] min-h-screen flex items-center justify-center p-4 text-ink">
  <div class="w-full max-w-md">
    <div class="flex items-center justify-center mb-6">
      <?php if(has_custom_logo()): ?>
        <img src="<?php echo e(logo_url()); ?>" alt="<?php echo e(site_name()); ?>" class="h-10 w-auto max-w-[180px] object-contain" />
      <?php else: ?>
        <?php echo $__env->make('partials.brand', ['href' => route('home'), 'size' => 'lg'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-8">
      <h1 class="text-xl font-bold text-center font-display">Admin Sign in</h1>
      <p class="text-sm text-stone-500 text-center mt-1 mb-6">Welcome back, please sign in to continue.</p>

      <?php if($errors->any()): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm px-3 py-2 rounded-lg"><?php echo e($errors->first()); ?></div>
      <?php endif; ?>

      <?php if(testing_mode()): ?>
                <div class="mb-4 overflow-hidden rounded-lg shadow-md ring-2" style="--tm-brand: #0f766e; box-shadow: 0 0 0 2px color-mix(in srgb, #0f766e 40%, transparent);">
          <div class="px-3 py-2.5 text-sm font-extrabold tracking-wide uppercase" style="background: #0f766e; color: #ffffff;">
            Testing mode is ON
          </div>
          <div class="border border-t-0 px-3 py-3 text-sm text-slate-800" style="border-color: color-mix(in srgb, #0f766e 35%, #e5e7eb); background: color-mix(in srgb, #0f766e 8%, #ffffff);">
            <p class="font-semibold" style="color: #0f766e;">Demo admin login</p>
            <p class="mt-2"><span class="text-slate-500">Email:</span> <code class="font-mono font-medium px-1 rounded" style="background: color-mix(in srgb, #0f766e 12%, #ffffff);"><?php echo e(config('app.testing_admin_email')); ?></code></p>
            <p class="mt-1"><span class="text-slate-500">Password:</span> <code class="font-mono font-medium px-1 rounded" style="background: color-mix(in srgb, #0f766e 12%, #ffffff);"><?php echo e(config('app.testing_admin_password')); ?></code></p>
            <div class="mt-3 pt-3 border-t" style="border-color: color-mix(in srgb, #0f766e 25%, #e5e7eb);">
              <p class="font-semibold" style="color: #0f766e;">After you purchase — turn testing mode OFF</p>
              <ol class="mt-1.5 list-decimal list-inside space-y-1 text-slate-700 text-xs sm:text-sm">
                <li>Open this site’s <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">shop/.env</code> file</li>
                <li>Find <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">TESTING_MODE=true</code></li>
                <li>Change it to <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">TESTING_MODE=false</code></li>
                <li>Save the file, then refresh this page</li>
              </ol>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('admin.login.attempt')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div>
          <label class="block text-sm text-stone-600 mb-1">Email</label>
          <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="username"
            class="w-full border border-stone-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/30" />
        </div>
        <div>
          <label class="block text-sm text-stone-600 mb-1">Password</label>
          <input type="password" name="password" value="" required autocomplete="current-password"
            class="w-full border border-stone-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600/30" />
        </div>
        <label class="flex items-center gap-2 text-sm text-stone-600">
          <input type="checkbox" name="remember" class="rounded border-stone-300 text-brand-600" /> Remember me
        </label>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg py-2.5 transition">Sign in</button>
      </form>
    </div>

    <p class="text-center text-xs text-stone-400 mt-6">© <?php echo e(date('Y')); ?> <?php echo e(site_name()); ?>. Admin Panel.</p>
    <p class="text-center mt-2"><a href="<?php echo e(route('home')); ?>" class="text-sm text-stone-500 hover:text-brand-600">← Back to store</a></p>
  </div>
</body>
</html>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>