@extends('layouts.admin')
@section('title', 'Orders Management')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-full">
  
  {{-- Top Header Title & Quick KPI Summary --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-gray-200/80 pb-4">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Orders</h1>
        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 border border-gray-200">
          {{ number_format($counts['all'] ?? 0) }} total
        </span>
      </div>
      <p class="text-xs text-gray-500 mt-1">Manage customer purchases, verify payments, track couriers, and print invoices.</p>
    </div>

    {{-- Quick KPI Counters --}}
    <div class="grid grid-cols-3 gap-2 sm:flex sm:items-center text-xs">
      <div class="bg-white px-3 py-1.5 rounded-xl border border-gray-200/90 shadow-2xs text-center sm:text-left">
        <span class="text-gray-400 font-medium block sm:inline text-[11px] sm:text-xs">Pending:</span>
        <strong class="text-amber-700 font-mono font-bold text-xs sm:text-sm sm:ml-1">{{ $counts['pending_verification'] ?? 0 }}</strong>
      </div>
      <div class="bg-white px-3 py-1.5 rounded-xl border border-gray-200/90 shadow-2xs text-center sm:text-left">
        <span class="text-gray-400 font-medium block sm:inline text-[11px] sm:text-xs">Confirmed:</span>
        <strong class="text-primary font-mono font-bold text-xs sm:text-sm sm:ml-1">{{ $counts['confirmed'] ?? 0 }}</strong>
      </div>
      <div class="bg-white px-3 py-1.5 rounded-xl border border-gray-200/90 shadow-2xs text-center sm:text-left">
        <span class="text-gray-400 font-medium block sm:inline text-[11px] sm:text-xs">Delivered:</span>
        <strong class="text-emerald-700 font-mono font-bold text-xs sm:text-sm sm:ml-1">{{ $counts['delivered'] ?? 0 }}</strong>
      </div>
    </div>
  </div>

  {{-- Status Navigation Tabs (Smooth Scroll on Mobile) --}}
  <div class="relative">
    <div class="flex items-center gap-1.5 sm:gap-2 pb-1 overflow-x-auto no-scrollbar scroll-smooth snap-x max-w-full -mx-3 sm:mx-0 px-3 sm:px-0">
      @php
        $tabs = [
          'all'                  => 'All Orders',
          'pending_verification' => 'Needs Review',
          'not_printed'          => 'Not Printed',
          'confirmed'            => 'Confirmed',
          'processing'           => 'Processing',
          'shipped'              => 'Shipped',
          'delivered'            => 'Delivered',
          'returned'             => 'Returned',
          'cancelled'            => 'Cancelled',
        ];
      @endphp
      @foreach($tabs as $key => $label)
        @php $active = $status === $key || ($key === 'all' && !in_array($status, array_keys($tabs))); @endphp
        <a href="{{ route('admin.orders.index', ['status' => $key, 'q' => $q, 'method' => $method]) }}"
           class="snap-start px-3 py-1.5 text-xs font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap shrink-0 flex items-center gap-1.5 {{ $active ? 'bg-primary text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200/90' }}">
          <span>{{ $label }}</span>
          @if(isset($counts[$key]) && $counts[$key] > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $active ? 'bg-white/20 text-white font-bold' : 'bg-gray-100 text-gray-700 font-semibold' }}">
              {{ $counts[$key] }}
            </span>
          @endif
        </a>
      @endforeach
    </div>
  </div>

  {{-- Search & Method Filter Form Card --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="p-3 sm:p-4 border-b border-gray-200/80 bg-gray-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
      <input type="hidden" name="status" value="{{ $status }}">
      
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
        {{-- Search Input --}}
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input type="text" name="q" value="{{ $q }}" placeholder="Search order #, customer name, phone..." class="inp pl-8 text-xs py-1.5 w-full" />
          @if($q !== '')
            <a href="{{ route('admin.orders.index', ['status' => $status, 'method' => $method]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>

        {{-- Payment Method Filter --}}
        <div class="relative w-full sm:w-auto shrink-0">
          <select name="method" onchange="this.form.submit()" class="text-xs bg-white border border-gray-300 rounded-xl px-3 py-1.5 pr-7 text-gray-800 font-semibold focus:outline-none focus:ring-2 focus:ring-primary shadow-2xs cursor-pointer appearance-none w-full sm:w-auto">
            <option value="">All Payment Methods</option>
            @foreach(['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cod' => 'Cash on Delivery'] as $k => $v)
              <option value="{{ $k }}" @selected($method === $k)>{{ $v }}</option>
            @endforeach
          </select>
          <svg class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </div>
      </div>

      <div class="flex items-center gap-2 justify-end shrink-0">
        <button type="submit" class="btn-primary text-xs px-4 py-1.5 flex-1 sm:flex-initial justify-center">Filter</button>
        @if($q !== '' || $method !== '')
          <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="px-3 py-1.5 text-xs font-semibold text-gray-500 hover:text-gray-800 border border-gray-200 rounded-xl hover:bg-white transition-colors text-center shrink-0">Clear</a>
        @endif
      </div>
    </form>

    {{-- Mobile Orders Card View (< md screens) --}}
    <div class="block md:hidden divide-y divide-gray-100 bg-white">
      @forelse($orders as $order)
        <div class="p-3.5 space-y-2.5 hover:bg-gray-50/70 transition-colors">
          {{-- Card Header: Order #, Date & Total --}}
          <div class="flex items-start justify-between gap-2">
            <label class="-m-2 p-2 shrink-0 cursor-pointer" title="Select for printing">
              <input type="checkbox" value="{{ $order->order_number }}" class="order-select h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" aria-label="Select order {{ $order->order_number }}">
            </label>
            <div class="flex-1 min-w-0">
              <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline text-sm font-mono block">
                {{ $order->order_number }}
              </a>
              <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5">
                <span>{{ $order->items_count }} item(s)</span>
                <span>&middot;</span>
                <span>{{ $order->created_at->format('d M, g:i A') }}</span>
              </div>
              @if($order->prints->isNotEmpty())
                <div class="flex gap-1 mt-1">@include('admin.orders.partials.print-badges')</div>
              @endif
            </div>
            <div class="text-right shrink-0">
              <span class="text-base font-bold text-gray-900 font-mono block">
                {{ money($order->total) }}
              </span>
              <span class="text-[10px] font-semibold text-gray-500">
                {{ $order->paymentMethodLabel() }}
              </span>
            </div>
          </div>

          {{-- Customer Info Bar --}}
          <div class="bg-gray-50 rounded-xl p-2.5 border border-gray-100 flex items-center justify-between gap-2 text-xs">
            <div class="min-w-0 flex-1 truncate">
              <p class="font-semibold text-gray-900 truncate">{{ $order->customer_name }}</p>
              @if($order->city)
                <p class="text-gray-500 text-[11px] truncate mt-0.5">{{ $order->city }}</p>
              @endif
            </div>
            @if($order->customer_phone)
              <a href="tel:{{ $order->customer_phone }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 font-mono font-semibold text-xs shadow-2xs hover:bg-gray-100 shrink-0">
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="text-[11px]">{{ $order->customer_phone }}</span>
              </a>
            @endif
          </div>

          {{-- Badges Row --}}
          <div class="flex items-center gap-1.5 flex-wrap text-xs">
            <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->statusBadge() }}">
              {{ ucfirst($order->status) }}
            </span>
            @if(in_array($order->courier_status, \App\Services\Courier\CourierStatusUpdater::ATTENTION, true) && $order->status === 'shipped')
              <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-900">{{ \App\Services\Courier\CourierStatusUpdater::label($order->courier_status) }}</span>
            @endif

            <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->paymentBadge() }}">
              {{ ucfirst($order->payment_status) }}
            </span>

            @if($order->isDispatchedToCourier())
              <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ $order->courierLabel() }}
              </span>
            @endif

            @if($order->fraudRiskLevel() === 'high')
              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                High Risk
              </span>
            @elseif($order->fraudRiskLevel() === 'medium')
              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                Med Risk
              </span>
            @endif
          </div>

          {{-- Mobile Action Buttons --}}
          <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-1.5">
            <a href="{{ route('admin.orders.show', $order) }}" class="flex-1 py-1.5 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-xs transition-colors text-center inline-flex items-center justify-center gap-1">
              Details
            </a>

            @if($order->isAwaitingReview())
              <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline">
                @csrf
                <button type="submit" class="py-1.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors shadow-2xs cursor-pointer">
                  {{ $order->payment_method === 'cod' ? 'Confirm' : 'Verify' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline">
                @csrf
                <button type="submit" class="py-1.5 px-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-semibold text-xs transition-colors cursor-pointer">
                  Reject
                </button>
              </form>
            @endif

            <a href="{{ route('admin.orders.labels', ['orders' => [$order->order_number], 'print' => 1]) }}" target="_blank" data-print-link data-print-warning="{{ $order->printWarning('label') }}" class="py-1.5 px-3 rounded-xl bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100 font-semibold text-xs transition-colors inline-flex items-center gap-1">
              Label
            </a>
            <a href="{{ route('admin.orders.invoice', ['order' => $order, 'print' => 1]) }}" target="_blank" data-invoice-link data-print-link data-print-warning="{{ $order->printWarning('invoice') }}" class="py-1.5 px-3 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 font-semibold text-xs transition-colors inline-flex items-center gap-1">
              Invoice
            </a>
          </div>
        </div>
      @empty
        <div class="text-center py-12 text-gray-400 text-xs px-4">
          No orders matching your filters.
        </div>
      @endforelse
    </div>

    {{-- Desktop Table View (>= md screens) --}}
    <div class="hidden md:block overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-200 whitespace-nowrap">
            <th class="py-3 pl-3 lg:pl-4 pr-0 w-8">
              <input type="checkbox" id="selectAllOrders" class="h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" title="Select all on this page" aria-label="Select all orders on this page">
            </th>
            <th class="py-3 px-3 lg:px-4">Order #</th>
            <th class="py-3 px-3 lg:px-4">Customer</th>
            <th class="py-3 px-3 lg:px-4 text-center">Total</th>
            <th class="py-3 px-3 lg:px-4 text-center">Payment</th>
            <th class="py-3 px-3 lg:px-4 text-center">Risk</th>
            <th class="py-3 px-3 lg:px-4 text-center">Fulfillment</th>
            <th class="py-3 px-3 lg:px-4 text-center">Date</th>
            <th class="py-3 px-3 lg:px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($orders as $order)
            <tr class="hover:bg-gray-50/70 transition-colors whitespace-nowrap">
              <td class="py-3 pl-3 lg:pl-4 pr-0">
                <input type="checkbox" value="{{ $order->order_number }}" class="order-select h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" aria-label="Select order {{ $order->order_number }}">
              </td>
              {{-- Order Number --}}
              <td class="py-3 px-3 lg:px-4 whitespace-nowrap">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-primary hover:underline block text-xs font-mono">
                  {{ $order->order_number }}
                </a>
                <span class="text-[11px] text-gray-400 block mt-0.5">
                  {{ $order->items_count }} item(s)
                </span>
                @if($order->prints->isNotEmpty())
                  <span class="flex gap-1 mt-1">@include('admin.orders.partials.print-badges')</span>
                @endif
              </td>

              {{-- Customer Info --}}
              <td class="py-3 px-3 lg:px-4">
                <p class="font-semibold text-gray-900 text-xs leading-tight">{{ $order->customer_name }}</p>
                <p class="font-mono text-gray-500 text-[11px] whitespace-nowrap">{{ $order->customer_phone }}</p>
                @if($order->city)
                  <p class="text-gray-400 text-[10px]">{{ $order->city }}</p>
                @endif
              </td>

              {{-- Total Amount --}}
              <td class="py-3 px-3 lg:px-4 text-center font-bold text-gray-900 font-mono text-xs sm:text-sm whitespace-nowrap">
                {{ money($order->total) }}
              </td>

              {{-- Payment Method & Status --}}
              <td class="py-3 px-3 lg:px-4 text-center whitespace-nowrap">
                <div class="space-y-0.5">
                  <span class="block font-medium text-gray-700 text-[11px]">{{ $order->paymentMethodLabel() }}</span>
                  <span class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $order->paymentBadge() }}">
                    {{ ucfirst($order->payment_status) }}
                  </span>
                </div>
              </td>

              {{-- Risk Check --}}
              <td class="py-3 px-3 lg:px-4 text-center whitespace-nowrap">
                @if($order->fraudRiskLevel() === 'high')
                  <span class="inline-block px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold" title="High Risk ({{ $order->fraud_score }}%)">
                    High
                  </span>
                @elseif($order->fraudRiskLevel() === 'medium')
                  <span class="inline-block px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold" title="Medium Risk ({{ $order->fraud_score }}%)">
                    Medium
                  </span>
                @else
                  <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-medium" title="Low Risk">
                    Low
                  </span>
                @endif
              </td>

              {{-- Fulfillment Status --}}
              <td class="py-3 px-3 lg:px-4 text-center whitespace-nowrap">
                <span class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full {{ $order->statusBadge() }}">
                  {{ ucfirst($order->status) }}
                </span>
                @if(in_array($order->courier_status, \App\Services\Courier\CourierStatusUpdater::ATTENTION, true) && $order->status === 'shipped')
                  <span class="block mt-0.5"><span class="px-1.5 py-px rounded-md bg-amber-100 text-amber-900 text-[10px] font-bold" title="{{ $order->courier_status_message }}">{{ \App\Services\Courier\CourierStatusUpdater::label($order->courier_status) }}</span></span>
                @endif
                @if($order->isDispatchedToCourier())
                  <span class="block text-[10px] font-medium text-emerald-700 mt-0.5" title="Dispatched to {{ $order->courierLabel() }}">
                    {{ $order->courierLabel() }}
                  </span>
                @endif
              </td>

              {{-- Order Date --}}
              <td class="py-3 px-3 lg:px-4 text-center text-gray-500 text-[11px] font-medium whitespace-nowrap">
                <p>{{ $order->created_at->format('d M Y') }}</p>
                <p class="text-[10px] text-gray-400">{{ $order->created_at->format('g:i A') }}</p>
              </td>

              {{-- Actions --}}
              <td class="py-3 px-3 lg:px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.orders.show', $order) }}" title="View Order Details" class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold transition-colors inline-flex items-center justify-center text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>

                  @if($order->isAwaitingReview())
                    <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="{{ $order->acceptLabel() }}" class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-colors inline-flex items-center justify-center text-xs shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                      </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="Reject order" class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold transition-colors inline-flex items-center justify-center text-xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </form>
                  @endif

                  <a href="{{ route('admin.orders.labels', ['orders' => [$order->order_number], 'print' => 1]) }}" target="_blank" data-print-link data-print-warning="{{ $order->printWarning('label') }}" title="Print Parcel Label" aria-label="Print parcel label" class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100 transition-colors inline-flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5v14"/><path d="M8 5v14"/><path d="M12 5v14"/><path d="M17 5v14"/><path d="M21 5v14"/></svg>
                  </a>
                  <a href="{{ route('admin.orders.invoice', ['order' => $order, 'print' => 1]) }}" target="_blank" data-invoice-link data-print-link data-print-warning="{{ $order->printWarning('invoice') }}" title="Print Invoice" class="w-7 h-7 lg:w-8 lg:h-8 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 font-semibold transition-colors inline-flex items-center justify-center text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-12 text-gray-400 text-xs">
                No orders matching your filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($orders->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">
        {{ $orders->links() }}
      </div>
    @endif
  </div>
</div>

{{-- Bulk printing: selection is kept while moving between pages of the list. --}}
<div id="bulkBar" class="hidden fixed bottom-3 inset-x-3 lg:left-auto lg:right-6 lg:bottom-6 z-30 lg:max-w-3xl" role="region" aria-label="Selected orders">
  <div class="bg-gray-900 text-white rounded-2xl shadow-2xl px-3 py-2.5 sm:px-4 flex flex-wrap items-center gap-2 sm:gap-3">
    <div class="flex items-center gap-2 mr-auto">
      <span class="text-sm font-semibold"><span id="bulkCount">0</span> selected</span>
      <button type="button" id="bulkClear" class="text-xs text-gray-300 hover:text-white underline underline-offset-2">Clear</button>
    </div>
    <label class="sr-only" for="bulkFormat">Invoice format</label>
    <select id="bulkFormat" data-invoice-format class="h-9 rounded-xl bg-gray-800 border border-gray-700 text-xs font-semibold text-white pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-teal-500">
      @foreach(\App\Http\Controllers\Admin\OrderController::INVOICE_FORMATS as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>
    <button type="button" id="bulkInvoices" class="h-9 px-4 rounded-xl bg-teal-600 hover:bg-teal-500 text-xs font-bold inline-flex items-center gap-1.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
      Print invoices
    </button>
    <button type="button" id="bulkLabels" class="h-9 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold">Print labels</button>
  </div>
  <p id="bulkNote" class="hidden mt-1.5 text-center text-[11px] text-gray-600"></p>
</div>

{{-- Shown when some selected orders were already printed. --}}
<div id="reprintDialog" class="hidden fixed inset-0 z-50 bg-gray-900/50 flex items-end sm:items-center justify-center p-3" role="dialog" aria-modal="true" aria-labelledby="reprintTitle">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-5 space-y-3">
    <div class="flex items-start gap-3">
      <span class="h-9 w-9 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0" aria-hidden="true">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
      </span>
      <div class="min-w-0">
        <h2 id="reprintTitle" class="text-sm font-bold text-gray-900"></h2>
        <p class="text-xs text-gray-500 mt-0.5">Make sure these orders are not packed twice.</p>
      </div>
    </div>
    <ul id="reprintList" class="max-h-48 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 text-xs"></ul>
    <div class="flex flex-col sm:flex-row-reverse gap-2 pt-1">
      <button type="button" id="reprintSkip" class="h-10 px-4 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold"></button>
      <button type="button" id="reprintAll" class="h-10 px-4 rounded-xl border border-gray-300 hover:bg-gray-50 text-gray-800 text-xs font-bold"></button>
      <button type="button" id="reprintCancel" class="h-10 px-4 rounded-xl text-gray-500 hover:text-gray-800 text-xs font-semibold sm:mr-auto">Cancel</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var KEY = 'admin.orders.selected';
    var MAX = 100;
    var invoicesUrl = @json(route('admin.orders.invoices'));
    var labelsUrl = @json(route('admin.orders.labels'));
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.order-select'));
    var all = document.getElementById('selectAllOrders');
    var bar = document.getElementById('bulkBar');
    var count = document.getElementById('bulkCount');
    var fmt = document.getElementById('bulkFormat');
    var note = document.getElementById('bulkNote');

    function load() { try { return JSON.parse(sessionStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
    var selected = load();
    function save() { try { sessionStorage.setItem(KEY, JSON.stringify(selected)); } catch (e) {} }

    function render() {
      boxes.forEach(function (b) { b.checked = selected.indexOf(b.value) !== -1; });
      // The table and the mobile cards each have a box per order; count the table ones for "select all".
      var onPage = boxes.filter(function (b) { return b.closest('table'); });
      var checked = onPage.filter(function (b) { return b.checked; }).length;
      if (all) {
        all.checked = onPage.length > 0 && checked === onPage.length;
        all.indeterminate = checked > 0 && checked < onPage.length;
      }
      count.textContent = selected.length;
      bar.classList.toggle('hidden', selected.length === 0);
      // Keep the pagination reachable above the floating bar.
      var main = document.querySelector('main');
      if (main) main.style.paddingBottom = selected.length ? '7rem' : '';
      var sheets = fmt.value === 'half' ? Math.ceil(selected.length / 2) : null;
      note.textContent = sheets ? 'Half page: ' + selected.length + ' orders on ' + sheets + ' A4 ' + (sheets === 1 ? 'sheet' : 'sheets') : '';
      note.classList.toggle('hidden', !sheets);
    }

    function toggle(value, on) {
      var i = selected.indexOf(value);
      if (on && i === -1) {
        if (selected.length >= MAX) { alert('You can print up to ' + MAX + ' orders at a time.'); return false; }
        selected.push(value);
      }
      if (!on && i !== -1) selected.splice(i, 1);
      return true;
    }

    boxes.forEach(function (b) {
      b.addEventListener('change', function () {
        if (!toggle(b.value, b.checked)) b.checked = false;
        save(); render(); refreshStatusSoon();
      });
    });
    if (all) all.addEventListener('change', function () {
      boxes.filter(function (b) { return b.closest('table'); }).forEach(function (b) { toggle(b.value, all.checked); });
      save(); render(); refreshStatusSoon();
    });
    document.getElementById('bulkClear').addEventListener('click', function () { selected = []; save(); render(); refreshStatusSoon(); });
    fmt.addEventListener('change', render);

    function open(base, numbers, extra) {
      var params = new URLSearchParams();
      numbers.forEach(function (n) { params.append('orders[]', n); });
      Object.keys(extra).forEach(function (k) { params.set(k, extra[k]); });
      params.set('print', '1');
      window.open(base + '?' + params.toString(), '_blank');
      status = null; // printed now; fetch fresh history next time
    }

    // Print history of the selection is fetched in the background, so the warning can open
    // straight from the click (browsers block new tabs opened after a slow request).
    var statusUrl = @json(route('admin.orders.prints.status'));
    var status = null, statusFor = '', statusReq = null;
    function fetchStatus() {
      var key = selected.slice().sort().join('|');
      if (!selected.length) { status = null; statusFor = ''; return Promise.resolve(null); }
      if (status && statusFor === key) return Promise.resolve(status);
      var params = new URLSearchParams();
      selected.forEach(function (n) { params.append('orders[]', n); });
      statusReq = fetch(statusUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : { printed: {} }; })
        .then(function (d) { status = d.printed || {}; statusFor = key; return status; })
        .catch(function () { return null; });
      return statusReq;
    }
    var statusTimer = null;
    function refreshStatusSoon() { clearTimeout(statusTimer); status = null; statusTimer = setTimeout(fetchStatus, 250); }
    window.addEventListener('focus', function () { if (selected.length) { status = null; fetchStatus(); } });

    var dialog = document.getElementById('reprintDialog');
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function closeDialog() { dialog.classList.add('hidden'); }
    document.getElementById('reprintCancel').addEventListener('click', closeDialog);
    dialog.addEventListener('click', function (e) { if (e.target === dialog) closeDialog(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !dialog.classList.contains('hidden')) closeDialog(); });

    function printWithCheck(type, base, extra) {
      var go = function (st) {
        var printed = (st && st[type]) || [];
        var done = printed.map(function (p) { return p.order_number; });
        var already = selected.filter(function (n) { return done.indexOf(n) !== -1; });
        if (!already.length) { open(base, selected.slice(), extra); return; }

        var fresh = selected.filter(function (n) { return done.indexOf(n) === -1; });
        var what = type === 'invoice' ? 'invoice' : 'label';
        document.getElementById('reprintTitle').textContent = already.length === 1
          ? '1 of ' + selected.length + ' orders already has its ' + what + ' printed'
          : already.length + ' of ' + selected.length + ' orders already have their ' + what + 's printed';
        document.getElementById('reprintList').innerHTML = printed.map(function (p) {
          return '<li class="px-3 py-2"><span class="font-mono font-bold text-gray-900">' + esc(p.order_number) + '</span>' +
            '<span class="block text-gray-500">' + (p.times > 1 ? 'Printed ' + p.times + '×, last by ' : 'By ') + esc(p.last) + '</span></li>';
        }).join('');
        var skip = document.getElementById('reprintSkip');
        skip.textContent = fresh.length ? 'Skip printed, print ' + fresh.length : 'Nothing new to print';
        skip.disabled = !fresh.length;
        skip.classList.toggle('opacity-50', !fresh.length);
        skip.onclick = function () { closeDialog(); open(base, fresh, extra); };
        var all = document.getElementById('reprintAll');
        all.textContent = 'Print all ' + selected.length + ' anyway';
        all.onclick = function () { closeDialog(); open(base, selected.slice(), extra); };
        dialog.classList.remove('hidden');
        (fresh.length ? skip : all).focus();
      };
      if (status && statusFor === selected.slice().sort().join('|')) go(status); else fetchStatus().then(go);
    }
    document.getElementById('bulkInvoices').addEventListener('click', function () { printWithCheck('invoice', invoicesUrl, { format: fmt.value }); });
    document.getElementById('bulkLabels').addEventListener('click', function () { printWithCheck('label', labelsUrl, {}); });

    render();
    fetchStatus();
  })();
</script>
@endpush
