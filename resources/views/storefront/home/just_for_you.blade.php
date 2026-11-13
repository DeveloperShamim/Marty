{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 9. JUST FOR YOU: near the end (only vouchers follow), picked from what's in the cart and what hasn't been shown above --}}
@if(($justForYou ?? collect())->isNotEmpty())
  <section class="mt-12 sm:mt-16 last:mb-12" data-reveal data-just-for-you>
    <div class="flex items-end justify-between mb-4 sm:mb-6 gap-3">
      <div>
        <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight">Just for you</h2>
      </div>
    </div>
    <div class="pgrid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
      @foreach($justForYou as $product)
        {{-- Phones show 8 --}}
        <div class="grid min-w-0 {{ $loop->index >= 8 ? 'max-md:hidden' : '' }}">
          @include('storefront.partials.product-card', ['product' => $product])
        </div>
      @endforeach
    </div>
    @include('storefront.partials.see-all', ['href' => route('shop'), 'label' => 'products'])
  </section>
@endif
