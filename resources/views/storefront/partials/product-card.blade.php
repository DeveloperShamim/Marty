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
@endphp

<article class="fk-card product-card group relative flex flex-col bg-white rounded-2xl border border-stone-200/90 hover:border-brand-500/40 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
  {{-- Floating Badges --}}
  @if($isOutOfStock)
    <span class="absolute top-2.5 right-2.5 z-10 bg-stone-900/90 backdrop-blur-xs text-white font-extrabold text-[9px] sm:text-[10px] tracking-wide uppercase px-2.5 py-1 rounded-lg shadow-sm">Out of Stock</span>
  @else
    <div class="absolute top-2.5 left-2.5 z-10 flex flex-wrap gap-1.5 items-center pointer-events-none">
      @if($product->is_flash_sale)
        <span class="bg-gradient-to-r from-red-600 via-rose-600 to-amber-500 text-white font-black text-[9px] sm:text-[10px] tracking-wider uppercase px-2 py-1 rounded-lg shadow-sm flex items-center gap-1">
          <span>⚡ FLASH SALE</span>
          @if($discount > 0)
            <span class="bg-black/20 px-1 py-0.2 rounded font-black text-[9px]">· {{ $discount }}% OFF</span>
          @endif
        </span>
      @elseif($discount > 0)
        <span class="bg-rose-600 text-white font-black text-[10px] sm:text-[11px] px-2 py-0.5 rounded-lg shadow-sm tracking-tight">
          -{{ $discount }}%
        </span>
      @endif
    </div>
  @endif

  {{-- Media / Image Container --}}
  <a href="{{ route('product.show', $product) }}" class="fk-card-media relative overflow-hidden block aspect-square bg-gradient-to-b from-stone-50/80 to-stone-100/40 p-2 sm:p-3 group/img">
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-contain mix-blend-multiply transition-all duration-500 ease-out group-hover/img:scale-108 {{ $secondaryImg ? 'group-hover/img:opacity-0' : '' }} {{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}" />
    @if($secondaryImg)
      <img src="{{ $secondaryImg }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-contain mix-blend-multiply transition-all duration-500 ease-out opacity-0 group-hover/img:opacity-100 group-hover/img:scale-108 {{ $isOutOfStock ? 'grayscale-[40%]' : '' }}" />
    @endif
  </a>

  {{-- Card Content --}}
  <div class="fk-card-body p-2.5 sm:p-4 flex flex-col flex-1 text-left">
    {{-- Brand Label or Category --}}
    @if($brandName)
      <span class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider text-stone-400 truncate block mb-0.5">
        {{ $brandName }}
      </span>
    @elseif($product->category)
      <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-stone-400 truncate block mb-0.5">
        {{ $product->category->name }}
      </span>
    @endif

    {{-- Product Title --}}
    <a href="{{ route('product.show', $product) }}" class="fk-card-title text-left font-bold text-xs sm:text-sm text-stone-900 hover:text-brand-600 line-clamp-2 leading-snug min-h-[2.5em] transition-colors mb-1.5" title="{{ $product->name }}">
      {{ $product->name }}
    </a>

    {{-- Price Row --}}
    <div class="fk-card-price product-card-price flex items-baseline gap-1.5 sm:gap-2 flex-wrap mb-2">
      <span class="fk-price text-xs sm:text-base font-extrabold text-stone-900 tracking-tight">{{ money($product->price) }}</span>
      @if($product->on_sale)
        <span class="fk-price-was text-[10px] sm:text-xs text-stone-400 line-through font-medium">{{ money($product->regular_price) }}</span>
      @endif
    </div>

    {{-- Flash Sale Sold Urgency Bar --}}
    @if($flashCard)
      @php $progress = $product->calculatedFlashSaleProgress(); @endphp
      <div class="sold-container my-1 sm:my-1.5">
        <div class="flex items-center justify-between text-[9px] sm:text-[10px] font-bold text-stone-600 mb-1">
          <span class="flex items-center gap-1 text-amber-600 font-extrabold">
            <span>🔥</span> <span>Flash Deal</span>
          </span>
          <span class="text-stone-500 font-mono">{{ $progress }}% Sold</span>
        </div>
        <div class="relative w-full h-1.5 rounded-full bg-stone-100 overflow-hidden">
          <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 rounded-full transition-all duration-500" style="width:{{ $progress }}%"></div>
        </div>
      </div>
    @endif

    {{-- Add to Cart Action --}}
    @if($isOutOfStock)
      <button type="button" disabled class="w-full mt-auto py-2 px-2 sm:py-2.5 sm:px-3 rounded-xl bg-stone-100 text-stone-400 font-bold text-[11px] sm:text-xs cursor-not-allowed border border-stone-200 text-center pointer-events-none select-none" aria-disabled="true">
        Out of Stock
      </button>
    @else
      <button type="button" class="fk-add-btn add-to-cart w-full mt-auto py-2 px-2 sm:py-2.5 sm:px-3 rounded-xl font-extrabold text-[11px] sm:text-xs flex items-center justify-center gap-1 sm:gap-1.5 shadow-2xs hover:shadow-md active:scale-95 transition-all cursor-pointer" 
              data-product-id="{{ $product->id }}" 
              data-title="{{ $product->name }}" 
              data-stock="{{ $product->stock_quantity }}"
              data-price="{{ money($product->price) }}"
              data-raw-price="{{ (float) $product->price }}"
              data-regular-price="{{ $product->on_sale ? money($product->regular_price) : '' }}"
              data-raw-regular-price="{{ $product->on_sale && $product->regular_price ? (float) $product->regular_price : '' }}"
              data-discount="{{ $discount }}"
              data-image="{{ $img }}"
              data-url="{{ route('product.show', $product) }}"
              data-has-variants="{{ $hasVariants ? 'true' : 'false' }}"
              data-variants="{{ json_encode($variantsGrouped) }}"
              data-skus="{{ json_encode($product->skus ? $product->skus->map(fn($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment, 'regular_price' => $s->getCalculatedRegularPrice(), 'sale_price' => $s->getCalculatedSalePrice()])->values() : []) }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span>{{ $cta }}</span>
      </button>
    @endif
  </div>
</article>
