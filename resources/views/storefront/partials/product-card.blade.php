@php
  $img = $product->imageUrl();
  $secondaryImg = ($product->images && $product->images->count() > 1) ? image_url($product->images->get(1)?->path, $product->slug) : null;
  $discount = $product->discount_percent;
  $flashCard = $flashCard ?? false;
  $cta = setting('default_cta_text', 'Add to Cart');
  $isOutOfStock = (int) $product->stock_quantity <= 0;
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
@endphp

<article class="fk-card product-card group">
  @if($isOutOfStock)
    <span class="absolute top-2.5 right-2.5 z-10 bg-stone-800/90 text-white font-extrabold text-[10px] tracking-wide uppercase px-2.5 py-1 rounded-md shadow-sm">Out of Stock</span>
  @else
    <div class="absolute top-2.5 left-2.5 z-10 flex flex-col gap-1 items-start pointer-events-none">
      @if($product->is_flash_sale)
        <span class="bg-gradient-to-r from-red-600 to-amber-500 text-white font-black text-[9px] sm:text-[10px] tracking-wider uppercase px-2.5 py-1 rounded-md shadow-md flex items-center gap-1 animate-pulse">⚡ FLASH SALE</span>
      @endif
      @if($discount > 0)
        <span class="fk-badge static">{{ $discount }}% OFF</span>
      @endif
    </div>
  @endif

  <a href="{{ route('product.show', $product) }}" class="fk-card-media relative overflow-hidden block rounded-none aspect-square bg-stone-100 group/img">
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-500 ease-out group-hover/img:scale-110 {{ $secondaryImg ? 'group-hover/img:opacity-0' : '' }} {{ $isOutOfStock ? 'opacity-75 grayscale-[30%]' : '' }}" />
    @if($secondaryImg)
      <img src="{{ $secondaryImg }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover transition-all duration-500 ease-out opacity-0 group-hover/img:opacity-100 group-hover/img:scale-110 {{ $isOutOfStock ? 'grayscale-[30%]' : '' }}" />
    @endif
  </a>

  <div class="fk-card-body">
    <a href="{{ route('product.show', $product) }}" class="fk-card-title">{{ $product->name }}</a>

    <div class="fk-card-price product-card-price">
      @if($product->on_sale)
        <span class="whitespace-nowrap fk-price">{{ money($product->price) }}</span>
        <span class="whitespace-nowrap fk-price-was">{{ money($product->regular_price) }}</span>
      @else
        <span class="whitespace-nowrap fk-price">{{ money($product->price) }}</span>
      @endif
    </div>

    @if($flashCard && $product->flash_sale_progress !== null)
      @php $progress = max(0, min(100, (int) $product->flash_sale_progress)); @endphp
      <div class="sold-track mb-2 relative mx-auto w-full h-2 rounded bg-stone-100 overflow-hidden">
        <div class="sold-fill absolute inset-y-0 left-0 bg-brand-500" style="width:{{ $progress }}%"></div>
      </div>
      <p class="text-[10px] font-bold text-stone-500 -mt-1">{{ $progress }}% sold</p>
    @endif

    @if($isOutOfStock)
      <button type="button" disabled class="w-full py-2.5 px-3 rounded-lg bg-stone-100 text-stone-400 font-bold text-xs cursor-not-allowed border border-stone-200" aria-disabled="true">
        Out of Stock
      </button>
    @else
      <button type="button" class="fk-add-btn add-to-cart" 
              data-product-id="{{ $product->id }}" 
              data-title="{{ $product->name }}" 
              data-price="{{ money($product->price) }}"
              data-raw-price="{{ (float) $product->price }}"
              data-regular-price="{{ $product->on_sale ? money($product->regular_price) : '' }}"
              data-raw-regular-price="{{ $product->on_sale && $product->regular_price ? (float) $product->regular_price : '' }}"
              data-discount="{{ $discount }}"
              data-image="{{ $img }}"
              data-url="{{ route('product.show', $product) }}"
              data-has-variants="{{ $hasVariants ? 'true' : 'false' }}"
              data-variants="{{ json_encode($variantsGrouped) }}"
              data-skus="{{ json_encode($product->skus && $product->skus->isNotEmpty() ? $product->skus->map(fn($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment])->values() : ($product->variants ? $product->variants->map(fn($v) => ['id' => null, 'attributes' => [$v->type => $v->value], 'stock' => (int) $v->stock, 'price_adjustment' => (float) $v->price_delta])->values() : [])) }}">
        <span class="fk-add-plus">+</span> {{ $cta }}
      </button>
    @endif
  </div>
</article>
