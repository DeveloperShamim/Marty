<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — {{ site_name() }}</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="icon" href="{{ favicon_url() }}" />
  @php $theme = generate_3_color_matching_theme(); @endphp
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: {
      colors: {
        primary: { DEFAULT: '{{ $theme['primary'] }}' },
        brand: { 50: '{{ $theme['primary_soft_bg'] }}', 600: '{{ $theme['primary'] }}', 700: '{{ $theme['primary_hover'] }}' },
        accent: { 500: '{{ $theme['primary'] }}' },
        ink: '{{ $theme['dark'] }}',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'ui-sans-serif', 'system-ui'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
    } } };
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root { --brand: {{ $theme['primary'] }}; --brand-dark: {{ $theme['dark'] }}; }
    body { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; }
    /* Prevent mobile auto-zoom on input focus (iOS zooms in if font-size < 16px) */
    @media screen and (max-width: 768px) { input:not([type="checkbox"]), select, textarea { font-size: 16px !important; } }
  </style>
</head>
<body class="bg-[#F6F3EF] min-h-screen text-ink">
  <div class="min-h-screen grid lg:grid-cols-[1.05fr_1fr]">
    {{-- Brand panel (desktop) --}}
    <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden p-12 text-white" style="background: radial-gradient(90% 70% at 100% 0%, color-mix(in srgb, var(--brand) 60%, transparent) 0%, transparent 60%), linear-gradient(160deg, var(--brand-dark) 0%, color-mix(in srgb, var(--brand-dark) 80%, var(--brand)) 100%);">
      <div class="pointer-events-none absolute -left-24 -bottom-24 h-96 w-96 rounded-full border border-white/[0.07]"></div>
      <div class="pointer-events-none absolute -left-8 -bottom-40 h-96 w-96 rounded-full border border-white/[0.05]"></div>
      <a href="{{ route('home') }}" class="relative inline-flex items-center gap-3">
        @if(has_custom_logo())
          <span class="inline-flex rounded-xl bg-white px-3 py-2 shadow-sm"><img src="{{ logo_url() }}" alt="{{ site_name() }}" class="h-8 w-auto max-w-[170px] object-contain" /></span>
        @else
          <span class="grid h-10 w-10 place-items-center rounded-xl font-extrabold text-lg" style="background: var(--brand);">{{ mb_strtoupper(mb_substr(site_name(), 0, 1)) }}</span>
          <span class="text-lg font-bold tracking-tight">{{ site_name() }}</span>
        @endif
      </a>
      <div class="relative max-w-md">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/50">Admin panel</p>
        <h2 class="mt-3 text-4xl font-extrabold leading-tight tracking-tight">Run your store from one calm place.</h2>
        <p class="mt-4 text-white/65 leading-relaxed">Orders, products, stock, couriers and payments, all in one dashboard.</p>
      </div>
      <p class="relative text-xs text-white/40">© {{ date('Y') }} {{ site_name() }}</p>
    </aside>

    <main class="flex items-center justify-center p-5 sm:p-10">
      <div class="w-full max-w-[420px]">
        <div class="lg:hidden flex items-center justify-center mb-8">
          @if(has_custom_logo())
            <img src="{{ logo_url() }}" alt="{{ site_name() }}" class="h-10 w-auto max-w-[180px] object-contain" />
          @else
            @include('partials.brand', ['href' => route('home'), 'size' => 'lg'])
          @endif
        </div>

        <div class="bg-white rounded-3xl border border-[#ebe5de] shadow-[0_1px_2px_rgba(41,37,36,.04),0_20px_40px_-24px_rgba(41,37,36,.25)] p-7 sm:p-9">
          <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-brand-600 mb-5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
          </span>
          <h1 class="text-2xl font-extrabold tracking-tight font-display">Sign in</h1>
          <p class="text-sm text-stone-500 mt-1 mb-7">Welcome back. Enter your details to open the admin.</p>

      @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm px-3 py-2 rounded-lg">{{ $errors->first() }}</div>
      @endif

      @if(testing_mode())
                <div class="mb-4 overflow-hidden rounded-lg shadow-md ring-2" style="--tm-brand: var(--brand); box-shadow: 0 0 0 2px color-mix(in srgb, var(--brand) 40%, transparent);">
          <div class="px-3 py-2.5 text-sm font-extrabold tracking-wide uppercase" style="background: var(--brand); color: #ffffff;">
            Testing mode is ON
          </div>
          <div class="border border-t-0 px-3 py-3 text-sm text-slate-800" style="border-color: color-mix(in srgb, var(--brand) 35%, #e5e7eb); background: color-mix(in srgb, var(--brand) 8%, #ffffff);">
            <p class="font-semibold" style="color: var(--brand);">Demo admin login</p>
            <p class="mt-2"><span class="text-slate-500">Email:</span> <code class="font-mono font-medium px-1 rounded" style="background: color-mix(in srgb, var(--brand) 12%, #ffffff);">{{ config('app.testing_admin_email') }}</code></p>
            <p class="mt-1"><span class="text-slate-500">Password:</span> <code class="font-mono font-medium px-1 rounded" style="background: color-mix(in srgb, var(--brand) 12%, #ffffff);">{{ config('app.testing_admin_password') }}</code></p>
            <div class="mt-3 pt-3 border-t" style="border-color: color-mix(in srgb, var(--brand) 25%, #e5e7eb);">
              <p class="font-semibold" style="color: var(--brand);">After you purchase — turn testing mode OFF</p>
              <ol class="mt-1.5 list-decimal list-inside space-y-1 text-slate-700 text-xs sm:text-sm">
                <li>Open this site’s <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">shop/.env</code> file</li>
                <li>Find <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">TESTING_MODE=true</code></li>
                <li>Change it to <code class="font-mono px-1 rounded bg-slate-50 border border-slate-200">TESTING_MODE=false</code></li>
                <li>Save the file, then refresh this page</li>
              </ol>
            </div>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
        @csrf
        <div>
          <label class="block text-[13px] font-semibold text-stone-700 mb-1.5">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
            class="w-full h-12 border border-stone-200 bg-stone-50/60 rounded-xl px-4 text-sm transition focus:bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-600/15" />
        </div>
        <div>
          <label class="block text-[13px] font-semibold text-stone-700 mb-1.5">Password</label>
          <input type="password" name="password" value="" required autocomplete="current-password"
            class="w-full h-12 border border-stone-200 bg-stone-50/60 rounded-xl px-4 text-sm transition focus:bg-white focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-600/15" />
        </div>
        <label class="flex items-center gap-2 text-sm text-stone-600">
          <input type="checkbox" name="remember" class="rounded border-stone-300 text-brand-600" style="accent-color: var(--brand);" /> Remember me
        </label>
        <button type="submit" class="w-full h-12 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition shadow-[0_10px_20px_-12px_var(--brand)] active:translate-y-px">Sign in</button>
      </form>
      @if(google_login_enabled())
        <div class="flex items-center gap-3 my-5 text-[11px] font-semibold uppercase tracking-wider text-stone-400"><span class="h-px flex-1 bg-stone-200"></span>or<span class="h-px flex-1 bg-stone-200"></span></div>
        @include('partials.google-button', ['href' => route('auth.google', ['for' => 'admin']), 'class' => 'h-12 px-4 text-sm text-stone-700 ring-stone-200'])
        <p class="text-center text-[11px] text-stone-400 mt-2">Use the Google account with the same email as your staff account.</p>
      @endif
        </div>

        <p class="text-center mt-6"><a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-500 hover:text-brand-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 18l-6-6 6-6"/></svg>Back to store</a></p>
      </div>
    </main>
  </div>
</body>
</html>
