@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="dash-glass space-y-4 max-w-full">

  @php
    // Inventory managers see the dashboard without money or order details (App\Support\StaffAccess).
    $canMoney = \App\Support\StaffAccess::allows(auth()->user(), 'analytics');
    $canOrders = \App\Support\StaffAccess::allows(auth()->user(), 'orders');
  @endphp
  @php
    $trend = fn ($now, $before) => $before > 0 ? round(($now - $before) / $before * 100, 1) : null;
    $salesTrend = $trend($todayRevenue ?? 0, $yesterdayRevenue ?? 0);
    $ordersTrend = $trend($todayOrdersCount ?? 0, $yesterdayOrdersCount ?? 0);

    // Order status rings: each status as a share of all orders
    $statusRings = [
      ['Active', $ordersCount ?? 0, 'var(--brand)'],
      ['Shipped', $shippedCount ?? 0, 'var(--brand-dark)'],
      ['Delivered', $deliveredCount ?? 0, '#10b981'],
      ['To review', $pendingCount ?? 0, '#f59e0b'],
      ['Returned', $returnedOrdersCount ?? 0, '#a8a29e'],
      ['Cancelled', $cancelledOrdersCount ?? 0, '#d6d3d1'],
    ];
    $ringTotal = max(1, ($ordersCount ?? 0) + ($deliveredCount ?? 0) + ($returnedOrdersCount ?? 0) + ($cancelledOrdersCount ?? 0));

    // Order success rate: delivered vs returned
    $finished = ($deliveredCount ?? 0) + ($returnedOrdersCount ?? 0);
    $successRate = $finished > 0 ? round(($deliveredCount ?? 0) / $finished * 100, 1) : 0;
  @endphp

  @section('subtitle', 'Welcome back, ' . (auth()->user()->name ?? 'Admin') . '. Here is how the store is doing today.')
  @section('page-actions')
    <a href="{{ route('home') }}" target="_blank" class="pill-btn">
      View store
      <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9"/></svg></span>
    </a>
    @if($canMoney)
      <a href="{{ route('admin.analytics.export', ['range' => 'this_month']) }}" class="pill-btn">
        Export
        <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/></svg></span>
      </a>
    @endif
    @if($canOrders)
      <a href="{{ route('admin.orders.index') }}" class="pill-btn pill-btn-dark">
        <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg></span>
        Orders
      </a>
    @endif
  @endsection

  @php
    $canCarts = \App\Support\StaffAccess::allows(auth()->user(), 'abandoned-carts');
    $returnRate = $finishedRecent > 0 ? round($returnedRecent / $finishedRecent * 100, 1) : 0;
    $costPerOrder = $thisMonthOrders > 0 ? $adSpend / $thisMonthOrders : null;
    $roas = $adSpend > 0 ? $thisMonthRevenue / $adSpend : null;
    $waNumber = fn ($phone) => ($n = \App\Services\Courier\BdCourierService::normalizePhone($phone)) ? '88' . $n : null;
    $phoneIcon = '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/>';

    // Jobs waiting on the admin, shown as counts; a list opens below only for the ones that aren't zero
    $actions = collect([
      $canOrders ? ['key' => 'payments', 'label' => 'Payments to verify', 'count' => $pendingCount ?? 0, 'href' => route('admin.orders.index', ['status' => 'awaiting_payment']),
        'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'] : null,
      $canOrders ? ['key' => 'risky', 'label' => 'Risky orders to call', 'count' => $riskyCount, 'href' => route('admin.orders.index'),
        'icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/>'] : null,
      $canCarts ? ['key' => 'carts', 'label' => 'Carts to call back', 'count' => $callbackCount, 'href' => route('admin.abandoned-carts.index'),
        'icon' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 7H6"/>'] : null,
      ['key' => 'stock', 'label' => 'Low stock', 'count' => $lowStockCount, 'href' => route('admin.inventory.index'),
        'icon' => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>'],
    ])->filter()->values();
    $waiting = $actions->where('count', '>', 0);
    // Lists that open under the counts; a single list spreads its rows across the width instead
    $openLists = collect([
      'payments' => $canOrders && ($pendingCount ?? 0) > 0,
      'risky' => $canOrders && $riskyCount > 0,
      'carts' => $canCarts && $callbackCount > 0,
      'stock' => $lowStockCount > 0 && $lowStockProducts->isNotEmpty(),
    ])->filter();
    $listCols = ['', '', 'md:grid-cols-2', 'md:grid-cols-2 xl:grid-cols-3', 'md:grid-cols-2 xl:grid-cols-4'][$openLists->count()];
    $rowCols = $openLists->count() === 1 ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-6 divide-y md:divide-y-0' : 'divide-y';
    $monthlyAvg = $totalSeriesRevenue > 0 ? ($totalSeriesRevenue / 12) : 0;
  @endphp

  {{-- Needs action: everything waiting on you, at a glance --}}
  @php
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('')) ?: '?';
    $waitingTotal = $waiting->sum('count');
  @endphp
  <section class="panel p-4 sm:p-5">
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0">
        <h2 class="text-[15px] sm:text-base font-semibold text-gray-900">Needs action</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ $waiting->isEmpty() ? 'All clear. Nothing is waiting on you.' : 'Jobs waiting on you right now' }}</p>
      </div>
      @if($waitingTotal > 0)
        <span class="shrink-0 inline-flex items-center gap-1.5 h-7 px-3 rounded-full text-xs font-semibold text-white" style="background: var(--brand-dark);">
          <span class="h-1.5 w-1.5 rounded-full" style="background: var(--brand-border);"></span>{{ number_format($waitingTotal) }} waiting
        </span>
      @else
        <span class="shrink-0 grid h-8 w-8 place-items-center rounded-full" style="background: var(--brand-soft); color: var(--brand);" aria-hidden="true">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
        </span>
      @endif
    </div>
    <div class="mt-3.5 grid grid-cols-2 {{ $actions->count() >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-' . $actions->count() }} gap-2 sm:gap-3">
      @foreach($actions as $a)
        @php $on = $a['count'] > 0; @endphp
        <a href="{{ $on && $a['key'] !== 'stock' ? '#act-' . $a['key'] : $a['href'] }}" class="na-tile {{ $on ? 'is-on' : '' }} group flex items-center gap-3 rounded-2xl p-3 sm:p-3.5 min-w-0">
          <span class="na-ico hidden sm:grid h-10 w-10 shrink-0 place-items-center rounded-full">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $a['icon'] !!}</svg>
          </span>
          <span class="min-w-0 flex-1">
            <span class="block text-xl leading-none font-semibold tabular-nums {{ $on ? 'text-gray-900' : 'text-gray-400' }}">{{ number_format($a['count']) }}</span>
            <span class="block mt-1 text-[11px] sm:text-xs leading-tight {{ $on ? 'text-gray-600' : 'text-gray-400' }}">{{ $a['label'] }}</span>
          </span>
          @if($on)
            <svg class="na-go hidden sm:block w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
          @endif
        </a>
      @endforeach
    </div>

    @if($openLists->isNotEmpty())
    <div class="mt-3 grid grid-cols-1 {{ $listCols }} gap-3">
      @if($canOrders && ($pendingCount ?? 0) > 0)
        <div id="act-payments" class="na-list rounded-2xl p-3 sm:p-3.5 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-[13px] font-semibold text-gray-900">Payment approvals</h3>
            <a href="{{ route('admin.orders.index', ['status' => 'awaiting_payment']) }}" class="text-[11px] font-semibold hover:underline" style="color: var(--brand);">View all &rarr;</a>
          </div>
          <div class="mt-2 {{ $rowCols }} divide-gray-100">
            @foreach($pendingOrders->take(4) as $order)
              <div class="flex items-center gap-2.5 py-2">
                <span class="na-avatar grid h-8 w-8 shrink-0 place-items-center rounded-full text-[11px] font-semibold">{{ $initials($order->customer_name) }}</span>
                <a href="{{ route('admin.orders.show', $order) }}" class="min-w-0 flex-1 group">
                  <span class="block text-[13px] font-semibold text-gray-900 truncate group-hover:underline">{{ $order->customer_name }}</span>
                  <span class="block text-[11px] text-gray-500 truncate">{{ $order->order_number }} &middot; {{ money($order->total) }} &middot; {{ $order->paymentMethodLabel() }}</span>
                </a>
                <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="shrink-0">
                  @csrf
                  <button type="submit" class="na-btn h-8 px-3.5 rounded-full text-[11px] font-semibold text-white">Verify</button>
                </form>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if($canOrders && $riskyCount > 0)
        <div id="act-risky" class="na-list rounded-2xl p-3 sm:p-3.5 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-[13px] font-semibold text-gray-900">Risky orders to call</h3>
            <span class="text-[11px] text-gray-500">Fraud check or courier history</span>
          </div>
          <div class="mt-2 {{ $rowCols }} divide-gray-100">
            @foreach($riskyOrders->take(4) as $o)
              <div class="flex items-center gap-2.5 py-2">
                <span class="na-avatar relative grid h-8 w-8 shrink-0 place-items-center rounded-full text-[11px] font-semibold" title="{{ $o->risk >= 2 ? 'High risk' : 'Medium risk' }}">{{ $initials($o->customer_name) }}<span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-white {{ $o->risk >= 2 ? 'bg-rose-500' : 'bg-amber-400' }}"></span></span>
                <a href="{{ route('admin.orders.show', $o) }}" class="min-w-0 flex-1 group">
                  <span class="block text-[13px] font-semibold text-gray-900 truncate group-hover:underline">{{ $o->customer_name }} <span class="font-normal text-[11px] text-gray-400">{{ $o->order_number }} &middot; {{ money($o->total) }}</span></span>
                  <span class="block text-[11px] {{ $o->risk >= 2 ? 'text-rose-600' : 'text-amber-700' }} truncate">{{ $o->riskReason }}</span>
                </a>
                <a href="tel:{{ $o->customer_phone }}" class="shrink-0 grid h-8 w-8 place-items-center rounded-full text-white" style="background: var(--brand-dark);" aria-label="Call {{ $o->customer_name }}" title="Call {{ $o->customer_phone }}">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $phoneIcon !!}</svg>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if($canCarts && $callbackCount > 0)
        <div id="act-carts" class="na-list rounded-2xl p-3 sm:p-3.5 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-[13px] font-semibold text-gray-900">Carts to call back</h3>
            <a href="{{ route('admin.abandoned-carts.index') }}" class="text-[11px] font-semibold hover:underline" style="color: var(--brand);">{{ $callbackCount > 4 ? 'All ' . $callbackCount : 'Open' }} &rarr;</a>
          </div>
          <div class="mt-2 {{ $rowCols }} divide-gray-100">
            @foreach($callbackCarts->take(4) as $cart)
              @php $wa = $waNumber($cart->customer_phone); @endphp
              <div class="flex items-center gap-2 py-2">
                <span class="na-avatar grid h-8 w-8 shrink-0 place-items-center rounded-full text-[11px] font-semibold">{{ $initials($cart->customer_name ?: '#') }}</span>
                <div class="min-w-0 flex-1">
                  <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $cart->customer_name ?: $cart->customer_phone }}</p>
                  <p class="text-[11px] text-gray-500 truncate">{{ money($cart->total) }} &middot; {{ $cart->created_at->diffForHumans() }}</p>
                </div>
                @if($wa)
                  <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="shrink-0 grid h-8 w-8 place-items-center rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100" aria-label="WhatsApp {{ $cart->customer_phone }}" title="WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3Z"/></svg>
                  </a>
                @endif
                <a href="tel:{{ $cart->customer_phone }}" class="shrink-0 grid h-8 w-8 place-items-center rounded-full text-white" style="background: var(--brand-dark);" aria-label="Call {{ $cart->customer_phone }}" title="Call {{ $cart->customer_phone }}">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $phoneIcon !!}</svg>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if($lowStockCount > 0 && $lowStockProducts->isNotEmpty())
        <div id="act-stock" class="na-list rounded-2xl p-3 sm:p-3.5 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="text-[13px] font-semibold text-gray-900">Low stock</h3>
            <a href="{{ route('admin.inventory.index') }}" class="text-[11px] font-semibold hover:underline" style="color: var(--brand);">Manage &rarr;</a>
          </div>
          <div class="mt-2 {{ $rowCols }} divide-gray-100">
            @foreach($lowStockProducts->take(4) as $lowItem)
              <div class="flex items-center justify-between gap-2 py-2">
                <span class="min-w-0 text-[13px] font-medium text-gray-900 truncate">{{ $lowItem->name }}</span>
                <span class="shrink-0 px-2.5 py-1 text-[10px] font-semibold rounded-full {{ $lowItem->stock_quantity <= 0 ? 'text-white' : '' }}" style="{{ $lowItem->stock_quantity <= 0 ? 'background: var(--brand-dark);' : 'background: var(--brand-soft); color: var(--brand);' }}">{{ $lowItem->stock_quantity <= 0 ? 'Out of stock' : $lowItem->stock_quantity . ' left' }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
    @endif
  </section>

  {{-- Headline numbers --}}
  <div class="grid grid-cols-2 {{ $canMoney ? 'xl:grid-cols-4' : '' }} gap-3 sm:gap-4">
    @if($canMoney)
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <h2 class="text-xs sm:text-[13px] text-gray-500">Total Sales</h2>
      <div class="mt-1.5 flex items-center gap-x-2 gap-y-1 flex-wrap">
        <p class="text-lg sm:text-[22px] leading-tight font-semibold tracking-tight text-gray-900 font-mono">{{ money($revenue) }}</p>
        @if($salesTrend !== null)
          <span class="inline-flex items-center h-5 px-1.5 rounded-full text-[10px] font-semibold {{ $salesTrend >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}" title="Today vs yesterday">{{ $salesTrend >= 0 ? '↗' : '↘' }} {{ abs($salesTrend) }}%</span>
        @endif
      </div>
      <p class="mt-1 text-[11px] leading-snug text-gray-500 sm:truncate"><span class="font-semibold" style="color: var(--brand);">{{ money($todayRevenue) }}</span> today &middot; {{ number_format($totalSalesOrdersCount ?? $ordersCount) }} paid orders</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <h2 class="text-xs sm:text-[13px] text-gray-500">Net Profit</h2>
      <div class="mt-1.5 flex items-center gap-x-2 gap-y-1 flex-wrap">
        <p class="text-lg sm:text-[22px] leading-tight font-semibold tracking-tight font-mono {{ $netProfit < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $netProfit < 0 ? '-' : '' }}{{ money(abs($netProfit)) }}</p>
        <span class="inline-flex items-center h-5 px-1.5 rounded-full text-[10px] font-semibold {{ $profitMargin >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ round($profitMargin, 1) }}%</span>
      </div>
      <p class="mt-1 text-[11px] leading-snug text-gray-500 sm:truncate">Goods {{ money($totalCogs) }}@if($totalExpenses > 0) &middot; expenses <span class="text-rose-600">{{ money($totalExpenses) }}</span>@endif</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <h2 class="text-xs sm:text-[13px] text-gray-500">This month</h2>
      <p class="mt-1.5 text-lg sm:text-[22px] leading-tight font-semibold tracking-tight text-gray-900 font-mono truncate">{{ money($thisMonthRevenue) }}</p>
      <p class="mt-1 text-[11px] leading-snug text-gray-500 sm:truncate">{{ number_format($thisMonthOrders) }} {{ \Illuminate\Support\Str::plural('order', $thisMonthOrders) }} &middot; avg. {{ money($monthlyAvg) }} a month</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <h2 class="text-xs sm:text-[13px] text-gray-500">Avg. order</h2>
      <p class="mt-1.5 text-lg sm:text-[22px] leading-tight font-semibold tracking-tight text-gray-900 font-mono truncate">{{ money($avgOrderValue) }}</p>
      <p class="mt-1 text-[11px] leading-snug text-gray-500 sm:truncate">Orders today {{ number_format($todayOrdersCount) }} &middot; yesterday {{ number_format($yesterdayOrdersCount) }}</p>
    </div>
    @else
    <a href="{{ route('admin.inventory.index') }}" class="panel p-3.5 sm:p-4 min-w-0 col-span-2 hover:bg-gray-50 transition-colors">
      <h2 class="text-xs sm:text-[13px] text-gray-500">Stock health</h2>
      <p class="mt-1.5 flex items-baseline gap-2">
        <span class="text-lg sm:text-[22px] leading-tight font-semibold text-gray-900 font-mono">{{ number_format($totalStockUnits) }}</span>
        <span class="text-[11px] font-semibold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $lowStockCount > 0 ? $lowStockCount . ' low' : 'all good' }}</span>
      </p>
    </a>
    @endif
  </div>

  @if($canMoney || $canOrders)
  <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
    @if($canMoney)
    {{-- Monthly revenue --}}
    <div class="panel p-4 min-w-0 {{ $canOrders ? 'xl:col-span-8' : 'xl:col-span-12' }}">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Monthly Revenue</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ $firstMonthLabel }} – {{ $lastMonthLabel }} &middot; <span class="font-semibold text-gray-800">{{ money($totalSeriesRevenue) }}</span>@if(($peakMonth['value'] ?? 0) > 0) &middot; best {{ $peakMonth['label'] }}@endif</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="relative shrink-0">
          <label for="year" class="sr-only">Year</label>
          <select name="year" id="year" onchange="this.form.submit()" class="h-8 text-xs bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-full pl-3 pr-7 text-gray-800 font-semibold cursor-pointer appearance-none">
            @foreach($availableYears as $yr)
              <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
          </select>
          <svg class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </form>
      </div>
      <div class="relative mt-3">
        <div class="absolute inset-x-0 top-4 bottom-0 flex flex-col justify-between pointer-events-none" aria-hidden="true">
          <div class="border-b border-dashed border-gray-100"></div>
          <div class="border-b border-dashed border-gray-100"></div>
          <div class="border-b border-gray-200"></div>
        </div>
        <div class="relative flex items-end gap-1 sm:gap-2.5 h-28 sm:h-32 px-0.5">
          @foreach($monthlySeries as $point)
            @php
              $hasRevenue = $point['value'] > 0;
              $heightPercent = $hasRevenue && $seriesMax > 0 ? max(8, (int) round(($point['value'] / $seriesMax) * 78)) : 0;
              $isCurrent = $point['is_current'];
            @endphp
            <div class="flex-1 flex flex-col items-center justify-end h-full min-w-0" title="{{ $point['full_label'] }}: {{ money($point['value']) }}">
              @if($hasRevenue)
                <span class="mb-1 text-[9px] sm:text-[10px] font-semibold font-mono text-gray-500 leading-none truncate max-w-full max-sm:hidden">{{ money($point['value']) }}</span>
                <div class="w-full max-w-[14px] sm:max-w-[26px] rounded-full {{ $isCurrent ? '' : 'bg-teal-200 hover:bg-teal-300' }}" style="height: {{ $heightPercent }}%;{{ $isCurrent ? ' background: var(--brand);' : '' }}"></div>
              @else
                <div class="w-2 sm:w-3 h-0.5 bg-gray-200 rounded-full"></div>
              @endif
            </div>
          @endforeach
        </div>
        <div class="mt-1.5 grid grid-cols-12 text-center text-[9px] sm:text-[11px] font-medium text-gray-400">
          @foreach($monthlySeries as $point)
            <span class="{{ $point['is_current'] ? 'font-semibold' : ($point['value'] > 0 ? 'text-gray-700' : '') }} {{ !$point['is_current'] && ($loop->remaining % 2 === 1) ? 'max-[419px]:invisible' : '' }}" @if($point['is_current']) style="color: var(--brand);" @endif>{{ $point['label'] }}</span>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    @if($canOrders)
    {{-- Order status rings --}}
    <div class="panel p-4 min-w-0 {{ $canMoney ? 'xl:col-span-4' : 'xl:col-span-12' }}">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Order Breakdown</h2>
          <p class="text-xs text-gray-500 mt-0.5">Share of all {{ number_format($ringTotal) }} orders by status</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="shrink-0 text-xs font-semibold text-gray-600 hover:text-gray-900">Orders &rarr;</a>
      </div>
      <div class="mt-3 grid grid-cols-3 sm:grid-cols-6 xl:grid-cols-3 gap-y-3 gap-x-2">
        @foreach($statusRings as [$label, $count, $color])
          @php $pct = min(100, round($count / $ringTotal * 100)); @endphp
          <div class="flex flex-col items-center text-center" title="{{ $label }}: {{ $count }} ({{ $pct }}%)">
            <div class="relative h-[48px] w-[48px]">
              <svg viewBox="0 0 40 40" class="h-full w-full -rotate-90" aria-hidden="true">
                <circle cx="20" cy="20" r="15.5" fill="none" stroke="#f0efed" stroke-width="4.5"/>
                <circle cx="20" cy="20" r="15.5" fill="none" stroke="{{ $color }}" stroke-width="4.5" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ max($pct, $count > 0 ? 3 : 0) }} 100"/>
              </svg>
              <span class="absolute inset-0 grid place-items-center text-[11px] font-semibold text-gray-800 tabular-nums">{{ number_format($count) }}</span>
            </div>
            <span class="mt-1 text-[11px] text-gray-500">{{ $label }}</span>
          </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
  @endif

  @if($canMoney)
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Return loss --}}
    <div class="panel p-4 min-w-0">
      <div class="min-w-0">
        <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Return loss</h2>
        <p class="text-xs text-gray-500 mt-0.5">Parcels finished in the last 30 days</p>
      </div>
      <div class="mt-2.5 flex items-baseline gap-2 flex-wrap">
        <p class="text-xl leading-none font-semibold tracking-tight font-mono {{ $returnLoss > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ money($returnLoss) }}</p>
        <span class="text-[11px] text-gray-500">lost on {{ $returnedRecent }} {{ \Illuminate\Support\Str::plural('return', $returnedRecent) }} &middot; {{ $returnRate }}% return rate</span>
      </div>
      <div class="mt-3 space-y-2">
        @forelse($returnCities->take(3) as $c)
          @php $cityRate = $c->finished > 0 ? round($c->returned / $c->finished * 100) : 0; @endphp
          <div>
            <div class="flex items-center justify-between gap-2 text-xs">
              <span class="font-medium text-gray-800 truncate">{{ $c->city ?: 'Unknown' }}</span>
              <span class="shrink-0 text-gray-500">{{ $c->returned }} of {{ $c->finished }} &middot; <span class="font-semibold text-gray-800">{{ $cityRate }}%</span></span>
            </div>
            <div class="mt-1 h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-rose-400" style="width: {{ max(4, $cityRate) }}%"></div></div>
          </div>
        @empty
          <p class="text-xs text-gray-500">No returns in the last 30 days.</p>
        @endforelse
      </div>
    </div>

    {{-- Ad spend vs sales --}}
    <div class="panel p-4 min-w-0">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Ad spend vs sales</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ date('F') }}, from Marketing &amp; Facebook Ads expenses</p>
        </div>
        <a href="{{ route('admin.expenses.index') }}" class="shrink-0 text-xs font-semibold text-gray-600 hover:text-gray-900">Expenses &rarr;</a>
      </div>
      <div class="mt-2.5 grid grid-cols-3 gap-2">
        <div class="rounded-xl bg-gray-50 px-2.5 py-2 min-w-0">
          <span class="block text-[11px] text-gray-500 truncate">Ad spend</span>
          <span class="block mt-0.5 text-sm font-semibold text-gray-900 font-mono truncate">{{ money($adSpend) }}</span>
        </div>
        <div class="rounded-xl bg-gray-50 px-2.5 py-2 min-w-0">
          <span class="block text-[11px] text-gray-500 truncate">Cost per order</span>
          <span class="block mt-0.5 text-sm font-semibold text-gray-900 font-mono truncate">{{ $adSpend > 0 && $costPerOrder !== null ? money($costPerOrder) : '—' }}</span>
        </div>
        <div class="rounded-xl bg-gray-50 px-2.5 py-2 min-w-0" title="Sales per {{ currency_symbol() }}1 of ads">
          <span class="block text-[11px] text-gray-500 truncate">Sales per {{ currency_symbol() }}1</span>
          <span class="block mt-0.5 text-sm font-semibold font-mono truncate {{ $roas === null ? 'text-gray-900' : ($roas >= 3 ? 'text-emerald-600' : ($roas >= 1.5 ? 'text-amber-600' : 'text-rose-600')) }}">{{ $roas === null ? '—' : currency_symbol() . number_format($roas, 1) }}</span>
        </div>
      </div>
      <p class="mt-2.5 text-[11px] text-gray-500">{{ $adSpend > 0 ? 'Against ' . money($thisMonthRevenue) . ' of sales this month.' : "Log this month's ad spend under Expenses to see cost per order." }}</p>
    </div>
  </div>
  @endif

  @if($canMoney || $canOrders)
  @php
    // Bottom block: Top products on the left; Sales by day, Store performance and Recent orders on the right
    $both = $canMoney && $canOrders;
    $dayMax = max(1, $salesByDay->max('value'));
    $bestDay = $salesByDay->max('value');
    $perfParts = [
      ['Delivered', $deliveredCount ?? 0, 'var(--brand)'],
      ['In progress', $ordersCount ?? 0, 'color-mix(in srgb, var(--brand) 45%, #fff)'],
      ['Returned or cancelled', ($returnedOrdersCount ?? 0) + ($cancelledOrdersCount ?? 0), 'var(--brand-dark)'],
    ];
    $perfTotal = max(1, collect($perfParts)->sum(1));
    // Two-row pages fill column by column, so on wide screens each page of 6 gets a CSS order that reads #1 #2 #3 across the top row
    $tpOrder = fn ($i) => intdiv($i, 6) * 6 + [0, 2, 4, 1, 3, 5][$i % 6];
  @endphp
  <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-stretch">
    @if($canMoney)
    {{-- Top products: product cards in two rows, paged with the arrows (swipe on phones) --}}
    <section class="rounded-[22px] p-3.5 sm:p-4 min-w-0 flex flex-col {{ $both ? 'xl:col-span-6' : 'xl:col-span-12' }}" data-tint data-top-products>
      <div class="flex items-start justify-between gap-3 px-0.5">
        <div class="min-w-0">
          <h2 class="text-[15px] sm:text-base font-semibold text-gray-900">Top products</h2>
          <p class="text-xs text-gray-500 mt-0.5" title="The % compares units sold in the last 30 days with the 30 days before">Best sellers by verified revenue</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
          <button type="button" data-tp-prev aria-label="Previous products" class="h-9 w-9 rounded-full bg-white text-gray-800 grid place-items-center shadow-sm hover:bg-gray-50 transition disabled:opacity-40 disabled:cursor-default">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
          </button>
          <button type="button" data-tp-next aria-label="More products" class="h-9 w-9 rounded-full bg-white text-gray-800 grid place-items-center shadow-sm hover:bg-gray-50 transition disabled:opacity-40 disabled:cursor-default">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </div>
      </div>

      @if($topProducts->isEmpty())
        <div class="mt-3 rounded-2xl bg-white py-8 text-center text-xs text-gray-400">No revenue data recorded yet.</div>
      @else
        <div data-tp-track class="tp-track flex-1 mt-3 sm:mt-4 grid gap-2.5 sm:gap-3 overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth {{ $both ? 'tp-rows' : '' }}">
          @foreach($topProducts as $index => $item)
            @php
              $img = $item->product ? $item->product->imageUrl() : $item->image;
              $link = $item->product ? route('admin.products.edit', $item->product) : route('admin.products.index');
              $stock = $item->product?->stock_quantity;
            @endphp
            <a href="{{ $link }}" class="tp-card snap-start rounded-2xl bg-white p-2.5 sm:p-3 hover:shadow-md transition-shadow min-w-0" style="--tp-order: {{ $tpOrder($index) }};">
              <div class="tp-img relative aspect-[4/3] rounded-xl bg-stone-50 grid place-items-center overflow-hidden">
                @if($img)
                  <img src="{{ $img }}" alt="{{ $item->product_name }}" loading="lazy" class="h-full w-full object-cover" onerror="this.remove()" />
                @else
                  <span class="text-2xl font-semibold text-gray-300">{{ mb_substr($item->product_name, 0, 1) }}</span>
                @endif
                <span class="absolute top-2 left-2 h-6 min-w-[24px] px-1.5 rounded-full text-[11px] font-semibold grid place-items-center {{ $index === 0 ? 'text-white' : 'bg-white text-gray-700 shadow-sm' }}" @if($index === 0) style="background: var(--brand-dark);" @endif>#{{ $index + 1 }}</span>
                @if($stock !== null && $stock <= 5)
                  <span class="absolute top-2 right-2 px-2 h-6 rounded-full text-[10px] font-semibold grid place-items-center {{ $stock <= 0 ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-800' }}">{{ $stock <= 0 ? 'Out of stock' : $stock.' left' }}</span>
                @endif
              </div>
              <p class="mt-2.5 text-[13px] font-semibold text-gray-900 truncate" title="{{ $item->product_name }}">{{ $item->product_name }}</p>
              <p class="mt-0.5 text-[11px] text-gray-500 flex items-center gap-1.5 min-w-0">
                <span class="whitespace-nowrap">{{ number_format($item->total_units) }} sold</span>
                @if($item->trend === 'new')
                  <span class="font-semibold text-emerald-600">New</span>
                @elseif($item->trend !== null)
                  <span class="font-semibold tabular-nums {{ $item->trend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $item->trend >= 0 ? '+' : '' }}{{ $item->trend }}%</span>
                @endif
              </p>
              <p class="mt-1 text-xs font-semibold text-gray-900 tabular-nums truncate">{{ money($item->total_revenue) }}</p>
            </a>
          @endforeach
        </div>
      @endif
    </section>
    @endif

    @if($canOrders)
    <div class="flex flex-col gap-4 min-w-0 {{ $both ? 'xl:col-span-6' : 'xl:col-span-12' }}">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($canMoney)
        {{-- Sales by day: last 7 days --}}
        <div class="rounded-[22px] p-4 min-w-0" data-tint>
          <h2 class="text-[15px] font-semibold text-gray-900">Sales by day</h2>
          <p class="text-xs text-gray-500 mt-0.5">Last 7 days &middot; <span class="font-semibold text-gray-800">{{ money($salesByDay->sum('value')) }}</span></p>
          {{-- Every day gets the same track; the fill is the brand brown, the best day dark brown, and an empty day shows only its track --}}
          <div class="mt-3 grid grid-cols-7 gap-1 h-24">
            @foreach($salesByDay as $day)
              @php $isBest = $day['value'] > 0 && $day['value'] == $bestDay; @endphp
              <div class="flex justify-center" title="{{ $day['date'] }}: {{ money($day['value']) }}">
                <div class="relative h-full w-3.5 sm:w-4 rounded-full overflow-hidden" style="background: color-mix(in srgb, var(--brand-border) 45%, #fff);">
                  @if($day['value'] > 0)
                    <div class="absolute inset-x-0 bottom-0 rounded-full" style="height: max(1rem, {{ round($day['value'] / $dayMax * 100) }}%); background: {{ $isBest ? 'var(--brand-dark)' : 'var(--brand)' }};"></div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
          <div class="mt-2 grid grid-cols-7 gap-1 text-[11px] text-gray-500">
            @foreach($salesByDay as $day)
              <span class="text-center {{ $day['is_today'] ? 'font-semibold' : '' }}" @if($day['is_today']) style="color: var(--brand-dark);" @endif>{{ $day['label'] }}</span>
            @endforeach
          </div>
        </div>
        @endif

        {{-- Store performance: order success rate with the order mix --}}
        <div class="rounded-[22px] p-4 min-w-0 flex flex-col {{ $canMoney ? '' : 'sm:col-span-2' }}" data-tint>
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
          <div class="mt-auto pt-3 grid grid-cols-3 gap-2">
            <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Orders</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($allOrdersCount) }}</span></div>
            <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Items sold</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($itemsSold) }}</span></div>
            <div class="min-w-0"><span class="block text-[11px] text-gray-500 truncate">Customers</span><span class="block text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($customersCount) }}</span></div>
          </div>
          <div class="mt-2.5 flex h-2.5 gap-1">
            @foreach($perfParts as [$label, $count, $color])
              @if($count > 0)
                <span class="rounded-full" style="flex: {{ $count }} 1 0; background: {{ $color }};" title="{{ $label }}: {{ number_format($count) }} ({{ round($count / $perfTotal * 100) }}%)"></span>
              @endif
            @endforeach
          </div>
        </div>
      </div>

      {{-- Recent orders --}}
      <div class="rounded-[22px] p-4 min-w-0 flex-1" data-tint>
        <div class="flex items-start justify-between gap-3">
          <h2 class="text-[15px] font-semibold text-gray-900">Recent orders</h2>
          <a href="{{ route('admin.orders.index') }}" class="shrink-0 text-xs font-semibold text-gray-700 hover:text-gray-900">See all &rarr;</a>
        </div>

        <div class="hidden md:block mt-1.5">
          <table class="w-full text-left text-xs border-collapse">
            <thead class="!bg-transparent">
              <tr class="whitespace-nowrap">
                <th class="py-2 pr-3 !font-medium !normal-case !tracking-normal !text-[11px] !text-gray-500">Order #</th>
                <th class="py-2 px-3 !font-medium !normal-case !tracking-normal !text-[11px] !text-gray-500">Customer</th>
                <th class="py-2 px-3 text-right !font-medium !normal-case !tracking-normal !text-[11px] !text-gray-500">Total</th>
                <th class="py-2 px-3 !font-medium !normal-case !tracking-normal !text-[11px] !text-gray-500">Status</th>
                <th class="py-2 pl-3 text-right !font-medium !normal-case !tracking-normal !text-[11px] !text-gray-500">Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentOrders as $order)
                <tr class="hover:!bg-white/60">
                  <td class="py-2 pr-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 hover:underline whitespace-nowrap">{{ $order->order_number }}</a></td>
                  <td class="py-2 px-3 text-gray-800 max-w-[150px] truncate">{{ $order->customer_name }}</td>
                  <td class="py-2 px-3 text-right font-semibold text-gray-900 font-mono whitespace-nowrap">{{ money($order->total) }}</td>
                  <td class="py-2 px-3 whitespace-nowrap"><span class="px-2.5 py-1 text-[10px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span></td>
                  <td class="py-2 pl-3 text-right text-gray-500 whitespace-nowrap">{{ $order->created_at->format('d M, Y') }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="py-6 text-center text-gray-400">No recent orders found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="md:hidden mt-2 space-y-1.5">
          @forelse($recentOrders as $order)
            <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 rounded-xl bg-white/70 px-3 py-2">
              <div class="min-w-0 flex-1">
                <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $order->customer_name }}</p>
                <p class="text-[11px] text-gray-500 truncate">{{ $order->order_number }} &middot; {{ $order->created_at->format('d M') }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-[13px] font-semibold text-gray-900 font-mono">{{ money($order->total) }}</p>
                <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
              </div>
            </a>
          @empty
            <p class="py-6 text-center text-xs text-gray-400">No recent orders found.</p>
          @endforelse
        </div>
      </div>
    </div>
    @endif
  </div>
  <style>
    /* Top products: 2 cards per view on phones, then 3; beside Recent orders on wide screens they stack in two rows of 3 */
    .tp-track { grid-auto-flow: column; grid-auto-columns: calc((100% - .625rem) / 2); }
    @media (min-width: 640px)  { .tp-track { grid-auto-columns: calc((100% - 1.5rem) / 3); } }
    @media (min-width: 1024px) { .tp-track:not(.tp-rows) { grid-auto-columns: calc((100% - 3rem) / 5); } }
    @media (min-width: 1280px) {
      .tp-track:not(.tp-rows) { grid-auto-columns: calc((100% - 3.75rem) / 6); }
      .tp-track.tp-rows { grid-template-rows: repeat(2, 1fr); }
      .tp-track.tp-rows .tp-img { aspect-ratio: 1 / 1; }
      .tp-track.tp-rows > a { order: var(--tp-order); }
    }
  </style>
  <script>
    (function () {
      var box = document.querySelector('[data-top-products]');
      var track = box && box.querySelector('[data-tp-track]');
      if (!track) return;
      var prev = box.querySelector('[data-tp-prev]'), next = box.querySelector('[data-tp-next]');
      function sync() {
        prev.disabled = track.scrollLeft <= 2;
        next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
      }
      prev.addEventListener('click', function () { track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }); });
      next.addEventListener('click', function () { track.scrollBy({ left: track.clientWidth, behavior: 'smooth' }); });
      track.addEventListener('scroll', sync, { passive: true });
      // Re-check whenever the row changes size (also once the styles have loaded)
      if (window.ResizeObserver) new ResizeObserver(sync).observe(track); else window.addEventListener('resize', sync);
      window.addEventListener('load', sync);
      sync();
    })();
  </script>
  @endif
</div>
@endsection
