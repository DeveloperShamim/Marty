@php
  $promoText = trim((string) setting('header_promo_text', ''));
  $promoLink = trim((string) setting('header_promo_link', ''));
  $navCats = ($navCategories ?? collect());
  $navBrs = ($navBrands ?? collect());
  $topCats = $navCats->take(4);
  $moreCats = $navCats->skip(4);
  $dropdownCats = $moreCats->isNotEmpty() ? $moreCats : $navCats;
@endphp

@if($promoText !== '')
  <div class="bg-ink text-white text-xs sm:text-sm text-center py-2 px-4 font-medium">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-3.5 flex items-center justify-between gap-3 sm:gap-6">
      
      {{-- Mobile Menu Toggle & Brand Logo --}}
      <div class="flex items-center gap-3 shrink-0">
        <button type="button" data-open-menu class="lg:hidden text-stone-700 hover:text-brand-600 p-1.5 rounded-xl hover:bg-stone-100 transition" aria-label="Open Menu">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        @include('partials.brand')
      </div>

      {{-- Modern Search Bar --}}
      <form action="{{ route('shop') }}" method="GET" class="hidden md:flex flex-1 max-w-xl lg:max-w-2xl mx-auto">
        <div class="flex w-full rounded-xl border border-stone-200 bg-stone-50/60 hover:bg-white focus-within:bg-white focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 transition-all duration-200 overflow-hidden shadow-xs">
          <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search shoes, watches, leather belts, wallets...') }}" class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-medium bg-transparent focus:outline-none text-stone-800 placeholder:text-stone-400" autocomplete="off" />
          <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-5 flex items-center justify-center transition-colors group" aria-label="Search">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
          </button>
        </div>
      </form>

      {{-- Top Actions: Track Order, Account, Cart --}}
      <div class="ml-auto flex items-center gap-2 sm:gap-3 shrink-0">
        <button type="button" data-toggle-search class="md:hidden p-2 text-stone-700 hover:text-brand-600 hover:bg-stone-100 rounded-xl" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
        </button>

        {{-- Track Order Pill --}}
        <a href="{{ route('track') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold text-stone-700 bg-stone-50 hover:bg-stone-100 hover:text-brand-600 border border-stone-200/80 transition-all duration-200 shadow-xs group">
          <svg class="w-4 h-4 text-brand-600 group-hover:scale-110 transition-transform shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/>
          </svg>
          <span>Track Order</span>
        </a>

        {{-- My Account Dropdown --}}
        @include('storefront.partials.account-dropdown', ['lightHeader' => false])

        {{-- Cart Module Button --}}
        <button type="button" data-open-cart class="relative flex items-center gap-2 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm transition-all shadow-sm group cursor-pointer" aria-label="Cart">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="group-hover:scale-110 transition-transform">
            <path d="M6 6h15l-1.5 9H7.5L6 6Zm0 0-.7-3H3"/>
            <circle cx="9" cy="20" r="1.3"/>
            <circle cx="17" cy="20" r="1.3"/>
          </svg>
          <span class="hidden sm:inline">Cart</span>
          <span data-cart-count class="cart-count h-5 min-w-5 px-1.5 rounded-full bg-white text-brand-700 text-xs font-black flex items-center justify-center shadow-xs">
            {{ $cartCount }}
          </span>
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

        {{-- 3. 4 Categories --}}
        @foreach($topCats as $cat)
          <a href="{{ route('shop.category', $cat) }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ optional($activeCategory ?? null)->id === $cat->id ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }}">{{ $cat->name }}</a>
        @endforeach

        {{-- 4. Brands Dropdown --}}
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

        {{-- 5. More Categories Dropdown --}}
        @if($navCats->isNotEmpty())
          <div class="relative group/catdropdown" id="catDropdownContainer">
            <button type="button" 
                    onclick="event.stopPropagation(); document.getElementById('catDropdownMenu').classList.toggle('hidden');" 
                    class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ isset($activeCategory) && ! $topCats->pluck('id')->contains($activeCategory->id) ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }} inline-flex items-center gap-1 cursor-pointer">
              <span>More Categories</span>
              <svg class="w-3.5 h-3.5 transition-transform group-hover/catdropdown:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>
            <div id="catDropdownMenu" class="absolute right-0 top-full pt-1.5 hidden group-hover/catdropdown:block z-50 min-w-[260px] max-w-sm">
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

        {{-- 6. Deals --}}
        @if($hasFlashSale ?? true)
          <a href="{{ route('shop', ['flash' => 1]) }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors text-amber-700 bg-amber-50 font-bold hover:bg-amber-100 flex items-center gap-1">
            <span>⚡ Deals</span>
          </a>
        @endif

        {{-- 7. Help & Support --}}
        <a href="{{ route('contact') }}" class="px-3 py-1.5 whitespace-nowrap rounded-lg transition-colors {{ request()->routeIs('contact') ? 'bg-brand-50 text-brand-600 font-bold' : 'text-stone-700 hover:text-brand-600 hover:bg-stone-50' }}">Help &amp; Support</a>
      </nav>
    </div>
  </div>

  {{-- Mobile Search Dropdown Panel --}}
  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3 md:hidden">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search Products...') }}" class="flex-1 border border-stone-200 rounded-md px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-600/30" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-brand-600 text-white px-5 rounded-md font-semibold text-sm hover:bg-brand-700 transition">Search</button>
    </form>
  </div>
</header>
