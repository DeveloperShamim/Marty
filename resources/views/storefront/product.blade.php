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
    $isOutOfStock = (int) $product->stock_quantity <= 0;
    $savings = $product->on_sale ? ($product->regular_price - $product->price) : 0;
@endphp

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-5 py-6">
  {{-- Clean Breadcrumb --}}
  <nav class="flex items-center gap-2 text-xs sm:text-sm text-stone-500 mb-6 flex-wrap">
    <a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Home</a>
    <span class="text-stone-300">/</span>
    @if($product->category)
      <a href="{{ route('shop.category', $product->category) }}" class="hover:text-brand-600 transition-colors">{{ $product->category->name }}</a>
      <span class="text-stone-300">/</span>
    @endif
    <span class="text-stone-800 font-semibold truncate max-w-[220px] sm:max-w-xs">{{ $product->name }}</span>
  </nav>

  {{-- Main Product Card Container --}}
  <div class="bg-white rounded-2xl border border-stone-200/80 p-5 sm:p-8 flex flex-col md:flex-row gap-8 items-start shadow-sm">
    {{-- Left: Vertical Thumbnails + Main Image Frame --}}
    <div class="flex flex-col-reverse sm:flex-row gap-4 items-start w-full md:w-1/2 shrink-0">
      @if($product->images->count() > 0)
        <div class="flex sm:flex-col gap-3 overflow-x-auto sm:overflow-y-auto w-full sm:w-20 shrink-0 pb-1 sm:pb-0 max-h-[460px] no-scrollbar">
          @foreach($product->images as $img)
            <button type="button" data-thumb="{{ $img->url() }}" data-color="{{ strtolower(trim($img->color ?? '')) }}" data-variation-tag="{{ strtolower(trim($img->color ?? '')) }}" data-alt="{{ strtolower(trim($img->alt ?? '')) }}" class="gallery-thumb-btn w-16 h-16 sm:w-20 sm:h-20 rounded-xl border {{ $loop->first ? 'border-brand-500' : 'border-stone-200 opacity-80 hover:opacity-100' }} shrink-0 bg-white overflow-hidden relative transition-colors focus:outline-none">
              <img src="{{ $img->url() }}" loading="lazy" decoding="async" class="w-full h-full object-cover" alt="{{ $img->alt }}">
              @if($loop->first)
                <span data-active-check class="absolute inset-0 flex items-center justify-center pointer-events-none"><span class="w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs">✓</span></span>
              @endif
            </button>
          @endforeach
        </div>
      @endif

      <div class="flex-1 relative border border-stone-200/80 rounded-2xl aspect-square w-full bg-white overflow-hidden shadow-xs flex items-center justify-center">
        @if($isOutOfStock)
          <span class="absolute top-4 left-4 z-10 bg-stone-800/90 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm">Out of Stock</span>
        @else
          <div class="absolute top-4 left-4 z-10 flex flex-col gap-1.5 items-start pointer-events-none">
            @if($product->is_flash_sale)
              <span class="bg-gradient-to-r from-red-600 to-amber-500 text-white font-black text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-md flex items-center gap-1 animate-pulse">⚡ FLASH SALE</span>
            @endif
            @if($product->on_sale)
              <span id="pdImageDiscountBadge" class="bg-red-500 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm {{ $product->on_sale ? '' : 'hidden' }}">{{ $product->discount_percent }}% OFF</span>
            @else
              <span id="pdImageDiscountBadge" class="hidden bg-red-500 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm">0% OFF</span>
            @endif
          </div>
        @endif

        @if($product->images->count() > 1)
          <button type="button" id="pdPrevImg" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 p-1 text-brand-500 hover:text-brand-600 transition-colors focus:outline-none bg-transparent" aria-label="Previous Image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" id="pdNextImg" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 p-1 text-brand-500 hover:text-brand-600 transition-colors focus:outline-none bg-transparent" aria-label="Next Image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        @endif

        <img id="galleryMain" data-gallery-main src="{{ $product->imageUrl() }}" loading="lazy" decoding="async" class="w-full h-full object-contain {{ $isOutOfStock ? 'opacity-75 grayscale-[30%]' : '' }}" alt="{{ $product->name }}" />
      </div>
    </div>

    {{-- Right: Clean Product Info Panel --}}
    <div class="w-full md:w-1/2 space-y-4">
      <div>

        <h1 class="text-2xl sm:text-3xl font-bold text-stone-900 leading-snug mb-3">{{ $product->name }}</h1>

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

        {{-- Pricing Row --}}
        <div id="pdpPriceContainer" class="flex items-center gap-3 flex-wrap" data-skus="{{ json_encode($skusPayload) }}">
          <span id="pdPrice" class="text-3xl font-extrabold text-brand-500" data-base-price="{{ number_format((float) $product->price, 2, '.', '') }}">{{ money($product->price) }}</span>
          <span id="pdRegularPrice" class="text-stone-400 line-through text-lg font-normal {{ $product->on_sale ? '' : 'hidden' }}" data-base-regular="{{ number_format((float) ($product->regular_price ?? 0), 2, '.', '') }}">{{ money($product->regular_price) }}</span>
          <span id="pdDiscountBadge" class="bg-emerald-500 text-white font-extrabold text-xs px-2.5 py-1 rounded shadow-xs {{ $product->on_sale ? '' : 'hidden' }}">Save {{ $product->discount_percent }}%</span>
        </div>
      </div>

      {{-- Compact High-Energy Flash Sale Strip --}}
      @if($product->is_flash_sale)
        @php
          $flashEndsAt = setting('flash_sale_ends_at');
          $flashEndsIso = $flashEndsAt ? \Illuminate\Support\Carbon::parse($flashEndsAt)->toIso8601String() : null;
          $progress = (int) $product->flash_sale_progress;
          $flashStock = $product->skus()->exists() ? (int) $product->skus()->sum('stock_quantity') : (int) $product->stock_quantity;
        @endphp
        <div class="rounded-xl px-3.5 py-3 bg-gradient-to-r from-red-600 via-brand-600 to-amber-500 text-white shadow-md space-y-2.5 my-2.5 relative overflow-hidden border border-white/20">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            {{-- Left Title & Flame --}}
            <div class="flex items-center gap-1.5">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-black/20 text-amber-300 text-xs shadow-xs border border-white/20 animate-pulse shrink-0">🔥</span>
              <span class="font-black text-xs sm:text-sm tracking-wider uppercase text-white drop-shadow-xs">FLASH SALE</span>
            </div>

            {{-- Compact Timer --}}
            <div class="flex items-center gap-1 text-xs font-bold bg-black/30 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/15" data-pdp-flash-timer data-ends-at="{{ $flashEndsIso }}">
              <span class="text-amber-200 text-[11px] font-semibold mr-0.5">Ends in:</span>
              <span data-timer-days class="font-mono font-black text-amber-300">00</span><span class="text-amber-200 text-[10px]">d</span> :
              <span data-timer-hours class="font-mono font-black text-white">00</span><span class="text-amber-200 text-[10px]">h</span> :
              <span data-timer-mins class="font-mono font-black text-white">00</span><span class="text-amber-200 text-[10px]">m</span> :
              <span data-timer-secs class="font-mono font-black text-amber-300">00</span><span class="text-amber-200 text-[10px]">s</span>
            </div>
          </div>

          {{-- Prominent Progress Bar & Stock Alert --}}
          <div class="relative z-10 space-y-1">
            <div class="flex justify-between items-center text-[11px] font-extrabold">
              <span class="text-amber-100 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-ping"></span>
                <span>Sold: {{ $progress }}%</span>
              </span>
              <span class="text-white drop-shadow-xs font-extrabold">
                @if($flashStock > 0)
                  Only {{ $flashStock }} left in stock!
                @else
                  Selling Fast!
                @endif
              </span>
            </div>
            <div class="w-full h-2.5 bg-black/40 backdrop-blur-xs rounded-full overflow-hidden p-0.5 border border-white/20 shadow-inner">
              <div class="h-full bg-gradient-to-r from-amber-300 via-yellow-300 to-amber-400 rounded-full transition-all duration-700 shadow-xs relative" style="width: {{ $progress }}%">
                <div class="absolute inset-0 bg-white/25 animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>
      @endif

      <hr class="border-stone-100 my-4" />

      {{-- Dynamic Variants (Color, Size, Weight, Packaging, Pack Option, etc.) --}}
      @if(isset($variantGroups) && $variantGroups->isNotEmpty())
        @foreach($variantGroups as $groupType => $group)
          <div data-variant-group="{{ $groupType }}" class="mb-3">
            <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">{{ $groupType }}</p>
            <div class="flex flex-wrap gap-2">
              @foreach($group->options as $optValue)
                <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all border border-stone-200 text-stone-700 hover:border-stone-300" data-type="{{ $groupType }}" data-value="{{ $optValue }}">{{ $optValue }}</button>
              @endforeach
            </div>
          </div>
        @endforeach
      @endif

      {{-- Quantity Stepper --}}
      <div class="flex items-center gap-3 py-1">
        <span class="text-sm font-semibold text-stone-600">Quantity:</span>
        <div data-qty data-stepper class="inline-flex items-center border border-stone-300 rounded-lg overflow-hidden bg-white shadow-xs">
          <button type="button" data-dec class="px-3.5 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">−</button>
          <input id="pdQty" value="1" min="1" max="3" class="w-10 text-center border-0 font-bold text-stone-800 focus:outline-none text-sm bg-transparent" readonly />
          <button type="button" data-inc class="px-3.5 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">+</button>
        </div>
      </div>

      {{-- Validation Error Alert Box --}}
      <div id="pdpErrorAlert" class="hidden bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3 rounded-xl flex items-center gap-2 my-1">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="pdpErrorMessage">Please select a variation option before adding to cart.</span>
      </div>

      {{-- Action Buttons --}}
      <div class="space-y-3 pt-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button type="button" id="pdAddToCart" data-product-id="{{ $product->id }}" data-title="{{ $product->name }}" class="w-full border-2 border-brand-500 bg-transparent text-brand-500 hover:bg-brand-500 hover:text-white font-extrabold py-3.5 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2 text-xs sm:text-sm uppercase tracking-wide cursor-pointer disabled:bg-stone-200 disabled:text-stone-400 disabled:cursor-not-allowed" @disabled($isOutOfStock)>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span id="pdAddToCartText">{{ $isOutOfStock ? 'OUT OF STOCK' : setting('default_cta_text', 'ADD TO CART') }}</span>
          </button>

          <button type="button" id="pdBuyNow" data-buy-now data-product-id="{{ $product->id }}" data-title="{{ $product->name }}" data-checkout-url="{{ route('checkout.show') }}" class="w-full bg-[#0B2523] hover:bg-black text-white font-extrabold py-3.5 px-4 rounded-xl shadow transition-all flex items-center justify-center text-xs sm:text-sm uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed" @disabled($isOutOfStock)>
            BUY NOW
          </button>
        </div>

        @if($whatsapp)
          <a href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Hi, I want to buy: '.$product->name) }}" target="_blank" rel="noopener" class="w-full bg-[#10B981] hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.19 4.18-1.096z"/></svg>
            Order On WhatsApp
          </a>
        @endif
      </div>

      {{-- Brand Badge Box --}}
      @php
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

      @if($brandObj)
        <div class="pt-2">
          <a href="{{ route('shop.brand', $brandObj) }}" class="inline-flex items-center gap-2.5 border border-stone-200/90 hover:border-brand-500 rounded-xl px-4 py-2 text-xs sm:text-sm font-semibold text-stone-700 bg-white hover:bg-brand-50/50 shadow-xs transition-all duration-200 group">
            <span class="text-stone-500 font-medium">Brand:</span>
            <span class="w-5 h-5 rounded-full overflow-hidden bg-stone-100 border border-stone-200 shrink-0 flex items-center justify-center shadow-2xs">
              <img src="{{ $brandObj->logoUrl() }}" alt="{{ $brandObj->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" />
            </span>
            <span class="font-extrabold text-stone-900 group-hover:text-brand-600 transition-colors">{{ $brandObj->name }}</span>
            <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-brand-600 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Description & Specifications --}}
  <div class="bg-white rounded-2xl border border-stone-200/80 p-6 sm:p-8 mt-8 shadow-sm space-y-6">
    <div class="border-b border-stone-200 pb-4">
      <h2 class="font-extrabold text-xl text-stone-900">Product Details &amp; Specifications</h2>
    </div>

    @if(! empty($specRows))
      <div>
        <h3 class="font-extrabold text-base text-stone-900 mb-3">Specifications</h3>
        <div class="overflow-x-auto rounded-xl border border-stone-200/80">
          <table class="w-full text-sm text-left">
            <tbody>
              <tr class="bg-stone-50 border-b border-stone-200/80"><td colspan="2" class="px-4 py-2.5 font-bold text-stone-700">Basic Information</td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 w-60 font-semibold text-stone-500">Brand</td><td class="px-4 py-2.5 text-stone-800">{{ $product->brand ?: "—" }}</td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">Category</td><td class="px-4 py-2.5 text-stone-800">{{ $product->category?->name }}</td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">Unit</td><td class="px-4 py-2.5 text-stone-800">{{ $product->unit ?: "—" }}</td></tr>
              <tr class="border-b border-stone-100"><td class="px-4 py-2.5 font-semibold text-stone-500">SKU</td><td class="px-4 py-2.5 text-stone-800">{{ $product->sku ?: 'N/A' }}</td></tr>
              <tr class="bg-stone-50 border-b border-stone-200/80"><td colspan="2" class="px-4 py-2.5 font-bold text-stone-700">Product Specifications</td></tr>
              @foreach($specRows as $row)
                <tr class="border-b border-stone-100">
                  <td class="px-4 py-2.5 font-semibold text-stone-500">{{ $row['label'] !== '' ? $row['label'] : 'Detail' }}</td>
                  <td class="px-4 py-2.5 text-stone-800">{{ $row['value'] !== '' ? $row['value'] : "—" }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

    @if($product->description)
      <div>
        <h3 class="font-extrabold text-base text-stone-900 mb-3">Description</h3>
        <div class="text-stone-600 leading-relaxed max-w-4xl space-y-3 text-sm sm:text-base">
          @foreach(preg_split('/\n\n+/', (string) $product->description) as $para)
            @if(trim($para))<p>{{ $para }}</p>@endif
          @endforeach
        </div>
      </div>
    @endif
  </div>

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

    thumbBtns.forEach((x, i) => {
      const check = x.querySelector('[data-active-check]');
      if (i === currentIndex) {
        x.classList.add('border-brand-500');
        x.classList.remove('border-stone-200', 'opacity-80');
        if (!check) {
          const checkWrap = document.createElement('span');
          checkWrap.setAttribute('data-active-check', 'true');
          checkWrap.className = 'absolute inset-0 flex items-center justify-center pointer-events-none';
          checkWrap.innerHTML = '<span class="w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-xs">✓</span>';
          x.appendChild(checkWrap);
        }
      } else {
        x.classList.remove('border-brand-500');
        x.classList.add('border-stone-200', 'opacity-80');
        if (check) check.remove();
      }
    });
  }

  thumbBtns.forEach((btn, index) => {
    btn.addEventListener('click', () => setActiveImage(index));
  });

  if (prevBtn) prevBtn.addEventListener('click', () => setActiveImage(currentIndex - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => setActiveImage(currentIndex + 1));

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
    const attrs = sku.attributes || {};
    return selKeys.every(k => {
      const targetVal = String(selectedAttrs[k] || '').trim().toLowerCase();
      const skuVal = String(attrs[k] || '').trim().toLowerCase();
      return skuVal === targetVal;
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

        if (matchingSku) {
          if (matchingSku.stock > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = '';
          } else {
            btn.disabled = true;
            btn.classList.add('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = `${val} is out of stock`;
          }
        }
      });
    });
  }

  // Find exact matching SKU combination
  const matchedSku = findPdpMatchingSku(window.productSkus, selectedAttrs);

  let finalPrice = basePrice;
  let finalReg = baseReg;
  let isAvailable = true;

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

    isAvailable = matchedSku.stock > 0;
    if (addBtn) addBtn.dataset.skuId = matchedSku.id;
  }

  const availStock = matchedSku ? matchedSku.stock : 99;
  const maxAllowed = Math.min(3, availStock);
  const qtyInput = document.getElementById('pdQty');
  if (qtyInput && parseInt(qtyInput.value, 10) > maxAllowed) {
    qtyInput.value = Math.max(1, maxAllowed);
  }

  // Currency Formatter Helper
  const formatMoney = (num) => '৳' + Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  // Update Offer / Sale Price element
  priceEl.textContent = formatMoney(finalPrice);

  // Calculate discount percent accurately
  const hasDiscount = finalReg > finalPrice && finalReg > 0;
  const discPercent = hasDiscount ? Math.round(((finalReg - finalPrice) / finalReg) * 100) : 0;

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
      badgeEl.textContent = `Save ${discPercent}%`;
      badgeEl.classList.remove('hidden');
    } else {
      badgeEl.classList.add('hidden');
    }
  }

  // Update Image Overlay "X% OFF" Badge
  if (imgBadgeEl) {
    if (hasDiscount && discPercent > 0) {
      imgBadgeEl.textContent = `${discPercent}% OFF`;
      imgBadgeEl.classList.remove('hidden');
    } else {
      imgBadgeEl.classList.add('hidden');
    }
  }

  if (addBtn) {
    addBtn.disabled = !isAvailable;
    if (btnText) btnText.textContent = isAvailable ? 'ADD TO CART' : 'OUT OF STOCK';
  }
  if (buyBtn) {
    buyBtn.disabled = !isAvailable;
  }
}

document.querySelectorAll('[data-variant-group] .variant-btn').forEach((b) => b.addEventListener('click', (e) => {
  if (b.disabled) {
    e.preventDefault();
    return;
  }
  const group = b.closest('[data-variant-group]');
  const wasSelected = b.classList.contains('is-selected');
  group.querySelectorAll('.variant-btn').forEach((x) => {
    x.classList.remove('is-selected', 'border-2', 'border-brand-500', 'text-brand-600', 'bg-brand-50/40');
    x.classList.add('border', 'border-stone-200', 'text-stone-700');
  });
  if (!wasSelected) {
    b.classList.add('is-selected', 'border-2', 'border-brand-500', 'text-brand-600', 'bg-brand-50/40');
    b.classList.remove('border-stone-200', 'text-stone-700');
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
