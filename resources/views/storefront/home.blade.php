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

  @if($hasHero)
    <section class="relative min-h-[300px] sm:min-h-[420px] lg:min-h-[480px] overflow-hidden" data-reveal>
      @if($heroBanners->count() > 1)
        <div id="heroSlider" class="absolute inset-0">
          @foreach($heroBanners as $i => $slide)
            <div data-slide class="hero-slide absolute inset-0 {{ $i > 0 ? 'is-hidden' : '' }}">
              <div class="absolute inset-0 bg-cover bg-right-bottom bg-no-repeat"
                @if($slide->image) style="background-image:linear-gradient(105deg,rgba(0,0,0,.45) 0%,rgba(0,0,0,.15) 50%,transparent 100%),url('{{ $slide->imageUrl() }}')" @else style="background:linear-gradient(105deg,#1e293b,var(--brand-primary,#E8751B))" @endif>
              </div>
              @php
                $sBadge = $slide->badge ?: setting('hero_fallback_badge');
                $sTitle = $slide->title ?: setting('hero_fallback_title');
                $sSubtitle = $slide->subtitle ?: setting('hero_fallback_subtitle');
                $sShowCta = (bool) ($sTitle || $slide->button_text);
              @endphp
              <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-5 py-10 sm:py-20 lg:py-24 text-white">
                <div class="max-w-xl pr-6 sm:pr-0">
                  @if($sBadge)
                    <span class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black tracking-wider uppercase bg-brand-500 text-white shadow-md border border-white/20 mb-2 sm:mb-3 max-w-[95%] truncate">
                      <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-300 animate-pulse shrink-0"></span>
                      <span class="truncate">{{ $sBadge }}</span>
                    </span>
                  @endif

                  @if($sTitle)
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight">{!! nl2br(e($sTitle)) !!}</h1>
                  @endif

                  @if($sSubtitle)
                    <p class="mt-2 sm:mt-3 text-white/85 text-xs sm:text-base max-w-md line-clamp-3 sm:line-clamp-none">{{ $sSubtitle }}</p>
                  @endif

                  @if($sShowCta || setting('tagline'))
                    <div class="mt-4 sm:mt-6 flex flex-wrap items-center gap-3 sm:gap-4">
                      @if($sShowCta)
                        <a href="{{ $slide->linkHref() }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-bold px-4 py-2 sm:px-5 sm:py-2.5 rounded-full text-xs sm:text-sm shadow-md transition-all">
                          {{ $slide->button_text ?: $ctaDefault }}
                          <span class="h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-white text-brand-700 flex items-center justify-center text-[10px] sm:text-xs">→</span>
                        </a>
                      @endif
                      @if(setting('tagline'))
                        <span class="text-xs sm:text-sm text-white/80 italic hidden sm:inline">{{ setting('tagline') }}</span>
                      @endif
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach

          <div class="absolute bottom-3 sm:bottom-5 left-4 sm:left-8 flex gap-1.5 sm:gap-2 z-20">
            @foreach($heroBanners as $di => $dot)
              <button type="button" data-dot class="hero-dot {{ $di === 0 ? 'is-on' : '' }}" aria-label="Slide {{ $di + 1 }}"></button>
            @endforeach
          </div>
          <button type="button" data-hero-prev class="absolute left-1.5 sm:left-3 top-1/2 -translate-y-1/2 grid h-8 w-8 sm:h-9 sm:w-9 place-items-center rounded-full bg-white/90 text-ink hover:bg-white shadow z-30 pointer-events-auto" aria-label="Previous">
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m15 18-6-6 6-6"/></svg>
          </button>
          <button type="button" data-hero-next class="absolute right-1.5 sm:right-3 top-1/2 -translate-y-1/2 grid h-8 w-8 sm:h-9 sm:w-9 place-items-center rounded-full bg-white/90 text-ink hover:bg-white shadow z-30 pointer-events-auto" aria-label="Next">
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 18 6-6-6-6"/></svg>
          </button>
        </div>
      @elseif($mainHero)
        <div class="absolute inset-0 bg-cover bg-right-bottom bg-no-repeat"
          @if($mainHero->image) style="background-image:linear-gradient(105deg,rgba(0,0,0,.45) 0%,rgba(0,0,0,.15) 50%,transparent 100%),url('{{ $mainHero->imageUrl() }}')" @else style="background:linear-gradient(105deg,#1e293b,var(--brand-primary,#E8751B))" @endif></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-5 py-10 sm:py-20 lg:py-24 text-white">
          <div class="max-w-xl">
            @if($heroBadge)
              <span class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black tracking-wider uppercase bg-brand-500 text-white shadow-md border border-white/20 mb-2 sm:mb-3 max-w-[95%] truncate">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-300 animate-pulse shrink-0"></span>
                <span class="truncate">{{ $heroBadge }}</span>
              </span>
            @endif

            @if($heroTitle)
              <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight">{!! nl2br(e($heroTitle)) !!}</h1>
            @endif

            @if($heroSubtitle)
              <p class="mt-2 sm:mt-3 text-white/85 text-xs sm:text-base max-w-md line-clamp-3 sm:line-clamp-none">{{ $heroSubtitle }}</p>
            @endif

            @if($showHeroCta || setting('tagline'))
              <div class="mt-4 sm:mt-6 flex flex-wrap items-center gap-3 sm:gap-4">
                @if($showHeroCta)
                  <a href="{{ $mainHero->linkHref() }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-bold px-4 py-2 sm:px-5 sm:py-2.5 rounded-full text-xs sm:text-sm shadow-md transition-all">
                    {{ $mainHero->button_text ?: $ctaDefault }}
                    <span class="h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-white text-brand-700 flex items-center justify-center text-[10px] sm:text-xs">→</span>
                  </a>
                @endif
                @if(setting('tagline'))
                  <span class="text-xs sm:text-sm text-white/80 italic hidden sm:inline">{{ setting('tagline') }}</span>
                @endif
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="absolute inset-0" style="background:linear-gradient(105deg,#1e293b,var(--brand-primary,#E8751B))"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-5 py-10 sm:py-20 lg:py-24 text-white">
          <div class="max-w-xl">
            @if($heroBadge)
              <span class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black tracking-wider uppercase bg-brand-500 text-white shadow-md border border-white/20 mb-2 sm:mb-3 max-w-[95%] truncate">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-300 animate-pulse shrink-0"></span>
                <span class="truncate">{{ $heroBadge }}</span>
              </span>
            @endif

            @if($heroTitle)
              <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">{!! nl2br(e($heroTitle)) !!}</h1>
            @endif

            @if($heroSubtitle)
              <p class="mt-3 text-white/85 text-sm sm:text-base max-w-md">{{ $heroSubtitle }}</p>
            @endif

            @if($showHeroCta || setting('tagline'))
              <div class="mt-6 flex flex-wrap items-center gap-4">
                @if($showHeroCta)
                  <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-bold px-5 py-2.5 rounded-full text-sm">
                    {{ $ctaDefault }}
                    <span class="h-6 w-6 rounded-full bg-white text-brand-700 flex items-center justify-center text-xs">→</span>
                  </a>
                @endif
                @if(setting('tagline'))
                  <span class="text-sm text-white/80 italic hidden sm:inline">{{ setting('tagline') }}</span>
                @endif
              </div>
            @endif
          </div>
        </div>
      @endif
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
        <div class="flex items-center justify-between mb-4 border-b border-stone-200 pb-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">{{ setting('home_coupons_title', 'Special Offers & Coupons') }}</h2>
            <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="couponPrev" class="h-8 w-8 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md flex items-center justify-center focus:outline-none" aria-label="Previous Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="couponNext" class="h-8 w-8 rounded-full bg-brand-500 hover:bg-brand-600 text-white transition-all shadow-md flex items-center justify-center focus:outline-none" aria-label="Next Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="swiper couponsSwiper !py-1 !px-0.5 -mx-0.5">
          <div class="swiper-wrapper">
            @foreach($coupons as $coupon)
              <div class="swiper-slide">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-500 to-brand-700 p-3.5 sm:p-4 text-white shadow-md hover:shadow-lg transition-all duration-300 flex flex-col justify-between h-full group border border-white/10">
                  <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/15 rounded-full blur-xl group-hover:scale-125 transition-transform"></div>

                  <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                      <span class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-md text-white border border-white/30 font-mono font-extrabold text-[10px] sm:text-xs px-2 py-0.5 rounded-md tracking-wider uppercase shadow-xs">
                        <svg class="w-3 h-3 text-white/90 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1 1 0 01.707.293l7 7a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A1 1 0 013 12V7a4 4 0 014-4z"/></svg>
                        {{ $coupon->code }}
                      </span>
                      <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-emerald-950 bg-emerald-300 px-1.5 py-0.5 rounded-full shadow-xs">Active</span>
                    </div>

                    <div class="flex items-baseline gap-1 mt-1">
                      <span class="text-lg sm:text-2xl font-black tracking-tight text-white leading-none">{{ $coupon->valueLabel() }}</span>
                      <span class="text-[9px] sm:text-[10px] font-black uppercase bg-white text-stone-900 px-1.5 py-0.5 rounded leading-none shadow-xs">OFF</span>
                    </div>

                    <p class="text-[10px] sm:text-xs text-white/90 mt-1.5 line-clamp-1 font-medium leading-tight">
                      @if($coupon->description)
                        {{ $coupon->description }}
                      @elseif($coupon->min_order_amount)
                        Min. order {{ money($coupon->min_order_amount) }}
                      @else
                        Valid at checkout
                      @endif
                    </p>
                  </div>

                  <div class="mt-3 pt-2 border-t border-dashed border-white/25 flex items-center justify-between gap-1">
                    <span class="text-[9px] sm:text-[11px] font-medium text-white/80">At checkout</span>
                    <button type="button" onclick="applyAndCopyCoupon('{{ $coupon->code }}')" class="inline-flex items-center gap-1 rounded-full bg-white text-stone-900 hover:bg-stone-900 hover:text-white font-extrabold text-[10px] sm:text-xs px-3 py-1 shadow-sm transition-all cursor-pointer">
                      Use Code <span class="text-xs">→</span>
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

    @if($homeReviews->isNotEmpty())
      <section class="mt-12 mb-8" data-reveal>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-6">{{ setting('home_reviews_title', 'Customer Reviews') }}</h2>
        <div class="grid md:grid-cols-2 gap-4">
          @foreach($homeReviews as $review)
            <blockquote class="rounded-xl border border-stone-100 bg-white p-6">
              <p class="text-stone-600 text-sm leading-relaxed">{{ $review->body }}</p>
              <footer class="mt-4 flex items-center gap-3">
                @php $initials = collect(preg_split('/\s+/', trim((string) $review->author_name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode(''); @endphp
                <span class="h-10 w-10 rounded-full bg-brand-600 text-white text-sm font-bold grid place-items-center" aria-hidden="true">{{ $initials ?: '?' }}</span>
                <div>
                  <p class="font-bold text-sm">{{ $review->author_name }}</p>
                  @if($review->title)<p class="text-xs text-stone-400">{{ $review->title }}</p>@endif
                </div>
              </footer>
            </blockquote>
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
