<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
  <title><?php echo $__env->yieldContent('title', 'Admin'); ?> &mdash; <?php echo e(site_name()); ?> Admin</title>
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
  <link rel="stylesheet" href="<?php echo e(asset('theme/css/admin.css')); ?>?v=<?php echo e(@filemtime(public_path('theme/css/admin.css')) ?: '1'); ?>" />
  <style>body{font-family:'Plus Jakarta Sans','Hind Siliguri',sans-serif}</style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased<?php echo e(testing_mode() ? ' testing-mode' : ''); ?>">
  <?php if(testing_mode()): ?>
    <div class="bg-amber-50 border-b border-amber-200 text-amber-900 text-sm px-4 py-2 text-center font-medium">
      Testing mode is on &mdash; Save, Delete, and other write actions are disabled.
    </div>
  <?php endif; ?>
  <div class="admin-shell flex min-h-screen min-h-[100dvh]">
    <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-main flex-1 flex flex-col min-w-0 w-full">
      <?php echo $__env->make('admin.partials.topbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      <?php if(session('status')): ?>
        <div class="px-3 sm:px-4 lg:px-6 pt-4">
          <div class="bg-primary/10 border border-primary/20 text-primary text-sm font-medium px-4 py-3 rounded-xl"><?php echo e(session('status')); ?></div>
        </div>
      <?php endif; ?>
      <?php if($errors->any()): ?>
        <div class="px-3 sm:px-4 lg:px-6 pt-4">
          <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
          </div>
        </div>
      <?php endif; ?>

      <main class="flex-1 p-3 sm:p-4 lg:p-6">
        <?php echo $__env->yieldContent('content'); ?>
      </main>
    </div>
  </div>
  <div id="backdrop" class="fixed inset-0 bg-ink/40 z-40 hidden lg:hidden" aria-hidden="true"></div>
  <script src="<?php echo e(asset('theme/js/admin-shell.js')); ?>?v=<?php echo e(@filemtime(public_path('theme/js/admin-shell.js')) ?: '1'); ?>"></script>
  <?php if(testing_mode()): ?>
  <script>
    (function () {
      var MSG = 'In testing mode, not clickable.';

      function formMethod(form) {
        var method = (form.getAttribute('method') || 'get').toUpperCase();
        var spoof = form.querySelector('input[name="_method"]');
        if (spoof && spoof.value) method = spoof.value.toUpperCase();
        return method;
      }

      function isLogoutForm(form) {
        var action = (form.getAttribute('action') || '').toLowerCase();
        return action.indexOf('/admin/logout') !== -1;
      }

      function isMutatingForm(form) {
        if (!(form instanceof HTMLFormElement)) return false;
        if (isLogoutForm(form)) return false;
        return formMethod(form) !== 'GET';
      }

      function markLockedControls() {
        document.querySelectorAll('form').forEach(function (form) {
          if (!isMutatingForm(form)) return;
          form.querySelectorAll('button, input[type="submit"], input[type="button"]').forEach(function (el) {
            el.classList.add('testing-locked');
          });
        });
        document.querySelectorAll('button[form], input[type="submit"][form]').forEach(function (el) {
          var form = document.getElementById(el.getAttribute('form'));
          if (isMutatingForm(form)) el.classList.add('testing-locked');
        });
        var saveAll = document.getElementById('saveAllSettings');
        if (saveAll) saveAll.classList.add('testing-locked');
      }

      document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!isMutatingForm(form)) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        alert(MSG);
      }, true);

      document.addEventListener('click', function (e) {
        var locked = e.target.closest('.testing-locked, #saveAllSettings');
        if (locked) {
          e.preventDefault();
          e.stopImmediatePropagation();
          alert(MSG);
          return;
        }

        var btn = e.target.closest('button[form], input[type="submit"][form]');
        if (!btn || !btn.getAttribute('form')) return;
        var form = document.getElementById(btn.getAttribute('form'));
        if (!isMutatingForm(form)) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        alert(MSG);
      }, true);

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', markLockedControls);
      } else {
        markLockedControls();
      }
    })();
  </script>
  <?php endif; ?>
  <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/layouts/admin.blade.php ENDPATH**/ ?>