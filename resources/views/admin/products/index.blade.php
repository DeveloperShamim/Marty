@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="space-y-4 sm:space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">Products</h2>
      <p class="text-xs text-stone-500 mt-0.5">Manage store catalog, stock quantities, and bulk operations.</p>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
      {{-- Export CSV Button --}}
      <a href="{{ route('admin.products.export', request()->only(['q', 'category'])) }}" class="flex-1 sm:flex-none justify-center px-3 py-2 text-xs font-bold text-stone-700 bg-white border border-stone-300 hover:bg-stone-50 rounded-xl transition-colors inline-flex items-center gap-1.5 shadow-xs" title="Export matching products to CSV">
        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Export</span>
      </a>

      {{-- Import CSV Button --}}
      <button type="button" onclick="document.getElementById('importProductModal').classList.remove('hidden')" class="flex-1 sm:flex-none justify-center px-3 py-2 text-xs font-bold text-stone-700 bg-white border border-stone-300 hover:bg-stone-50 rounded-xl transition-colors inline-flex items-center gap-1.5 shadow-xs">
        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        <span>Import</span>
      </button>

      {{-- New Product Button --}}
      <a href="{{ route('admin.products.create') }}" class="btn-primary flex-1 sm:flex-none justify-center py-2 px-3.5 text-xs font-bold flex items-center gap-1">
        <span>+ New Product</span>
      </a>
    </div>
  </div>

  <div class="card bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
    <form method="GET" class="p-3.5 sm:p-4 border-b border-stone-100 flex flex-col sm:flex-row flex-wrap gap-2.5 sm:gap-3 items-stretch sm:items-center bg-stone-50/50">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search product name, SKU, or attribute..." class="flex-1 border border-stone-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white" />
      <div class="flex items-center gap-2">
        <select name="category" onchange="this.form.submit()" class="border border-stone-300 rounded-xl px-3 py-2 text-xs sm:text-sm bg-white font-medium flex-1 sm:flex-none">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
        <button class="btn-primary text-xs py-2 px-3.5 shrink-0">Filter</button>
        @if(!empty($q) || !empty($category))
          <a href="{{ route('admin.products.index') }}" class="text-xs text-stone-500 hover:text-stone-800 underline shrink-0">Reset</a>
        @endif
      </div>
    </form>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-stone-500 bg-stone-50 border-b border-stone-200">
          <tr>
            <th class="px-4 py-3 text-center w-12">
              <input type="checkbox" id="selectAllProducts" class="rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-stone-300 cursor-pointer" title="Select all on this page" />
            </th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider">Product</th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider">Category</th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider">Price</th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider">Stock</th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 font-bold text-xs uppercase tracking-wider text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
          @forelse($products as $product)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="px-4 py-3 text-center">
                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-select-chk rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-stone-300 cursor-pointer" />
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-xl bg-stone-100 border border-stone-200 shrink-0" alt="">
                  <div class="min-w-0">
                    <p class="font-bold text-stone-900 truncate max-w-xs">{{ $product->name }}</p>
                    <p class="text-stone-400 text-xs font-mono">{{ $product->sku }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3 text-stone-600 font-medium">{{ $product->category?->name ?? '—' }}</td>
              <td class="px-5 py-3 font-bold text-stone-900">
                {{ money($product->price) }}
                @if($product->on_sale)
                  <span class="text-stone-400 line-through text-xs font-normal ml-1">{{ money($product->regular_price) }}</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <span class="font-bold {{ $product->stock_quantity <= 3 ? 'text-rose-600' : 'text-stone-700' }}">{{ $product->stock_quantity }}</span>
              </td>
              <td class="px-5 py-3">
                @if($product->is_published)
                  <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">Published</span>
                @else
                  <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-stone-100 text-stone-600">Draft</span>
                @endif
              </td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <a href="{{ route('admin.products.edit', $product) }}" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-stone-100 hover:bg-stone-200 text-stone-700 transition-colors">Edit</a>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline ml-1" onsubmit="return confirm('Delete this product?')">
                  @csrf
                  @method('DELETE')
                  <button class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200/60 transition-colors">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-stone-400 font-medium">No products found matching criteria.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card List View --}}
    <div class="block sm:hidden divide-y divide-stone-100 bg-white">
      @forelse($products as $product)
        <div class="p-3.5 space-y-2.5">
          <div class="flex items-start gap-3">
            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-select-chk rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-stone-300 cursor-pointer mt-1" />
            <img src="{{ $product->imageUrl() }}" class="h-12 w-12 object-cover rounded-xl bg-stone-100 border border-stone-200 shrink-0" alt="">
            <div class="min-w-0 flex-1">
              <a href="{{ route('admin.products.edit', $product) }}" class="font-extrabold text-xs text-stone-900 hover:text-brand-600 truncate block">{{ $product->name }}</a>
              <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                <span class="text-stone-400 font-mono">{{ $product->sku ?: 'No SKU' }}</span>
                <span class="text-stone-300">•</span>
                <span class="text-stone-500 font-semibold truncate">{{ $product->category?->name ?? 'Uncategorized' }}</span>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2 pt-2 border-t border-stone-100">
            <div>
              <span class="text-xs font-extrabold text-stone-900">{{ money($product->price) }}</span>
              @if($product->on_sale)
                <span class="text-stone-400 line-through text-[10px] ml-1">{{ money($product->regular_price) }}</span>
              @endif
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $product->stock_quantity <= 3 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-stone-100 text-stone-700' }}">
                Stock: {{ $product->stock_quantity }}
              </span>

              @if($product->is_published)
                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800">Published</span>
              @else
                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-stone-100 text-stone-600">Draft</span>
              @endif
            </div>
          </div>

          <div class="flex items-center justify-end gap-1.5 pt-1">
            <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1 text-xs font-bold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 transition">
              Edit
            </a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete {{ $product->name }}?')">
              @csrf
              @method('DELETE')
              <button class="px-3 py-1 text-xs font-bold rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200/60 transition">
                Delete
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400">No products found matching criteria.</div>
      @endforelse
    </div>

    <div class="p-3.5 sm:p-4 border-t border-stone-200 bg-stone-50/50">{{ $products->links() }}</div>
  </div>
</div>

{{-- Floating Bulk Actions Bar --}}
<div id="bulkActionsBar" class="fixed bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white rounded-2xl shadow-2xl px-4 py-2.5 sm:px-6 sm:py-3.5 flex items-center gap-3 sm:gap-5 hidden transition-all duration-200 border border-slate-700 max-w-[92vw] sm:max-w-auto">
  <div class="flex items-center gap-2">
    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
    <span class="text-xs font-extrabold text-slate-100 tracking-wide truncate"><span id="selectedCount">0</span> selected</span>
  </div>
  <form id="bulkDeleteForm" action="{{ route('admin.products.bulk-delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all selected products? This action cannot be undone.')">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds" value="" />
    <button type="submit" class="px-3.5 py-1.5 sm:px-4 sm:py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      <span>Delete Selected</span>
    </button>
  </form>
</div>

{{-- Bulk Product Import Modal --}}
<div id="importProductModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
  <div class="bg-white rounded-2xl shadow-xl border border-stone-200 w-full max-w-lg overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-stone-100 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-xl">📊</span>
        <h3 class="font-extrabold text-stone-900 text-sm sm:text-base">Bulk Import Products (CSV)</h3>
      </div>
      <button type="button" onclick="document.getElementById('importProductModal').classList.add('hidden')" class="h-8 w-8 rounded-lg text-stone-400 hover:bg-stone-100 flex items-center justify-center text-lg font-bold">&times;</button>
    </div>

    <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4">
      @csrf

      <div class="bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl p-3.5 sm:p-4 space-y-2">
        <p class="font-bold flex items-center gap-1.5">
          <span>💡</span>
          <span>Instructions &amp; Formatting:</span>
        </p>
        <ul class="list-disc list-inside space-y-1 text-[11px] leading-relaxed text-amber-800">
          <li>CSV file must include headers: <code class="bg-amber-100 px-1 rounded">name</code>, <code class="bg-amber-100 px-1 rounded">sku</code>, <code class="bg-amber-100 px-1 rounded">regular_price</code>, <code class="bg-amber-100 px-1 rounded">sale_price</code>, <code class="bg-amber-100 px-1 rounded">stock_quantity</code>.</li>
          <li>Categories and Brands will be matched or automatically created if new.</li>
          <li>If an existing product SKU is found in your CSV, its stock and price will be updated.</li>
        </ul>
        <div class="pt-1">
          <a href="{{ route('admin.products.sample-csv') }}" class="text-xs font-extrabold text-brand-600 hover:text-brand-700 underline inline-flex items-center gap-1">
            <span>📥 Download Sample CSV Template</span>
          </a>
        </div>
      </div>

      <div class="space-y-1.5">
        <label for="csv_file" class="block text-xs font-bold text-stone-700">Select CSV File (.csv)</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv, text/csv" required class="w-full text-xs font-medium text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 border border-stone-300 rounded-xl bg-stone-50 cursor-pointer" />
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-stone-100">
        <button type="button" onclick="document.getElementById('importProductModal').classList.add('hidden')" class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition-colors">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors inline-flex items-center gap-1.5">
          <span>Start Import</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const selectAll = document.getElementById('selectAllProducts');
  const checkboxes = document.querySelectorAll('.product-select-chk');
  const bulkBar = document.getElementById('bulkActionsBar');
  const countEl = document.getElementById('selectedCount');
  const bulkIdsInput = document.getElementById('bulkDeleteIds');

  function updateBulkState() {
    const checked = Array.from(checkboxes).filter(c => c.checked);
    const ids = checked.map(c => c.value);

    if (checked.length > 0) {
      bulkBar.classList.remove('hidden');
      countEl.textContent = checked.length;
      bulkIdsInput.value = ids.join(',');
    } else {
      bulkBar.classList.add('hidden');
      bulkIdsInput.value = '';
    }

    if (selectAll) {
      selectAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', function() {
      checkboxes.forEach(c => c.checked = selectAll.checked);
      updateBulkState();
    });
  }

  checkboxes.forEach(c => c.addEventListener('change', updateBulkState));
});
</script>
@endsection
