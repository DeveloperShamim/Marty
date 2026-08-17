@extends('layouts.admin')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit: ' . $product->name : 'Create New Organic Product — ShodeshiFood Admin')

@section('content')
@php
  if ($editing) {
    $sizesFromVariants = $product->variants->whereIn('type', ['Size', 'Weight', 'Volume', 'Unit'])->pluck('value');
    $colorsFromVariants = $product->variants->whereIn('type', ['Color', 'Packaging', 'Flavor', 'Type'])->pluck('value');

    if ($sizesFromVariants->isEmpty() && $product->skus->isNotEmpty()) {
      $sizesFromVariants = $product->skus->flatMap(function($sku) {
        $attrs = $sku->getAttributesData();
        $res = [];
        foreach ($attrs as $k => $v) {
          if (in_array(strtolower($k), ['size', 'weight', 'volume', 'unit'])) $res[] = $v;
        }
        return $res;
      });
    }

    if ($colorsFromVariants->isEmpty() && $product->skus->isNotEmpty()) {
      $colorsFromVariants = $product->skus->flatMap(function($sku) {
        $attrs = $sku->getAttributesData();
        $res = [];
        foreach ($attrs as $k => $v) {
          if (in_array(strtolower($k), ['color', 'packaging', 'flavor', 'type'])) $res[] = $v;
        }
        return $res;
      });
    }

    $sizeValues = $sizesFromVariants->unique()->filter()->implode(', ');
    $colorValues = $colorsFromVariants->unique()->filter()->implode(', ');
  } else {
    $sizeValues = '';
    $colorValues = '';
  }
@endphp

<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Sticky Action Bar Header --}}
  <div class="sticky top-0 z-30 bg-white/90 backdrop-blur-md -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5 border-b border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
      <a href="{{ route('admin.products.index') }}" class="h-9 w-9 rounded-xl border border-stone-200 bg-white hover:bg-stone-100 flex items-center justify-center text-stone-500 hover:text-stone-900 transition-colors shrink-0" title="Back to Products List">
        ‹
      </a>
      <div class="min-w-0">
        <div class="flex items-center gap-2">
          <h1 class="text-lg sm:text-xl font-extrabold text-stone-900 truncate tracking-tight">
            {{ $editing ? $product->name : 'Create New Product' }}
          </h1>
          @if($editing)
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $product->is_published ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
              <span class="w-1.5 h-1.5 rounded-full {{ $product->is_published ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
              <span>{{ $product->is_published ? 'Live on Store' : 'Draft' }}</span>
            </span>
          @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
              New Draft
            </span>
          @endif
        </div>
        <p class="text-xs text-stone-500 truncate mt-0.5">
          {{ $editing ? 'Manage details, prices, weights & images for this organic food item' : 'Fill out product details to list a new chemical-free organic food item' }}
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2.5 self-end sm:self-auto shrink-0">
      @if($editing && $product->is_published)
        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="px-3 py-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 font-bold text-xs shadow-2xs transition-all flex items-center gap-1.5">
          <span>👁️ View on Store</span>
        </a>
      @endif

      <a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded-xl border border-stone-200 bg-white hover:bg-stone-100 text-stone-600 font-bold text-xs transition-colors">
        Cancel
      </a>

      <button type="submit" class="px-6 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-2">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ $editing ? 'Update Product' : 'Publish Product' }}</span>
      </button>
    </div>
  </div>

  {{-- Main Two-Column Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left Column (Main Form Content) --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Card 1: Basic Product Information --}}
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <h2 class="text-base font-extrabold text-stone-900 flex items-center gap-2">
            <span>📝 Basic Information</span>
          </h2>
          <span class="text-[11px] font-bold text-stone-400">Step 1 of 5</span>
        </div>

        <div class="space-y-4">
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-xs font-bold text-stone-800">Product Name <span class="text-rose-500">*</span></label>
              <span id="nameCharCount" class="text-[10px] font-mono text-stone-400">0 chars</span>
            </div>
            <input type="text" id="productNameInput" name="name" class="w-full px-3.5 py-2.5 text-sm font-bold text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" value="{{ old('name', $product->name) }}" placeholder="e.g. Sundarban Raw Wildflower Honey (সুন্দরবন খাঁটি মধু)" required />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-bold text-stone-800">URL Slug</label>
                <button type="button" id="autoSlugBtn" class="text-[10px] font-bold text-brand-600 hover:underline">Auto Generate</button>
              </div>
              <input type="text" id="productSlugInput" name="slug" class="w-full px-3.5 py-2 text-xs font-mono text-stone-700 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('slug', $product->slug) }}" placeholder="e.g. sundarban-raw-wildflower-honey" />
            </div>

            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1">Base Product SKU Code</label>
              <input type="text" name="sku" class="w-full px-3.5 py-2 text-xs font-mono font-bold text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('sku', $product->sku) }}" placeholder="e.g. SHODESHI-HONEY-01" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-bold text-stone-800">Brand / Producer</label>
                <a href="{{ route('admin.brands.create') }}" target="_blank" class="text-[10px] font-bold text-brand-600 hover:underline">+ New Brand</a>
              </div>
              <select name="brand_id" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-500">
                <option value="">-- No Brand / Direct ShodeshiFood --</option>
                @foreach($brands as $b)
                  <option value="{{ $b->id }}" @selected((int) old('brand_id', $product->brand_id) === (int) $b->id)>
                    {{ $b->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1">Product Category <span class="text-rose-500">*</span></label>
              <select name="category_id" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-500" required>
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
            <label class="text-xs font-bold text-stone-800 block mb-1">Short Tagline Summary <span class="text-stone-400 font-normal">(Shown on catalog cards)</span></label>
            <textarea name="short_description" rows="2" class="w-full px-3.5 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" placeholder="e.g. 100% Unfiltered Sundarban forest honey collected directly from honeycomb. No added sugar or chemicals.">{{ old('short_description', $product->short_description) }}</textarea>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Full Detailed Description & Health Benefits</label>
            <textarea name="description" rows="5" class="w-full px-3.5 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" placeholder="Provide full details, nutritional benefits, harvest origin, and usage instructions...">{{ old('description', $product->description) }}</textarea>
          </div>
        </div>
      </div>

      {{-- Card 2: Pricing & General Inventory --}}
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <h2 class="text-base font-extrabold text-stone-900 flex items-center gap-2">
            <span>💰 Pricing &amp; Inventory</span>
          </h2>
          <span id="autoStockNotice" class="hidden px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300"></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Regular Price (৳) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" id="regPriceInput" name="regular_price" class="w-full px-3.5 py-2.5 text-sm font-black text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('regular_price', $product->regular_price) }}" placeholder="e.g. 850" required />
          </div>

          <div>
            <label class="text-xs font-bold text-emerald-800 block mb-1">Sale Discount Price (৳)</label>
            <input type="number" step="0.01" id="salePriceInput" name="sale_price" class="w-full px-3.5 py-2.5 text-sm font-black text-emerald-700 bg-emerald-50/40 rounded-xl border border-emerald-200 focus:outline-none focus:border-emerald-500" value="{{ old('sale_price', $product->sale_price) }}" placeholder="e.g. 750 (optional)" />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Total Stock (Units) <span class="text-rose-500">*</span></label>
            <input type="number" name="stock_quantity" class="w-full px-3.5 py-2.5 text-sm font-black text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Unit / Pack Size</label>
            <input type="text" name="unit" class="w-full px-3.5 py-2.5 text-xs font-bold text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" value="{{ old('unit', $product->unit) }}" placeholder="e.g. 500g Jar, 1 Kg" />
          </div>
        </div>

        <div id="discountBadgePreview" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs font-bold text-amber-900">
          <span>Discount Applied: <span id="discountPercentText" class="text-brand-700 font-extrabold"></span></span>
          <span class="text-[10px] font-mono text-amber-700 uppercase tracking-wider">Storefront Badge Live</span>
        </div>
      </div>

      {{-- Card 3: Variants & Weight Pack Options --}}
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-emerald-200/80 shadow-2xs space-y-5">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
          <div>
            <h2 class="text-base font-extrabold text-stone-900 flex items-center gap-2">
              <span>🌿 Weight, Size &amp; Pack Options (Variants)</span>
            </h2>
            <p class="text-xs text-stone-500 mt-0.5">Generate option variations (e.g. 250g, 500g, 1kg) with custom price &amp; stock per size.</p>
          </div>
          <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Variant Matrix</span>
        </div>

        {{-- Preset Options Quick Bar --}}
        <div class="space-y-3 bg-emerald-50/40 p-4 rounded-xl border border-emerald-100">
          <div class="flex items-center justify-between">
            <label class="text-xs font-extrabold text-emerald-900 uppercase tracking-wider">⚡ Quick Weight Presets</label>
            <a href="{{ route('admin.variations.index') }}" target="_blank" class="text-[11px] font-bold text-emerald-700 hover:underline">Manage All Attributes →</a>
          </div>

          <div class="flex flex-wrap gap-1.5">
            @php
              $quickWeights = ['250g', '500g', '1 kg', '2 kg', '500 ml', '1 Liter', 'Glass Jar', 'Plastic Bottle'];
            @endphp
            @foreach($quickWeights as $qw)
              <button type="button" class="preset-pill-btn px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-900 font-bold border border-emerald-200 shadow-2xs transition cursor-pointer text-xs" data-target="sizesInput" data-value="{{ $qw }}">
                + {{ $qw }}
              </button>
            @endforeach
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1">Primary Weight / Size Options <span class="text-stone-400 font-normal">(Comma-separated)</span></label>
              <input id="sizesInput" name="sizes" class="w-full px-3.5 py-2 text-xs font-bold text-stone-900 rounded-xl border border-stone-200 bg-white" value="{{ old('sizes', $sizeValues) }}" placeholder="e.g. 250g, 500g, 1kg" />
            </div>
            <div>
              <label class="text-xs font-bold text-stone-800 block mb-1">Packaging / Container Variant <span class="text-stone-400 font-normal">(Optional)</span></label>
              <input id="colorsInput" name="colors" class="w-full px-3.5 py-2 text-xs font-bold text-stone-900 rounded-xl border border-stone-200 bg-white" value="{{ old('colors', $colorValues) }}" placeholder="e.g. Glass Jar, Craft Pouch" />
            </div>
          </div>

          <div class="flex justify-end pt-1">
            <button type="button" id="generateMatrixBtn" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
              <span>⚡ Generate Combination Matrix</span>
            </button>
          </div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
        @endphp

        {{-- Combination Matrix Table --}}
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border border-stone-200 rounded-xl overflow-hidden">
            <thead class="bg-stone-100 text-stone-700 font-extrabold border-b border-stone-200">
              <tr>
                <th class="py-3 px-3.5">Option / Weight</th>
                <th class="py-3 px-3.5">SKU Code</th>
                <th class="py-3 px-3.5 w-36 text-center bg-stone-200/60">Regular Price (৳)</th>
                <th class="py-3 px-3.5 w-36 text-center bg-emerald-100/60 text-emerald-900">Sale Price (৳)</th>
                <th class="py-3 px-3.5 w-28 text-center">Stock Qty</th>
                <th class="py-3 px-3.5 w-16 text-center">Active</th>
                <th class="py-3 px-3.5 w-10"></th>
              </tr>
            </thead>
            <tbody id="skuMatrixBody" class="divide-y divide-stone-100 bg-white">
              @forelse($skus as $index => $sku)
                @php
                  $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
                  $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
                @endphp
                <tr class="sku-row hover:bg-stone-50/80">
                  <td class="py-3 px-3.5">
                    <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                    <div class="flex items-center gap-1 flex-wrap">
                      @foreach($sku->getAttributesData() as $k => $v)
                        <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                        <span class="bg-emerald-50 text-emerald-800 px-2.5 py-0.5 rounded-md text-[11px] font-extrabold border border-emerald-200/80">{{ $k }}: {{ $v }}</span>
                      @endforeach
                    </div>
                  </td>
                  <td class="py-3 px-3.5">
                    <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="SKU" class="w-full px-2.5 py-1 text-xs font-mono rounded-lg border border-stone-200" />
                  </td>
                  <td class="py-3 px-3.5">
                    <input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $isCustomReg ? $sku->regular_price : '' }}" class="w-full px-2.5 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-regular-price-input" placeholder="Auto Base" />
                  </td>
                  <td class="py-3 px-3.5">
                    <input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $isCustomSale ? $sku->sale_price : '' }}" class="w-full px-2.5 py-1 text-xs text-center font-black text-emerald-700 bg-emerald-50/40 rounded-lg border border-emerald-200 sku-sale-price-input" placeholder="Auto Base" />
                  </td>
                  <td class="py-3 px-3.5">
                    <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="w-full px-2.5 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-stock-input" required />
                  </td>
                  <td class="py-3 px-3.5 text-center">
                    <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
                  </td>
                  <td class="py-3 px-3.5 text-center">
                    <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-rose-400 hover:text-rose-600 font-bold text-base cursor-pointer">×</button>
                  </td>
                </tr>
              @empty
                <tr id="emptyMatrixRow">
                  <td colspan="7" class="py-6 text-center text-stone-400 italic bg-stone-50/50">
                    🌿 No weight or size options generated yet.<br/>
                    Enter weights above (e.g. <strong class="text-stone-800">250g, 500g, 1kg</strong>) and click <strong class="text-emerald-700">"⚡ Generate Combination Matrix"</strong>.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
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
            ['label' => 'Purity Standard', 'value' => '100% Organic & Chemical-Free'],
            ['label' => 'Source / Origin', 'value' => 'Direct Organic Farm Collect'],
            ['label' => 'Shelf Life', 'value' => '12 Months'],
          ];
        }
      @endphp
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <div>
            <h2 class="text-base font-extrabold text-stone-900">📋 Organic Specifications &amp; Highlights</h2>
            <p class="text-xs text-stone-500 mt-0.5">Key selling features shown as bullet points and specification table on storefront.</p>
          </div>
          <button type="button" id="addSpecRow" class="px-3.5 py-1.5 text-xs font-bold rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-800 shadow-2xs">+ Add Feature</button>
        </div>

        <div id="specRows" class="space-y-3">
          @foreach($specRows as $row)
            <div class="spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-3 items-center p-3 sm:p-2 bg-stone-50 rounded-xl border border-stone-200/80">
              <div>
                <label class="text-[10px] font-bold text-stone-500 block uppercase">Feature Name</label>
                <input name="spec_labels[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" value="{{ $row['label'] ?? '' }}" placeholder="e.g. Origin" />
              </div>
              <div>
                <label class="text-[10px] font-bold text-stone-500 block uppercase">Value / Detail</label>
                <input name="spec_values[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" value="{{ $row['value'] ?? '' }}" placeholder="e.g. Sundarbans Wild Forest" />
              </div>
              <button type="button" class="remove-spec-row sm:mt-4 h-8 w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors" title="Remove Feature">×</button>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Card 5: Media Gallery & Drag-and-Drop Image Uploader --}}
      <div class="bg-white p-5 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <div>
            <h2 class="text-base font-extrabold text-stone-900">🖼️ Product Media Gallery</h2>
            <p class="text-xs text-stone-500 mt-0.5">Upload high-resolution photos on white background (Ghorer Bazar visual style).</p>
          </div>
          <span class="text-xs font-bold text-stone-400">Max 5MB per image</span>
        </div>

        {{-- Existing Uploaded Product Photos --}}
        @if($editing && $product->images->isNotEmpty())
          <div>
            <label class="text-xs font-bold text-stone-800 block mb-2">Existing Uploaded Images</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3" id="productImagesGrid">
              @foreach($product->images as $img)
                <div class="relative group bg-stone-50 border border-stone-200 rounded-xl p-1.5 flex flex-col items-center gap-1 shadow-2xs" id="imgcard-{{ $img->id }}">
                  <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden flex items-center justify-center border border-stone-100">
                    <img src="{{ $img->url() }}" class="max-h-full max-w-full object-contain p-1" alt="{{ $img->alt }}" />
                    @if($img->is_primary)
                      <span class="absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs">Main</span>
                    @endif
                    <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute top-1 right-1 bg-rose-600 text-white h-5 w-5 rounded-full text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-md cursor-pointer" title="Delete Image">&times;</button>
                  </div>
                  <input type="text" name="image_colors[{{ $img->id }}]" value="{{ old("image_colors.{$img->id}", $img->color) }}" placeholder="Tag (e.g. 500g)" class="w-full text-[10px] font-medium px-1.5 py-1 bg-white border border-stone-200 rounded-md text-center focus:outline-none focus:border-brand-500" />
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
        <div id="newImagesPreview" class="grid grid-cols-3 sm:grid-cols-6 gap-3"></div>
      </div>
    </div>

    {{-- Right Sidebar Column (Organization, Badges & SEO) --}}
    <div class="space-y-6">

      {{-- Card: Storefront Badges & Visibility --}}
      <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <h3 class="text-sm font-extrabold text-stone-900 border-b border-stone-100 pb-2.5">Storefront Visibility &amp; Badges</h3>
        @php
          $toggles = [
            'is_published'   => ['Published on Storefront', 'Visible to shoppers for direct purchase'],
            'is_featured'    => ['⭐ Featured Product', 'Highlighted on home page flagship section'],
            'is_flash_sale'  => ['⚡ Flash Sale Deal', 'Promoted inside limited-time deal section'],
            'is_best_seller' => ['🏆 Best Seller', 'Show badge on catalog card'],
            'is_new_arrival' => ['🆕 New Arrival', 'Show new badge'],
          ];
        @endphp
        <div class="space-y-2.5">
          @foreach($toggles as $field => [$label, $hint])
            <label class="flex items-start justify-between gap-3 text-xs font-bold text-stone-800 cursor-pointer p-2.5 rounded-xl hover:bg-stone-50 transition-colors border border-transparent hover:border-stone-200">
              <div>
                <span class="block">{{ $label }}</span>
                <span class="text-[10px] font-normal text-stone-400 block mt-0.5">{{ $hint }}</span>
              </div>
              <input type="checkbox" name="{{ $field }}" value="1" class="accent-brand-600 h-4 w-4 rounded cursor-pointer mt-0.5" @checked(old($field, $product->$field)) />
            </label>
          @endforeach
        </div>
      </div>

      {{-- Card: Search Engine Optimization (SEO) --}}
      <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <h3 class="text-sm font-extrabold text-stone-900 border-b border-stone-100 pb-2.5">🔍 Search Engine Optimization (SEO)</h3>

        {{-- Live Google Search Preview --}}
        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200 text-xs space-y-1">
          <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Google Search Preview</span>
          <p id="seoPreviewTitle" class="text-sm font-bold text-blue-700 truncate hover:underline cursor-pointer">
            {{ old('meta_title', $product->meta_title) ?: ($editing ? $product->name . ' — ShodeshiFood' : 'Product Name — ShodeshiFood') }}
          </p>
          <p class="text-[11px] text-emerald-700 truncate font-mono">
            {{ url('/products') }}/<span id="seoPreviewSlug">{{ old('slug', $product->slug) ?: 'product-slug' }}</span>
          </p>
          <p id="seoPreviewDesc" class="text-[11px] text-stone-600 line-clamp-2">
            {{ old('meta_description', $product->meta_description) ?: 'Buy 100% pure chemical-free organic food online in Bangladesh at best prices.' }}
          </p>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Title</label>
            <input type="text" id="metaTitleInput" name="meta_title" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200" value="{{ old('meta_title', $product->meta_title) }}" placeholder="e.g. Buy Pure Sundarban Honey Online — ShodeshiFood" />
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Description</label>
            <textarea id="metaDescInput" name="meta_description" rows="2" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200" placeholder="Short description for Google search results...">{{ old('meta_description', $product->meta_description) }}</textarea>
          </div>

          <div>
            <label class="text-xs font-bold text-stone-800 block mb-1">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="w-full px-3 py-2 text-xs font-medium text-stone-800 rounded-xl border border-stone-200" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="e.g. sundarban honey, organic honey bd" />
          </div>
        </div>
      </div>

    </div>
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
        seoTitle.innerText = nameInput.value ? `${nameInput.value} — ShodeshiFood` : 'Product Name — ShodeshiFood';
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
      seoTitle.innerText = metaTitle.value || (nameInput.value ? `${nameInput.value} — ShodeshiFood` : 'Product Name — ShodeshiFood');
    });
  }

  if (metaDesc && seoDesc) {
    metaDesc.addEventListener('input', () => {
      seoDesc.innerText = metaDesc.value || 'Buy 100% pure chemical-free organic food online in Bangladesh at best prices.';
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
    row.className = 'spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-3 items-center p-3 sm:p-2 bg-stone-50 rounded-xl border border-stone-200/80';
    row.innerHTML = `
      <div>
        <label class="text-[10px] font-bold text-stone-500 block uppercase">Feature Name</label>
        <input name="spec_labels[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. Shelf Life" />
      </div>
      <div>
        <label class="text-[10px] font-bold text-stone-500 block uppercase">Value / Detail</label>
        <input name="spec_values[]" class="w-full px-3 py-1.5 text-xs font-bold text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. 12 Months" />
      </div>
      <button type="button" class="remove-spec-row sm:mt-4 h-8 w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors" title="Remove Feature">×</button>
    `;
    list.appendChild(row);
    bindRemove(row.querySelector('.remove-spec-row'));
  });
})();

// Preset Pills & Variant Matrix Script
(function () {
  document.querySelectorAll('.preset-pill-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = btn.dataset.target;
      const val = btn.dataset.value;
      const input = document.getElementById(targetId);
      if (!input) return;
      const current = input.value.split(',').map(s => s.trim()).filter(Boolean);
      if (!current.includes(val)) {
        current.push(val);
        input.value = current.join(', ');
      }
    });
  });

  const genBtn = document.getElementById('generateMatrixBtn');
  const body = document.getElementById('skuMatrixBody');
  const sizesInput = document.getElementById('sizesInput');
  const colorsInput = document.getElementById('colorsInput');

  if (!genBtn) return;

  genBtn.addEventListener('click', () => {
    const sizes = (sizesInput ? sizesInput.value : '').split(',').map(s => s.trim()).filter(Boolean);
    const colors = (colorsInput ? colorsInput.value : '').split(',').map(c => c.trim()).filter(Boolean);

    if (sizes.length === 0 && colors.length === 0) {
      alert('Please enter at least one Weight or Size option first.');
      return;
    }

    const combinations = [];

    if (colors.length > 0 && sizes.length > 0) {
      colors.forEach(c => {
        sizes.forEach(s => {
          combinations.push({ Packaging: c, Weight: s });
        });
      });
    } else if (colors.length > 0) {
      colors.forEach(c => combinations.push({ Packaging: c }));
    } else if (sizes.length > 0) {
      sizes.forEach(s => combinations.push({ Weight: s }));
    }

    if (body) body.innerHTML = '';

    combinations.forEach((combo, idx) => {
      let attrBadgesHtml = '';
      let attrInputsHtml = '';
      Object.keys(combo).forEach(k => {
        const v = combo[k];
        attrBadgesHtml += `<span class="bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded text-[11px] font-extrabold border border-emerald-200">${k}: ${v}</span> `;
        attrInputsHtml += `<input type="hidden" name="sku_matrix[${idx}][attributes][${k}]" value="${v}" />`;
      });

      let sizeVal = (combo.Weight || combo.Size || '').replace(/[^A-Za-z0-9]/g, '');
      let colorVal = (combo.Packaging || combo.Color || '').split(' ')[0].replace(/[^A-Za-z0-9]/g, '');
      let autoSkuHint = (colorVal || sizeVal) ? (colorVal + sizeVal) : 'VAR';

      if (body) {
        const row = document.createElement('tr');
        row.className = 'sku-row hover:bg-stone-50/80';
        row.innerHTML = `
          <td class="py-3 px-3.5">
            ${attrInputsHtml}
            <div class="flex items-center gap-1 flex-wrap">${attrBadgesHtml}</div>
          </td>
          <td class="py-3 px-3.5">
            <input name="sku_matrix[${idx}][sku]" value="" placeholder="Auto: [SKU]-${autoSkuHint}" class="w-full px-2.5 py-1 text-xs font-mono rounded-lg border border-stone-200" />
          </td>
          <td class="py-3 px-3.5">
            <input name="sku_matrix[${idx}][regular_price]" type="number" step="0.01" value="" class="w-full px-2.5 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-regular-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-3 px-3.5">
            <input name="sku_matrix[${idx}][sale_price]" type="number" step="0.01" value="" class="w-full px-2.5 py-1 text-xs text-center font-black text-emerald-700 bg-emerald-50/40 rounded-lg border border-emerald-200 sku-sale-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-3 px-3.5">
            <input name="sku_matrix[${idx}][stock]" type="number" min="0" value="10" class="w-full px-2.5 py-1 text-xs text-center font-bold rounded-lg border border-stone-200 sku-stock-input" required />
          </td>
          <td class="py-3 px-3.5 text-center">
            <input type="checkbox" name="sku_matrix[${idx}][is_active]" value="1" checked class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
          </td>
          <td class="py-3 px-3.5 text-center">
            <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-rose-400 hover:text-rose-600 font-bold text-base cursor-pointer">×</button>
          </td>
        `;
        body.appendChild(row);
      }
    });

    updateMatrixCalculations();
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

// Live Drag-and-Drop Image Upload Preview
(function() {
  const fileInput = document.getElementById('imageFileInput');
  const previewBox = document.getElementById('newImagesPreview');
  if (!fileInput || !previewBox) return;

  fileInput.addEventListener('change', function(e) {
    previewBox.innerHTML = '';
    const files = Array.from(e.target.files || []);
    files.forEach((file, idx) => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = function(evt) {
        const div = document.createElement('div');
        div.className = 'relative flex flex-col items-center gap-1.5 p-1 bg-stone-50 border border-stone-200 rounded-xl shadow-2xs';
        div.innerHTML = `
          <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border border-stone-100 flex items-center justify-center">
            <img src="${evt.target.result}" class="max-h-full max-w-full object-contain p-1" />
            ${idx === 0 ? '<span class="absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs">Main</span>' : ''}
          </div>
          <span class="text-[10px] text-stone-500 font-mono w-full truncate text-center">${file.name}</span>
        `;
        previewBox.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  });
})();
</script>
@endpush
@endsection
