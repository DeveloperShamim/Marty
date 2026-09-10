@extends('layouts.storefront')

@section('content')
  @php
    $ctaDefault = setting('default_cta_text', 'Add to Cart');
    $viewMore = setting('home_view_more_label', 'View More');
    $featuredProducts = $trending;
    $recentProducts = $newArrivals->isNotEmpty() ? $newArrivals : $bestSellers;
    $flashEndsAt = setting('flash_sale_ends_at');
    $flashEndsIso = $flashEndsAt ? \Illuminate\Support\Carbon::parse($flashEndsAt)->toIso8601String() : null;
    $homeReviews = \App\Models\ProductReview::approved()->latest()->take(4)->get();
    $mainHero = $heroBanners->first();
    $sideTiles = collect();
    $hasHero = $heroBanners->isNotEmpty() || setting('hero_fallback_title') || setting('hero_fallback_badge');
    $homeProducts = $featuredProducts->isNotEmpty() ? $featuredProducts : $recentProducts;
    $hero = $mainHero;
    $heroTitle = $hero?->title ?: setting('hero_fallback_title');
    $heroSubtitle = $hero?->subtitle ?: setting('hero_fallback_subtitle');
    $heroBadge = $hero?->badge ?: setting('hero_fallback_badge');
    $showHeroCta = (bool) ($heroTitle || $hero?->button_text);
  @endphp

  @php
    $hasHero = $heroBanners->isNotEmpty() || $heroSideBanners->isNotEmpty() || setting('hero_fallback_title') || setting('hero_fallback_badge');
  @endphp

  @if($hasHero)
    <section class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-3 sm:pt-4 pb-2" data-reveal>
      <div class="grid grid-cols-2 lg:grid-cols-10 gap-2.5 sm:gap-3.5 lg:gap-4">

        {{-- Main Hero Slider --}}
        @php
          $sliderClasses = $heroSideBanners->isNotEmpty()
            ? 'col-span-2 lg:col-span-7 lg:row-span-2'
            : 'col-span-2 lg:col-span-10';
        @endphp

        <div class="{{ $sliderClasses }} relative rounded-2xl sm:rounded-3xl overflow-hidden bg-neutral-900 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-center aspect-[15/8] w-full">
          @if($heroBanners->count() > 1)
            {{-- Carousel Slider --}}
            <div id="homeHeroSlider" class="relative w-full h-full aspect-[15/8] overflow-hidden select-none">
              @foreach($heroBanners as $i => $slide)
                <div data-hero-slide class="absolute inset-0 transition-opacity duration-500 ease-in-out {{ $i === 0 ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none' }}">
                  <a href="{{ $slide->linkHref() }}" class="block w-full h-full relative" aria-label="{{ $slide->title ?: 'Banner Slide' }}">
                    @if($slide->image)
                      <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}" class="w-full h-full object-cover object-center" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                    @else
                      <div class="w-full h-full bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center p-6 sm:p-12 text-white">
                        <div class="max-w-md">
                          @if($slide->badge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $slide->badge }}</span>@endif
                          @if($slide->title)<h2 class="text-2xl sm:text-4xl font-extrabold leading-tight">{{ $slide->title }}</h2>@endif
                          @if($slide->subtitle)<p class="text-xs sm:text-sm text-white/80 mt-2">{{ $slide->subtitle }}</p>@endif
                          @if($slide->button_text)<span class="inline-block mt-4 px-5 py-2 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-bold">{{ $slide->button_text }}</span>@endif
                        </div>
                      </div>
                    @endif
                  </a>
                </div>
              @endforeach

              {{-- Chevron Previous Button --}}
              <button type="button" data-hero-arrow-prev class="absolute left-2.5 sm:left-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-neutral-800 shadow-md flex items-center justify-center transition-all opacity-80 hover:opacity-100 hover:scale-105 active:scale-95 focus:outline-none" aria-label="Previous Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
              </button>

              {{-- Chevron Next Button --}}
              <button type="button" data-hero-arrow-next class="absolute right-2.5 sm:right-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-neutral-800 shadow-md flex items-center justify-center transition-all opacity-80 hover:opacity-100 hover:scale-105 active:scale-95 focus:outline-none" aria-label="Next Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
              </button>

              {{-- Indicator Dots (Active is orange elongated pill, inactive is small white dot) --}}
              <div class="absolute bottom-2.5 sm:bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 sm:gap-2 bg-black/15 backdrop-blur-[2px] px-2 py-1 rounded-full">
                @foreach($heroBanners as $di => $dot)
                  <button type="button" data-hero-dot="{{ $di }}" class="hero-slider-dot rounded-full transition-all duration-300 {{ $di === 0 ? 'w-7 sm:w-8 h-2 sm:h-2.5 bg-orange-500' : 'w-2 sm:w-2.5 h-2 sm:h-2.5 bg-white/70 hover:bg-white' }}" aria-label="Slide {{ $di + 1 }}"></button>
                @endforeach
              </div>
            </div>
          @elseif($mainHero)
            {{-- Single Hero Slide --}}
            <div class="relative w-full h-full aspect-[15/8] overflow-hidden">
              <a href="{{ $mainHero->linkHref() }}" class="block w-full h-full relative" aria-label="{{ $mainHero->title ?: 'Banner' }}">
                @if($mainHero->image)
                  <img src="{{ $mainHero->imageUrl() }}" alt="{{ $mainHero->title }}" class="w-full h-full object-cover object-center">
                @else
                  <div class="w-full h-full bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center p-6 sm:p-12 text-white">
                    <div class="max-w-md">
                      @if($heroBadge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $heroBadge }}</span>@endif
                      @if($heroTitle)<h2 class="text-2xl sm:text-4xl font-extrabold leading-tight">{{ $heroTitle }}</h2>@endif
                      @if($heroSubtitle)<p class="text-xs sm:text-sm text-white/80 mt-2">{{ $heroSubtitle }}</p>@endif
                      @if($showHeroCta)<span class="inline-block mt-4 px-5 py-2 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-bold">{{ $mainHero->button_text ?: $ctaDefault }}</span>@endif
                    </div>
                  </div>
                @endif
              </a>
            </div>
          @else
            {{-- Fallback Hero --}}
            <div class="relative w-full h-full aspect-[15/8] overflow-hidden bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center p-6 sm:p-12 text-white">
              <div class="max-w-md">
                @if($heroBadge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $heroBadge }}</span>@endif
                <h2 class="text-2xl sm:text-4xl font-extrabold leading-tight">{{ $heroTitle ?: site_name() }}</h2>
                @if($heroSubtitle)<p class="text-xs sm:text-sm text-white/80 mt-2">{{ $heroSubtitle }}</p>@endif
                <a href="{{ route('shop') }}" class="inline-block mt-4 px-5 py-2 rounded-full bg-blue-600 text-white text-xs sm:text-sm font-bold">{{ $ctaDefault }}</a>
              </div>
            </div>
          @endif
        </div>

        {{-- Side / Bottom Promo Banners (Direct grid children: col-span-1 lg:col-span-3) --}}
        @foreach($heroSideBanners->take(2) as $sideCard)
          <a href="{{ $sideCard->linkHref() }}" class="col-span-1 lg:col-span-3 relative flex rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group hover:-translate-y-0.5 bg-neutral-900 aspect-[868/476] lg:aspect-auto lg:h-full min-h-0" aria-label="{{ $sideCard->title ?: 'Promo Card' }}">
            @if($sideCard->image)
              <img src="{{ $sideCard->imageUrl() }}" alt="{{ $sideCard->title }}" class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-500 rounded-2xl sm:rounded-3xl" loading="lazy">
            @else
              <div class="w-full h-full bg-gradient-to-br from-neutral-800 to-stone-900 p-4 sm:p-6 text-white flex flex-col justify-between rounded-2xl sm:rounded-3xl">
                <div>
                  @if($sideCard->badge)<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500 text-white uppercase">{{ $sideCard->badge }}</span>@endif
                  <h3 class="font-extrabold text-sm sm:text-base mt-2 line-clamp-2">{{ $sideCard->title }}</h3>
                </div>
                @if($sideCard->button_text)<span class="text-xs font-semibold text-brand-400 mt-2 inline-flex items-center gap-1">{{ $sideCard->button_text }} &rarr;</span>@endif
              </div>
            @endif
          </a>
        @endforeach

      </div>
    </section>
  @endif

  <main class="max-w-7xl mx-auto px-4 sm:px-5">
    @if($categories->isNotEmpty())
      <section class="mt-8 sm:mt-12" data-reveal>
        <div class="text-center mb-6">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-ink">{{ setting('home_categories_title', 'Featured Categories') }}</h2>
          <p class="text-xs sm:text-sm text-stone-500 mt-1">Explore our wide selection</p>
        </div>

        <div class="relative group/carousel px-2 sm:px-4">
          <button type="button" id="catPrev" class="absolute -left-2 sm:-left-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 flex items-center justify-center focus:outline-none" aria-label="Previous Category">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="catNext" class="absolute -right-2 sm:-right-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 flex items-center justify-center focus:outline-none" aria-label="Next Category">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>

          <div class="swiper categoriesSwiper !py-3.5 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($categories as $cat)
                <div class="swiper-slide">
                  <a href="{{ route('shop.category', $cat) }}" class="group block rounded-2xl border border-stone-200/80 bg-white hover:border-brand-500/50 p-3.5 text-center transition-all duration-300 shadow-sm hover:shadow-md">
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden mb-2.5 bg-stone-100 grid place-items-center">
                      @if($cat->image)
                        <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-108" />
                      @else
                        <div class="w-full h-full grid place-items-center bg-brand-600/10 text-brand-600 font-extrabold text-2xl group-hover:scale-108 transition-transform">
                          {{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}
                        </div>
                      @endif
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-ink group-hover:text-brand-600 transition-colors line-clamp-1 leading-snug">{{ $cat->name }}</h3>
                    @if(isset($cat->products_count) && $cat->products_count > 0)
                      <p class="text-[11px] font-semibold text-stone-400 mt-0.5">{{ $cat->products_count }} {{ Str::plural('Item', $cat->products_count) }}</p>
                    @else
                      <p class="text-[11px] font-semibold text-stone-400 mt-0.5">Explore</p>
                    @endif
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    @if($homeProducts->isNotEmpty())
      <section class="mt-12" data-reveal>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-6">{{ setting('home_featured_title', 'Featured Products') }}</h2>
        <div id="homeProductsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($homeProducts as $product)
            @include('storefront.partials.product-card', ['product' => $product])
          @endforeach
        </div>
        @if($hasMoreProducts ?? false)
          <div class="mt-8 text-center max-w-xs mx-auto" id="loadMoreContainer">
            <p class="text-xs font-semibold text-stone-500 mb-2">
              Showing <span id="loadedCount" class="font-bold text-ink">{{ $initialLoadedCount ?? count($homeProducts) }}</span> of <span id="totalCount" class="font-bold text-ink">{{ $totalFeaturedCount ?? count($homeProducts) }}</span> products
            </p>
            @php $progressPercent = round((($initialLoadedCount ?? count($homeProducts)) / max(1, $totalFeaturedCount ?? count($homeProducts))) * 100); @endphp
            <div class="w-full bg-stone-200 h-1.5 rounded-full overflow-hidden mb-4">
              <div id="loadMoreProgress" class="bg-brand-600 h-full transition-all duration-300 rounded-full" style="width: {{ $progressPercent }}%"></div>
            </div>

            <button type="button" id="loadMoreBtn" data-page="1" class="group inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-bold px-7 py-3 rounded-full text-sm transition-all shadow-md hover:shadow-lg hover:scale-105 focus:outline-none" aria-label="Load More Products">
              <span>Load More Products</span>
              <svg id="loadMoreArrow" class="h-4 w-4 transition-transform group-hover:translate-y-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
              <svg id="loadMoreSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        @endif
      </section>
    @endif

    @if(setting('show_featured_brands', '1') === '1' && isset($featuredBrands) && $featuredBrands->isNotEmpty())
      <section class="mt-12 sm:mt-14" data-reveal>
        <div class="flex items-center justify-between mb-4 px-1">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-ink">{{ setting('home_featured_brands_title', 'Featured Brands') }}</h2>
            <p class="text-xs sm:text-sm text-stone-500">{{ setting('home_featured_brands_subtitle', 'Shop authentic products directly from leading brands') }}</p>
          </div>
          <div class="flex items-center gap-2">
            <a href="{{ route('shop') }}" class="text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700 mr-1 sm:mr-3">Explore All &rarr;</a>
            <button type="button" id="brandPrev" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-600 hover:text-white hover:border-brand-600 text-stone-600 transition-all shadow-sm flex items-center justify-center focus:outline-none" aria-label="Previous Brand">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="brandNext" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-600 hover:text-white hover:border-brand-600 text-stone-600 transition-all shadow-sm flex items-center justify-center focus:outline-none" aria-label="Next Brand">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="relative group/carousel">
          <div class="swiper brandsSwiper !py-3 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($featuredBrands as $b)
                <div class="swiper-slide">
                  <a href="{{ route('shop.brand', $b) }}" class="group flex flex-col items-center justify-center p-3 sm:p-4 rounded-2xl border border-stone-200/80 bg-white hover:border-brand-500/50 hover:shadow-md transition-all text-center h-full">
                    <div class="h-14 sm:h-16 w-full flex items-center justify-center mb-2 p-1.5 bg-stone-50/80 rounded-xl group-hover:bg-brand-50/60 transition-colors">
                      <img src="{{ $b->logoUrl() }}" alt="{{ $b->name }}" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-110" />
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-ink group-hover:text-brand-600 truncate w-full">{{ $b->name }}</span>
                    @if(isset($b->products_count) && $b->products_count > 0)
                      <span class="text-[10px] text-stone-400 group-hover:text-brand-500 font-medium mt-0.5">{{ $b->products_count }} {{ Str::plural('item', $b->products_count) }}</span>
                    @else
                      <span class="text-[10px] text-stone-400 group-hover:text-brand-500 font-medium mt-0.5">Official Brand</span>
                    @endif
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    @if(($bestSellers ?? collect())->isNotEmpty())
      <section class="mt-14" data-reveal>
        <div class="flex items-end justify-between border-b border-stone-200 pb-3 mb-6 gap-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">{{ setting('home_best_sellers_title', 'Best Selling Products') }}</h2>
            <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
          </div>
          <a href="{{ route('shop', ['best_seller' => 1]) }}" class="text-xs sm:text-sm font-extrabold text-brand-500 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
            VIEW ALL ITEMS <span class="text-base font-normal">→</span>
          </a>
        </div>

        <div class="relative group/carousel px-0.5 sm:px-4">
          <button type="button" id="bestSellerPrev" class="hidden sm:flex absolute -left-2 sm:-left-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 items-center justify-center focus:outline-none" aria-label="Previous Best Sellers">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="bestSellerNext" class="hidden sm:flex absolute -right-2 sm:-right-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 items-center justify-center focus:outline-none" aria-label="Next Best Sellers">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>

          <div class="swiper bestSellersSwiper !py-3.5 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($bestSellers as $product)
                <div class="swiper-slide">
                  @include('storefront.partials.product-card', ['product' => $product])
                </div>
              @endforeach
            </div>
            <div class="swiper-pagination bestSellersPagination !relative !bottom-0 mt-4 flex justify-center"></div>
          </div>

          <div class="mt-4 sm:mt-5 text-center">
            <a href="{{ route('shop', ['best_seller' => 1]) }}" class="group inline-flex items-center justify-center gap-1.5 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white font-bold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider transition-all duration-200 shadow-2xs hover:shadow-xs hover:scale-105 active:scale-95">
              <span>VIEW ALL ITEMS</span>
              <span class="text-sm font-normal transition-transform duration-200 group-hover:translate-x-1">→</span>
            </a>
          </div>
        </div>
      </section>
    @endif

    @if(($newArrivals ?? collect())->isNotEmpty())
      <section class="mt-14" data-reveal>
        <div class="flex items-end justify-between border-b border-stone-200 pb-3 mb-6 gap-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">{{ setting('home_new_arrivals_title', 'New Arrivals') }}</h2>
            <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
          </div>
          <a href="{{ route('shop', ['new' => 1]) }}" class="text-xs sm:text-sm font-extrabold text-brand-500 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
            VIEW ALL ITEMS <span class="text-base font-normal">→</span>
          </a>
        </div>

        <div class="relative group/carousel px-0.5 sm:px-4">
          <button type="button" id="newArrivalPrev" class="hidden sm:flex absolute -left-2 sm:-left-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 items-center justify-center focus:outline-none" aria-label="Previous New Arrivals">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="newArrivalNext" class="hidden sm:flex absolute -right-2 sm:-right-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md hover:scale-110 items-center justify-center focus:outline-none" aria-label="Next New Arrivals">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>

          <div class="swiper newArrivalsSwiper !py-3.5 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($newArrivals as $product)
                <div class="swiper-slide">
                  @include('storefront.partials.product-card', ['product' => $product])
                </div>
              @endforeach
            </div>
            <div class="swiper-pagination newArrivalsPagination !relative !bottom-0 mt-4 flex justify-center"></div>
          </div>

          <div class="mt-4 sm:mt-5 text-center">
            <a href="{{ route('shop', ['new' => 1]) }}" class="group inline-flex items-center justify-center gap-1.5 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white font-bold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider transition-all duration-200 shadow-2xs hover:shadow-xs hover:scale-105 active:scale-95">
              <span>VIEW ALL ITEMS</span>
              <span class="text-sm font-normal transition-transform duration-200 group-hover:translate-x-1">→</span>
            </a>
          </div>
        </div>
      </section>
    @endif

    @if(($featuredHomeCategories ?? collect())->isNotEmpty())
      @foreach($featuredHomeCategories as $featuredCat)
        @if($featuredCat->products->isNotEmpty())
          <section class="mt-14" data-reveal>
            <div class="flex items-end justify-between border-b border-stone-200 pb-3 mb-6 gap-3">
              <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
                  @if($featuredCat->icon)<span class="mr-1.5">{{ $featuredCat->icon }}</span>@endif
                  {{ $featuredCat->name }}
                </h2>
                <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
              </div>
              <a href="{{ route('shop.category', $featuredCat) }}" class="text-xs sm:text-sm font-extrabold text-brand-500 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
                VIEW ALL ITEMS <span class="text-base font-normal">→</span>
              </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
              @foreach($featuredCat->products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
              @endforeach
            </div>

            <div class="mt-4 sm:mt-5 text-center">
              <a href="{{ route('shop.category', $featuredCat) }}" class="group inline-flex items-center justify-center gap-1.5 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white font-bold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider transition-all duration-200 shadow-2xs hover:shadow-xs hover:scale-105 active:scale-95">
                <span>VIEW ALL ITEMS</span>
                <span class="text-sm font-normal transition-transform duration-200 group-hover:translate-x-1">→</span>
              </a>
            </div>
          </section>
        @endif
      @endforeach
    @endif

    @if(($featuredHomeBrands ?? collect())->isNotEmpty())
      @foreach($featuredHomeBrands as $featuredBrand)
        @if($featuredBrand->products->isNotEmpty())
          <section class="mt-14" data-reveal>
            <div class="flex items-center justify-between border-b border-stone-200 pb-3 mb-6 gap-3">
              <div class="flex items-center gap-3">
                <img src="{{ $featuredBrand->logoUrl() }}" class="h-10 w-10 object-contain rounded-xl border border-stone-200 bg-white p-1 shadow-xs" alt="{{ $featuredBrand->name }}">
                <div>
                  <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
                    {{ $featuredBrand->name }}
                  </h2>
                  <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
                </div>
              </div>
              <a href="{{ route('shop.brand', $featuredBrand) }}" class="text-xs sm:text-sm font-extrabold text-brand-500 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
                EXPLORE BRAND PAGE <span class="text-base font-normal">&rarr;</span>
              </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
              @foreach($featuredBrand->products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
              @endforeach
            </div>

            <div class="mt-4 sm:mt-5 text-center">
              <a href="{{ route('shop.brand', $featuredBrand) }}" class="group inline-flex items-center justify-center gap-1.5 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white font-bold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider transition-all duration-200 shadow-2xs hover:shadow-xs hover:scale-105 active:scale-95">
                <span>VIEW ALL ITEMS</span>
                <span class="text-sm font-normal transition-transform duration-200 group-hover:translate-x-1">→</span>
              </a>
            </div>
          </section>
        @endif
      @endforeach
    @endif

    @if($flashProducts->isNotEmpty())
      <section class="mt-14" data-reveal>
        <div class="flex items-end justify-between border-b border-stone-200 pb-3 mb-6 gap-3 flex-wrap">
          <div>
            <div class="flex items-center gap-2 mb-1.5">
              <span class="inline-flex items-center gap-1 bg-brand-50 text-brand-700 font-extrabold text-[10px] sm:text-xs px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-brand-200/80">
                ⚡ SPECIAL DISCOUNTS
              </span>
              @if($flashEndsIso)
                <div data-countdown-end="{{ $flashEndsIso }}" class="flex items-center gap-1 font-mono font-bold text-stone-600 text-xs">
                  <span class="text-stone-400 font-sans">Ends in:</span>
                  <span class="bg-stone-100 text-stone-800 px-1.5 py-0.5 rounded font-mono font-black" data-h>00</span>:
                  <span class="bg-stone-100 text-stone-800 px-1.5 py-0.5 rounded font-mono font-black" data-m>00</span>:
                  <span class="bg-stone-100 text-stone-800 px-1.5 py-0.5 rounded font-mono font-black" data-s>00</span>
                </div>
              @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">{{ setting('home_hot_deal_title', 'Products With Discounts') }}</h2>
            <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
          </div>
          <a href="{{ route('shop', ['flash' => 1]) }}" class="text-xs sm:text-sm font-extrabold text-brand-600 hover:text-brand-700 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
            VIEW ALL DISCOUNTS <span class="text-base font-normal">&rarr;</span>
          </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($flashProducts->take(8) as $product)
            @include('storefront.partials.product-card', ['product' => $product, 'flashCard' => true])
          @endforeach
        </div>

        <div class="mt-4 sm:mt-5 text-center">
          <a href="{{ route('shop', ['flash' => 1]) }}" class="group inline-flex items-center justify-center gap-1.5 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white font-bold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider transition-all duration-200 shadow-2xs hover:shadow-xs hover:scale-105 active:scale-95">
            <span>VIEW ALL DISCOUNTS</span>
            <span class="text-sm font-normal transition-transform duration-200 group-hover:translate-x-1">→</span>
          </a>
        </div>
      </section>
    @endif

    @if($features->isNotEmpty())
      <section class="mt-12 grid grid-cols-2 lg:grid-cols-4 gap-4" data-reveal>
        @foreach($features->take(4) as $feature)
          <div class="rounded-xl border border-stone-100 bg-surface-soft p-5 text-center transition-all hover:shadow-sm">
            @if($feature->icon)
              <div class="mb-2 flex items-center justify-center h-10" aria-hidden="true">
                {!! $feature->renderIconHtml('h-8 w-8', 'text-brand-600') !!}
              </div>
            @endif
            <p class="font-bold text-stone-900">{{ $feature->title }}</p>
            @if($feature->subtitle)
              <p class="text-xs sm:text-sm text-stone-500 mt-1">{{ $feature->subtitle }}</p>
            @endif
          </div>
        @endforeach
      </section>
    @endif

    @if($coupons->isNotEmpty())
      <section class="mt-12" data-reveal>
        <div class="flex items-center justify-between mb-4 border-b border-stone-200/80 pb-3">
          <div class="flex items-center gap-2.5">
            <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
            <h2 class="text-lg sm:text-2xl font-extrabold text-slate-900 leading-none">{{ setting('home_coupons_title', 'Special Offers & Coupons') }}</h2>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="couponPrev" class="h-8 w-8 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="couponNext" class="h-8 w-8 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="swiper couponsSwiper !py-1 !px-0.5 -mx-0.5">
          <div class="swiper-wrapper">
            @foreach($coupons as $coupon)
              <div class="swiper-slide">
                <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-brand-300 transition-all duration-200 flex flex-col justify-between h-full group">
                  
                  {{-- Classic Ticket Cutout Notches --}}
                  <div class="absolute -left-2.5 bottom-11 w-5 h-5 bg-slate-50 border-r border-slate-200/90 rounded-full"></div>
                  <div class="absolute -right-2.5 bottom-11 w-5 h-5 bg-slate-50 border-l border-slate-200/90 rounded-full"></div>

                  <div>
                    {{-- Header: Code Pill & Status Badge --}}
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                      <span class="inline-flex items-center gap-1.5 bg-brand-50 border border-dashed border-brand-300 text-brand-700 font-mono font-black text-xs px-2.5 py-1 rounded-lg tracking-wider uppercase shadow-2xs">
                        <svg class="w-3 h-3 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1 1 0 01.707.293l7 7a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A1 1 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $coupon->code }}
                      </span>
                      <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                      </span>
                    </div>

                    {{-- Discount Value --}}
                    <div class="flex items-baseline gap-1.5 mt-2">
                      <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-none">{{ $coupon->valueLabel() }}</span>
                      <span class="text-[10px] sm:text-xs font-black uppercase text-brand-700 bg-brand-50 border border-brand-200/80 px-2 py-0.5 rounded-md leading-none">OFF</span>
                    </div>

                    {{-- Description --}}
                    <p class="text-xs text-slate-600 mt-2 line-clamp-1 font-medium leading-tight">
                      @if($coupon->description)
                        {{ $coupon->description }}
                      @elseif($coupon->min_order_amount)
                        Min. order {{ money($coupon->min_order_amount) }}
                      @else
                        Valid at checkout
                      @endif
                    </p>
                  </div>

                  {{-- Dashed Ticket Divider & Action CTA --}}
                  <div class="mt-4 pt-3 border-t border-dashed border-slate-200 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-medium text-slate-400">At checkout</span>
                    <button type="button" onclick="applyAndCopyCoupon('{{ $coupon->code }}')" class="btn-shine inline-flex items-center gap-1.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-bold text-xs px-3.5 py-1.5 shadow-2xs transition-colors cursor-pointer">
                      <span>Use Code</span>
                      <span class="text-xs font-mono">→</span>
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <div class="swiper-pagination couponsPagination !relative !bottom-0 mt-3 flex justify-center"></div>
        </div>
      </section>
    @endif

    @if((string) setting('show_home_reviews', '1') === '1' && $homeReviews->isNotEmpty())
      <section class="mt-14 mb-8" data-reveal>
        <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 border border-brand-200/80 text-brand-700 text-xs font-bold mb-2.5 shadow-2xs">
            <span class="text-amber-400">★★★★★</span>
            <span>Real Verified Shoppers</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ setting('home_reviews_title', 'Customer Feedback') }}</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-1.5">{{ setting('home_reviews_subtitle', 'What our happy customers say about our authentic products and service') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          @foreach($homeReviews as $review)
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-2xs hover:shadow-md hover:border-brand-300 transition-all duration-300 flex flex-col justify-between group">
              
              {{-- Subtle decorative watermark --}}
              <div class="absolute -right-2 -bottom-2 text-slate-100 text-7xl font-serif font-black select-none pointer-events-none group-hover:text-brand-50/70 transition-colors">”</div>

              <div>
                {{-- Top: Rating Stars + Date --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                  <div class="flex items-center gap-1 text-amber-400 text-sm">
                    @php $rating = (int) ($review->rating ?: 5); @endphp
                    @for($i = 1; $i <= 5; $i++)
                      <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200 fill-slate-200' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    @endfor
                    <span class="text-xs font-extrabold text-slate-700 ml-1">{{ $rating }}.0</span>
                  </div>
                  <span class="text-[11px] font-medium text-slate-400">{{ $review->created_at?->diffForHumans() ?? 'Verified' }}</span>
                </div>

                {{-- Review Body --}}
                <p class="text-slate-700 text-xs sm:text-sm leading-relaxed font-normal relative z-10">
                  “{{ $review->body }}”
                </p>
              </div>

              {{-- Footer / Author Information --}}
              <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3 min-w-0">
                  @php
                    $initials = collect(preg_split('/\s+/', trim((string) $review->author_name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                  @endphp
                  <div class="h-10 w-10 rounded-full bg-brand-50 border border-brand-200 text-brand-700 font-extrabold text-xs grid place-items-center shrink-0 shadow-2xs">
                    {{ $initials ?: 'U' }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">{{ $review->author_name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                      <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Verified Buyer
                      </span>
                    </div>
                  </div>
                </div>

                @if($review->product)
                  <span class="hidden sm:inline-block text-[11px] font-medium text-slate-400 truncate max-w-[130px] bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100">
                    {{ $review->product->name }}
                  </span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  </main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('loadMoreBtn');
  const grid = document.getElementById('homeProductsGrid');
  const container = document.getElementById('loadMoreContainer');
  const arrow = document.getElementById('loadMoreArrow');
  const spinner = document.getElementById('loadMoreSpinner');

  if (btn && grid) {
    btn.addEventListener('click', function () {
      let page = parseInt(btn.getAttribute('data-page') || '1') + 1;

      btn.disabled = true;
      if (arrow) arrow.classList.add('hidden');
      if (spinner) spinner.classList.remove('hidden');

      fetch(`{{ route('home.load-more') }}?page=${page}&limit=8`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.html) {
          grid.insertAdjacentHTML('beforeend', data.html);
          btn.setAttribute('data-page', page);
        }
        if (data.loaded_count && data.total_count) {
          const loadedEl = document.getElementById('loadedCount');
          const totalEl = document.getElementById('totalCount');
          const progressEl = document.getElementById('loadMoreProgress');
          if (loadedEl) loadedEl.textContent = data.loaded_count;
          if (totalEl) totalEl.textContent = data.total_count;
          if (progressEl) {
            const pct = Math.round((data.loaded_count / data.total_count) * 100);
            progressEl.style.width = pct + '%';
          }
        }
        if (!data.has_more) {
          if (container) container.remove();
        } else {
          btn.disabled = false;
          if (arrow) arrow.classList.remove('hidden');
          if (spinner) spinner.classList.add('hidden');
        }
      })
      .catch(err => {
        console.error('Error loading products:', err);
        btn.disabled = false;
        if (arrow) arrow.classList.remove('hidden');
        if (spinner) spinner.classList.add('hidden');
      });
    });
  }

  /* ---------------- AppleGadgets-style Hero Slider ---------------- */
  const homeHeroSlider = document.getElementById('homeHeroSlider');
  if (homeHeroSlider) {
    const slides = homeHeroSlider.querySelectorAll('[data-hero-slide]');
    const dots = homeHeroSlider.querySelectorAll('[data-hero-dot]');
    const prevBtn = homeHeroSlider.querySelector('[data-hero-arrow-prev]');
    const nextBtn = homeHeroSlider.querySelector('[data-hero-arrow-next]');

    if (slides.length > 1) {
      let currentIndex = 0;
      let slideTimer = null;
      const totalSlides = slides.length;

      function showSlide(index) {
        currentIndex = (index + totalSlides) % totalSlides;
        slides.forEach((slide, i) => {
          if (i === currentIndex) {
            slide.classList.remove('opacity-0', 'pointer-events-none', 'z-0');
            slide.classList.add('opacity-100', 'pointer-events-auto', 'z-10');
          } else {
            slide.classList.remove('opacity-100', 'pointer-events-auto', 'z-10');
            slide.classList.add('opacity-0', 'pointer-events-none', 'z-0');
          }
        });

        dots.forEach((dot, i) => {
          if (i === currentIndex) {
            dot.className = 'hero-slider-dot rounded-full transition-all duration-300 w-7 sm:w-8 h-2 sm:h-2.5 bg-orange-500';
          } else {
            dot.className = 'hero-slider-dot rounded-full transition-all duration-300 w-2 sm:w-2.5 h-2 sm:h-2.5 bg-white/70 hover:bg-white';
          }
        });
      }

      function nextSlide() {
        showSlide(currentIndex + 1);
      }

      function prevSlide() {
        showSlide(currentIndex - 1);
      }

      function startAutoPlay() {
        stopAutoPlay();
        slideTimer = setInterval(nextSlide, 4500);
      }

      function stopAutoPlay() {
        if (slideTimer) {
          clearInterval(slideTimer);
          slideTimer = null;
        }
      }

      if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); nextSlide(); startAutoPlay(); });
      if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); prevSlide(); startAutoPlay(); });

      dots.forEach((dot, idx) => {
        dot.addEventListener('click', (e) => {
          e.preventDefault();
          showSlide(idx);
          startAutoPlay();
        });
      });

      homeHeroSlider.addEventListener('mouseenter', stopAutoPlay);
      homeHeroSlider.addEventListener('mouseleave', startAutoPlay);

      // Touch swipe support
      let touchStartX = 0;
      let touchEndX = 0;
      homeHeroSlider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
      }, { passive: true });
      homeHeroSlider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diffX = touchStartX - touchEndX;
        if (Math.abs(diffX) > 40) {
          if (diffX > 0) nextSlide();
          else prevSlide();
        }
        startAutoPlay();
      }, { passive: true });

      startAutoPlay();
    }
  }

  if (typeof Swiper !== 'undefined' && document.querySelector('.bestSellersSwiper')) {
    new Swiper('.bestSellersSwiper', {
      slidesPerView: 2,
      spaceBetween: 8,
      loop: true,
      watchSlidesProgress: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      pagination: {
        el: '.bestSellersPagination',
        clickable: true,
      },
      navigation: {
        nextEl: '#bestSellerNext',
        prevEl: '#bestSellerPrev',
      },
      breakpoints: {
        480: { slidesPerView: 2, spaceBetween: 12 },
        640: { slidesPerView: 2, spaceBetween: 16 },
        768: { slidesPerView: 3, spaceBetween: 16 },
        1024: { slidesPerView: 4, spaceBetween: 20 },
      },
    });
  }
  if (typeof Swiper !== 'undefined' && document.querySelector('.newArrivalsSwiper')) {
    new Swiper('.newArrivalsSwiper', {
      slidesPerView: 2,
      spaceBetween: 8,
      loop: true,
      watchSlidesProgress: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      pagination: {
        el: '.newArrivalsPagination',
        clickable: true,
      },
      navigation: {
        nextEl: '#newArrivalNext',
        prevEl: '#newArrivalPrev',
      },
      breakpoints: {
        480: { slidesPerView: 2, spaceBetween: 12 },
        640: { slidesPerView: 2, spaceBetween: 16 },
        768: { slidesPerView: 3, spaceBetween: 16 },
        1024: { slidesPerView: 4, spaceBetween: 20 },
      },
    });
  }
  if (typeof Swiper !== 'undefined' && document.querySelector('.categoriesSwiper')) {
    new Swiper('.categoriesSwiper', {
      slidesPerView: 2.2,
      spaceBetween: 12,
      loop: true,
      watchSlidesProgress: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      breakpoints: {
        480: { slidesPerView: 3, spaceBetween: 14 },
        640: { slidesPerView: 4, spaceBetween: 16 },
        768: { slidesPerView: 5, spaceBetween: 16 },
        1024: { slidesPerView: 6, spaceBetween: 18 },
      },
    });
  }
  if (typeof Swiper !== 'undefined' && document.querySelector('.brandsSwiper')) {
    new Swiper('.brandsSwiper', {
      slidesPerView: 2.3,
      spaceBetween: 10,
      loop: true,
      watchSlidesProgress: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: '#brandNext',
        prevEl: '#brandPrev',
      },
      breakpoints: {
        480: { slidesPerView: 3.2, spaceBetween: 12 },
        640: { slidesPerView: 4.2, spaceBetween: 14 },
        768: { slidesPerView: 5.2, spaceBetween: 16 },
        1024: { slidesPerView: 7, spaceBetween: 16 },
      },
    });
  }
  if (typeof Swiper !== 'undefined' && document.querySelector('.couponsSwiper')) {
    new Swiper('.couponsSwiper', {
      slidesPerView: 2,
      spaceBetween: 10,
      loop: true,
      watchSlidesProgress: true,
      autoplay: {
        delay: 3500,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      pagination: {
        el: '.couponsPagination',
        clickable: true,
      },
      navigation: {
        nextEl: '#couponNext',
        prevEl: '#couponPrev',
      },
      breakpoints: {
        640: { slidesPerView: 2, spaceBetween: 14 },
        1024: { slidesPerView: 3, spaceBetween: 16 },
      },
    });
  }
});

window.applyAndCopyCoupon = function(code) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(code).catch(function() {});
  }
  try {
    sessionStorage.setItem('auto_apply_coupon', code);
  } catch (e) {}
  if (window.showToast) {
    window.showToast('Coupon ' + code + ' copied! Applying at checkout...', 'success');
  }
  setTimeout(function() {
    window.location.href = "{{ route('checkout.show') }}?coupon=" + encodeURIComponent(code);
  }, 250);
};
</script>
@endpush
