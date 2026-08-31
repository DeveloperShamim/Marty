@extends('layouts.admin')
@section('title', 'Dashboard & Revenue Analytics')

@section('content')
<div class="space-y-6 sm:space-y-8 max-w-full">

  {{-- Top Welcome Banner & Action Bar (Classic Light Style) --}}
  <div class="rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs p-5 sm:p-7 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 sm:gap-6">
    <div class="space-y-1.5 min-w-0">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-extrabold border border-emerald-200">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Live Store Analytics</span>
        </span>
        <span class="text-xs text-stone-400 font-medium hidden sm:inline">&middot; {{ date('l, d M Y') }}</span>
      </div>
      <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-stone-900 truncate">
        Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 max-w-2xl leading-relaxed">
        Real-time financial revenue tracking, fulfillment updates, payment verifications, and stock alerts.
      </p>
    </div>

    {{-- Header Quick Action Buttons --}}
    <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap shrink-0 w-full sm:w-auto">
      <a href="{{ route('admin.products.create') }}" class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs font-extrabold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-800 border border-stone-200 transition shadow-2xs flex items-center justify-center gap-1.5 text-center">
        <span>➕</span> Add Product
      </a>
      <a href="{{ route('admin.orders.index') }}" class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition shadow-sm flex items-center justify-center gap-1.5 text-center">
        <span>📦</span> Orders
      </a>
      <a href="{{ route('home') }}" target="_blank" class="flex-1 sm:flex-none px-4 py-2 sm:py-2.5 text-xs font-extrabold rounded-xl bg-white text-stone-800 hover:bg-stone-50 border border-stone-200 transition shadow-2xs flex items-center justify-center gap-1.5 text-center">
        <span>🌐</span> Shop ↗
      </a>
    </div>
  </div>

  {{-- KPI Stat Cards Grid (Responsive 2-col on Mobile, 4-col on Tablet/Desktop) --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-5">
    
    {{-- Card 1: Verified Total Revenue --}}
    <div class="rounded-2xl sm:rounded-3xl border border-emerald-200/90 bg-gradient-to-br from-emerald-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-emerald-800 uppercase tracking-wider block">Total Revenue</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-emerald-500/10 text-emerald-700 border border-emerald-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          💰
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-emerald-700 font-mono tracking-tight">{{ money($revenue) }}</p>
        <div class="flex items-center justify-between text-[11px] font-bold text-emerald-800 pt-0.5">
          <span>Verified Orders:</span>
          <span class="font-mono">{{ number_format($ordersCount) }}</span>
        </div>
      </div>
    </div>

    {{-- Card 2: Today's Revenue --}}
    <div class="rounded-2xl sm:rounded-3xl border border-stone-200/90 bg-gradient-to-br from-stone-50 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-stone-700 uppercase tracking-wider block">Today's Sales</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-stone-100 text-stone-700 border border-stone-200 flex items-center justify-center text-base sm:text-xl shrink-0">
          📈
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-stone-900 font-mono tracking-tight">{{ money($todayRevenue) }}</p>
        <div class="flex items-center justify-between text-[11px] text-stone-500 pt-0.5">
          <span>Yesterday:</span>
          <strong class="font-mono text-stone-800">{{ money($yesterdayRevenue) }}</strong>
        </div>
      </div>
    </div>

    {{-- Card 3: This Month --}}
    <div class="rounded-2xl sm:rounded-3xl border border-indigo-200/90 bg-gradient-to-br from-indigo-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-indigo-900 uppercase tracking-wider block">This Month</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-indigo-500/10 text-indigo-700 border border-indigo-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          📅
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-indigo-800 font-mono tracking-tight">{{ money($thisMonthRevenue) }}</p>
        <div class="flex items-center justify-between text-[11px] font-extrabold text-indigo-900 pt-0.5">
          <span>{{ date('F Y') }}</span>
          <span class="px-1.5 py-0.2 rounded bg-indigo-100/90 text-indigo-900 text-[10px]">Active</span>
        </div>
      </div>
    </div>

    {{-- Card 4: Average Order Value (AOV) --}}
    <div class="rounded-2xl sm:rounded-3xl border border-sky-200/90 bg-gradient-to-br from-sky-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-sky-900 uppercase tracking-wider block">Avg Order Value</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-sky-500/10 text-sky-700 border border-sky-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          🛒
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-sky-800 font-mono tracking-tight">{{ money($avgOrderValue) }}</p>
        <div class="flex items-center justify-between text-[11px] text-sky-700 font-semibold pt-0.5">
          <span>Per verified cart</span>
          <span>Basket Avg</span>
        </div>
      </div>
    </div>

    {{-- Card 5: Active Orders Count --}}
    <div class="rounded-2xl sm:rounded-3xl border border-stone-200/90 bg-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-stone-700 uppercase tracking-wider block">Active Orders</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-stone-100 text-stone-700 border border-stone-200 flex items-center justify-center text-base sm:text-xl shrink-0">
          📦
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-stone-900 tracking-tight font-mono">{{ number_format($ordersCount) }}</p>
        <p class="text-[10px] sm:text-[11px] text-stone-400 font-medium truncate">Excludes {{ number_format($cancelledOrdersCount ?? 0) }} cancelled</p>
      </div>
    </div>

    {{-- Card 6: Pending Verification --}}
    <div class="rounded-2xl sm:rounded-3xl border border-amber-200/90 bg-gradient-to-br from-amber-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-amber-900 uppercase tracking-wider block">Pending Payments</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-amber-500/10 text-amber-700 border border-amber-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          ⏳
        </div>
      </div>
      <div class="mt-3 sm:mt-4 flex items-center justify-between">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-amber-800 tracking-tight font-mono">{{ number_format($pendingCount) }}</p>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition shadow-2xs">
          Review ↗
        </a>
      </div>
    </div>

    {{-- Card 7: Unique Visitors Today --}}
    <div class="rounded-2xl sm:rounded-3xl border border-purple-200/90 bg-gradient-to-br from-purple-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-purple-900 uppercase tracking-wider block">Store Traffic</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-purple-500/10 text-purple-700 border border-purple-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          👥
        </div>
      </div>
      <div class="mt-3 sm:mt-4 space-y-1">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-purple-900 tracking-tight font-mono">{{ number_format($visitorsToday) }}</p>
        <div class="flex items-center justify-between text-[11px] text-stone-500 pt-0.5">
          <span>Yesterday:</span>
          <strong class="font-mono text-stone-800">{{ number_format($visitorsYesterday) }}</strong>
        </div>
      </div>
    </div>

    {{-- Card 8: Live Product Stock & Inventory --}}
    <div class="rounded-2xl sm:rounded-3xl border border-rose-200/90 bg-gradient-to-br from-rose-50/80 via-white to-white p-4 sm:p-5 shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-black text-rose-900 uppercase tracking-wider block">Total Stock Units</span>
        <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-rose-500/10 text-rose-700 border border-rose-200/70 flex items-center justify-center text-base sm:text-xl shrink-0">
          🏭
        </div>
      </div>
      <div class="mt-3 sm:mt-4 flex items-center justify-between">
        <p class="text-xl sm:text-2xl lg:text-3xl font-black text-rose-800 tracking-tight font-mono">{{ number_format($totalStockUnits) }}</p>
        <a href="{{ route('admin.inventory.index') }}" class="px-2 py-1 text-[10px] sm:text-[11px] font-extrabold rounded-lg {{ $lowStockCount > 0 ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white' }} shadow-2xs">
          {{ $lowStockCount > 0 ? $lowStockCount . ' Low' : 'Stock OK' }}
        </a>
      </div>
    </div>

  </div>

  {{-- Main Analytics Grid: Chart (Left 2 cols on Desktop) & Pending Approvals / Courier Widget (Right 1 col) --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Monthly Revenue Performance Chart Card --}}
    <div class="rounded-2xl sm:rounded-3xl border border-stone-200 bg-white p-5 sm:p-6 lg:p-7 shadow-2xs lg:col-span-2 flex flex-col justify-between space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-stone-100 pb-4">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-extrabold text-base sm:text-lg text-stone-900">Monthly Revenue Performance</h2>
            <span class="px-2.5 py-0.5 text-[10px] sm:text-[11px] font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
              12-Month Series
            </span>
          </div>
          <p class="text-xs text-stone-500 mt-1">
            Rolling revenue trend: <strong class="text-stone-800 font-mono">{{ $firstMonthLabel }} – {{ $lastMonthLabel }}</strong>
          </p>
        </div>

        {{-- Peak Month & Year Selector --}}
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
          <div class="hidden sm:flex items-center gap-2 bg-stone-50 px-3 py-1.5 rounded-xl border border-stone-200 text-xs">
            <span class="text-stone-500">🏆 Peak:</span>
            <span class="font-black text-emerald-800 font-mono">{{ $peakMonth['full_label'] ?? 'N/A' }} ({{ money($peakMonth['value'] ?? 0) }})</span>
          </div>

          <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5">
            <label for="year" class="text-xs font-bold text-stone-600">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-stone-50 border border-stone-300 rounded-xl px-2.5 py-1.5 text-stone-800 font-extrabold focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
          </form>
        </div>
      </div>

      {{-- 12-Month Bar Chart Graphic --}}
      <div class="pt-2">
        <div class="flex items-end gap-1.5 sm:gap-3 h-48 sm:h-56 px-1 sm:px-2 border-b border-stone-200 pb-2">
          @foreach($monthlySeries as $index => $point)
            @php
              $heightPercent = max(6, (int) round(($point['value'] / $seriesMax) * 100));
            @endphp
            <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
              
              {{-- Floating Tooltip --}}
              <div class="opacity-0 group-hover:opacity-100 transition-all duration-150 absolute -top-12 bg-stone-900 text-white text-[11px] font-bold py-1.5 px-2.5 rounded-xl shadow-lg pointer-events-none z-20 whitespace-nowrap flex flex-col items-center">
                <span>{{ $point['full_label'] }}</span>
                <span class="text-emerald-300 font-mono text-xs">{{ money($point['value']) }}</span>
                <div class="w-2 h-2 bg-stone-900 rotate-45 -mb-1 mt-0.5"></div>
              </div>

              {{-- Bar Element --}}
              <div class="w-full rounded-t-lg sm:rounded-t-xl transition-all duration-300 {{ $point['is_current'] ? 'bg-gradient-to-t from-emerald-600 to-emerald-400 ring-2 ring-emerald-400/50 shadow-md' : ($point['value'] > 0 ? 'bg-gradient-to-t from-stone-700 to-stone-500 group-hover:from-emerald-600 group-hover:to-emerald-500' : 'bg-stone-100') }}" style="height: {{ $heightPercent }}%">
              </div>
            </div>
          @endforeach
        </div>

        {{-- Month Labels --}}
        <div class="mt-2.5 grid grid-cols-12 text-center text-[10px] sm:text-[11px] font-bold text-stone-400">
          @foreach($monthlySeries as $point)
            <span class="{{ $point['is_current'] ? 'text-emerald-700 font-black underline decoration-2 underline-offset-4' : ($point['value'] > 0 ? 'text-stone-800 font-extrabold' : '') }}">
              {{ $point['label'] }}
            </span>
          @endforeach
        </div>
      </div>

      {{-- Footer Chart Summary Stats --}}
      <div class="pt-3 border-t border-stone-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-stone-500">
        <div>
          <span>12-Month Total Series Sales: </span>
          <strong class="font-extrabold text-stone-900 font-mono">{{ money($totalSeriesRevenue) }}</strong>
        </div>
        <div class="flex items-center gap-3 text-[11px]">
          <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Current Month</span>
          <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-stone-600"></span> Verified Sales</span>
        </div>
      </div>
    </div>

    {{-- Pending Payment Approval & Courier Dispatch Sidebar --}}
    <div class="rounded-2xl sm:rounded-3xl border border-stone-200 bg-white p-5 sm:p-6 shadow-2xs flex flex-col justify-between space-y-4">
      <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
          <div>
            <h2 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-1.5">
              <span>⏳</span> Pending Approvals
            </h2>
            <p class="text-xs text-stone-400">Orders requiring payment check</p>
          </div>
          <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="text-xs font-extrabold text-brand-600 hover:underline">
            View All &rarr;
          </a>
        </div>

        <div class="space-y-2.5 max-h-72 overflow-y-auto no-scrollbar">
          @forelse($pendingOrders as $order)
            <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/80 flex items-center justify-between gap-2.5 hover:bg-amber-50 transition-colors">
              <div class="min-w-0 flex-1">
                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-black text-brand-700 hover:underline block truncate">
                  {{ $order->order_number }}
                </a>
                <p class="text-xs font-bold text-stone-900 truncate mt-0.5">{{ $order->customer_name }}</p>
                <p class="text-[11px] text-stone-500">{{ $order->paymentMethodLabel() }} &middot; <span class="font-mono font-bold text-stone-900">{{ money($order->total) }}</span></p>
              </div>

              <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-2.5 py-1.5 text-xs font-extrabold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs cursor-pointer">
                  ✓ Verify
                </button>
              </form>
            </div>
          @empty
            <div class="text-center py-8 bg-stone-50 rounded-xl border border-dashed border-stone-200">
              <p class="text-2xl mb-1">🎉</p>
              <p class="text-xs font-bold text-stone-700">All clear!</p>
              <p class="text-[11px] text-stone-400 mt-0.5">No orders awaiting payment verification.</p>
            </div>
          @endforelse
        </div>
      </div>

      {{-- Courier Dispatch Summary --}}
      <div class="pt-3 border-t border-stone-100 bg-stone-50 p-3 rounded-xl border border-stone-200 space-y-2">
        <span class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-stone-500 block">🚚 Courier &amp; Fulfillment</span>
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
          <div class="bg-white p-2 rounded-lg border border-stone-200 shadow-2xs">
            <span class="block text-[10px] text-stone-400 font-bold">Dispatched</span>
            <span class="font-black text-stone-900 font-mono text-xs sm:text-sm">{{ number_format($dispatchedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-lg border border-stone-200 shadow-2xs">
            <span class="block text-[10px] text-stone-400 font-bold">Shipped</span>
            <span class="font-black text-brand-600 font-mono text-xs sm:text-sm">{{ number_format($shippedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-lg border border-stone-200 shadow-2xs">
            <span class="block text-[10px] text-stone-400 font-bold">Delivered</span>
            <span class="font-black text-emerald-600 font-mono text-xs sm:text-sm">{{ number_format($deliveredCount ?? 0) }}</span>
          </div>
        </div>
      </div>

      {{-- Live Low Stock Alert --}}
      <div class="pt-2 border-t border-stone-100 space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold uppercase tracking-wider text-stone-700 flex items-center gap-1">
            <span>🚨</span> Low Stock Alerts
          </span>
          <a href="{{ route('admin.inventory.index') }}" class="text-[11px] font-bold text-brand-600 hover:underline">
            Manage &rarr;
          </a>
        </div>

        <div class="space-y-1.5">
          @forelse($lowStockProducts as $lowItem)
            <div class="p-2 bg-rose-50/70 rounded-lg border border-rose-200/80 flex items-center justify-between gap-2">
              <div class="min-w-0">
                <p class="text-xs font-bold text-stone-900 truncate">{{ $lowItem->name }}</p>
                <p class="text-[10px] text-stone-400 font-mono">{{ $lowItem->sku }}</p>
              </div>
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $lowItem->stock_quantity <= 0 ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-800' }} shrink-0">
                {{ $lowItem->stock_quantity <= 0 ? 'Out of Stock' : $lowItem->stock_quantity . ' Left' }}
              </span>
            </div>
          @empty
            <div class="text-center py-3 bg-emerald-50/60 rounded-lg border border-emerald-200/60 text-xs text-emerald-800 font-bold">
              <span>✅ Healthy Stock (All items in stock)</span>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Top Revenue Products Leaderboard Card --}}
  <div class="rounded-2xl sm:rounded-3xl border border-stone-200 bg-white p-4 sm:p-6 lg:p-7 shadow-2xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 border-b border-stone-100 pb-3 sm:pb-4">
      <div>
        <h2 class="font-extrabold text-base sm:text-lg text-stone-900 flex items-center gap-2">
          <span>🏆</span> Top Revenue Products
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Best-selling items ranked by verified revenue earned</p>
      </div>
      <a href="{{ route('admin.products.index') }}" class="text-xs font-extrabold text-brand-600 hover:underline">
        View All Products &rarr;
      </a>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-stone-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4 w-16">Rank</th>
            <th class="py-3 px-4">Product Details</th>
            <th class="py-3 px-4 text-center">Units Sold</th>
            <th class="py-3 px-4 text-right">Total Revenue</th>
            <th class="py-3 px-4 text-center">Live Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($topProducts as $index => $item)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4 font-black text-xs">
                @if($index === 0)
                  <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 font-extrabold shadow-2xs">🥇 #1</span>
                @elseif($index === 1)
                  <span class="px-2 py-0.5 rounded-lg bg-stone-200 text-stone-800 border border-stone-300 font-extrabold">🥈 #2</span>
                @elseif($index === 2)
                  <span class="px-2 py-0.5 rounded-lg bg-orange-100 text-orange-900 border border-orange-200 font-extrabold">🥉 #3</span>
                @else
                  <span class="px-2 py-0.5 rounded-lg bg-stone-100 text-stone-600 font-bold">#{{ $index + 1 }}</span>
                @endif
              </td>
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  @if($item->image)
                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-9 w-9 object-cover rounded-lg border border-stone-200 shrink-0" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
                  @else
                    <div class="h-9 w-9 bg-stone-100 rounded-lg flex items-center justify-center text-stone-400 font-extrabold text-xs shrink-0 border border-stone-200">
                      {{ substr($item->product_name, 0, 1) }}
                    </div>
                  @endif
                  <div class="min-w-0">
                    <span class="font-extrabold text-stone-900 text-xs line-clamp-1 block">{{ $item->product_name }}</span>
                    @if($item->product)
                      <span class="text-[11px] text-stone-500 font-medium">Price: {{ money($item->product->price) }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-stone-100 text-stone-800 border border-stone-200 font-mono">
                  {{ number_format($item->total_units) }} units
                </span>
              </td>
              <td class="py-3.5 px-4 text-right font-black text-emerald-700 font-mono text-xs sm:text-sm">
                {{ money($item->total_revenue) }}
              </td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                @if($item->product)
                  @if($item->product->stock_quantity <= 0)
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-100 text-rose-800 border border-rose-200">Out of Stock</span>
                  @elseif($item->product->stock_quantity <= 5)
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Low ({{ $item->product->stock_quantity }})</span>
                  @else
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">In Stock ({{ $item->product->stock_quantity }})</span>
                  @endif
                @else
                  <span class="text-xs text-stone-400">N/A</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-8 text-stone-400 text-xs">
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
        <div class="p-3.5 bg-stone-50/80 rounded-2xl border border-stone-200 space-y-2.5 shadow-2xs">
          <div class="flex items-center gap-2.5">
            <div class="shrink-0 font-black text-xs">
              @if($index === 0)
                <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-[11px] shadow-2xs">🥇 #1</span>
              @elseif($index === 1)
                <span class="px-2 py-0.5 rounded-lg bg-stone-200 text-stone-800 border border-stone-300 font-extrabold text-[11px]">🥈 #2</span>
              @elseif($index === 2)
                <span class="px-2 py-0.5 rounded-lg bg-orange-100 text-orange-900 border border-orange-200 font-extrabold text-[11px]">🥉 #3</span>
              @else
                <span class="px-2 py-0.5 rounded-lg bg-stone-100 text-stone-600 font-bold text-[11px]">#{{ $index + 1 }}</span>
              @endif
            </div>

            @if($item->image)
              <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-10 w-10 object-cover rounded-xl border border-stone-200 shrink-0 bg-white" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'36\' height=\'36\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
            @else
              <div class="h-10 w-10 bg-white rounded-xl flex items-center justify-center text-stone-400 font-extrabold text-xs shrink-0 border border-stone-200">
                {{ substr($item->product_name, 0, 1) }}
              </div>
            @endif

            <div class="min-w-0 flex-1">
              <span class="font-extrabold text-stone-900 text-xs line-clamp-2 block leading-snug">{{ $item->product_name }}</span>
              @if($item->product)
                <span class="text-[11px] text-stone-500 font-medium">Unit: {{ money($item->product->price) }}</span>
              @endif
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2 pt-2 border-t border-stone-200/70 text-center text-xs">
            <div class="bg-white p-2 rounded-xl border border-stone-200 shadow-2xs">
              <span class="text-[10px] font-bold text-stone-400 block">Sold</span>
              <span class="font-black text-stone-800 font-mono text-xs">{{ number_format($item->total_units) }} units</span>
            </div>
            <div class="bg-white p-2 rounded-xl border border-stone-200 shadow-2xs">
              <span class="text-[10px] font-bold text-emerald-700 block">Revenue</span>
              <span class="font-black text-emerald-700 font-mono text-xs">{{ money($item->total_revenue) }}</span>
            </div>
            <div class="bg-white p-2 rounded-xl border border-stone-200 shadow-2xs flex flex-col items-center justify-center">
              <span class="text-[10px] font-bold text-stone-400 block">Stock</span>
              @if($item->product)
                @if($item->product->stock_quantity <= 0)
                  <span class="text-[10px] font-extrabold text-rose-600">Out of Stock</span>
                @elseif($item->product->stock_quantity <= 5)
                  <span class="text-[10px] font-extrabold text-amber-600">Low ({{ $item->product->stock_quantity }})</span>
                @else
                  <span class="text-[10px] font-extrabold text-emerald-700">{{ $item->product->stock_quantity }} Left</span>
                @endif
              @else
                <span class="text-[10px] text-stone-400">N/A</span>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-6 text-stone-400 text-xs bg-stone-50 rounded-xl border border-stone-200">
          No revenue data recorded yet.
        </div>
      @endforelse
    </div>
  </div>

  {{-- Recent Orders Activity Table --}}
  <div class="rounded-2xl sm:rounded-3xl border border-stone-200 bg-white p-4 sm:p-6 lg:p-7 shadow-2xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 border-b border-stone-100 pb-3 sm:pb-4">
      <div>
        <h2 class="font-extrabold text-base sm:text-lg text-stone-900 flex items-center gap-2">
          <span>📦</span> Recent Orders Activity
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Latest incoming customer purchases across storefront</p>
      </div>
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-extrabold text-brand-600 hover:underline">
        View All Orders &rarr;
      </a>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto rounded-xl border border-stone-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">Order Number</th>
            <th class="py-3 px-4">Customer Name</th>
            <th class="py-3 px-4 text-center">Total Value</th>
            <th class="py-3 px-4 text-center">Payment Status</th>
            <th class="py-3 px-4 text-center">Fulfillment Status</th>
            <th class="py-3 px-4 text-right">Order Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @foreach($recentOrders as $order)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-brand-700 hover:underline">
                  {{ $order->order_number }}
                </a>
              </td>
              <td class="py-3.5 px-4 font-bold text-stone-800">{{ $order->customer_name }}</td>
              <td class="py-3.5 px-4 text-center font-black text-stone-900 font-mono">{{ money($order->total) }}</td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                  {{ ucfirst($order->payment_status) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                  {{ ucfirst($order->status) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right text-stone-500 font-medium whitespace-nowrap">{{ $order->created_at->format('d M, Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden space-y-2.5">
      @forelse($recentOrders as $order)
        <div class="p-3.5 bg-stone-50/80 rounded-2xl border border-stone-200 space-y-2.5 shadow-2xs">
          <div class="flex items-center justify-between">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-black text-brand-700 hover:underline">
              {{ $order->order_number }}
            </a>
            <span class="text-[10px] font-medium text-stone-400">{{ $order->created_at->format('d M, Y') }}</span>
          </div>

          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 truncate">{{ $order->customer_name }}</p>
              <p class="text-[11px] text-stone-500 font-mono">{{ $order->customer_phone ?: 'No Phone' }}</p>
            </div>
            <p class="text-sm font-black text-stone-900 font-mono shrink-0">{{ money($order->total) }}</p>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-stone-200/70 text-xs">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                {{ ucfirst($order->payment_status) }}
              </span>
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                {{ ucfirst($order->status) }}
              </span>
            </div>
            <a href="{{ route('admin.orders.show', $order) }}" class="text-[11px] font-extrabold text-brand-600 hover:underline">
              Details &rarr;
            </a>
          </div>
        </div>
      @empty
        <div class="text-center py-6 text-stone-400 text-xs bg-stone-50 rounded-xl border border-stone-200">
          No recent orders found.
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
