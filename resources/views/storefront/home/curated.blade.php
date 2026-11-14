{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 5. CURATED PRODUCT SHOWCASE WITH INTERACTIVE SEGMENTED TABS (Solves product repetition) --}}
<section class="mt-10 sm:mt-14" data-reveal>
  {{-- On phones the tabs sit under the title at full width --}}
  <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-4 sm:mb-6 gap-3 sm:gap-4" data-home-heading>
    <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">Curated Collection</h2>

    {{-- Segmented Tab Switcher --}}
    <div class="w-full sm:w-auto flex sm:inline-flex p-1 rounded-xl bg-stone-100 border border-stone-200/70 text-xs font-bold" id="homeProductTabs" role="tablist">
      <button type="button" data-home-tab="best-sellers" class="home-tab-btn active flex-1 sm:flex-initial text-center justify-center px-2.5 sm:px-3.5 py-2 sm:py-1.5 rounded-lg bg-white text-stone-900 shadow-2xs transition-all text-[11px] sm:text-xs">
        Best Sellers
      </button>
      <button type="button" data-home-tab="new-arrivals" class="home-tab-btn flex-1 sm:flex-initial text-center justify-center px-2.5 sm:px-3.5 py-2 sm:py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all text-[11px] sm:text-xs">
        New Arrivals
      </button>
    </div>
  </div>

  {{-- Tab Panes --}}
  <div id="homeTabPanes">
    {{-- Pane 1: Best Sellers --}}
    <div id="homePane-best-sellers" class="home-tab-pane">
      <div class="pgrid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
        @foreach($bestSellers->take(8) as $product)
          <div class="{{ $loop->index >= 6 ? 'max-sm:hidden' : '' }} grid min-w-0">
            @include('storefront.partials.product-card', ['product' => $product])
          </div>
        @endforeach
      </div>
      @include('storefront.partials.see-all', ['href' => route('shop', ['best_seller' => 1]), 'label' => 'best sellers'])
    </div>

    {{-- Pane 2: New Arrivals --}}
    <div id="homePane-new-arrivals" class="home-tab-pane hidden">
      <div class="pgrid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
        @foreach($newArrivals->take(8) as $product)
          <div class="{{ $loop->index >= 6 ? 'max-sm:hidden' : '' }} grid min-w-0">
            @include('storefront.partials.product-card', ['product' => $product])
          </div>
        @endforeach
      </div>
      @include('storefront.partials.see-all', ['href' => route('shop', ['new' => 1]), 'label' => 'new arrivals'])
    </div>

  </div>
</section>
