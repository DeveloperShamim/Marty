{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- FEATURED CATEGORIES: the first 4 marked 'Featured on home' in Admin → Categories, one short row each --}}
@if(($featuredHomeCategories ?? collect())->isNotEmpty())
  @foreach($featuredHomeCategories as $featuredCat)
    @if($featuredCat->products->isNotEmpty())
      <section class="mt-8 sm:mt-12" data-reveal>
        <x-home-heading :title="$featuredCat->name" :href="route('shop.category', $featuredCat)" />

        {{-- One row that slides by itself (pauses on touch or hover); swipe sideways too. 2 visible on phones, 4 from tablets up --}}
        <div class="flex gap-2.5 sm:gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth scroll-px-3 sm:scroll-px-0 -mx-3 px-3 sm:mx-0 sm:px-0 pb-1" data-auto-row>
          @foreach($featuredCat->products as $product)
            <div class="prow-item gb-row5 w-[calc((100%-0.625rem)/2)] shrink-0 snap-start sm:w-[calc((100%-3rem)/4)] grid min-w-0">
              @include('storefront.partials.product-card', ['product' => $product])
            </div>
          @endforeach
        </div>
      </section>
    @endif
  @endforeach
@endif
