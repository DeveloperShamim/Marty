@extends('layouts.admin')
@section('title', 'Inventory & Variant Stock')

@section('content')
<div class="space-y-4 sm:space-y-6">
  <!-- Top Stat Cards (3 Cards Side-by-Side on Mobile) -->
  <div class="grid grid-cols-3 gap-2 sm:gap-4">
    <div class="card p-2.5 sm:p-5 bg-white border border-stone-200 rounded-xl sm:rounded-2xl shadow-xs text-center sm:text-left">
      <p class="text-[9px] sm:text-xs font-extrabold uppercase tracking-wider text-stone-400 truncate">Total SKUs</p>
      <p class="text-lg sm:text-3xl font-black text-stone-900 mt-0.5 sm:mt-1 font-mono leading-none">{{ number_format($totalSkusCount) }}</p>
      <p class="text-[9px] sm:text-[11px] text-stone-400 mt-1 hidden sm:block">Tracked across catalog</p>
    </div>

    <div class="card p-2.5 sm:p-5 bg-amber-50/60 border border-amber-200/80 rounded-xl sm:rounded-2xl shadow-xs text-center sm:text-left">
      <p class="text-[9px] sm:text-xs font-extrabold uppercase tracking-wider text-amber-700 truncate">Low Stock</p>
      <p class="text-lg sm:text-3xl font-black text-amber-800 mt-0.5 sm:mt-1 font-mono leading-none">{{ number_format($lowStockCount) }}</p>
      <p class="text-[9px] sm:text-[11px] text-amber-700/80 mt-1 hidden sm:block">Requires reorder</p>
    </div>

    <div class="card p-2.5 sm:p-5 bg-rose-50/60 border border-rose-200/80 rounded-xl sm:rounded-2xl shadow-xs text-center sm:text-left">
      <p class="text-[9px] sm:text-xs font-extrabold uppercase tracking-wider text-rose-700 truncate">Out Stock</p>
      <p class="text-lg sm:text-3xl font-black text-rose-800 mt-0.5 sm:mt-1 font-mono leading-none">{{ number_format($outOfStockCount) }}</p>
      <p class="text-[9px] sm:text-[11px] text-rose-700/80 mt-1 hidden sm:block">Unavailable for buyers</p>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div class="card p-4 sm:p-5 bg-white border border-stone-200 rounded-2xl shadow-xs space-y-3.5 sm:space-y-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
      <div>
        <h2 class="text-base sm:text-lg font-extrabold text-stone-900">Inventory Stock Management</h2>
        <p class="text-xs text-stone-500 mt-0.5">Organized product catalog inventory with expandable variant stock controls.</p>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1 bg-stone-100 p-1.5 rounded-xl text-xs font-bold overflow-x-auto no-scrollbar w-full md:w-auto border border-stone-200/60">
        <a href="{{ route('admin.inventory.index', ['filter' => 'all', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all shrink-0 {{ $filter === 'all' ? 'bg-white shadow-xs text-stone-900' : 'text-stone-500 hover:text-stone-800' }}">All Items</a>
        <a href="{{ route('admin.inventory.index', ['filter' => 'low_stock', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all shrink-0 {{ $filter === 'low_stock' ? 'bg-amber-500 text-white shadow-xs' : 'text-stone-500 hover:text-stone-800' }}">
          Low Stock @if($lowStockCount > 0)<span class="ml-1 bg-amber-700 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $lowStockCount }}</span>@endif
        </a>
        <a href="{{ route('admin.inventory.index', ['filter' => 'out_of_stock', 'q' => $q]) }}" class="px-3 py-1.5 rounded-lg transition-all shrink-0 {{ $filter === 'out_of_stock' ? 'bg-rose-600 text-white shadow-xs' : 'text-stone-500 hover:text-stone-800' }}">
          Out of Stock @if($outOfStockCount > 0)<span class="ml-1 bg-rose-800 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $outOfStockCount }}</span>@endif
        </a>
      </div>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.inventory.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
      <input type="hidden" name="filter" value="{{ $filter }}" />
      <input type="text" name="q" value="{{ $q }}" placeholder="Search by product name, SKU, or variant attributes..." class="border border-stone-300 rounded-xl px-3.5 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-stone-50 flex-1" />
      <div class="flex items-center gap-2">
        <button type="submit" class="btn-primary py-2 px-4 text-xs font-bold flex-1 sm:flex-none">Search</button>
        @if($q)
          <a href="{{ route('admin.inventory.index', ['filter' => $filter]) }}" class="px-3 py-2 text-xs font-bold text-stone-500 hover:text-stone-800 underline shrink-0">Clear</a>
        @endif
      </div>
    </form>
  </div>

  <!-- Grouped Inventory Stock Container -->
  <div class="card bg-white rounded-2xl border border-stone-200 shadow-xs overflow-hidden">
    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-left text-sm border-collapse">
        <thead class="bg-stone-50 text-stone-500 uppercase tracking-wider text-[11px] font-extrabold border-b border-stone-200">
          <tr>
            <th class="py-3.5 px-5">Product Details</th>
            <th class="py-3.5 px-4">Category</th>
            <th class="py-3.5 px-4">Buying Cost</th>
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
              $costPrice = (float) ($product->cost_price ?: 0);
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
              <td class="py-3.5 px-4">
                <span class="font-mono font-bold text-indigo-700 text-xs">৳{{ number_format($costPrice, 2) }}</span>
                @if($hasSkus)
                  <span class="text-[10px] text-stone-400 font-semibold block">(Catalog Avg)</span>
                @endif
              </td>
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
                  <div class="inline-flex items-center gap-1.5 justify-end">
                    <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-1">
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $product->id }}" />
                      <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="0" class="border border-stone-300 rounded-xl text-xs py-1.5 px-2 w-16 text-center font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20" title="Direct stock count" required />
                      <button type="submit" class="px-2.5 py-1.5 text-xs font-bold rounded-xl bg-stone-800 hover:bg-stone-900 text-white transition-colors cursor-pointer" title="Save stock directly">Save</button>
                    </form>
                    <button type="button" onclick="openAddStockModal({
                        productId: {{ $product->id }},
                        skuId: null,
                        title: '{{ addslashes($product->name) }}',
                        sku: '{{ $product->sku ?: "No SKU" }}',
                        currentStock: {{ $totalStock }},
                        currentCost: {{ $costPrice }}
                      })" class="px-2.5 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer shadow-xs inline-flex items-center gap-1" title="Add restock with purchase cost">
                      <span>+ Add Stock</span>
                    </button>
                  </div>
                @endif
              </td>
            </tr>

            <!-- Expandable Accordion Row for Variants -->
            @if($hasSkus)
              <tr id="variant-group-{{ $product->id }}" class="hidden bg-stone-50/70 border-y border-stone-200/80">
                <td colspan="7" class="p-4 sm:p-5">
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
                          $skuCost = (float) ($sku->cost_price ?: $product->cost_price ?: 0);
                        @endphp
                        <div class="p-3.5 rounded-xl border border-stone-200/80 bg-stone-50/50 flex flex-col justify-between gap-3 hover:border-emerald-300 transition-all">
                          <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                              <span class="px-2 py-0.5 rounded-lg text-xs font-extrabold bg-white text-stone-800 border border-stone-200 inline-block truncate max-w-full shadow-2xs">
                                {{ $sku->attributeLabel() }}
                              </span>
                              <span class="text-[10px] font-mono text-stone-400">{{ $sku->sku }}</span>
                            </div>
                            <div class="mt-1.5 text-xs text-stone-600 flex items-center gap-1">
                              <span class="text-stone-400 font-medium">Buying Cost:</span>
                              <strong class="font-mono text-indigo-700 font-bold">৳{{ number_format($skuCost, 2) }}</strong>
                            </div>
                          </div>

                          <div class="flex items-center justify-between gap-1.5 pt-2 border-t border-stone-200/60">
                            <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-1">
                              @csrf
                              <input type="hidden" name="sku_id" value="{{ $sku->id }}" />
                              <input type="number" name="stock_quantity" value="{{ $sStock }}" min="0" class="border border-stone-300 rounded-lg text-xs py-1 px-2 w-16 text-center font-bold bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20" title="Direct stock count" required />
                              <button type="submit" class="px-2 py-1 text-xs font-bold rounded-lg bg-stone-800 hover:bg-stone-900 text-white transition-colors cursor-pointer shadow-xs" title="Save stock directly">Save</button>
                            </form>
                            <button type="button" onclick="openAddStockModal({
                                productId: {{ $product->id }},
                                skuId: {{ $sku->id }},
                                title: '{{ addslashes($product->name) }} ({{ addslashes($sku->attributeLabel()) }})',
                                sku: '{{ $sku->sku }}',
                                currentStock: {{ $sStock }},
                                currentCost: {{ $skuCost }}
                              })" class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer shadow-xs inline-flex items-center gap-1" title="Add restock with purchase cost">
                              <span>+ Add Stock</span>
                            </button>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center text-stone-400 font-medium">No inventory products found matching criteria.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card List View --}}
    <div class="block sm:hidden divide-y divide-stone-100 bg-white">
      @forelse($products as $product)
        @php
          $hasSkus = $product->skus->isNotEmpty();
          $totalStock = (int) $product->stock_quantity;
          $isLow = $product->isLowStock(3);
          $isOut = $totalStock <= 0;
          $costPrice = (float) ($product->cost_price ?: 0);
        @endphp
        <div class="p-3.5 space-y-3">
          <div class="flex items-start gap-3">
            <img src="{{ $product->imageUrl() }}" class="h-12 w-12 object-cover bg-stone-100 rounded-xl border border-stone-200 shrink-0" alt="">
            <div class="min-w-0 flex-1">
              <a href="{{ route('admin.products.edit', $product) }}" class="font-extrabold text-xs text-stone-900 hover:text-brand-600 truncate block">{{ $product->name }}</a>
              <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                <span class="text-stone-400 font-mono">{{ $product->sku ?: 'No SKU' }}</span>
                <span class="text-stone-300">•</span>
                <span class="text-stone-500 font-semibold">{{ $product->category?->name ?? 'Uncategorized' }}</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-1 border-t border-stone-100 text-xs">
            <div>
              <span class="text-[10px] font-bold uppercase text-stone-400 block">Total Stock</span>
              <div class="flex items-center gap-2">
                <span class="text-sm font-black font-mono text-stone-900">{{ number_format($totalStock) }}</span>
                @if($isOut)
                  <span class="px-1.5 py-0.2 text-[9px] font-extrabold rounded-full bg-rose-100 text-rose-800">Out</span>
                @elseif($isLow)
                  <span class="px-1.5 py-0.2 text-[9px] font-extrabold rounded-full bg-amber-100 text-amber-800">Low</span>
                @else
                  <span class="px-1.5 py-0.2 text-[9px] font-extrabold rounded-full bg-emerald-100 text-emerald-800">In Stock</span>
                @endif
              </div>
            </div>

            <div>
              <span class="text-[10px] font-bold uppercase text-stone-400 block">Buying Cost</span>
              <span class="text-xs font-black font-mono text-indigo-700">৳{{ number_format($costPrice, 2) }}</span>
              @if($hasSkus)
                <span class="text-[9px] text-stone-400 block">(Catalog Avg)</span>
              @endif
            </div>
          </div>

          @if($hasSkus)
            <div class="pt-1">
              <button type="button" onclick="toggleVariantGroupMobile({{ $product->id }})" class="w-full py-2 px-3 text-xs font-bold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 transition flex items-center justify-between cursor-pointer border border-stone-200/80">
                <span class="flex items-center gap-1.5">📦 <strong>{{ $product->skus->count() }} Variants Breakdown</strong></span>
                <span id="mobile-arrow-{{ $product->id }}" class="text-[11px] transition-transform">▾</span>
              </button>

              <div id="mobile-variant-group-{{ $product->id }}" class="hidden mt-2.5 space-y-2 p-2.5 bg-stone-50 rounded-xl border border-stone-200/80">
                @foreach($product->skus as $sku)
                  @php
                    $sStock = (int) $sku->stock_quantity;
                    $skuCost = (float) ($sku->cost_price ?: $product->cost_price ?: 0);
                  @endphp
                  <div class="p-2.5 rounded-lg border border-stone-200 bg-white space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="text-xs font-extrabold text-stone-900 block truncate">{{ $sku->attributeLabel() }}</span>
                      <span class="text-[10px] font-mono text-stone-400">{{ $sku->sku }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                      <span class="text-stone-500 text-[11px]">Buying Cost: <strong class="font-mono text-indigo-700">৳{{ number_format($skuCost, 2) }}</strong></span>
                      <span class="text-stone-500 text-[11px]">Stock: <strong class="font-mono text-stone-900">{{ $sStock }}</strong></span>
                    </div>

                    <div class="flex items-center justify-between gap-1.5 pt-1 border-t border-stone-100">
                      <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="flex items-center gap-1">
                        @csrf
                        <input type="hidden" name="sku_id" value="{{ $sku->id }}" />
                        <input type="number" name="stock_quantity" value="{{ $sStock }}" min="0" class="border border-stone-300 rounded-lg text-xs py-1 px-1.5 w-14 text-center font-bold bg-stone-50 focus:bg-white" required />
                        <button type="submit" class="px-2 py-1 text-xs font-bold rounded-lg bg-stone-800 text-white shadow-xs">Save</button>
                      </form>
                      <button type="button" onclick="openAddStockModal({
                          productId: {{ $product->id }},
                          skuId: {{ $sku->id }},
                          title: '{{ addslashes($product->name) }} ({{ addslashes($sku->attributeLabel()) }})',
                          sku: '{{ $sku->sku }}',
                          currentStock: {{ $sStock }},
                          currentCost: {{ $skuCost }}
                        })" class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-emerald-600 text-white shadow-xs">
                        + Add Stock
                      </button>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @else
            <div class="pt-1 flex items-center justify-between gap-2 border-t border-stone-100">
              <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="flex items-center gap-1.5">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}" />
                <span class="text-xs font-bold text-stone-600">Stock:</span>
                <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="0" class="border border-stone-300 rounded-xl text-xs py-1.5 px-2 w-16 text-center font-bold bg-stone-50 focus:bg-white" required />
                <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-stone-900 text-white shadow-xs">Save</button>
              </form>
              <button type="button" onclick="openAddStockModal({
                  productId: {{ $product->id }},
                  skuId: null,
                  title: '{{ addslashes($product->name) }}',
                  sku: '{{ $product->sku ?: "No SKU" }}',
                  currentStock: {{ $totalStock }},
                  currentCost: {{ $costPrice }}
                })" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-600 text-white shadow-xs">
                + Add Stock
              </button>
            </div>
          @endif
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400">No inventory products found matching criteria.</div>
      @endforelse
    </div>

    <div class="p-4 border-t border-stone-200 bg-stone-50/50">{{ $products->links() }}</div>
  </div>
</div>

<!-- Add Stock & Weighted Average Buying Cost Modal -->
<div id="addStockModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
  <div class="relative w-full max-w-lg bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-stone-200 overflow-hidden transform transition-all">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 sm:px-6 py-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-stone-50 border-b border-stone-200">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-xs">
          📦
        </div>
        <div>
          <h3 class="text-base font-extrabold text-stone-900">Add Stock &amp; Buying Cost</h3>
          <p class="text-[11px] text-stone-500">Calculates Moving Weighted Average Cost automatically</p>
        </div>
      </div>
      <button type="button" onclick="closeAddStockModal()" class="w-8 h-8 rounded-full bg-stone-200/60 hover:bg-stone-200 text-stone-600 flex items-center justify-center text-sm font-bold transition-colors cursor-pointer">
        ✕
      </button>
    </div>

    <form method="POST" action="{{ route('admin.inventory.add-stock') }}" class="p-5 sm:p-6 space-y-4">
      @csrf
      <input type="hidden" name="product_id" id="modalProductId" value="" />
      <input type="hidden" name="sku_id" id="modalSkuId" value="" />

      <!-- Target Item Card -->
      <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80 space-y-1.5">
        <div class="text-xs font-extrabold text-stone-900 truncate" id="modalItemTitle">—</div>
        <div class="flex items-center gap-2 text-[11px] text-stone-500 font-mono">
          <span>SKU: <strong class="text-stone-700" id="modalItemSku">—</strong></span>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-stone-200/60 text-xs">
          <div class="bg-white p-2 rounded-lg border border-stone-200/60">
            <span class="text-[10px] text-stone-400 uppercase font-bold block">Current Stock</span>
            <span class="font-black text-stone-900 font-mono text-sm" id="modalCurrentStockText">0 units</span>
          </div>
          <div class="bg-white p-2 rounded-lg border border-stone-200/60">
            <span class="text-[10px] text-stone-400 uppercase font-bold block">Current Buying Cost</span>
            <span class="font-black text-indigo-700 font-mono text-sm" id="modalCurrentCostText">৳0.00</span>
          </div>
        </div>
      </div>

      <!-- Inputs Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="text-xs font-bold text-stone-800 block mb-1">
            Added Quantity (Units) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <input type="number" name="added_quantity" id="modalAddedQtyInput" min="1" step="1" value="10" class="w-full pl-3.5 pr-10 py-2.5 text-sm font-black text-stone-900 bg-stone-50 border border-stone-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono" required />
            <span class="absolute right-3 top-2.5 text-xs font-bold text-stone-400">PCS</span>
          </div>
          <span class="text-[10px] text-stone-400 mt-0.5 block">Quantity received in this new shipment</span>
        </div>

        <div>
          <label class="text-xs font-bold text-stone-800 block mb-1">
            Buying Cost per Product (৳) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-xs font-bold text-stone-400">৳</span>
            <input type="number" name="unit_cost" id="modalUnitCostInput" min="0" step="0.01" value="0.00" class="w-full pl-7 pr-3 py-2.5 text-sm font-black text-indigo-700 bg-indigo-50/40 border border-indigo-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono" required />
          </div>
          <span class="text-[10px] text-stone-400 mt-0.5 block">Purchase cost per unit for this batch</span>
        </div>
      </div>

      <!-- Live Calculation Breakdown Card -->
      <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-indigo-500/10 border border-emerald-200/90 space-y-2">
        <div class="flex items-center justify-between text-xs font-extrabold text-stone-800">
          <span class="flex items-center gap-1.5">
            <span>⚡</span> Live Weighted Average Preview
          </span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">AVCO Formula</span>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
          <div>
            <span class="text-[10px] text-stone-500 font-semibold block">New Total Stock:</span>
            <span class="font-black text-stone-900 font-mono text-base" id="previewNewStock">0 units</span>
          </div>
          <div>
            <span class="text-[10px] text-stone-500 font-semibold block">New Average Buying Cost:</span>
            <span class="font-black text-emerald-800 font-mono text-base" id="previewNewAvgCost">৳0.00</span>
          </div>
        </div>

        <div class="text-[11px] text-stone-600 bg-white/80 p-2.5 rounded-lg border border-emerald-100/80 font-mono leading-relaxed break-words" id="previewFormulaText">
          Formula: ((Current Stock × Current Cost) + (Added Qty × New Cost)) ÷ Total Stock
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-stone-100">
        <button type="button" onclick="closeAddStockModal()" class="px-4 py-2 text-xs font-bold rounded-xl text-stone-600 hover:bg-stone-100 transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 text-xs font-extrabold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
          <span>✓ Add Stock &amp; Update Average Cost</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let currentModalData = {
  productId: null,
  skuId: null,
  currentStock: 0,
  currentCost: 0
};

function openAddStockModal(data) {
  currentModalData = {
    productId: data.productId,
    skuId: data.skuId,
    currentStock: Math.max(0, parseInt(data.currentStock) || 0),
    currentCost: Math.max(0, parseFloat(data.currentCost) || 0)
  };

  document.getElementById('modalProductId').value = data.productId || '';
  document.getElementById('modalSkuId').value = data.skuId || '';
  document.getElementById('modalItemTitle').innerText = data.title;
  document.getElementById('modalItemSku').innerText = data.sku || 'N/A';
  document.getElementById('modalCurrentStockText').innerText = currentModalData.currentStock + ' units';
  document.getElementById('modalCurrentCostText').innerText = '৳' + currentModalData.currentCost.toFixed(2);
  
  document.getElementById('modalAddedQtyInput').value = 10;
  document.getElementById('modalUnitCostInput').value = currentModalData.currentCost > 0 ? currentModalData.currentCost.toFixed(2) : '0.00';

  updateModalPreview();

  const modal = document.getElementById('addStockModal');
  modal.classList.remove('hidden');
  document.getElementById('modalAddedQtyInput').focus();
}

function closeAddStockModal() {
  const modal = document.getElementById('addStockModal');
  modal.classList.add('hidden');
}

function updateModalPreview() {
  const addedQty = Math.max(1, parseInt(document.getElementById('modalAddedQtyInput').value) || 0);
  const newBatchCost = Math.max(0, parseFloat(document.getElementById('modalUnitCostInput').value) || 0);
  
  const curStock = currentModalData.currentStock;
  const curCost = currentModalData.currentCost;
  const newStock = curStock + addedQty;
  
  let newAvgCost = 0;
  if (curStock > 0 && curCost > 0) {
    newAvgCost = ((curStock * curCost) + (addedQty * newBatchCost)) / newStock;
  } else {
    newAvgCost = newBatchCost;
  }

  document.getElementById('previewNewStock').innerText = newStock + ' units (+' + addedQty + ')';
  document.getElementById('previewNewAvgCost').innerText = '৳' + newAvgCost.toFixed(2);

  if (curStock > 0 && curCost > 0) {
    document.getElementById('previewFormulaText').innerText = 
      '((' + curStock + ' × ৳' + curCost.toFixed(2) + ') + (' + addedQty + ' × ৳' + newBatchCost.toFixed(2) + ')) ÷ ' + newStock + ' = ৳' + newAvgCost.toFixed(2);
  } else {
    document.getElementById('previewFormulaText').innerText = 
      'Current stock or cost is 0. New average buying cost will be batch cost: ৳' + newBatchCost.toFixed(2);
  }
}

document.getElementById('modalAddedQtyInput')?.addEventListener('input', updateModalPreview);
document.getElementById('modalUnitCostInput')?.addEventListener('input', updateModalPreview);

document.getElementById('addStockModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeAddStockModal();
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeAddStockModal();
});

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

function toggleVariantGroupMobile(productId) {
  const row = document.getElementById('mobile-variant-group-' + productId);
  const arrow = document.getElementById('mobile-arrow-' + productId);

  if (row) {
    if (row.classList.contains('hidden')) {
      row.classList.remove('hidden');
      if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
      row.classList.add('hidden');
      if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
  }
}
</script>
@endsection
