{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 9. JUST FOR YOU: near the end (only vouchers follow), picked from what's in the cart and what hasn't been shown above --}}
@if(($justForYou ?? collect())->isNotEmpty())
  <section class="mt-10 sm:mt-14" data-reveal data-just-for-you>
    <x-home-heading title="Just For You" />
    <div class="pgrid gb-grid5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
      @foreach($justForYou as $product)
        {{-- Phones show 8 --}}
        <div class="grid min-w-0 {{ $loop->index >= 10 ? 'max-md:hidden' : '' }}">
          @include('storefront.partials.product-card', ['product' => $product])
        </div>
      @endforeach
    </div>
    {{-- "Load more", as on ghorerbazar.com: opens the full list --}}
    <div class="mt-5 sm:mt-6 flex justify-center">
      <a href="{{ route('shop') }}" class="gb-load-more" data-see-all>Load more</a>
    </div>
  </section>
@endif
