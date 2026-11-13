@extends('layouts.storefront')

@section('content')
  @php
    $ctaDefault = setting('default_cta_text', 'Add to Cart');
    $flashEndsAt = setting('flash_sale_ends_at');
    $flashEndsIso = $flashEndsAt ? \Illuminate\Support\Carbon::parse($flashEndsAt)->toIso8601String() : null;
    $mainHero = $heroBanners->first();
    // Promo cards: up to 2 on desktop and up to 2 on phones, each kept to the screens admin picked
    $deskCards = $heroSideBanners->filter->showsOnDesktop()->take(2);
    $phoneCards = $heroSideBanners->filter->showsOnPhone()->take(2);
    $sideCards = $heroSideBanners->filter(fn ($c) => $deskCards->contains("id", $c->id) || $phoneCards->contains("id", $c->id));
    $hasHero = $heroBanners->isNotEmpty() || $sideCards->isNotEmpty() || setting('hero_fallback_title') || setting('hero_fallback_badge');
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
          // With no promo card on desktop the slider spans the full width but keeps the height it has beside the cards
          // (75:28 ≈ 70% of the width at 15:8). Slide images fill it, so their top and bottom edges are trimmed there.
          $heroWide = $deskCards->isEmpty();
          $sliderClasses = $heroWide
            ? 'col-span-2 lg:col-span-10 lg:aspect-[75/28]'
            : 'col-span-2 lg:col-span-7 lg:row-span-2';
          $slideBox = $heroWide ? 'lg:aspect-auto' : '';
        @endphp

        <div data-hero-main class="{{ $sliderClasses }} relative rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-center aspect-[15/8] w-full bg-transparent">
          @if($heroBanners->count() > 1)
            {{-- Carousel Slider --}}
            <div id="homeHeroSlider" class="relative w-full h-full aspect-[15/8] {{ $slideBox }} overflow-hidden select-none">
              @foreach($heroBanners as $i => $slide)
                <div data-hero-slide class="absolute inset-0 transition-opacity duration-500 ease-in-out {{ $i === 0 ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none' }}">
                  <a href="{{ $slide->linkHref() }}" class="block w-full h-full relative" aria-label="{{ $slide->title ?: 'Banner Slide' }}">
                    @if($slide->image)
                      <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}" class="w-full h-full object-cover object-center" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                    @else
                      <div class="w-full h-full bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center py-5 px-5 sm:py-12 sm:px-20 text-white">
                        <div class="max-w-md">
                          @if($slide->badge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $slide->badge }}</span>@endif
                          @if($slide->title)<h2 class="text-xl sm:text-4xl font-extrabold leading-tight">{{ $slide->title }}</h2>@endif
                          @if($slide->subtitle)<p class="max-[389px]:hidden text-[11px] sm:text-sm text-white/80 mt-1.5 sm:mt-2 line-clamp-2">{{ $slide->subtitle }}</p>@endif
                          @if($slide->button_text)<span class="inline-block mt-3 sm:mt-4 px-4 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-brand-600 text-white text-xs sm:text-sm font-bold">{{ $slide->button_text }}</span>@endif
                        </div>
                      </div>
                    @endif
                  </a>
                </div>
              @endforeach

              {{-- Chevron Controls: frosted, fade in on hover (always shown on touch tablets; phones swipe) --}}
              <button type="button" data-hero-arrow-prev class="max-sm:hidden absolute left-3 sm:left-4 [@media(hover:hover)]:translate-x-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-black/25 hover:bg-black/45 backdrop-blur-md border border-white/25 text-white flex items-center justify-center transition duration-300 active:scale-95 [@media(hover:hover)]:opacity-0 group-hover:opacity-100 group-hover:translate-x-0 focus-visible:opacity-100 focus-visible:translate-x-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70" aria-label="Previous Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
              </button>

              <button type="button" data-hero-arrow-next class="max-sm:hidden absolute right-3 sm:right-4 [@media(hover:hover)]:-translate-x-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-black/25 hover:bg-black/45 backdrop-blur-md border border-white/25 text-white flex items-center justify-center transition duration-300 active:scale-95 [@media(hover:hover)]:opacity-0 group-hover:opacity-100 group-hover:translate-x-0 focus-visible:opacity-100 focus-visible:translate-x-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70" aria-label="Next Slide">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
              </button>

              {{-- Indicator Dots --}}
              {{-- Phones: dots sit bottom-right so they never cover the slide button --}}
              <div class="absolute bottom-2.5 right-3 sm:bottom-4 sm:right-auto sm:left-1/2 sm:-translate-x-1/2 z-20 flex items-center gap-1.5 sm:gap-2 bg-black/25 backdrop-blur-[2px] px-2 py-1.5 rounded-full">
                @foreach($heroBanners as $di => $dot)
                  <button type="button" data-hero-dot="{{ $di }}" class="hero-slider-dot rounded-full transition-all duration-300 {{ $di === 0 ? 'w-6 sm:w-8 h-1.5 sm:h-2 bg-white' : 'w-1.5 sm:w-2 h-1.5 sm:h-2 bg-white/50 hover:bg-white/80' }}" aria-label="Slide {{ $di + 1 }}"></button>
                @endforeach
              </div>
            </div>
          @elseif($mainHero)
            <div class="relative w-full h-full aspect-[15/8] {{ $slideBox }} overflow-hidden">
              <a href="{{ $mainHero->linkHref() }}" class="block w-full h-full relative" aria-label="{{ $mainHero->title ?: 'Banner' }}">
                @if($mainHero->image)
                  <img src="{{ $mainHero->imageUrl() }}" alt="{{ $mainHero->title }}" class="w-full h-full object-cover object-center">
                @else
                  <div class="w-full h-full bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center py-5 px-5 sm:py-12 sm:px-20 text-white">
                    <div class="max-w-md">
                      @if($heroBadge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $heroBadge }}</span>@endif
                      @if($heroTitle)<h2 class="text-xl sm:text-4xl font-extrabold leading-tight">{{ $heroTitle }}</h2>@endif
                      @if($heroSubtitle)<p class="max-[389px]:hidden text-[11px] sm:text-sm text-white/80 mt-1.5 sm:mt-2 line-clamp-2">{{ $heroSubtitle }}</p>@endif
                      @if($showHeroCta)<span class="inline-block mt-3 sm:mt-4 px-4 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-brand-600 text-white text-xs sm:text-sm font-bold">{{ $mainHero->button_text ?: $ctaDefault }}</span>@endif
                    </div>
                  </div>
                @endif
              </a>
            </div>
          @else
            <div class="relative w-full h-full aspect-[15/8] {{ $slideBox }} overflow-hidden bg-gradient-to-tr from-stone-950 via-neutral-900 to-brand-600 flex items-center py-5 px-5 sm:py-12 sm:px-20 text-white">
              <div class="max-w-md">
                @if($heroBadge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-500 text-white inline-block mb-2">{{ $heroBadge }}</span>@endif
                <h2 class="text-xl sm:text-4xl font-extrabold leading-tight">{{ $heroTitle ?: site_name() }}</h2>
                @if($heroSubtitle)<p class="max-[389px]:hidden text-[11px] sm:text-sm text-white/80 mt-1.5 sm:mt-2 line-clamp-2">{{ $heroSubtitle }}</p>@endif
                <a href="{{ route('shop') }}" class="inline-block mt-3 sm:mt-4 px-4 sm:px-5 py-1.5 sm:py-2 bg-brand-600 text-white text-xs sm:text-sm font-bold rounded-xl">{{ $ctaDefault }}</a>
              </div>
            </div>
          @endif
        </div>

        {{-- Side Promo Tiles --}}
        @foreach($sideCards as $sideCard)
          @php
            $onDesk = $deskCards->contains("id", $sideCard->id);
            $onPhone = $phoneCards->contains("id", $sideCard->id);
            $cardScreens = ($onDesk && $onPhone ? '' : ($onDesk ? 'hidden lg:flex' : 'lg:hidden')) . ($phoneCards->count() === 1 ? ' col-span-2 lg:col-span-3' : ' col-span-1 lg:col-span-3')
              // A lone desktop card stands the full height of the slider instead of leaving a gap under it
              . ($onDesk && $deskCards->count() === 1 ? ' lg:row-span-2' : '');
          @endphp
          {{-- On desktop the slider sets the height and the cards fill it: the picture is placed absolutely, so a
               square or tall promo picture is cropped to fit instead of stretching the row and leaving a gap. --}}
          <a href="{{ $sideCard->linkHref() }}" data-promo-card="{{ $sideCard->show_on ?? 'both' }}" class="{{ $cardScreens }} relative flex rounded-xl sm:rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group hover:-translate-y-0.5 bg-transparent aspect-[868/476] lg:aspect-auto lg:h-full min-h-0" aria-label="{{ $sideCard->title ?: 'Promo Card' }}">
            @if($sideCard->image)
              <img src="{{ $sideCard->imageUrl() }}" alt="{{ $sideCard->title }}" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-500" loading="lazy">
            @else
              <div class="w-full h-full bg-gradient-to-br from-neutral-800 to-stone-900 p-4 sm:p-6 text-white flex flex-col justify-between">
                <div>
                  @if($sideCard->badge)<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500 text-white uppercase">{{ $sideCard->badge }}</span>@endif
                  <h3 class="font-extrabold text-sm sm:text-base mt-2 line-clamp-2">{{ $sideCard->title }}</h3>
                </div>
                @if($sideCard->button_text)<span class="hidden sm:inline-flex text-xs font-semibold text-brand-400 mt-2 items-center gap-1">{{ $sideCard->button_text }} &rarr;</span>@endif
              </div>
            @endif
          </a>
        @endforeach

      </div>
    </section>
  @endif

  @if(\App\Support\HomeSections::isOn('trust'))
  {{-- 2. TRUST STRIP (Admin → Storefront → Trust Strip): soft coloured icon tiles, 4 across on desktop, 2 x 2 on phones --}}
  @if(isset($features) && $features->isNotEmpty())
    <section class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 mt-3 sm:mt-4" data-reveal>
      <div class="grid grid-cols-2 lg:grid-cols-{{ min(4, $features->count()) }} gap-2 sm:gap-4 bg-white border border-stone-200/80 rounded-2xl p-2.5 sm:p-4 shadow-2xs" data-trust-strip>
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
          <div class="flex items-center gap-2 sm:gap-3 p-1 sm:p-2 min-w-0">
            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl {{ $palette['bg'] }} {{ $palette['text'] }} flex items-center justify-center shrink-0 border {{ $palette['border'] }} shadow-2xs">
              {!! $feature->renderIconHtml('w-4 h-4 sm:w-5 sm:h-5', $palette['text']) !!}
            </div>
            <div class="min-w-0 flex-1">
              <h4 class="text-[11px] sm:text-sm font-extrabold text-stone-900 truncate leading-snug">{{ $feature->title }}</h4>
              @if($feature->subtitle)
                <p class="text-[10px] sm:text-[11px] text-stone-500 truncate mt-0.5">{{ $feature->subtitle }}</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif
  @endif

  <main class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pb-12">

    {{-- Sections 3-10 in the owner's order; each one hides itself when it has nothing to show --}}
    @foreach(\App\Support\HomeSections::ordered() as $homeSection)
      @include('storefront.home.' . $homeSection)
    @endforeach

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
            dot.className = 'hero-slider-dot rounded-full transition-all duration-300 w-6 sm:w-8 h-1.5 sm:h-2 bg-white';
          } else {
            dot.className = 'hero-slider-dot rounded-full transition-all duration-300 w-1.5 sm:w-2 h-1.5 sm:h-2 bg-white/50 hover:bg-white/80';
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

  /* ---------------- 3. Categories Swiper (moves only by arrows or swipe; the hero is the one thing that slides by itself) ---------------- */
  if (typeof Swiper !== 'undefined' && document.querySelector('.categoriesSwiper')) {
    new Swiper('.categoriesSwiper', {
      slidesPerView: 3.25,
      centerInsufficientSlides: true,
      spaceBetween: 8,
      rewind: true,
      speed: 600,
      watchSlidesProgress: true,
      navigation: {
        nextEl: '#catNext',
        prevEl: '#catPrev',
      },
      breakpoints: {
        380: { slidesPerView: 3.4, spaceBetween: 8 },
        480: { slidesPerView: 4.2, spaceBetween: 10 },
        640: { slidesPerView: 5, spaceBetween: 12 },
        768: { slidesPerView: 6, spaceBetween: 14 },
        1024: { slidesPerView: 7, spaceBetween: 16 },
@if(\App\Support\StoreStyle::isWide())
        1280: { slidesPerView: 8, spaceBetween: 16 }, // Wide page width: one more category in the row
@endif
      },
    });
  }

  /* ---------------- 4. Brands Swiper ---------------- */
  if (typeof Swiper !== 'undefined' && document.querySelector('.brandsSwiper')) {
    new Swiper('.brandsSwiper', {
      slidesPerView: 3.25,
      spaceBetween: 8,
      rewind: true,
      speed: 600,
      watchSlidesProgress: true,
      navigation: {
        nextEl: '#brandNext',
        prevEl: '#brandPrev',
      },
      breakpoints: {
        380: { slidesPerView: 3.4, spaceBetween: 8 },
        480: { slidesPerView: 4.2, spaceBetween: 10 },
        640: { slidesPerView: 5.2, spaceBetween: 12 },
        768: { slidesPerView: 6, spaceBetween: 14 },
        1024: { slidesPerView: 7, spaceBetween: 16 },
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

/* ---------------- Coupon: copy the code, and apply it at checkout ---------------- */
// With something in the cart the shopper goes straight to checkout, where the code is applied.
// With an empty cart there is no checkout to go to, so the code is copied and kept; checkout applies it later.
window.applyAndCopyCoupon = function(code) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(code).catch(function() {});
  }
  try {
    sessionStorage.setItem('auto_apply_coupon', code);
  } catch (e) {}

  var badge = document.querySelector('[data-cart-count]');
  var cartHasItems = !!badge && !badge.classList.contains('hidden') && parseInt(badge.textContent, 10) > 0;
  if (!cartHasItems) {
    if (window.showToast) {
      window.showToast('Code ' + code + ' copied. It applies by itself at checkout.', 'success');
    }
    return;
  }

  if (window.showToast) {
    window.showToast('Coupon ' + code + ' copied! Applying at checkout...', 'success');
  }
  setTimeout(function() {
    window.location.href = "{{ route('checkout.show') }}?coupon=" + encodeURIComponent(code);
  }, 250);
};
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-use-coupon]');
  if (btn) window.applyAndCopyCoupon(btn.getAttribute('data-use-coupon'));
});
</script>
@endpush
