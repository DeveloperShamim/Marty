@extends('layouts.admin')
@section('title', 'Sales & Profit Analytics')

@section('content')
<div class="space-y-6">

  {{-- Page Header & Filters --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-5 sm:p-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </span>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Sales &amp; Profit Analytics</h1>
            <p class="text-xs sm:text-sm text-gray-500">Real-time revenue, COGS, net profit, AOV and courier financial impact</p>
          </div>
        </div>
      </div>

      {{-- Filters Form --}}
      <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex items-center gap-2 flex-wrap" id="analyticsFilterForm">
        
        {{-- Channel Filter --}}
        <select name="channel" onchange="this.form.submit()" class="text-xs font-semibold px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
          <option value="all" @selected($channel === 'all')>All Channels</option>
          <option value="online" @selected($channel === 'online')>🌐 Online Store</option>
          <option value="pos" @selected($channel === 'pos')>🖥️ POS Counter</option>
        </select>

        {{-- Date Range Preset --}}
        <select name="range" id="rangeSelect" onchange="handleRangeChange(this.value)" class="text-xs font-bold px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
          <option value="today" @selected($range === 'today')>Today</option>
          <option value="yesterday" @selected($range === 'yesterday')>Yesterday</option>
          <option value="last_7_days" @selected($range === 'last_7_days')>Last 7 Days</option>
          <option value="last_30_days" @selected($range === 'last_30_days')>Last 30 Days</option>
          <option value="this_month" @selected($range === 'this_month')>This Month</option>
          <option value="last_month" @selected($range === 'last_month')>Last Month</option>
          <option value="this_year" @selected($range === 'this_year')>This Year</option>
          <option value="custom" @selected($range === 'custom')>Custom Date Range...</option>
        </select>

        {{-- Custom Date Inputs (hidden unless custom selected) --}}
        <div id="customDateWrap" class="{{ $range === 'custom' ? 'flex' : 'hidden' }} items-center gap-1.5">
          <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="text-xs px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg">
          <span class="text-xs text-gray-400">to</span>
          <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="text-xs px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg">
          <button type="submit" class="px-3 py-1.5 bg-brand-600 text-white text-xs font-bold rounded-lg hover:bg-brand-700">Apply</button>
        </div>

        {{-- Export CSV Button --}}
        <a href="{{ route('admin.analytics.export', request()->all()) }}" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-gray-900 hover:bg-black text-white shadow-2xs transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Export CSV
        </a>

      </form>
    </div>

    <div class="mt-2 text-xs font-semibold text-brand-700 flex items-center gap-1.5">
      <span class="w-2 h-2 rounded-full bg-brand-500"></span>
      Showing report for: <strong>{{ $rangeLabel }}</strong>
    </div>
  </div>

  {{-- 4 Primary KPI Cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    {{-- Card 1: Gross Revenue --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between text-xs text-gray-500">
        <span class="font-bold uppercase tracking-wider">Gross Sales</span>
        <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
      </div>
      <div class="text-2xl font-black text-gray-900">৳{{ number_format($grossRevenue, 2) }}</div>
      <div class="text-[11px] text-gray-400 font-medium">From {{ $totalOrdersCount }} confirmed orders</div>
    </div>

    {{-- Card 2: Cost of Goods Sold (COGS) --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between text-xs text-gray-500">
        <span class="font-bold uppercase tracking-wider">COGS (Product Cost)</span>
        <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </span>
      </div>
      <div class="text-2xl font-black text-indigo-700">৳{{ number_format($cogs, 2) }}</div>
      <div class="text-[11px] text-gray-400 font-medium">Actual wholesale buying cost</div>
    </div>

    {{-- Card 3: Net Profit (Factoring in returns) --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between text-xs text-gray-500">
        <span class="font-bold uppercase tracking-wider">Net Profit</span>
        <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-xs">
          {{ number_format($profitMargin, 1) }}% Margin
        </span>
      </div>
      <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
        ৳{{ number_format($netProfit, 2) }}
      </div>
      <div class="text-[11px] text-gray-400 font-medium">
        Gross ৳{{ number_format($grossProfit, 0) }} &minus; Courier Loss ৳{{ number_format($courierLoss, 0) }}
      </div>
    </div>

    {{-- Card 4: Average Order Value (AOV) --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between text-xs text-gray-500">
        <span class="font-bold uppercase tracking-wider">Average Order Value</span>
        <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </span>
      </div>
      <div class="text-2xl font-black text-amber-700">৳{{ number_format($aov, 2) }}</div>
      <div class="text-[11px] text-gray-400 font-medium">Average spent per checkout</div>
    </div>

  </div>

  {{-- TRUE IN-POCKET NET PROFIT & EXPENSE / ADS BANNER --}}
  <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 sm:p-6 text-white shadow-md">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-700/60 pb-5">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase tracking-wider border border-emerald-500/30">True P&amp;L</span>
          <span class="text-xs text-slate-400">All Expenses &amp; Ad Spend Deducted</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-white mt-1">Real In-Pocket Net Profit</h2>
      </div>

      <div class="flex items-baseline gap-3">
        <div class="text-2xl sm:text-4xl font-black tracking-tight {{ $trueNetProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
          ৳{{ number_format($trueNetProfit, 2) }}
        </div>
        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $trueProfitMargin >= 15 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }} border border-white/10">
          {{ number_format($trueProfitMargin, 1) }}% Real Margin
        </span>
      </div>
    </div>

    {{-- Expense & Ad ROAS Breakdown Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-5">
      
      {{-- Facebook Ads & ROAS --}}
      <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
        <div class="text-[11px] text-blue-300 font-bold uppercase tracking-wider flex items-center justify-between">
          <span>📣 Meta / Facebook Ads</span>
          @if($marketingExpense > 0)
            <span class="px-1.5 py-0.2 rounded text-[10px] font-black {{ $fbRoas >= 3 ? 'bg-emerald-500/20 text-emerald-300' : ($fbRoas >= 1.5 ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300') }}">
              {{ number_format($fbRoas, 1) }}x ROAS
            </span>
          @endif
        </div>
        <div class="text-lg font-black text-white mt-1">৳{{ number_format($marketingExpense, 2) }}</div>
        <div class="text-[11px] text-slate-400 mt-0.5">
          @if($fbOrdersCount > 0)
            CPA: <strong>৳{{ number_format($fbCpa, 0) }}</strong> / order ({{ $fbOrdersCount }} orders)
          @else
            No Facebook tagged orders yet
          @endif
        </div>
      </div>

      {{-- Sourcing & Travel Trips --}}
      <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
        <div class="text-[11px] text-amber-300 font-bold uppercase tracking-wider">🚗 Sourcing &amp; Travel</div>
        <div class="text-lg font-black text-white mt-1">৳{{ number_format($sourcingExpense, 2) }}</div>
        <div class="text-[11px] text-slate-400 mt-0.5">Market visits, fares &amp; sundries</div>
      </div>

      {{-- Packaging Supplies --}}
      <div class="bg-white/5 rounded-xl p-3.5 border border-white/10">
        <div class="text-[11px] text-purple-300 font-bold uppercase tracking-wider">📦 Packaging Materials</div>
        <div class="text-lg font-black text-white mt-1">৳{{ number_format($packagingExpense, 2) }}</div>
        <div class="text-[11px] text-slate-400 mt-0.5">Poly bags, stickers &amp; boxes</div>
      </div>

      {{-- Total Operating Deductions --}}
      <div class="bg-white/5 rounded-xl p-3.5 border border-white/10 flex flex-col justify-between">
        <div>
          <div class="text-[11px] text-rose-300 font-bold uppercase tracking-wider">Total Operating Costs</div>
          <div class="text-lg font-black text-rose-300 mt-1">-৳{{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="mt-2">
          <a href="{{ route('admin.expenses.index') }}" class="text-[11px] text-brand-300 hover:text-white underline font-bold flex items-center gap-1">
            Manage Expenses &amp; Ads &rarr;
          </a>
        </div>
      </div>

    </div>
  </div>

  {{-- Secondary Insight Breakdown Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    
    {{-- Channel Performance (Online vs POS) --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="text-sm font-extrabold text-gray-900">Sales Channel Split</h3>
        <span class="text-xs text-gray-400">Online vs In-Store</span>
      </div>

      <div class="space-y-3">
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
              🌐 Online Storefront
            </span>
            <strong class="text-sm font-black text-gray-900">৳{{ number_format($onlineRevenue, 2) }}</strong>
          </div>
          <div class="flex items-center justify-between text-[11px] text-gray-500">
            <span>{{ $onlineOrdersCount }} orders</span>
            <span>AOV: <strong>৳{{ number_format($onlineAov, 0) }}</strong></span>
          </div>
        </div>

        <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-200 space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
              🖥️ POS Counter Sale
            </span>
            <strong class="text-sm font-black text-emerald-800">৳{{ number_format($posRevenue, 2) }}</strong>
          </div>
          <div class="flex items-center justify-between text-[11px] text-emerald-700">
            <span>{{ $posOrdersCount }} orders</span>
            <span>AOV: <strong>৳{{ number_format($posAov, 0) }}</strong></span>
          </div>
        </div>
      </div>
    </div>

    {{-- Courier Returns Financial Impact --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="text-sm font-extrabold text-gray-900">Courier Return Analysis</h3>
        <span class="text-xs text-gray-400">Total Returns: {{ $returnedCount }}</span>
      </div>

      <div class="space-y-3 text-xs">
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
          <div>
            <div class="font-bold text-emerald-900">✓ Buyer Paid Delivery</div>
            <div class="text-[10px] text-emerald-700">{{ $paidReturnsCount }} parcels (Delivery fee recovered)</div>
          </div>
          <span class="font-extrabold text-emerald-800">৳0 Loss</span>
        </div>

        <div class="flex items-center justify-between p-2.5 rounded-xl bg-rose-50 border border-rose-200">
          <div>
            <div class="font-bold text-rose-900">❌ Failed Pickup (Unpaid)</div>
            <div class="text-[10px] text-rose-700">{{ $unpaidReturnsCount }} parcels (Store bears shipping)</div>
          </div>
          <span class="font-black text-rose-700">-৳{{ number_format($courierLoss, 2) }}</span>
        </div>

        <div class="text-[11px] text-gray-400 text-center">
          Courier loss is deducted automatically from Gross Profit.
        </div>
      </div>
    </div>

    {{-- Profit Margin Progress --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h3 class="text-sm font-extrabold text-gray-900">Net Profit Margin</h3>
        <span class="text-xs font-bold text-emerald-600">{{ number_format($profitMargin, 1) }}%</span>
      </div>

      <div class="space-y-2">
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
          <div class="bg-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $profitMargin)) }}%"></div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs pt-2">
          <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-200">
            <span class="text-gray-400 block text-[10px]">Net Sales</span>
            <span class="font-black text-gray-900">৳{{ number_format($grossRevenue, 0) }}</span>
          </div>
          <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-200">
            <span class="text-gray-400 block text-[10px]">Net Profit</span>
            <span class="font-black text-emerald-700">৳{{ number_format($netProfit, 0) }}</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Visual Trend Chart: Revenue vs Profit over Time --}}
  <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200/90 shadow-2xs space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3.5">
      <div>
        <h2 class="text-sm sm:text-base font-extrabold text-gray-900">Financial Trend (Revenue vs Net Profit)</h2>
        <p class="text-xs text-gray-500">Historical performance across the selected date range</p>
      </div>
    </div>

    <div class="h-72 w-full">
      <canvas id="financialChart"></canvas>
    </div>
  </div>

  {{-- Top 10 Most Profitable Products Table --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
      <div>
        <h3 class="text-sm sm:text-base font-extrabold text-gray-900">Top 10 Most Profitable Products</h3>
        <p class="text-xs text-gray-500">Ranked by actual profit generated (Sales Price &minus; Buying Cost)</p>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="py-3 px-4 w-12 text-center">#</th>
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4 text-center">Units Sold</th>
            <th class="py-3 px-4 text-right">Revenue Generated</th>
            <th class="py-3 px-4 text-right">Wholesale Cost</th>
            <th class="py-3 px-4 text-right">Net Profit</th>
            <th class="py-3 px-4 text-right">Profit Margin</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($topProfitable as $idx => $tp)
            @php
              $rev = (float) $tp->total_revenue;
              $profit = (float) $tp->total_profit;
              $margin = $rev > 0 ? (($profit / $rev) * 100) : 0;
            @endphp
            <tr class="hover:bg-gray-50/80 transition-colors">
              <td class="py-3 px-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <img src="{{ image_url($tp->image, $tp->product_name) }}" class="w-9 h-9 rounded-lg object-cover bg-gray-100 border border-gray-200 shrink-0">
                  <span class="font-bold text-gray-900">{{ $tp->product_name }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-center font-extrabold text-gray-700">{{ $tp->units_sold }}</td>
              <td class="py-3 px-4 text-right font-extrabold text-gray-900">৳{{ number_format($rev, 2) }}</td>
              <td class="py-3 px-4 text-right text-indigo-700 font-semibold">৳{{ number_format($tp->total_cost, 2) }}</td>
              <td class="py-3 px-4 text-right font-black text-emerald-600">৳{{ number_format($profit, 2) }}</td>
              <td class="py-3 px-4 text-right">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  {{ number_format($margin, 1) }}%
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-8 text-center text-gray-400">No sales recorded for this date range.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  function handleRangeChange(val) {
    const wrap = document.getElementById('customDateWrap');
    if (val === 'custom') {
      wrap.classList.remove('hidden');
      wrap.classList.add('flex');
    } else {
      wrap.classList.add('hidden');
      wrap.classList.remove('flex');
      document.getElementById('analyticsFilterForm').submit();
    }
  }

  // Initialize Financial Trend Chart
  document.addEventListener('DOMContentLoaded', function() {
    const trendData = @json($trendData);
    const labels = trendData.map(d => d.label);
    const revenues = trendData.map(d => d.revenue);
    const profits = trendData.map(d => d.profit);

    const ctx = document.getElementById('financialChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Revenue (৳)',
            data: revenues,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.3
          },
          {
            label: 'Net Profit (৳)',
            data: profits,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: { font: { weight: 'bold', size: 11 } }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) { return '৳' + value.toLocaleString(); }
            }
          }
        }
      }
    });
  });
</script>
@endpush
@endsection
