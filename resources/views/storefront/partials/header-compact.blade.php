<header class="site-header sticky top-0 z-40 bg-white border-b border-stone-100">
  <div class="max-w-7xl mx-auto pl-2 pr-3 sm:px-5 h-14 sm:h-16 flex items-center gap-1 sm:gap-4 min-w-0">
    <button type="button" data-open-menu class="lg:hidden w-11 h-11 grid place-items-center rounded-xl hover:bg-stone-100 shrink-0 text-brand-600" aria-label="Menu">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    @include('partials.brand', ['size' => 'sm', 'logoClass' => 'max-[359px]:max-w-[100px]'])

    <div class="ml-auto flex items-center gap-0.5 sm:gap-2 shrink-0">
      <button type="button" data-toggle-search class="w-11 h-11 grid place-items-center text-ink hover:text-brand-600 hover:bg-stone-100 rounded-xl" aria-label="Search" aria-expanded="false" aria-controls="mobileSearchPanel">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
      </button>

      @include('storefront.partials.account-dropdown', ['lightHeader' => false])

      <button type="button" data-open-cart class="relative w-11 h-11 grid place-items-center text-ink hover:text-brand-600 hover:bg-stone-100 rounded-xl" aria-label="Cart">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-1.5 9h-12L6 6Zm0 0-.7-3H3"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
        <span data-cart-count class="cart-count absolute top-0.5 right-0 h-[18px] min-w-[18px] px-1 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center {{ $cartCount ? '' : 'hidden' }}">{{ $cartCount }}</span>
      </button>
    </div>
  </div>

  <div id="mobileSearchPanel" class="hidden bg-white border-b border-stone-200 py-3">
    <form action="{{ route('shop') }}" method="GET" class="max-w-7xl mx-auto px-4 sm:px-5 flex gap-2">
      <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ setting('search_placeholder', 'Search…') }}" class="flex-1 border border-stone-200 rounded-lg px-4 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand-600/30" data-mobile-search-input autocomplete="off" />
      <button type="submit" class="bg-brand-600 text-white px-5 rounded-lg font-semibold text-sm hover:bg-brand-700 transition">Search</button>
    </form>
  </div>
</header>
