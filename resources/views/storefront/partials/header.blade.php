@php
  $promoText = trim((string) setting('header_promo_text', ''));
  $promoLink = trim((string) setting('header_promo_link', ''));
  $navCats = ($navCategories ?? collect());
  $navBrs = ($navBrands ?? collect());
  $topCats = $navCats->take(6);
  $moreCats = $navCats->skip(6);
  $dropdownCats = $moreCats->isNotEmpty() ? $moreCats : $navCats;
@endphp

@if($promoText !== '')
  <div class="bg-emerald-950 text-amber-300 text-xs sm:text-sm text-center py-2 px-4 font-bold tracking-wide flex items-center justify-center gap-2">
    <span class="inline-block animate-pulse">🌿</span>
    @if($promoLink !== '')
      <a href="{{ $promoLink }}" class="hover:underline">{!! strip_tags($promoText, '<b><strong><span>') !!}</a>
    @else
      {!! strip_tags($promoText, '<b><strong><span>') !!}
    @endif
  </div>
@endif

<header class="site-header sticky top-0 z-40 bg-emerald-800 shadow-md">
  {{-- ROW 1: Deep Emerald Header Row with White Logo & Crisp Search --}}
  <div class="bg-emerald-800 text-white border-b border-emerald-700/60 py-3 sm:py-3.5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-3 sm:gap-6">
      
      {{-- Brand Logo --}}
      <div class="flex items-center gap-3 shrink-0">
        <button type="button" data-open-menu class="lg:hidden text-white hover:bg-white/10 p-2 rounded-2xl transition cursor-pointer" aria-label="Open Menu">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        @include('partials.brand', ['light' => true])
      </div>

      {{-- Crisp Search Box (Center) --}}
      <form action="{{ route('shop') }}" method="GET" class="hidden md:flex flex-1 max-w-xl lg:max-w-2xl mx-auto">
        <div class="flex w-full h-11 sm:h-12 rounded-2xl bg-white shadow-md overflow-hidden p-1">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search raw honey, mustard oil, deshi ghee, chia seeds...') }}" class="flex-1 px-4 text-xs sm:text-sm font-semibold bg-transparent focus:outline-none text-stone-900 placeholder:text-stone-400" autocomplete="off" />
          <button type="submit" class="bg-amber-400 hover:bg-amber-500 text-stone-950 font-black text-xs px-6 rounded-xl flex items-center justify-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-2xs" aria-label="Search">
            <svg class="w-4 h-4 text-stone-950" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
            <span class="hidden sm:inline">Search</span>
          </button>
        </div>
      </form>

      {{-- Top Actions: Track Order, Account, Cart --}}
      <div class="ml-auto flex items-center gap-2 sm:gap-3 shrink-0">
        <button type="button" data-toggle-search class="md:hidden p-2 text-white hover:bg-white/10 rounded-2xl" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
        </button>

        {{-- Track Order Pill --}}
        <a href="{{ route('track') }}" class="hidden sm:inline-flex items-center gap-2 h-10 sm:h-11 px-3.5 rounded-2xl text-xs sm:text-sm font-extrabold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition-all duration-200 shadow-2xs group">
          <svg class="w-4 h-4 text-amber-300 group-hover:scale-110 transition-transform shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/>
          </svg>
          <span>Track Order</span>
        </a>

        {{-- My Account Dropdown --}}
        @include('storefront.partials.account-dropdown', ['lightHeader' => true])

        {{-- Cart Module Button --}}
        <button type="button" data-open-cart class="h-10 sm:h-11 relative flex items-center gap-2 px-4 rounded-2xl bg-amber-400 hover:bg-amber-500 text-stone-950 font-black text-xs sm:text-sm transition-all shadow-md group cursor-pointer" aria-label="Cart">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" class="group-hover:scale-110 transition-transform">
            <path d="M6 6h15l-1.5 9H7.5L6 6Zm0 0-.7-3H3"/>
            <circle cx="9" cy="20" r="1.3"/>
            <circle cx="17" cy="20" r="1.3"/>
          </svg>
          <span class="hidden sm:inline">Cart</span>
          <span data-cart-count class="cart-count h-5 min-w-5 px-1.5 rounded-full bg-stone-950 text-white text-xs font-black flex items-center justify-center shadow-2xs">
            {{ $cartCount }}
          </span>
        </button>
      </div>
    </div>
  </div>

  {{-- ROW 2: Soft Cream Category Navigation Bar --}}
  <div class="hidden lg:block bg-[#FAFAF5] text-stone-800 border-b border-emerald-100 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-11 text-xs sm:text-sm font-bold">
      <nav class="flex items-center justify-between h-full">
        
        {{-- Navigation Links --}}
        <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto whitespace-nowrap scrollbar-none py-1">
          <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-emerald-700 text-white shadow-xs' : 'text-stone-700 hover:text-emerald-800 hover:bg-emerald-100/60' }}">
            🏠 Home
          </a>

          <a href="{{ route('shop') }}" class="px-3 py-1.5 rounded-xl transition-all {{ request()->routeIs('shop') && ! request('flash') && ! request('brand') && ! isset($activeCategory) ? 'bg-emerald-700 text-white shadow-xs' : 'text-stone-700 hover:text-emerald-800 hover:bg-emerald-100/60' }}">
            🌿 All Products
          </a>

          <div class="h-4 w-px bg-emerald-200/80 mx-1"></div>

          @foreach($topCats as $cat)
            <a href="{{ route('shop.category', $cat) }}" class="px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ optional($activeCategory ?? null)->id === $cat->id ? 'bg-emerald-700 text-white shadow-xs' : 'text-stone-700 hover:text-emerald-800 hover:bg-emerald-100/60' }}">
              @if($cat->icon)<span class="text-sm">{{ $cat->icon }}</span>@endif
              <span>{{ $cat->name }}</span>
            </a>
          @endforeach
        </div>

        {{-- Right Actions: Brands & Deals --}}
        <div class="flex items-center gap-2 shrink-0">
          @if($navBrs->isNotEmpty())
            <div class="relative group/branddropdown" id="brandDropdownContainer">
              <button type="button" 
                      onclick="event.stopPropagation(); document.getElementById('brandDropdownMenu').classList.toggle('hidden');" 
                      class="px-3.5 py-1.5 rounded-xl transition-all text-xs font-bold text-stone-800 hover:text-emerald-800 hover:bg-emerald-100/60 border border-emerald-200/60 inline-flex items-center gap-1.5 cursor-pointer">
                <span>🏷️ Brands</span>
                <svg class="w-3.5 h-3.5 transition-transform group-hover/branddropdown:rotate-180 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
              </button>
              <div id="brandDropdownMenu" class="absolute right-0 top-full pt-1.5 hidden group-hover/branddropdown:block z-50 w-[380px]">
                <div class="bg-white text-stone-800 rounded-2xl shadow-2xl border border-stone-200 p-4 space-y-3">
                  <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">🌱 Organic Brands</span>
                    <a href="{{ route('shop') }}" class="text-xs font-bold text-emerald-600 hover:underline">View All &rarr;</a>
                  </div>
                  <div class="grid grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
                    @foreach($navBrs as $b)
                      <a href="{{ route('shop.brand', $b) }}" class="flex items-center gap-2.5 p-2 rounded-xl border border-stone-100 hover:border-emerald-500/40 hover:bg-emerald-50/60 transition-all group/item">
                        <img src="{{ $b->logoUrl() }}" class="h-7 w-7 object-contain rounded-md border border-stone-100 bg-white p-0.5 shrink-0" alt="{{ $b->name }}">
                        <div class="min-w-0 flex-1">
                          <span class="block text-xs font-bold text-stone-900 group-hover/item:text-emerald-700 truncate">{{ $b->name }}</span>
                          <span class="block text-[10px] text-stone-400">Organic</span>
                        </div>
                      </a>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif

          @if($hasFlashSale ?? true)
            <a href="{{ route('shop', ['flash' => 1]) }}" class="px-3.5 py-1.5 rounded-xl transition-all text-xs font-extrabold text-amber-900 bg-amber-200 hover:bg-amber-300 border border-amber-300 flex items-center gap-1.5 shadow-2xs">
              <span class="animate-pulse">⚡</span>
              <span>Deals</span>
            </a>
          @endif
        </div>

      </nav>
    </div>
  </div>

  {{-- Mobile Search Dropdown Panel --}}
  <div id="mobileSearchPanel" class="hidden bg-emerald-900 border-b border-emerald-700 py-3 md:hidden">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search raw honey, mustard oil, deshi ghee...') }}" class="flex-1 border border-emerald-700 rounded-xl px-4 py-2.5 text-sm text-stone-900 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-amber-400 text-stone-950 px-5 rounded-xl font-bold text-sm hover:bg-amber-500 transition">Search</button>
    </form>
  </div>
</header>
