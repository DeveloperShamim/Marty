@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 max-w-full">

  {{-- Welcome & Overview Header --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="space-y-1">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Store Active
          </span>
          <span class="text-xs text-gray-400 font-medium">&middot; {{ date('l, d M Y') }}</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">
          Welcome back, {{ auth()->user()->name ?? 'Admin' }}
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 max-w-2xl leading-relaxed">
          Real-time summary of sales, orders, payment verifications, and store inventory.
        </p>
      </div>

      {{-- Header Quick Actions --}}
      <div class="flex items-center gap-2 flex-wrap shrink-0">
        <a href="{{ route('admin.products.create') }}" class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 transition-colors shadow-2xs inline-flex items-center justify-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Add Product
        </a>
        <a href="{{ route('admin.orders.index') }}" class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-primary hover:bg-brand-700 text-white transition-colors shadow-2xs inline-flex items-center justify-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-white/90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          Orders
        </a>
        <a href="{{ route('home') }}" target="_blank" class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 transition-colors shadow-2xs inline-flex items-center justify-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Storefront
        </a>
      </div>
    </div>
  </div>

  {{-- Primary Financial & Order KPIs (1-col on mobile, 2-col on small tablet, 4-col on desktop) --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

    {{-- 1. Total Revenue --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition-all">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Revenue</span>
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="mt-3 space-y-1">
        <p class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight">{{ money($revenue) }}</p>
        <p class="text-xs text-gray-500 flex items-center justify-between">
          <span>Verified orders:</span>
          <span class="font-semibold text-gray-700 font-mono">{{ number_format($ordersCount) }}</span>
        </p>
      </div>
    </div>

    {{-- 2. Today's Revenue --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition-all">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Today's Sales</span>
        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
      </div>
      <div class="mt-3 space-y-1">
        <p class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight">{{ money($todayRevenue) }}</p>
        <p class="text-xs text-gray-500 flex items-center justify-between">
          <span>Yesterday:</span>
          <span class="font-semibold text-gray-700 font-mono">{{ money($yesterdayRevenue) }}</span>
        </p>
      </div>
    </div>

    {{-- 3. Active Orders --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition-all">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Orders</span>
        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
      </div>
      <div class="mt-3 space-y-1">
        <p class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight">{{ number_format($ordersCount) }}</p>
        <p class="text-xs text-gray-500 truncate">
          Excludes {{ number_format($cancelledOrdersCount ?? 0) }} cancelled
        </p>
      </div>
    </div>

    {{-- 4. Pending Payment Approvals --}}
    <div class="bg-white rounded-2xl border {{ $pendingCount > 0 ? 'border-amber-300/80 bg-amber-50/20' : 'border-gray-200/90' }} p-4 sm:p-5 shadow-2xs hover:shadow-sm transition-all">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending Payments</span>
        <div class="w-9 h-9 rounded-xl {{ $pendingCount > 0 ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="mt-3 flex items-center justify-between">
        <p class="text-xl sm:text-2xl font-bold {{ $pendingCount > 0 ? 'text-amber-800' : 'text-gray-900' }} font-mono tracking-tight">
          {{ number_format($pendingCount) }}
        </p>
        @if($pendingCount > 0)
          <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition-colors shadow-2xs">
            Review &rarr;
          </a>
        @else
          <span class="text-xs font-medium text-emerald-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            All clear
          </span>
        @endif
      </div>
    </div>

  </div>

  {{-- Secondary Quick Stats (Compact 2-col or 4-col strip) --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="bg-white rounded-xl border border-gray-200/80 p-3.5 sm:p-4 shadow-2xs">
      <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wide block">This Month ({{ date('M Y') }})</span>
      <p class="text-base sm:text-lg font-bold text-gray-900 font-mono mt-1 truncate">{{ money($thisMonthRevenue) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200/80 p-3.5 sm:p-4 shadow-2xs">
      <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wide block">Avg. Order Value</span>
      <p class="text-base sm:text-lg font-bold text-gray-900 font-mono mt-1 truncate">{{ money($avgOrderValue) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200/80 p-3.5 sm:p-4 shadow-2xs">
      <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wide block">Traffic Today</span>
      <div class="flex items-center justify-between mt-1">
        <p class="text-base sm:text-lg font-bold text-gray-900 font-mono">{{ number_format($visitorsToday) }}</p>
        <span class="text-[11px] text-gray-400">Yest: {{ number_format($visitorsYesterday) }}</span>
      </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200/80 p-3.5 sm:p-4 shadow-2xs">
      <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wide block">Stock Health</span>
      <div class="flex items-center justify-between mt-1">
        <p class="text-base sm:text-lg font-bold text-gray-900 font-mono">{{ number_format($totalStockUnits) }}</p>
        <a href="{{ route('admin.inventory.index') }}" class="text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
          {{ $lowStockCount > 0 ? $lowStockCount . ' Low' : 'In Stock' }}
        </a>
      </div>
    </div>
  </div>

  {{-- Main Analytics Grid: 12-Month Performance Chart & Operations Column --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Revenue Trend Chart (2 cols on desktop) --}}
    @php
      $monthlyAvg = $totalSeriesRevenue > 0 ? ($totalSeriesRevenue / 12) : 0;
      $currentMonthData = $monthlySeries->firstWhere('is_current', true) ?? ['value' => 0];
      $activeSalesMonths = $monthlySeries->where('value', '>', 0)->count();
    @endphp
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-6 shadow-2xs lg:col-span-2 flex flex-col justify-between space-y-5">
      
      {{-- Card Header & Filter Bar --}}
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-bold text-sm sm:text-base text-gray-900">Monthly Revenue Performance</h2>
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-md bg-gray-100 text-gray-600 border border-gray-200">
              12 Months
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-0.5">
            Rolling window: <span class="font-mono text-gray-700 font-medium">{{ $firstMonthLabel }} – {{ $lastMonthLabel }}</span>
          </p>
        </div>

        <div class="flex items-center gap-2.5">
          <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5">
            <label for="year" class="text-xs font-semibold text-gray-500">Filter Year:</label>
            <div class="relative">
              <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-xl px-3 py-1.5 pr-7 text-gray-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-2xs cursor-pointer appearance-none">
                @foreach($availableYears as $yr)
                  <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
              </select>
              <svg class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </form>
        </div>
      </div>

      {{-- Total Period Revenue Highlight --}}
      <div class="flex flex-wrap items-baseline justify-between gap-2 px-1">
        <div>
          <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block">Total 12-Month Revenue</span>
          <p class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tracking-tight mt-0.5">{{ money($totalSeriesRevenue) }}</p>
        </div>
        <div class="text-right">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $activeSalesMonths > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-600 border border-gray-200' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $activeSalesMonths > 0 ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
            {{ $activeSalesMonths }} / 12 Months with Sales
          </span>
        </div>
      </div>

      {{-- Interactive Bar Chart Canvas with Background Grid Guidelines --}}
      <div class="pt-2 overflow-x-auto no-scrollbar">
        <div class="min-w-[460px] relative">
          
          {{-- Subtle Background Guidelines --}}
          <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8 pt-1" aria-hidden="true">
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-dashed border-gray-100"></div>
            <div class="w-full border-b border-gray-200"></div>
          </div>

          {{-- Bars Container --}}
          <div class="relative z-10 flex items-end gap-2 sm:gap-3.5 h-48 sm:h-56 px-2 pb-2">
            @foreach($monthlySeries as $point)
              @php
                $hasRevenue = $point['value'] > 0;
                $heightPercent = $hasRevenue && $seriesMax > 0 ? max(8, (int) round(($point['value'] / $seriesMax) * 75)) : 0;
              @endphp
              <div class="flex-1 flex flex-col items-center justify-end h-full relative cursor-default" title="{{ $point['full_label'] }}: {{ money($point['value']) }}">
                
                {{-- Direct Value Label Above Bar --}}
                @if($hasRevenue)
                  <span class="text-[10px] sm:text-[11px] font-bold font-mono text-emerald-700 mb-1.5 leading-none text-center whitespace-nowrap">
                    {{ money($point['value']) }}
                  </span>
                @endif

                {{-- Bar Column --}}
                @if($hasRevenue)
                  <div class="w-full max-w-[28px] mx-auto rounded-t-lg sm:rounded-t-xl transition-all duration-300 {{ $point['is_current'] ? 'bg-gradient-to-t from-primary to-teal-500 shadow-sm ring-2 ring-teal-400/40' : 'bg-slate-800 hover:bg-primary transition-colors' }}" style="height: {{ $heightPercent }}%">
                  </div>
                @else
                  <div class="w-3 h-0.5 bg-gray-200 rounded-full mx-auto mb-0.5"></div>
                @endif
              </div>
            @endforeach
          </div>

          {{-- X-Axis Labels --}}
          <div class="mt-2.5 grid grid-cols-12 text-center text-[10px] sm:text-[11px] font-semibold text-gray-400 relative z-10">
            @foreach($monthlySeries as $point)
              <div class="flex flex-col items-center gap-0.5">
                <span class="{{ $point['is_current'] ? 'text-primary font-bold' : ($point['value'] > 0 ? 'text-gray-700 font-medium' : '') }}">
                  {{ $point['label'] }}
                </span>
                @if($point['is_current'])
                  <span class="w-1.5 h-1.5 rounded-full bg-primary" title="Current Month"></span>
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
          <span class="font-bold text-gray-900 font-mono text-[11px] truncate max-w-[140px]" title="{{ $peakMonth['full_label'] ?? 'N/A' }}">
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

    {{-- Right: Action Items & Operations (1 col on desktop) --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-6 shadow-2xs flex flex-col justify-between space-y-5">
      
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
          <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="text-xs font-semibold text-primary hover:underline">
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

      {{-- Courier & Fulfillment Breakdown --}}
      <div class="bg-gray-50/80 p-3 rounded-xl border border-gray-200/80 space-y-2">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 block">Fulfillment Overview</span>
        <div class="grid grid-cols-3 gap-2 text-center">
          <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-2xs">
            <span class="block text-[10px] text-gray-400 font-semibold">Dispatched</span>
            <span class="font-bold text-gray-900 font-mono text-sm">{{ number_format($dispatchedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-2xs">
            <span class="block text-[10px] text-gray-400 font-semibold">Shipped</span>
            <span class="font-bold text-primary font-mono text-sm">{{ number_format($shippedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-2xs">
            <span class="block text-[10px] text-gray-400 font-semibold">Delivered</span>
            <span class="font-bold text-emerald-600 font-mono text-sm">{{ number_format($deliveredCount ?? 0) }}</span>
          </div>
        </div>
      </div>

      {{-- Low Stock Alerts Feed --}}
      <div class="space-y-2 pt-1 border-t border-gray-100">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Low Stock Alerts
          </span>
          <a href="{{ route('admin.inventory.index') }}" class="text-xs font-semibold text-primary hover:underline">
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

  {{-- Top Products Leaderboard --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-6 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h2 class="font-bold text-sm sm:text-base text-gray-900">Top Revenue Products</h2>
        <p class="text-xs text-gray-500">Best-selling products ranked by verified revenue</p>
      </div>
      <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-primary hover:underline">
        All Products &rarr;
      </a>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4 w-14">Rank</th>
            <th class="py-3 px-4">Product Details</th>
            <th class="py-3 px-4 text-center">Units Sold</th>
            <th class="py-3 px-4 text-right">Total Revenue</th>
            <th class="py-3 px-4 text-center">Stock Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($topProducts as $index => $item)
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4 font-bold text-xs">
                <span class="inline-block px-2 py-0.5 rounded-md font-mono text-xs {{ $index === 0 ? 'bg-primary/10 text-primary font-bold' : 'bg-gray-100 text-gray-700' }}">
                  #{{ $index + 1 }}
                </span>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  @if($item->image)
                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-9 w-9 object-cover rounded-lg border border-gray-200 shrink-0 bg-gray-100" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
                  @else
                    <div class="h-9 w-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 font-bold text-xs shrink-0 border border-gray-200">
                      {{ substr($item->product_name, 0, 1) }}
                    </div>
                  @endif
                  <div class="min-w-0">
                    <span class="font-semibold text-gray-900 text-xs line-clamp-1 block">{{ $item->product_name }}</span>
                    @if($item->product)
                      <span class="text-[11px] text-gray-400 font-mono">Price: {{ money($item->product->price) }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 font-mono">
                  {{ number_format($item->total_units) }} units
                </span>
              </td>
              <td class="py-3 px-4 text-right font-bold text-emerald-700 font-mono text-xs sm:text-sm">
                {{ money($item->total_revenue) }}
              </td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                @if($item->product)
                  @if($item->product->stock_quantity <= 0)
                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-rose-100 text-rose-800">Out of Stock</span>
                  @elseif($item->product->stock_quantity <= 5)
                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-800">Low ({{ $item->product->stock_quantity }})</span>
                  @else
                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800">{{ $item->product->stock_quantity }} In Stock</span>
                  @endif
                @else
                  <span class="text-xs text-gray-400">N/A</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-6 text-gray-400 text-xs">
                No revenue data recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden space-y-2.5">
      @forelse($topProducts as $index => $item)
        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-200 space-y-2 shadow-2xs">
          <div class="flex items-center gap-2.5">
            <span class="px-2 py-0.5 rounded-md font-mono text-xs font-bold shrink-0 {{ $index === 0 ? 'bg-primary/10 text-primary' : 'bg-gray-200 text-gray-700' }}">
              #{{ $index + 1 }}
            </span>

            @if($item->image)
              <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-9 w-9 object-cover rounded-lg border border-gray-200 shrink-0 bg-white" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
            @else
              <div class="h-9 w-9 bg-white rounded-lg flex items-center justify-center text-gray-400 font-bold text-xs shrink-0 border border-gray-200">
                {{ substr($item->product_name, 0, 1) }}
              </div>
            @endif

            <div class="min-w-0 flex-1">
              <span class="font-semibold text-gray-900 text-xs line-clamp-1 block">{{ $item->product_name }}</span>
              <div class="flex items-center justify-between text-[11px] text-gray-500 mt-0.5">
                <span>{{ number_format($item->total_units) }} units sold</span>
                <span class="font-bold text-emerald-700 font-mono">{{ money($item->total_revenue) }}</span>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-6 text-gray-400 text-xs bg-gray-50 rounded-xl border border-gray-200">
          No revenue data recorded yet.
        </div>
      @endforelse
    </div>
  </div>

  {{-- Recent Orders Activity --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 p-4 sm:p-6 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h2 class="font-bold text-sm sm:text-base text-gray-900">Recent Orders</h2>
        <p class="text-xs text-gray-500">Latest incoming purchases across the storefront</p>
      </div>
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-primary hover:underline">
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
                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">
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

</div>
@endsection
