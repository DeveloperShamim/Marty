@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-4 max-w-full">

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

    // Delivery success gauge: delivered vs returned
    $finished = ($deliveredCount ?? 0) + ($returnedOrdersCount ?? 0);
    $successRate = $finished > 0 ? round(($deliveredCount ?? 0) / $finished * 100, 1) : 0;
    $gaugeSegments = 28;
    $gaugeOn = (int) round($successRate / 100 * $gaugeSegments);
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

  <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
    <div class="xl:col-span-7 space-y-4 min-w-0">
      @if($canMoney)
      {{-- Headline numbers (stacked on very small phones so the numbers and notes fit) --}}
      <div class="grid grid-cols-1 min-[400px]:grid-cols-2 gap-3 sm:gap-4">
        <div class="panel p-3.5 sm:p-4">
          <div class="flex items-start justify-between gap-3">
            <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Total Sales</h2>
            <span class="hidden sm:grid h-8 w-8 place-items-center rounded-full border border-gray-200 text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>
          </div>
          <div class="mt-2.5 sm:mt-3 flex items-center gap-x-2 gap-y-1.5 flex-wrap">
            <p class="text-xl sm:text-[26px] leading-none font-semibold tracking-tight text-gray-900 font-mono">{{ money($revenue) }}</p>
            @if($salesTrend !== null)
              <span class="inline-flex items-center gap-0.5 h-6 px-2 rounded-full text-[11px] font-semibold {{ $salesTrend >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $salesTrend >= 0 ? '↗' : '↘' }} {{ abs($salesTrend) }}%</span>
            @endif
          </div>
          <p class="mt-2 text-[11px] sm:text-xs text-gray-500">You made <span class="font-semibold" style="color: var(--brand);">{{ money($todayRevenue) }}</span> today &middot; {{ number_format($totalSalesOrdersCount ?? $ordersCount) }} paid orders</p>
        </div>
        <div class="panel p-3.5 sm:p-4">
          <div class="flex items-start justify-between gap-3">
            <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Net Profit</h2>
            <span class="hidden sm:grid h-8 w-8 place-items-center rounded-full border border-gray-200 text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg></span>
          </div>
          <div class="mt-2.5 sm:mt-3 flex items-center gap-x-2 gap-y-1.5 flex-wrap">
            <p class="text-xl sm:text-[26px] leading-none font-semibold tracking-tight font-mono {{ $netProfit < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $netProfit < 0 ? '-' : '' }}{{ money(abs($netProfit)) }}</p>
            <span class="inline-flex items-center gap-0.5 h-6 px-2 rounded-full text-[11px] font-semibold {{ $profitMargin >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ round($profitMargin, 1) }}% margin</span>
          </div>
          <p class="mt-2 text-[11px] sm:text-xs text-gray-500 truncate">Cost of goods <span class="font-semibold text-gray-700">{{ money($totalCogs) }}</span>@if($totalExpenses > 0) &middot; expenses <span class="font-semibold text-rose-600">{{ money($totalExpenses) }}</span>@endif</p>
        </div>
      </div>
      @endif

      @if($canOrders)
      {{-- Order status rings --}}
      <div class="panel p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Order Breakdown</h2>
            <p class="text-xs text-gray-500 mt-0.5">Share of all {{ number_format($ringTotal) }} orders by status</p>
          </div>
          <a href="{{ route('admin.orders.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50" aria-label="Open orders"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg></a>
        </div>
        <div class="mt-5 grid grid-cols-3 sm:grid-cols-6 gap-y-5 gap-x-2">
          @foreach($statusRings as [$label, $count, $color])
            @php $pct = min(100, round($count / $ringTotal * 100)); @endphp
            <div class="flex flex-col items-center text-center" title="{{ $label }}: {{ $count }} ({{ $pct }}%)">
              <div class="relative h-[60px] w-[60px]">
                <svg viewBox="0 0 40 40" class="h-full w-full -rotate-90" aria-hidden="true">
                  <circle cx="20" cy="20" r="15.5" fill="none" stroke="#f0efed" stroke-width="4.5"/>
                  <circle cx="20" cy="20" r="15.5" fill="none" stroke="{{ $color }}" stroke-width="4.5" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ max($pct, $count > 0 ? 3 : 0) }} 100"/>
                </svg>
                <span class="absolute inset-0 grid place-items-center text-[12px] font-semibold text-gray-800 tabular-nums">{{ number_format($count) }}</span>
              </div>
              <span class="mt-2 text-xs text-gray-500">{{ $label }}</span>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Small numbers --}}
      <div class="grid {{ $canMoney ? 'grid-cols-2 min-[420px]:grid-cols-3' : 'grid-cols-1' }} gap-3 sm:gap-4">
        @if($canMoney)
        <div class="panel px-3.5 py-3 min-w-0">
          <span class="text-[11px] text-gray-500 block truncate">This month</span>
          <p class="mt-1 text-[15px] sm:text-lg font-semibold text-gray-900 font-mono truncate">{{ money($thisMonthRevenue) }}</p>
        </div>
        <div class="panel px-3.5 py-3 min-w-0">
          <span class="text-[11px] text-gray-500 block truncate">Avg. order</span>
          <p class="mt-1 text-[15px] sm:text-lg font-semibold text-gray-900 font-mono truncate">{{ money($avgOrderValue) }}</p>
        </div>
        @endif
        <a href="{{ route('admin.inventory.index') }}" class="panel px-3.5 py-3 min-w-0 hover:bg-gray-50 transition-colors {{ $canMoney ? 'col-span-2 min-[420px]:col-span-1' : '' }}">
          <span class="text-[11px] text-gray-500 block truncate">Stock health</span>
          <p class="mt-1 flex items-baseline flex-wrap gap-x-1.5 min-w-0">
            <span class="text-[15px] sm:text-lg font-semibold text-gray-900 font-mono">{{ number_format($totalStockUnits) }}</span>
            <span class="text-[11px] font-semibold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $lowStockCount > 0 ? $lowStockCount . ' low' : 'all good' }}</span>
          </p>
        </a>
      </div>

    </div>

    <div class="xl:col-span-5 grid grid-cols-1 gap-4 min-w-0">
      @if($canOrders)
      {{-- Risky orders to call before shipping --}}
      <div class="panel p-4 flex flex-col">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Risky orders to call</h2>
            <p class="text-xs text-gray-500 mt-0.5">Not shipped yet, flagged by fraud check or courier history</p>
          </div>
          @if($riskyCount > 0)
            <span class="shrink-0 inline-flex items-center h-6 px-2.5 rounded-full bg-rose-50 text-rose-700 text-[11px] font-semibold">{{ $riskyCount }} to call</span>
          @endif
        </div>
        <div class="mt-3 divide-y divide-gray-100">
          @forelse($riskyOrders as $o)
            <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
              <span class="h-2 w-2 shrink-0 rounded-full {{ $o->risk >= 2 ? 'bg-rose-500' : 'bg-amber-400' }}" title="{{ $o->risk >= 2 ? 'High risk' : 'Medium risk' }}"></span>
              <a href="{{ route('admin.orders.show', $o) }}" class="min-w-0 flex-1 group">
                <span class="flex items-baseline gap-2 min-w-0">
                  <span class="text-[13px] font-semibold text-gray-900 truncate group-hover:underline">{{ $o->customer_name }}</span>
                  <span class="text-[11px] text-gray-400 shrink-0">{{ $o->order_number }} &middot; {{ money($o->total) }}</span>
                </span>
                <span class="block text-[11px] {{ $o->risk >= 2 ? 'text-rose-600' : 'text-amber-700' }} truncate">{{ $o->riskReason }}</span>
              </a>
              <a href="tel:{{ $o->customer_phone }}" class="shrink-0 grid h-8 w-8 place-items-center rounded-full text-white" style="background: var(--brand-dark);" aria-label="Call {{ $o->customer_name }}" title="Call {{ $o->customer_phone }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>
              </a>
            </div>
          @empty
            <p class="py-6 text-center text-xs text-gray-500">No risky orders waiting. Everything not yet shipped looks safe.</p>
          @endforelse
        </div>
      </div>
      @endif

      @if($canOrders)
      {{-- Delivery success gauge --}}
      <div class="panel p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Delivery Success</h2>
            <p class="text-xs text-gray-500 mt-0.5">Delivered vs returned parcels</p>
          </div>
          <a href="{{ route('admin.courier-scan.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-gray-200 text-gray-600 hover:bg-gray-50" aria-label="Open courier scan"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg></a>
        </div>
        <div class="relative mx-auto mt-2 w-full max-w-[240px]">
          <svg viewBox="0 0 200 112" class="w-full" aria-hidden="true">
            @for($k = 0; $k < $gaugeSegments; $k++)
              @php
                $ang = M_PI - ($k + .5) * M_PI / $gaugeSegments;
                $x1 = 100 + cos($ang) * 70; $y1 = 104 - sin($ang) * 70;
                $x2 = 100 + cos($ang) * 92; $y2 = 104 - sin($ang) * 92;
                $mixPct = $gaugeOn > 1 ? round(35 + 65 * $k / max(1, $gaugeOn - 1)) : 100;
              @endphp
              <line x1="{{ round($x1, 2) }}" y1="{{ round($y1, 2) }}" x2="{{ round($x2, 2) }}" y2="{{ round($y2, 2) }}" stroke-width="7" stroke-linecap="round"
                    stroke="{{ $k < $gaugeOn ? 'color-mix(in srgb, var(--brand) ' . $mixPct . '%, #fff)' : '#e7e5e4' }}"/>
            @endfor
          </svg>
          <div class="absolute inset-x-0 bottom-1 text-center">
            <p class="text-[26px] leading-none font-semibold tracking-tight text-gray-900 tabular-nums">{{ $successRate }}%</p>
            <p class="mt-1 text-xs text-gray-500">{{ number_format($deliveredCount ?? 0) }} delivered &middot; {{ number_format($returnedOrdersCount ?? 0) }} returned</p>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between gap-2 rounded-[20px] border border-gray-200 pl-4 pr-1.5 py-1.5">
          <span class="text-xs text-gray-600 min-w-0">Orders today <span class="font-semibold text-gray-900">{{ number_format($todayOrdersCount) }}</span> &middot; yesterday {{ number_format($yesterdayOrdersCount) }}</span>
          @if($ordersTrend !== null)
            <span class="inline-flex items-center gap-1 h-7 px-2.5 rounded-full text-[11px] font-semibold shrink-0" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $ordersTrend >= 0 ? '↑' : '↓' }} {{ abs($ordersTrend) }}%</span>
          @endif
        </div>
      </div>
      @endif
    </div>
  </div>

  {{-- Main Analytics Grid: 12-Month Performance Chart & Operations Column --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

    @if($canMoney)
    {{-- Left: Revenue Trend Chart (2 cols on desktop) --}}
    @php
      $monthlyAvg = $totalSeriesRevenue > 0 ? ($totalSeriesRevenue / 12) : 0;
      $currentMonthData = $monthlySeries->firstWhere('is_current', true) ?? ['value' => 0];
      $activeSalesMonths = $monthlySeries->where('value', '>', 0)->count();
    @endphp
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs xl:col-span-2 flex flex-col justify-between space-y-5 min-w-0">
      
      {{-- Card Header & Filter Bar --}}
      <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3 sm:pb-4 flex-wrap">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-bold text-sm sm:text-base text-gray-900">Monthly Revenue</h2>
            <span class="px-2 py-0.5 text-[10px] sm:text-[11px] font-semibold rounded-md bg-gray-100 text-gray-600 border border-gray-200">
              12 Months
            </span>
          </div>
          <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">
            {{ $firstMonthLabel }} – {{ $lastMonthLabel }}
          </p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5 ml-auto sm:ml-0">
          <label for="year" class="text-xs font-semibold text-gray-500 hidden sm:inline">Year:</label>
          <div class="relative">
            <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-xl px-2.5 sm:px-3 py-1 sm:py-1.5 pr-6 sm:pr-7 text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-2xs cursor-pointer appearance-none">
              @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
            <svg class="w-3 h-3 text-gray-400 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </form>
      </div>

      {{-- Total Period Revenue Highlight --}}
      <div class="flex flex-wrap items-baseline justify-between gap-2 px-1">
        <div>
          <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block">Total 12-Month Revenue</span>
          <p class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight mt-0.5">{{ money($totalSeriesRevenue) }}</p>
        </div>
        <div class="text-right">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $activeSalesMonths > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-600 border border-gray-200' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $activeSalesMonths > 0 ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
            {{ $activeSalesMonths }} / 12 Months with Sales
          </span>
        </div>
      </div>

      {{-- Interactive Bar Chart Canvas (Fully responsive, no horizontal cutoff) --}}
      <div class="pt-2 w-full">
        <div class="w-full relative">
          
          {{-- Subtle Background Guidelines --}}
          <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-7 pt-1" aria-hidden="true">
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-gray-200"></div>
          </div>

          {{-- Bars Container (12 months fully scaled across available width) --}}
          <div class="relative z-10 flex items-end gap-1 sm:gap-2.5 md:gap-3.5 h-36 sm:h-44 px-0.5 sm:px-2 pb-2">
            @foreach($monthlySeries as $index => $point)
              @php
                $hasRevenue = $point['value'] > 0;
                $heightPercent = $hasRevenue && $seriesMax > 0 ? max(8, (int) round(($point['value'] / $seriesMax) * 72)) : 0;
                $isCurrent = $point['is_current'];
              @endphp
              <div class="flex-1 flex flex-col items-center justify-end h-full relative cursor-default group " title="{{ $point['full_label'] }}: {{ money($point['value']) }}">
                
                {{-- Direct Value Label Above Bar --}}
                @if($hasRevenue)
                  <span class="text-[9px] sm:text-[11px] font-semibold font-mono text-gray-500 mb-1.5 leading-none text-center truncate max-w-full">
                    {{ money($point['value']) }}
                  </span>
                @endif

                {{-- Bar Column --}}
                @if($hasRevenue)
                  <div class="w-full max-w-[16px] sm:max-w-[34px] mx-auto rounded-full transition-all duration-300 {{ $isCurrent ? 'shadow-[0_10px_20px_-10px_var(--brand)]' : 'bg-teal-200 hover:bg-teal-300' }}" style="height: {{ $heightPercent }}%;{{ $isCurrent ? ' background: var(--brand);' : '' }}">
                  </div>
                @else
                  <div class="w-2 sm:w-3 h-0.5 bg-gray-200 rounded-full mx-auto mb-0.5"></div>
                @endif
              </div>
            @endforeach
          </div>

          {{-- X-Axis Labels (All 12 months fit natively on mobile) --}}
          <div class="mt-2 grid grid-cols-12 text-center text-[9px] sm:text-[11px] font-semibold text-gray-400 relative z-10">
            @foreach($monthlySeries as $point)
              {{-- On very small phones only every other month is labelled (the current month always is) --}}
              <div class="flex flex-col items-center justify-center {{ !$point['is_current'] && ($loop->remaining % 2 === 1) ? 'max-[419px]:invisible' : '' }}">
                @if($point['is_current'])
                  <span class="px-1 py-0.5 rounded text-[8px] sm:text-[10px] font-bold bg-primary text-white shadow-2xs leading-none">
                    {{ $point['label'] }}
                  </span>
                @else
                  <span class="{{ $point['value'] > 0 ? 'text-gray-800 font-bold' : 'text-gray-400' }}">
                    {{ $point['label'] }}
                  </span>
                @endif
              </div>
            @endforeach
          </div>

        </div>
      </div>

      {{-- Bottom Key Insights Strip --}}
      <div class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs text-gray-600">
        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200/70 flex items-center justify-between">
          <span class="text-gray-400 text-[11px]">Peak Month:</span>
          <span class="font-bold text-gray-900 font-mono text-[11px] truncate" title="{{ $peakMonth['full_label'] ?? 'N/A' }}">
            {{ $peakMonth['label'] ?? '' }} &middot; {{ money($peakMonth['value'] ?? 0) }}
          </span>
        </div>
        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200/70 flex items-center justify-between">
          <span class="text-gray-400 text-[11px]">Monthly Average:</span>
          <span class="font-bold text-gray-900 font-mono text-[11px]">{{ money($monthlyAvg) }}</span>
        </div>
        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200/70 flex items-center justify-between">
          <span class="text-gray-400 text-[11px]">Current Month:</span>
          <span class="font-bold text-primary font-mono text-[11px]">{{ money($currentMonthData['value'] ?? 0) }}</span>
        </div>
      </div>

    </div>

    @endif

    {{-- Right: Action Items & Operations (1 col on desktop) --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs flex flex-col space-y-5 min-w-0">
      
      @if($canOrders)
      {{-- Pending Payments Action Feed --}}
      <div class="space-y-3">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h2 class="font-bold text-sm sm:text-base text-gray-900 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Payment Approvals
            </h2>
            <p class="text-xs text-gray-400">Needs manual verification</p>
          </div>
          <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap shrink-0">
            View All &rarr;
          </a>
        </div>

        <div class="space-y-2 max-h-56 overflow-y-auto no-scrollbar">
          @forelse($pendingOrders as $order)
            <div class="p-2.5 bg-amber-50/50 rounded-xl border border-amber-200/60 flex items-center justify-between gap-2 hover:bg-amber-50 transition-colors">
              <div class="min-w-0 flex-1">
                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-primary hover:underline block truncate">
                  {{ $order->order_number }}
                </a>
                <p class="text-xs text-gray-800 truncate font-medium">{{ $order->customer_name }}</p>
                <p class="text-[11px] text-gray-500 font-mono">{{ money($order->total) }} &middot; {{ $order->paymentMethodLabel() }}</p>
              </div>

              <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-2xs">
                  Verify
                </button>
              </form>
            </div>
          @empty
            <div class="text-center py-6 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
              <p class="text-xs font-medium text-gray-500">No orders awaiting payment verification.</p>
            </div>
          @endforelse
        </div>
      </div>

      @endif

      {{-- Low Stock Alerts Feed --}}
      <div class="space-y-2 pt-1 border-t border-gray-100">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Low Stock Alerts
          </span>
          <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap shrink-0">
            Manage &rarr;
          </a>
        </div>

        <div class="space-y-1.5">
          @forelse($lowStockProducts as $lowItem)
            <div class="p-2 bg-rose-50/50 rounded-lg border border-rose-200/70 flex items-center justify-between gap-2">
              <div class="min-w-0">
                <p class="text-xs font-medium text-gray-900 truncate">{{ $lowItem->name }}</p>
                <p class="text-[10px] text-gray-400 font-mono">{{ $lowItem->sku }}</p>
              </div>
              <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $lowItem->stock_quantity <= 0 ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-800' }} shrink-0">
                {{ $lowItem->stock_quantity <= 0 ? 'Out of Stock' : $lowItem->stock_quantity . ' left' }}
              </span>
            </div>
          @empty
            <div class="text-center py-2.5 bg-emerald-50/50 rounded-lg border border-emerald-200/60 text-xs text-emerald-700 font-medium">
              Healthy stock levels across inventory.
            </div>
          @endforelse
        </div>
      </div>

    </div>

  </div>

  @php
    $canCarts = \App\Support\StaffAccess::allows(auth()->user(), 'abandoned-carts');
    $returnRate = $finishedRecent > 0 ? round($returnedRecent / $finishedRecent * 100, 1) : 0;
    $costPerOrder = $thisMonthOrders > 0 ? $adSpend / $thisMonthOrders : null;
    $roas = $adSpend > 0 ? $thisMonthRevenue / $adSpend : null;
    $waNumber = fn ($phone) => ($n = \App\Services\Courier\BdCourierService::normalizePhone($phone)) ? '88' . $n : null;
  @endphp
  @if($canCarts || $canMoney)
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @if($canCarts)
    {{-- Carts to call back --}}
    <div class="panel p-4 flex flex-col">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Carts to call back</h2>
          <p class="text-xs text-gray-500 mt-0.5">Left in the last 3 days, with a phone number</p>
        </div>
        <a href="{{ route('admin.abandoned-carts.index') }}" class="shrink-0 text-xs font-semibold text-gray-600 hover:text-gray-900">{{ $callbackCount > 5 ? 'All ' . $callbackCount : 'Open' }} &rarr;</a>
      </div>
      <div class="mt-3 divide-y divide-gray-100">
        @forelse($callbackCarts as $cart)
          @php $wa = $waNumber($cart->customer_phone); @endphp
          <div class="flex items-center gap-2.5 py-2.5 first:pt-0 last:pb-0">
            <div class="min-w-0 flex-1">
              <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $cart->customer_name ?: $cart->customer_phone }}</p>
              <p class="text-[11px] text-gray-500 truncate">{{ money($cart->total) }} &middot; {{ $cart->created_at->diffForHumans() }}</p>
            </div>
            @if($wa)
              <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="shrink-0 grid h-8 w-8 place-items-center rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100" aria-label="WhatsApp {{ $cart->customer_phone }}" title="WhatsApp">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.4-.3Z"/></svg>
              </a>
            @endif
            <a href="tel:{{ $cart->customer_phone }}" class="shrink-0 grid h-8 w-8 place-items-center rounded-full text-white" style="background: var(--brand-dark);" aria-label="Call {{ $cart->customer_phone }}" title="Call {{ $cart->customer_phone }}">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>
            </a>
          </div>
        @empty
          <p class="py-6 text-center text-xs text-gray-500">No carts to call back right now.</p>
        @endforelse
      </div>
    </div>
    @endif

    @if($canMoney)
    {{-- Return loss --}}
    <div class="panel p-4 flex flex-col">
      <div class="min-w-0">
        <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Return loss</h2>
        <p class="text-xs text-gray-500 mt-0.5">Parcels finished in the last 30 days</p>
      </div>
      <div class="mt-3 flex items-baseline gap-2 flex-wrap">
        <p class="text-xl sm:text-2xl leading-none font-semibold tracking-tight font-mono {{ $returnLoss > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ money($returnLoss) }}</p>
        <span class="text-xs text-gray-500">lost on {{ $returnedRecent }} {{ \Illuminate\Support\Str::plural('return', $returnedRecent) }} &middot; {{ $returnRate }}% return rate</span>
      </div>
      <div class="mt-4 space-y-2.5">
        <p class="text-[11px] font-medium text-gray-500">Most returns by city</p>
        @forelse($returnCities as $c)
          @php $cityRate = $c->finished > 0 ? round($c->returned / $c->finished * 100) : 0; @endphp
          <div>
            <div class="flex items-center justify-between gap-2 text-xs">
              <span class="font-medium text-gray-800 truncate">{{ $c->city ?: 'Unknown' }}</span>
              <span class="shrink-0 text-gray-500">{{ $c->returned }} of {{ $c->finished }} &middot; <span class="font-semibold text-gray-800">{{ $cityRate }}%</span></span>
            </div>
            <div class="mt-1 h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-rose-400" style="width: {{ max(4, $cityRate) }}%"></div></div>
          </div>
        @empty
          <p class="py-3 text-xs text-gray-500">No returns in the last 30 days.</p>
        @endforelse
      </div>
    </div>

    {{-- Ad spend vs sales --}}
    <div class="panel p-4 flex flex-col">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-sm sm:text-[15px] font-medium text-gray-900">Ad spend vs sales</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ date('F') }}, from Marketing &amp; Facebook Ads expenses</p>
        </div>
        <a href="{{ route('admin.expenses.index') }}" class="shrink-0 text-xs font-semibold text-gray-600 hover:text-gray-900">Expenses &rarr;</a>
      </div>
      <div class="mt-3 grid grid-cols-2 gap-2.5">
        <div class="rounded-xl bg-gray-50 px-3 py-2.5 min-w-0">
          <span class="block text-[11px] text-gray-500">Ad spend</span>
          <span class="block mt-0.5 text-[15px] font-semibold text-gray-900 font-mono truncate">{{ money($adSpend) }}</span>
        </div>
        <div class="rounded-xl bg-gray-50 px-3 py-2.5 min-w-0">
          <span class="block text-[11px] text-gray-500">Sales</span>
          <span class="block mt-0.5 text-[15px] font-semibold text-gray-900 font-mono truncate">{{ money($thisMonthRevenue) }}</span>
        </div>
        <div class="rounded-xl bg-gray-50 px-3 py-2.5 min-w-0">
          <span class="block text-[11px] text-gray-500">Cost per order</span>
          <span class="block mt-0.5 text-[15px] font-semibold text-gray-900 font-mono truncate">{{ $adSpend > 0 && $costPerOrder !== null ? money($costPerOrder) : '—' }}</span>
        </div>
        <div class="rounded-xl bg-gray-50 px-3 py-2.5 min-w-0">
          <span class="block text-[11px] text-gray-500">Sales per {{ currency_symbol() }}1 of ads</span>
          <span class="block mt-0.5 text-[15px] font-semibold font-mono truncate {{ $roas === null ? 'text-gray-900' : ($roas >= 3 ? 'text-emerald-600' : ($roas >= 1.5 ? 'text-amber-600' : 'text-rose-600')) }}">{{ $roas === null ? '—' : currency_symbol() . number_format($roas, 1) }}</span>
        </div>
      </div>
      @if($adSpend <= 0)
        <p class="mt-3 text-[11px] text-gray-500">Log this month's ad spend under Expenses (Marketing &amp; Facebook Ads) to see cost per order.</p>
      @endif
    </div>
    @endif
  </div>
  @endif

  @if($canMoney)
  {{-- Top products: a row of product cards paged with the arrows (swipe on phones) --}}
  <section class="rounded-[22px] p-3.5 sm:p-5" style="background: color-mix(in srgb, var(--brand) 9%, #f5f5f4);" data-top-products>
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
      <div data-tp-track class="tp-track mt-3 sm:mt-4 flex gap-2.5 sm:gap-3 overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth">
        @foreach($topProducts as $index => $item)
          @php
            $img = $item->product ? $item->product->imageUrl() : $item->image;
            $link = $item->product ? route('admin.products.edit', $item->product) : route('admin.products.index');
            $stock = $item->product?->stock_quantity;
          @endphp
          <a href="{{ $link }}" class="tp-card snap-start shrink-0 rounded-2xl bg-white p-2.5 sm:p-3 hover:shadow-md transition-shadow min-w-0">
            <div class="relative aspect-square rounded-xl bg-stone-50 grid place-items-center overflow-hidden">
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
  <style>
    /* Cards per view: 2 on phones, then 3, 4 and 5 as the screen widens */
    .tp-card { width: calc((100% - .625rem) / 2); }
    @media (min-width: 640px)  { .tp-card { width: calc((100% - 1.5rem) / 3); } }
    @media (min-width: 1024px) { .tp-card { width: calc((100% - 2.25rem) / 4); } }
    @media (min-width: 1280px) { .tp-card { width: calc((100% - 3rem) / 5); } }
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

  @if($canOrders)
  {{-- Recent Orders Activity --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h2 class="font-bold text-sm sm:text-base text-gray-900">Recent Orders</h2>
        <p class="text-xs text-gray-500">Latest incoming purchases across the storefront</p>
      </div>
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap shrink-0">
        All Orders &rarr;
      </a>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">Order ID</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4 text-center">Total</th>
            <th class="py-3 px-4 text-center">Payment</th>
            <th class="py-3 px-4 text-center">Fulfillment</th>
            <th class="py-3 px-4 text-right">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @foreach($recentOrders as $order)
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline whitespace-nowrap">
                  {{ $order->order_number }}
                </a>
              </td>
              <td class="py-3 px-4 font-medium text-gray-800">{{ $order->customer_name }}</td>
              <td class="py-3 px-4 text-center font-bold text-gray-900 font-mono">{{ money($order->total) }}</td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full {{ $order->paymentBadge() }}">
                  {{ ucfirst($order->payment_status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full {{ $order->statusBadge() }}">
                  {{ ucfirst($order->status) }}
                </span>
              </td>
              <td class="py-3 px-4 text-right text-gray-400 font-medium whitespace-nowrap">{{ $order->created_at->format('d M, Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden space-y-2.5">
      @forelse($recentOrders as $order)
        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-200 space-y-2 shadow-2xs">
          <div class="flex items-center justify-between">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-primary hover:underline">
              {{ $order->order_number }}
            </a>
            <span class="text-[11px] text-gray-400 font-mono">{{ $order->created_at->format('d M, Y') }}</span>
          </div>

          <div class="flex items-center justify-between text-xs">
            <div class="min-w-0">
              <p class="font-semibold text-gray-800 truncate">{{ $order->customer_name }}</p>
              <p class="text-[11px] text-gray-400 font-mono">{{ $order->customer_phone ?: 'No phone' }}</p>
            </div>
            <p class="font-bold text-gray-900 font-mono text-sm shrink-0">{{ money($order->total) }}</p>
          </div>

          <div class="flex items-center justify-between pt-1.5 border-t border-gray-200/60 text-xs">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->paymentBadge() }}">
                {{ ucfirst($order->payment_status) }}
              </span>
              <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->statusBadge() }}">
                {{ ucfirst($order->status) }}
              </span>
            </div>
            <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-primary hover:underline">
              Details &rarr;
            </a>
          </div>
        </div>
      @empty
        <div class="text-center py-6 text-gray-400 text-xs bg-gray-50 rounded-xl border border-gray-200">
          No recent orders found.
        </div>
      @endforelse
    </div>
  </div>

  @endif
</div>
@endsection
