@extends('layouts.admin')
@section('title', 'Orders Management')

@section('content')
<div class="space-y-6 max-w-full">
  <!-- Top Header Title & Quick KPI Summary -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>📦</span> Orders Management
      </h2>
      <p class="text-xs text-gray-500 mt-1">Manage customer purchases, verify payments, track courier dispatches, and print invoices.</p>
    </div>

    <!-- Quick Stats Badges -->
    <div class="flex items-center gap-2 text-xs flex-wrap">
      <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
        <span class="text-slate-400 font-medium">Pending Checks:</span>
        <strong class="text-amber-700 font-mono font-black ml-1">{{ $counts['pending_verification'] ?? 0 }}</strong>
      </div>
      <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
        <span class="text-slate-400 font-medium">Confirmed:</span>
        <strong class="text-indigo-700 font-mono font-black ml-1">{{ $counts['confirmed'] ?? 0 }}</strong>
      </div>
      <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
        <span class="text-slate-400 font-medium">Delivered:</span>
        <strong class="text-emerald-700 font-mono font-black ml-1">{{ $counts['delivered'] ?? 0 }}</strong>
      </div>
    </div>
  </div>

  <!-- Filter Status Navigation Tabs (Smooth Scroll Container) -->
  <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto max-w-full">
    @php
      $tabs = [
        'all'                  => 'All Orders',
        'pending_verification' => '⏳ Pending Verification',
        'confirmed'            => '✅ Confirmed',
        'processing'           => '⚙️ Processing',
        'shipped'              => '🚚 Shipped',
        'delivered'            => '🎉 Delivered',
        'cancelled'            => '❌ Cancelled',
      ];
    @endphp
    @foreach($tabs as $key => $label)
      @php $active = $status === $key || ($key === 'all' && !in_array($status, array_keys($tabs))); @endphp
      <a href="{{ route('admin.orders.index', ['status' => $key, 'q' => $q, 'method' => $method]) }}"
         class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer whitespace-nowrap shrink-0 {{ $active ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        {{ $label }}
        @if(isset($counts[$key]) && $counts[$key] > 0)
          <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full {{ $active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700 font-bold' }}">
            {{ $counts[$key] }}
          </span>
        @endif
      </a>
    @endforeach
  </div>

  <!-- Search & Method Filter Form Card -->
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="p-4 border-b border-gray-200 bg-slate-50/50 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <input type="hidden" name="status" value="{{ $status }}">
      
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
        <div class="relative flex-1">
          <input type="text" name="q" value="{{ $q }}" placeholder="Search order #, customer name, phone..." class="inp text-xs pr-8 py-2 w-full" />
          @if($q !== '')
            <a href="{{ route('admin.orders.index', ['status' => $status, 'method' => $method]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>

        <select name="method" onchange="this.form.submit()" class="text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-2xs cursor-pointer w-full sm:w-auto shrink-0">
          <option value="">All Payment Methods</option>
          @foreach(['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cod' => 'Cash on Delivery'] as $k => $v)
            <option value="{{ $k }}" @selected($method === $k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex items-center gap-2 justify-end shrink-0">
        <button type="submit" class="btn-primary text-xs px-4 py-2 shrink-0">Filter</button>
        @if($q !== '' || $method !== '')
          <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 border border-slate-200 rounded-xl hover:bg-white transition shrink-0">Clear</a>
        @endif
      </div>
    </form>

    <!-- Orders Data Table Container -->
    <div class="overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[700px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3.5 px-3 sm:px-4">Order Number</th>
            <th class="py-3.5 px-3 sm:px-4">Customer Info</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Total Amount</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Payment Status</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Source</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Risk Check</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Fulfillment Status</th>
            <th class="py-3.5 px-3 sm:px-4 text-center">Date</th>
            <th class="py-3.5 px-3 sm:px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($orders as $order)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <!-- Order Number & Items Count -->
              <td class="py-4 px-3 sm:px-4 whitespace-nowrap">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-brand-600 hover:underline block text-xs font-mono">
                  {{ $order->order_number }}
                </a>
                <span class="text-[11px] font-medium text-slate-400 block mt-0.5">
                  📦 {{ $order->items_count }} item(s)
                </span>
              </td>

              <!-- Customer Info -->
              <td class="py-4 px-3 sm:px-4">
                <p class="font-extrabold text-slate-900 text-xs leading-tight">{{ $order->customer_name }}</p>
                <p class="font-mono text-slate-600 text-[11px] whitespace-nowrap">📞 {{ $order->customer_phone }}</p>
                @if($order->city)
                  <p class="text-slate-400 text-[10px]">📍 {{ $order->city }}</p>
                @endif
              </td>

              <!-- Total Amount -->
              <td class="py-4 px-3 sm:px-4 text-center font-black text-slate-900 font-mono text-sm whitespace-nowrap">
                {{ money($order->total) }}
              </td>

              <!-- Payment Method & Status -->
              <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                <div class="space-y-1">
                  <span class="block font-semibold text-slate-700 text-[11px]">{{ $order->paymentMethodLabel() }}</span>
                  <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                    {{ ucfirst($order->payment_status) }}
                  </span>
                </div>
              </td>

              <!-- Order Source -->
              <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                <span class="text-base" title="Source: {{ $order->utm_source ?? 'Direct' }}">
                  {{ $order->utmSourceIcon() }}
                </span>
              </td>

              <!-- Fraud Risk Check Badge (Color Badge Only) -->
              <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                @if($order->fraudRiskLevel() === 'high')
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 border border-rose-300 text-xs shadow-2xs" title="High Risk ({{ $order->fraud_score }}%)">
                    🔴
                  </span>
                @elseif($order->fraudRiskLevel() === 'medium')
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 border border-amber-300 text-xs shadow-2xs" title="Medium Risk ({{ $order->fraud_score }}%)">
                    🟡
                  </span>
                @else
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 border border-emerald-300 text-xs shadow-2xs" title="Low Risk">
                    🟢
                  </span>
                @endif
              </td>

              <!-- Order Fulfillment Status -->
              <td class="py-4 px-3 sm:px-4 text-center whitespace-nowrap">
                <span class="inline-block px-2.5 py-1 text-[11px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                  {{ ucfirst($order->status) }}
                </span>
                @if($order->isDispatchedToCourier())
                  <span class="block text-[10px] font-extrabold text-emerald-700 mt-1" title="Dispatched to {{ $order->courierLabel() }}">
                    🚚 {{ $order->courierLabel() }}
                  </span>
                @endif
              </td>

              <!-- Order Date -->
              <td class="py-4 px-3 sm:px-4 text-center text-slate-500 text-[11px] font-medium whitespace-nowrap">
                <p>{{ $order->created_at->format('d M Y') }}</p>
                <p class="text-[10px] text-slate-400">{{ $order->created_at->format('g:i A') }}</p>
              </td>

              <!-- Quick Actions (Icon-Only) -->
              <td class="py-4 px-3 sm:px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <!-- View Details -->
                  <a href="{{ route('admin.orders.show', $order) }}" title="View Order Details" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition inline-flex items-center justify-center text-xs">
                    👁️
                  </a>

                  @if($order->payment_status === 'pending')
                    <!-- Verify Payment -->
                    <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="Verify Payment" class="w-8 h-8 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold transition inline-flex items-center justify-center text-xs shadow-2xs cursor-pointer">
                        ✓
                      </button>
                    </form>

                    <!-- Reject Payment -->
                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="Reject Payment" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-extrabold transition inline-flex items-center justify-center text-xs cursor-pointer">
                        ✕
                      </button>
                    </form>
                  @endif

                  <!-- Print Invoice -->
                  <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" title="Print Invoice" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 font-bold transition inline-flex items-center justify-center text-xs">
                    🖨️
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-12 text-slate-400 text-xs">
                No orders matching your filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($orders->hasPages())
      <div class="p-4 border-t border-gray-100">
        {{ $orders->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
