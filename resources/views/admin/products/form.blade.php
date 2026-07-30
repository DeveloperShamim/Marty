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
      <div class="card p-5 space-y-5">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div>
          <h3 class="font-semibold text-slate-800 text-base">Variants & Stock Inventory Matrix</h3>
          <p class="text-xs text-slate-400 mt-1">Define sizes/colors, then set individual stock quantities per combination (e.g. Black / Size 40 = 10 pairs).</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div><label class="lbl">Sizes (Comma-separated)</label><input id="sizesInput" name="sizes" class="inp" value="{{ old('sizes', $sizeValues) }}" placeholder="40, 41, 42" /></div>
          <div><label class="lbl">Colors (Comma-separated)</label><input id="colorsInput" name="colors" class="inp" value="{{ old('colors', $colorValues) }}" placeholder="Black, Brown" /></div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
        @endphp

        <div class="pt-3 border-t border-slate-100 space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="font-semibold text-sm text-slate-700">Stock Matrix Per Variant Combination</h4>
            <button type="button" id="generateMatrixBtn" class="text-xs font-semibold text-teal-600 hover:text-teal-700 cursor-pointer bg-teal-50 px-3 py-1.5 rounded-lg border border-teal-200">
              ⚡ Generate Combination Matrix
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
              <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Variant Combination</th>
                  <th class="py-2.5 px-3">SKU Code</th>
                  <th class="py-2.5 px-3 w-28">Stock Qty</th>
                  <th class="py-2.5 px-3 w-28">Price Adj (৳)</th>
                  <th class="py-2.5 px-3 w-16 text-center">Active</th>
                  <th class="py-2.5 px-3 w-10"></th>
                </tr>
              </thead>
              <tbody id="skuMatrixBody" class="divide-y divide-slate-100">
                @forelse($skus as $index => $sku)
                  <tr class="sku-row">
                    <td class="py-2.5 px-3">
                      <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                      <div class="flex items-center gap-1 flex-wrap">
                        @foreach($sku->getAttributesData() as $k => $v)
                          <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                          <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-200">{{ $k }}: {{ $v }}</span>
                        @endforeach
                      </div>
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="e.g. SHOE-BLK-40" class="inp text-xs py-1 px-2 font-mono" />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="inp text-xs py-1 px-2 text-center font-bold" required />
                    </td>
                    <td class="py-2.5 px-3">
                      <input name="sku_matrix[{{ $index }}][price_adjustment]" type="number" step="0.01" value="{{ $sku->price_adjustment }}" class="inp text-xs py-1 px-2 text-center" />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="accent-primary h-4 w-4" />
                    </td>
                    <td class="py-2.5 px-3 text-center">
                      <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 font-bold text-base">×</button>
                    </td>
                  </tr>
                @empty
                  <tr id="emptyMatrixRow">
                    <td colspan="6" class="py-4 text-center text-slate-400 italic">
                      No variant combinations generated yet. Enter Sizes/Colors above and click "Generate Combination Matrix".
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
              <div class="relative group" id="imgcard-{{ $img->id }}">
                <img src="{{ $img->url() }}" class="h-24 w-24 object-cover rounded-lg bg-gray-100 border border-gray-200 {{ $img->is_primary ? 'ring-2 ring-primary' : '' }}" alt="">
                @if($img->is_primary)<span class="absolute top-1 left-1 bg-primary text-white text-[10px] font-semibold px-1.5 py-0.5 rounded shadow">Main</span>@endif
                <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white h-6 w-6 rounded-full text-xs font-bold shadow-md flex items-center justify-center cursor-pointer transition transform hover:scale-110 z-10" title="Delete Image">&times;</button>
              </div>
            @endforeach
          </div>
        @endif
        <div>
          <label class="lbl">Add images (first upload becomes the main image if none set)</label>
          <input name="images[]" type="file" accept="image/*" multiple class="text-sm" />
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
      <div class="card p-5 space-y-3">
        <h3 class="font-semibold">Organization</h3>
        <div>
          <label class="lbl">Category</label>
          <select name="category_id" class="inp" required>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === $cat->id)>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="card p-5 space-y-3">
        <h3 class="font-semibold">Visibility</h3>
        @php
          $toggles = [
            'is_published' => 'Published',
            'is_featured' => 'Featured',
            'is_new_arrival' => 'New arrival',
            'is_best_seller' => 'Best seller',
            'is_flash_sale'  => 'Flash sale',
          ];
        @endphp
        @foreach($toggles as $field => $label)
          <label class="flex items-center justify-between text-sm cursor-pointer">
            <span>{{ $label }}</span>
            <input type="checkbox" name="{{ $field }}" value="1" class="h-5 w-9 accent-primary" @checked(old($field, $product->$field)) />
          </label>
        @endforeach
        <div class="pt-2 border-t border-gray-100">
          <label class="lbl">Flash sale progress (0–100%)</label>
          <input name="flash_sale_progress" type="number" min="0" max="100" class="inp" value="{{ old('flash_sale_progress', $product->flash_sale_progress ?? 50) }}" />
          <p class="text-xs text-gray-400 mt-1">Homepage progress bar fill when this product is in Flash Sale.</p>
        </div>
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
</script>
@endpush
@endsection
