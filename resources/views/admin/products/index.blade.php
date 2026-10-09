@extends('layouts.admin')
@section('title', 'Products')
@section('subtitle', 'Manage your catalog, prices and stock.')

@section('page-actions')
  <a href="{{ route('admin.products.export', request()->only(['q', 'category'])) }}" class="pill-btn" title="Export matching products to CSV">
    Export
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg></span>
  </a>
  <button type="button" onclick="document.getElementById('importProductModal').classList.remove('hidden')" class="pill-btn">
    Import
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9M7 14l5-5 5 5"/><path d="M5 3h14"/></svg></span>
  </button>
  <a href="{{ route('admin.products.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    New product
  </a>
@endsection

@section('content')
@php
  $tabs = ['all' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'low' => 'Low stock', 'out' => 'Out of stock'];
  $keep = array_filter(['q' => $q, 'category' => $category]);
  $stockChip = function ($product) {
      if ($product->stock_quantity <= 0) return ['Out of stock', 'bg-rose-50 text-rose-700'];
      if ($product->stock_quantity <= 3) return ['Low · ' . $product->stock_quantity, 'bg-amber-50 text-amber-800'];
      return [null, null];
  };
@endphp
<div class="space-y-4">
  <div class="card overflow-hidden" data-product-list>
    {{-- Quick views: the counts cover the whole catalog --}}
    <nav class="px-3 sm:px-4 pt-3 sm:pt-4 overflow-x-auto no-scrollbar" aria-label="Product views">
      <div class="inline-flex items-center gap-1 whitespace-nowrap">
        @foreach($tabs as $key => $label)
          @php $active = $show === $key; @endphp
          <a href="{{ route('admin.products.index', $keep + ($key === 'all' ? [] : ['show' => $key])) }}" data-product-tab="{{ $key }}"
             class="h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
             @if($active) style="background: var(--brand-dark);" aria-current="true" @endif>
            {{ $label }}
            @if(($counts[$key] ?? 0) > 0)
              <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : (in_array($key, ['low', 'out']) ? 'bg-amber-50 text-amber-800' : 'bg-gray-100 text-gray-600') }}">{{ $counts[$key] }}</span>
            @endif
          </a>
        @endforeach
      </div>
    </nav>

    <form method="GET" class="p-3 sm:p-4 flex items-center gap-2">
      @if($show !== 'all')<input type="hidden" name="show" value="{{ $show }}">@endif
      <label class="relative flex-1 min-w-0">
        <span class="sr-only">Search products</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="Search name, SKU or size" enterkeyhint="search" class="w-full h-10 pl-10 pr-9 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
        @if(!empty($q))
          <a href="{{ route('admin.products.index', array_filter(['category' => $category, 'show' => $show === 'all' ? null : $show])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-700" aria-label="Clear search">✕</a>
        @endif
      </label>
      <label class="relative shrink-0 w-full sm:w-auto">
        <span class="sr-only">Category</span>
        <select name="category" onchange="this.form.submit()" class="w-full sm:w-auto h-10 rounded-full bg-gray-100 border border-transparent pl-4 pr-9 text-sm font-medium text-gray-800 appearance-none cursor-pointer focus:bg-white focus:border-gray-200 outline-none truncate">
          <option value="">All categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
        <svg class="w-3.5 h-3.5 text-gray-500 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
      </label>
    </form>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 pl-4 lg:pl-5 pr-0 w-8">
              <input type="checkbox" id="selectAllProducts" class="rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-gray-300 cursor-pointer" title="Select all on this page" aria-label="Select all products on this page" />
            </th>
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4 text-right">Price</th>
            <th class="py-3 px-4">Stock</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 pr-4 lg:pr-5 text-right"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($products as $product)
            @php [$chipText, $chipTone] = $stockChip($product); @endphp
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 pl-4 lg:pl-5 pr-0">
                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-select-chk rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-gray-300 cursor-pointer" aria-label="Select {{ $product->name }}" />
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl bg-gray-100 shrink-0" alt="">
                  <div class="min-w-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-gray-900 hover:underline truncate block max-w-xs">{{ $product->name }}</a>
                    <p class="text-gray-400 text-[11px] font-mono">{{ $product->sku }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
              <td class="py-3 px-4 whitespace-nowrap text-right">
                <span class="font-semibold text-gray-900 tabular-nums">{{ money($product->price) }}</span>
                @if($product->on_sale)
                  <span class="block text-gray-400 line-through text-[11px] tabular-nums">{{ money($product->regular_price) }}</span>
                @endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                @if($chipText)
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $chipTone }}" data-stock-chip>{{ $chipText }}</span>
                @else
                  <span class="font-semibold tabular-nums text-gray-700">{{ $product->stock_quantity }}</span>
                @endif
              </td>
              <td class="py-3 px-4">
                @if($product->is_published)
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Published</span>
                @else
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Draft</span>
                @endif
              </td>
              <td class="py-3 px-4 pr-4 lg:pr-5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.products.edit', $product) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold inline-flex items-center">Edit</a>
                  @include('admin.products.partials.row-menu', ['product' => $product, 'up' => $loop->remaining < 3 && $loop->count > 4])
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500 text-sm">No products match these filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: tap a card to edit; less common actions sit in the ⋯ menu --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($products as $product)
        @php [$chipText, $chipTone] = $stockChip($product); @endphp
        <article class="relative rounded-2xl bg-gray-50/80 p-3 flex items-center gap-3" data-product-card>
          <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-select-chk relative z-10 rounded text-brand-600 focus:ring-brand-500 h-4 w-4 border-gray-300 cursor-pointer shrink-0" aria-label="Select {{ $product->name }}" />
          <img src="{{ $product->imageUrl() }}" class="h-12 w-12 object-cover rounded-xl bg-gray-100 shrink-0" alt="">
          <div class="min-w-0 flex-1">
            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-[13px] text-gray-900 truncate block after:absolute after:inset-0 after:rounded-2xl">{{ $product->name }}</a>
            <p class="mt-0.5 text-[11px] text-gray-500 truncate">{{ $product->category?->name ?? 'Uncategorized' }}</p>
            <div class="mt-1 flex items-center gap-1.5 flex-wrap">
              <span class="text-[13px] font-semibold text-gray-900 tabular-nums">{{ money($product->price) }}</span>
              @if($chipText)
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $chipTone }}" data-stock-chip>{{ $chipText }}</span>
              @else
                <span class="text-[11px] text-gray-500 tabular-nums">· {{ $product->stock_quantity }} in stock</span>
              @endif
              @unless($product->is_published)
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-200/70 text-gray-700">Draft</span>
              @endunless
            </div>
          </div>
          <div class="relative z-10 shrink-0">
            @include('admin.products.partials.row-menu', ['product' => $product, 'up' => $loop->remaining < 2 && $loop->count > 3])
          </div>
        </article>
      @empty
        <div class="py-12 text-center text-sm text-gray-500">No products match these filters.</div>
      @endforelse
    </div>

    @if($products->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $products->links() }}</div>
    @endif
  </div>
</div>

{{-- Floating bulk actions bar --}}
<div id="bulkActionsBar" data-above-nav class="fixed bottom-3 inset-x-3 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 sm:bottom-6 z-40 text-white rounded-full shadow-2xl pl-4 pr-1.5 py-1.5 flex items-center justify-between gap-2 hidden" style="background: var(--brand-dark);">
  <span class="text-[13px] font-semibold whitespace-nowrap"><span id="selectedCount">0</span> selected</span>
  <div class="flex items-center gap-1.5">
    <form action="{{ route('admin.products.bulk-status') }}" method="POST" class="bulk-status-form">
      @csrf
      <input type="hidden" name="ids" value="" />
      <input type="hidden" name="status" value="publish" />
      <button type="submit" id="bulkPublish" class="h-9 px-3.5 bg-white/15 hover:bg-white/25 text-white font-semibold text-xs rounded-full">Publish</button>
    </form>
    <form action="{{ route('admin.products.bulk-status') }}" method="POST" class="bulk-status-form">
      @csrf
      <input type="hidden" name="ids" value="" />
      <input type="hidden" name="status" value="hide" />
      <button type="submit" id="bulkHide" class="h-9 px-3.5 bg-white/15 hover:bg-white/25 text-white font-semibold text-xs rounded-full" title="Move to draft so shoppers can't see them">Hide</button>
    </form>
    <form id="bulkDeleteForm" action="{{ route('admin.products.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete the selected products? This can\'t be undone.')">
      @csrf
      <input type="hidden" name="ids" id="bulkDeleteIds" value="" />
      <button type="submit" class="h-9 w-9 sm:w-auto sm:px-3.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-full transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer" aria-label="Delete selected">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
        <span class="hidden sm:inline">Delete</span>
      </button>
    </form>
  </div>
</div>

{{-- Bulk product import modal --}}
<div id="importProductModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-4 bg-gray-900/50 hidden">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
    <div class="px-4 sm:px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
      <h3 class="font-semibold text-gray-900 text-[15px]">Import products from CSV</h3>
      <button type="button" onclick="document.getElementById('importProductModal').classList.add('hidden')" class="h-8 w-8 rounded-full text-gray-400 hover:bg-gray-100 flex items-center justify-center" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-4">
      @csrf

      <div class="bg-gray-50 text-gray-700 text-xs rounded-xl p-3.5 space-y-2">
        <p class="font-semibold text-gray-900">How it works</p>
        <ul class="list-disc pl-4 space-y-1 text-[12px] leading-relaxed text-gray-600">
          <li>The CSV must include the headers <code class="bg-white px-1 rounded">name</code>, <code class="bg-white px-1 rounded">sku</code>, <code class="bg-white px-1 rounded">regular_price</code>, <code class="bg-white px-1 rounded">sale_price</code> and <code class="bg-white px-1 rounded">stock_quantity</code>.</li>
          <li>Categories and brands are matched, or created if new.</li>
          <li>When a SKU already exists, its stock and price are updated.</li>
        </ul>
        <a href="{{ route('admin.products.sample-csv') }}" class="text-xs font-semibold text-brand-700 hover:underline inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
          Download sample CSV
        </a>
      </div>

      <div>
        <label for="csv_file" class="lbl">CSV file</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv, text/csv" required class="w-full text-xs text-gray-600 file:mr-3 file:h-9 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-800 rounded-xl border border-gray-200 p-1 cursor-pointer" />
      </div>

      <div class="pt-1 flex items-center justify-end gap-2">
        <button type="button" onclick="document.getElementById('importProductModal').classList.add('hidden')" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center gap-1.5" style="background: var(--brand-dark);">Start import</button>
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
  const idInputs = document.querySelectorAll('#bulkActionsBar input[name=ids]');

  // The table and the phone cards each have a box per product; keep the pair in step and count products once.
  function updateBulkState() {
    const ids = [...new Set(Array.from(checkboxes).filter(c => c.checked).map(c => c.value))];
    const all = new Set(Array.from(checkboxes).map(c => c.value));

    bulkBar.classList.toggle('hidden', ids.length === 0);
    countEl.textContent = ids.length;
    idInputs.forEach(i => i.value = ids.join(','));

    if (selectAll) {
      selectAll.checked = all.size > 0 && ids.length === all.size;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', function() {
      checkboxes.forEach(c => c.checked = selectAll.checked);
      updateBulkState();
    });
  }

  checkboxes.forEach(c => c.addEventListener('change', function () {
    checkboxes.forEach(o => { if (o.value === c.value) o.checked = c.checked; });
    updateBulkState();
  }));

  // Close an open ⋯ menu when tapping anywhere else.
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.order-menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });
});
</script>
@endsection
