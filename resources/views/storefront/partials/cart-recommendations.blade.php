{{-- "You May Also Like" in the cart drawer. The Add buttons use the shared .add-to-cart handler
     (adds straight away, or opens the size/colour picker for products with options). --}}
@if($recommendations->isNotEmpty())
  <div class="px-4 sm:px-5 pt-3.5 pb-4">
    <div class="flex items-center justify-between gap-3 mb-3">
      <div>
        <h3 class="font-bold text-[15px] text-ink leading-tight">You May Also Like</h3>
        <span class="block mt-1.5 h-0.5 w-12 rounded-full bg-brand-600"></span>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button type="button" data-recs-prev class="h-9 w-9 rounded-full bg-brand-600 hover:bg-brand-700 text-white grid place-items-center transition disabled:opacity-30 disabled:cursor-default" aria-label="Previous suggestions">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <button type="button" data-recs-next class="h-9 w-9 rounded-full bg-brand-600 hover:bg-brand-700 text-white grid place-items-center transition disabled:opacity-30 disabled:cursor-default" aria-label="More suggestions">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
      </div>
    </div>

    <div data-recs-track class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth no-scrollbar -mx-1 px-1 pb-1">
      @foreach($recommendations as $product)
        @php
          $variantsGrouped = $product->variants ? $product->variants->groupBy('type')->map(fn ($items) => $items->pluck('value')->unique()->values()) : collect();
          if ($variantsGrouped->isEmpty() && $product->skus->isNotEmpty()) {
              $grouped = [];
              foreach ($product->skus as $sku) {
                  foreach ($sku->getAttributesData() as $type => $val) {
                      $grouped[$type][] = $val;
                  }
              }
              $variantsGrouped = collect($grouped)->map(fn ($vals) => collect($vals)->unique()->values());
          }
          $img = $product->imageUrl();
        @endphp
        <article class="snap-start shrink-0 w-[85%] flex items-center gap-3 rounded-2xl border border-stone-200 bg-white p-3">
          <a href="{{ route('product.show', $product) }}" class="shrink-0">
            <img src="{{ $img }}" alt="{{ $product->name }}" class="h-20 w-20 rounded-xl object-cover bg-stone-100" loading="lazy">
          </a>
          <div class="min-w-0 flex-1">
            <a href="{{ route('product.show', $product) }}" class="block text-[13px] font-semibold text-ink leading-snug line-clamp-2 hover:underline">{{ $product->name }}</a>
            <p class="mt-1 text-[13px]">
              <span class="font-semibold text-stone-700">{{ money($product->price) }}</span>
              @if($product->on_sale && $product->regular_price)
                <span class="ml-1 text-xs text-stone-400 line-through">{{ money($product->regular_price) }}</span>
              @endif
            </p>
            <button type="button"
                    class="add-to-cart mt-1.5 inline-flex items-center gap-1 h-8 px-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold active:scale-95 transition"
                    aria-label="Add {{ $product->name }} to cart"
                    data-product-id="{{ $product->id }}"
                    data-title="{{ $product->name }}"
                    data-stock="{{ $product->stock_quantity }}"
                    data-price="{{ money($product->price) }}"
                    data-raw-price="{{ (float) $product->price }}"
                    data-regular-price="{{ $product->on_sale ? money($product->regular_price) : '' }}"
                    data-raw-regular-price="{{ $product->on_sale && $product->regular_price ? (float) $product->regular_price : '' }}"
                    data-discount="{{ $product->discount_percent }}"
                    data-image="{{ $img }}"
                    data-url="{{ route('product.show', $product) }}"
                    data-has-variants="{{ $variantsGrouped->isNotEmpty() ? 'true' : 'false' }}"
                    data-variants="{{ json_encode($variantsGrouped) }}"
                    data-skus="{{ json_encode($product->skus->map(fn ($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment, 'regular_price' => $s->getCalculatedRegularPrice(), 'sale_price' => $s->getCalculatedSalePrice()])->values()) }}">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
              Add
            </button>
          </div>
        </article>
      @endforeach
    </div>
  </div>
@endif
