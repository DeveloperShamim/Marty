@extends('layouts.admin')
@section('title', 'Inventory & Variant Stock')

@section('content')
<div class="space-y-6">
  <!-- Top Stat Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="card p-5 bg-white border border-stone-200 rounded-2xl shadow-xs">
      <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Total Variants / SKUs</p>
      <p class="text-3xl font-black text-stone-900 mt-1 font-mono">{{ number_format($totalSkusCount) }}</p>
      <p class="text-xs text-stone-400 mt-1">Tracked across catalog</p>
    </div>

    <div class="card p-5 bg-amber-50/50 border border-amber-200/80 rounded-2xl shadow-xs">
      <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Low Stock Alerts (≤ 3)</p>
      <p class="text-3xl font-black text-amber-800 mt-1 font-mono">{{ number_format($lowStockCount) }}</p>
      <p class="text-xs text-amber-700/80 mt-1">Requires reorder</p>
    </div>

    <div class="card p-5 bg-rose-50/50 border border-rose-200/80 rounded-2xl shadow-xs">
      <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Out of Stock</p>
      <p class="text-3xl font-black text-rose-800 mt-1 font-mono">{{ number_format($outOfStockCount) }}</p>
      <p class="text-xs text-rose-700/80 mt-1">Unavailable for buyers</p>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div class="card p-5 bg-white border border-stone-200 rounded-2xl shadow-xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-extrabold text-stone-900">Inventory Stock Management</h2>
        <p class="text-xs text-stone-500 mt-0.5">Organized product catalog inventory with expandable variant stock controls.</p>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1 bg-stone-100 p-1.5 rounded-xl text-xs font-bold self-start sm:self-auto border border-stone-200/60">
        <a href="{{ route('admin.inventory.index', ['filter' => 'all', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all {{ $filter === 'all' ? 'bg-white shadow-xs text-stone-900' : 'text-stone-500 hover:text-stone-800' }}">All Items</a>
        <a href="{{ route('admin.inventory.index', ['filter' => 'low_stock', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all {{ $filter === 'low_stock' ? 'bg-amber-500 text-white shadow-xs' : 'text-stone-500 hover:text-stone-800' }}">
          Low Stock @if($lowStockCount > 0)<span class="ml-1 bg-amber-700 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $lowStockCount }}</span>@endif
        </a>
        <a href="{{ route('admin.inventory.index', ['filter' => 'out_of_stock', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all {{ $filter === 'out_of_stock' ? 'bg-rose-600 text-white shadow-xs' : 'text-stone-500 hover:text-stone-800' }}">
          Out of Stock @if($outOfStockCount > 0)<span class="ml-1 bg-rose-800 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $outOfStockCount }}</span>@endif
        </a>
      </div>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex items-center gap-2">
      <input type="hidden" name="filter" value="{{ $filter }}" />
      <input type="text" name="q" value="{{ $q }}" placeholder="Search by product name, SKU, or variant attributes..." class="border border-stone-300 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-stone-50 flex-1" />
      <button type="submit" class="btn-primary py-2 px-4 text-xs font-bold">Search</button>
      @if($q)
        <a href="{{ route('admin.inventory.index', ['filter' => $filter]) }}" class="px-3 py-2 text-xs font-bold text-stone-500 hover:text-stone-800 underline">Clear Search</a>
      @endif
    </form>
  </div>

  <!-- Grouped Inventory Stock Table -->
  <div class="card bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm border-collapse">
        <thead class="bg-stone-50 text-stone-500 uppercase tracking-wider text-[11px] font-extrabold border-b border-stone-200">
          <tr>
            <th class="py-3.5 px-5">Product Details</th>
            <th class="py-3.5 px-4">Category</th>
            <th class="py-3.5 px-4 text-center">Variants</th>
            <th class="py-3.5 px-4 text-center">Total Stock</th>
            <th class="py-3.5 px-4">Stock Status</th>
            <th class="py-3.5 px-5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 text-stone-700">
          @forelse($products as $product)
            @php
              $hasSkus = $product->skus->isNotEmpty();
              $totalStock = (int) $product->stock_quantity;
              $isLow = $product->isLowStock(3);
              $isOut = $totalStock <= 0;
            @endphp
            <!-- Parent Product Row -->
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-5">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover bg-stone-100 rounded-xl border border-stone-200 shrink-0" alt="">
                  <div class="min-w-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="font-bold text-stone-900 hover:text-brand-600 transition truncate block max-w-xs">{{ $product->name }}</a>
                    <span class="text-stone-400 text-xs font-mono">{{ $product->sku ?: 'No SKU' }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-xs font-semibold text-stone-600">{{ $product->category?->name ?? '—' }}</td>
              <td class="py-3.5 px-4 text-center">
                @if($hasSkus)
                  <button type="button" onclick="toggleVariantGroup({{ $product->id }})" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200/60 transition-colors cursor-pointer" title="Click to expand variant list">
                    <span>📦 {{ $product->skus->count() }} Variants</span>
                    <span id="arrow-{{ $product->id }}" class="text-[10px] transition-transform">▾</span>
                  </button>
                @else
                  <span class="text-xs text-stone-400 font-medium italic">Standard Item</span>
                @endif
              </td>
              <td class="py-3.5 px-4 text-center font-extrabold text-stone-900 text-base font-mono">
                {{ number_format($totalStock) }}
              </td>
              <td class="py-3.5 px-4">
                @if($isOut)
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-rose-100 text-rose-800 border border-rose-200">Out of Stock</span>
                @elseif($isLow)
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Low Stock Alert</span>
                @else
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">In Stock</span>
                @endif
              </td>
              <td class="py-3.5 px-5 text-right">
                @if($hasSkus)
                  <button type="button" onclick="toggleVariantGroup({{ $product->id }})" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 transition-colors inline-flex items-center gap-1 cursor-pointer">
                    <span>Manage Variants</span>
                    <span id="btn-arrow-{{ $product->id }}">▾</span>
                  </button>
                @else
                  <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-2 justify-end">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="0" class="border border-stone-300 rounded-xl text-xs py-1.5 px-2.5 w-20 text-center font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20" required />
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-stone-900 hover:bg-stone-800 text-white transition-colors cursor-pointer">Save</button>
                  </form>
                @endif
              </td>
            </tr>

            <!-- Expandable Accordion Row for Variants -->
            @if($hasSkus)
              <tr id="variant-group-{{ $product->id }}" class="hidden bg-stone-50/70 border-y border-stone-200/80">
                <td colspan="6" class="p-4 sm:p-5">
                  <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
                      <span class="text-xs font-extrabold text-stone-800 flex items-center gap-1.5">
                        <span>📦</span> Variant Stock Breakdown for {{ $product->name }}
                      </span>
                      <span class="text-[11px] font-bold text-stone-400">{{ $product->skus->count() }} total variants</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                      @foreach($product->skus as $sku)
                        @php
                          $sStock = (int) $sku->stock_quantity;
                          $sIsLow = $sStock <= 3 && $sStock > 0;
                          $sIsOut = $sStock <= 0;
                        @endphp
                        <div class="p-3 rounded-xl border border-stone-200/80 bg-stone-50/50 flex items-center justify-between gap-3 hover:border-brand-300 transition-all">
                          <div class="min-w-0 flex-1">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-extrabold bg-white text-stone-800 border border-stone-200 inline-block truncate max-w-full shadow-2xs">
                              {{ $sku->attributeLabel() }}
                            </span>
                            <p class="text-[10px] font-mono text-stone-400 mt-1 truncate">{{ $sku->sku }}</p>
                          </div>

                          <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-1.5 shrink-0">
                            @csrf
                            <input type="hidden" name="sku_id" value="{{ $sku->id }}" />
                            <input type="number" name="stock_quantity" value="{{ $sStock }}" min="0" class="border border-stone-300 rounded-lg text-xs py-1 px-2 w-16 text-center font-bold bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20" required />
                            <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-brand-600 hover:bg-brand-700 text-white transition-colors cursor-pointer shadow-xs">Save</button>
                          </form>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="6" class="py-12 text-center text-stone-400 font-medium">No inventory products found matching criteria.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4 border-t border-stone-200 bg-stone-50/50">{{ $products->links() }}</div>
  </div>
</div>

<script>
function toggleVariantGroup(productId) {
  const row = document.getElementById('variant-group-' + productId);
  const arrow = document.getElementById('arrow-' + productId);
  const btnArrow = document.getElementById('btn-arrow-' + productId);

  if (row) {
    if (row.classList.contains('hidden')) {
      row.classList.remove('hidden');
      if (arrow) arrow.style.transform = 'rotate(180deg)';
      if (btnArrow) btnArrow.style.transform = 'rotate(180deg)';
    } else {
      row.classList.add('hidden');
      if (arrow) arrow.style.transform = 'rotate(0deg)';
      if (btnArrow) btnArrow.style.transform = 'rotate(0deg)';
    }
  }
}
</script>
@endsection
