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
  <div class="bg-brand-900 text-amber-300 text-xs sm:text-sm text-center py-2 px-4 font-bold tracking-wide flex items-center justify-center gap-2">
    <span class="inline-block animate-pulse">🌿</span>
    @if($promoLink !== '')
      <a href="{{ $promoLink }}" class="hover:underline">{!! strip_tags($promoText, '<b><strong><span>') !!}</a>
    @else
      {!! strip_tags($promoText, '<b><strong><span>') !!}
    @endif
  </div>
@endif

<header class="site-header sticky top-0 z-40 bg-white shadow-sm border-b border-brand-100">
  {{-- ROW 1: Clean Minimalist White Header --}}
  <div class="bg-white border-b border-stone-100 py-3.5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-3 sm:gap-6">
      
      {{-- Brand Logo --}}
      <div class="flex items-center gap-3 shrink-0">
        <button type="button" data-open-menu class="lg:hidden text-stone-700 hover:text-brand-700 p-2 rounded-2xl hover:bg-brand-50 transition cursor-pointer" aria-label="Open Menu">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        @include('partials.brand', ['light' => false])
      </div>

      {{-- Wide Pill Search Bar (Center) --}}
      <form action="{{ route('shop') }}" method="GET" class="hidden md:flex flex-1 min-w-[280px] lg:min-w-[440px] max-w-2xl mx-2 lg:mx-6">
        <div class="relative flex items-center w-full h-11 rounded-xl sm:rounded-2xl border border-stone-200 bg-white hover:border-brand-400 focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-600/10 transition-all duration-200 shadow-2xs overflow-hidden">
          <div class="pl-3.5 pr-1.5 text-stone-400 shrink-0">
            <svg class="w-4 h-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
          </div>
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search raw honey, mustard oil, deshi ghee, chia seeds...') }}" class="flex-1 px-2 text-xs sm:text-sm font-semibold bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400 min-w-0" autocomplete="off" />
          <button type="submit" class="h-full bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm px-5 lg:px-6 flex items-center justify-center gap-1.5 transition-colors shrink-0 cursor-pointer" aria-label="Search">
            <span>Search</span>
          </button>
        </div>
      </form>

      {{-- Top Actions: Track Order, Account, Cart --}}
      <div class="ml-auto flex items-center gap-2 sm:gap-3 shrink-0">
        <button type="button" data-toggle-search class="md:hidden p-2 text-stone-700 hover:text-brand-600 hover:bg-brand-50 rounded-2xl" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
        </button>

        {{-- Track Order Pill --}}
        <a href="{{ route('track') }}" class="hidden sm:inline-flex items-center gap-2 h-10 sm:h-11 px-3.5 rounded-2xl text-xs sm:text-sm font-extrabold text-stone-700 bg-stone-50 hover:bg-stone-100 hover:border-stone-300 border border-stone-200/80 transition-all duration-200 shadow-2xs group">
          <svg class="w-4 h-4 text-brand-600 group-hover:scale-110 transition-transform shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/>
          </svg>
          <span>Track Order</span>
        </a>

        {{-- My Account Dropdown --}}
        @include('storefront.partials.account-dropdown', ['lightHeader' => false])

        {{-- Cart Button --}}
        <button type="button" data-open-cart class="h-10 sm:h-11 relative flex items-center gap-2 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm transition-all shadow-xs group cursor-pointer" aria-label="Cart">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="group-hover:scale-110 transition-transform">
            <path d="M6 6h15l-1.5 9H7.5L6 6Zm0 0-.7-3H3"/>
            <circle cx="9" cy="20" r="1.3"/>
            <circle cx="17" cy="20" r="1.3"/>
          </svg>
          <span class="hidden sm:inline">Cart</span>
          <span data-cart-count class="cart-count h-5 min-w-5 px-1.5 rounded-full bg-white text-brand-800 text-xs font-black flex items-center justify-center shadow-2xs">
            {{ $cartCount }}
          </span>
        </button>
      </div>
    </div>
  </div>

  {{-- ROW 2: Clean White Navigation Bar with Browse Categories Button --}}
  <div class="hidden lg:block bg-white border-t border-stone-100 border-b border-stone-200/80 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-12 text-xs sm:text-sm font-semibold">
      <div class="flex items-center justify-between h-full gap-4">
        
        {{-- Left: Browse Categories Dropdown Button --}}
        <div class="relative group/catdropdown shrink-0">
          <button type="button" 
                  onclick="event.stopPropagation(); document.getElementById('catDropdownMenu').classList.toggle('hidden');" 
                  class="bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs px-4 py-2 rounded-xl flex items-center gap-2 shadow-xs transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Browse Categories</span>
            <svg class="w-3.5 h-3.5 transition-transform group-hover/catdropdown:rotate-180 text-brand-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
          </button>
          
          {{-- Categories Dropdown Menu --}}
          <div id="catDropdownMenu" class="absolute left-0 top-full pt-1.5 hidden group-hover/catdropdown:block z-50 w-72">
            <div class="bg-white rounded-2xl shadow-2xl border border-stone-200 p-2 space-y-1">
              <a href="{{ route('shop') }}" class="flex items-center justify-between gap-2 px-3 py-2 text-xs font-extrabold text-brand-700 hover:bg-brand-50 rounded-xl transition-colors">
                <span>🌿 View All Categories</span>
                <span>&rarr;</span>
              </a>
              <div class="h-px bg-stone-100 my-1"></div>
              @foreach($navCats as $cat)
                <a href="{{ route('shop.category', $cat) }}" class="flex items-center justify-between gap-2 px-3.5 py-2.5 text-xs font-bold text-stone-700 hover:text-brand-700 hover:bg-brand-50 rounded-xl transition-colors">
                  <span class="flex items-center gap-2">
                    @if($cat->icon)<span class="text-sm">{{ $cat->icon }}</span>@endif
                    <span>{{ $cat->name }}</span>
                  </span>
                  @if(isset($cat->products_count) && $cat->products_count > 0)
                    <span class="text-[10px] text-stone-400 bg-stone-100 px-1.5 py-0.5 rounded-full font-mono">{{ $cat->products_count }}</span>
                  @endif
                </a>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Center: Clean Category Links (Max 6) --}}
        <nav class="flex items-center gap-1 sm:gap-2 overflow-x-auto whitespace-nowrap scrollbar-none py-1">
          <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-xl transition-all font-bold {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-700 border border-brand-200/60' : 'text-stone-700 hover:text-brand-700 hover:bg-stone-50' }}">
            Home
          </a>

          @foreach($topCats->take(6) as $cat)
            <a href="{{ route('shop.category', $cat) }}" class="px-3 py-1.5 rounded-xl transition-all font-bold flex items-center gap-1.5 {{ optional($activeCategory ?? null)->id === $cat->id ? 'bg-brand-50 text-brand-700 border border-brand-200/60' : 'text-stone-700 hover:text-brand-700 hover:bg-stone-50' }}">
              @if($cat->icon)<span class="text-sm">{{ $cat->icon }}</span>@endif
              <span>{{ $cat->name }}</span>
            </a>
          @endforeach
        </nav>

        {{-- Right: Deals --}}
        <div class="flex items-center gap-2 shrink-0">
          @if($hasFlashSale ?? true)
            <a href="{{ route('shop', ['flash' => 1]) }}" class="px-3.5 py-1.5 rounded-xl transition-all text-xs font-extrabold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 flex items-center gap-1.5 shadow-2xs">
              <span class="animate-pulse">⚡</span>
              <span>Deals</span>
            </a>
          @endif
        </div>

      </div>
    </div>
  </div>

  {{-- Mobile Search Dropdown Panel --}}
  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3 md:hidden">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search raw honey, mustard oil, deshi ghee...') }}" class="flex-1 border border-stone-200 rounded-xl px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-600/30" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-brand-600 text-white px-5 rounded-xl font-semibold text-sm hover:bg-brand-700 transition">Search</button>
    </form>
  </div>
</header>
