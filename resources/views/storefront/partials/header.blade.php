@php
  $promoText = trim((string) setting('header_promo_text', ''));
  $promoLink = trim((string) setting('header_promo_link', ''));
  $navCats = ($navCategories ?? collect());
  $navBrs = ($navBrands ?? collect());

  // Check if categories are short 1-word names vs multi-word names
  $firstSeven = $navCats->take(7);
  $avgWords = $firstSeven->isNotEmpty() 
      ? $firstSeven->avg(fn($c) => count(preg_split('/\s+/', trim($c->name)))) 
      : 1;
  $avgLength = $firstSeven->isNotEmpty() 
      ? $firstSeven->avg(fn($c) => mb_strlen(trim($c->name))) 
      : 10;

  // If short 1-word categories (avg words <= 1.5 and avg length <= 14), show 6 to 7 categories!
  // If multi-word categories, show 4 categories.
  if ($avgWords <= 1.5 && $avgLength <= 14) {
      $visibleCount = min(7, $navCats->count() >= 7 ? 7 : 6);
  } else {
      $visibleCount = 4;
  }

  $visibleCount = max(4, min(7, $visibleCount));

  $topCats = $navCats->take($visibleCount);
  $moreCats = $navCats->skip($visibleCount);
  $dropdownCats = $moreCats->isNotEmpty() ? $moreCats : $navCats;
@endphp

@php
  // Top news ticker (Admin → Store Settings → Homepage → Top News Ticker): one headline per line ("||" also works).
  $promoMessages = collect(preg_split('/\R|\|\|/', $promoText))->map(fn ($m) => trim(strip_tags($m, '<b><strong><span>')))->filter()->values();

  // Live coupon codes mentioned in a headline become tap-to-copy.
  if ($promoMessages->isNotEmpty()) {
      $liveCodes = \App\Models\Coupon::where('is_active', true)->get()
          ->filter(fn ($c) => $c->isCurrentlyActive())->pluck('code')
          ->filter(fn ($code) => preg_match('/^[A-Z0-9_-]{3,}$/', $code))->values();
      if ($liveCodes->isNotEmpty()) {
          $codeRe = '/(?<![\w-])(' . $liveCodes->map(fn ($c) => preg_quote($c, '/'))->implode('|') . ')(?![\w-])(?![^<]*>)/i';
          $promoMessages = $promoMessages->map(fn ($m) => preg_replace_callback($codeRe, fn ($hit) =>
              '<span role="button" tabindex="0" data-copy-code="' . e(strtoupper($hit[1])) . '" class="promo-code" title="Tap to copy">' . e($hit[1]) . '</span>', $m));
      }
  }

  // Flash sale countdown headline, while a flash sale with an end time is running.
  $flashEnds = null;
  if (($hasFlashSale ?? false) && setting('ticker_show_countdown', '1') === '1' && ($rawEnd = setting('flash_sale_ends_at'))) {
      try { $flashEnds = \Illuminate\Support\Carbon::parse($rawEnd); } catch (\Throwable) { $flashEnds = null; }
      if ($flashEnds && $flashEnds->isPast()) $flashEnds = null;
  }
  // Each headline carries the promo link itself, so the countdown can link to the sale.
  $tickerItems = $promoLink === '' ? $promoMessages
      : $promoMessages->map(fn ($m) => '<a href="' . e($promoLink) . '">' . $m . '</a>');
  if ($flashEnds) {
      $tickerItems = collect([
          '<a href="' . e(route('shop', ['flash' => 1])) . '" class="ticker-flash" data-countdown-end="' . $flashEnds->toIso8601String() . '" data-countdown-hide>'
          . '&#9889; Flash Sale ends in '
          . '<span class="ticker-clock"><span data-d-wrap><span data-d>00</span>d </span><span data-h>00</span>:<span data-m>00</span>:<span data-s>00</span></span></a>',
      ])->merge($tickerItems);
  }
@endphp

@if($tickerItems->isNotEmpty())
  @php
    // Each of the two copies must be wider than the widest screen, so short lists are repeated.
    $tickerChars = max(1, mb_strlen(strip_tags($tickerItems->implode(' '))) + 6 * $tickerItems->count());
    $promoLoop = collect(array_fill(0, max(1, (int) ceil(280 / $tickerChars)), $tickerItems))->flatten();
    $tickerLabel = trim((string) setting('ticker_label', 'Hot Deals'));
    $labelClass = match (setting('ticker_label_style', 'dark')) {
        'red'   => 'bg-red-600 text-white',
        'white' => 'bg-white text-ink',
        default => 'bg-ink text-white',
    };
    $promoSeconds = max(14, (int) round(mb_strlen(strip_tags($promoLoop->implode(' '))) * 0.16));
  @endphp
  {{-- News-ticker bar (fixed label + scrolling headlines), all screen sizes --}}
  <div class="promo-marquee flex items-stretch h-9 sm:h-10 bg-gradient-to-r from-brand-700 via-brand-600 to-brand-700 text-white overflow-hidden">
    @if($tickerLabel !== '')
      <span class="ticker-label relative z-10 shrink-0 flex items-center gap-1.5 pl-3 sm:pl-5 pr-5 sm:pr-7 {{ $labelClass }} text-[11px] sm:text-xs font-extrabold uppercase tracking-wider">
        <span class="ticker-dot h-2 w-2 rounded-full {{ setting('ticker_label_style', 'dark') === 'red' ? 'bg-white' : 'bg-red-500' }}"></span>{{ $tickerLabel }}
      </span>
    @endif
    <span class="ticker-window relative flex-1 min-w-0 flex items-center overflow-hidden">
      <span class="promo-track flex w-max" style="--promo-dur: {{ $promoSeconds }}s">
        @foreach([1, 2] as $copy)
          <span class="flex shrink-0 items-center" @if($copy === 2) aria-hidden="true" @endif>
            @foreach($promoLoop as $msg)
              <span class="text-[13px] sm:text-sm font-bold whitespace-nowrap">{!! $msg !!}</span>
              <span class="mx-4 sm:mx-6 text-[9px] text-white/70" aria-hidden="true">&#9670;</span>
            @endforeach
          </span>
        @endforeach
      </span>
    </span>
  </div>
@endif

<header class="site-header sticky top-0 z-40 bg-white">
  {{-- ROW 1: Logo + Modern Search + Actions --}}
  <div class="bg-white/95 backdrop-blur-md border-b border-stone-200/80 shadow-2xs">
    <div class="max-w-7xl mx-auto pl-1.5 pr-2.5 sm:px-6 py-1.5 sm:py-3 flex items-center justify-between gap-1 sm:gap-6">

      {{-- Phones: menu + search on the left --}}
      <div class="flex items-center shrink-0 lg:hidden">
        <button type="button" data-open-menu class="text-ink hover:text-brand-600 w-9 h-9 min-[390px]:w-10 min-[390px]:h-10 sm:w-11 sm:h-11 flex items-center justify-center rounded-xl hover:bg-stone-100 active:scale-95 transition-all shrink-0 cursor-pointer" aria-label="Open Menu">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M3.5 6h17M6.5 12h14M3.5 18h17"/></svg>
        </button>
        <button type="button" data-toggle-search class="md:hidden flex items-center justify-center w-9 h-9 min-[390px]:w-10 min-[390px]:h-10 rounded-xl text-ink hover:text-brand-600 hover:bg-stone-100 active:scale-95 transition-all cursor-pointer focus:outline-none" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </button>
      </div>

      {{-- Brand logo: centred between the icons on phones --}}
      <div class="flex-1 md:flex-none flex justify-center md:justify-start min-w-0">
        @include('partials.brand', ['compactMobile' => true, 'logoClass' => 'max-[429px]:max-w-[112px] max-[389px]:max-w-[88px] max-[359px]:max-w-[80px] max-[429px]:h-7'])
      </div>

      {{-- Modern Search Bar --}}
      <form action="{{ route('shop') }}" method="GET" class="hidden md:flex flex-1 max-w-md lg:max-w-lg mx-auto px-2 lg:px-4">
        <div class="flex items-center w-full rounded-lg border border-brand-600 bg-stone-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-brand-500/15 transition-all duration-200 p-[3px] pl-3.5">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Type your product...') }}" class="flex-1 min-w-0 text-[13px] font-medium bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400" autocomplete="off" />
          <button type="submit" class="h-8 px-3.5 rounded-md bg-brand-600 hover:bg-brand-700 text-white text-[13px] font-semibold flex items-center justify-center gap-1.5 transition-colors active:scale-95 shrink-0 cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <span>Search</span>
          </button>
        </div>
      </form>

      {{-- Top Actions: Track Order, Account, Cart --}}
      <div class="ml-auto flex items-center gap-0.5 sm:gap-4 lg:gap-6 shrink-0">
        {{-- Flash sale (phones) --}}
        @if($hasFlashSale ?? false)
          <a href="{{ route('shop', ['flash' => 1]) }}" class="flash-pill sm:hidden mr-2 min-[390px]:mr-3.5 inline-flex items-center h-8 px-2 min-[390px]:px-2.5 rounded-lg text-white text-[10px] font-extrabold uppercase tracking-wider whitespace-nowrap">
            <span class="max-[359px]:hidden">Flash&nbsp;</span>Sale
          </a>
        @endif

        {{-- 1. Track Order (Desktop & Tablet) --}}
        <a href="{{ route('track') }}" class="hidden sm:flex flex-col items-center justify-center text-center group cursor-pointer py-0.5 px-1 min-w-[48px] text-stone-700 hover:text-brand-600 transition-colors" aria-label="Track Order">
          <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Track Order</span>
        </a>

        {{-- 2. My Account Dropdown (last on phones) --}}
        <div class="order-last sm:order-none">
          @include('storefront.partials.account-dropdown', ['lightHeader' => false])
        </div>

        {{-- 3. Cart Button --}}
        <button type="button" data-open-cart class="flex flex-col sm:flex-col items-center justify-center text-center group cursor-pointer focus:outline-none w-9 h-9 min-[390px]:w-10 min-[390px]:h-10 sm:w-auto sm:h-auto sm:min-w-[44px] rounded-xl sm:rounded-none hover:bg-stone-100 sm:hover:bg-transparent active:scale-95 transition-all text-stone-700 hover:text-brand-600" aria-label="Cart">
          <div class="relative inline-flex items-center justify-center">
            <svg class="sm:hidden w-[26px] h-[26px] text-ink group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 10h18l-1.6 8.4a2 2 0 0 1-2 1.6H6.6a2 2 0 0 1-2-1.6L3 10Z"/><path d="m8 10 3-6M16 10l-3-6M9 13.5v3M12 13.5v3M15 13.5v3"/>
            </svg>
            <svg class="hidden sm:block w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/>
              <circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span data-cart-count class="cart-count absolute -top-2 -left-2.5 sm:left-auto sm:-top-1.5 sm:-right-2 bg-ink sm:bg-brand-500 text-white text-[11px] sm:text-[10px] font-black h-5 min-w-5 sm:h-[18px] sm:min-w-[18px] px-1 rounded-full flex items-center justify-center shadow-xs ring-2 ring-white leading-none {{ $cartCount ? '' : 'hidden' }}">
              {{ $cartCount }}
            </span>
          </div>
          <span class="hidden sm:block text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Cart</span>
        </button>
      </div>
    </div>
  </div>

  {{-- ROW 2: Secondary Navigation Bar --}}
  <div class="hidden lg:block bg-white border-b border-stone-200/80 text-stone-700 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-5 h-11 text-sm font-semibold">
      <nav class="flex items-center justify-between h-full py-1">
        {{-- 1. Home --}}
        <a href="{{ route('home') }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }}">Home</a>
        
        {{-- 2. All Products --}}
        <a href="{{ route('shop') }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ request()->routeIs('shop') && ! request('flash') && ! request('brand') && ! isset($activeCategory) ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }}">All Products</a>

        {{-- 3. Dynamic Categories (4 to 7 items based on character/word length) --}}
        @foreach($topCats as $cat)
          <a href="{{ route('shop.category', $cat) }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ optional($activeCategory ?? null)->id === $cat->id ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }}">{{ $cat->name }}</a>
        @endforeach

        {{-- 4. More Categories Dropdown (if remaining categories exist) --}}
        @if($moreCats->isNotEmpty())
          <div class="relative group/catdropdown" id="catDropdownContainer">
            <button type="button" 
                    onclick="event.stopPropagation(); document.getElementById('catDropdownMenu').classList.toggle('hidden');" 
                    class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ isset($activeCategory) && ! $topCats->pluck('id')->contains($activeCategory->id) ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }} inline-flex items-center gap-1 cursor-pointer">
              <span>More</span>
              <svg class="w-3.5 h-3.5 transition-transform group-hover/catdropdown:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>
            <div id="catDropdownMenu" class="absolute left-0 top-full pt-1.5 hidden group-hover/catdropdown:block z-50 min-w-[260px] max-w-sm">
              <div class="bg-white rounded-2xl shadow-2xl border border-stone-200 p-2 space-y-1 max-h-80 overflow-y-auto">
                <a href="{{ route('shop') }}" class="flex items-center justify-between gap-2 px-3 py-2 text-xs font-bold text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                  <span>Browse All Categories</span>
                  <span class="text-xs">&rarr;</span>
                </a>
                <div class="h-px bg-stone-100 my-1"></div>
                @foreach($dropdownCats as $cat)
                  <a href="{{ route('shop.category', $cat) }}" class="flex items-center justify-between gap-2 px-3 py-2 text-xs font-semibold text-stone-700 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                    <span class="truncate">@if($cat->icon)<span class="mr-1.5">{{ $cat->icon }}</span>@endif{{ $cat->name }}</span>
                    @if(isset($cat->products_count) && $cat->products_count > 0)
                      <span class="text-[10px] text-stone-400 bg-stone-100 px-1.5 py-0.5 rounded-full font-mono">{{ $cat->products_count }}</span>
                    @endif
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        {{-- 5. Brands Dropdown --}}
        @if($navBrs->isNotEmpty())
          <div class="relative group/branddropdown" id="brandDropdownContainer">
            <button type="button" 
                    onclick="event.stopPropagation(); document.getElementById('brandDropdownMenu').classList.toggle('hidden');" 
                    class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ request()->routeIs('shop.brand') || request('brand') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }} inline-flex items-center gap-1 cursor-pointer">
              <span class="inline-flex items-center gap-1">
                <span>Brands</span>
                <span class="h-2 w-2 rounded-full bg-brand-500 animate-pulse"></span>
              </span>
              <svg class="w-3.5 h-3.5 transition-transform group-hover/branddropdown:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>
            <div id="brandDropdownMenu" class="absolute left-0 top-full pt-1.5 hidden group-hover/branddropdown:block z-50 w-[420px]">
              <div class="bg-white rounded-2xl shadow-2xl border border-stone-200 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                  <span class="text-xs font-extrabold uppercase tracking-wider text-stone-400">Official Brands</span>
                  <a href="{{ route('shop') }}" class="text-xs font-semibold text-brand-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="grid grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-1">
                  @foreach($navBrs as $b)
                    <a href="{{ route('shop.brand', $b) }}" class="flex items-center gap-2.5 p-2 rounded-xl border border-stone-100 hover:border-brand-500/40 hover:bg-brand-50/50 transition-all group/item">
                      <img src="{{ $b->logoUrl() }}" class="h-7 w-7 object-contain rounded-md border border-stone-100 bg-white p-0.5 shrink-0" alt="{{ $b->name }}">
                      <div class="min-w-0 flex-1">
                        <span class="block text-xs font-bold text-ink group-hover/item:text-brand-600 truncate">{{ $b->name }}</span>
                        @if(isset($b->products_count) && $b->products_count > 0)
                          <span class="block text-[10px] text-stone-400">{{ $b->products_count }} {{ Str::plural('item', $b->products_count) }}</span>
                        @else
                          <span class="block text-[10px] text-stone-400">Authentic Brand</span>
                        @endif
                      </div>
                    </a>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif

        {{-- 6. Deals --}}
        @if($hasFlashSale ?? true)
          <a href="{{ route('shop', ['flash' => 1]) }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors text-amber-700 bg-amber-50 font-bold hover:bg-amber-100 flex items-center gap-1 shrink-0">
            <span>⚡ Deals</span>
          </a>
        @endif
      </nav>
    </div>
  </div>

  {{-- Mobile Search Dropdown Panel --}}
  <div id="mobileSearchPanel" class="hidden bg-white/95 backdrop-blur-md border-b border-stone-200/80 px-3.5 py-2.5 shadow-md md:hidden transition-all">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto flex items-center gap-2">
      <div class="flex items-center flex-1 rounded-lg border border-brand-600 bg-stone-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-brand-500/15 p-[3px] pl-3 transition-all">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Type your product...') }}" class="flex-1 min-w-0 text-base sm:text-sm font-medium bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400" data-mobile-search-input autocomplete="off" />
        <button type="submit" class="h-8 px-3 rounded-md bg-brand-600 hover:bg-brand-700 text-white text-[13px] font-semibold flex items-center justify-center gap-1.5 transition-colors active:scale-95 shrink-0 cursor-pointer">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          <span>Search</span>
        </button>
      </div>
    </form>
  </div>
</header>
