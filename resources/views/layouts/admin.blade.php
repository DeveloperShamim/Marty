<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Admin') &mdash; {{ site_name() }} Admin</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="icon" href="{{ favicon_url() }}" />
  <script>try{if(localStorage.getItem('admin.sidebar.collapsed')==='1')document.documentElement.classList.add('sb-collapsed')}catch(e){}</script>
  @php
    // Same brand colours as the storefront (Settings > theme), spread into a full 50-900 scale for the admin.
    $theme = generate_3_color_matching_theme();
    $mix = function (string $a, string $b, float $t): string {
        [$a, $b] = [ltrim($a, '#'), ltrim($b, '#')];
        $c = fn ($h, $i) => hexdec(substr($h, $i, 2));
        return sprintf('#%02X%02X%02X', ...array_map(fn ($i) => (int) round($c($a, $i) + ($c($b, $i) - $c($a, $i)) * $t), [0, 2, 4]));
    };
    $p = $theme['primary']; $d = $theme['dark'];
    $scale = [
        50 => $mix($p, '#FFFFFF', .92), 100 => $mix($p, '#FFFFFF', .84), 200 => $mix($p, '#FFFFFF', .68), 300 => $mix($p, '#FFFFFF', .46),
        400 => $mix($p, '#FFFFFF', .22), 500 => $mix($p, '#FFFFFF', .08), 600 => $p, 700 => $theme['primary_hover'],
        800 => $mix($p, $d, .6), 900 => $d,
    ];
  @endphp
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    {{-- gray (and slate) use the warm stone palette and teal (the old admin accent) follows the store brand, so every page picks up the new look. --}}
    var brandScale = @json($scale);
    var stone = { 50:'#fafaf9', 100:'#f5f5f4', 200:'#e7e5e4', 300:'#d6d3d1', 400:'#a8a29e', 500:'#78716c', 600:'#57534e', 700:'#44403c', 800:'#292524', 900:'#1c1917', 950:'#0c0a09' };
    tailwind.config = { theme: { extend: {
      colors: {
        primary: { DEFAULT: '{{ $p }}' },
        brand: brandScale,
        teal: brandScale,
        gray: stone,
        {{-- slate panels (POS, order page) turn warm, with the darkest shades in the store's dark leather tone --}}
        slate: Object.assign({}, stone, { 800: '{{ $mix($d, '#FFFFFF', .12) }}', 900: '{{ $d }}', 950: '{{ $mix($d, '#000000', .3) }}' }),
        accent: { 500: '{{ $p }}' },
        ink: '{{ $d }}',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'ui-sans-serif', 'system-ui'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
      boxShadow: { card: '0 1px 2px rgba(41,37,36,.04), 0 4px 16px -8px rgba(41,37,36,.08)' },
    } } };
  </script>
  <style>
    :root {
      --brand: {{ $p }}; --primary: {{ $p }}; --brand-primary: {{ $p }};
      --brand-700: {{ $theme['primary_hover'] }}; --brand-hover: {{ $theme['primary_hover'] }};
      --brand-soft: {{ $scale[50] }}; --brand-ring: {{ $scale[300] }};
      --ink: {{ $d }}; --brand-dark: {{ $d }};
    }
  </style>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('theme/css/admin.css') }}?v={{ @filemtime(public_path('theme/css/admin.css')) ?: '1' }}" />
  <style>
    body{font-family:'Plus Jakarta Sans','Hind Siliguri',sans-serif}
    /* Prevent mobile auto-zoom on input focus (iOS Safari/Chrome zooms in if font-size < 16px) */
    @media screen and (max-width: 768px) {
      input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]),
      select,
      textarea {
        font-size: 16px !important;
      }
    }
  </style>
</head>
<body class="admin-body bg-[#F6F3EF] text-gray-800 antialiased{{ testing_mode() ? ' testing-mode' : '' }}">
  @if(testing_mode())
    <div class="bg-amber-50 border-b border-amber-200 text-amber-900 text-sm px-4 py-2 text-center font-medium">
      Testing mode is on &mdash; Save, Delete, and other write actions are disabled.
    </div>
  @endif
  <div class="admin-shell flex min-h-screen min-h-[100dvh]">
    @include('admin.partials.sidebar')

    <div class="admin-main flex-1 flex flex-col min-w-0 w-full">
      @include('admin.partials.topbar')

      @if(session('status'))
        <div class="px-3 sm:px-4 lg:px-6 pt-4">
          <div class="flex items-center gap-2.5 bg-white border border-emerald-200 text-emerald-800 text-sm font-semibold px-4 py-3 rounded-2xl shadow-sm"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>{{ session('status') }}</div>
        </div>
      @endif
      @if(session('error'))
        <div class="px-3 sm:px-4 lg:px-6 pt-4">
          <div class="flex items-center gap-2.5 bg-white border border-red-200 text-red-700 text-sm font-semibold px-4 py-3 rounded-2xl shadow-sm"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-red-100 text-red-600 font-black text-xs">!</span>{{ session('error') }}</div>
        </div>
      @endif
      @if($errors->any())
        <div class="px-3 sm:px-4 lg:px-6 pt-4">
          <div class="bg-white border border-red-200 text-red-700 text-sm px-4 py-3 rounded-2xl shadow-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        </div>
      @endif

      <main class="flex-1 p-3 sm:p-5 lg:p-7">
        @yield('content')
      </main>
    </div>
  </div>
  <div id="backdrop" class="fixed inset-0 bg-ink/50 backdrop-blur-[2px] z-40 hidden lg:hidden" aria-hidden="true"></div>
  <script src="{{ asset('theme/js/admin-shell.js') }}?v={{ @filemtime(public_path('theme/js/admin-shell.js')) ?: '1' }}"></script>
  @php $alertUser = auth()->user(); @endphp
  @if(\App\Support\StaffAccess::allows($alertUser, 'orders'))
    {{-- New-order popup: polls the order feed; see public/theme/js/order-alerts.js --}}
    <div id="orderAlertsConfig" hidden
         data-feed="{{ route('admin.orders.feed') }}"
         data-review-url="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}"
         data-icon="{{ favicon_url() }}"></div>
    <script src="{{ asset('theme/js/order-alerts.js') }}?v={{ @filemtime(public_path('theme/js/order-alerts.js')) ?: '1' }}" defer></script>
  @endif
  @if(testing_mode())
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
  @endif
  @stack('scripts')
</body>
</html>
