@extends('layouts.admin')
@section('title', 'Dashboard & Revenue Analytics')

@section('content')
<div class="space-y-6">
  <!-- Revenue & Stat Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Orders</p>
      <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($ordersCount) }}</p>
      <p class="text-[11px] text-slate-400 mt-1">Excludes {{ number_format($cancelledOrdersCount ?? 0) }} cancelled</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Verified Revenue</p>
      <p class="mt-1 text-2xl font-extrabold text-emerald-600">{{ money($revenue) }}</p>
      <p class="text-[11px] text-emerald-600/80 mt-1">Excludes cancelled orders</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Today's Revenue</p>
      <p class="mt-1 text-2xl font-extrabold text-primary">{{ money($todayRevenue) }}</p>
      <p class="text-[11px] text-gray-400 mt-1">
        Yesterday: <span class="font-medium text-slate-600">{{ money($yesterdayRevenue) }}</span>
      </p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">This Month</p>
      <p class="mt-1 text-2xl font-extrabold text-indigo-600">{{ money($thisMonthRevenue) }}</p>
      <p class="text-[11px] text-indigo-600/80 mt-1">{{ date('F Y') }}</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Avg Order Value (AOV)</p>
      <p class="mt-1 text-2xl font-extrabold text-sky-600">{{ money($avgOrderValue) }}</p>
      <p class="text-[11px] text-sky-600/80 mt-1">Per verified order</p>
    </div>

    <div class="card p-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending Verification</p>
      <p class="mt-1 text-2xl font-extrabold text-amber-600">{{ number_format($pendingCount) }}</p>
      <p class="text-[11px] text-amber-600/80 mt-1">Needs payment check</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Monthly Revenue Performance Chart (Last 12 Months ending at Current Month) -->
    <div class="card p-5 lg:col-span-2 flex flex-col justify-between">
      <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="font-bold text-slate-800">Monthly Revenue Performance</h2>
            <p class="text-xs text-slate-400 mt-0.5">12-Month rolling trend: <strong>{{ $firstMonthLabel }} – {{ $lastMonthLabel }}</strong></p>
          </div>

          <!-- Year Selector Form -->
          <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <label for="year" class="text-xs font-semibold text-slate-500">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" class="text-xs bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-sm cursor-pointer">
              @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
          </form>
        </div>
      </div>

      <!-- 12-Month Bar Chart (Last bar = Current Month) -->
      <div class="mt-6 flex items-end gap-2 h-48 px-1">
        @foreach($monthlySeries as $index => $point)
          <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
            <div class="w-full rounded-t transition-all {{ $point['is_current'] ? 'bg-primary ring-2 ring-primary/40 shadow-sm' : 'bg-primary/75 hover:bg-primary' }}" style="height: {{ max(4, (int) round($point['value'] / $seriesMax * 100)) }}%"></div>
            
            <!-- Hover Tooltip -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 bg-slate-900 text-white text-[10px] py-1 px-2.5 rounded shadow pointer-events-none z-10 whitespace-nowrap">
              {{ $point['full_label'] }}: {{ money($point['value']) }}
            </div>
          </div>
        @endforeach
      </div>

      <!-- Month Labels -->
      <div class="mt-3 grid grid-cols-12 text-center text-[10px] font-semibold text-gray-400 border-t pt-2">
        @foreach($monthlySeries as $point)
          <span class="{{ $point['is_current'] ? 'text-primary font-extrabold underline decoration-2 underline-offset-4' : ($point['value'] > 0 ? 'text-slate-700 font-bold' : '') }}">
            {{ $point['label'] }}
          </span>
        @endforeach
      </div>
    </div>

    <!-- Pending Payment Verification Sidebar -->
    <div class="card p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-slate-800">Pending Verification</h2>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="text-xs font-semibold text-primary hover:underline">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($pendingOrders as $order)
          <div class="flex items-center gap-3 p-2 bg-amber-50/50 rounded-xl border border-amber-100">
            <div class="flex-1 min-w-0">
              <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-bold text-primary hover:underline">{{ $order->order_number }}</a>
              <p class="text-xs text-gray-500 truncate">{{ $order->customer_name }} &middot; {{ $order->paymentMethodLabel() }}</p>
            </div>
            <span class="text-sm font-extrabold text-slate-800">{{ money($order->total) }}</span>
            <form method="POST" action="{{ route('admin.orders.verify', $order) }}">
              @csrf
              <button class="px-2.5 py-1 text-xs font-bold rounded-lg bg-primary hover:bg-primary-dark text-white transition">Verify</button>
            </form>
          </div>
        @empty
          <div class="text-center py-8">
            <p class="text-2xl mb-1">🎉</p>
            <p class="text-sm text-gray-400">No pending payment verifications</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Top Revenue-Generating Products Leaderboard -->
  <div class="card p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-bold text-slate-800">🏆 Top Revenue-Generating Products</h2>
        <p class="text-xs text-slate-400">Best-selling items ranked by total revenue earned</p>
      </div>
      <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-primary hover:underline">View all products</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 rounded-xl">
          <tr>
            <th class="px-4 py-3 font-semibold rounded-l-xl"># Rank</th>
            <th class="px-4 py-3 font-semibold">Product Name</th>
            <th class="px-4 py-3 font-semibold text-center">Units Sold</th>
            <th class="px-4 py-3 font-semibold text-right">Total Revenue (৳)</th>
            <th class="px-4 py-3 font-semibold text-center rounded-r-xl">Stock Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($topProducts as $index => $item)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="px-4 py-3 font-bold text-slate-400">
                @if($index === 0) 🥇 #1 @elseif($index === 1) 🥈 #2 @elseif($index === 2) 🥉 #3 @else #{{ $index + 1 }} @endif
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  @if($item->image)
                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200 shrink-0" />
                  @else
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 font-bold text-xs shrink-0">
                      {{ substr($item->product_name, 0, 1) }}
                    </div>
                  @endif
                  <div>
                    <span class="font-bold text-slate-800 line-clamp-1">{{ $item->product_name }}</span>
                    @if($item->product)
                      <span class="text-xs text-slate-400">Price: {{ money($item->product->price) }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                  {{ number_format($item->total_units) }} units
                </span>
              </td>
              <td class="px-4 py-3 text-right font-extrabold text-emerald-600 text-base">
                {{ money($item->total_revenue) }}
              </td>
              <td class="px-4 py-3 text-center">
                @if($item->product)
                  @if($item->product->stock_quantity <= 0)
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">Out of Stock</span>
                  @elseif($item->product->stock_quantity <= 5)
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700">Low Stock ({{ $item->product->stock_quantity }})</span>
                  @else
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">In Stock ({{ $item->product->stock_quantity }})</span>
                  @endif
                @else
                  <span class="text-xs text-slate-400">N/A</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-6 text-slate-400 text-sm">
                No revenue data recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Orders Table -->
  <div class="card">
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
      <h2 class="font-bold text-slate-800">Recent Orders</h2>
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-primary hover:underline">All orders</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500 bg-gray-50">
          <tr>
            <th class="px-5 py-3 font-medium">Order</th>
            <th class="px-5 py-3 font-medium">Customer</th>
            <th class="px-5 py-3 font-medium">Total</th>
            <th class="px-5 py-3 font-medium">Payment</th>
            <th class="px-5 py-3 font-medium">Status</th>
            <th class="px-5 py-3 font-medium">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($recentOrders as $order)
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline">{{ $order->order_number }}</a></td>
              <td class="px-5 py-3 font-medium text-slate-700">{{ $order->customer_name }}</td>
              <td class="px-5 py-3 font-bold text-slate-900">{{ money($order->total) }}</td>
              <td class="px-5 py-3">{{ $order->paymentMethodLabel() }} · <span class="px-2 py-0.5 text-xs rounded-full {{ $order->paymentBadge() }}">{{ ucfirst($order->payment_status) }}</span></td>
              <td class="px-5 py-3"><span class="px-2 py-1 text-xs rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span></td>
              <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_at->format('d M, Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
