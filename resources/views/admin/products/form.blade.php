@extends('layouts.admin')
@php $editing = $product->exists; @endphp
@section('title', $editing ? 'Edit Product' : 'New Product')

@section('content')
@php
  $sizeValues = $editing ? $product->variants->where('type', 'Size')->pluck('value')->implode(', ') : '';
  $colorValues = $editing ? $product->variants->where('type', 'Color')->pluck('value')->implode(', ') : '';
@endphp
<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
  @csrf
  @if($editing) @method('PUT') @endif

  <!-- Top Action Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
    <div>
      <a href="{{ route('admin.products.index') }}" class="text-xs sm:text-sm text-gray-500 hover:text-primary">&larr; Back to products</a>
      <h2 class="text-lg sm:text-xl font-bold mt-1 text-slate-900">{{ $editing ? 'Edit Product' : 'New Product' }}</h2>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
      <a href="{{ route('admin.products.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl border border-gray-300 bg-white">Cancel</a>
      <button class="btn-primary py-2 px-5 text-xs sm:text-sm font-bold">Save Product</button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
      <!-- Basic Information -->
      <div class="card p-4 sm:p-5 space-y-4">
        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Basic information</h3>
        <div><label class="lbl">Name</label><input name="name" class="inp" value="{{ old('name', $product->name) }}" required /></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div><label class="lbl">Slug (blank = auto)</label><input name="slug" class="inp" value="{{ old('slug', $product->slug) }}" /></div>
          <div><label class="lbl">SKU</label><input name="sku" class="inp" value="{{ old('sku', $product->sku) }}" /></div>
        </div>
        <div>
          <div class="flex items-center justify-between">
            <label class="lbl">Brand</label>
            <a href="{{ route('admin.brands.create') }}" target="_blank" class="text-xs text-indigo-600 hover:underline">+ Manage Brands</a>
          </div>
          <select name="brand_id" class="inp">
            <option value="">-- Select Brand --</option>
            @foreach($brands as $b)
              <option value="{{ $b->id }}" @selected((int) old('brand_id', $product->brand_id) === (int) $b->id)>
                {{ $b->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div><label class="lbl">Short description</label><textarea name="short_description" class="inp" rows="2">{{ old('short_description', $product->short_description) }}</textarea></div>
        <div><label class="lbl">Full description</label><textarea name="description" class="inp" rows="5">{{ old('description', $product->description) }}</textarea></div>
      </div>

      <!-- Specifications -->
      @php
        $oldLabels = old('spec_labels');
        $oldValues = old('spec_values');
        if (is_array($oldLabels)) {
          $specRows = [];
          foreach ($oldLabels as $i => $label) {
            $specRows[] = ['label' => $label, 'value' => $oldValues[$i] ?? ''];
          }
        } else {
          $specRows = $product->specificationRows();
        }
        if (empty($specRows)) {
          $specRows = [['label' => '', 'value' => '']];
        }
      @endphp
      <div class="card p-4 sm:p-5 space-y-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="font-bold text-slate-900 text-sm sm:text-base">Specifications</h3>
            <p class="text-xs text-stone-400 mt-0.5">Shown as bullets on product cards and in the product page table.</p>
          </div>
          <button type="button" id="addSpecRow" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-300 bg-white hover:bg-gray-50">+ Add row</button>
        </div>
        <div id="specRows" class="space-y-3">
          @foreach($specRows as $row)
            <div class="spec-row grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-start p-2.5 sm:p-0 bg-slate-50 sm:bg-transparent rounded-xl border sm:border-0 border-slate-200">
              <div>
                <label class="lbl">Label</label>
                <input name="spec_labels[]" class="inp" value="{{ $row['label'] ?? '' }}" placeholder="e.g. Model" />
              </div>
              <div>
                <label class="lbl">Value</label>
                <input name="spec_values[]" class="inp" value="{{ $row['value'] ?? '' }}" placeholder="e.g. A0023" />
              </div>
              <button type="button" class="remove-spec-row sm:mt-6 h-9 w-full sm:w-9 rounded-lg border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 flex items-center justify-center font-bold text-base" title="Remove" aria-label="Remove row">×</button>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Pricing -->
      <div class="card p-4 sm:p-5 space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <h3 class="font-bold text-slate-900 text-sm sm:text-base">Base Starting Pricing &amp; General Stock</h3>
          <span id="autoStockNotice" class="hidden px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300"></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div><label class="lbl">Base Regular price (৳)</label><input name="regular_price" type="number" step="0.01" class="inp font-bold" value="{{ old('regular_price', $product->regular_price) }}" required /></div>
          <div><label class="lbl">Base Sale price (৳)</label><input name="sale_price" type="number" step="0.01" class="inp font-bold text-emerald-700" value="{{ old('sale_price', $product->sale_price) }}" placeholder="optional" /></div>
          <div><label class="lbl">Total stock quantity</label><input name="stock_quantity" type="number" class="inp font-bold text-slate-900" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required /></div>
          <div><label class="lbl">Unit / default size</label><input name="unit" class="inp" value="{{ old('unit', $product->unit) }}" placeholder="e.g. Pack, Jar, 500g" /></div>
          <div><label class="lbl">Rating (from reviews)</label><input type="text" class="inp bg-gray-50 text-slate-600 font-semibold" value="{{ number_format((float) ($product->rating ?? 0), 2) }} ★ · {{ (int) ($product->reviews_count ?? 0) }} reviews" readonly /></div>
        </div>
      </div>

      <!-- Variants & Combination Inventory -->
      <div class="card p-4 sm:p-5 space-y-4 sm:space-y-5 border border-emerald-100/80 shadow-xs">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
              <span>🌿 Product Options, Weights &amp; Variant Matrix</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">Set product weights or pack options (e.g. <code class="bg-slate-100 px-1 py-0.5 rounded text-emerald-800">250g, 500g, 1kg</code>), then generate stock &amp; price per option.</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Variants</span>
        </div>

        <div class="space-y-3.5 bg-emerald-50/40 p-3.5 sm:p-4 rounded-xl border border-emerald-100">
          {{-- Presets Quick Bar --}}
          @if(isset($attributeTypes) && $attributeTypes->isNotEmpty())
            <div class="space-y-2 border-b border-emerald-100/80 pb-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-emerald-900 uppercase tracking-wider block">⚡ Quick Presets</label>
                <a href="{{ route('admin.variations.index') }}" target="_blank" class="text-[11px] font-bold text-emerald-700 hover:underline">Variations →</a>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($attributeTypes as $attType)
                  <div class="bg-white p-2 rounded-xl border border-emerald-200/60 text-xs shadow-2xs">
                    <span class="font-extrabold text-slate-800 block mb-1 text-[11px]">{{ $attType->name }}</span>
                    <div class="flex flex-wrap gap-1">
                      @foreach($attType->values as $presetVal)
                        <button type="button" class="preset-pill-btn px-2 py-0.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold border border-emerald-200 transition cursor-pointer text-[10px]" data-target="{{ in_array($attType->name, ['Weight', 'Volume', 'Size', 'Flavor']) ? 'sizesInput' : 'colorsInput' }}" data-value="{{ $presetVal->value }}">
                          + {{ $presetVal->value }}
                        </button>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="lbl font-bold text-slate-800">Primary Options (Weight / Size) <span class="text-slate-400 font-normal text-[11px]">(Comma-separated)</span></label>
              <input id="sizesInput" name="sizes" class="inp bg-white text-xs" value="{{ old('sizes', $sizeValues) }}" placeholder="e.g. 250g, 500g, 1kg" />
            </div>
            <div>
              <label class="lbl font-bold text-slate-800">Secondary Options (Color / Packaging) <span class="text-slate-400 font-normal text-[11px]">(Optional)</span></label>
              <input id="colorsInput" name="colors" class="inp bg-white text-xs" value="{{ old('colors', $colorValues) }}" placeholder="e.g. Glass Jar, Bottle" />
            </div>
          </div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
          $basePriceVal = (float) ($product->sale_price ?? $product->regular_price ?? 0);
        @endphp

        <div class="pt-3 border-t border-slate-100 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div>
              <h4 class="font-bold text-sm text-slate-800">Stock &amp; Price Adjustment Per Option</h4>
              <p class="text-xs text-slate-400">Set custom price delta and stock quantity for each weight option.</p>
            </div>
            <button type="button" id="generateMatrixBtn" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer px-3.5 py-2 rounded-xl shadow-xs flex items-center justify-center gap-1.5 shrink-0">
              <span>⚡ Generate Combination Matrix</span>
            </button>
          </div>

          {{-- Desktop Matrix Table --}}
          <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
              <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Option / Weight</th>
                  <th class="py-2.5 px-3">SKU Code</th>
                  <th class="py-2.5 px-3 w-36 text-center bg-stone-100 text-slate-800">Regular Price (৳)</th>
                  <th class="py-2.5 px-3 w-36 text-center bg-emerald-100/60 text-emerald-900">Sale Price (৳)</th>
                  <th class="py-2.5 px-3 w-28 text-center">Stock Qty</th>
                  <th class="py-2.5 px-3 w-16 text-center">Active</th>
                  <th class="py-2.5 px-3 w-10"></th>
                </tr>
              </thead>
              <tbody id="skuMatrixBody" class="divide-y divide-slate-100">
                @forelse($skus as $index => $sku)
                  @php
                    $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
                    $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
                  @endphp
                  <tr class="sku-row hover:bg-slate-50/80">
                    <td class="py-2.5 px-3">
                      <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                      <div class="flex items-center gap-1 flex-wrap">
                        @foreach($sku->getAttributesData() as $k => $v)
                          <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                          <span class="bg-emerald-50 text-emerald-800 px-2.5 py-0.5 rounded-md text-[11px] font-bold border border-emerald-200/80">{{ $k }}: {{ $v }}</span>
                        @endforeach
                      </div>
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="e.g. HONEY-500G" class="inp text-xs py-1 px-2 font-mono" />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $isCustomReg ? $sku->regular_price : '' }}" class="inp text-xs py-1 px-2 text-center font-bold sku-regular-price-input" placeholder="Auto Base" />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $isCustomSale ? $sku->sale_price : '' }}" class="inp text-xs py-1 px-2 text-center font-extrabold text-emerald-700 bg-emerald-50/20 sku-sale-price-input" placeholder="Auto Base" />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="inp text-xs py-1 px-2 text-center font-bold sku-stock-input" required />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-red-400 hover:text-red-600 font-bold text-base cursor-pointer">×</button>
                    </td>
                  </tr>
                @empty
                  <tr id="emptyMatrixRow">
                    <td colspan="7" class="py-5 text-center text-slate-500 italic bg-stone-50/50">
                      🌿 No weight or size options generated yet.<br/>
                      Enter weights above (e.g. <strong class="text-slate-800">250g, 500g, 1kg</strong>) and click <strong class="text-emerald-700">"⚡ Generate Combination Matrix"</strong>.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Mobile Matrix Card List --}}
          <div id="skuMatrixMobileContainer" class="block sm:hidden space-y-3">
            @forelse($skus as $index => $sku)
              @php
                $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
                $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
              @endphp
              <div class="sku-row p-3 rounded-xl border border-stone-200 bg-white space-y-2.5 shadow-2xs">
                <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                <div class="flex items-center justify-between gap-2 border-b border-stone-100 pb-2">
                  <div class="flex items-center gap-1 flex-wrap">
                    @foreach($sku->getAttributesData() as $k => $v)
                      <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                      <span class="bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded text-[11px] font-bold border border-emerald-200">{{ $k }}: {{ $v }}</span>
                    @endforeach
                  </div>
                  <button type="button" onclick="this.closest('.sku-row').remove(); updateMatrixCalculations();" class="text-rose-500 hover:text-rose-700 font-bold text-xs px-2 py-0.5 bg-rose-50 rounded border border-rose-200">Remove ×</button>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <label class="text-[10px] font-bold text-stone-500 block">SKU Code</label>
                    <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="SKU" class="inp text-xs py-1 px-2 font-mono" />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-stone-500 block">Stock Qty</label>
                    <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="inp text-xs py-1 px-2 text-center font-bold sku-stock-input" required />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-stone-500 block">Reg Price (৳)</label>
                    <input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $isCustomReg ? $sku->regular_price : '' }}" class="inp text-xs py-1 px-2 text-center font-bold sku-regular-price-input" placeholder="Auto Base" />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-stone-500 block">Sale Price (৳)</label>
                    <input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $isCustomSale ? $sku->sale_price : '' }}" class="inp text-xs py-1 px-2 text-center font-bold text-emerald-700 sku-sale-price-input" placeholder="Auto Base" />
                  </div>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-stone-100">
                  <span class="text-xs font-bold text-stone-600">Active Option</span>
                  <label class="flex items-center gap-1.5 text-xs font-bold text-stone-800 cursor-pointer">
                    <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
                    <span>Enabled</span>
                  </label>
                </div>
              </div>
            @empty
              <div id="mobileEmptyMatrix" class="p-4 text-center text-xs text-stone-400 italic bg-stone-50 rounded-xl border border-stone-200">
                🌿 No weight or size options generated yet.<br/>
                Enter weights above and click "⚡ Generate Combination Matrix".
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Images -->
      <div class="card p-4 sm:p-5 space-y-4">
        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Images</h3>
        @if($editing && $product->images->isNotEmpty())
          <div class="grid grid-cols-3 sm:flex gap-3 sm:gap-4 flex-wrap pt-1" id="productImagesGrid">
            @foreach($product->images as $img)
              <div class="relative group flex flex-col items-center gap-1.5" id="imgcard-{{ $img->id }}">
                <div class="relative">
                  <img src="{{ $img->url() }}" class="h-20 w-20 sm:h-24 sm:w-24 object-cover rounded-xl bg-gray-100 border border-gray-200 {{ $img->is_primary ? 'ring-2 ring-primary' : '' }}" alt="">
                  @if($img->is_primary)<span class="absolute top-1 left-1 bg-primary text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">Main</span>@endif
                  <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute -top-2 -right-2 bg-rose-600 hover:bg-rose-700 text-white h-6 w-6 rounded-full text-xs font-bold shadow-md flex items-center justify-center cursor-pointer transition z-10" title="Delete Image">&times;</button>
                </div>
                <input type="text" name="image_colors[{{ $img->id }}]" value="{{ old("image_colors.{$img->id}", $img->color) }}" placeholder="Variation (1kg)" class="w-20 sm:w-24 text-[10px] sm:text-[11px] font-medium px-1.5 py-1 bg-gray-50 border border-gray-300 rounded-lg text-center focus:outline-none focus:border-brand-500 focus:bg-white" title="Tag image to variation option" />
              </div>
            @endforeach
          </div>
        @endif
        <div>
          <label class="lbl">Add images (first upload becomes main image if none set)</label>
          <input id="imageFileInput" name="images[]" type="file" accept="image/*" multiple class="text-xs border border-slate-200 rounded-xl p-2 w-full bg-white cursor-pointer" />
          <div id="newImagesPreview" class="flex gap-2.5 flex-wrap mt-3"></div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-4 sm:space-y-6">
      <div class="card p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Organization</h3>
        <div>
          <label class="lbl font-semibold text-slate-800">Category <span class="text-red-500">*</span></label>
          <select name="category_id" class="inp bg-white text-xs sm:text-sm" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="card p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Visibility &amp; Badges</h3>
        @php
          $toggles = [
            'is_published'   => 'Published (Visible on storefront)',
            'is_featured'    => 'Featured Item',
            'is_new_arrival' => 'New Arrival',
            'is_best_seller' => 'Best Seller',
            'is_flash_sale'  => '⚡ Flash Sale Deal',
          ];
        @endphp
        @foreach($toggles as $field => $label)
          <label class="flex items-center justify-between text-xs sm:text-sm font-medium text-slate-700 cursor-pointer py-1.5 border-b border-slate-50 last:border-0 hover:text-emerald-700">
            <span>{{ $label }}</span>
            <input type="checkbox" name="{{ $field }}" value="1" class="h-4 w-4 sm:h-5 sm:w-5 accent-emerald-600 rounded cursor-pointer" @checked(old($field, $product->$field)) />
          </label>
        @endforeach
      </div>

      <div class="card p-4 sm:p-5 space-y-3">
        <h3 class="font-bold text-slate-900 text-sm sm:text-base">SEO</h3>
        <div><label class="lbl">Meta title</label><input name="meta_title" class="inp" value="{{ old('meta_title', $product->meta_title) }}" /></div>
        <div><label class="lbl">Meta description</label><textarea name="meta_description" class="inp" rows="2">{{ old('meta_description', $product->meta_description) }}</textarea></div>
        <div><label class="lbl">Meta keywords</label><textarea name="meta_keywords" class="inp" rows="2" placeholder="organic honey, mustard oil">{{ old('meta_keywords', $product->meta_keywords) }}</textarea></div>
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
    row.className = 'spec-row grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-start p-2.5 sm:p-0 bg-slate-50 sm:bg-transparent rounded-xl border sm:border-0 border-slate-200';
    row.innerHTML = `
      <div>
        <label class="lbl">Label</label>
        <input name="spec_labels[]" class="inp" value="" placeholder="e.g. Model" />
      </div>
      <div>
        <label class="lbl">Value</label>
        <input name="spec_values[]" class="inp" value="" placeholder="e.g. A0023" />
      </div>
      <button type="button" class="remove-spec-row sm:mt-6 h-9 w-full sm:w-9 rounded-lg border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 flex items-center justify-center font-bold text-base" title="Remove" aria-label="Remove row">×</button>
    `;
    list.appendChild(row);
    bindRemove(row.querySelector('.remove-spec-row'));
  });
})();

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
  const mobileContainer = document.getElementById('skuMatrixMobileContainer');
  const sizesInput = document.getElementById('sizesInput');
  const colorsInput = document.getElementById('colorsInput');

  if (!genBtn) return;

  genBtn.addEventListener('click', () => {
    const sizes = (sizesInput ? sizesInput.value : '').split(',').map(s => s.trim()).filter(Boolean);
    const colors = (colorsInput ? colorsInput.value : '').split(',').map(c => c.trim()).filter(Boolean);

    if (sizes.length === 0 && colors.length === 0) {
      alert('Please enter at least one Size or Option first.');
      return;
    }

    const combinations = [];

    if (colors.length > 0 && sizes.length > 0) {
      colors.forEach(c => {
        sizes.forEach(s => {
          combinations.push({ Color: c, Size: s });
        });
      });
    } else if (colors.length > 0) {
      colors.forEach(c => combinations.push({ Color: c }));
    } else if (sizes.length > 0) {
      sizes.forEach(s => combinations.push({ Size: s }));
    }

    if (body) body.innerHTML = '';
    if (mobileContainer) mobileContainer.innerHTML = '';

    combinations.forEach((combo, idx) => {
      let attrBadgesHtml = '';
      let attrInputsHtml = '';
      Object.keys(combo).forEach(k => {
        const v = combo[k];
        attrBadgesHtml += `<span class="bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded text-[11px] font-bold border border-emerald-200">${k}: ${v}</span> `;
        attrInputsHtml += `<input type="hidden" name="sku_matrix[${idx}][attributes][${k}]" value="${v}" />`;
      });

      let sizeVal = (combo.Size || '').replace(/[^A-Za-z0-9]/g, '');
      let colorVal = (combo.Color || '').split(' ')[0].replace(/[^A-Za-z0-9]/g, '');
      let autoSkuHint = (colorVal || sizeVal) ? (colorVal + sizeVal) : 'VAR';

      if (body) {
        const row = document.createElement('tr');
        row.className = 'sku-row';
        row.innerHTML = `
          <td class="py-2.5 px-3">
            ${attrInputsHtml}
            <div class="flex items-center gap-1 flex-wrap">${attrBadgesHtml}</div>
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][sku]" value="" placeholder="Auto: [SKU]-${autoSkuHint}" class="inp text-xs py-1 px-2 font-mono" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][regular_price]" type="number" step="0.01" value="" class="inp text-xs py-1 px-2 text-center font-bold sku-regular-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][sale_price]" type="number" step="0.01" value="" class="inp text-xs py-1 px-2 text-center font-extrabold text-emerald-700 bg-emerald-50/20 sku-sale-price-input" placeholder="Auto Base" />
          </td>
          <td class="py-2.5 px-3">
            <input name="sku_matrix[${idx}][stock]" type="number" min="0" value="10" class="inp text-xs py-1 px-2 text-center font-bold sku-stock-input" required />
          </td>
          <td class="py-2.5 px-3 text-center">
            <input type="checkbox" name="sku_matrix[${idx}][is_active]" value="1" checked class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
          </td>
          <td class="py-2.5 px-3 text-center">
            <button type="button" onclick="this.closest('tr').remove(); updateMatrixCalculations();" class="text-red-400 hover:text-red-600 font-bold text-base cursor-pointer">×</button>
          </td>
        `;
        body.appendChild(row);
      }

      if (mobileContainer) {
        const mcard = document.createElement('div');
        mcard.className = 'sku-row p-3 rounded-xl border border-stone-200 bg-white space-y-2.5 shadow-2xs';
        mcard.innerHTML = `
          <div class="flex items-center justify-between gap-2 border-b border-stone-100 pb-2">
            <div class="flex items-center gap-1 flex-wrap">
              ${attrInputsHtml}
              ${attrBadgesHtml}
            </div>
            <button type="button" onclick="this.closest('.sku-row').remove(); updateMatrixCalculations();" class="text-rose-500 hover:text-rose-700 font-bold text-xs px-2 py-0.5 bg-rose-50 rounded border border-rose-200">Remove ×</button>
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <label class="text-[10px] font-bold text-stone-500 block">SKU Code</label>
              <input name="sku_matrix[${idx}][sku]" value="" placeholder="Auto SKU" class="inp text-xs py-1 px-2 font-mono" />
            </div>
            <div>
              <label class="text-[10px] font-bold text-stone-500 block">Stock Qty</label>
              <input name="sku_matrix[${idx}][stock]" type="number" min="0" value="10" class="inp text-xs py-1 px-2 text-center font-bold sku-stock-input" required />
            </div>
            <div>
              <label class="text-[10px] font-bold text-stone-500 block">Reg Price (৳)</label>
              <input name="sku_matrix[${idx}][regular_price]" type="number" step="0.01" value="" class="inp text-xs py-1 px-2 text-center font-bold sku-regular-price-input" placeholder="Auto Base" />
            </div>
            <div>
              <label class="text-[10px] font-bold text-stone-500 block">Sale Price (৳)</label>
              <input name="sku_matrix[${idx}][sale_price]" type="number" step="0.01" value="" class="inp text-xs py-1 px-2 text-center font-bold text-emerald-700 sku-sale-price-input" placeholder="Auto Base" />
            </div>
          </div>
          <div class="flex items-center justify-between pt-1 border-t border-stone-100">
            <span class="text-xs font-bold text-stone-600">Active Option</span>
            <label class="flex items-center gap-1.5 text-xs font-bold text-stone-800 cursor-pointer">
              <input type="checkbox" name="sku_matrix[${idx}][is_active]" value="1" checked class="accent-emerald-600 h-4 w-4 cursor-pointer sku-active-check" />
              <span>Enabled</span>
            </label>
          </div>
        `;
        mobileContainer.appendChild(mcard);
      }
    });

    updateMatrixCalculations();
  });

  function updateMatrixCalculations() {
    const regInput = document.querySelector('input[name="regular_price"]');
    const saleInput = document.querySelector('input[name="sale_price"]');
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

  const regInput = document.querySelector('input[name="regular_price"]');
  const saleInput = document.querySelector('input[name="sale_price"]');
  if (regInput) regInput.addEventListener('input', updateMatrixCalculations);
  if (saleInput) saleInput.addEventListener('input', updateMatrixCalculations);

  document.addEventListener('input', function(e) {
    if (e.target.classList.contains('sku-stock-input') || e.target.classList.contains('sku-regular-price-input') || e.target.classList.contains('sku-sale-price-input') || e.target.classList.contains('sku-active-check')) {
      updateMatrixCalculations();
    }
  });
  document.addEventListener('change', function(e) {
    if (e.target.classList.contains('sku-active-check')) {
      updateMatrixCalculations();
    }
  });

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

// Live Image Upload Preview
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
        div.className = 'relative flex flex-col items-center gap-1';
        div.innerHTML = `
          <div class="relative">
            <img src="${evt.target.result}" class="h-20 w-20 object-cover rounded-xl border border-slate-200 shadow-2xs bg-white" />
            ${idx === 0 ? '<span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">Main</span>' : ''}
          </div>
          <span class="text-[10px] text-slate-500 font-mono max-w-[80px] truncate">${file.name}</span>
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
