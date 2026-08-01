@extends('layouts.admin')
@section('title', 'Dashboard & Revenue Analytics')

@section('content')
<div class="space-y-8">
  <!-- Top Welcome Banner & Action Bar -->
  <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-850 to-brand-900 text-white p-6 sm:p-8 shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative z-10 space-y-1">
      <div class="flex items-center gap-2">
        <span class="px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 text-xs font-extrabold border border-brand-500/30">
          ⚡ Live Analytics Dashboard
        </span>
        <span class="text-xs text-slate-400 font-medium hidden sm:inline">&middot; Updated {{ date('d M Y') }}</span>
      </div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
        Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋
      </h1>
      <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
        Monitor your store's total revenue, order fulfillment status, courier dispatches, and sales performance in real-time.
      </p>
    </div>

    <!-- Header Quick Action Buttons -->
    <div class="relative z-10 flex items-center gap-2.5 flex-wrap shrink-0">
      <a href="{{ route('admin.products.create') }}" class="px-4 py-2.5 text-xs font-extrabold rounded-2xl bg-white/10 hover:bg-white/20 text-white border border-white/10 backdrop-blur transition shadow-xs flex items-center gap-1.5">
        <span>➕</span> Add Product
      </a>
      <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 text-xs font-extrabold rounded-2xl bg-brand-600 hover:bg-brand-500 text-white transition shadow-sm flex items-center gap-1.5">
        <span>📦</span> Manage Orders
      </a>
      <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 text-xs font-extrabold rounded-2xl bg-white text-slate-900 hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
        <span>🌐</span> View Shop &rarr;
      </a>
    </div>
  </div>

  <!-- KPI Stat Cards Grid (2 Rows of 3 Spacious Cards) -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    
    <!-- Card 1: Verified Total Revenue (Hero) -->
    <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-emerald-800 uppercase tracking-wider block">Total Verified Revenue</span>
          <span class="text-[11px] text-emerald-600/90 font-medium">All verified orders</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-200/60 flex items-center justify-center text-xl shrink-0">
          💰
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-emerald-700 font-mono tracking-tight">{{ money($revenue) }}</p>
        <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
          {{ number_format($ordersCount) }} Active
        </span>
      </div>
    </div>

    <!-- Card 2: Today's Revenue -->
    <div class="rounded-3xl border border-brand-200 bg-gradient-to-br from-brand-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-brand-800 uppercase tracking-wider block">Today's Sales</span>
          <span class="text-[11px] text-slate-500 font-medium">Real-time daily sales</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-brand-500/10 text-brand-600 border border-brand-200/60 flex items-center justify-center text-xl shrink-0">
          📈
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-slate-900 font-mono tracking-tight">{{ money($todayRevenue) }}</p>
        <span class="text-xs font-semibold text-slate-500">
          Yesterday: <strong class="text-slate-800 font-mono">{{ money($yesterdayRevenue) }}</strong>
        </span>
      </div>
    </div>

    <!-- Card 3: This Month -->
    <div class="rounded-3xl border border-indigo-200 bg-gradient-to-br from-indigo-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-indigo-800 uppercase tracking-wider block">This Month Sales</span>
          <span class="text-[11px] text-indigo-600/90 font-medium">{{ date('F Y') }}</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-indigo-500/10 text-indigo-600 border border-indigo-200/60 flex items-center justify-center text-xl shrink-0">
          📅
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-indigo-700 font-mono tracking-tight">{{ money($thisMonthRevenue) }}</p>
        <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
          {{ date('M') }} Growth
        </span>
      </div>
    </div>

    <!-- Card 4: Average Order Value (AOV) -->
    <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-sky-800 uppercase tracking-wider block">Avg Order Value (AOV)</span>
          <span class="text-[11px] text-sky-600/90 font-medium">Per verified purchase</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-sky-500/10 text-sky-600 border border-sky-200/60 flex items-center justify-center text-xl shrink-0">
          🛒
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-sky-700 font-mono tracking-tight">{{ money($avgOrderValue) }}</p>
        <span class="text-xs font-medium text-slate-400">Basket Avg</span>
      </div>
    </div>

    <!-- Card 5: Active Orders Count -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-slate-600 uppercase tracking-wider block">Total Active Orders</span>
          <span class="text-[11px] text-slate-400 font-medium">Processing &amp; completed</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-700 border border-slate-200/80 flex items-center justify-center text-xl shrink-0">
          📦
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($ordersCount) }}</p>
        <span class="text-xs font-medium text-slate-400">Excludes {{ number_format($cancelledOrdersCount ?? 0) }} cancelled</span>
      </div>
    </div>

    <!-- Card 6: Pending Verification -->
    <div class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-amber-800 uppercase tracking-wider block">Pending Verifications</span>
          <span class="text-[11px] text-amber-700/90 font-medium">Payment approval required</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/60 flex items-center justify-center text-xl shrink-0">
          ⏳
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-amber-700 tracking-tight">{{ number_format($pendingCount) }}</p>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="px-3 py-1 text-xs font-extrabold rounded-full bg-amber-600 hover:bg-amber-700 text-white transition shadow-2xs">
          Review &rarr;
        </a>
      </div>
    </div>

    <!-- Card 7: Unique Visitors Today -->
    <div class="rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-violet-800 uppercase tracking-wider block">Unique Visitors</span>
          <span class="text-[11px] text-violet-700/90 font-medium">Storefront traffic today</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-violet-500/10 text-violet-600 border border-violet-200/60 flex items-center justify-center text-xl shrink-0">
          👥
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-violet-700 tracking-tight">{{ number_format($visitorsToday) }}</p>
        <span class="text-xs font-medium text-slate-500">
          Yesterday: <strong class="text-slate-800 font-mono">{{ number_format($visitorsYesterday) }}</strong>
        </span>
      </div>
    </div>

    <!-- Card 8: Live Product Stock & Inventory -->
    <div class="rounded-3xl border border-rose-200 bg-gradient-to-br from-rose-50/90 via-white to-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all relative overflow-hidden group">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-xs font-extrabold text-rose-800 uppercase tracking-wider block">Live Product Stock</span>
          <span class="text-[11px] text-rose-600/90 font-medium">{{ number_format($productsCount) }} Catalog Products</span>
        </div>
        <div class="h-12 w-12 rounded-2xl bg-rose-500/10 text-rose-600 border border-rose-200/60 flex items-center justify-center text-xl shrink-0">
          🏭
        </div>
      </div>
      <div class="mt-4 flex items-baseline justify-between">
        <p class="text-3xl font-black text-rose-700 tracking-tight font-mono">{{ number_format($totalStockUnits) }} <span class="text-xs font-bold text-slate-500">Units</span></p>
        <a href="{{ route('admin.inventory.index') }}" class="px-2.5 py-1 text-[11px] font-extrabold rounded-full {{ $lowStockCount > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
          {{ $lowStockCount }} Low Stock Alert
        </a>
      </div>
    </div>

  </div>

  <!-- Main Analytics Grid: Chart (Left 2 cols) & Pending Approval List (Right 1 col) -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Monthly Revenue Performance Chart Card -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs lg:col-span-2 flex flex-col justify-between space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-extrabold text-lg text-slate-900">Monthly Revenue Performance</h2>
            <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
              12-Month Series
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            Rolling revenue trend: <strong class="text-slate-800">{{ $firstMonthLabel }} – {{ $lastMonthLabel }}</strong>
          </p>
        </div>

        <!-- KPI Pill & Year Selector Form -->
        <div class="flex items-center gap-3 flex-wrap">
          <div class="hidden sm:flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
            <span class="text-slate-500">🏆 Peak:</span>
            <span class="font-black text-emerald-700 font-mono">{{ $peakMonth['full_label'] ?? 'N/A' }} ({{ money($peakMonth['value'] ?? 0) }})</span>
          </div>

          <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <label for="year" class="text-xs font-bold text-slate-600">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-slate-800 font-extrabold focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
          </form>
        </div>
      </div>

      <!-- 12-Month Bar Chart Graphic -->
      <div class="pt-2">
        <div class="flex items-end gap-2 sm:gap-3 h-52 px-2 border-b border-slate-200 pb-2">
          @foreach($monthlySeries as $index => $point)
            @php
              $heightPercent = max(6, (int) round(($point['value'] / $seriesMax) * 100));
            @endphp
            <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
              
              <!-- Floating Tooltip -->
              <div class="opacity-0 group-hover:opacity-100 transition-all duration-200 absolute -top-12 bg-slate-900 text-white text-[11px] font-bold py-1.5 px-3 rounded-xl shadow-lg pointer-events-none z-20 whitespace-nowrap flex flex-col items-center">
                <span>{{ $point['full_label'] }}</span>
                <span class="text-brand-400 font-mono text-xs">{{ money($point['value']) }}</span>
                <div class="w-2 h-2 bg-slate-900 rotate-45 -mb-1 mt-0.5"></div>
              </div>

              <!-- Bar Element -->
              <div class="w-full rounded-t-xl transition-all duration-300 {{ $point['is_current'] ? 'bg-gradient-to-t from-brand-600 to-amber-500 ring-2 ring-brand-400/50 shadow-md' : ($point['value'] > 0 ? 'bg-gradient-to-t from-slate-700 to-slate-500 group-hover:from-brand-600 group-hover:to-brand-500' : 'bg-slate-100') }}" style="height: {{ $heightPercent }}%">
              </div>
            </div>
          @endforeach
        </div>

        <!-- Month Labels -->
        <div class="mt-3 grid grid-cols-12 text-center text-[11px] font-bold text-slate-400">
          @foreach($monthlySeries as $point)
            <span class="{{ $point['is_current'] ? 'text-brand-600 font-black underline decoration-2 underline-offset-4' : ($point['value'] > 0 ? 'text-slate-700 font-extrabold' : '') }}">
              {{ $point['label'] }}
            </span>
          @endforeach
        </div>
      </div>

      <!-- Footer Chart Summary Stats -->
      <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <div>
          <span>12-Month Total Series Sales: </span>
          <strong class="font-extrabold text-slate-900 font-mono">{{ money($totalSeriesRevenue) }}</strong>
        </div>
        <div class="flex items-center gap-4">
          <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gradient-to-r from-brand-600 to-amber-500"></span> Current Month</span>
          <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-600"></span> Verified Sales</span>
        </div>
      </div>
    </div>

    <!-- Pending Payment Approval Action Widget (Right Sidebar) -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs flex flex-col justify-between space-y-4">
      <div>
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
          <div>
            <h2 class="font-extrabold text-base text-slate-900 flex items-center gap-1.5">
              <span>⏳</span> Pending Approvals
            </h2>
            <p class="text-xs text-slate-400">Orders requiring payment check</p>
          </div>
          <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="text-xs font-extrabold text-brand-600 hover:underline">
            View All &rarr;
          </a>
        </div>

        <div class="space-y-3">
          @forelse($pendingOrders as $order)
            <div class="p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200/80 flex items-center justify-between gap-3 hover:bg-amber-50 transition-colors">
              <div class="min-w-0 flex-1">
                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-black text-brand-700 hover:underline block truncate">
                  {{ $order->order_number }}
                </a>
                <p class="text-xs font-bold text-slate-800 truncate mt-0.5">{{ $order->customer_name }}</p>
                <p class="text-[11px] text-slate-500">{{ $order->paymentMethodLabel() }} &middot; <span class="font-mono font-bold text-slate-900">{{ money($order->total) }}</span></p>
              </div>

              <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="shrink-0">
                @csrf
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition shadow-xs cursor-pointer">
                  ✓ Verify
                </button>
              </form>
            </div>
          @empty
            <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
              <p class="text-3xl mb-1">🎉</p>
              <p class="text-xs font-bold text-slate-700">All clear!</p>
              <p class="text-[11px] text-slate-400 mt-0.5">No orders awaiting payment verification.</p>
            </div>
          @endforelse
        </div>
      </div>

      <!-- Courier Dispatch Quick KPI Summary -->
      <div class="pt-3 border-t border-slate-100 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/80 space-y-2">
        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">🚚 Courier &amp; Fulfillment Summary</span>
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
          <div class="bg-white p-2 rounded-xl border border-slate-200">
            <span class="block text-[10px] text-slate-400 font-bold">Dispatched</span>
            <span class="font-black text-slate-900 font-mono">{{ number_format($dispatchedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-xl border border-slate-200">
            <span class="block text-[10px] text-slate-400 font-bold">Shipped</span>
            <span class="font-black text-brand-600 font-mono">{{ number_format($shippedCount ?? 0) }}</span>
          </div>
          <div class="bg-white p-2 rounded-xl border border-slate-200">
            <span class="block text-[10px] text-slate-400 font-bold">Delivered</span>
            <span class="font-black text-emerald-600 font-mono">{{ number_format($deliveredCount ?? 0) }}</span>
          </div>
        </div>
      </div>

      <!-- Live Inventory & Low Stock Alert Widget -->
      <div class="pt-3 border-t border-slate-100 space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
            <span>🚨</span> Low Stock Alerts (Live)
          </span>
          <a href="{{ route('admin.inventory.index') }}" class="text-[11px] font-bold text-brand-600 hover:underline">
            Manage Inventory &rarr;
          </a>
        </div>

        <div class="space-y-2">
          @forelse($lowStockProducts as $lowItem)
            <div class="p-2.5 bg-rose-50/60 rounded-xl border border-rose-200/70 flex items-center justify-between gap-2">
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate">{{ $lowItem->name }}</p>
                <p class="text-[10px] text-slate-400 font-mono">{{ $lowItem->sku }}</p>
              </div>
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $lowItem->stock_quantity <= 0 ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-800' }} shrink-0">
                {{ $lowItem->stock_quantity <= 0 ? 'Out of Stock' : $lowItem->stock_quantity . ' Left' }}
              </span>
            </div>
          @empty
            <div class="text-center py-4 bg-emerald-50/60 rounded-xl border border-emerald-200/60 text-xs text-emerald-800 font-bold">
              <span>✅ Healthy Stock (All items in stock)</span>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <!-- Top Revenue Products Leaderboard Card -->
  <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div>
        <h2 class="font-extrabold text-lg text-slate-900 flex items-center gap-2">
          <span>🏆</span> Top Revenue-Generating Products
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Best-selling items ranked by total revenue earned from verified orders</p>
      </div>
      <a href="{{ route('admin.products.index') }}" class="text-xs font-extrabold text-brand-600 hover:underline">
        View All Products &rarr;
      </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider">
            <th class="py-3 px-4 w-20">Rank</th>
            <th class="py-3 px-4">Product Details</th>
            <th class="py-3 px-4 text-center">Volume Sold</th>
            <th class="py-3 px-4 text-right">Total Revenue (৳)</th>
            <th class="py-3 px-4 text-center">Live Stock</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($topProducts as $index => $item)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-4 px-4 font-black text-xs">
                @if($index === 0)
                  <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs font-extrabold">🥇 #1</span>
                @elseif($index === 1)
                  <span class="px-2.5 py-1 rounded-xl bg-slate-200 text-slate-800 border border-slate-300 font-extrabold">🥈 #2</span>
                @elseif($index === 2)
                  <span class="px-2.5 py-1 rounded-xl bg-orange-100 text-orange-900 border border-orange-200 font-extrabold">🥉 #3</span>
                @else
                  <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 font-bold">#{{ $index + 1 }}</span>
                @endif
              </td>
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  @if($item->image)
                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-10 w-10 object-cover rounded-xl border border-slate-200 shrink-0" />
                  @else
                    <div class="h-10 w-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-extrabold text-xs shrink-0">
                      {{ substr($item->product_name, 0, 1) }}
                    </div>
                  @endif
                  <div>
                    <span class="font-extrabold text-slate-900 text-xs line-clamp-1 block">{{ $item->product_name }}</span>
                    @if($item->product)
                      <span class="text-[11px] text-slate-400 font-medium">Unit Price: {{ money($item->product->price) }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-800 border border-slate-200">
                  {{ number_format($item->total_units) }} units
                </span>
              </td>
              <td class="py-4 px-4 text-right font-black text-emerald-600 font-mono text-sm">
                {{ money($item->total_revenue) }}
              </td>
              <td class="py-4 px-4 text-center">
                @if($item->product)
                  @if($item->product->stock_quantity <= 0)
                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-rose-100 text-rose-800 border border-rose-200">Out of Stock</span>
                  @elseif($item->product->stock_quantity <= 5)
                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Low ({{ $item->product->stock_quantity }})</span>
                  @else
                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">In Stock ({{ $item->product->stock_quantity }})</span>
                  @endif
                @else
                  <span class="text-xs text-slate-400">N/A</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-8 text-slate-400 text-xs">
                No revenue data recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Orders Table Card -->
  <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div>
        <h2 class="font-extrabold text-lg text-slate-900 flex items-center gap-2">
          <span>📦</span> Recent Orders Activity
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Latest incoming purchases across storefront</p>
      </div>
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-extrabold text-brand-600 hover:underline">
        View All Orders &rarr;
      </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider">
            <th class="py-3 px-4">Order Number</th>
            <th class="py-3 px-4">Customer Name</th>
            <th class="py-3 px-4 text-center">Total Value</th>
            <th class="py-3 px-4 text-center">Payment Status</th>
            <th class="py-3 px-4 text-center">Fulfillment Status</th>
            <th class="py-3 px-4 text-right">Order Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @foreach($recentOrders as $order)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3.5 px-4">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-brand-600 hover:underline">
                  {{ $order->order_number }}
                </a>
              </td>
              <td class="py-3.5 px-4 font-bold text-slate-800">{{ $order->customer_name }}</td>
              <td class="py-3.5 px-4 text-center font-extrabold text-slate-900 font-mono">{{ money($order->total) }}</td>
              <td class="py-3.5 px-4 text-center">
                <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                  {{ ucfirst($order->payment_status) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                  {{ ucfirst($order->status) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right text-slate-500 font-medium">{{ $order->created_at->format('d M, Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
