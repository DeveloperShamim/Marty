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
@endphp

<article class="fk-card product-card group relative flex flex-col bg-white rounded-2xl border border-stone-200/90 hover:border-brand-500/40 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
  {{-- Floating Badges --}}
  @if($isOutOfStock)
    <span class="absolute top-2.5 right-2.5 z-10 bg-stone-900/90 backdrop-blur-xs text-white font-extrabold text-[9px] sm:text-[10px] tracking-wide uppercase px-2.5 py-1 rounded-lg shadow-sm">Out of Stock</span>
  @else
    <div class="absolute top-2.5 left-2.5 z-10 flex flex-wrap gap-1.5 items-center pointer-events-none">
      @if($product->is_flash_sale)
        <span class="text-white font-extrabold text-[9px] sm:text-[10px] tracking-wider uppercase pl-2 pr-1 py-1 rounded-md shadow-sm flex items-center gap-1.5" style="background-color: var(--brand-dark, #1c1917);">
          <span class="flex items-center gap-1"><svg class="w-2.5 h-2.5 text-amber-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>Flash Sale</span>
          @if($discount > 0)
            <span class="px-1.5 py-px rounded font-extrabold text-[9px] sm:text-[10px]" style="background-color: var(--brand-primary, #8B5A2B);">{{ $discount }}% OFF</span>
          @endif
        </span>
      @elseif($discount > 0)
        <span class="text-white font-extrabold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-md shadow-sm tracking-tight" style="background-color: var(--brand-dark, #1c1917);">
          -{{ $discount }}%
        </span>
      @endif
      @if($product->free_delivery)
        <span class="bg-emerald-600 text-white font-extrabold text-[9px] sm:text-[10px] tracking-wide uppercase px-2 py-1 rounded-lg shadow-sm">🚚 Free Delivery</span>
      @endif
    </div>
  @endif

  {{-- Media / Image Container (Full-bleed, edge-to-edge, zero blank space) --}}
  <a href="{{ route('product.show', $product) }}" class="fk-card-media relative overflow-hidden block aspect-square bg-stone-100/70 !p-0 group/img">
    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-500 ease-out group-hover/img:scale-105 {{ $secondaryImg ? 'group-hover/img:opacity-0' : '' }} {{ $isOutOfStock ? 'opacity-60 grayscale-[40%]' : '' }}" />
    @if($secondaryImg)
      <img src="{{ $secondaryImg }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover transition-all duration-500 ease-out opacity-0 group-hover/img:opacity-100 group-hover/img:scale-105 {{ $isOutOfStock ? 'grayscale-[40%]' : '' }}" />
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

    {{-- Variations at a glance: colour dots and a size line; the picker does the choosing --}}
    @if($opts['colors'] || $opts['sizeLabel'] || $opts['otherLabel'])
      <div class="flex items-center gap-1.5 min-w-0 mb-1.5 text-[10.5px] sm:text-[11px] text-stone-500 font-medium" data-card-options>
        @if($opts['colors'])
          <span class="flex items-center gap-1 shrink-0" aria-label="Colours: {{ collect($opts['colors'])->pluck('name')->join(', ') }}">
            @foreach($opts['colors'] as $c)
              <span class="h-3.5 w-3.5 rounded-full border border-stone-300/80 {{ $c['swatch'] ? '' : 'bg-stone-200' }}" @if($c['swatch']) style="background-color: {{ $c['swatch'] }}" @endif title="{{ $c['name'] }}"></span>
            @endforeach
          </span>
          @if($opts['moreColors'])<span class="shrink-0">+{{ $opts['moreColors'] }}</span>@endif
        @endif
        @if($opts['sizeLabel'])
          @if($opts['colors'])<span class="text-stone-300" aria-hidden="true">·</span>@endif
          <span class="truncate">{{ $opts['sizeLabel'] }}</span>
        @elseif($opts['otherLabel'])
          @if($opts['colors'])<span class="text-stone-300" aria-hidden="true">·</span>@endif
          <span class="truncate">{{ $opts['otherLabel'] }}</span>
        @endif
      </div>
    @endif

    {{-- Price Row: "From" when the variations cost different amounts --}}
    <div class="fk-card-price product-card-price flex items-baseline gap-1.5 sm:gap-2 flex-wrap mb-2">
      @if($opts['fromPrice'])
        <span class="fk-price text-xs sm:text-base font-extrabold text-stone-900 tracking-tight"><span class="text-[10px] sm:text-xs font-semibold text-stone-500 mr-0.5">From</span>{{ money($opts['fromPrice']) }}</span>
      @else
        <span class="fk-price text-xs sm:text-base font-extrabold text-stone-900 tracking-tight">{{ money($product->price) }}</span>
      @endif
      @if($product->on_sale && ! $opts['fromPrice'])
        <span class="fk-price-was text-[10px] sm:text-xs text-stone-400 line-through font-medium">{{ money($product->regular_price) }}</span>
      @endif
    </div>

    {{-- Flash deal bar: real sales and stock only --}}
    @if($flashCard)
      @php $fs = $product->flashStats(); @endphp
      <div class="sold-container my-1 sm:my-1.5">
        <div class="flex items-center justify-between text-[9px] sm:text-[10px] font-bold text-stone-600 mb-1">
          <span class="flex items-center gap-1 text-brand-600 font-extrabold">
            <span>🔥</span> <span>Flash Deal</span>
          </span>
          @if($fs['left'] > 0 && $fs['left'] <= 10)
            <span class="text-rose-600">Only {{ $fs['left'] }} left</span>
          @elseif($fs['sold'] > 0)
            <span class="text-stone-500">{{ $fs['sold'] }} sold</span>
          @endif
        </div>
        @if($fs['percent'] !== null)
          <div class="relative w-full h-1.5 rounded-full bg-stone-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $fs['percent'] }}" aria-valuemin="0" aria-valuemax="100">
            <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500" style="width:{{ $fs['percent'] }}%; background: linear-gradient(90deg, var(--brand-border, #d6c4b0), var(--brand-primary, #8B5A2B));"></div>
          </div>
        @endif
      </div>
    @endif

    {{-- Actions: Order now (opens the option picker when there are variations) plus a quick add-to-cart bag.
         The photo and name already open the product page. --}}
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
    @if($isOutOfStock)
      <div class="flex items-center mt-auto pt-1 w-full relative z-10">
        <button type="button" disabled class="flex-1 min-w-0 h-9 sm:h-10 bg-stone-100 text-stone-500 font-bold text-xs sm:text-[13px] flex items-center justify-center rounded-xl cursor-not-allowed select-none">Sold out</button>
      </div>
    @else
      <div class="flex items-center gap-2 sm:gap-2.5 mt-auto pt-1 w-full relative z-10">
        <button type="button" class="add-to-cart flex-1 min-w-0 h-9 sm:h-10 font-semibold text-xs sm:text-[13px] flex items-center justify-center gap-1.5 text-white transition-all shadow-xs hover:shadow-md active:scale-[0.98] select-none touch-manipulation cursor-pointer btn-view-details rounded-xl" style="background-color: var(--brand-primary, #1D68FE);"
                data-order-now="true" @foreach($cartData as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach>
          <span class="truncate">Order now</span>
        </button>
        <button type="button" class="fk-add-btn fk-icon-only add-to-cart w-9 sm:w-10 h-9 sm:h-10 text-white flex items-center justify-center shrink-0 shadow-xs hover:shadow-md active:scale-95 transition-all cursor-pointer select-none touch-manipulation rounded-xl"
                aria-label="Add to cart" title="Add to cart"
                @foreach($cartData as $k => $v) data-{{ $k }}="{{ $v }}" @endforeach>
          <svg class="w-[18px] h-[18px] shrink-0 relative z-10 pointer-events-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </button>
      </div>
    @endif
  </div>
</article>
