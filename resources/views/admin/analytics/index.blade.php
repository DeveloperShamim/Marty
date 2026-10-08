@extends('layouts.admin')
@section('title', 'Analytics')
@section('subtitle', 'Revenue, product cost, profit, order value and courier losses.')

@section('page-actions')
  <a href="{{ route('admin.analytics.export', request()->all()) }}" class="pill-btn">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
    Export CSV
  </a>
@endsection

@section('content')
<div class="space-y-4">

  {{-- Filters --}}
  <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 flex-wrap" id="analyticsFilterForm">
    <div class="grid grid-cols-2 sm:flex items-center gap-2">
      <label class="sr-only" for="channelSelect">Sales channel</label>
      <select name="channel" id="channelSelect" onchange="this.form.submit()" class="h-10 rounded-full bg-white shadow-panel border-transparent px-4 text-sm text-gray-800 cursor-pointer">
        <option value="all" @selected($channel === 'all')>All channels</option>
        <option value="online" @selected($channel === 'online')>Online store</option>
        <option value="pos" @selected($channel === 'pos')>POS counter</option>
      </select>
      <label class="sr-only" for="rangeSelect">Date range</label>
      <select name="range" id="rangeSelect" onchange="handleRangeChange(this.value)" class="h-10 rounded-full bg-white shadow-panel border-transparent px-4 text-sm text-gray-800 cursor-pointer">
        <option value="today" @selected($range === 'today')>Today</option>
        <option value="yesterday" @selected($range === 'yesterday')>Yesterday</option>
        <option value="last_7_days" @selected($range === 'last_7_days')>Last 7 days</option>
        <option value="last_30_days" @selected($range === 'last_30_days')>Last 30 days</option>
        <option value="this_month" @selected($range === 'this_month')>This month</option>
        <option value="last_month" @selected($range === 'last_month')>Last month</option>
        <option value="this_year" @selected($range === 'this_year')>This year</option>
        <option value="custom" @selected($range === 'custom')>Custom range</option>
      </select>
    </div>

    <div id="customDateWrap" class="{{ $range === 'custom' ? 'flex' : 'hidden' }} items-center gap-1.5 flex-wrap">
      <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="h-10 rounded-full bg-white shadow-panel border-transparent px-3.5 text-sm" aria-label="Start date">
      <span class="text-xs text-gray-500">to</span>
      <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="h-10 rounded-full bg-white shadow-panel border-transparent px-3.5 text-sm" aria-label="End date">
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold" style="background: var(--brand-dark);">Apply</button>
    </div>

    <p class="text-xs text-gray-500 sm:ml-auto">Showing <span class="font-medium text-gray-800">{{ $rangeLabel }}</span></p>
  </form>

  {{-- Primary stats --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Gross sales</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-emerald-50 text-emerald-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">৳{{ number_format($grossRevenue, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">{{ $totalOrdersCount }} confirmed orders</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Product cost</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-gray-100 text-gray-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">৳{{ number_format($cogs, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Wholesale buying cost</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Profit before expenses</span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 shrink-0">{{ number_format($profitMargin, 1) }}%</span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold tabular-nums truncate {{ $netProfit >= 0 ? 'text-gray-900' : 'text-rose-600' }}">৳{{ number_format($netProfit, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">
        Gross ৳{{ number_format($grossProfit, 0) }} &minus; courier loss ৳{{ number_format($courierLoss, 0) }}@if($freeDeliveryCost > 0) &minus; free delivery ৳{{ number_format($freeDeliveryCost, 0) }}@endif
      </p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Average order</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">৳{{ number_format($aov, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Per checkout</p>
    </div>
  </div>

  {{-- True profit after expenses --}}
  <section class="panel p-4 sm:p-5">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Net profit after expenses</h2>
        <p class="text-xs text-gray-500 mt-0.5">After product cost, courier return losses and logged expenses.</p>
      </div>
      <div class="flex items-center gap-2">
        <p class="text-xl sm:text-2xl font-semibold tabular-nums {{ $trueNetProfit >= 0 ? 'text-gray-900' : 'text-rose-600' }}">৳{{ number_format($trueNetProfit, 2) }}</p>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $trueProfitMargin >= 15 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ number_format($trueProfitMargin, 1) }}% margin</span>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2.5">
      <div class="rounded-2xl bg-gray-50 p-3 min-w-0">
        <div class="flex items-center justify-between gap-x-2 gap-y-1 flex-wrap">
          <span class="text-xs text-gray-500">Facebook ads</span>
          @if($marketingExpense > 0)
            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $fbRoas >= 3 ? 'bg-emerald-50 text-emerald-700' : ($fbRoas >= 1.5 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">{{ number_format($fbRoas, 1) }}x ROAS</span>
          @endif
        </div>
        <p class="mt-1 text-[15px] font-semibold text-gray-900 tabular-nums">৳{{ number_format($marketingExpense, 2) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">
          @if($fbOrdersCount > 0)
            ৳{{ number_format($fbCpa, 0) }} per order ({{ $fbOrdersCount }} orders)
          @else
            No Facebook orders yet
          @endif
        </p>
      </div>
      <div class="rounded-2xl bg-gray-50 p-3 min-w-0">
        <span class="text-xs text-gray-500">Sourcing and travel</span>
        <p class="mt-1 text-[15px] font-semibold text-gray-900 tabular-nums">৳{{ number_format($sourcingExpense, 2) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">Market visits and fares</p>
      </div>
      <div class="rounded-2xl bg-gray-50 p-3 min-w-0">
        <span class="text-xs text-gray-500">Packaging</span>
        <p class="mt-1 text-[15px] font-semibold text-gray-900 tabular-nums">৳{{ number_format($packagingExpense, 2) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">Bags, stickers, boxes</p>
      </div>
      <div class="rounded-2xl bg-gray-50 p-3 min-w-0">
        <span class="text-xs text-gray-500">Total costs</span>
        <p class="mt-1 text-[15px] font-semibold text-rose-600 tabular-nums">-৳{{ number_format($totalExpenses, 2) }}</p>
        <a href="{{ route('admin.expenses.index') }}" class="text-[11px] font-medium text-gray-600 hover:text-gray-900 hover:underline">Manage expenses</a>
      </div>
    </div>
  </section>

  {{-- Insights --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <section class="panel p-4 sm:p-5">
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-[15px] font-semibold text-gray-900">Sales channels</h2>
        <span class="text-xs text-gray-500">Online vs in store</span>
      </div>
      <div class="mt-3 divide-y divide-gray-100">
        <div class="py-2.5 flex items-center justify-between gap-3">
          <div>
            <p class="text-[13px] font-medium text-gray-800">Online store</p>
            <p class="text-[11px] text-gray-500">{{ $onlineOrdersCount }} orders · avg ৳{{ number_format($onlineAov, 0) }}</p>
          </div>
          <p class="text-sm font-semibold text-gray-900 tabular-nums">৳{{ number_format($onlineRevenue, 2) }}</p>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-3">
          <div>
            <p class="text-[13px] font-medium text-gray-800">POS counter</p>
            <p class="text-[11px] text-gray-500">{{ $posOrdersCount }} orders · avg ৳{{ number_format($posAov, 0) }}</p>
          </div>
          <p class="text-sm font-semibold text-gray-900 tabular-nums">৳{{ number_format($posRevenue, 2) }}</p>
        </div>
      </div>
    </section>

    <section class="panel p-4 sm:p-5">
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-[15px] font-semibold text-gray-900">Courier returns</h2>
        <span class="text-xs text-gray-500">{{ $returnedCount }} returned</span>
      </div>
      <div class="mt-3 divide-y divide-gray-100">
        <div class="py-2.5 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[13px] font-medium text-gray-800">Buyer paid delivery</p>
            <p class="text-[11px] text-gray-500">{{ $paidReturnsCount }} parcels, fee recovered</p>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 shrink-0">৳0 loss</span>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[13px] font-medium text-gray-800">Failed pickup (unpaid)</p>
            <p class="text-[11px] text-gray-500">{{ $unpaidReturnsCount }} parcels, store paid shipping</p>
          </div>
          <span class="text-sm font-semibold text-rose-600 tabular-nums shrink-0">-৳{{ number_format($courierLoss, 2) }}</span>
        </div>
        <div class="py-2.5 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[13px] font-medium text-gray-800">Free delivery cost</p>
            <p class="text-[11px] text-gray-500">{{ $freeDeliveryOrders }} {{ \Illuminate\Support\Str::plural('order', $freeDeliveryOrders) }} shipped free</p>
          </div>
          <span class="text-sm font-semibold text-amber-700 tabular-nums shrink-0">-৳{{ number_format($freeDeliveryCost, 2) }}</span>
        </div>
      </div>
      <p class="mt-2 text-[11px] text-gray-400">Both are deducted from gross profit automatically.</p>
    </section>

    <section class="panel p-4 sm:p-5">
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-[15px] font-semibold text-gray-900">Net profit margin</h2>
        <span class="text-sm font-semibold tabular-nums {{ $trueProfitMargin >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">{{ number_format($trueProfitMargin, 1) }}%</span>
      </div>
      <div class="mt-4 w-full bg-gray-100 rounded-full h-2 overflow-hidden">
        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $trueProfitMargin)) }}%"></div>
      </div>
      <div class="mt-4 grid grid-cols-2 gap-2.5">
        <div class="rounded-2xl bg-gray-50 p-3">
          <span class="text-[11px] text-gray-500">Net sales</span>
          <p class="text-sm font-semibold text-gray-900 tabular-nums">৳{{ number_format($grossRevenue, 0) }}</p>
        </div>
        <div class="rounded-2xl bg-gray-50 p-3">
          <span class="text-[11px] text-gray-500">Net profit</span>
          <p class="text-sm font-semibold tabular-nums {{ $trueNetProfit >= 0 ? 'text-gray-900' : 'text-rose-600' }}">৳{{ number_format($trueNetProfit, 0) }}</p>
        </div>
      </div>
    </section>

  </div>

  {{-- Trend chart --}}
  <section class="panel p-4 sm:p-5">
    <h2 class="text-[15px] font-semibold text-gray-900">Revenue and net profit</h2>
    <p class="text-xs text-gray-500 mt-0.5">Across the selected date range.</p>
    <div class="mt-3 h-64 sm:h-72 w-full">
      <canvas id="financialChart"></canvas>
    </div>
  </section>

  {{-- Top products --}}
  <div class="card overflow-hidden">
    <div class="p-4 sm:p-5">
      <h2 class="text-[15px] font-semibold text-gray-900">Most profitable products</h2>
      <p class="text-xs text-gray-500 mt-0.5">Top 10 by profit (sale price minus buying cost).</p>
    </div>

    {{-- Phone list --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($topProfitable as $idx => $tp)
        @php
          $rev = (float) $tp->total_revenue;
          $profit = (float) $tp->total_profit;
          $margin = $rev > 0 ? (($profit / $rev) * 100) : 0;
        @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3 flex items-center gap-3">
          <img src="{{ image_url($tp->image, $tp->product_name) }}" alt="" class="w-10 h-10 rounded-xl object-cover bg-gray-100 shrink-0">
          <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $tp->product_name }}</p>
            <p class="text-[11px] text-gray-500 tabular-nums">{{ $tp->units_sold }} sold · ৳{{ number_format($rev, 0) }} revenue</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-[13px] font-semibold text-emerald-700 tabular-nums">৳{{ number_format($profit, 0) }}</p>
            <p class="text-[11px] text-gray-500 tabular-nums">{{ number_format($margin, 1) }}%</p>
          </div>
        </article>
      @empty
        <div class="py-8 text-center text-sm text-gray-500">No sales recorded for this date range.</div>
      @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="whitespace-nowrap border-y border-gray-100">
            <th class="py-3 px-4 w-12 text-center">#</th>
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4 text-right">Sold</th>
            <th class="py-3 px-4 text-right">Revenue</th>
            <th class="py-3 px-4 text-right">Cost</th>
            <th class="py-3 px-4 text-right">Profit</th>
            <th class="py-3 px-4 text-right">Margin</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($topProfitable as $idx => $tp)
            @php
              $rev = (float) $tp->total_revenue;
              $profit = (float) $tp->total_profit;
              $margin = $rev > 0 ? (($profit / $rev) * 100) : 0;
            @endphp
            <tr>
              <td class="py-3 px-4 text-center text-gray-400 tabular-nums">{{ $idx + 1 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <img src="{{ image_url($tp->image, $tp->product_name) }}" alt="" class="w-9 h-9 rounded-xl object-cover bg-gray-100 shrink-0">
                  <span class="font-semibold text-gray-900">{{ $tp->product_name }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-right text-gray-700 tabular-nums">{{ $tp->units_sold }}</td>
              <td class="py-3 px-4 text-right text-gray-900 tabular-nums">৳{{ number_format($rev, 2) }}</td>
              <td class="py-3 px-4 text-right text-gray-500 tabular-nums">৳{{ number_format($tp->total_cost, 2) }}</td>
              <td class="py-3 px-4 text-right font-semibold text-emerald-700 tabular-nums">৳{{ number_format($profit, 2) }}</td>
              <td class="py-3 px-4 text-right">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">{{ number_format($margin, 1) }}%</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="py-8 text-center text-gray-500 text-sm">No sales recorded for this date range.</td></tr>
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
            labels: { font: { size: 11 }, boxWidth: 10 }
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
