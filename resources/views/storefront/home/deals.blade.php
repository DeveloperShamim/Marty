{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 4. FLASH DEALS while a timed sale runs (Admin → Flash Sale "Ends at" in the future), otherwise OUR PICKS --}}
@if($flashActive)
  <section class="mt-10 sm:mt-14" data-reveal data-home-flash>
    <div class="flex items-end justify-between mb-4 sm:mb-6 gap-3 flex-wrap">
      <div>
        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
          @if($flashEndsIso)
            <div data-countdown-end="{{ $flashEndsIso }}" class="flex items-center gap-1 font-mono font-bold text-stone-600 text-xs">
              <span class="text-stone-400 font-sans text-[11px] mr-0.5">Ends in:</span>
              <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-d>00</span><span class="text-stone-500 font-bold text-[10px]">d</span> :
              <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-h>00</span><span class="text-stone-500 font-bold text-[10px]">h</span> :
              <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-m>00</span><span class="text-stone-500 font-bold text-[10px]">m</span> :
              <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-s>00</span><span class="text-stone-500 font-bold text-[10px]">s</span>
            </div>
          @endif
        </div>
        <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">
          {{ setting('home_hot_deal_title', 'Special Flash Discounts') }}
        </h2>
      </div>

    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
      @foreach($flashProducts->take(8) as $product)
        {{-- Phones show the first 4; View all deals has the rest --}}
        <div class="{{ $loop->index >= 4 ? 'max-sm:hidden' : '' }} grid min-w-0">
          @include('storefront.partials.product-card', ['product' => $product, 'flashCard' => true])
        </div>
      @endforeach
    </div>
    @include('storefront.partials.see-all', ['href' => route('shop', ['flash' => 1]), 'label' => 'deals'])
  </section>
@elseif($ourPicks->isNotEmpty())
  <section class="mt-10 sm:mt-14" data-reveal data-our-picks>
    <div class="text-center mb-4 sm:mb-6" data-centered-heading>
      <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">{{ setting('home_picks_title', 'Top Selling Products') }}</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
      @foreach($ourPicks as $product)
        {{-- Phones show the first 4 --}}
        <div class="{{ $loop->index >= 4 ? 'max-sm:hidden' : '' }} grid min-w-0">
          @include('storefront.partials.product-card', ['product' => $product])
        </div>
      @endforeach
    </div>
    @include('storefront.partials.see-all', ['href' => route('shop', ['featured' => 1]), 'label' => 'top selling'])
  </section>
@endif
