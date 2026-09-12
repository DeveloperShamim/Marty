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

  @php
    $theme = generate_3_color_matching_theme();
  @endphp

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50:  '{{ $theme["primary_soft_bg"] }}',
              100: '{{ $theme["primary_border"] }}',
              200: '{{ $theme["primary_border"] }}',
              300: '{{ $theme["primary"] }}',
              400: '{{ $theme["primary"] }}',
              500: '{{ $theme["primary"] }}',
              600: '{{ $theme["primary"] }}',
              700: '{{ $theme["primary_hover"] }}',
              800: '{{ $theme["dark"] }}',
              900: '{{ $theme["dark"] }}',
            },
            accent: {
              400: '{{ $theme["primary_border"] }}',
              500: '{{ $theme["primary"] }}',
              600: '{{ $theme["primary"] }}',
              700: '{{ $theme["dark"] }}',
            },
            ink: '{{ $theme["dark"] }}',
            surface: {
              light: '#FFFFFF',
              soft: '{{ $theme["surface"] }}',
            },
          },
          fontFamily: { sans:['Plus Jakarta Sans','Hind Siliguri','sans-serif'], display:['Plus Jakarta Sans','Hind Siliguri','sans-serif'] },
        }
      }
    };
  </script>

  <style>
    :root {
      --brand-primary: {{ $theme['primary'] }};
      --brand-hover: {{ $theme['primary_hover'] }};
      --brand-soft-bg: {{ $theme['primary_soft_bg'] }};
      --brand-border: {{ $theme['primary_border'] }};
      --brand-dark: {{ $theme['dark'] }};
      --brand-surface: {{ $theme['surface'] }};
    }

    /* Hide scrollbar for Chrome, Safari, Opera, Edge, and Firefox */
    .scrollbar-none::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }
    .scrollbar-none {
      -ms-overflow-style: none !important;  /* IE and Edge */
      scrollbar-width: none !important;  /* Firefox */
    }

    /* Primary Text Color Utilities */
    .text-brand-600,
    .text-brand-500 {
      color: var(--brand-primary);
    }

    /* Navigation & Menu Hover Text Color -> Primary Color */
    .site-header nav a:hover,
    .site-header nav button:hover,
    #catDropdownMenu a:hover,
    #catDropdownMenu a:hover span,
    #brandDropdownMenu a:hover,
    #brandDropdownMenu a:hover span,
    .group\/item:hover .group-hover\/item\:text-brand-600 {
      color: var(--brand-primary);
    }

    .fk-badge,
    .btn-primary {
      background-color: var(--brand-primary) !important;
      color: #ffffff !important;
    }

    .btn-primary:hover {
      background-color: var(--brand-hover) !important;
      color: #ffffff !important;
    }

    .fk-add-btn,
    .add-to-cart-btn,
    .add-to-cart,
    #pdAddToCart,
    #qmAddToCartBtn {
      background-color: var(--brand-primary) !important;
      border: 1px solid var(--brand-primary) !important;
      color: #ffffff !important;
      border-radius: 12px !important;
    }

    .fk-add-btn:hover,
    .add-to-cart-btn:hover,
    .add-to-cart:hover,
    #pdAddToCart:hover,
    #qmAddToCartBtn:hover {
      background-color: var(--brand-hover) !important;
      border-color: var(--brand-hover) !important;
      color: #ffffff !important;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
    }

    .site-header [data-open-cart],
    .site-header [data-open-cart]:hover,
    .site-header [data-open-cart]:focus {
      background-color: transparent !important;
      border-color: transparent !important;
    }
  </style>

  <link rel="stylesheet" href="{{ asset('theme/css/style.css') . '?v=' . (file_exists(public_path('theme/css/style.css')) ? filemtime(public_path('theme/css/style.css')) : '1') }}" />
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
  @if(setting('size_guide_enabled', '1') === '1')
    @include('storefront.partials.size-guide-modal')
  @endif
  @include('storefront.partials.whatsapp-widget')

  <div id="overlay" data-drawer-overlay class="fixed inset-0 bg-ink/40 z-40 opacity-0 pointer-events-none transition-opacity"></div>

  {{-- Floating Side Quick Cart Widget --}}
  <button type="button" data-open-cart class="hidden sm:flex fixed right-0 top-1/2 -translate-y-1/2 z-50 flex-col items-center rounded-l-lg shadow-xl overflow-hidden focus:outline-none bg-white border border-r-0 border-brand-500/30 min-w-[72px]" aria-label="Quick Cart">
    <div class="bg-brand-500 text-white p-2.5 px-3.5 flex flex-col items-center text-center w-full">
      <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      <span class="cart-count-text text-xs font-extrabold tracking-tight leading-none whitespace-nowrap">{{ ($cartCount ?? 0) }} {{ Str::plural('Item', ($cartCount ?? 0)) }}</span>
    </div>
    <div class="bg-white text-stone-800 p-2 px-3.5 text-xs font-extrabold w-full text-center whitespace-nowrap border-t border-stone-100">
      <span class="cart-total text-brand-600 font-extrabold">{{ money($cartSubtotal ?? 0) }}</span>
    </div>
  </button>

  <button type="button" id="backToTop" data-back-top class="fixed {{ request()->routeIs('product.show') ? 'hidden lg:flex bottom-6 right-6' : 'bottom-6 right-5 sm:right-6' }} z-40 h-10 w-10 sm:h-11 sm:w-11 rounded-full bg-stone-900/85 hover:bg-stone-950 text-white shadow-xl flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 backdrop-blur-xs border border-white/10 hover:scale-105 active:scale-95" aria-label="Back to top">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
  </button>

  <script src="{{ asset('theme/js/storefront.js') }}?v={{ filemtime(public_path('theme/js/storefront.js')) }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
