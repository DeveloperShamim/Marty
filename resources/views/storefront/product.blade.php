@extends('layouts.storefront')

@php
    $title = $product->name;
    $rawTitle = $product->meta_title ?: null;
    $metaDescription = $product->meta_description ?: $product->short_description;
    $metaKeywords = $product->meta_keywords;
    $ogImage = $product->imageUrl();
    $ogType = 'product';
    $bulletSpecs = collect($product->specificationBullets(6));
    if ($bulletSpecs->isEmpty()) {
        $bulletSpecs = collect(preg_split('/\r\n|\r|\n/', (string) ($product->short_description ?: '')))
            ->map(fn ($l) => trim($l, " \t-•"))
            ->filter()
            ->take(6)
            ->values();
    }
    $specRows = $product->specificationRows();
    $whatsapp = preg_replace('/\D+/', '', (string) setting('contact_phone', ''));
    $isOutOfStock = $product->isOutOfStock();
    $savings = $product->on_sale ? ($product->regular_price - $product->price) : 0;

    $brandObj = null;
    if (!empty($product->brand_id)) {
        $brandObj = \App\Models\Brand::find($product->brand_id);
    }
    if (!$brandObj && is_object($product->brand) && $product->brand instanceof \App\Models\Brand) {
        $brandObj = $product->brand;
    }
    if (!$brandObj && is_string($product->brand) && trim($product->brand) !== '') {
        $bName = trim($product->brand);
        $brandObj = \App\Models\Brand::where('name', 'LIKE', $bName)
            ->orWhere('slug', \Illuminate\Support\Str::slug($bName))
            ->first();

        if (!$brandObj) {
            $brandObj = \App\Models\Brand::create([
                'name' => $bName,
                'slug' => \Illuminate\Support\Str::slug($bName),
                'is_active' => true,
            ]);
        }
    }
@endphp

@section('content')
<main class="max-w-7xl mx-auto px-3.5 sm:px-5 lg:px-6 py-4 sm:py-6 pb-28 lg:pb-8">
  {{-- Clean Breadcrumb --}}
  <nav class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-stone-500 mb-4 sm:mb-6 flex-wrap">
    <a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Home</a>
    <span class="text-stone-300">/</span>
    @if($product->category)
      <a href="{{ route('shop.category', $product->category) }}" class="hover:text-brand-600 transition-colors">{{ $product->category->name }}</a>
      <span class="text-stone-300">/</span>
    @endif
    <span class="text-stone-800 font-semibold truncate max-w-[180px] sm:max-w-xs md:max-w-md">{{ $product->name }}</span>
  </nav>

  {{-- Main Product Card Container --}}
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200/80 p-4 sm:p-6 lg:p-8 flex flex-col lg:flex-row gap-6 md:gap-8 lg:gap-12 items-start shadow-xs">
    {{-- Left: Gallery (Horizontal Thumbnails on Mobile/Tablet, Vertical on Desktop) --}}
    <div class="flex flex-col-reverse lg:flex-row gap-3 sm:gap-4 items-start w-full lg:w-1/2 shrink-0">
      @if($product->images->count() > 0)
        <div class="flex lg:flex-col gap-2 sm:gap-2.5 overflow-x-auto lg:overflow-y-auto w-full lg:w-20 shrink-0 pb-1.5 lg:pb-0 max-h-[480px] no-scrollbar">
          @foreach($product->images as $img)
            <button type="button" data-thumb="{{ $img->url() }}" data-color="{{ strtolower(trim($img->color ?? '')) }}" data-variation-tag="{{ strtolower(trim($img->color ?? '')) }}" data-alt="{{ strtolower(trim($img->alt ?? '')) }}" class="gallery-thumb-btn w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 rounded-xl border {{ $loop->first ? 'border-stone-900 ring-2 ring-stone-900/10' : 'border-stone-200 opacity-75 hover:opacity-100 hover:border-stone-400' }} shrink-0 bg-white overflow-hidden p-1 transition-all focus:outline-none cursor-pointer">
              <img src="{{ $img->url() }}" loading="lazy" decoding="async" class="w-full h-full object-contain" alt="{{ $img->alt }}">
            </button>
          @endforeach
        </div>
      @endif

      <div class="flex-1 relative border border-stone-200/80 rounded-2xl sm:rounded-3xl aspect-square w-full bg-white overflow-hidden shadow-xs flex items-center justify-center group p-3 sm:p-5">
        {{-- Minimal Floating Badge --}}
        <div class="absolute top-3 sm:top-3.5 left-3 sm:left-3.5 z-10 pointer-events-none">
          @if($isOutOfStock)
            <span class="bg-stone-900 text-white font-bold text-[10px] sm:text-[11px] px-2.5 py-1 rounded-md tracking-tight uppercase">
              Out of Stock
            </span>
          @elseif($product->on_sale)
            <span id="pdImageDiscountBadge" class="bg-stone-900 text-white font-bold text-[10px] sm:text-[11px] px-2.5 py-1 rounded-md tracking-tight">
              {{ $product->discount_percent }}% OFF
            </span>
          @else
            <span id="pdImageDiscountBadge" class="hidden bg-stone-900 text-white font-bold text-[10px] sm:text-[11px] px-2.5 py-1 rounded-md tracking-tight">
              0% OFF
            </span>
          @endif
        </div>

        {{-- Mobile & Tablet Image Counter Pill --}}
        @if($product->images->count() > 1)
          <div id="galleryCounterPill" class="absolute bottom-3 left-3 z-10 bg-stone-900/75 backdrop-blur-xs text-white px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider font-mono pointer-events-none shadow-xs">
            <span id="galleryCurrentIdx">1</span> / {{ $product->images->count() }}
          </div>
        @endif

        @if($product->images->count() > 1)
          <button type="button" id="pdPrevImg" class="absolute left-2 sm:left-2.5 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/95 hover:bg-white shadow-md border border-stone-200 text-stone-700 hover:text-stone-900 flex items-center justify-center transition-all opacity-85 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 focus:outline-none cursor-pointer" aria-label="Previous Image">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="pdNextImg" class="absolute right-2 sm:right-2.5 top-1/2 -translate-y-1/2 z-10 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/95 hover:bg-white shadow-md border border-stone-200 text-stone-700 hover:text-stone-900 flex items-center justify-center transition-all opacity-85 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 focus:outline-none cursor-pointer" aria-label="Next Image">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        @endif

        {{-- Zoom Hint (Desktop only) --}}
        <div class="hidden sm:flex absolute bottom-3 right-3 z-10 bg-white/90 backdrop-blur-xs text-stone-500 px-2 py-1 rounded-lg border border-stone-200/80 pointer-events-none items-center gap-1 text-[11px] font-medium opacity-0 group-hover:opacity-100 transition-opacity">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
          <span>Zoom</span>
        </div>

        <img id="galleryMain" data-gallery-main src="{{ $product->imageUrl() }}" loading="lazy" decoding="async" class="w-full h-full object-contain select-none {{ $isOutOfStock ? 'opacity-70 grayscale-[30%]' : '' }}" alt="{{ $product->name }}" />
      </div>
    </div>

    {{-- Right: Modern Clean Product Info Panel --}}
    <div class="w-full lg:w-1/2 space-y-4">
      <div>
        {{-- Clean Category & Stock Line --}}
        <div class="flex items-center justify-between gap-3 text-xs text-stone-500 font-medium pb-1.5">
          <div class="flex items-center gap-2 flex-wrap">
            @if($brandObj)
              <a href="{{ route('shop.brand', $brandObj) }}" class="font-bold text-stone-900 hover:text-brand-600 transition-colors uppercase tracking-wider text-xs">{{ $brandObj->name }}</a>
            @elseif($product->brand)
              <span class="font-bold text-stone-900 uppercase tracking-wider text-xs">{{ $product->brand }}</span>
            @endif

            @if($product->category)
              <span class="text-stone-300">·</span>
              <a href="{{ route('shop.category', $product->category) }}" class="hover:text-stone-800 transition-colors">{{ $product->category->name }}</a>
            @endif
          </div>

          <div>
            @if($isOutOfStock)
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
              </span>
            @elseif($product->stock_quantity > 0 && $product->stock_quantity <= 5)
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Only {{ $product->stock_quantity }} Left
              </span>
            @else
              <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> In Stock
              </span>
            @endif
          </div>
        </div>

        {{-- Product Title --}}
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-stone-900 tracking-tight leading-snug">{{ $product->name }}</h1>

        {{-- Authentic & SKU Row --}}
        <div class="flex items-center gap-2 sm:gap-2.5 mt-1.5 sm:mt-2 text-xs text-stone-500 flex-wrap">
          <span class="text-emerald-700 font-medium">✓ Verified Authentic</span>
          @if($product->sku)
            <span class="text-stone-300">·</span>
            <span class="text-stone-400">SKU: <span class="font-mono text-stone-600">{{ $product->sku }}</span></span>
          @endif
        </div>

        {{-- Pricing Row --}}
        @php
          $skusPayload = $product->skus->map(fn($s) => [
            'id'               => $s->id,
            'attributes'       => $s->getAttributesData(),
            'stock'            => (int) $s->stock_quantity,
            'price_adjustment' => (float) $s->price_adjustment,
            'regular_price'    => $s->getCalculatedRegularPrice(),
            'sale_price'       => $s->getCalculatedSalePrice(),
          ])->values();
        @endphp

        <div id="pdpPriceContainer" class="flex items-baseline gap-2.5 sm:gap-3 flex-wrap pt-2.5 sm:pt-3 pb-1" data-skus="{{ json_encode($skusPayload) }}">
          <span id="pdPrice" class="text-2xl sm:text-3xl lg:text-4xl font-black text-stone-900 tracking-tight" data-base-price="{{ (float) $product->price }}">{{ money($product->price) }}</span>
          <span id="pdRegularPrice" class="text-stone-400 line-through text-sm sm:text-base font-normal {{ $product->on_sale ? '' : 'hidden' }}" data-base-regular="{{ (float) ($product->regular_price ?? 0) }}">{{ money($product->regular_price) }}</span>
          @if($product->on_sale)
            <span id="pdDiscountBadge" class="inline-flex items-center text-[11px] sm:text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2.5 py-0.5 rounded-full">
              Save {{ money($savings) }} ({{ $product->discount_percent }}% OFF)
            </span>
          @else
            <span id="pdDiscountBadge" class="hidden inline-flex items-center text-[11px] sm:text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-2.5 py-0.5 rounded-full"></span>
          @endif
        </div>
      </div>

      {{-- Refined Flash Sale Urgency Strip --}}
      @if($product->is_flash_sale)
        @php
          $flashEndsAt = setting('flash_sale_ends_at');
          $flashEndsIso = $flashEndsAt ? \Illuminate\Support\Carbon::parse($flashEndsAt)->toIso8601String() : null;
          $progress = $product->calculatedFlashSaleProgress();
          $flashStock = $product->skus()->exists() ? (int) $product->skus()->sum('stock_quantity') : (int) $product->stock_quantity;
          $diffDays = $flashEndsAt ? now()->diffInDays(\Illuminate\Support\Carbon::parse($flashEndsAt), false) : -1;
          $showLiveTimer = $diffDays >= 0 && $diffDays <= 14;
        @endphp
        <div class="rounded-xl bg-stone-50 border border-stone-200/80 p-3 space-y-2 my-2">
          <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-bold text-stone-900 flex items-center gap-1.5">
              <span>⚡</span>
              <span class="uppercase tracking-wider text-[11px]">Limited Time Deal</span>
            </span>
            @if($showLiveTimer)
              <div class="flex items-center gap-1 text-[11px] font-mono text-stone-600 bg-white border border-stone-200/80 px-2 py-0.5 rounded-md" data-pdp-flash-timer data-ends-at="{{ $flashEndsIso }}">
                <span class="text-stone-400 font-sans text-[10px] mr-0.5">Ends in:</span>
                <span data-timer-days class="font-bold text-stone-900">00</span><span class="text-stone-400 text-[10px]">d</span> :
                <span data-timer-hours class="font-bold text-stone-900">00</span><span class="text-stone-400 text-[10px]">h</span> :
                <span data-timer-mins class="font-bold text-stone-900">00</span><span class="text-stone-400 text-[10px]">m</span> :
                <span data-timer-secs class="font-bold text-brand-600">00</span><span class="text-stone-400 text-[10px]">s</span>
              </div>
            @else
              <span class="text-xs font-semibold text-brand-600">Selling fast</span>
            @endif
          </div>
          <div class="space-y-1">
            <div class="flex justify-between items-center text-[11px]">
              <span class="text-stone-500">{{ $progress }}% claimed</span>
              <span class="text-stone-700 font-medium">
                @if($flashStock > 0)
                  Only {{ $flashStock }} left at this price
                @else
                  Limited stock
                @endif
              </span>
            </div>
            <div class="w-full h-1.5 bg-stone-200 rounded-full overflow-hidden">
              <div class="h-full bg-brand-500 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
            </div>
          </div>
        </div>
      @endif

      <hr class="border-stone-100 my-3" />

      {{-- Dynamic Variants --}}
      @if(isset($variantGroups) && $variantGroups->isNotEmpty())
        @foreach($variantGroups as $groupType => $group)
          @php
            $groupLower = strtolower($groupType);
            $isSizeRelated = (str_contains($groupLower, 'size') || str_contains($groupLower, 'dimension') || str_contains($groupLower, 'fitting') || str_contains($groupLower, 'length') || str_contains($groupLower, 'waist') || str_contains($groupLower, 'chest')) && !str_contains($groupLower, 'color') && !str_contains($groupLower, 'colour');
          @endphp
          <div data-variant-group="{{ $groupType }}" class="mb-3.5">
            <div class="flex items-center justify-between mb-2">
              <div class="text-xs font-bold uppercase tracking-wider text-stone-500 flex items-center gap-1">
                <span>{{ $groupType }}:</span>
                <span class="text-stone-900 font-extrabold normal-case text-xs tracking-normal" data-selected-val-hint></span>
              </div>
              @if(setting('size_guide_enabled', '1') === '1' && $isSizeRelated)
                <button type="button" data-open-size-guide data-category-hint="{{ $product->category->name ?? $groupType }}" class="text-[11px] font-semibold text-stone-500 hover:text-stone-950 underline underline-offset-2 transition-colors cursor-pointer" title="Open Size Guide">
                  Size Guide
                </button>
              @endif
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-2.5">
              @foreach($group->options as $optValue)
                <button type="button" class="variant-btn min-h-[38px] px-3.5 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all border border-stone-200 bg-stone-50/60 hover:bg-white text-stone-800 hover:border-stone-400 active:scale-95 cursor-pointer shadow-2xs flex items-center justify-center select-none" data-type="{{ $groupType }}" data-value="{{ $optValue }}">{{ $optValue }}</button>
              @endforeach
            </div>
          </div>
        @endforeach
      @endif

      {{-- Validation Error Alert Box --}}
      <div id="pdpErrorAlert" class="hidden bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold p-3 rounded-xl flex items-center gap-2 my-1">
        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="pdpErrorMessage">Please select a variation option before adding to cart.</span>
      </div>

      {{-- Unified Action Bar: Quantity + Add to Cart + Buy Now --}}
      <div class="space-y-2.5 pt-1">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
          {{-- Quantity Stepper --}}
          <div data-qty data-stepper class="inline-flex items-center justify-between border border-stone-200 rounded-xl bg-stone-50/50 hover:bg-white overflow-hidden shrink-0 h-11 sm:h-12 shadow-2xs self-start sm:self-auto w-32 sm:w-auto">
            <button type="button" data-dec class="w-9 sm:w-10 h-full text-stone-600 hover:text-stone-950 font-extrabold text-base hover:bg-stone-100 transition-colors focus:outline-none cursor-pointer flex items-center justify-center select-none" aria-label="Decrease quantity">−</button>
            <input id="pdQty" value="1" min="1" max="3" class="w-8 text-center border-0 font-bold text-stone-900 focus:outline-none text-sm bg-transparent" readonly />
            <button type="button" data-inc class="w-9 sm:w-10 h-full text-stone-600 hover:text-stone-950 font-extrabold text-base hover:bg-stone-100 transition-colors cursor-pointer flex items-center justify-center select-none" aria-label="Increase quantity">+</button>
          </div>

          {{-- Balanced 50/50 CTA Buttons Grid --}}
          <div id="mainProductActions" class="flex items-center gap-2.5 sm:gap-3 flex-1">
            {{-- Add to Cart (Brand Colored) --}}
            <button type="button" id="pdAddToCart" data-product-id="{{ $product->id }}" data-title="{{ $product->name }}" class="flex-1 h-11 sm:h-12 bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-extrabold rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 text-xs sm:text-sm uppercase tracking-wider cursor-pointer disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none" @disabled($isOutOfStock)>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              <span id="pdAddToCartText">{{ $isOutOfStock ? 'OUT OF STOCK' : setting('default_cta_text', 'ADD TO CART') }}</span>
            </button>

            {{-- Buy Now (Solid Black with Eye-Catching Motion) --}}
            <button type="button" id="pdBuyNow" data-buy-now data-product-id="{{ $product->id }}" data-title="{{ $product->name }}" data-checkout-url="{{ route('checkout.show') }}" class="buy-now-cta-effect flex-1 h-11 sm:h-12 bg-stone-950 hover:bg-black text-white font-extrabold rounded-xl transition-all flex items-center justify-center gap-2 text-xs sm:text-sm uppercase tracking-wider disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none cursor-pointer select-none" @disabled($isOutOfStock)>
              <span id="pdBuyNowText">{{ $isOutOfStock ? 'OUT OF STOCK' : 'BUY NOW' }}</span>
            </button>
          </div>
        </div>

        @if($whatsapp)
          <a href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Hi, I want to inquire about: '.$product->name.' - '.url()->current()) }}" target="_blank" rel="noopener" class="w-full h-10 sm:h-11 bg-emerald-50/60 hover:bg-emerald-100/70 border border-emerald-200/80 hover:border-emerald-300 text-emerald-900 font-bold rounded-xl transition-all flex items-center justify-center gap-2 text-xs shadow-2xs">
            <svg class="w-4 h-4 text-emerald-600 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.19 4.18-1.096z"/></svg>
            <span>Order or Inquire on WhatsApp</span>
          </a>
        @endif
      </div>

      {{-- Clean Trust Guarantee Strip --}}
      <div class="pt-4 border-t border-stone-100">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-2.5">
          <div class="flex items-center gap-2 sm:gap-2.5 bg-stone-50/70 p-2.5 rounded-xl border border-stone-100/90">
            <svg class="w-4 h-4 text-stone-800 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 leading-tight truncate">100% Genuine</p>
              <p class="text-[10px] text-stone-400 mt-0.5 truncate">Authentic Item</p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-2.5 bg-stone-50/70 p-2.5 rounded-xl border border-stone-100/90">
            <svg class="w-4 h-4 text-stone-800 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 leading-tight truncate">Fast Delivery</p>
              <p class="text-[10px] text-stone-400 mt-0.5 truncate">24–48h Nationwide</p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-2.5 bg-stone-50/70 p-2.5 rounded-xl border border-stone-100/90">
            <svg class="w-4 h-4 text-stone-800 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 leading-tight truncate">7 Days Return</p>
              <p class="text-[10px] text-stone-400 mt-0.5 truncate">Easy Replacement</p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-2.5 bg-stone-50/70 p-2.5 rounded-xl border border-stone-100/90">
            <svg class="w-4 h-4 text-stone-800 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 leading-tight truncate">Cash on Delivery</p>
              <p class="text-[10px] text-stone-400 mt-0.5 truncate">Pay on Arrival</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Bullet Highlights --}}
      @if($bulletSpecs->isNotEmpty())
        <div class="pt-3 border-t border-stone-100">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-400 mb-2">Key Highlights</p>
          <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-stone-600">
            @foreach($bulletSpecs as $bullet)
              <li class="flex items-start gap-1.5">
                <span class="text-stone-400 font-bold shrink-0">·</span>
                <span class="leading-tight">{{ $bullet }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>

  {{-- Luxury Product Information & Specifications Section --}}
  <section id="productDetailsSection" class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200/90 p-5 sm:p-7 lg:p-9 mt-6 sm:mt-8 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-stone-100 pb-5">
      <div>
        <h2 class="text-lg sm:text-xl md:text-2xl font-black text-stone-950 tracking-tight flex items-center gap-2.5">
          <span class="w-1.5 h-5 bg-stone-900 rounded-full"></span>
          <span>Product Overview &amp; Specifications</span>
        </h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-1">Full breakdown of features, technical specs, authenticity and delivery coverage</p>
      </div>

      {{-- Modern Pill Tabs --}}
      <div class="inline-flex p-1 bg-stone-100/90 rounded-2xl border border-stone-200/70 text-xs font-bold gap-1 self-start sm:self-auto flex-wrap" data-pdp-tabs>
        <button type="button" data-pdp-tab="description" class="pdp-tab-btn active px-3.5 sm:px-4 py-2 rounded-xl bg-white text-stone-950 shadow-xs font-extrabold transition-all cursor-pointer flex items-center gap-1.5">
          <span>📝</span>
          <span>Overview</span>
        </button>
        @if(! empty($specRows))
          <button type="button" data-pdp-tab="specs" class="pdp-tab-btn px-3.5 sm:px-4 py-2 rounded-xl text-stone-600 hover:text-stone-950 font-bold transition-all cursor-pointer flex items-center gap-1.5">
            <span>⚙️</span>
            <span>Specifications</span>
            <span class="ml-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-stone-200 text-stone-700">{{ count($specRows) }}</span>
          </button>
        @endif
        <button type="button" data-pdp-tab="delivery" class="pdp-tab-btn px-3.5 sm:px-4 py-2 rounded-xl text-stone-600 hover:text-stone-950 font-bold transition-all cursor-pointer flex items-center gap-1.5">
          <span>🛡️</span>
          <span>Shipping &amp; Warranty</span>
        </button>
      </div>
    </div>

    {{-- Tab 1: Description & Overview --}}
    <div id="pdpTabPaneDescription" class="pdp-tab-pane space-y-5">
      @if($product->description)
        @if(strip_tags($product->description) !== $product->description)
          <div class="product-rich-description prose prose-stone max-w-none text-stone-700 leading-relaxed text-xs sm:text-sm md:text-base space-y-4">
            {!! $product->description !!}
          </div>
        @else
          <div class="text-stone-700 leading-relaxed max-w-4xl space-y-3.5 text-xs sm:text-sm md:text-base">
            @foreach(preg_split('/\n\n+/', (string) $product->description) as $para)
              @if(trim($para))<p>{{ $para }}</p>@endif
            @endforeach
          </div>
        @endif
      @else
        <p class="text-stone-400 italic text-sm">No detailed description provided for this item yet.</p>
      @endif
    </div>

    {{-- Tab 2: Specifications --}}
    @if(! empty($specRows))
      <div id="pdpTabPaneSpecs" class="pdp-tab-pane hidden space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-stone-200/90 shadow-2xs bg-white">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <tbody>
              <tr class="bg-stone-50/80 border-b border-stone-200/90">
                <th colspan="2" class="px-4 py-2.5 font-extrabold text-stone-800 uppercase tracking-wider text-[11px]">Key Information</th>
              </tr>
              @if($product->brand)
                <tr class="border-b border-stone-100 hover:bg-stone-50/40 transition-colors">
                  <td class="px-4 py-3 w-36 sm:w-52 font-semibold text-stone-500">Brand / Manufacturer</td>
                  <td class="px-4 py-3 font-bold text-stone-900">{{ $product->brand }}</td>
                </tr>
              @endif
              <tr class="border-b border-stone-100 hover:bg-stone-50/40 transition-colors">
                <td class="px-4 py-3 w-36 sm:w-52 font-semibold text-stone-500">Category</td>
                <td class="px-4 py-3 font-bold text-stone-900">{{ $product->category?->name ?: 'General' }}</td>
              </tr>
              @if($product->sku)
                <tr class="border-b border-stone-100 hover:bg-stone-50/40 transition-colors">
                  <td class="px-4 py-3 w-36 sm:w-52 font-semibold text-stone-500">Product SKU</td>
                  <td class="px-4 py-3 font-mono font-bold text-xs text-stone-800">{{ $product->sku }}</td>
                </tr>
              @endif
              @if($product->unit)
                <tr class="border-b border-stone-100 hover:bg-stone-50/40 transition-colors">
                  <td class="px-4 py-3 w-36 sm:w-52 font-semibold text-stone-500">Unit / Packing</td>
                  <td class="px-4 py-3 font-bold text-stone-900">{{ $product->unit }}</td>
                </tr>
              @endif

              <tr class="bg-stone-50/80 border-b border-stone-200/90">
                <th colspan="2" class="px-4 py-2.5 font-extrabold text-stone-800 uppercase tracking-wider text-[11px]">Technical Specifications</th>
              </tr>
              @foreach($specRows as $row)
                <tr class="border-b border-stone-100 hover:bg-stone-50/40 transition-colors">
                  <td class="px-4 py-3 font-semibold text-stone-500">{{ $row['label'] ?: 'Detail' }}</td>
                  <td class="px-4 py-3 font-bold text-stone-900">{{ $row['value'] ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

    {{-- Tab 3: Shipping & Warranty --}}
    <div id="pdpTabPaneDelivery" class="pdp-tab-pane hidden space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-extrabold text-sm">✓</div>
          <div>
            <h4 class="text-sm font-bold text-stone-900 mb-0.5">100% Verified Authentic</h4>
            <p class="text-xs text-stone-500 leading-relaxed">Direct genuine source with complete packaging, serial verification and official standards.</p>
          </div>
        </div>
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 font-extrabold text-sm">🚚</div>
          <div>
            <h4 class="text-sm font-bold text-stone-900 mb-0.5">Fast Nationwide Delivery</h4>
            <p class="text-xs text-stone-500 leading-relaxed">24–48 hours inside Dhaka Metro and 48–72 hours across all 64 districts with live tracking updates.</p>
          </div>
        </div>
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-extrabold text-sm">💳</div>
          <div>
            <h4 class="text-sm font-bold text-stone-900 mb-0.5">Cash on Delivery &amp; Mobile Pay</h4>
            <p class="text-xs text-stone-500 leading-relaxed">Inspect your package upon arrival. Pay cash on delivery or instant bKash, Nagad, or Cards.</p>
          </div>
        </div>
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 font-extrabold text-sm">🔄</div>
          <div>
            <h4 class="text-sm font-bold text-stone-900 mb-0.5">7-Day Replacement Support</h4>
            <p class="text-xs text-stone-500 leading-relaxed">In the rare event of transit damage or manufacturing defect, our support team replaces it immediately.</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Clean Scoped CSS for Rich Content --}}
    <style>
      .product-rich-description img {
        max-width: 100%;
        height: auto;
        border-radius: 0 !important;
        margin: 1.25rem 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
      }
      .product-rich-description iframe {
        max-width: 100%;
        border-radius: 0 !important;
      }
      .product-rich-description video {
        max-width: 100%;
        border-radius: 0 !important;
        margin: 1.25rem 0;
      }
      .product-rich-description .aspect-video {
        border-radius: 0 !important;
      }
      .product-rich-description figure {
        border-radius: 0 !important;
      }
      .product-rich-description table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.25rem 0;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e7e5e4;
      }
      .product-rich-description table th,
      .product-rich-description table td {
        padding: 0.625rem 0.875rem;
        border: 1px solid #e7e5e4;
      }
      .product-rich-description blockquote {
        border-left: 3px solid #0f766e;
        padding-left: 1rem;
        font-style: italic;
        color: #44403c;
        margin: 1rem 0;
      }
      .product-rich-description ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin: 0.75rem 0;
      }
      .product-rich-description ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin: 0.75rem 0;
      }
      .product-rich-description h2,
      .product-rich-description h3,
      .product-rich-description h4 {
        font-weight: 800;
        color: #0c0a09;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
      }
      .product-rich-description a {
        color: #0f766e;
        text-decoration: underline;
        font-weight: 600;
      }
      .product-rich-description .desc-media-toolbar {
        display: none !important;
      }

      /* Eye-Catching Shimmer & Breathing Motion on BUY NOW Button */
      @keyframes buyNowShimmer {
        0% {
          transform: translateX(-160%) skewX(-20deg);
        }
        26%, 100% {
          transform: translateX(260%) skewX(-20deg);
        }
      }

      @keyframes buyNowPulseMotion {
        0%, 100% {
          box-shadow: 0 4px 14px -1px rgba(0, 0, 0, 0.35), 0 0 0 0 rgba(24, 24, 27, 0.25);
          transform: scale(1);
        }
        50% {
          box-shadow: 0 8px 24px -1px rgba(0, 0, 0, 0.55), 0 0 0 4px rgba(24, 24, 27, 0.09);
          transform: scale(1.02);
        }
      }

      .buy-now-cta-effect {
        position: relative !important;
        overflow: hidden !important;
        animation: buyNowPulseMotion 3.2s ease-in-out infinite;
      }

      .buy-now-cta-effect::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 60%;
        height: 100%;
        background: linear-gradient(
          90deg,
          rgba(255, 255, 255, 0) 0%,
          rgba(255, 255, 255, 0.32) 50%,
          rgba(255, 255, 255, 0) 100%
        );
        animation: buyNowShimmer 3.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        pointer-events: none;
      }

      .buy-now-cta-effect:disabled {
        animation: none !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background-color: #e7e5e4 !important;
        color: #a8a29e !important;
      }

      .buy-now-cta-effect:disabled::after {
        display: none !important;
      }

      .buy-now-cta-effect:hover {
        animation-play-state: paused;
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 28px -2px rgba(0, 0, 0, 0.65) !important;
      }

      .buy-now-cta-effect:active {
        transform: scale(0.97) !important;
      }
    </style>
  </section>

  {{-- Customer Reviews & Feedback Section --}}
  <section id="reviews" class="mt-10 rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-8 shadow-2xs space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
      <div>
        <div class="flex items-center gap-2.5">
          <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
          <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Customer Reviews &amp; Feedback</h2>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Authentic ratings from verified buyers who purchased this product</p>
      </div>

      <div class="flex items-center gap-3">
        @if(auth()->check() && $alreadyReviewed)
          <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-2xs">
            ✓ Feedback Submitted
          </span>
        @elseif(auth()->check() && ! $hasPurchased)
          <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-50 text-slate-500 border border-slate-200 text-xs font-medium" title="Only customers who purchased this item can leave a review">
            🔒 Verified Buyers Only
          </span>
        @else
          <button type="button" id="toggleReviewFormBtn" class="btn-shine inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-4 py-2.5 shadow-2xs transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Write a Review</span>
          </button>
        @endif
      </div>
    </div>

    @if(session('status'))
      <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm p-3.5 font-semibold">
        ✓ {{ session('status') }}
      </div>
    @endif

    {{-- Review Submission Form (Classic Card) --}}
    <div id="reviewFormDrawer" class="{{ $errors->has('review_body') ? '' : 'hidden' }} rounded-2xl bg-white border border-slate-200/90 p-5 sm:p-7 shadow-xs space-y-5">
      <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div>
          <h3 class="font-extrabold text-base sm:text-lg text-slate-900 flex items-center gap-2">
            <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
            <span>Write a Customer Review</span>
          </h3>
          <p class="text-xs text-slate-500 mt-0.5">Please share your honest feedback about this product</p>
        </div>
        <button type="button" id="closeReviewFormBtn" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors cursor-pointer">
          <span>Cancel</span> ✕
        </button>
      </div>

      <form method="POST" action="{{ route('product.reviews.store', $product) }}" class="space-y-4">
        @csrf

        {{-- Classic Interactive Star Rating --}}
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Rating <span class="text-red-500">*</span>
          </label>
          <input type="hidden" name="rating" id="reviewRatingInput" value="{{ old('rating', 5) }}">
          
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1" id="starRatingContainer">
              @for($i = 1; $i <= 5; $i++)
                <button type="button" data-star-value="{{ $i }}" class="star-rating-btn p-1 text-slate-200 hover:scale-110 transition-transform focus:outline-none cursor-pointer" aria-label="{{ $i }} Stars">
                  <svg class="w-7 h-7 star-svg {{ $i <= old('rating', 5) ? 'text-amber-400 fill-amber-400' : 'text-slate-200 fill-slate-200' }}" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </button>
              @endfor
            </div>
            <span id="starRatingLabel" class="text-xs font-extrabold text-amber-800 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-md">
              {{ old('rating', 5) }}.0 / 5.0 (Excellent)
            </span>
          </div>
        </div>

        {{-- Author Name & Email (No Headline Field) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Your Name <span class="text-red-500">*</span>
            </label>
            <input name="author_name" value="{{ old('author_name', auth()->user()?->name) }}" required placeholder="e.g. Asif Chowdhury" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
              Checkout Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="author_email" value="{{ old('author_email', auth()->user()?->email) }}" required placeholder="Enter email used when ordering" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all" />
          </div>
        </div>

        {{-- Feedback Body --}}
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
            Your Review <span class="text-red-500">*</span>
          </label>
          <textarea name="body" rows="4" required placeholder="Write your review here. What did you think about the product quality, fit, and delivery?" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/50 focus:outline-none transition-all">{{ old('body') }}</textarea>
          @error('review_body')
            <p class="text-xs text-red-600 mt-1.5 font-semibold flex items-center gap-1.5 bg-red-50 border border-red-200 p-2.5 rounded-xl">
              <span>⚠️</span> <span>{{ $message }}</span>
            </p>
          @enderror
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
          <span class="text-[11px] text-slate-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Verified purchase check enabled
          </span>
          <button type="submit" class="btn-shine rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 shadow-2xs transition-all cursor-pointer">
            Submit Review
          </button>
        </div>
      </form>
    </div>

    {{-- Reviews List Feed --}}
    @if($reviews->isNotEmpty())
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($reviews as $rev)
          <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1 text-amber-400 text-sm">
                @php $rScore = (int) ($rev->rating ?: 5); @endphp
                @for($s = 1; $s <= 5; $s++)
                  <span>{{ $s <= $rScore ? '★' : '☆' }}</span>
                @endfor
                <span class="text-xs font-bold text-slate-700 ml-1">{{ $rScore }}.0</span>
              </div>
              <span class="text-[11px] text-slate-400">{{ $rev->created_at?->diffForHumans() ?? 'Recent' }}</span>
            </div>

            @if($rev->title)
              <h4 class="font-extrabold text-sm text-slate-900 leading-snug">{{ $rev->title }}</h4>
            @endif

            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
              “{{ $rev->body }}”
            </p>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
              <div class="h-7 w-7 rounded-full bg-brand-50 text-brand-700 font-extrabold text-[11px] grid place-items-center shrink-0 border border-brand-200">
                {{ mb_strtoupper(mb_substr($rev->author_name, 0, 1)) }}
              </div>
              <span class="font-bold text-xs text-slate-800">{{ $rev->author_name }}</span>
              <span class="inline-flex items-center gap-0.5 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/70">
                ✓ Verified
              </span>
            </div>
          </div>
        @endforeach
      </div>

      @if($reviews->hasPages())
        <div class="pt-4 flex justify-center">
          {{ $reviews->links() }}
        </div>
      @endif
    @else
      <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center space-y-2">
        <div class="text-2xl">💬</div>
        <h4 class="font-bold text-sm text-slate-800">No customer reviews yet</h4>
        <p class="text-xs text-slate-500">Have you purchased this product? Be the first to share your feedback!</p>
      </div>
    @endif
  </section>

  {{-- Related Products --}}
  @if($related->isNotEmpty())
    <section class="mt-10">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900">Related Products</h2>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-1 text-sm font-bold text-brand-600 hover:underline">
          See All <span class="text-base">→</span>
        </a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($related as $rel)
          @include('storefront.partials.product-card', ['product' => $rel])
        @endforeach
      </div>
    </section>
  @endif

  {{-- Image Lightbox Modal --}}
  <div id="imageLightboxModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity duration-300 opacity-0" aria-hidden="true">
    <div class="relative max-w-4xl w-full max-h-[90vh] bg-white rounded-2xl p-4 overflow-hidden flex flex-col items-center justify-center shadow-2xl">
      <button type="button" id="closeLightbox" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 font-extrabold flex items-center justify-center text-base transition-colors focus:outline-none" aria-label="Close Lightbox">✕</button>
      <img id="lightboxImg" src="" class="max-h-[82vh] w-auto h-auto object-contain rounded-xl" alt="Enlarged product image" />
    </div>
  </div>

  {{-- Mobile & Tablet Floating Action Buttons (`lg:hidden`) - Floating with No Background Below --}}
  <div id="stickyMobileBar" class="lg:hidden fixed bottom-6 sm:bottom-7 left-4 right-4 sm:left-6 sm:right-6 z-50 pointer-events-none transition-all duration-300 ease-out transform translate-y-28 opacity-0" style="bottom: max(1.5rem, calc(env(safe-area-inset-bottom, 0px) + 1.25rem));">
    <div class="max-w-md mx-auto grid grid-cols-2 gap-2.5 sm:gap-3 w-full pointer-events-auto">
      {{-- Add to Cart (Brand Orange Floating Pill Button) --}}
      <button type="button" id="stickyBarAddToCart" class="btn-shine h-12 bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider rounded-2xl shadow-[0_8px_20px_rgba(234,88,12,0.38)] hover:shadow-[0_10px_25px_rgba(234,88,12,0.45)] transition-all flex items-center justify-center gap-2 cursor-pointer border border-white/20 disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none" @disabled($isOutOfStock)>
        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span id="stickyBarAddToCartText">{{ $isOutOfStock ? 'OUT OF STOCK' : 'ADD TO CART' }}</span>
      </button>

      {{-- Buy Now (Solid Black Floating Pill Button) --}}
      <button type="button" id="stickyBarBuyNow" class="buy-now-cta-effect h-12 bg-stone-950 hover:bg-black active:scale-[0.98] text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider rounded-2xl shadow-[0_8px_20px_rgba(0,0,0,0.35)] hover:shadow-[0_10px_25px_rgba(0,0,0,0.45)] transition-all flex items-center justify-center gap-1.5 cursor-pointer border border-white/10 disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none select-none" @disabled($isOutOfStock)>
        <span id="stickyBarBuyNowText">{{ $isOutOfStock ? 'OUT OF STOCK' : 'BUY NOW' }}</span>
      </button>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
(function() {
  const thumbBtns = Array.from(document.querySelectorAll('.gallery-thumb-btn'));
  const mainImg = document.getElementById('galleryMain');
  const prevBtn = document.getElementById('pdPrevImg');
  const nextBtn = document.getElementById('pdNextImg');
  const modal = document.getElementById('imageLightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const closeBtn = document.getElementById('closeLightbox');

  if (!thumbBtns.length || !mainImg) return;

  let currentIndex = 0;

  function setActiveImage(index) {
    if (index < 0) index = thumbBtns.length - 1;
    if (index >= thumbBtns.length) index = 0;
    currentIndex = index;

    const btn = thumbBtns[currentIndex];
    const src = btn.getAttribute('data-thumb');
    if (src) mainImg.src = src;

    const counterEl = document.getElementById('galleryCurrentIdx');
    if (counterEl) counterEl.textContent = currentIndex + 1;

    thumbBtns.forEach((x, i) => {
      const check = x.querySelector('[data-active-check]');
      if (check) check.remove();
      if (i === currentIndex) {
        x.classList.add('border-stone-900', 'ring-2', 'ring-stone-900/10');
        x.classList.remove('border-stone-200', 'opacity-75');
      } else {
        x.classList.remove('border-stone-900', 'ring-2', 'ring-stone-900/10');
        x.classList.add('border-stone-200', 'opacity-75');
      }
    });
  }

  thumbBtns.forEach((btn, index) => {
    btn.addEventListener('click', () => setActiveImage(index));
  });

  if (prevBtn) prevBtn.addEventListener('click', () => setActiveImage(currentIndex - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => setActiveImage(currentIndex + 1));

  // Touch Swipe Gesture for Mobile & Tablet
  const galleryFrame = mainImg.closest('.aspect-square') || mainImg.parentElement;
  if (galleryFrame) {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    galleryFrame.addEventListener('touchstart', (e) => {
      if (e.touches && e.touches[0]) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
      }
    }, { passive: true });

    galleryFrame.addEventListener('touchend', (e) => {
      if (e.changedTouches && e.changedTouches[0]) {
        touchEndX = e.changedTouches[0].clientX;
        touchEndY = e.changedTouches[0].clientY;
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;
        if (Math.abs(diffX) > 35 && Math.abs(diffX) > Math.abs(diffY)) {
          if (diffX < 0) {
            setActiveImage(currentIndex + 1);
          } else {
            setActiveImage(currentIndex - 1);
          }
        }
      }
    }, { passive: true });
  }

  if (mainImg && modal && lightboxImg) {
    mainImg.classList.add('cursor-pointer');
    mainImg.addEventListener('click', () => {
      lightboxImg.src = mainImg.src;
      modal.classList.remove('hidden');
      setTimeout(() => modal.classList.remove('opacity-0'), 10);
    });

    const hideModal = () => {
      modal.classList.add('opacity-0');
      setTimeout(() => modal.classList.add('hidden'), 300);
    };

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => {
      if (e.target === modal) hideModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) hideModal();
    });
  }

  window.selectProductImageByVariation = function(selectedAttrs, lastClickedVal) {
    if (!thumbBtns.length) return;

    // Collect all candidate values to test (last clicked value first, then remaining selected attributes)
    const candidates = [];
    if (lastClickedVal) {
      candidates.push(String(lastClickedVal).trim().toLowerCase());
    }
    if (selectedAttrs && typeof selectedAttrs === 'object') {
      Object.values(selectedAttrs).forEach(v => {
        if (v) {
          const s = String(v).trim().toLowerCase();
          if (!candidates.includes(s)) candidates.push(s);
        }
      });
    }

    if (!candidates.length) return;

    for (const target of candidates) {
      if (!target) continue;

      const foundIndex = thumbBtns.findIndex(btn => {
        const vTag = (btn.getAttribute('data-variation-tag') || btn.getAttribute('data-color') || '').trim().toLowerCase();
        const alt = (btn.getAttribute('data-alt') || '').trim().toLowerCase();

        // Exact variation tag match (e.g. 'xxl', 'xl', 'l', 'm', '500g', 'red')
        if (vTag && (vTag === target || vTag.split(/[\s,/-]+/).includes(target))) {
          return true;
        }

        // Alt text tag match (e.g. "Shoes — (XXL) — 1" or "Shoes XXL")
        if (alt && (alt.includes(`(${target})`) || alt.includes(`[${target}]`) || alt.includes(` ${target} `) || alt.endsWith(` ${target}`) || alt.startsWith(`${target} `) || alt === target)) {
          return true;
        }

        return false;
      });

      if (foundIndex !== -1) {
        setActiveImage(foundIndex);
        return;
      }
    }
  };

  window.selectProductImageByColor = function(colorVal) {
    window.selectProductImageByVariation({ color: colorVal }, colorVal);
  };
})();

(function() {
  window.productSkus = {!! $skusPayload->toJson() !!};
})();

function findPdpMatchingSku(skus, selectedAttrs) {
  if (!skus || skus.length === 0) return null;
  const selKeys = Object.keys(selectedAttrs).filter(k => selectedAttrs[k]);
  if (selKeys.length === 0) return null;

  return skus.find(sku => {
    let attrs = sku.attributes || {};
    if (typeof attrs === 'string') {
      try { attrs = JSON.parse(attrs); } catch (e) { attrs = {}; }
    }
    const attrMap = {};
    Object.keys(attrs).forEach(k => {
      attrMap[String(k).trim().toLowerCase()] = String(attrs[k]).trim().toLowerCase();
    });

    return selKeys.every(k => {
      const targetVal = String(selectedAttrs[k] || '').trim().toLowerCase();
      const kLower = String(k).trim().toLowerCase();
      if (attrMap[kLower] === undefined) return true;
      return attrMap[kLower] === targetVal;
    });
  }) || null;
}

function syncPdpVariantStockAndPrice(lastClickedVal) {
  const priceEl = document.getElementById('pdPrice');
  const regEl = document.getElementById('pdRegularPrice');
  const badgeEl = document.getElementById('pdDiscountBadge');
  const imgBadgeEl = document.getElementById('pdImageDiscountBadge');
  const addBtn = document.getElementById('pdAddToCart');
  const buyBtn = document.getElementById('pdBuyNow');
  const btnText = document.getElementById('pdAddToCartText');

  if (!priceEl) return;

  const basePrice = parseFloat(priceEl.getAttribute('data-base-price') || '0');
  const baseReg = regEl ? parseFloat(regEl.getAttribute('data-base-regular') || '0') : 0;

  // Selected attributes
  const selectedAttrs = {};
  document.querySelectorAll('[data-variant-group]').forEach(group => {
    const activeBtn = group.querySelector('.variant-btn.is-selected');
    if (activeBtn) {
      const type = activeBtn.getAttribute('data-type') || group.getAttribute('data-variant-group');
      const val = activeBtn.getAttribute('data-value');
      if (type && val) {
        selectedAttrs[type] = val;
      }
    }
  });

  // Switch image to match clicked / selected variation
  if (typeof window.selectProductImageByVariation === 'function') {
    window.selectProductImageByVariation(selectedAttrs, lastClickedVal);
  }

  // Update option availability dynamically across groups
  if (window.productSkus && window.productSkus.length > 0) {
    document.querySelectorAll('[data-variant-group]').forEach(group => {
      const gType = group.getAttribute('data-variant-group');
      group.querySelectorAll('.variant-btn').forEach(btn => {
        const val = btn.getAttribute('data-value');
        const testAttrs = Object.assign({}, selectedAttrs, { [gType]: val });
        const matchingSku = findPdpMatchingSku(window.productSkus, testAttrs);

        let optionStock = 0;
        if (matchingSku) {
          optionStock = parseInt(matchingSku.stock, 10) || 0;
        } else {
          const skusWithOpt = window.productSkus.filter(s => {
            let attrs = s.attributes || {};
            if (typeof attrs === 'string') {
              try { attrs = JSON.parse(attrs); } catch (e) { attrs = {}; }
            }
            return Object.values(attrs).some(v => String(v).trim().toLowerCase() === String(val).trim().toLowerCase());
          });
          optionStock = skusWithOpt.reduce((sum, s) => sum + (parseInt(s.stock, 10) || 0), 0);
        }

        if (optionStock <= 0) {
          btn.disabled = true;
          btn.classList.add('opacity-40', 'line-through', 'cursor-not-allowed', 'pointer-events-none');
          btn.title = `${val} is out of stock`;
        } else {
          btn.disabled = false;
          btn.classList.remove('opacity-40', 'line-through', 'cursor-not-allowed', 'pointer-events-none');
          btn.title = '';
        }
      });
    });
  }

  // Find exact matching SKU combination
  const matchedSku = findPdpMatchingSku(window.productSkus, selectedAttrs);

  let finalPrice = basePrice;
  let finalReg = baseReg;

  const hasSkus = window.productSkus && window.productSkus.length > 0;
  const totalSkuStock = hasSkus ? window.productSkus.reduce((sum, s) => sum + (parseInt(s.stock, 10) || 0), 0) : 0;
  const isProductOutOfStockServer = {{ $isOutOfStock ? 'true' : 'false' }};

  let isAvailable = !isProductOutOfStockServer;

  if (hasSkus) {
    if (totalSkuStock <= 0) {
      isAvailable = false;
    } else if (matchedSku) {
      isAvailable = (parseInt(matchedSku.stock, 10) || 0) > 0;
    } else {
      isAvailable = totalSkuStock > 0;
    }
  } else {
    isAvailable = !isProductOutOfStockServer;
  }

  if (matchedSku) {
    const skuSalePrice = parseFloat(matchedSku.sale_price);
    const skuRegPrice = parseFloat(matchedSku.regular_price);
    const adj = parseFloat(matchedSku.price_adjustment) || 0;

    if (!isNaN(skuSalePrice) && skuSalePrice > 0) {
      finalPrice = skuSalePrice;
    } else {
      finalPrice = Math.max(0, basePrice + adj);
    }

    if (!isNaN(skuRegPrice) && skuRegPrice > 0) {
      finalReg = skuRegPrice;
    } else if (baseReg > 0) {
      finalReg = Math.max(0, baseReg + adj);
    } else {
      finalReg = 0;
    }

    if (addBtn) addBtn.dataset.skuId = matchedSku.id;
  }

  const availStock = matchedSku ? matchedSku.stock : 99;
  const maxAllowed = Math.min(3, availStock);
  const qtyInput = document.getElementById('pdQty');
  if (qtyInput && parseInt(qtyInput.value, 10) > maxAllowed) {
    qtyInput.value = Math.max(1, maxAllowed);
  }

  // Currency Formatter Helper
  const formatMoney = (num) => '৳' + (Number(num) % 1 === 0 ? Number(num).toLocaleString('en-US') : Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  // Update Offer / Sale Price element
  priceEl.textContent = formatMoney(finalPrice);

  // Calculate discount percent accurately
  const hasDiscount = finalReg > finalPrice && finalReg > 0;
  const discPercent = hasDiscount ? Math.round(((finalReg - finalPrice) / finalReg) * 100) : 0;
  const savedAmount = Math.max(0, finalReg - finalPrice);

  // Update Regular / MRP Price element
  if (regEl) {
    if (hasDiscount) {
      regEl.textContent = formatMoney(finalReg);
      regEl.classList.remove('hidden');
    } else {
      regEl.classList.add('hidden');
    }
  }

  // Update "Save X%" Badge next to price
  if (badgeEl) {
    if (hasDiscount && discPercent > 0) {
      badgeEl.textContent = `Save ${formatMoney(savedAmount)} (${discPercent}% OFF)`;
      badgeEl.classList.remove('hidden');
    } else {
      badgeEl.classList.add('hidden');
    }
  }

  // Update Image Overlay "X% OFF" Badge
  if (imgBadgeEl) {
    if (hasDiscount && discPercent > 0) {
      imgBadgeEl.textContent = `${discPercent}% OFF`;
      imgBadgeEl.closest('#pdImageDiscountWrap')?.classList.remove('hidden');
    } else {
      imgBadgeEl.closest('#pdImageDiscountWrap')?.classList.add('hidden');
    }
  }

  // Sync Mobile Sticky Bar Price
  const mobPrice = document.querySelector('[data-mobile-price]');
  if (mobPrice) mobPrice.textContent = formatMoney(finalPrice);
  const mobReg = document.querySelector('[data-mobile-reg]');
  if (mobReg) {
    if (hasDiscount) {
      mobReg.textContent = formatMoney(finalReg);
      mobReg.classList.remove('hidden');
    } else {
      mobReg.classList.add('hidden');
    }
  }

  const pdpDefaultCta = "{{ setting('default_cta_text', 'ADD TO CART') }}";
  if (addBtn) {
    addBtn.disabled = !isAvailable;
    if (btnText) btnText.textContent = isAvailable ? pdpDefaultCta : 'OUT OF STOCK';
  }
  if (buyBtn) {
    buyBtn.disabled = !isAvailable;
    const buyBtnText = document.getElementById('pdBuyNowText') || buyBtn.querySelector('span');
    if (buyBtnText) buyBtnText.textContent = isAvailable ? 'BUY NOW' : 'OUT OF STOCK';
  }

  const mobStock = document.querySelector('[data-mobile-stock]');
  if (mobStock) {
    if (!isAvailable) {
      mobStock.innerHTML = '<span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500"></span><span class="text-[11px] font-semibold text-rose-600 truncate">Out of Stock</span>';
    } else {
      mobStock.innerHTML = '<span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span class="text-[11px] font-semibold text-emerald-700 truncate">In Stock · Ready to Ship</span>';
    }
  }

  const stickyAddBtn = document.getElementById('stickyBarAddToCart');
  const stickyBuyBtn = document.getElementById('stickyBarBuyNow');
  if (stickyAddBtn) {
    stickyAddBtn.disabled = !isAvailable;
    const sAddText = document.getElementById('stickyBarAddToCartText') || stickyAddBtn.querySelector('span');
    if (sAddText) sAddText.textContent = isAvailable ? 'ADD TO CART' : 'OUT OF STOCK';
  }
  if (stickyBuyBtn) {
    stickyBuyBtn.disabled = !isAvailable;
    const sBuyText = document.getElementById('stickyBarBuyNowText') || stickyBuyBtn.querySelector('span');
    if (sBuyText) sBuyText.textContent = isAvailable ? 'BUY NOW' : 'OUT OF STOCK';
  }
}

document.getElementById('stickyBarAddToCart')?.addEventListener('click', (e) => {
  e.preventDefault();
  document.getElementById('pdAddToCart')?.click();
});
document.getElementById('stickyBarBuyNow')?.addEventListener('click', (e) => {
  e.preventDefault();
  document.getElementById('pdBuyNow')?.click();
});

// Show mobile sticky bar only when visitor scrolls down past the original in-page action buttons
(function initStickyBarScrollTrigger() {
  const stickyBar = document.getElementById('stickyMobileBar');
  const mainActions = document.getElementById('mainProductActions') || document.getElementById('pdAddToCart');

  if (!stickyBar || !mainActions) return;

  function updateStickyBar() {
    const rect = mainActions.getBoundingClientRect();
    // When the bottom of the original action buttons is scrolled past the top of the viewport
    if (rect.bottom < 0) {
      stickyBar.classList.remove('translate-y-28', 'opacity-0', 'pointer-events-none');
      stickyBar.classList.add('translate-y-0', 'opacity-100');
    } else {
      stickyBar.classList.add('translate-y-28', 'opacity-0', 'pointer-events-none');
      stickyBar.classList.remove('translate-y-0', 'opacity-100');
    }
  }

  window.addEventListener('scroll', updateStickyBar, { passive: true });
  window.addEventListener('resize', updateStickyBar, { passive: true });

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(() => {
      updateStickyBar();
    }, { threshold: [0, 1] });
    observer.observe(mainActions);
  }

  updateStickyBar();
})();

document.querySelectorAll('[data-variant-group] .variant-btn').forEach((b) => b.addEventListener('click', (e) => {
  if (b.disabled) {
    e.preventDefault();
    return;
  }
  const group = b.closest('[data-variant-group]');
  const wasSelected = b.classList.contains('is-selected');
  group.querySelectorAll('.variant-btn').forEach((x) => {
    x.classList.remove('is-selected', 'border-stone-950', 'bg-stone-950', 'text-white', 'shadow-xs', 'border-stone-900', 'bg-stone-900');
    x.classList.add('border-stone-200', 'bg-stone-50/60', 'text-stone-800');
  });
  const hintEl = group.querySelector('[data-selected-val-hint]');
  if (!wasSelected) {
    b.classList.add('is-selected', 'border-stone-950', 'bg-stone-950', 'text-white', 'shadow-xs');
    b.classList.remove('border-stone-200', 'bg-stone-50/60', 'text-stone-800');
    if (hintEl) hintEl.textContent = b.getAttribute('data-value');
  } else {
    if (hintEl) hintEl.textContent = '';
  }

  const pdpAlert = document.getElementById('pdpErrorAlert');
  if (pdpAlert) pdpAlert.classList.add('hidden');

  const clickedVal = b.getAttribute('data-value');
  syncPdpVariantStockAndPrice(wasSelected ? null : clickedVal);
}));

// Run initial sync on load
syncPdpVariantStockAndPrice();

// Live Flash Sale Countdown Timer
const pdpTimerEl = document.querySelector('[data-pdp-flash-timer]');
if (pdpTimerEl) {
  const endsAt = pdpTimerEl.dataset.endsAt;
  if (endsAt) {
    const targetTime = new Date(endsAt).getTime();
    const updatePdpTimer = () => {
      const now = new Date().getTime();
      const diff = Math.max(0, targetTime - now);
      
      const d = Math.floor(diff / (1000 * 60 * 60 * 24));
      const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const s = Math.floor((diff % (1000 * 60)) / 1000);

      const daysEl = pdpTimerEl.querySelector('[data-timer-days]');
      const hoursEl = pdpTimerEl.querySelector('[data-timer-hours]');
      const minsEl = pdpTimerEl.querySelector('[data-timer-mins]');
      const secsEl = pdpTimerEl.querySelector('[data-timer-secs]');

      if (daysEl) daysEl.textContent = String(d).padStart(2, '0');
      if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
      if (minsEl) minsEl.textContent = String(m).padStart(2, '0');
      if (secsEl) secsEl.textContent = String(s).padStart(2, '0');
    };
    updatePdpTimer();
    setInterval(updatePdpTimer, 1000);
  }
}

// Customer Review Form Toggle & Interactive Star Rating Picker
const toggleReviewBtn = document.getElementById('toggleReviewFormBtn');
const closeReviewBtn = document.getElementById('closeReviewFormBtn');
const reviewDrawer = document.getElementById('reviewFormDrawer');

if (toggleReviewBtn && reviewDrawer) {
  toggleReviewBtn.addEventListener('click', () => {
    reviewDrawer.classList.toggle('hidden');
    if (!reviewDrawer.classList.contains('hidden')) {
      reviewDrawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  });
}
if (closeReviewBtn && reviewDrawer) {
  closeReviewBtn.addEventListener('click', () => {
    reviewDrawer.classList.add('hidden');
  });
}

// Star Rating Interactive Picker
const starRatingContainer = document.getElementById('starRatingContainer');
const starRatingInput = document.getElementById('reviewRatingInput');
const starRatingLabel = document.getElementById('starRatingLabel');

if (starRatingContainer && starRatingInput) {
  const starBtns = Array.from(starRatingContainer.querySelectorAll('.star-rating-btn'));
  const labels = {
    1: '1.0 / 5.0 (Poor)',
    2: '2.0 / 5.0 (Fair)',
    3: '3.0 / 5.0 (Average)',
    4: '4.0 / 5.0 (Good)',
    5: '5.0 / 5.0 (Excellent)'
  };

  function renderStars(val) {
    starBtns.forEach((btn, idx) => {
      const starSvg = btn.querySelector('.star-svg');
      if (idx < val) {
        starSvg.classList.add('text-amber-400', 'fill-amber-400');
        starSvg.classList.remove('text-slate-200', 'fill-slate-200');
      } else {
        starSvg.classList.remove('text-amber-400', 'fill-amber-400');
        starSvg.classList.add('text-slate-200', 'fill-slate-200');
      }
    });
    if (starRatingLabel && labels[val]) {
      starRatingLabel.textContent = labels[val];
    }
  }

  starBtns.forEach(btn => {
    const val = parseInt(btn.dataset.starValue, 10);

    btn.addEventListener('mouseenter', () => {
      renderStars(val);
    });

    btn.addEventListener('click', () => {
      starRatingInput.value = val;
      renderStars(val);
    });
  });

  starRatingContainer.addEventListener('mouseleave', () => {
    const currentVal = parseInt(starRatingInput.value, 10) || 5;
    renderStars(currentVal);
  });
}

// PDP Description, Specs & Shipping Tab Switcher
const pdpTabBtns = document.querySelectorAll('[data-pdp-tab]');
const pdpTabPanes = document.querySelectorAll('.pdp-tab-pane');

if (pdpTabBtns.length > 0) {
  pdpTabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.pdpTab;
      pdpTabBtns.forEach(b => {
        b.classList.remove('active', 'bg-white', 'text-stone-950', 'shadow-xs', 'font-extrabold');
        b.classList.add('text-stone-600', 'font-bold');
      });
      btn.classList.add('active', 'bg-white', 'text-stone-950', 'shadow-xs', 'font-extrabold');
      btn.classList.remove('text-stone-600', 'font-bold');

      pdpTabPanes.forEach(pane => pane.classList.add('hidden'));
      const paneName = 'pdpTabPane' + target.charAt(0).toUpperCase() + target.slice(1);
      const targetPane = document.getElementById(paneName);
      if (targetPane) {
        targetPane.classList.remove('hidden');
      }
    });
  });
}

// Meta (Facebook) Pixel ViewContent Event
if (typeof fbq === 'function') {
  fbq('track', 'ViewContent', {
    content_name: '{{ addslashes($product->name) }}',
    content_ids: ['{{ $product->id }}'],
    content_type: 'product',
    value: {{ (float) $product->price }},
    currency: 'BDT'
  });
}
</script>
@endpush
