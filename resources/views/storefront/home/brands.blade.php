{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 6. SHOP BY BRAND: logos only, each opens the brand's page (hidden while the shop has a single brand) --}}
@if(isset($featuredBrands) && $featuredBrands->count() > 1)
  <section class="mt-10 sm:mt-14" data-reveal>
    <x-home-heading :title="setting('home_featured_brands_title', 'Official Brands')">
        <a href="{{ route('shop') }}" class="text-xs font-bold text-stone-600 hover:text-brand-600 mr-2 hidden sm:inline-block transition-colors">View All Brands &rarr;</a>
        <button type="button" id="brandPrev" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Brand">
          <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" id="brandNext" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Brand">
          <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </x-home-heading>

    <div class="relative group/carousel">
      <div class="swiper brandsSwiper !py-2 !px-0.5 -mx-0.5">
        <div class="swiper-wrapper">
          @foreach($featuredBrands as $b)
            <div class="swiper-slide">
              <a href="{{ route('shop.brand', $b) }}" class="group flex flex-col items-center justify-center p-2 sm:p-4 rounded-xl sm:rounded-2xl border border-stone-200/90 bg-white hover:border-brand-300/60 transition-all duration-300 shadow-2xs hover:shadow-md hover:-translate-y-1 text-center h-full">
                <div class="h-10 sm:h-16 w-full flex items-center justify-center mb-1.5 sm:mb-2.5 p-1.5 sm:p-2 bg-stone-50 rounded-lg sm:rounded-xl border border-stone-150/80 group-hover:bg-brand-50/50 group-hover:border-brand-200/60 transition-all duration-300">
                  <img src="{{ $b->logoUrl() }}" alt="{{ $b->name }}" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-500 ease-out group-hover:scale-110" />
                </div>
                <span class="text-[11px] sm:text-sm font-bold text-stone-800 group-hover:text-brand-600 transition-colors truncate w-full">{{ $b->name }}</span>
                @if(isset($b->products_count) && $b->products_count > 0)
                  <span class="text-[10px] sm:text-[11px] text-stone-400 group-hover:text-brand-500/80 font-medium mt-0.5 transition-colors">{{ $b->products_count }} {{ Str::plural('item', $b->products_count) }}</span>
                @else
                  <span class="text-[10px] sm:text-[11px] text-stone-400 group-hover:text-brand-500/80 font-medium mt-0.5 transition-colors">Official Brand</span>
                @endif
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>
@endif
