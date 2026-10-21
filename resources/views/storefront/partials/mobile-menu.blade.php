@php
  $menuPhone = trim((string) setting('contact_phone', ''));
  $menuWa = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: $menuPhone));
  if (str_starts_with($menuWa, '0')) {
      $menuWa = '88' . $menuWa;
  } elseif ($menuWa !== '' && !str_starts_with($menuWa, '880') && strlen($menuWa) === 10) {
      $menuWa = '880' . $menuWa;
  }
  $quickLinks = array_values(array_filter([
    ['Shop all', route('shop')],
    ['New in', route('shop', ['new' => 1])],
    ($hasFlashSale ?? false) ? ['Flash deals', route('shop', ['flash' => 1])] : ['Top selling', route('shop', ['featured' => 1])],
    ['Track order', route('track')],
  ]));
  $menuBrands = isset($navBrands) ? $navBrands->filter(fn ($b) => ($b->products_count ?? 1) > 0) : collect();
  // Other links from Admin → Homepage → Menu bar. Categories, brands, Home, All Products and Track order are already in the lists.
  $menuLinks = collect(\App\Support\HeaderMenu::resolve($navCategories ?? collect(), $navBrands ?? collect(), $activeCategory ?? null, $hasFlashSale ?? false))
      ->filter(fn ($e) => $e['kind'] === 'link' && ! in_array($e['type'] ?? '', ['home', 'shop', 'category', 'brand', 'track', 'flash', 'new', 'best', 'featured'], true))
      ->values();
  $row = 'flex items-center gap-3 py-3.5 text-[15px] text-stone-800 transition hover:text-brand-600';
  $label = 'mm-item mt-6 mb-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400';
  $chev = '<svg class="mm-chev h-4 w-4 shrink-0 text-stone-300 transition" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>';
  $d = 0;
@endphp
<style>
  .mobile-menu .mm-item{opacity:0;transform:translateX(-14px);transition:opacity .35s ease,transform .45s cubic-bezier(.2,.8,.2,1);transition-delay:0s}
  .mobile-menu:not(.-translate-x-full) .mm-item{opacity:1;transform:none;transition-delay:calc(90ms + var(--d,0) * 30ms)}
  @media (prefers-reduced-motion:reduce){.mobile-menu .mm-item{opacity:1;transform:none;transition:none}}
  .mobile-menu .mm-list > * + *{border-top:1px solid #f0eeec}
  .mobile-menu .mm-list details[open] > summary .mm-chev{transform:rotate(90deg)}
</style>
<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-[60] flex w-[88vw] max-w-[380px] -translate-x-full flex-col overflow-hidden bg-white shadow-2xl lg:hidden transition-transform duration-[450ms] ease-[cubic-bezier(.2,.8,.2,1)] overscroll-contain" aria-label="Menu">
  {{-- Header: shop name, close, and a light sign-in line --}}
  <div class="shrink-0 border-b border-stone-100 px-5 pt-4 pb-4">
    <div class="flex items-center justify-between">
      <span class="text-sm font-bold tracking-wide text-stone-900">{{ site_name() }}</span>
      <button type="button" data-close-menu class="grid h-9 w-9 place-items-center rounded-full text-stone-500 hover:bg-stone-100 hover:text-stone-900 transition cursor-pointer" aria-label="Close menu">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    @auth
      <a href="{{ route('account') }}" class="mm-item mt-2 flex items-center gap-2 text-sm text-stone-600 hover:text-brand-600" style="--d:{{ $d++ }}">
        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-stone-100 text-xs font-semibold text-stone-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
        <span class="min-w-0 flex-1 truncate">Hi, {{ auth()->user()->name }}</span>
        <span class="text-xs text-brand-600">My account</span>
      </a>
    @else
      <div class="mm-item mt-2" style="--d:{{ $d++ }}">
        <p class="text-sm text-stone-500">
          <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Sign in</a>
          <span class="mx-1 text-stone-300">·</span>
          <a href="{{ route('register') }}" class="font-semibold text-stone-800 hover:underline">Create account</a>
        </p>
        @if(google_login_enabled())
          <div class="mt-3">@include('partials.google-button', ['href' => route('auth.google'), 'class' => 'h-10 px-3 text-sm text-stone-700 ring-stone-200'])</div>
        @endif
      </div>
    @endauth
  </div>

  <nav class="flex-1 overflow-y-auto overscroll-contain px-5 pb-8">
    {{-- Categories: a plain list; a category with sub-categories has an arrow and opens them --}}
    @if(isset($navCategories) && $navCategories->isNotEmpty())
      <p class="{{ $label }}" style="--d:{{ $d++ }}">Categories</p>
      <div class="mm-list" data-mobile-categories>
        @foreach($navCategories as $cat)
          @php $subs = $cat->relationLoaded('children') ? $cat->children : collect(); @endphp
          @if($subs->isEmpty())
            <a href="{{ route('shop.category', $cat) }}" class="mm-item {{ $row }}" style="--d:{{ $d++ }}"><span class="min-w-0 flex-1 truncate">{{ $cat->name }}</span></a>
          @else
            <details class="mm-item group" style="--d:{{ $d++ }}" data-mobile-subcats>
              <summary class="{{ $row }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                <span class="min-w-0 flex-1 truncate">{{ $cat->name }}</span>{!! $chev !!}
              </summary>
              <div class="pb-2 pl-4">
                <a href="{{ route('shop.category', $cat) }}" class="block py-2 text-sm text-brand-600">All {{ $cat->name }}</a>
                @foreach($subs as $sub)
                  <a href="{{ route('shop.category', $sub) }}" class="block truncate py-2 text-sm text-stone-600 hover:text-brand-600">{{ $sub->name }}</a>
                @endforeach
              </div>
            </details>
          @endif
        @endforeach

        {{-- Brands: closed by default; tap to open the full list --}}
        @if($menuBrands->isNotEmpty())
          <details class="mm-item group" style="--d:{{ $d++ }}" data-mobile-brands>
            <summary class="{{ $row }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
              <span class="min-w-0 flex-1 truncate">Brands</span>{!! $chev !!}
            </summary>
            <div class="pb-2 pl-4">
              @foreach($menuBrands as $b)
                <a href="{{ route('shop.brand', $b) }}" class="block truncate py-2 text-sm text-stone-600 hover:text-brand-600">{{ $b->name }}</a>
              @endforeach
            </div>
          </details>
        @endif
      </div>
    @endif

    {{-- Shop: quick links and the other menu-bar links, in the same plain list --}}
    <p class="{{ $label }}" style="--d:{{ $d++ }}">Shop</p>
    <div class="mm-list" data-mobile-menu-links>
      @foreach($quickLinks as [$text, $href])
        <a href="{{ $href }}" class="mm-item {{ $row }}" style="--d:{{ $d++ }}"><span class="min-w-0 flex-1 truncate">{{ $text }}</span></a>
      @endforeach
      @foreach($menuLinks as $e)
        @if(empty($e['children']))
          <a href="{{ $e['url'] }}" @if($e['external'] ?? false) target="_blank" rel="noopener" @endif class="mm-item {{ $row }}" style="--d:{{ $d++ }}"><span class="min-w-0 flex-1 truncate {{ $e['active'] ? 'text-brand-600' : '' }}">{{ $e['label'] }}</span></a>
        @else
          <details class="mm-item group" style="--d:{{ $d++ }}">
            <summary class="{{ $row }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
              <span class="min-w-0 flex-1 truncate">{{ $e['label'] }}</span>{!! $chev !!}
            </summary>
            <div class="pb-2 pl-4">
              @if(! collect($e['children'])->contains('url', $e['url']))
                <a href="{{ $e['url'] }}" class="block py-2 text-sm text-brand-600">Open {{ $e['label'] }}</a>
              @endif
              @foreach($e['children'] as $child)
                <a href="{{ $child['url'] }}" class="block truncate py-2 text-sm text-stone-600 hover:text-brand-600">{{ $child['label'] }}</a>
              @endforeach
            </div>
          </details>
        @endif
      @endforeach
    </div>

    {{-- Help: one quiet line --}}
    @if($menuPhone !== '' || $menuWa !== '')
      <p class="mm-item mt-8 text-sm text-stone-500" style="--d:{{ $d++ }}">
        Need help?
        @if($menuPhone !== '')<a href="tel:{{ preg_replace('/\s+/', '', $menuPhone) }}" class="ml-1 font-semibold text-stone-800 hover:text-brand-600">Call</a>@endif
        @if($menuPhone !== '' && $menuWa !== '')<span class="mx-1 text-stone-300">·</span>@endif
        @if($menuWa !== '')<a href="https://wa.me/{{ $menuWa }}" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:underline">WhatsApp</a>@endif
      </p>
    @else
      <p class="mm-item mt-8 text-sm text-stone-500" style="--d:{{ $d++ }}">Need help? <a href="{{ route('contact') }}" class="font-semibold text-stone-800 hover:text-brand-600">Contact us</a></p>
    @endif

    @auth
      <form method="POST" action="{{ route('logout') }}" class="mm-item mt-3" style="--d:{{ $d++ }}">
        @csrf
        <button type="submit" class="text-sm text-stone-400 hover:text-rose-600 transition cursor-pointer">Log out</button>
      </form>
    @endauth
  </nav>
</aside>
