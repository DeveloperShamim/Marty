@extends('layouts.storefront')

@section('content')
  @php
    $ctaDefault = setting('default_cta_text', 'Add to Cart');
    $flashEndsAt = setting('flash_sale_ends_at');
    $flashEndsIso = $flashEndsAt ? \Illuminate\Support\Carbon::parse($flashEndsAt)->toIso8601String() : null;
    $mainHero = $heroBanners->first();
    $hasHero = $heroBanners->isNotEmpty() || $heroSideBanners->isNotEmpty() || setting('hero_fallback_title') || setting('hero_fallback_badge');
    $hero = $mainHero;
    $heroTitle = $hero?->title ?: setting('hero_fallback_title');
    $heroSubtitle = $hero?->subtitle ?: setting('hero_fallback_subtitle');
    $heroBadge = $hero?->badge ?: setting('hero_fallback_badge');
    $showHeroCta = (bool) ($heroTitle || $hero?->button_text);
  @endphp

  {{-- 1. HERO SHOWCASE --}}
  @if($hasHero)
    <section class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-3 sm:pt-4 pb-2" data-reveal>
      <div class="grid grid-cols-2 lg:grid-cols-10 gap-2.5 sm:gap-3.5 lg:gap-4">

        {{-- Main Hero Slider --}}
        @php
          $sliderClasses = $heroSideBanners->isNotEmpty()
            ? 'col-span-2 lg:col-span-7 lg:row-span-2'
            : 'col-span-2 lg:col-span-10';
        @endphp

        <div class="{{ $sliderClasses }} relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-center aspect-[15/8] w-full bg-transparent">
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

              {{-- Chevron Controls --}}
              <button type="button" data-hero-arrow-prev class="absolute left-2.5 sm:left-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-neutral-800 shadow-md flex items-center justify-center transition-all opacity-80 hover:opacity-100 hover:scale-105 active:scale-95 focus:outline-none" aria-label="Previous Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
              </button>

              <button type="button" data-hero-arrow-next class="absolute right-2.5 sm:right-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-neutral-800 shadow-md flex items-center justify-center transition-all opacity-80 hover:opacity-100 hover:scale-105 active:scale-95 focus:outline-none" aria-label="Next Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
              </button>

              {{-- Indicator Dots --}}
              <div class="absolute bottom-2.5 sm:bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 sm:gap-2 bg-black/15 backdrop-blur-[2px] px-2 py-1 rounded-full">
                @foreach($heroBanners as $di => $dot)
                  <button type="button" data-hero-dot="{{ $di }}" class="hero-slider-dot rounded-full transition-all duration-300 {{ $di === 0 ? 'w-7 sm:w-8 h-2 sm:h-2.5 bg-orange-500' : 'w-2 sm:w-2.5 h-2 sm:h-2.5 bg-white/70 hover:bg-white' }}" aria-label="Slide {{ $di + 1 }}"></button>
                @endforeach
              </div>
            </div>
          @elseif($mainHero)
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

        {{-- Side Promo Tiles --}}
        @foreach($heroSideBanners->take(2) as $sideCard)
          <a href="{{ $sideCard->linkHref() }}" class="col-span-1 lg:col-span-3 relative flex rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group hover:-translate-y-0.5 bg-transparent aspect-[868/476] lg:aspect-auto lg:h-full min-h-0" aria-label="{{ $sideCard->title ?: 'Promo Card' }}">
            @if($sideCard->image)
              <img src="{{ $sideCard->imageUrl() }}" alt="{{ $sideCard->title }}" class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-500" loading="lazy">
            @else
              <div class="w-full h-full bg-gradient-to-br from-neutral-800 to-stone-900 p-4 sm:p-6 text-white flex flex-col justify-between">
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

  {{-- 2. TRUST & GUARANTEE BAR (Managed dynamically via https://marty.test/admin/features) --}}
  @if(isset($features) && $features->isNotEmpty())
    <section class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 mt-3 sm:mt-4" data-reveal>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 bg-white border border-stone-200/80 rounded-2xl p-3 sm:p-4 shadow-2xs">
        @foreach($features as $index => $feature)
          @php
            $palettes = [
              ['bg' => 'bg-amber-50/90', 'border' => 'border-amber-200/90', 'text' => 'text-amber-600'],
              ['bg' => 'bg-emerald-50/90', 'border' => 'border-emerald-200/90', 'text' => 'text-emerald-600'],
              ['bg' => 'bg-amber-50/90', 'border' => 'border-amber-200/90', 'text' => 'text-amber-600'],
              ['bg' => 'bg-sky-50/90', 'border' => 'border-sky-200/90', 'text' => 'text-sky-600'],
            ];
            $palette = $palettes[$index % count($palettes)];
          @endphp
          <div class="flex items-center gap-3 p-1.5 sm:p-2 min-w-0">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl {{ $palette['bg'] }} {{ $palette['text'] }} flex items-center justify-center shrink-0 border {{ $palette['border'] }} shadow-2xs">
              {!! $feature->renderIconHtml('w-5 h-5', $palette['text']) !!}
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-xs sm:text-sm font-extrabold text-stone-900 truncate leading-snug">{{ $feature->title }}</h4>
              @if($feature->subtitle)
                <p class="text-[11px] text-stone-500 truncate mt-0.5">{{ $feature->subtitle }}</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  <main class="max-w-7xl mx-auto px-4 sm:px-5">

    {{-- 3. SHOP BY CATEGORY --}}
    @if($categories->isNotEmpty())
      <section class="mt-8 sm:mt-12" data-reveal>
        <div class="flex items-end justify-between mb-5 border-b border-stone-200/80 pb-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
              {{ setting('home_categories_title', 'Explore Categories') }}
            </h2>
            <p class="text-xs text-stone-500 mt-1">Discover curated lifestyle essentials &amp; smart tech</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="catPrev" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Category">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="catNext" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Category">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="relative group/carousel">
          <div class="swiper categoriesSwiper !py-2.5 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($categories as $cat)
                <div class="swiper-slide">
                  <a href="{{ route('shop.category', $cat) }}" class="group block rounded-2xl border border-stone-200/90 bg-white hover:border-brand-300/60 p-3 sm:p-3.5 text-center transition-all duration-300 shadow-2xs hover:shadow-md hover:-translate-y-1">
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden mb-2.5 bg-stone-100/80 grid place-items-center">
                      @if($cat->image)
                        <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-108" />
                      @else
                        <div class="w-full h-full grid place-items-center bg-stone-100 text-stone-700 font-extrabold text-2xl group-hover:scale-108 transition-transform">
                          {{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}
                        </div>
                      @endif
                    </div>
                    <h3 class="font-bold text-xs sm:text-sm text-stone-800 group-hover:text-brand-600 transition-colors line-clamp-1 leading-snug">{{ $cat->name }}</h3>
                    @if(isset($cat->products_count) && $cat->products_count > 0)
                      <p class="text-[11px] font-medium text-stone-400 mt-0.5">{{ $cat->products_count }} {{ Str::plural('Item', $cat->products_count) }}</p>
                    @else
                      <p class="text-[11px] font-medium text-stone-400 mt-0.5">Explore</p>
                    @endif
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    {{-- 4. FLASH SALE & LIMITED DROPS (Elevated here for high-converting urgency) --}}
    @if($flashProducts->isNotEmpty())
      <section class="mt-10 sm:mt-14" data-reveal>
        <div class="flex items-end justify-between border-b border-stone-200/80 pb-3 mb-6 gap-3 flex-wrap">
          <div>
            <div class="flex items-center gap-2 mb-1.5">
              <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 font-extrabold text-[10px] sm:text-xs px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-red-200">
                ⚡ LIMITED TIME DROPS
              </span>
              @if($flashEndsIso)
                <div data-countdown-end="{{ $flashEndsIso }}" class="flex items-center gap-1 font-mono font-bold text-stone-600 text-xs">
                  <span class="text-stone-400 font-sans">Ends in:</span>
                  <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-h>00</span>:
                  <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-m>00</span>:
                  <span class="bg-stone-900 text-white px-1.5 py-0.5 rounded font-mono font-black text-xs" data-s>00</span>
                </div>
              @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
              {{ setting('home_hot_deal_title', 'Special Flash Discounts') }}
            </h2>
          </div>

          <a href="{{ route('shop', ['flash' => 1]) }}" class="text-xs sm:text-sm font-bold text-stone-900 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
            VIEW ALL DEALS <span class="text-base font-normal">&rarr;</span>
          </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($flashProducts->take(8) as $product)
            @include('storefront.partials.product-card', ['product' => $product, 'flashCard' => true])
          @endforeach
        </div>
      </section>
    @endif

    {{-- 5. CURATED PRODUCT SHOWCASE WITH INTERACTIVE SEGMENTED TABS (Solves product repetition) --}}
    <section class="mt-12 sm:mt-16" data-reveal>
      <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-stone-200/80 pb-4 mb-6 gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
            Curated Collection
          </h2>
          <p class="text-xs text-stone-500 mt-1">Handpicked for authenticity, build quality, and trending demand</p>
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="inline-flex p-1 rounded-xl bg-stone-100 border border-stone-200/70 text-xs font-bold shrink-0 self-start sm:self-auto" id="homeProductTabs" role="tablist">
          <button type="button" data-home-tab="best-sellers" class="home-tab-btn active px-3.5 py-1.5 rounded-lg bg-white text-stone-900 shadow-2xs transition-all">
            🔥 Best Sellers
          </button>
          <button type="button" data-home-tab="new-arrivals" class="home-tab-btn px-3.5 py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all">
            ✨ New Arrivals
          </button>
          <button type="button" data-home-tab="trending" class="home-tab-btn px-3.5 py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all">
            ⭐ Trending
          </button>
        </div>
      </div>

      {{-- Tab Panes --}}
      <div id="homeTabPanes">
        {{-- Pane 1: Best Sellers --}}
        <div id="homePane-best-sellers" class="home-tab-pane">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($bestSellers->take(8) as $product)
              @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
          </div>
          <div class="mt-6 text-center">
            <a href="{{ route('shop', ['best_seller' => 1]) }}" class="group inline-flex items-center gap-3 pl-5 pr-2 py-2 rounded-xl border border-stone-200/90 bg-white hover:border-brand-500/40 hover:bg-stone-50/80 shadow-2xs hover:shadow-md transition-all duration-300 active:scale-95">
              <span class="text-xs font-bold uppercase tracking-wider text-stone-800 group-hover:text-brand-600 transition-colors">View All Best Sellers</span>
              <span class="h-7 w-7 rounded-lg bg-stone-100 group-hover:bg-brand-500 text-stone-500 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-2xs">
                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </span>
            </a>
          </div>
        </div>

        {{-- Pane 2: New Arrivals --}}
        <div id="homePane-new-arrivals" class="home-tab-pane hidden">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($newArrivals->take(8) as $product)
              @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
          </div>
          <div class="mt-6 text-center">
            <a href="{{ route('shop', ['new' => 1]) }}" class="group inline-flex items-center gap-3 pl-5 pr-2 py-2 rounded-xl border border-stone-200/90 bg-white hover:border-brand-500/40 hover:bg-stone-50/80 shadow-2xs hover:shadow-md transition-all duration-300 active:scale-95">
              <span class="text-xs font-bold uppercase tracking-wider text-stone-800 group-hover:text-brand-600 transition-colors">View All New Arrivals</span>
              <span class="h-7 w-7 rounded-lg bg-stone-100 group-hover:bg-brand-500 text-stone-500 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-2xs">
                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </span>
            </a>
          </div>
        </div>

        {{-- Pane 3: Trending --}}
        <div id="homePane-trending" class="home-tab-pane hidden">
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach($trending->take(8) as $product)
              @include('storefront.partials.product-card', ['product' => $product])
            @endforeach
          </div>
          <div class="mt-6 text-center">
            <a href="{{ route('shop') }}" class="group inline-flex items-center gap-3 pl-5 pr-2 py-2 rounded-xl border border-stone-200/90 bg-white hover:border-brand-500/40 hover:bg-stone-50/80 shadow-2xs hover:shadow-md transition-all duration-300 active:scale-95">
              <span class="text-xs font-bold uppercase tracking-wider text-stone-800 group-hover:text-brand-600 transition-colors">Explore All Products</span>
              <span class="h-7 w-7 rounded-lg bg-stone-100 group-hover:bg-brand-500 text-stone-500 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-2xs">
                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>

    {{-- 6. OFFICIAL BRANDS CAROUSEL --}}
    @if(setting('show_featured_brands', '1') === '1' && isset($featuredBrands) && $featuredBrands->isNotEmpty())
      <section class="mt-14 sm:mt-16" data-reveal>
        <div class="flex items-end justify-between mb-4 border-b border-stone-200/80 pb-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
              {{ setting('home_featured_brands_title', 'Official Brands') }}
            </h2>
            <p class="text-xs text-stone-500 mt-1">{{ setting('home_featured_brands_subtitle', '100% genuine products sourced directly from authorized channels') }}</p>
          </div>
          <div class="flex items-center gap-2">
            <a href="{{ route('shop') }}" class="text-xs font-bold text-stone-600 hover:text-brand-600 mr-2 hidden sm:inline-block transition-colors">View All Brands &rarr;</a>
            <button type="button" id="brandPrev" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Brand">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="brandNext" class="h-8 w-8 rounded-full border border-stone-200 bg-white hover:bg-brand-500 hover:text-white hover:border-brand-500 text-stone-600 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Brand">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="relative group/carousel">
          <div class="swiper brandsSwiper !py-2.5 !px-1 -mx-1">
            <div class="swiper-wrapper">
              @foreach($featuredBrands as $b)
                <div class="swiper-slide">
                  <a href="{{ route('shop.brand', $b) }}" class="group flex flex-col items-center justify-center p-3.5 sm:p-4 rounded-2xl border border-stone-200/90 bg-white hover:border-brand-300/60 transition-all duration-300 shadow-2xs hover:shadow-md hover:-translate-y-1 text-center h-full">
                    <div class="h-14 sm:h-16 w-full flex items-center justify-center mb-2.5 p-2 bg-stone-50 rounded-xl border border-stone-150/80 group-hover:bg-brand-50/50 group-hover:border-brand-200/60 transition-all duration-300">
                      <img src="{{ $b->logoUrl() }}" alt="{{ $b->name }}" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-500 ease-out group-hover:scale-110" />
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-stone-800 group-hover:text-brand-600 transition-colors truncate w-full">{{ $b->name }}</span>
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

    {{-- ADMIN HOMEPAGE FEATURED CATEGORIES (Shown only when admin enables 'Featured on Homepage' & has products) --}}
    @if(($featuredHomeCategories ?? collect())->isNotEmpty())
      @foreach($featuredHomeCategories as $featuredCat)
        @if($featuredCat->products->isNotEmpty())
          <section class="mt-12 sm:mt-16" data-reveal>
            <div class="flex items-end justify-between border-b border-stone-200/80 pb-3 mb-6 gap-3">
              <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
                  @if($featuredCat->icon)<span class="mr-1.5">{{ $featuredCat->icon }}</span>@endif
                  {{ $featuredCat->name }}
                </h2>
                <div class="w-10 h-1 bg-brand-500 rounded-full mt-2"></div>
              </div>
              <a href="{{ route('shop.category', $featuredCat) }}" class="text-xs sm:text-sm font-extrabold text-brand-500 hover:text-brand-600 tracking-wider uppercase inline-flex items-center gap-1 transition-colors shrink-0">
                VIEW ALL ITEMS <span class="text-base font-normal">&rarr;</span>
              </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
              @foreach($featuredCat->products as $product)
                @include('storefront.partials.product-card', ['product' => $product])
              @endforeach
            </div>

            <div class="mt-6 text-center">
              <a href="{{ route('shop.category', $featuredCat) }}" class="group inline-flex items-center gap-3 pl-5 pr-2 py-2 rounded-xl border border-stone-200/90 bg-white hover:border-brand-500/40 hover:bg-stone-50/80 shadow-2xs hover:shadow-md transition-all duration-300 active:scale-95">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-800 group-hover:text-brand-600 transition-colors">View All {{ $featuredCat->name }}</span>
                <span class="h-7 w-7 rounded-lg bg-stone-100 group-hover:bg-brand-500 text-stone-500 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-2xs">
                  <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
              </a>
            </div>
          </section>
        @endif
      @endforeach
    @endif

    {{-- ADMIN HOMEPAGE FEATURED BRANDS (Shown only when admin enables 'Homepage Featured' on Brand & has products) --}}
    @if(($featuredHomeBrands ?? collect())->isNotEmpty())
      @foreach($featuredHomeBrands as $featuredBrand)
        @if($featuredBrand->products->isNotEmpty())
          <section class="mt-12 sm:mt-16" data-reveal>
            <div class="flex items-center justify-between border-b border-stone-200/80 pb-3 mb-6 gap-3">
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

            <div class="mt-6 text-center">
              <a href="{{ route('shop.brand', $featuredBrand) }}" class="group inline-flex items-center gap-3 pl-5 pr-2 py-2 rounded-xl border border-stone-200/90 bg-white hover:border-brand-500/40 hover:bg-stone-50/80 shadow-2xs hover:shadow-md transition-all duration-300 active:scale-95">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-800 group-hover:text-brand-600 transition-colors">Explore {{ $featuredBrand->name }}</span>
                <span class="h-7 w-7 rounded-lg bg-stone-100 group-hover:bg-brand-500 text-stone-500 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-2xs">
                  <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
              </a>
            </div>
          </section>
        @endif
      @endforeach
    @endif

    {{-- 7. EXCLUSIVE COUPONS & VOUCHERS --}}
    @if($coupons->isNotEmpty())
      <section class="mt-14 sm:mt-16" data-reveal>
        <div class="flex items-end justify-between mb-4 border-b border-stone-200/80 pb-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900 leading-none">
              Exclusive Vouchers &amp; Offers
            </h2>
            <p class="text-xs text-stone-500 mt-1">Apply promo codes at checkout for instant savings</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="couponPrev" class="h-8 w-8 rounded-full bg-white hover:bg-brand-500 hover:text-white border border-stone-200 hover:border-brand-500 text-stone-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Previous Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="couponNext" class="h-8 w-8 rounded-full bg-white hover:bg-brand-500 hover:text-white border border-stone-200 hover:border-brand-500 text-stone-700 transition-all shadow-2xs flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Next Coupon">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <div class="swiper couponsSwiper !py-1 !px-0.5 -mx-0.5">
          <div class="swiper-wrapper">
            @foreach($coupons as $coupon)
              <div class="swiper-slide">
                <div class="relative overflow-hidden rounded-2xl bg-white border border-stone-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-md hover:border-stone-900 transition-all duration-200 flex flex-col justify-between h-full group">
                  
                  {{-- Ticket Cutout Notches --}}
                  <div class="absolute -left-2.5 bottom-11 w-5 h-5 bg-stone-50 border-r border-stone-200/90 rounded-full"></div>
                  <div class="absolute -right-2.5 bottom-11 w-5 h-5 bg-stone-50 border-l border-stone-200/90 rounded-full"></div>

                  <div>
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                      <span class="inline-flex items-center gap-1.5 bg-stone-100 border border-dashed border-stone-300 text-stone-900 font-mono font-black text-xs px-2.5 py-1 rounded-lg tracking-wider uppercase">
                        {{ $coupon->code }}
                      </span>
                      <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Active
                      </span>
                    </div>

                    <div class="flex items-baseline gap-1.5 mt-2">
                      <span class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight leading-none">{{ $coupon->valueLabel() }}</span>
                      <span class="text-[10px] sm:text-xs font-black uppercase text-stone-700 bg-stone-100 px-2 py-0.5 rounded-md leading-none">OFF</span>
                    </div>

                    <p class="text-xs text-stone-600 mt-2 line-clamp-1 font-medium leading-tight">
                      @if($coupon->description)
                        {{ $coupon->description }}
                      @elseif($coupon->min_order_amount)
                        Min. order {{ money($coupon->min_order_amount) }}
                      @else
                        Valid at checkout
                      @endif
                    </p>
                  </div>

                  <div class="mt-4 pt-3 border-t border-dashed border-stone-200 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-medium text-stone-400">At checkout</span>
                    <button type="button" onclick="applyAndCopyCoupon('{{ $coupon->code }}')" class="inline-flex items-center gap-1.5 rounded-xl bg-stone-900 hover:bg-black text-white font-bold text-xs px-3.5 py-1.5 shadow-2xs transition-colors cursor-pointer">
                      <span>Use Code</span>
                      <span class="text-xs font-mono">&rarr;</span>
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    {{-- 8. VERIFIED CUSTOMER REVIEWS & SOCIAL PROOF --}}
    @if($homeReviews->isNotEmpty())
      <section class="mt-14 sm:mt-16 mb-12" data-reveal>
        <div class="text-center max-w-xl mx-auto mb-8 sm:mb-10">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 border border-stone-200 text-stone-800 text-xs font-bold mb-2.5">
            <span class="text-amber-500">★★★★★</span>
            <span>Real Verified Buyers</span>
          </div>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
            {{ setting('home_reviews_title', 'Customer Feedback') }}
          </h2>
          <p class="text-xs sm:text-sm text-stone-500 mt-1.5">
            {{ setting('home_reviews_subtitle', 'What our customers say about our authentic products and service') }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          @foreach($homeReviews as $review)
            <div class="relative overflow-hidden rounded-2xl border border-stone-200/80 bg-white p-5 sm:p-6 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
              <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                  <div class="flex items-center gap-1 text-amber-400 text-sm">
                    @php $rating = (int) ($review->rating ?: 5); @endphp
                    @for($i = 1; $i <= 5; $i++)
                      <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400 fill-amber-400' : 'text-stone-200 fill-stone-200' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                    @endfor
                    <span class="text-xs font-extrabold text-stone-800 ml-1">{{ $rating }}.0</span>
                  </div>
                  <span class="text-[11px] font-medium text-stone-400">{{ $review->created_at?->diffForHumans() ?? 'Verified' }}</span>
                </div>

                <p class="text-stone-700 text-xs sm:text-sm leading-relaxed">
                  “{{ $review->body }}”
                </p>
              </div>

              <div class="mt-5 pt-4 border-t border-stone-150 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                  @php
                    $initials = collect(preg_split('/\s+/', trim((string) $review->author_name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                  @endphp
                  <div class="h-9 w-9 rounded-full bg-stone-100 border border-stone-200 text-stone-800 font-extrabold text-xs grid place-items-center shrink-0">
                    {{ $initials ?: 'U' }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="font-bold text-xs sm:text-sm text-stone-900 truncate leading-tight">{{ $review->author_name }}</p>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700">
                      <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                      Verified Buyer
                    </span>
                  </div>
                </div>

                @if($review->product)
                  <span class="hidden sm:inline-block text-[11px] font-medium text-stone-400 truncate max-w-[130px] bg-stone-50 px-2 py-0.5 rounded-md border border-stone-100">
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

  /* ---------------- 1. Hero Carousel Slider ---------------- */
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

      function nextSlide() { showSlide(currentIndex + 1); }
      function prevSlide() { showSlide(currentIndex - 1); }

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

  /* ---------------- 2. Segmented Product Tabs ---------------- */
  const tabBtns = document.querySelectorAll('#homeProductTabs .home-tab-btn');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const targetKey = this.getAttribute('data-home-tab');

      tabBtns.forEach(b => {
        const isSelected = b === this;
        b.classList.toggle('active', isSelected);
        b.classList.toggle('bg-white', isSelected);
        b.classList.toggle('text-stone-900', isSelected);
        b.classList.toggle('shadow-2xs', isSelected);
        b.classList.toggle('text-stone-500', !isSelected);
      });

      document.querySelectorAll('#homeTabPanes .home-tab-pane').forEach(pane => {
        pane.classList.toggle('hidden', pane.id !== 'homePane-' + targetKey);
      });
    });
  });

  /* ---------------- 3. Categories Swiper ---------------- */
  if (typeof Swiper !== 'undefined' && document.querySelector('.categoriesSwiper')) {
    new Swiper('.categoriesSwiper', {
      slidesPerView: 2.2,
      spaceBetween: 10,
      loop: false,
      watchSlidesProgress: true,
      navigation: {
        nextEl: '#catNext',
        prevEl: '#catPrev',
      },
      breakpoints: {
        480: { slidesPerView: 3, spaceBetween: 12 },
        640: { slidesPerView: 4, spaceBetween: 14 },
        768: { slidesPerView: 5, spaceBetween: 14 },
        1024: { slidesPerView: 6, spaceBetween: 16 },
      },
    });
  }

  /* ---------------- 4. Brands Swiper ---------------- */
  if (typeof Swiper !== 'undefined' && document.querySelector('.brandsSwiper')) {
    new Swiper('.brandsSwiper', {
      slidesPerView: 2.3,
      spaceBetween: 10,
      loop: false,
      watchSlidesProgress: true,
      navigation: {
        nextEl: '#brandNext',
        prevEl: '#brandPrev',
      },
      breakpoints: {
        480: { slidesPerView: 3.2, spaceBetween: 12 },
        640: { slidesPerView: 4.2, spaceBetween: 14 },
        768: { slidesPerView: 5.2, spaceBetween: 16 },
        1024: { slidesPerView: 6, spaceBetween: 16 },
      },
    });
  }

  /* ---------------- 5. Coupons Swiper ---------------- */
  if (typeof Swiper !== 'undefined' && document.querySelector('.couponsSwiper')) {
    new Swiper('.couponsSwiper', {
      slidesPerView: 1.2,
      spaceBetween: 12,
      loop: false,
      watchSlidesProgress: true,
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

/* ---------------- Coupon 1-Tap Copy & Auto-Apply ---------------- */
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
