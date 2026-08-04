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

  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Back to products</a>
      <h2 class="text-xl font-bold mt-1">{{ $editing ? 'Edit Product' : 'New Product' }}</h2>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-sm rounded-lg border border-gray-300 bg-white">Cancel</a>
      <button class="btn-primary">Save</button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <!-- Basic -->
      <div class="card p-5 space-y-4">
        <h3 class="font-semibold">Basic information</h3>
        <div><label class="lbl">Name</label><input name="name" class="inp" value="{{ old('name', $product->name) }}" required /></div>
        <div class="grid grid-cols-2 gap-4">
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
        <div><label class="lbl">Full description</label><textarea name="description" class="inp" rows="6">{{ old('description', $product->description) }}</textarea></div>
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
      <div class="card p-5 space-y-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="font-semibold">Specifications</h3>
            <p class="text-sm text-gray-400 mt-0.5">Shown as bullets on product cards and in the product page table.</p>
          </div>
          <button type="button" id="addSpecRow" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 bg-white hover:bg-gray-50">+ Add row</button>
        </div>
        <div id="specRows" class="space-y-3">
          @foreach($specRows as $row)
            <div class="spec-row grid grid-cols-[1fr_1fr_auto] gap-2 items-start">
              <div>
                <label class="lbl">Label</label>
                <input name="spec_labels[]" class="inp" value="{{ $row['label'] ?? '' }}" placeholder="e.g. Model" />
              </div>
              <div>
                <label class="lbl">Value</label>
                <input name="spec_values[]" class="inp" value="{{ $row['value'] ?? '' }}" placeholder="e.g. A0023" />
              </div>
              <button type="button" class="remove-spec-row mt-6 h-10 w-10 rounded-lg border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200" title="Remove" aria-label="Remove row">×</button>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Pricing -->
      <div class="card p-5 space-y-4">
        <h3 class="font-semibold">Pricing & stock</h3>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="lbl">Regular price (৳)</label><input name="regular_price" type="number" step="0.01" class="inp" value="{{ old('regular_price', $product->regular_price) }}" required /></div>
          <div><label class="lbl">Sale price (৳)</label><input name="sale_price" type="number" step="0.01" class="inp" value="{{ old('sale_price', $product->sale_price) }}" placeholder="optional" /></div>
          <div><label class="lbl">Stock quantity</label><input name="stock_quantity" type="number" class="inp" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required /></div>
          <div><label class="lbl">Unit / default size</label><input name="unit" class="inp" value="{{ old('unit', $product->unit) }}" placeholder="e.g. M" /></div>
          <div><label class="lbl">Rating (from reviews)</label><input type="text" class="inp bg-gray-50" value="{{ number_format((float) ($product->rating ?? 0), 2) }} ★ · {{ (int) ($product->reviews_count ?? 0) }} reviews" readonly /></div>
        </div>
      </div>

      <!-- Variants & Combination Inventory -->
      <div class="card p-5 space-y-5 border border-emerald-100/80 shadow-xs">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
              <span>🌿 Product Options, Weights &amp; Variant Matrix</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">Set product weights or pack options (e.g. <code class="bg-slate-100 px-1 py-0.5 rounded text-emerald-800">250g, 500g, 1kg</code> or <code class="bg-slate-100 px-1 py-0.5 rounded text-emerald-800">250ml, 500ml, 1L</code>), then generate stock &amp; price per option.</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Weights &amp; Variants</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-emerald-50/40 p-4 rounded-xl border border-emerald-100">
          <div>
            <label class="lbl font-bold text-slate-800">Weights / Sizes / Pack Options <span class="text-slate-400 font-normal">(Comma-separated)</span></label>
            <input id="sizesInput" name="sizes" class="inp bg-white" value="{{ old('sizes', $sizeValues) }}" placeholder="e.g. 250g, 500g, 1kg (or 250ml, 500ml, 1L)" />
          </div>
          <div>
            <label class="lbl font-bold text-slate-800">Packaging Types / Variants <span class="text-slate-400 font-normal">(Optional, Comma-separated)</span></label>
            <input id="colorsInput" name="colors" class="inp bg-white" value="{{ old('colors', $colorValues) }}" placeholder="e.g. Glass Jar, Plastic Bottle" />
          </div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
        @endphp

        <div class="pt-3 border-t border-slate-100 space-y-3">
          <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
              <h4 class="font-bold text-sm text-slate-800">Stock &amp; Price Adjustment Per Option</h4>
              <p class="text-xs text-slate-400">Set custom price delta (e.g. +৳150 for 500g) and stock quantity for each weight.</p>
            </div>
            <button type="button" id="generateMatrixBtn" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer px-4 py-2 rounded-xl shadow-xs flex items-center gap-1.5">
              <span>⚡ Generate Combination Matrix</span>
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
              <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Option / Weight</th>
                  <th class="py-2.5 px-3">SKU Code</th>
                  <th class="py-2.5 px-3 w-28">Stock Qty</th>
                  <th class="py-2.5 px-3 w-32">Price Adj (৳)</th>
                  <th class="py-2.5 px-3 w-16 text-center">Active</th>
                  <th class="py-2.5 px-3 w-10"></th>
                </tr>
              </thead>
              <tbody id="skuMatrixBody" class="divide-y divide-slate-100">
                @forelse($skus as $index => $sku)
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
                      <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="inp text-xs py-1 px-2 text-center font-bold" required />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][price_adjustment]" type="number" step="0.01" value="{{ $sku->price_adjustment }}" class="inp text-xs py-1 px-2 text-center font-bold" placeholder="+0.00" />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-emerald-600 h-4 w-4 cursor-pointer" />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 font-bold text-base cursor-pointer">×</button>
                    </td>
                  </tr>
                @empty
                  <tr id="emptyMatrixRow">
                    <td colspan="6" class="py-5 text-center text-slate-500 italic bg-stone-50/50">
                      🌿 No weight or size options generated yet.<br/>
                      Enter weights above (e.g. <strong class="text-slate-800">250g, 500g, 1kg</strong>) and click <strong class="text-emerald-700">"⚡ Generate Combination Matrix"</strong>.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Images -->
      <div class="card p-5 space-y-4">
        <h3 class="font-semibold">Images</h3>
        @if($editing && $product->images->isNotEmpty())
          <div class="flex gap-4 flex-wrap pt-2" id="productImagesGrid">
            @foreach($product->images as $img)
              <div class="relative group flex flex-col items-center gap-1.5" id="imgcard-{{ $img->id }}">
                <div class="relative">
                  <img src="{{ $img->url() }}" class="h-24 w-24 object-cover rounded-lg bg-gray-100 border border-gray-200 {{ $img->is_primary ? 'ring-2 ring-primary' : '' }}" alt="">
                  @if($img->is_primary)<span class="absolute top-1 left-1 bg-primary text-white text-[10px] font-semibold px-1.5 py-0.5 rounded shadow">Main</span>@endif
                  <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white h-6 w-6 rounded-full text-xs font-bold shadow-md flex items-center justify-center cursor-pointer transition transform hover:scale-110 z-10" title="Delete Image">&times;</button>
                </div>
                <input type="text" name="image_colors[{{ $img->id }}]" value="{{ old("image_colors.{$img->id}", $img->color) }}" placeholder="Color (e.g. Black)" class="w-24 text-[11px] font-medium px-2 py-1 bg-gray-50 border border-gray-300 rounded-lg text-center focus:outline-none focus:border-brand-500 focus:bg-white" title="Color variation tag for this photo" />
              </div>
            @endforeach
          </div>
        @endif
        <div>
          <label class="lbl">Add images (first upload becomes the main image if none set)</label>
          <input id="imageFileInput" name="images[]" type="file" accept="image/*" multiple class="text-sm border border-slate-200 rounded-xl p-2 w-full bg-white cursor-pointer" />
          <div id="newImagesPreview" class="flex gap-3 flex-wrap mt-3"></div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
      <div class="card p-5 space-y-3">
        <h3 class="font-semibold text-slate-900">Organization</h3>
        <div>
          <label class="lbl font-semibold text-slate-800">Category <span class="text-red-500">*</span></label>
          <select name="category_id" class="inp bg-white" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="card p-5 space-y-3">
        <h3 class="font-semibold text-slate-900">Visibility &amp; Badges</h3>
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
          <label class="flex items-center justify-between text-sm font-medium text-slate-700 cursor-pointer py-1 border-b border-slate-50 last:border-0 hover:text-emerald-700">
            <span>{{ $label }}</span>
            <input type="checkbox" name="{{ $field }}" value="1" class="h-5 w-5 accent-emerald-600 rounded cursor-pointer" @checked(old($field, $product->$field)) />
          </label>
        @endforeach
      </div>

      <div class="card p-5 space-y-3">
        <h3 class="font-semibold">SEO</h3>
        <div><label class="lbl">Meta title</label><input name="meta_title" class="inp" value="{{ old('meta_title', $product->meta_title) }}" /></div>
        <div><label class="lbl">Meta description</label><textarea name="meta_description" class="inp" rows="2">{{ old('meta_description', $product->meta_description) }}</textarea></div>
        <div><label class="lbl">Meta keywords (comma-separated)</label><textarea name="meta_keywords" class="inp" rows="2" placeholder="jacket, leather, men, buy online">{{ old('meta_keywords', $product->meta_keywords) }}</textarea></div>
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
    row.className = 'spec-row grid grid-cols-[1fr_1fr_auto] gap-2 items-start';
    row.innerHTML = `
      <div>
        <label class="lbl">Label</label>
        <input name="spec_labels[]" class="inp" value="" placeholder="e.g. Model" />
      </div>
      <div>
        <label class="lbl">Value</label>
        <input name="spec_values[]" class="inp" value="" placeholder="e.g. A0023" />
      </div>
      <button type="button" class="remove-spec-row mt-6 h-10 w-10 rounded-lg border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200" title="Remove" aria-label="Remove row">×</button>
    `;
    list.appendChild(row);
    bindRemove(row.querySelector('.remove-spec-row'));
  });
})();

(function () {
  const genBtn = document.getElementById('generateMatrixBtn');
  const body = document.getElementById('skuMatrixBody');
  const sizesInput = document.getElementById('sizesInput');
  const colorsInput = document.getElementById('colorsInput');

  if (!genBtn || !body) return;

  genBtn.addEventListener('click', () => {
    const sizes = (sizesInput ? sizesInput.value : '').split(',').map(s => s.trim()).filter(Boolean);
    const colors = (colorsInput ? colorsInput.value : '').split(',').map(c => c.trim()).filter(Boolean);

    if (sizes.length === 0 && colors.length === 0) {
      alert('Please enter at least one Size or Color first.');
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

    body.innerHTML = '';
    combinations.forEach((combo, idx) => {
      const row = document.createElement('tr');
      row.className = 'sku-row';

      let attrBadgesHtml = '';
      let attrInputsHtml = '';
      Object.keys(combo).forEach(k => {
        const v = combo[k];
        attrBadgesHtml += `<span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-200">${k}: ${v}</span> `;
        attrInputsHtml += `<input type="hidden" name="sku_matrix[${idx}][attributes][${k}]" value="${v}" />`;
      });

      let sizeVal = (combo.Size || '').replace(/[^A-Za-z0-9]/g, '');
      let colorVal = (combo.Color || '').split(' ')[0].replace(/[^A-Za-z0-9]/g, '');
      let autoSkuHint = (colorVal || sizeVal) ? (colorVal + sizeVal) : 'VAR';

      row.innerHTML = `
        <td class="py-2.5 px-3">
          ${attrInputsHtml}
          <div class="flex items-center gap-1 flex-wrap">${attrBadgesHtml}</div>
        </td>
        <td class="py-2.5 px-3">
          <input name="sku_matrix[${idx}][sku]" value="" placeholder="Auto: [SKU]-${autoSkuHint}" class="inp text-xs py-1 px-2 font-mono" />
        </td>
        <td class="py-2.5 px-3">
          <input name="sku_matrix[${idx}][stock]" type="number" min="0" value="10" class="inp text-xs py-1 px-2 text-center font-bold" required />
        </td>
        <td class="py-2.5 px-3">
          <input name="sku_matrix[${idx}][price_adjustment]" type="number" step="0.01" value="0" class="inp text-xs py-1 px-2 text-center" />
        </td>
        <td class="py-2.5 px-3 text-center">
          <input type="checkbox" name="sku_matrix[${idx}][is_active]" value="1" checked class="accent-primary h-4 w-4" />
        </td>
        <td class="py-2.5 px-3 text-center">
          <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 font-bold text-base">×</button>
        </td>
      `;
      body.appendChild(row);
    });
  });
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
