{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 6. SHOP BY BRAND: logos only, each opens the brand's page (hidden while the shop has a single brand) --}}
@if(isset($featuredBrands) && $featuredBrands->count() > 1)
  <section class="mt-8 sm:mt-12" data-reveal>
    <x-home-heading :title="setting('home_featured_brands_title', 'Our Brands')" :href="route('shop')" link-label="See all" />

    <div class="relative group/carousel">
      <div class="swiper brandsSwiper !py-2 !px-0.5 -mx-0.5">
        <div class="swiper-wrapper">
          @foreach($featuredBrands as $b)
            <div class="swiper-slide">
              <a href="{{ route('shop.brand', $b) }}" class="gb-brand" title="{{ $b->name }}">
                <img src="{{ $b->logoUrl() }}" alt="{{ $b->name }}" loading="lazy" />
              </a>
            </div>
          @endforeach
        </div>
      </div>
      <div class="brandsPagination gb-dots"></div>
    </div>
  </section>
@endif
