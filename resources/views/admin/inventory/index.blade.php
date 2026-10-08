@extends('layouts.admin')
@section('title', 'Inventory')
@section('subtitle', 'Stock levels for every product and variant, with restocks at buying cost.')

@php
  $stockPill = function ($isOut, $isLow) {
    if ($isOut) return ['bg-rose-50 text-rose-700', 'Out of stock'];
    if ($isLow) return ['bg-amber-50 text-amber-700', 'Low stock'];
    return ['bg-emerald-50 text-emerald-700', 'In stock'];
  };
@endphp

@section('content')
<div class="space-y-4">
  {{-- Stock summary --}}
  <div class="grid grid-cols-3 gap-2 sm:gap-3">
    <a href="{{ route('admin.inventory.index', ['filter' => 'all', 'q' => $q]) }}" class="panel p-3 sm:p-4 hover:bg-gray-50 transition-colors">
      <div class="flex items-center gap-2.5">
        <span class="hidden sm:grid h-8 w-8 place-items-center rounded-xl bg-gray-100 text-gray-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
        </span>
        <p class="text-xs text-gray-500 truncate">Total SKUs</p>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($totalSkusCount) }}</p>
      <p class="hidden sm:block text-[11px] text-gray-400 mt-0.5">Tracked across the catalog</p>
    </a>
    <a href="{{ route('admin.inventory.index', ['filter' => 'low_stock', 'q' => $q]) }}" class="panel p-3 sm:p-4 hover:bg-gray-50 transition-colors">
      <div class="flex items-center gap-2.5">
        <span class="hidden sm:grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/></svg>
        </span>
        <p class="text-xs text-gray-500 truncate">Low stock</p>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold tabular-nums {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-gray-900' }}">{{ number_format($lowStockCount) }}</p>
      <p class="hidden sm:block text-[11px] text-gray-400 mt-0.5">Needs a reorder soon</p>
    </a>
    <a href="{{ route('admin.inventory.index', ['filter' => 'out_of_stock', 'q' => $q]) }}" class="panel p-3 sm:p-4 hover:bg-gray-50 transition-colors">
      <div class="flex items-center gap-2.5">
        <span class="hidden sm:grid h-8 w-8 place-items-center rounded-xl bg-rose-50 text-rose-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m5.7 5.7 12.6 12.6"/></svg>
        </span>
        <p class="text-xs text-gray-500 truncate">Out of stock</p>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold tabular-nums {{ $outOfStockCount > 0 ? 'text-rose-700' : 'text-gray-900' }}">{{ number_format($outOfStockCount) }}</p>
      <p class="hidden sm:block text-[11px] text-gray-400 mt-0.5">Hidden from buyers</p>
    </a>
  </div>

  {{-- Filter tabs --}}
  @php
    $tabs = [
      'all'          => ['All items', 0],
      'low_stock'    => ['Low stock', $lowStockCount],
      'out_of_stock' => ['Out of stock', $outOfStockCount],
    ];
  @endphp
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Stock filter">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($tabs as $key => [$label, $count])
        @php $active = $filter === $key; @endphp
        <a href="{{ route('admin.inventory.index', ['filter' => $key, 'q' => $q]) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
          @if($count > 0)
            <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $count }}</span>
          @endif
        </a>
      @endforeach
    </div>
  </nav>

  {{-- Product list --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.inventory.index') }}" class="p-3 sm:p-4 flex items-center gap-2">
      <input type="hidden" name="filter" value="{{ $filter }}" />
      <label class="relative flex-1 min-w-0">
        <span class="sr-only">Search inventory</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="Search products or SKU"
               class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
      </label>
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
      @if($q)
        <a href="{{ route('admin.inventory.index', ['filter' => $filter]) }}" class="h-10 px-3 rounded-full text-[13px] font-medium text-gray-600 hover:bg-gray-100 inline-flex items-center shrink-0">Clear</a>
      @endif
    </form>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Buying cost</th>
            <th class="py-3 px-4">Variants</th>
            <th class="py-3 px-4 text-right">Stock</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
          @forelse($products as $product)
            @php
              $hasSkus = $product->skus->isNotEmpty();
              $totalStock = (int) $product->stock_quantity;
              $isLow = $product->isLowStock(3);
              $isOut = $totalStock <= 0;
              $costPrice = (float) ($product->cost_price ?: 0);
              [$pillClass, $pillLabel] = $stockPill($isOut, $isLow);
            @endphp
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover bg-gray-100 rounded-xl shrink-0" alt="">
                  <div class="min-w-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-gray-900 hover:underline truncate block max-w-[16rem]">{{ $product->name }}</a>
                    <span class="text-gray-400 text-[11px] font-mono">{{ $product->sku ?: 'No SKU' }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="font-medium text-gray-900 tabular-nums">৳{{ number_format($costPrice, 2) }}</span>
                @if($hasSkus)
                  <span class="text-[11px] text-gray-400 block">Average</span>
                @endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                @if($hasSkus)
                  <button type="button" onclick="toggleVariantGroup({{ $product->id }})" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer" title="Show variants">
                    <span>{{ $product->skus->count() }} {{ Str::plural('variant', $product->skus->count()) }}</span>
                    <svg id="arrow-{{ $product->id }}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                  </button>
                @else
                  <span class="text-xs text-gray-400">Single item</span>
                @endif
              </td>
              <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums">{{ number_format($totalStock) }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $pillClass }}">{{ $pillLabel }}</span>
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                @if($hasSkus)
                  <button type="button" onclick="toggleVariantGroup({{ $product->id }})" class="h-8 px-3 text-xs font-medium rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 inline-flex items-center gap-1 cursor-pointer">
                    <span>Manage stock</span>
                    <svg id="btn-arrow-{{ $product->id }}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                  </button>
                @else
                  <div class="inline-flex items-center gap-1.5 justify-end">
                    <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-1">
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $product->id }}" />
                      <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="0" class="h-8 w-16 rounded-full border border-gray-200 bg-white px-2 text-center text-xs font-semibold tabular-nums focus:outline-none focus:border-gray-400" title="Direct stock count" aria-label="Stock for {{ $product->name }}" required />
                      <button type="submit" class="h-8 px-3 text-xs font-medium rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 cursor-pointer" title="Save stock directly">Save</button>
                    </form>
                    <button type="button" onclick="openAddStockModal({
                        productId: {{ $product->id }},
                        skuId: null,
                        title: '{{ addslashes($product->name) }}',
                        sku: '{{ $product->sku ?: "No SKU" }}',
                        currentStock: {{ $totalStock }},
                        currentCost: {{ $costPrice }}
                      })" class="h-8 px-3 text-xs font-semibold rounded-full text-white inline-flex items-center gap-1 cursor-pointer" style="background: var(--brand-dark);" title="Add restock with purchase cost">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                      <span>Restock</span>
                    </button>
                  </div>
                @endif
              </td>
            </tr>

            @if($hasSkus)
              <tr id="variant-group-{{ $product->id }}" class="hidden bg-gray-50/70">
                <td colspan="7" class="px-4 py-3">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-700">Variants of {{ $product->name }}</span>
                    <span class="text-[11px] text-gray-400">{{ $product->skus->count() }} {{ Str::plural('variant', $product->skus->count()) }}</span>
                  </div>
                  <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-2">
                    @foreach($product->skus as $sku)
                      @php
                        $sStock = (int) $sku->stock_quantity;
                        $skuCost = (float) ($sku->cost_price ?: $product->cost_price ?: 0);
                      @endphp
                      <div class="rounded-xl bg-white p-3 flex flex-col gap-2.5 shadow-sm">
                        <div class="flex items-start justify-between gap-2 min-w-0">
                          <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $sku->attributeLabel() }}</p>
                            <p class="text-[11px] font-mono text-gray-400 truncate">{{ $sku->sku }}</p>
                          </div>
                          <span class="text-[11px] text-gray-500 whitespace-nowrap">Cost <span class="font-medium text-gray-800 tabular-nums">৳{{ number_format($skuCost, 2) }}</span></span>
                        </div>
                        <div class="flex items-center justify-between gap-1.5">
                          <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="inline-flex items-center gap-1">
                            @csrf
                            <input type="hidden" name="sku_id" value="{{ $sku->id }}" />
                            <input type="number" name="stock_quantity" value="{{ $sStock }}" min="0" class="h-8 w-16 rounded-full border border-gray-200 bg-white px-2 text-center text-xs font-semibold tabular-nums focus:outline-none focus:border-gray-400" title="Direct stock count" aria-label="Stock for {{ $sku->attributeLabel() }}" required />
                            <button type="submit" class="h-8 px-3 text-xs font-medium rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 cursor-pointer" title="Save stock directly">Save</button>
                          </form>
                          <button type="button" onclick="openAddStockModal({
                              productId: {{ $product->id }},
                              skuId: {{ $sku->id }},
                              title: '{{ addslashes($product->name) }} ({{ addslashes($sku->attributeLabel()) }})',
                              sku: '{{ $sku->sku }}',
                              currentStock: {{ $sStock }},
                              currentCost: {{ $skuCost }}
                            })" class="h-8 px-3 text-xs font-semibold rounded-full text-white inline-flex items-center gap-1 cursor-pointer" style="background: var(--brand-dark);" title="Add restock with purchase cost">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            <span>Restock</span>
                          </button>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center text-gray-500 text-sm">No products match these filters.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: one card per product --}}
    <div class="md:hidden px-3 pb-3 space-y-2.5">
      @forelse($products as $product)
        @php
          $hasSkus = $product->skus->isNotEmpty();
          $totalStock = (int) $product->stock_quantity;
          $isLow = $product->isLowStock(3);
          $isOut = $totalStock <= 0;
          $costPrice = (float) ($product->cost_price ?: 0);
          [$pillClass, $pillLabel] = $stockPill($isOut, $isLow);
        @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-3">
          <div class="flex items-start gap-3">
            <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover bg-gray-100 rounded-xl shrink-0" alt="">
            <div class="min-w-0 flex-1">
              <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-sm text-gray-900 hover:underline truncate block">{{ $product->name }}</a>
              <p class="text-[11px] text-gray-500 mt-0.5 truncate">
                <span class="font-mono">{{ $product->sku ?: 'No SKU' }}</span> · {{ $product->category?->name ?? 'Uncategorized' }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-[15px] font-semibold text-gray-900 tabular-nums">{{ number_format($totalStock) }}</p>
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $pillClass }}">{{ $pillLabel }}</span>
            </div>
          </div>

          <p class="text-xs text-gray-500">Buying cost <span class="font-medium text-gray-900 tabular-nums">৳{{ number_format($costPrice, 2) }}</span>@if($hasSkus) <span class="text-gray-400">(average)</span>@endif</p>

          @if($hasSkus)
            <div>
              <button type="button" onclick="toggleVariantGroupMobile({{ $product->id }})" class="w-full h-9 px-3.5 text-xs font-medium rounded-full bg-white hover:bg-gray-100 ring-1 ring-gray-200 text-gray-800 flex items-center justify-between cursor-pointer">
                <span>{{ $product->skus->count() }} {{ Str::plural('variant', $product->skus->count()) }}</span>
                <svg id="mobile-arrow-{{ $product->id }}" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
              </button>

              <div id="mobile-variant-group-{{ $product->id }}" class="hidden mt-2 space-y-2">
                @foreach($product->skus as $sku)
                  @php
                    $sStock = (int) $sku->stock_quantity;
                    $skuCost = (float) ($sku->cost_price ?: $product->cost_price ?: 0);
                  @endphp
                  <div class="rounded-xl bg-white p-2.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                      <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $sku->attributeLabel() }}</p>
                        <p class="text-[11px] font-mono text-gray-400 truncate">{{ $sku->sku }}</p>
                      </div>
                      <div class="text-right text-[11px] text-gray-500 shrink-0">
                        <p>Stock <span class="font-semibold text-gray-900 tabular-nums">{{ $sStock }}</span></p>
                        <p>Cost <span class="font-medium text-gray-800 tabular-nums">৳{{ number_format($skuCost, 2) }}</span></p>
                      </div>
                    </div>
                    <div class="flex items-center justify-between gap-1.5">
                      <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="flex items-center gap-1">
                        @csrf
                        <input type="hidden" name="sku_id" value="{{ $sku->id }}" />
                        <input type="number" name="stock_quantity" value="{{ $sStock }}" min="0" class="h-8 w-16 rounded-full border border-gray-200 bg-white px-2 text-center text-xs font-semibold tabular-nums" aria-label="Stock for {{ $sku->attributeLabel() }}" required />
                        <button type="submit" class="h-8 px-3 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Save</button>
                      </form>
                      <button type="button" onclick="openAddStockModal({
                          productId: {{ $product->id }},
                          skuId: {{ $sku->id }},
                          title: '{{ addslashes($product->name) }} ({{ addslashes($sku->attributeLabel()) }})',
                          sku: '{{ $sku->sku }}',
                          currentStock: {{ $sStock }},
                          currentCost: {{ $skuCost }}
                        })" class="h-8 px-3 text-xs font-semibold rounded-full text-white inline-flex items-center gap-1" style="background: var(--brand-dark);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        Restock
                      </button>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @else
            <div class="flex items-center justify-between gap-2">
              <form method="POST" action="{{ route('admin.inventory.update-stock') }}" class="flex items-center gap-1">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}" />
                <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="0" class="h-8 w-16 rounded-full border border-gray-200 bg-white px-2 text-center text-xs font-semibold tabular-nums" aria-label="Stock for {{ $product->name }}" required />
                <button type="submit" class="h-8 px-3 text-xs font-medium rounded-full bg-white ring-1 ring-gray-200 text-gray-800">Save</button>
              </form>
              <button type="button" onclick="openAddStockModal({
                  productId: {{ $product->id }},
                  skuId: null,
                  title: '{{ addslashes($product->name) }}',
                  sku: '{{ $product->sku ?: "No SKU" }}',
                  currentStock: {{ $totalStock }},
                  currentCost: {{ $costPrice }}
                })" class="h-8 px-3 text-xs font-semibold rounded-full text-white inline-flex items-center gap-1" style="background: var(--brand-dark);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Restock
              </button>
            </div>
          @endif
        </article>
      @empty
        <div class="text-center py-12 text-gray-500 text-sm">No products match these filters.</div>
      @endforelse
    </div>

    @if($products->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $products->links() }}</div>
    @endif
  </div>
</div>

{{-- Add stock at buying cost (weighted average) --}}
<div id="addStockModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 flex items-end sm:items-center justify-center p-3 sm:p-4">
  <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
    <div class="flex items-start justify-between gap-3 px-5 pt-5">
      <div class="min-w-0">
        <h2 class="text-[15px] font-semibold text-gray-900">Add stock</h2>
        <p class="text-xs text-gray-500 mt-0.5">The average buying cost is updated automatically.</p>
      </div>
      <button type="button" onclick="closeAddStockModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 grid place-items-center shrink-0 cursor-pointer" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.inventory.add-stock') }}" class="px-5 pb-5 pt-4 space-y-4">
      @csrf
      <input type="hidden" name="product_id" id="modalProductId" value="" />
      <input type="hidden" name="sku_id" id="modalSkuId" value="" />

      <div class="rounded-xl bg-gray-50 p-3 space-y-2">
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-gray-900 truncate" id="modalItemTitle">—</p>
          <p class="text-[11px] text-gray-500">SKU <span class="font-mono text-gray-700" id="modalItemSku">—</span></p>
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <div>
            <span class="text-gray-500 block">Current stock</span>
            <span class="font-semibold text-gray-900 tabular-nums" id="modalCurrentStockText">0 units</span>
          </div>
          <div>
            <span class="text-gray-500 block">Current buying cost</span>
            <span class="font-semibold text-gray-900 tabular-nums" id="modalCurrentCostText">৳0.00</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label for="modalAddedQtyInput" class="lbl">Quantity received</label>
          <div class="relative">
            <input type="number" name="added_quantity" id="modalAddedQtyInput" min="1" step="1" value="10" class="inp pr-10 tabular-nums" required />
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-gray-400 pointer-events-none">pcs</span>
          </div>
        </div>
        <div>
          <label for="modalUnitCostInput" class="lbl">Cost per unit</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">৳</span>
            <input type="number" name="unit_cost" id="modalUnitCostInput" min="0" step="0.01" value="0.00" class="inp pl-7 tabular-nums" required />
          </div>
        </div>
      </div>

      <div class="rounded-xl bg-emerald-50/70 p-3 space-y-2">
        <p class="text-xs font-medium text-emerald-800">After this restock</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <div>
            <span class="text-gray-500 block">New stock</span>
            <span class="font-semibold text-gray-900 tabular-nums" id="previewNewStock">0 units</span>
          </div>
          <div>
            <span class="text-gray-500 block">New average cost</span>
            <span class="font-semibold text-emerald-800 tabular-nums" id="previewNewAvgCost">৳0.00</span>
          </div>
        </div>
        <p class="text-[11px] text-gray-500 font-mono leading-relaxed break-words" id="previewFormulaText">
          Formula: ((Current Stock × Current Cost) + (Added Qty × New Cost)) ÷ Total Stock
        </p>
      </div>

      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
        <button type="button" onclick="closeAddStockModal()" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium cursor-pointer">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer" style="background: var(--brand-dark);">Add stock</button>
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
