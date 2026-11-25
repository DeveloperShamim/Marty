{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 3. FEATURED CATEGORIES: centred title, white picture tiles with the name under each, round arrows at the sides --}}
@if($categories->isNotEmpty())
  <section class="mt-8 sm:mt-12" data-reveal>
    <x-home-heading :title="setting('home_categories_title', 'Featured Categories')" centered />

    <div class="relative">
      <button type="button" id="catPrev" class="gb-arrow gb-arrow-prev" aria-label="Previous categories">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <div class="swiper categoriesSwiper !py-2 !px-0.5 -mx-0.5">
        <div class="swiper-wrapper">
          @foreach($categories as $cat)
            <div class="swiper-slide">
              <a href="{{ route('shop.category', $cat) }}" class="gb-cat">
                <span class="gb-cat-tile">
                  @if($cat->image)
                    <img src="{{ $cat->imageUrl() }}" alt="{{ $cat->name }}" loading="lazy" decoding="async" />
                  @elseif($cat->icon && mb_strlen($cat->icon) <= 4)
                    <span class="text-4xl sm:text-5xl" aria-hidden="true">{{ $cat->icon }}</span>
                  @else
                    <span class="text-stone-700 font-bold text-2xl">{{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}</span>
                  @endif
                </span>
                <span class="gb-cat-name line-clamp-1">{{ $cat->name }}</span>
              </a>
            </div>
          @endforeach
        </div>
      </div>
      <button type="button" id="catNext" class="gb-arrow gb-arrow-next" aria-label="Next categories">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>
  </section>
@endif
