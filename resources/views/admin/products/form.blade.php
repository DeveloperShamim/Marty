@extends('layouts.admin')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit: ' . $product->name : 'Create New Product — Marty Admin')

@section('content')
@php
  if ($editing) {
    $sizes = collect();
    $colors = collect();

    foreach ($product->skus as $sku) {
      foreach ($sku->getAttributesData() as $k => $v) {
        $kLower = strtolower(trim((string)$k));
        $vTrim = trim((string)$v);
        if ($vTrim === '') continue;
        if (in_array($kLower, ['size', 'weight', 'volume', 'unit'])) {
          $sizes->push($vTrim);
        } elseif (in_array($kLower, ['color', 'packaging', 'flavor', 'type', 'container'])) {
          $colors->push($vTrim);
        } elseif (preg_match('/\d+\s*(g|kg|l|ml|oz|lb|liter|litre|gm|gram)/i', $vTrim)) {
          $sizes->push($vTrim);
        } else {
          $colors->push($vTrim);
        }
      }
    }

    foreach ($product->variants as $v) {
      $kLower = strtolower(trim((string)$v->type));
      $vTrim = trim((string)$v->value);
      if ($vTrim === '') continue;
      if (in_array($kLower, ['size', 'weight', 'volume', 'unit'])) {
        $sizes->push($vTrim);
      } elseif (in_array($kLower, ['color', 'packaging', 'flavor', 'type', 'container'])) {
        $colors->push($vTrim);
      } elseif (preg_match('/\d+\s*(g|kg|l|ml|oz|lb|liter|litre|gm|gram)/i', $vTrim)) {
        $sizes->push($vTrim);
      } else {
        $colors->push($vTrim);
      }
    }

    $sizeValues = $sizes->unique()->filter()->implode(', ');
    $colorValues = $colors->unique()->filter()->implode(', ');
  } else {
    $sizeValues = '';
    $colorValues = '';
  }
@endphp

<form id="productForm" method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-5 sm:space-y-6 max-w-full pb-20 sm:pb-8">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Sticky Action Bar Header --}}
  <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md -mx-3.5 sm:-mx-6 lg:-mx-8 px-3.5 sm:px-6 lg:px-8 py-3 border-b border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4">
    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
      <a href="{{ route('admin.products.index') }}" class="h-8 w-8 sm:h-9 sm:w-9 rounded-xl border border-stone-200 bg-white hover:bg-stone-100 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors shrink-0 font-extrabold text-sm" title="Back to Products List">
        ‹
      </a>
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
          <h1 class="text-sm sm:text-lg lg:text-xl font-extrabold text-stone-900 truncate tracking-tight">
            {{ $editing ? $product->name : 'Create New Product' }}
          </h1>
          @if($editing)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $product->is_published ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
              <span class="w-1.5 h-1.5 rounded-full {{ $product->is_published ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
              <span>{{ $product->is_published ? 'Live on Store' : 'Draft' }}</span>
            </span>
          @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
              New Draft
            </span>
          @endif
        </div>
        <p class="text-[10px] sm:text-xs text-stone-500 truncate mt-0.5">
          {{ $editing ? 'Manage product details, pricing, variations, stock & gallery' : 'Fill out product details to list a new catalog item' }}
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 justify-end">
      @if($editing && $product->is_published)
        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="px-3 py-1.5 sm:py-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs shadow-2xs transition-all flex items-center justify-center gap-1 shrink-0">
          <span>👁️ View</span>
        </a>
      @endif

      <a href="{{ route('admin.products.index') }}" class="px-3 py-1.5 sm:py-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-100 text-stone-600 font-bold text-xs transition-colors shrink-0 text-center">
        Cancel
      </a>

      <button type="submit" class="flex-1 sm:flex-none px-4 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ $editing ? 'Update Product' : 'Publish Product' }}</span>
      </button>
    </div>
  </div>

  {{-- Main Layout Grid: Full Responsive Desktop (12-Col) & Tablet/Mobile --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 xl:gap-8 items-start">

    {{-- Left Column (Main Form Content - 8 cols on Desktop) --}}
    <div class="lg:col-span-8 xl:col-span-8 space-y-6">

      {{-- Card 1: Basic Product Information --}}
      <div class="bg-white p-5 sm:p-6 lg:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 font-black text-sm flex items-center justify-center border border-brand-100">1</div>
            <div>
              <h2 class="text-sm sm:text-base font-extrabold text-stone-900">Basic Information</h2>
              <p class="text-[11px] text-stone-500">Core identification, title, slug, brand &amp; categorisation</p>
            </div>
          </div>
          <span class="text-[11px] font-bold text-stone-400 font-mono">Step 1 of 5</span>
        </div>

        <div class="space-y-4">
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-bold text-stone-800">Product Name <span class="text-rose-500">*</span></label>
              <span id="nameCharCount" class="text-[10px] font-mono text-stone-400">0 chars</span>
            </div>
            <input type="text" id="productNameInput" name="name" class="w-full px-3.5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all" value="{{ old('name', $product->name) }}" placeholder="e.g. Wood Milled Cold Pressed Mustard Oil" required />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-bold text-stone-800">URL Slug</label>
                <button type="button" id="autoSlugBtn" class="text-[10px] font-bold text-brand-600 hover:text-brand-800 hover:underline cursor-pointer">Auto Generate</button>
              </div>
              <input type="text" id="productSlugInput" name="slug" class="w-full px-3.5 py-2.5 text-xs font-mono text-stone-700 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" value="{{ old('slug', $product->slug) }}" placeholder="e.g. wood-milled-cold-pressed-mustard-oil" />
            </div>

            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1.5">Base SKU Code</label>
              <input type="text" name="sku" class="w-full px-3.5 py-2.5 text-xs font-mono font-bold text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" value="{{ old('sku', $product->sku) }}" placeholder="e.g. MARTY-OIL-01" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-bold text-stone-800">Brand / Producer</label>
                <a href="{{ route('admin.brands.create') }}" target="_blank" class="text-[10px] font-bold text-brand-600 hover:underline">+ New Brand</a>
              </div>
              <select name="brand_id" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                <option value="">-- Direct Brand / In-House --</option>
                @foreach($brands as $b)
                  <option value="{{ $b->id }}" @selected((int) old('brand_id', $product->brand_id) === (int) $b->id)>
                    {{ $b->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1.5">Product Category <span class="text-rose-500">*</span></label>
              <select name="category_id" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === (int) $cat->id)>
                    {{ $cat->icon }} {{ $cat->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1.5">Short Tagline Summary <span class="text-stone-400 font-normal">(Shown on catalog cards)</span></label>
            <textarea name="short_description" rows="2" class="w-full px-3.5 py-2.5 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="e.g. 100% natural cold pressed organic mustard oil with rich aroma and unadulterated flavor.">{{ old('short_description', $product->short_description) }}</textarea>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1.5">Full Detailed Description &amp; Highlights</label>
            <textarea name="description" rows="5" class="w-full px-3.5 py-2.5 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" placeholder="Provide full details, nutritional benefits, harvest origin, and usage instructions...">{{ old('description', $product->description) }}</textarea>
          </div>
        </div>
      </div>

      {{-- Card 2: Pricing & General Inventory --}}
      <div class="bg-white p-5 sm:p-6 lg:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-black text-sm flex items-center justify-center border border-emerald-100">2</div>
            <div>
              <h2 class="text-sm sm:text-base font-extrabold text-stone-900">Pricing &amp; Inventory</h2>
              <p class="text-[11px] text-stone-500">Base pricing, discount rules and default inventory counts</p>
            </div>
          </div>
          <span id="autoStockNotice" class="hidden px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1.5">Regular Price (৳) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" id="regPriceInput" name="regular_price" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-black text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" value="{{ old('regular_price', $product->regular_price) }}" placeholder="e.g. 850" required />
          </div>

          <div>
            <label class="text-xs font-bold text-emerald-800 block mb-1.5">Sale Price (৳)</label>
            <input type="number" step="0.01" id="salePriceInput" name="sale_price" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-black text-emerald-700 bg-emerald-50/40 rounded-xl border border-emerald-200 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20" value="{{ old('sale_price', $product->sale_price) }}" placeholder="e.g. 750 (optional)" />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1.5">Total Stock <span class="text-rose-500">*</span></label>
            <input type="number" name="stock_quantity" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-black text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1.5">Unit / Pack Size</label>
            <input type="text" name="unit" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20" value="{{ old('unit', $product->unit) }}" placeholder="e.g. 500ml, 1 Liter" />
          </div>
        </div>

        <div id="discountBadgePreview" class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs font-bold text-amber-900">
          <span>Discount Applied: <span id="discountPercentText" class="text-brand-700 font-extrabold"></span></span>
          <span class="text-[10px] font-mono text-amber-700 uppercase tracking-wider">Storefront Badge Live</span>
        </div>
      </div>

      {{-- Card 3: Variants & Weight Pack Options --}}
      <div class="bg-white p-5 sm:p-6 lg:p-7 rounded-2xl sm:rounded-3xl border border-emerald-200/80 shadow-2xs space-y-5">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-100 pb-3.5">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 font-black text-sm flex items-center justify-center border border-teal-100">3</div>
            <div>
              <h2 class="text-sm sm:text-base font-extrabold text-stone-900">Weight, Size &amp; Pack Options (Variants)</h2>
              <p class="text-[11px] text-stone-500">Generate option variations (e.g. 250ml, 500ml, 1L) with custom prices and stock per size</p>
            </div>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto shrink-0">Variant Matrix</span>
        </div>

        {{-- Dynamic Attribute Presets Bar --}}
        @php
          $dbAttributeTypes = \App\Models\ProductAttributeType::with('values')->where('is_active', true)->orderBy('position')->orderBy('name')->get();
        @endphp
        <div class="space-y-4 bg-emerald-50/50 p-4 sm:p-5 rounded-2xl border border-emerald-200/80">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 border-b border-emerald-100 pb-2.5">
            <span class="text-xs font-black text-emerald-950 uppercase tracking-wider">⚡ Attribute Quick-Add Presets</span>
            <a href="{{ route('admin.variations.index') }}" target="_blank" class="text-[11px] font-bold text-emerald-700 hover:underline">Manage Attributes →</a>
          </div>

          {{-- Compact Attribute Filter & Presets List --}}
          <div class="space-y-2">
            @foreach($dbAttributeTypes as $attType)
              @if($attType->values->isNotEmpty())
                @php
                  $isPrimaryGroup = in_array(strtolower($attType->name), ['weight', 'volume', 'size', 'unit']);
                  $targetInputId = $isPrimaryGroup ? 'sizesInput' : 'colorsInput';
                  $badgeClass = $isPrimaryGroup 
                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border-emerald-200/90' 
                    : 'bg-amber-50 hover:bg-amber-100 text-amber-900 border-amber-200/90';
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-white/90 p-2 sm:p-2.5 rounded-xl border border-emerald-100/90 shadow-2xs">
                  <span class="text-[11px] font-extrabold text-stone-700 font-mono uppercase tracking-wider shrink-0 sm:w-24">
                    {{ $attType->name }}:
                  </span>
                  <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
                    @foreach($attType->values as $valObj)
                      <button type="button" class="preset-pill-btn px-2.5 py-1 rounded-lg font-extrabold border shadow-2xs transition cursor-pointer text-[11px] sm:text-xs {{ $badgeClass }}" data-target="{{ $targetInputId }}" data-type="{{ $attType->name }}" data-value="{{ $valObj->value }}">
                        + {{ $valObj->value }}
                      </button>
                    @endforeach
                  </div>
                </div>
              @endif
            @endforeach
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1.5">Primary Weight / Size Options <span class="text-stone-400 font-normal">(Comma-separated)</span></label>
              <input id="sizesInput" name="sizes" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-900 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-2xs" value="{{ old('sizes', $sizeValues) }}" placeholder="e.g. 250g, 500g, 1kg" />
            </div>
            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1.5">Packaging / Container Variant <span class="text-stone-400 font-normal">(Optional)</span></label>
              <input id="colorsInput" name="colors" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-900 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-2xs" value="{{ old('colors', $colorValues) }}" placeholder="e.g. Glass Jar, Craft Pouch" />
            </div>
          </div>

          <div class="flex justify-end pt-1">
            <button type="button" id="generateMatrixBtn" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
              <span>⚡ Generate Combination Matrix</span>
            </button>
          </div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
        @endphp

        {{-- Combination Matrix: Desktop/Tablet Table Layout (`hidden md:block`) --}}
        <div class="hidden md:block overflow-x-auto w-full border border-stone-200 rounded-2xl">
          <table class="w-full text-left text-xs">
            <thead class="bg-stone-100 text-stone-700 font-extrabold border-b border-stone-200 whitespace-nowrap">
              <tr>
                <th class="py-3 px-4">Option / Weight</th>
                <th class="py-3 px-4">SKU Code</th>
                <th class="py-3 px-4 w-36 text-center bg-stone-200/60">Regular Price (৳)</th>
                <th class="py-3 px-4 w-36 text-center bg-emerald-100/60 text-emerald-900">Sale Price (৳)</th>
                <th class="py-3 px-4 w-28 text-center">Stock Qty</th>
                <th class="py-3 px-4 w-16 text-center">Active</th>
                <th class="py-3 px-3 w-10 text-center"></th>
              </tr>
            </thead>
            <tbody id="skuMatrixBody" class="divide-y divide-stone-100 bg-white">
              @forelse($skus as $index => $sku)
                @php
                  $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
                  $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
                @endphp
                <tr class="sku-row hover:bg-stone-50/80 transition-colors">
                  <td class="py-3 px-4">
                    <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                    <div class="flex items-center gap-1.5 flex-wrap">
                      @foreach($sku->getAttributesData() as $k => $v)
                        <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                        <span class="bg-emerald-50 text-emerald-800 px-2.5 py-0.5 rounded-lg text-[11px] font-extrabold border border-emerald-200/80">{{ $k }}: {{ $v }}</span>
                      @endforeach
                    </div>
                  </td>
                  <td class="py-3 px-4">
                    <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="SKU" class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg border border-stone-200 focus:outline-none focus:border-brand-500" />
                  </td>
                  <td class="py-3 px-4">
                    <input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $isCustomReg ? $sku->regular_price : '' }}" class="w-full px-2.5 py-1.5 text-xs text-center font-bold rounded-lg border border-stone-200 sku-regular-price-input focus:outline-none focus:border-brand-500" placeholder="Auto Base" />
                  </td>
                  <td class="py-3 px-4">
                    <input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $isCustomSale ? $sku->sale_price : '' }}" class="w-full px-2.5 py-1.5 text-xs text-center font-black text-emerald-700 bg-emerald-50/40 rounded-lg border border-emerald-200 sku-sale-price-input focus:outline-none focus:border-emerald-500" placeholder="Auto Base" />
                  </td>
                  <td class="py-3 px-4">
                    <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="w-full px-2.5 py-1.5 text-xs text-center font-bold rounded-lg border border-stone-200 sku-stock-input focus:outline-none focus:border-brand-500" required />
                  </td>
                  <td class="py-3 px-4 text-center">
                    <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
                  </td>
                  <td class="py-3 px-3 text-center">
                    <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-rose-400 hover:text-rose-600 font-bold text-base cursor-pointer">×</button>
                  </td>
                </tr>
              @empty
                <tr id="emptyMatrixRow">
                  <td colspan="7" class="py-8 text-center text-stone-400 italic bg-stone-50/50">
                    🌿 No weight or size options generated yet.<br/>
                    Enter weights above (e.g. <strong class="text-stone-800">250g, 500g, 1kg</strong>) and click <strong class="text-emerald-700">"⚡ Generate Combination Matrix"</strong>.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Combination Matrix: Mobile Card View (`block md:hidden`) --}}
        <div id="skuMatrixMobileCards" class="block md:hidden space-y-3">
          @forelse($skus as $index => $sku)
            @php
              $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
              $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
            @endphp
            <div class="sku-mobile-card bg-stone-50/80 p-3 rounded-2xl border border-stone-200 space-y-2.5 shadow-2xs">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-1 flex-wrap">
                  @foreach($sku->getAttributesData() as $k => $v)
                    <span class="bg-emerald-100 text-emerald-900 px-2 py-0.5 rounded-md text-[11px] font-extrabold border border-emerald-200">{{ $k }}: {{ $v }}</span>
                  @endforeach
                </div>
                <div class="flex items-center gap-2">
                  <label class="flex items-center gap-1 text-[11px] font-bold text-stone-600 cursor-pointer">
                    <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-3.5 w-3.5" />
                    <span>Active</span>
                  </label>
                  <button type="button" onclick="this.closest('.sku-mobile-card').remove(); updateMatrixCalculations();" class="text-rose-500 hover:text-rose-700 font-black text-sm px-1">✕</button>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="text-[10px] font-bold text-stone-500 block">Reg Price (৳)</label>
                  <input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $isCustomReg ? $sku->regular_price : '' }}" class="w-full px-2.5 py-1.5 text-xs text-center font-bold rounded-lg border border-stone-200 bg-white" placeholder="Auto Base" />
                </div>
                <div>
                  <label class="text-[10px] font-bold text-emerald-800 block">Sale Price (৳)</label>
                  <input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $isCustomSale ? $sku->sale_price : '' }}" class="w-full px-2.5 py-1.5 text-xs text-center font-black text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200" placeholder="Auto Base" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="text-[10px] font-bold text-stone-500 block">Stock Qty</label>
                  <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="w-full px-2.5 py-1.5 text-xs text-center font-bold rounded-lg border border-stone-200 bg-white" required />
                </div>
                <div>
                  <label class="text-[10px] font-bold text-stone-500 block">Custom SKU</label>
                  <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="Auto SKU" class="w-full px-2 py-1.5 text-xs font-mono rounded-lg border border-stone-200 bg-white" />
                </div>
              </div>
            </div>
          @empty
            <div id="emptyMatrixMobile" class="py-6 text-center text-stone-400 italic bg-stone-50 rounded-xl border border-stone-200 text-xs">
              🌿 No variations generated yet. Tap <strong>"⚡ Generate"</strong> above.
            </div>
          @endforelse
        </div>
      </div>

      {{-- Card 4: Product Specifications Builder --}}
      @php
        $oldLabels = old('spec_labels');
        $oldValues = old('spec_values');
        if (is_array($oldLabels)) {
          $specRows = [];
          foreach ($oldLabels as $i => $label) {
            $specRows[] = ['label' => $label, 'value' => $oldValues[$i] ?? ''];
          }
        } else {
          $specRows = $editing ? $product->specificationRows() : [];
        }
        if (empty($specRows)) {
          $specRows = [
            ['label' => 'Purity Standard', 'value' => '100% Pure & Unadulterated'],
            ['label' => 'Source / Origin', 'value' => 'Direct Farm Sourced'],
            ['label' => 'Shelf Life', 'value' => '12 Months'],
          ];
        }
      @endphp
      <div class="bg-white p-5 sm:p-6 lg:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 font-black text-sm flex items-center justify-center border border-amber-100">4</div>
            <div>
              <h2 class="text-sm sm:text-base font-extrabold text-stone-900">Specifications &amp; Key Highlights</h2>
              <p class="text-[11px] text-stone-500">Key features shown as bullet points and specification table on storefront</p>
            </div>
          </div>
          <button type="button" id="addSpecRow" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-800 shadow-2xs cursor-pointer shrink-0 transition-colors">+ Add Feature</button>
        </div>

        <div id="specRows" class="space-y-3">
          @foreach($specRows as $row)
            <div class="spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-3 items-center p-3 sm:p-2.5 bg-stone-50 rounded-xl border border-stone-200/80">
              <div>
                <label class="text-[10px] font-bold text-stone-500 block uppercase">Feature Name</label>
                <input name="spec_labels[]" class="w-full px-3 py-2 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white focus:outline-none focus:border-brand-500" value="{{ $row['label'] ?? '' }}" placeholder="e.g. Shelf Life" />
              </div>
              <div>
                <label class="text-[10px] font-bold text-stone-500 block uppercase">Value / Detail</label>
                <input name="spec_values[]" class="w-full px-3 py-2 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white focus:outline-none focus:border-brand-500" value="{{ $row['value'] ?? '' }}" placeholder="e.g. 12 Months" />
              </div>
              <button type="button" class="remove-spec-row sm:mt-4 h-8 w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors self-end sm:self-center cursor-pointer" title="Remove Feature">×</button>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Card 5: Media Gallery & Drag-and-Drop Image Uploader --}}
      <div class="bg-white p-5 sm:p-6 lg:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 font-black text-sm flex items-center justify-center border border-purple-100">5</div>
            <div>
              <h2 class="text-sm sm:text-base font-extrabold text-stone-900">Product Media Gallery</h2>
              <p class="text-[11px] text-stone-500">Upload high-resolution photos on white background</p>
            </div>
          </div>
          <span class="text-[11px] font-bold text-stone-400 font-mono">Max 5MB / img</span>
        </div>

        {{-- Existing Uploaded Product Photos --}}
        @if($editing && $product->images->isNotEmpty())
          <div>
            <div class="flex items-center justify-between mb-2.5">
              <label class="text-xs font-bold text-stone-800">Existing Uploaded Images ({{ $product->images->count() }})</label>
              <span class="text-[10px] font-bold text-stone-400">💡 Drag cards or click ◄ ► arrows to re-order</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3" id="productImagesGrid">
              @foreach($product->images as $imgIndex => $img)
                <div class="existing-image-card relative group bg-stone-50 border border-stone-200 rounded-xl p-1.5 flex flex-col items-center gap-1 shadow-2xs cursor-grab active:cursor-grabbing hover:border-brand-300 transition-all" id="imgcard-{{ $img->id }}" draggable="true" data-image-id="{{ $img->id }}">
                  <input type="hidden" name="image_positions[{{ $img->id }}]" class="image-position-input" value="{{ $imgIndex }}" />
                  <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden flex items-center justify-center border border-stone-100">
                    <img src="{{ $img->url() }}" class="max-h-full max-w-full object-contain p-1 pointer-events-none" alt="{{ $img->alt }}" />
                    <span class="main-badge absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs {{ $loop->first ? '' : 'hidden' }}">Main</span>
                    <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute top-1 right-1 bg-rose-600 text-white h-5 w-5 rounded-full text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-md cursor-pointer" title="Delete Image">&times;</button>
                  </div>

                  {{-- Reorder Control Arrows --}}
                  <div class="flex items-center justify-between w-full px-1 py-0.5 bg-stone-100/80 rounded-md border border-stone-200 text-[10px] font-bold text-stone-600">
                    <button type="button" class="move-image-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="left" title="Move Left">◄</button>
                    <span class="text-[9px] text-stone-400 uppercase tracking-tighter">Order</span>
                    <button type="button" class="move-image-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="right" title="Move Right">►</button>
                  </div>

                  <div class="w-full mt-0.5">
                    <label class="text-[9px] font-bold text-stone-500 block text-center mb-0.5 uppercase tracking-wider">Variation Tag</label>
                    <input type="text" name="image_colors[{{ $img->id }}]" value="{{ old("image_colors.{$img->id}", $img->color) }}" placeholder="e.g. 500ml, Jar" class="w-full text-[10px] font-bold px-1.5 py-1 bg-white border border-stone-200 rounded-lg text-center text-stone-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs" />
                  </div>
                  <div class="w-full text-[8.5px] font-semibold text-stone-500 bg-stone-100/70 p-1 rounded-md border border-stone-200/60 truncate" title="SEO Alt: {{ $img->alt }}">
                    ⚡ Alt: <span class="text-stone-800 font-bold">{{ $img->alt }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Upload Drag-and-Drop Area --}}
        <div class="border-2 border-dashed border-stone-300 hover:border-brand-500 rounded-2xl p-6 text-center bg-stone-50/60 hover:bg-brand-50/20 transition-all cursor-pointer relative">
          <input id="imageFileInput" name="images[]" type="file" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
          <div class="flex flex-col items-center gap-2">
            <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl">📸</div>
            <p class="text-xs font-extrabold text-stone-800">Click or Drag &amp; Drop Product Photos Here</p>
            <p class="text-[11px] text-stone-400">Upload clean PNG, JPG, or WEBP images with white background</p>
          </div>
        </div>

        {{-- Live New Uploads Preview Grid --}}
        <div id="newImagesPreview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3"></div>
      </div>
    </div>

    {{-- Right Sidebar Column (Sticky on Desktop: Publish Box, Organization, Badges & SEO - 4 cols on Desktop) --}}
    <div class="lg:col-span-4 xl:col-span-4 lg:sticky lg:top-20 space-y-5 sm:space-y-6">

      {{-- Card 0: Desktop Quick Publish Widget --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <h3 class="text-xs sm:text-sm font-extrabold text-stone-900">Publish Action</h3>
          <span class="text-[10px] font-mono text-stone-400">⌘S / Ctrl+S</span>
        </div>

        <div class="space-y-2.5">
          <button type="submit" class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>{{ $editing ? 'Save Product Changes' : 'Publish Product Now' }}</span>
          </button>

          <div class="flex items-center gap-2">
            @if($editing && $product->is_published)
              <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="flex-1 py-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs shadow-2xs transition-all text-center flex items-center justify-center gap-1">
                <span>👁️ Live View</span>
              </a>
            @endif
            <a href="{{ route('admin.products.index') }}" class="flex-1 py-2 rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-600 font-bold text-xs transition-colors text-center">
              Discard
            </a>
          </div>
        </div>

        @if($editing)
          <div class="pt-2 border-t border-stone-100 text-[11px] text-stone-500 space-y-1">
            <div class="flex justify-between">
              <span>Status:</span>
              <strong class="{{ $product->is_published ? 'text-emerald-700' : 'text-amber-700' }}">{{ $product->is_published ? 'Published' : 'Draft' }}</strong>
            </div>
            <div class="flex justify-between">
              <span>Created:</span>
              <span class="font-mono text-stone-700">{{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
              <span>Updated:</span>
              <span class="font-mono text-stone-700">{{ $product->updated_at ? $product->updated_at->diffForHumans() : 'N/A' }}</span>
            </div>
          </div>
        @endif
      </div>

      {{-- Card: Storefront Badges & Visibility --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <h3 class="text-xs sm:text-sm font-extrabold text-stone-900 border-b border-stone-100 pb-2.5">Storefront Visibility &amp; Badges</h3>
        @php
          $toggles = [
            'is_published'   => ['Published on Storefront', 'Visible to shoppers for direct purchase'],
            'is_featured'    => ['⭐ Featured Product', 'Highlighted on home page flagship section'],
            'is_flash_sale'  => ['⚡ Flash Sale Deal', 'Promoted inside limited-time deal section'],
            'is_best_seller' => ['🏆 Best Seller', 'Show badge on catalog card'],
            'is_new_arrival' => ['🆕 New Arrival', 'Show new arrival badge'],
          ];
        @endphp
        <div class="space-y-2">
          @foreach($toggles as $field => [$label, $hint])
            <label class="flex items-start justify-between gap-3 text-xs font-bold text-stone-800 cursor-pointer p-2.5 rounded-xl hover:bg-stone-50 transition-colors border border-transparent hover:border-stone-200">
              <div>
                <span class="block text-xs font-extrabold text-stone-900">{{ $label }}</span>
                <span class="text-[10px] font-normal text-stone-400 block mt-0.5">{{ $hint }}</span>
              </div>
              <input type="checkbox" name="{{ $field }}" value="1" class="accent-brand-600 h-4 w-4 rounded cursor-pointer mt-0.5 shrink-0" @checked(old($field, $product->$field)) />
            </label>
          @endforeach
        </div>
      </div>

      {{-- Card: Search Engine Optimization (SEO) --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <h3 class="text-xs sm:text-sm font-extrabold text-stone-900 border-b border-stone-100 pb-2.5">🔍 Search Engine Optimization (SEO)</h3>

        {{-- Live Google Search Preview --}}
        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200 text-xs space-y-1">
          <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Google Search Preview</span>
          <p id="seoPreviewTitle" class="text-xs sm:text-sm font-bold text-blue-700 truncate hover:underline cursor-pointer">
            {{ old('meta_title', $product->meta_title) ?: ($editing ? $product->name . ' — Marty' : 'Product Name — Marty') }}
          </p>
          <p class="text-[11px] text-emerald-700 truncate font-mono">
            {{ url('/products') }}/<span id="seoPreviewSlug">{{ old('slug', $product->slug) ?: 'product-slug' }}</span>
          </p>
          <p id="seoPreviewDesc" class="text-[11px] text-stone-600 line-clamp-2">
            {{ old('meta_description', $product->meta_description) ?: 'Buy 100% pure organic food online in Bangladesh at best prices.' }}
          </p>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Title</label>
            <input type="text" id="metaTitleInput" name="meta_title" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('meta_title', $product->meta_title) }}" placeholder="e.g. Buy Pure Mustard Oil Online — Marty" />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Description</label>
            <textarea id="metaDescInput" name="meta_description" rows="2" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" placeholder="Short description for Google search results...">{{ old('meta_description', $product->meta_description) }}</textarea>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="e.g. mustard oil, organic food bd" />
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- Floating Action Bar on Mobile Viewports --}}
  <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-stone-200 p-3 sm:hidden shadow-lg flex items-center justify-between gap-2">
    <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 rounded-xl border border-stone-200 bg-white text-stone-600 font-bold text-xs">
      Cancel
    </a>
    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>{{ $editing ? 'Save Changes' : 'Publish Product' }}</span>
    </button>
  </div>
</form>

@if($editing)
  @foreach($product->images as $img)
    <form id="delimg{{ $img->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}" onsubmit="return confirm('Remove this image?')">@csrf @method('DELETE')</form>
  @endforeach
@endif

@push('scripts')
<script>
(function () {
  // Live Name & Slug & SEO Binding
  const nameInput = document.getElementById('productNameInput');
  const slugInput = document.getElementById('productSlugInput');
  const nameCount = document.getElementById('nameCharCount');
  const autoSlugBtn = document.getElementById('autoSlugBtn');

  const seoTitle = document.getElementById('seoPreviewTitle');
  const seoSlug = document.getElementById('seoPreviewSlug');
  const seoDesc = document.getElementById('seoPreviewDesc');
  const metaTitle = document.getElementById('metaTitleInput');
  const metaDesc = document.getElementById('metaDescInput');

  function slugify(text) {
    return text.toString().toLowerCase().trim()
      .replace(/[\s\W-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (nameInput) {
    nameInput.addEventListener('input', () => {
      const len = nameInput.value.length;
      if (nameCount) nameCount.innerText = `${len} chars`;

      if (seoTitle && (!metaTitle || !metaTitle.value)) {
        seoTitle.innerText = nameInput.value ? `${nameInput.value} — Marty` : 'Product Name — Marty';
      }
    });
  }

  if (autoSlugBtn && nameInput && slugInput) {
    autoSlugBtn.addEventListener('click', () => {
      slugInput.value = slugify(nameInput.value);
      if (seoSlug) seoSlug.innerText = slugInput.value;
    });
  }

  if (slugInput && seoSlug) {
    slugInput.addEventListener('input', () => {
      seoSlug.innerText = slugInput.value || 'product-slug';
    });
  }

  if (metaTitle && seoTitle) {
    metaTitle.addEventListener('input', () => {
      seoTitle.innerText = metaTitle.value || (nameInput.value ? `${nameInput.value} — Marty` : 'Product Name — Marty');
    });
  }

  if (metaDesc && seoDesc) {
    metaDesc.addEventListener('input', () => {
      seoDesc.innerText = metaDesc.value || 'Buy 100% pure organic food online in Bangladesh at best prices.';
    });
  }

  // Live Price & Discount Calculator
  const regInput = document.getElementById('regPriceInput');
  const saleInput = document.getElementById('salePriceInput');
  const discountBox = document.getElementById('discountBadgePreview');
  const discountText = document.getElementById('discountPercentText');

  function updateDiscountBadge() {
    const reg = parseFloat(regInput ? regInput.value : 0) || 0;
    const sale = parseFloat(saleInput ? saleInput.value : 0) || 0;

    if (reg > 0 && sale > 0 && sale < reg) {
      const diff = reg - sale;
      const pct = Math.round((diff / reg) * 100);
      if (discountText) discountText.innerText = `${pct}% OFF (Save ৳${diff.toFixed(0)})`;
      if (discountBox) discountBox.classList.remove('hidden');
    } else if (discountBox) {
      discountBox.classList.add('hidden');
    }
  }

  if (regInput) regInput.addEventListener('input', updateDiscountBadge);
  if (saleInput) saleInput.addEventListener('input', updateDiscountBadge);
  updateDiscountBadge();
})();

// Specification Builder Script
(function () {
  const list = document.getElementById('specRows');
  const addBtn = document.getElementById('addSpecRow');
  if (!list || !addBtn) return;

  function bindRemove(btn) {
    btn.addEventListener('click', () => {
      const rows = list.querySelectorAll('.spec-row');
      if (rows.length <= 1) {
        rows[0].querySelectorAll('input').forEach((i) => { i.value = ''; });
        return;
      }
      btn.closest('.spec-row')?.remove();
    });
  }

  list.querySelectorAll('.remove-spec-row').forEach(bindRemove);

  addBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-2.5 sm:gap-3 items-center p-3 bg-stone-50 rounded-xl border border-stone-200/80';
    row.innerHTML = `
      <div>
        <label class="text-[10px] font-bold text-stone-500 block uppercase">Feature Name</label>
        <input name="spec_labels[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. Shelf Life" />
      </div>
      <div>
        <label class="text-[10px] font-bold text-stone-500 block uppercase">Value / Detail</label>
        <input name="spec_values[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. 12 Months" />
      </div>
      <button type="button" class="remove-spec-row sm:mt-4 h-7 w-7 sm:h-8 sm:w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors self-end sm:self-center cursor-pointer" title="Remove Feature">×</button>
    `;
    list.appendChild(row);
    bindRemove(row.querySelector('.remove-spec-row'));
  });
})();

// Preset Pills & Variant Matrix Script
(function () {
  const presetTypeMap = {};

  document.querySelectorAll('.preset-pill-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = btn.dataset.target;
      const val = btn.dataset.value;
      const attType = btn.dataset.type;
      if (attType && val) {
        presetTypeMap[val.toLowerCase().trim()] = attType;
      }
      const input = document.getElementById(targetId);
      if (!input) return;
      const current = input.value.split(',').map(s => s.trim()).filter(Boolean);
      if (!current.includes(val)) {
        current.push(val);
        input.value = current.join(', ');
      }
    });
  });

  function detectAttrType(val) {
    const vLower = val.toLowerCase().trim();
    if (presetTypeMap[vLower]) return presetTypeMap[vLower];

    if (/^(black|brown|natural gold|white|red|blue|green|yellow|silver|gold|grey|gray|pink|purple|orange|navy|cream|maroon)$/i.test(vLower)) {
      return 'Color';
    }
    if (/^(original|raw honey|black seed infused|spicy|honey|infused)$/i.test(vLower)) {
      return 'Flavor';
    }
    if (/glass|plastic|jar|bottle|pouch|can|box|container|pack/i.test(vLower)) {
      return 'Packaging';
    }
    if (/\d+\s*(l|ml|liter|litre)/i.test(vLower)) {
      return 'Volume';
    }
    if (/\d+\s*(g|kg|oz|lb|gm|gram)/i.test(vLower)) {
      return 'Weight';
    }
    if (/^(s|m|l|xl|xxl|eu\s*\d+|\d+)$/i.test(vLower)) {
      return 'Size';
    }
    return 'Size';
  }

  const genBtn = document.getElementById('generateMatrixBtn');
  const body = document.getElementById('skuMatrixBody');
  const mobileCardsContainer = document.getElementById('skuMatrixMobileCards');
  const sizesInput = document.getElementById('sizesInput');
  const colorsInput = document.getElementById('colorsInput');

  if (!genBtn) return;

  genBtn.addEventListener('click', () => {
    let rawSizes = (sizesInput ? sizesInput.value : '').split(',').map(s => s.trim()).filter(Boolean);
    let rawColors = (colorsInput ? colorsInput.value : '').split(',').map(c => c.trim()).filter(Boolean);

    if (rawSizes.length === 0 && rawColors.length === 0) {
      alert('Please enter at least one Weight or Size option first.');
      return;
    }

    const groupedAttrs = {};
    [...rawSizes, ...rawColors].forEach(item => {
      const type = detectAttrType(item);
      if (!groupedAttrs[type]) groupedAttrs[type] = [];
      if (!groupedAttrs[type].includes(item)) groupedAttrs[type].push(item);
    });

    const attrKeys = Object.keys(groupedAttrs);
    if (attrKeys.length === 0) {
      alert('Please select or enter variation options.');
      return;
    }

    function cartesianProduct(keys, index = 0, current = {}) {
      if (index === keys.length) return [{ ...current }];
      const key = keys[index];
      const results = [];
      groupedAttrs[key].forEach(val => {
        results.push(...cartesianProduct(keys, index + 1, { ...current, [key]: val }));
      });
      return results;
    }

    const combinations = cartesianProduct(attrKeys);

    if (body) body.innerHTML = '';
    if (mobileCardsContainer) mobileCardsContainer.innerHTML = '';

    combinations.forEach((combo, idx) => {
      let attrBadgesHtml = '';
      let attrInputsHtml = '';
      Object.keys(combo).forEach(k => {
        const v = combo[k];
        attrBadgesHtml += `<span class="bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded text-[11px] font-extrabold border border-emerald-200">${k}: ${v}</span> `;
        attrInputsHtml += `<input type="hidden" name="sku_matrix[${idx}][attributes][${k}]" value="${v}" />`;
      });

      let sizeVal = (combo.Weight || combo.Volume || combo.Size || '').replace(/[^A-Za-z0-9]/g, '');
      let colorVal = (combo.Packaging || combo.Color || combo.Flavor || '').split(' ')[0].replace(/[^A-Za-z0-9]/g, '');
      let autoSkuHint = (colorVal || sizeVal) ? (colorVal + sizeVal) : 'VAR';

      // Desktop Table Row
      if (body) {
        const row = document.createElement('tr');
        row.className = 'sku-row hover:bg-stone-50/80';
        row.innerHTML = `
          <td class="py-2.5 px-3">
            ${attrInputsHtml}
            <div class="flex items-center gap-1 flex-wrap">${attrBadgesHtml}</div>
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][sku]" value="" placeholder="Auto: [SKU]-${autoSkuHint}" class="w-full px-2 py-1 text-xs font-mono rounded-lg border border-stone-200" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][regular_price]" type="number" step="0.01" value="" class="w-full px-2 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-regular-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][sale_price]" type="number" step="0.01" value="" class="w-full px-2 py-1 text-xs text-center font-black text-emerald-700 bg-emerald-50/40 rounded-lg border border-emerald-200 sku-sale-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][stock]" type="number" min="0" value="10" class="w-full px-2 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-stock-input" required />
          </td>
          <td class="py-2.5 px-3 text-center">
            <input type="checkbox" name="sku_matrix[${idx}][is_active]" value="1" checked class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
          </td>
          <td class="py-2.5 px-2 text-center">
            <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-rose-400 hover:text-rose-600 font-bold text-base cursor-pointer">×</button>
          </td>
        `;
        body.appendChild(row);
      }
    });

    updateMatrixCalculations();

    if (body) {
      body.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  });

  function updateMatrixCalculations() {
    const regInput = document.getElementById('regPriceInput');
    const saleInput = document.getElementById('salePriceInput');
    const stockInput = document.querySelector('input[name="stock_quantity"]');
    const autoStockBadge = document.getElementById('autoStockNotice');

    const regPrice = parseFloat(regInput ? regInput.value : 0) || 0;
    const salePrice = parseFloat(saleInput ? saleInput.value : 0) || 0;
    const baseSale = salePrice > 0 ? salePrice : regPrice;

    let totalStockSum = 0;
    let activeSkusCount = 0;

    document.querySelectorAll('.sku-row').forEach(row => {
      const activeCheck = row.querySelector('.sku-active-check');
      const isActive = !activeCheck || activeCheck.checked;

      const regPriceInput = row.querySelector('.sku-regular-price-input');
      const salePriceInput = row.querySelector('.sku-sale-price-input');
      const stockItemInput = row.querySelector('.sku-stock-input');

      if (regPriceInput && (!regPriceInput.value || regPriceInput.value === '0')) {
        regPriceInput.placeholder = regPrice > 0 ? '৳' + regPrice.toFixed(2) : 'Auto Base';
      }
      if (salePriceInput && (!salePriceInput.value || salePriceInput.value === '0')) {
        salePriceInput.placeholder = baseSale > 0 ? '৳' + baseSale.toFixed(2) : 'Auto Base';
      }

      if (isActive && stockItemInput) {
        totalStockSum += Math.max(0, parseInt(stockItemInput.value, 10) || 0);
        activeSkusCount++;
      }
    });

    if (activeSkusCount > 0 && stockInput) {
      stockInput.value = totalStockSum;
      if (autoStockBadge) {
        autoStockBadge.textContent = `⚡ Total Stock: ${totalStockSum} units (${activeSkusCount} variations)`;
        autoStockBadge.classList.remove('hidden');
      }
    } else if (autoStockBadge) {
      autoStockBadge.classList.add('hidden');
    }
  }

  window.updateMatrixCalculations = updateMatrixCalculations;
  setTimeout(updateMatrixCalculations, 100);
})();

function deleteProductImage(productId, imageId) {
  if (!confirm('Remove this image?')) return;
  const card = document.getElementById('imgcard-' + imageId);
  const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
  const url = '/admin/products/' + productId + '/images/' + imageId;

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: '_method=DELETE&_token=' + encodeURIComponent(token)
  })
  .then(res => {
    if (res.ok) {
      if (card) {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        setTimeout(() => card.remove(), 300);
      }
    } else {
      const form = document.getElementById('delimg' + imageId);
      if (form) form.submit();
      else window.location.reload();
    }
  })
  .catch(() => {
    const form = document.getElementById('delimg' + imageId);
    if (form) form.submit();
    else window.location.reload();
  });
}

// Existing Images Drag & Drop / Button Re-ordering Script
(function() {
  const grid = document.getElementById('productImagesGrid');
  if (!grid) return;

  function updatePositionsAndBadges() {
    const cards = grid.querySelectorAll('.existing-image-card');
    cards.forEach((card, idx) => {
      const posInput = card.querySelector('.image-position-input');
      if (posInput) posInput.value = idx;

      const mainBadge = card.querySelector('.main-badge');
      if (mainBadge) {
        if (idx === 0) mainBadge.classList.remove('hidden');
        else mainBadge.classList.add('hidden');
      }
    });
  }

  grid.addEventListener('click', function(e) {
    const btn = e.target.closest('.move-image-btn');
    if (!btn) return;
    const card = btn.closest('.existing-image-card');
    if (!card) return;
    const dir = btn.dataset.dir;

    if (dir === 'left' && card.previousElementSibling) {
      grid.insertBefore(card, card.previousElementSibling);
    } else if (dir === 'right' && card.nextElementSibling) {
      grid.insertBefore(card.nextElementSibling, card);
    }
    updatePositionsAndBadges();
  });

  // HTML5 Drag and Drop Re-ordering
  let draggedCard = null;

  grid.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.existing-image-card');
    if (!card) return;
    draggedCard = card;
    card.classList.add('opacity-40');
    e.dataTransfer.effectAllowed = 'move';
  });

  grid.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const card = e.target.closest('.existing-image-card');
    if (card && card !== draggedCard) {
      const bounding = card.getBoundingClientRect();
      const offset = e.clientX - bounding.left;
      if (offset > bounding.width / 2) {
        grid.insertBefore(draggedCard, card.nextElementSibling);
      } else {
        grid.insertBefore(draggedCard, card);
      }
      updatePositionsAndBadges();
    }
  });

  grid.addEventListener('dragend', function(e) {
    const card = e.target.closest('.existing-image-card');
    if (card) card.classList.remove('opacity-40');
    draggedCard = null;
    updatePositionsAndBadges();
  });
})();

// Live Drag-and-Drop Image Upload Preview with Auto SEO & Variation Tag Pre-population
(function() {
  const fileInput = document.getElementById('imageFileInput');
  const previewBox = document.getElementById('newImagesPreview');
  if (!fileInput || !previewBox) return;

  function getEnteredVariantOptions() {
    const opts = [];
    const sizes = document.getElementById('sizesInput')?.value?.split(',').map(s => s.trim()).filter(Boolean) || [];
    const colors = document.getElementById('colorsInput')?.value?.split(',').map(c => c.trim()).filter(Boolean) || [];
    return [...sizes, ...colors];
  }

  fileInput.addEventListener('change', function(e) {
    previewBox.innerHTML = '';
    const files = Array.from(e.target.files || []);
    const productName = document.querySelector('input[name="name"]')?.value?.trim() || 'Product';
    const brandSelect = document.querySelector('select[name="brand_id"]');
    const brandName = brandSelect && brandSelect.selectedIndex > 0 ? brandSelect.options[brandSelect.selectedIndex].text.trim() : '';
    const variantOptions = getEnteredVariantOptions();

    files.forEach((file, idx) => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = function(evt) {
        const autoTag = variantOptions[idx] || '';
        let seoAlt = productName;
        if (autoTag) seoAlt += ` (${autoTag})`;
        if (brandName) seoAlt += ` by ${brandName}`;
        seoAlt += ' — 100% Pure & Organic Marty BD';

        const div = document.createElement('div');
        div.className = 'new-image-card relative flex flex-col items-center gap-1 p-1.5 bg-stone-50 border border-stone-200 rounded-xl shadow-2xs cursor-grab';
        div.innerHTML = `
          <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border border-stone-100 flex items-center justify-center">
            <img src="${evt.target.result}" class="max-h-full max-w-full object-contain p-1 pointer-events-none" />
            <span class="new-main-badge absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs ${idx === 0 ? '' : 'hidden'}">Main</span>
          </div>
          <div class="flex items-center justify-between w-full px-1 py-0.5 bg-stone-100/80 rounded-md border border-stone-200 text-[10px] font-bold text-stone-600">
            <button type="button" class="move-new-img-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="left" title="Move Left">◄</button>
            <span class="text-[9px] text-stone-400 uppercase tracking-tighter">Order</span>
            <button type="button" class="move-new-img-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="right" title="Move Right">►</button>
          </div>
          <div class="w-full mt-0.5">
            <label class="text-[9px] font-bold text-stone-500 block text-center mb-0.5 uppercase tracking-wider">Variation Tag</label>
            <input type="text" name="new_image_colors[${idx}]" value="${autoTag}" placeholder="e.g. 500ml, Jar" class="w-full text-[10px] font-bold px-1.5 py-1 bg-white border border-stone-200 rounded-lg text-center text-stone-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs" />
          </div>
          <div class="w-full text-[8.5px] font-semibold text-stone-500 bg-emerald-50/90 p-1 rounded-md border border-emerald-200/60 truncate" title="SEO Alt: ${seoAlt}">
            ⚡ Alt: <span class="text-emerald-900 font-bold">${seoAlt}</span>
          </div>
        `;
        previewBox.appendChild(div);
        updateNewPreviewBadges();
      };
      reader.readAsDataURL(file);
    });
  });

  previewBox.addEventListener('click', function(e) {
    const btn = e.target.closest('.move-new-img-btn');
    if (!btn) return;
    const card = btn.closest('.new-image-card');
    if (!card) return;
    const dir = btn.dataset.dir;

    if (dir === 'left' && card.previousElementSibling) {
      previewBox.insertBefore(card, card.previousElementSibling);
    } else if (dir === 'right' && card.nextElementSibling) {
      previewBox.insertBefore(card.nextElementSibling, card);
    }
    updateNewPreviewBadges();
  });

  function updateNewPreviewBadges() {
    const cards = previewBox.querySelectorAll('.new-image-card');
    cards.forEach((card, idx) => {
      const badge = card.querySelector('.new-main-badge');
      if (badge) {
        if (idx === 0) badge.classList.remove('hidden');
        else badge.classList.add('hidden');
      }
    });
  }
})();

// Desktop Keyboard Shortcut (Ctrl+S or Cmd+S to save/update)
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    const form = document.getElementById('productForm');
    if (form) {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.click();
      else form.submit();
    }
  }
});
</script>
@endpush
@endsection


