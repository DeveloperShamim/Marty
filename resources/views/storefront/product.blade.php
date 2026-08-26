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
            <button type="button" data-thumb="{{ $img->url() }}" data-color="{{ strtolower(trim($img->color ?? '')) }}" data-alt="{{ strtolower(trim($img->alt ?? '')) }}" class="gallery-thumb-btn w-16 h-16 sm:w-20 sm:h-20 rounded-xl border {{ $loop->first ? 'border-brand-500' : 'border-stone-200 opacity-80 hover:opacity-100' }} shrink-0 bg-white overflow-hidden relative transition-colors focus:outline-none">
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
              <span class="bg-red-500 text-white font-extrabold text-xs tracking-wider uppercase px-3 py-1.5 rounded-lg shadow-sm">{{ $product->discount_percent }}% OFF</span>
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
          $skusCollection = $product->skus;
          if ($skusCollection->isEmpty() && $product->variants->isNotEmpty()) {
              $skusCollection = $product->variants->map(function($v) {
                  return (object) [
                      'id'               => null,
                      'attributes'       => [$v->type => $v->value],
                      'stock_quantity'   => (int) $v->stock,
                      'price_adjustment' => (float) $v->price_delta,
                  ];
              });
          }
          $skusPayload = $skusCollection->map(fn($s) => [
            'id'               => is_object($s) && isset($s->id) ? $s->id : null,
            'attributes'       => is_a($s, \App\Models\ProductSku::class) ? $s->getAttributesData() : (array) ($s->attributes ?? []),
            'stock'            => (int) ($s->stock_quantity ?? $s->stock ?? 0),
            'price_adjustment' => (float) ($s->price_adjustment ?? $s->price_delta ?? 0),
          ])->values();
        @endphp

        {{-- Pricing Row --}}
        <div class="flex items-center gap-3 flex-wrap">
          <span id="pdPrice" class="text-3xl font-extrabold text-brand-500" data-base-price="{{ (float) $product->price }}">{{ money($product->price) }}</span>
          @if($product->on_sale)
            <span id="pdRegularPrice" class="text-stone-400 line-through text-lg font-normal" data-base-regular="{{ (float) $product->regular_price }}">{{ money($product->regular_price) }}</span>
            <span class="bg-emerald-500 text-white font-extrabold text-xs px-2.5 py-1 rounded shadow-xs">Save {{ $product->discount_percent }}%</span>
          @endif
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

      {{-- Variants --}}
      @if($colors->isNotEmpty())
        <div data-variant-group="Color">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Color</p>
          <div class="flex flex-wrap gap-2">
            @foreach($colors as $v)
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all border border-stone-200 text-stone-700 hover:border-stone-300" data-type="Color" data-value="{{ $v->value }}">{{ $v->value }}</button>
            @endforeach
          </div>
        </div>
      @endif

      @if($sizes->isNotEmpty())
        <div data-variant-group="Size">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Size</p>
          <div class="flex flex-wrap gap-2">
            @foreach($sizes as $v)
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all border border-stone-200 text-stone-700 hover:border-stone-300" data-type="Size" data-value="{{ $v->value }}">{{ $v->value }}</button>
            @endforeach
          </div>
        </div>
      @endif

      @if($weights->isNotEmpty())
        <div data-variant-group="Weight">
          <p class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Option</p>
          <div class="flex flex-wrap gap-2">
            @foreach($weights as $v)
              <button type="button" class="variant-btn px-4 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all border border-stone-200 text-stone-700 hover:border-stone-300" data-type="Weight" data-value="{{ $v->value }}">{{ $v->value }}</button>
            @endforeach
          </div>
        </div>
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

  window.selectProductImageByColor = function(colorVal) {
    if (!colorVal || !thumbBtns.length) return;
    const target = String(colorVal).trim().toLowerCase();
    const foundIndex = thumbBtns.findIndex(btn => {
      const c = (btn.getAttribute('data-color') || '').trim().toLowerCase();
      const alt = (btn.getAttribute('data-alt') || '').trim().toLowerCase();
      return c === target || alt.includes(target) || target.includes(c && c !== '' ? c : '___none___');
    });
    if (foundIndex !== -1) {
      setActiveImage(foundIndex);
    }
  };
})();

window.productSkus = {!! $skusPayload->toJson() !!};

function findPdpMatchingSku(skus, selectedAttrs) {
  if (!skus || !skus.length) return null;
  const selectedKeys = Object.keys(selectedAttrs);
  if (!selectedKeys.length) return null;

  return skus.find(s => {
    if (!s.attributes) return false;
    const attrs = s.attributes;
    const attrMap = {};
    Object.keys(attrs).forEach(k => {
      attrMap[String(k).trim().toLowerCase()] = String(attrs[k]).trim().toLowerCase();
    });

    return selectedKeys.every(k => {
      const kLower = String(k).trim().toLowerCase();
      const expectedVal = String(selectedAttrs[k]).trim().toLowerCase();
      return attrMap[kLower] === expectedVal;
    });
  });
}

function syncPdpVariantStockAndPrice() {
  const priceEl = document.getElementById('pdPrice');
  const regEl = document.getElementById('pdRegularPrice');
  const addBtn = document.getElementById('pdAddToCart');
  const buyBtn = document.getElementById('pdBuyNow');
  const btnText = document.getElementById('pdAddToCartText');

  const basePrice = parseFloat(priceEl?.getAttribute('data-base-price') || '0');
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

  const selectedColor = selectedAttrs['Color'] || selectedAttrs['color'] || null;

  if (selectedColor && typeof window.selectProductImageByColor === 'function') {
    window.selectProductImageByColor(selectedColor);
  }

  // Update Size availability based on selected Color
  if (selectedColor && window.productSkus && window.productSkus.length > 0) {
    const sizeGroup = document.querySelector('[data-variant-group="Size"]');
    if (sizeGroup) {
      sizeGroup.querySelectorAll('.variant-btn').forEach(btn => {
        const sizeVal = btn.getAttribute('data-value');
        const matchingSku = findPdpMatchingSku(window.productSkus, { Color: selectedColor, Size: sizeVal });

        if (matchingSku) {
          if (matchingSku.stock > 0) {
            btn.disabled = false;
            btn.classList.remove('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = '';
          } else {
            btn.disabled = true;
            btn.classList.add('opacity-40', 'line-through', 'cursor-not-allowed');
            btn.title = `Size ${sizeVal} is out of stock for ${selectedColor}`;
          }
        }
      });
    }
  }

  // Find exact matching SKU combination
  const matchedSku = findPdpMatchingSku(window.productSkus, selectedAttrs);

  let finalPrice = basePrice;
  let isAvailable = true;

  if (matchedSku) {
    const adj = parseFloat(matchedSku.price_adjustment) || 0;
    if (basePrice <= 0) {
      finalPrice = Math.max(0, adj);
    } else {
      finalPrice = Math.max(0, basePrice + adj);
    }
    isAvailable = (parseInt(matchedSku.stock, 10) || 0) > 0;
    if (addBtn && matchedSku.id) addBtn.dataset.skuId = matchedSku.id;
  }

  const availStock = matchedSku ? (parseInt(matchedSku.stock, 10) || 0) : 99;
  const maxAllowed = Math.min(3, availStock);
  const qtyInput = document.getElementById('pdQty');
  if (qtyInput && parseInt(qtyInput.value, 10) > maxAllowed) {
    qtyInput.value = Math.max(1, maxAllowed);
  }

  if (priceEl) {
    priceEl.textContent = '৳' + finalPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  if (regEl && baseReg > 0) {
    const adj = matchedSku ? (parseFloat(matchedSku.price_adjustment) || 0) : 0;
    const finalReg = baseReg <= 0 ? Math.max(0, adj) : Math.max(0, baseReg + adj);
    regEl.textContent = '৳' + finalReg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

  syncPdpVariantStockAndPrice();
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
