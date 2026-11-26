@php
  $img = $product->imageUrl();
  $secondaryImg = ($product->images && $product->images->count() > 1) ? image_url($product->images->get(1)?->path, $product->slug) : null;
  $discount = $product->discount_percent;
  $flashCard = $flashCard ?? false;
  $cta = setting('default_cta_text', 'Add to Cart');
  $isOutOfStock = $product->isOutOfStock();
  $brandName = is_object($product->brand) ? ($product->brand->name ?? null) : (string) ($product->brand ?: '');
  $variantsGrouped = $product->variants ? $product->variants->groupBy('type')->map(fn($items) => $items->pluck('value')->unique()->values()) : collect();
  if ($variantsGrouped->isEmpty() && $product->skus && $product->skus->isNotEmpty()) {
      $skusGrouped = [];
      foreach ($product->skus as $sku) {
          $attrs = $sku->getAttributesData();
          foreach ($attrs as $type => $val) {
              $skusGrouped[$type][] = $val;
          }
      }
      $variantsGrouped = collect($skusGrouped)->map(fn($vals) => collect($vals)->unique()->values());
  }
  $hasVariants = $variantsGrouped->isNotEmpty();
  $opts = \App\Support\ProductCardOptions::for($product);
  $wide = $wide ?? false;
@endphp

{{-- Product card, as on ghorerbazar.com: white bordered card, picture, name, orange price with the old price struck
     through and one full-width Add To Cart. The wide Top Selling card adds a Save pill and Buy now.
     A product with options opens the option picker. --}}
<article class="fk-card product-card gb-card {{ $wide ? 'gb-card-wide' : '' }} group">
  @if($wide && ! $isOutOfStock && $product->is_best_seller)
    {{-- Top Selling card, as on ghorerbazar.com: red "Best Selling" badge with a flame in the card's top right corner --}}
    <span class="gb-ribbon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22c4 0 7-2.7 7-7 0-3.5-2.2-5.6-3.6-7.4-.4 1.7-1.4 3-2.9 3.4.4-3.3-1-6.3-3.5-8-.2 3-2.1 4.9-3.6 6.7C4.1 11.2 5 13 5 15c0 4.3 3 7 7 7Z"/><path d="M12 22c-1.7 0-3-1.2-3-3 0-1.6 1.2-2.7 2-3.6.4 1 1.2 1.6 2 1.8.8.4 2 1.2 2 2.8 0 1.2-1.3 2-3 2Z"/></svg>Best Selling</span>
  @endif
  <a href="{{ route('product.show', $product) }}" class="gb-card-media">
    @if($wide)
      @if($isOutOfStock)
        <span class="gb-badge-out">Sold out</span>
      @endif
    @else
      {{-- Grid card: orange tag top left (Best Selling / New Arrival / Flash Sale / Free Delivery), green "Save X%" top right --}}
      @if($isOutOfStock)
        <span class="gb-tag gb-tag-out">Sold out</span>
      @elseif($product->is_flash_sale && flash_sale_running())
        <span class="gb-tag gb-badge-flash">Flash Sale</span>
      @elseif($product->is_best_seller)
        <span class="gb-tag">Best Selling</span>
      @elseif($product->is_new_arrival)
        <span class="gb-tag">New Arrival</span>
      @elseif($product->free_delivery)
        <span class="gb-tag gb-badge-free">Free Delivery</span>
      @endif
      @if($discount > 0 && ! $isOutOfStock)
        <span class="gb-save-tag">Save {{ $discount }}%</span>
      @endif
    @endif
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="{{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}" />
  </a>

  <div class="gb-card-body">
    <a href="{{ route('product.show', $product) }}" class="gb-card-title" title="{{ $product->name }}">{{ $product->name }}</a>

    <div class="gb-price fk-card-price product-card-price">
      @if($opts['fromPrice'])
        <span class="gb-price-now fk-price"><span class="text-xs font-medium text-stone-500 mr-0.5">From</span>{{ money($opts['fromPrice']) }}</span>
      @else
        <span class="gb-price-now fk-price">{{ money($product->price) }}</span>
      @endif
      @if($product->on_sale && ! $opts['fromPrice'])
        <span class="gb-price-was fk-price-was">{{ money($product->regular_price) }}</span>
        @if($wide && $product->regular_price > $product->price)
          <span class="gb-save">Save {{ money($product->regular_price - $product->price) }}</span>
        @endif
      @endif
    </div>

    @if($flashCard)
      @php $fs = $product->flashStats(); @endphp
      @if($fs['percent'] !== null)
        <div class="mb-3">
          <div class="flex items-center justify-between text-[11px] font-semibold text-stone-500 mb-1">
            <span style="color: var(--brand-primary)">Flash Deal</span>
            @if($fs['left'] > 0 && $fs['left'] <= 10)<span class="text-rose-600">Only {{ $fs['left'] }} left</span>@elseif($fs['sold'] > 0)<span>{{ $fs['sold'] }} sold</span>@endif
          </div>
          <div class="relative w-full h-1.5 rounded-full bg-stone-100 overflow-hidden"><div class="absolute inset-y-0 left-0 rounded-full" style="width:{{ $fs['percent'] }}%; background: var(--brand-primary);"></div></div>
        </div>
      @endif
    @endif

    @php
      $cartData = [
        'product-id' => $product->id, 'title' => $product->name, 'stock' => $product->stock_quantity,
        'price' => money($product->price), 'raw-price' => (float) $product->price,
        'regular-price' => $product->on_sale ? money($product->regular_price) : '',
        'raw-regular-price' => $product->on_sale && $product->regular_price ? (float) $product->regular_price : '',
        'discount' => $discount, 'image' => $img, 'url' => route('product.show', $product),
        'has-variants' => $hasVariants ? 'true' : 'false', 'variants' => json_encode($variantsGrouped),
        'skus' => json_encode($product->skus ? $product->skus->map(fn($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment, 'regular_price' => $s->getCalculatedRegularPrice(), 'sale_price' => $s->getCalculatedSalePrice()])->values() : []),
      ];
    @endphp
    <div class="gb-actions {{ $wide ? '' : 'gb-actions-one' }} relative z-10">
      @if($isOutOfStock)
        <button type="button" disabled class="gb-btn gb-btn-sold">Sold out</button>
      @else
        <button type="button" data-add-cart class="gb-btn gb-btn-outline" @foreach($cartData as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach>
          <svg class="gb-btn-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 3.5h2.6l2.3 11.2a1.6 1.6 0 0 0 1.6 1.3h8.6a1.6 1.6 0 0 0 1.6-1.2L21 7H6"/></svg>
          <span>{{ $cta }}</span>
        </button>
        @if($wide)
        <button type="button" class="add-to-cart gb-btn gb-btn-solid btn-view-details" data-order-now="true" @foreach($cartData as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach>
          <svg class="gb-btn-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.2"/><circle cx="18" cy="20" r="1.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 3.5h2.6l2.3 11.2a1.6 1.6 0 0 0 1.6 1.3h8.6a1.6 1.6 0 0 0 1.6-1.2L21 7H6"/></svg>
          <span>Buy now</span>
        </button>
        @endif
      @endif
    </div>
  </div>
</article>
