{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 3. SHOP BY CATEGORY --}}
@if($categories->isNotEmpty())
  <section class="mt-8 sm:mt-12" data-reveal>
    {{-- Centred heading; arrows sit on the right --}}
    <div class="relative flex items-center justify-center mb-4 sm:mb-6" data-centered-heading>
      <div class="text-center">
        <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">
          {{ setting('home_categories_title', 'Featured Categories') }}
        </h2>
      </div>
      <div class="absolute right-0 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-1.5 sm:gap-2">
        <button type="button" id="catPrev" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Category">
          <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" id="catNext" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Category">
          <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <div class="relative group/carousel">
      <div class="swiper categoriesSwiper !py-2 !px-0.5 -mx-0.5">
        <div class="swiper-wrapper">
          @foreach($categories as $cat)
            <div class="swiper-slide">
              <a href="{{ route('shop.category', $cat) }}" class="group block rounded-xl sm:rounded-2xl border border-stone-200/90 bg-white hover:border-brand-300/60 p-2 sm:p-3.5 text-center transition-all duration-300 shadow-2xs hover:shadow-md hover:-translate-y-1">
                <div class="relative w-full aspect-square rounded-lg sm:rounded-xl overflow-hidden mb-1.5 sm:mb-2.5 bg-stone-100/80 grid place-items-center">
                  @if($cat->image)
                    <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-108" />
                  @elseif($cat->icon && mb_strlen($cat->icon) <= 4)
                    {{-- No photo yet: the category's emoji (set by the starter pack, editable in Categories) --}}
                    <div class="w-full h-full grid place-items-center bg-brand-50 text-4xl sm:text-6xl group-hover:scale-108 transition-transform" aria-hidden="true">{{ $cat->icon }}</div>
                  @else
                    <div class="w-full h-full grid place-items-center bg-stone-100 text-stone-700 font-extrabold text-lg sm:text-2xl group-hover:scale-108 transition-transform">
                      {{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}
                    </div>
                  @endif
                </div>
                <h3 class="font-bold text-[11px] sm:text-sm text-stone-800 group-hover:text-brand-600 transition-colors line-clamp-1 leading-tight sm:leading-snug">{{ $cat->name }}</h3>
                @if(isset($cat->products_count) && $cat->products_count > 0)
                  <p class="text-[10px] sm:text-[11px] font-medium text-stone-400 mt-0.5">{{ $cat->products_count }} {{ Str::plural('Item', $cat->products_count) }}</p>
                @else
                  <p class="text-[10px] sm:text-[11px] font-medium text-stone-400 mt-0.5">Explore</p>
                @endif
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>
@endif
