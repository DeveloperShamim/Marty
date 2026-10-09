{{-- Ad spend (money roles) and Store performance (order roles): beside Monthly Revenue, or above Recent orders when there's no revenue chart --}}
@php
  $perfParts = [
    ['Delivered', $deliveredCount ?? 0, 'var(--brand)'],
    ['In progress', $ordersCount ?? 0, 'color-mix(in srgb, var(--brand) 45%, #fff)'],
    ['Returned', $returnedOrdersCount ?? 0, 'var(--brand-dark)'],
    ['Cancelled', $cancelledOrdersCount ?? 0, 'var(--brand-border)'],
  ];
  $perfTotal = max(1, collect($perfParts)->sum(1));
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  @if($canMoney)
  {{-- Ad spend this month: Marketing & Facebook Ads expenses --}}
  <div class="panel p-4 min-w-0 flex flex-col {{ $canOrders ? '' : 'sm:col-span-2' }}" data-ad-spend>
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <h2 class="text-[15px] font-semibold text-gray-900">Ad spend</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ date('F') }}, from Marketing expenses</p>
      </div>
      <a href="{{ route('admin.expenses.index') }}" aria-label="Expenses" class="h-9 w-9 shrink-0 rounded-full bg-white text-gray-800 grid place-items-center shadow-sm hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
      </a>
    </div>
    <p class="mt-3 text-[28px] leading-none font-semibold tracking-tight text-gray-900 tabular-nums">{{ money($adSpend) }}</p>
    <p class="mt-1 text-[11px] text-gray-500">{{ $adSpend > 0 ? 'Against ' . money($thisMonthRevenue) . ' of sales' : 'Log ad spend under Expenses' }}</p>
    <div class="mt-auto pt-3 grid grid-cols-2 gap-2">
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Cost per order</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ $adSpend > 0 && $costPerOrder !== null ? money($costPerOrder) : '—' }}</span></div>
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Paid orders</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($thisMonthOrders) }}</span></div>
    </div>
  </div>
  @endif

  @if($canOrders)
  {{-- Store performance: order success rate with the order mix --}}
  <div class="panel p-4 min-w-0 flex flex-col {{ $canMoney ? '' : 'sm:col-span-2' }}">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <h2 class="text-[15px] font-semibold text-gray-900">Store performance</h2>
        <p class="text-xs text-gray-500 mt-0.5">Order success rate</p>
      </div>
      <a href="{{ route('admin.courier-scan.index') }}" aria-label="Courier" class="h-9 w-9 shrink-0 rounded-full bg-white text-gray-800 grid place-items-center shadow-sm hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
      </a>
    </div>
    <p class="mt-3 text-[28px] leading-none font-semibold tracking-tight text-gray-900 tabular-nums" title="{{ number_format($deliveredCount ?? 0) }} delivered, {{ number_format($returnedOrdersCount ?? 0) }} returned">{{ $successRate }}%</p>
    <p class="mt-1 text-[11px] text-gray-500">{{ number_format($deliveredCount ?? 0) }} delivered &middot; {{ number_format($returnedOrdersCount ?? 0) }} returned</p>
    <div class="mt-auto pt-3 grid grid-cols-4 gap-2">
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Orders</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($allOrdersCount) }}</span></div>
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Items sold</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($itemsSold) }}</span></div>
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Customers</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($customersCount) }}</span></div>
      <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Returned</span><span class="block text-sm font-semibold tabular-nums" style="color: var(--brand-dark);">{{ number_format($returnedOrdersCount ?? 0) }}</span></div>
    </div>
    <div class="mt-2.5 flex h-2.5 gap-1">
      @foreach($perfParts as [$label, $count, $color])
        @if($count > 0)
          <span class="rounded-full" style="flex: {{ $count }} 1 0; background: {{ $color }};" title="{{ $label }}: {{ number_format($count) }} ({{ round($count / $perfTotal * 100) }}%)"></span>
        @endif
      @endforeach
    </div>
  </div>
  @endif
</div>
