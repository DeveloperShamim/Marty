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

@if($promoText !== '')
  <div class="bg-slate-900 text-slate-100 text-xs sm:text-sm text-center py-2 px-4 font-medium border-b border-slate-800">
    @if($promoLink !== '')
      <a href="{{ $promoLink }}" class="hover:text-brand-300 transition">{!! strip_tags($promoText, '<b><strong><span>') !!}</a>
    @else
      {!! strip_tags($promoText, '<b><strong><span>') !!}
    @endif
  </div>
@endif

<header class="site-header sticky top-0 z-40 bg-white">
  {{-- ROW 1: Logo + Modern Search + Actions --}}
  <div class="bg-white border-b border-stone-100 shadow-xs">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2.5 sm:py-3.5 flex items-center justify-between gap-2 sm:gap-6">
      
      {{-- Mobile Menu Toggle & Brand Logo --}}
      <div class="flex items-center gap-1.5 sm:gap-3 shrink-0 min-w-0">
        <button type="button" data-open-menu class="lg:hidden text-stone-700 hover:text-brand-600 p-1.5 rounded-xl hover:bg-stone-100 transition shrink-0 cursor-pointer" aria-label="Open Menu">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        @include('partials.brand')
      </div>

      {{-- Modern Search Bar --}}
      <form action="{{ route('shop') }}" method="GET" class="hidden md:flex flex-1 max-w-xl lg:max-w-2xl mx-auto px-2 lg:px-4">
        <div class="flex items-center w-full rounded-full border border-stone-200/90 bg-stone-50/80 hover:bg-white focus-within:bg-white focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 transition-all duration-200 pl-4 pr-1.5 py-1 shadow-2xs">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search shoes, watches, leather belts, wallets...') }}" class="flex-1 text-xs sm:text-sm font-medium bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400" autocomplete="off" />
          <button type="submit" class="h-8 w-8 rounded-full bg-brand-500 hover:bg-brand-600 text-white flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 shadow-xs shrink-0" aria-label="Search">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
          </button>
        </div>
      </form>

      {{-- Top Actions: Track Order, Account, Cart --}}
      <div class="ml-auto flex items-center gap-1 sm:gap-6 shrink-0">
        {{-- Mobile Search Trigger --}}
        <button type="button" data-toggle-search class="md:hidden flex flex-col items-center justify-center text-center group cursor-pointer focus:outline-none py-0.5 px-1 min-w-[38px] text-stone-700 hover:text-brand-600 transition-colors" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/>
          </svg>
          <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Search</span>
        </button>

        {{-- 1. Track Order (Desktop & Tablet) --}}
        <a href="{{ route('track') }}" class="hidden sm:flex flex-col items-center justify-center text-center group cursor-pointer py-0.5 px-1 min-w-[48px] text-stone-700 hover:text-brand-600 transition-colors" aria-label="Track Order">
          <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Track Order</span>
        </a>

        {{-- 2. My Account Dropdown --}}
        @include('storefront.partials.account-dropdown', ['lightHeader' => false])

        {{-- 3. Cart Button --}}
        <button type="button" data-open-cart class="flex flex-col items-center justify-center text-center group cursor-pointer focus:outline-none py-0.5 px-1 min-w-[42px] text-stone-700 hover:text-brand-600 transition-colors" aria-label="Cart">
          <div class="relative inline-flex items-center justify-center">
            <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"/>
              <circle cx="20" cy="21" r="1"/>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span data-cart-count class="cart-count absolute -top-1.5 -right-2.5 bg-brand-500 text-white text-[10px] font-black h-4 min-w-4 px-1 rounded-full flex items-center justify-center shadow-xs leading-none">
              {{ $cartCount }}
            </span>
          </div>
          <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Cart</span>
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
  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3 shadow-md md:hidden">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto px-4 flex items-center gap-2">
      <div class="flex items-center flex-1 rounded-full border border-stone-200 bg-stone-50 pl-4 pr-1 py-1 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search products...') }}" class="flex-1 text-xs sm:text-sm font-medium bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400" data-mobile-search-input autocomplete="off" />
        <button type="submit" class="h-8 w-8 rounded-full bg-brand-500 hover:bg-brand-600 text-white flex items-center justify-center transition shadow-xs shrink-0" aria-label="Search">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
        </button>
      </div>
    </form>
  </div>
</header>
