{{-- One homepage product row: a heading, a sliding row of product cards and a See all link.
     Used for the featured category rows and the brand rows. Needs: $title, $products, $href. --}}
<section class="mt-12 sm:mt-16" data-reveal>
  <div class="flex items-end justify-between mb-4 sm:mb-6 gap-3">
    <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">{{ $title }}</h2>
  </div>

  {{-- One row that slides by itself (pauses on touch or hover); swipe sideways too. 2 visible on phones, 4 from tablets up --}}
  <div class="flex gap-2.5 sm:gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth scroll-px-3 sm:scroll-px-0 -mx-3 px-3 sm:mx-0 sm:px-0 pb-1" data-auto-row>
    @foreach($products as $product)
      <div class="w-[calc((100%-0.625rem)/2)] shrink-0 snap-start sm:w-[calc((100%-3rem)/4)] grid min-w-0">
        @include('storefront.partials.product-card', ['product' => $product])
      </div>
    @endforeach
    {{-- Swiping to the end lands on See all --}}
    @if($products->count() > 2)
      <a href="{{ $href }}" class="w-[calc((100%-0.625rem)/2)] shrink-0 snap-start sm:w-[calc((100%-3rem)/4)] flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-stone-300 bg-stone-50/80 hover:border-brand-500/50 text-stone-800 hover:text-brand-600 transition-colors p-4 text-center" data-row-see-all>
        <span class="h-11 w-11 rounded-full bg-white border border-stone-200 flex items-center justify-center shadow-2xs">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </span>
        <span class="text-sm font-bold leading-snug">See all<br>{{ $title }}</span>
      </a>
    @endif
  </div>

  @include('storefront.partials.see-all', ['href' => $href, 'label' => $title])
</section>
