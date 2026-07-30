<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  @include('partials.seo')

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: {
colors: {
  brand: {
    50:  '#FAFAFA',
    100: '#F3F3F3',
    200: '#E5E5E5',
    300: '#D4D4D4',
    400: '#E8751B',
    500: '#FC8933', // Primary Orange
    600: '#E8751B',
    700: '#545454',
    800: '#545454', // Logo Dark
    900: '#353535', // Dark Navigation/Footer
  },

  accent: {
    400: '#FFD7B3',
    500: '#FC8933',
    600: '#E8751B',
    700: '#545454',
  },

  ink: '#2D2D2D',

  surface: {
    light: '#FFFFFF',
    soft: '#F7F7F7',
  },
},
      fontFamily: { sans:['Plus Jakarta Sans','Hind Siliguri','sans-serif'], display:['Plus Jakarta Sans','Hind Siliguri','sans-serif'] },
    } } };
  </script>
  <link rel="stylesheet" href="{{ asset('theme/css/style.css') . '?v=ws1' }}" />
  <link rel="icon" href="{{ favicon_url() }}" />
  <link rel="apple-touch-icon" href="{{ favicon_url() }}" />

  <script type="application/ld+json">
  {!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@'.'type' => 'Organization',
    'name' => site_name(),
    'url' => url('/'),
    'logo' => logo_url(),
    'telephone' => setting('contact_phone'),
    'email' => setting('contact_email'),
  ], JSON_UNESCAPED_SLASHES) !!}
  </script>

  @include('partials.tracking-head')
</head>

<body class="@yield('body_class', 'bg-white text-ink antialiased')">
  @include('partials.tracking-body')

  @hasSection('checkout_header')
    @yield('checkout_header')
  @elseif(($headerVariant ?? 'full') === 'compact')
    @include('storefront.partials.header-compact')
  @else
    @include('storefront.partials.header')
  @endif

  @if(session('status'))
    <div class="max-w-7xl mx-auto px-4 sm:px-5 mt-4">
      <div class="bg-white border border-brand-600/20 text-brand-700 text-sm font-semibold px-4 py-3 rounded-xl">{{ session('status') }}</div>
    </div>
  @endif

  @yield('content')

  @include('storefront.partials.footer')
  @include('storefront.partials.cart-drawer')
  @include('storefront.partials.mobile-menu')
  @include('storefront.partials.quick-select-modal')

  <div id="overlay" data-drawer-overlay class="fixed inset-0 bg-ink/40 z-40 opacity-0 pointer-events-none transition-opacity"></div>

  {{-- Floating Side Quick Cart Widget --}}
  <button type="button" data-open-cart class="fixed right-0 top-1/2 -translate-y-1/2 z-40 flex flex-col items-center rounded-l-lg shadow-xl overflow-hidden focus:outline-none bg-white border border-r-0 border-brand-500/30 min-w-[72px]" aria-label="Quick Cart">
    <div class="bg-brand-500 text-white p-2.5 px-3.5 flex flex-col items-center text-center w-full">
      <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      <span class="cart-count-text text-xs font-extrabold tracking-tight leading-none whitespace-nowrap">{{ ($cartCount ?? 0) }} {{ Str::plural('Item', ($cartCount ?? 0)) }}</span>
    </div>
    <div class="bg-white text-stone-800 p-2 px-3.5 text-xs font-extrabold w-full text-center whitespace-nowrap border-t border-stone-100">
      <span class="cart-total text-brand-600 font-extrabold">{{ money($cartSubtotal ?? 0) }}</span>
    </div>
  </button>

  <button type="button" id="backToTop" data-back-top class="fixed bottom-6 right-6 z-40 h-11 w-11 rounded-full bg-brand-600 text-white shadow-lg flex items-center justify-center opacity-0 pointer-events-none transition-opacity" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
  </button>

  <script src="{{ asset('theme/js/storefront.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
