@extends('layouts.admin')
@section('title', 'Orders')
@section('subtitle', 'Review new orders, print invoices and labels, and follow every parcel with the courier.')

@php
  $canCourier = \App\Support\StaffAccess::allows(auth()->user(), 'courier-scan');
  $courierStatusIcon = [
    'in_transit' => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
    'attention'  => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/>',
    'unchecked'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'delivered'  => '<path d="M20 6 9 17l-5-5"/>',
  ];
  $courierTone = [
    'in_transit' => 'bg-sky-50 text-sky-700',
    'attention'  => 'bg-amber-50 text-amber-700',
    'unchecked'  => 'bg-gray-100 text-gray-600',
    'delivered'  => 'bg-emerald-50 text-emerald-700',
  ];
@endphp

@section('page-actions')
  @if($canCourier)
    <a href="{{ route('admin.courier-scan.index') }}" class="pill-btn">
      Courier scan
      <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M8 8v8M12 8v8M16 8v8"/></svg></span>
    </a>
  @endif
@endsection

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Courier tracking in one line: parcel counts filter the list below --}}
  <section class="panel bg-white shadow-panel px-4 py-3 sm:px-5 flex flex-col lg:flex-row lg:items-center gap-2.5 lg:gap-4" data-courier-strip>
    <div class="flex items-center justify-between gap-3 lg:shrink-0">
      <div class="min-w-0">
        <h2 class="text-[14px] font-semibold text-gray-900 flex items-center gap-2">
          Courier tracking
          @if($autoSync)
            <span class="h-2 w-2 rounded-full bg-emerald-500" title="Auto update on: Steadfast, Pathao and RedX parcels are checked every night at 9:00 PM" aria-label="Auto update on"></span>
          @else
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10.5px] font-semibold text-gray-600">Auto update off</span>
          @endif
        </h2>
        <p class="text-[11px] text-gray-500 leading-snug">
          @if($lastSync && !empty($lastSync['at']))
            @php $syncedAt = \Illuminate\Support\Carbon::parse($lastSync['at'])->timezone(config('app.timezone')); @endphp
            Last checked <span class="font-medium text-gray-700" title="{{ $syncedAt->format('d M Y, g:i A') }} · {{ $lastSync['checked'] ?? 0 }} parcels">{{ $syncedAt->isToday() ? 'today' : ($syncedAt->isYesterday() ? 'yesterday' : $syncedAt->format('d M')) }}, {{ $syncedAt->format('g:i A') }}</span>
          @else
            Not checked yet
          @endif
          <span class="hidden sm:inline">· checked every night at 9:00 PM</span>
        </p>
      </div>
      @if($canCourier)
        <form method="POST" action="{{ route('admin.courier-scan.sync') }}" class="shrink-0 lg:hidden" onsubmit="this.querySelector('button').disabled = true; this.querySelector('[data-label]').textContent = 'Checking...';">
          @csrf
          <button type="submit" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
            <span data-label>Check now</span>
          </button>
        </form>
      @endif
    </div>
    <div class="-mx-4 px-4 sm:-mx-5 sm:px-5 lg:mx-0 lg:px-0 lg:flex-1 overflow-x-auto no-scrollbar">
      <div class="flex items-center gap-1.5 whitespace-nowrap lg:justify-end">
        @foreach(\App\Http\Controllers\Admin\OrderController::COURIER_FILTERS as $key => $label)
          @php $on = $courier === $key; $n = $courierCounts[$key] ?? 0; @endphp
          <a href="{{ $on ? route('admin.orders.index', ['q' => $q, 'method' => $method]) : route('admin.orders.index', ['courier' => $key, 'q' => $q, 'method' => $method]) }}"
             class="h-8 pl-2 pr-3 rounded-full inline-flex items-center gap-1.5 text-xs font-medium transition-colors {{ $on ? 'text-white' : 'bg-gray-50 ring-1 ring-gray-100 text-gray-600 hover:bg-gray-100' }}"
             @if($on) style="background: var(--brand-dark);" aria-current="true" title="Show all orders again" @endif>
            <span class="grid h-5 w-5 place-items-center rounded-full {{ $on ? 'bg-white/15 text-white' : $courierTone[$key] }}">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $courierStatusIcon[$key] !!}</svg>
            </span>
            <span class="font-semibold tabular-nums {{ $on ? 'text-white' : 'text-gray-900' }}">{{ number_format($n) }}</span>
            {{ $label }}
          </a>
        @endforeach
        @if($canCourier)
          <form method="POST" action="{{ route('admin.courier-scan.sync') }}" class="hidden lg:block shrink-0 ml-1" onsubmit="this.querySelector('button').disabled = true; this.querySelector('[data-label]').textContent = 'Checking...';">
            @csrf
            <button type="submit" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold inline-flex items-center gap-1.5 transition-colors disabled:opacity-60">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
              <span data-label>Check now</span>
            </button>
          </form>
        @endif
      </div>
    </div>
  </section>

  @php
    $tabs = [
      'all'                  => 'All',
      'pending_verification' => 'Needs review',
      'not_printed'          => 'Not printed',
      'confirmed'            => 'Confirmed',
      'processing'           => 'Processing',
      'shipped'              => 'Shipped',
      'delivered'            => 'Delivered',
      'returned'             => 'Returned',
      'cancelled'            => 'Cancelled',
    ];
  @endphp

  {{-- Order list: status tabs, search and payment filter sit together above it --}}
  <div class="card overflow-hidden">
    <nav class="px-3 sm:px-4 pt-3 sm:pt-4 overflow-x-auto no-scrollbar" aria-label="Order status">
      <div class="inline-flex items-center gap-1 whitespace-nowrap">
        @foreach($tabs as $key => $label)
          @php $active = $status === $key || ($key === 'all' && !in_array($status, array_keys($tabs))); @endphp
          <a href="{{ route('admin.orders.index', ['status' => $key, 'q' => $q, 'method' => $method]) }}"
             class="h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
             @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
            {{ $label }}
            @if(($counts[$key] ?? 0) > 0)
              <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts[$key] }}</span>
            @endif
          </a>
        @endforeach
      </div>
    </nav>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
      <input type="hidden" name="status" value="{{ $status }}">
      @if($courier !== '')<input type="hidden" name="courier" value="{{ $courier }}">@endif
      <label class="relative flex-1">
        <span class="sr-only">Search orders</span>
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="Order number, name or phone"
               class="w-full h-10 pl-11 pr-10 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-900/5 outline-none transition" />
        @if($q !== '')
          <a href="{{ route('admin.orders.index', ['status' => $status, 'method' => $method, 'courier' => $courier ?: null]) }}" class="absolute right-3.5 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-700" aria-label="Clear search">✕</a>
        @endif
      </label>
      <div class="flex items-center gap-2">
        <label class="relative flex-1 sm:flex-initial">
          <span class="sr-only">Payment method</span>
          <select name="method" onchange="this.form.submit()" class="w-full sm:w-auto h-10 rounded-full bg-gray-100 border border-transparent pl-4 pr-9 text-sm font-medium text-gray-800 appearance-none cursor-pointer focus:bg-white focus:border-gray-200 outline-none">
            <option value="">All payments</option>
            @foreach(['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cod' => 'Cash on delivery'] as $k => $v)
              <option value="{{ $k }}" @selected($method === $k)>{{ $v }}</option>
            @endforeach
          </select>
          <svg class="w-3.5 h-3.5 text-gray-500 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </label>
        <button type="submit" class="h-10 px-5 rounded-full text-white text-sm font-semibold shrink-0 hidden sm:inline-flex items-center" style="background: var(--brand-dark);">Search</button>
        @if($q !== '' || ($method ?? '') !== '' || $courier !== '')
          <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="h-10 px-3 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 inline-flex items-center shrink-0">Reset</a>
        @endif
      </div>
    </form>
    @if($courier !== '')
      <div class="px-4 pb-3 -mt-1 text-xs text-gray-600 flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 font-medium">
          Courier: {{ \App\Http\Controllers\Admin\OrderController::COURIER_FILTERS[$courier] }}
          <a href="{{ route('admin.orders.index', ['status' => $status, 'q' => $q, 'method' => $method]) }}" class="text-gray-400 hover:text-gray-800" aria-label="Remove courier filter">✕</a>
        </span>
      </div>
    @endif

    {{-- Phone: one compact card per order; Label and Invoice sit in the ⋯ menu --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($orders as $order)
        @php $risk = $order->fraudRiskLevel(); [$payText, $payTone] = $order->paymentSummary(); @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3">
          <div class="flex items-start gap-2.5">
            <label class="-m-2 p-2 shrink-0 cursor-pointer" title="Select for printing">
              <input type="checkbox" value="{{ $order->order_number }}" class="order-select h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" aria-label="Select order {{ $order->order_number }}">
            </label>
            <div class="flex-1 min-w-0">
              <div class="flex items-baseline justify-between gap-2">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 text-sm hover:underline truncate">{{ $order->order_number }}</a>
                <p class="text-[15px] font-semibold text-gray-900 tabular-nums shrink-0">{{ money($order->total) }}</p>
              </div>
              <div class="flex items-center justify-between gap-2 mt-0.5">
                <p class="text-[12.5px] text-gray-800 truncate"><span class="font-medium">{{ $order->customer_name }}</span>@if($order->city)<span class="text-gray-500"> · {{ $order->city }}</span>@endif</p>
                <p class="text-[11px] text-gray-500 shrink-0">{{ $order->created_at->format('d M, g:i A') }}</p>
              </div>
              <div class="mt-2 flex items-center gap-x-2 gap-y-1 flex-wrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
                <span class="text-[11.5px] font-medium {{ $payTone }}">{{ $payText }}</span>
                @if($order->payment_method !== 'cod')<span class="text-[11px] text-gray-400">{{ $order->paymentMethodLabel() }}</span>@endif
                @if($risk !== 'low')
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $risk === 'high' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($risk) }} risk</span>
                @endif
                @if($order->prints->isNotEmpty())@include('admin.orders.partials.print-badges')@endif
              </div>
            </div>
          </div>

          @if($order->isDispatchedToCourier())
            <div class="mt-2.5 rounded-xl bg-white p-2.5">@include('admin.orders.partials.courier-cell')</div>
          @endif

          <div class="mt-2.5 flex items-center gap-1.5">
            @if($order->isAwaitingReview())
              <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full h-9 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs cursor-pointer">{{ $order->payment_method === 'cod' ? 'Confirm' : 'Verify' }}</button>
              </form>
              <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full h-9 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold text-xs cursor-pointer">Reject</button>
              </form>
            @else
              <a href="{{ route('admin.orders.show', $order) }}" class="flex-1 h-9 rounded-full text-white font-semibold text-xs inline-flex items-center justify-center" style="background: var(--brand-dark);">Open order</a>
            @endif
            @if(preg_match('/\d{6,}/', (string) $order->customer_phone))
              <a href="tel:{{ $order->customer_phone }}" class="h-9 w-9 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 inline-flex items-center justify-center shrink-0" title="Call {{ $order->customer_phone }}" aria-label="Call {{ $order->customer_phone }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
              </a>
            @endif
            <details class="order-menu relative shrink-0">
              <summary class="h-9 w-9 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 inline-flex items-center justify-center cursor-pointer" aria-label="More for {{ $order->order_number }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
              </summary>
              <div class="absolute right-0 bottom-full mb-1.5 z-20 w-44 rounded-2xl bg-white shadow-xl ring-1 ring-gray-100 p-1 text-[13px]">
                @if($order->isAwaitingReview())
                  <a href="{{ route('admin.orders.show', $order) }}" class="block px-3 py-2 rounded-xl hover:bg-gray-50 font-medium text-gray-800">Open order</a>
                @endif
                <a href="{{ route('admin.orders.labels', ['orders' => [$order->order_number], 'print' => 1]) }}" target="_blank" data-print-link data-print-warning="{{ $order->printWarning('label') }}" class="block px-3 py-2 rounded-xl hover:bg-gray-50 font-medium text-gray-800">Print label</a>
                <a href="{{ route('admin.orders.invoice', ['order' => $order, 'print' => 1]) }}" target="_blank" data-invoice-link data-print-link data-print-warning="{{ $order->printWarning('invoice') }}" class="block px-3 py-2 rounded-xl hover:bg-gray-50 font-medium text-gray-800">Print invoice</a>
              </div>
            </details>
          </div>
        </article>
      @empty
        <div class="text-center py-12 text-gray-500 text-sm">No orders match these filters.</div>
      @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto w-full">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 pl-4 lg:pl-5 pr-0 w-8">
              <input type="checkbox" id="selectAllOrders" class="h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" title="Select all on this page" aria-label="Select all orders on this page">
            </th>
            <th class="py-3 px-3 lg:px-4">Order</th>
            <th class="py-3 px-3 lg:px-4">Customer</th>
            <th class="py-3 px-3 lg:px-4 text-right">Amount</th>
            <th class="py-3 px-3 lg:px-4">Status</th>
            <th class="py-3 px-3 lg:px-4">Courier</th>
            <th class="py-3 px-3 lg:px-4 pr-4 lg:pr-5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($orders as $order)
            @php $risk = $order->fraudRiskLevel(); [$payText, $payTone] = $order->paymentSummary(); @endphp
            <tr class="hover:bg-gray-50/70 transition-colors align-top">
              <td class="py-3.5 pl-4 lg:pl-5 pr-0">
                <input type="checkbox" value="{{ $order->order_number }}" class="order-select mt-0.5 h-4 w-4 rounded border-gray-300 text-teal-700 focus:ring-teal-600 cursor-pointer" aria-label="Select order {{ $order->order_number }}">
              </td>
              <td class="py-3.5 px-3 lg:px-4 whitespace-nowrap">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 hover:underline whitespace-nowrap">{{ $order->order_number }}</a>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ $order->created_at->format('d M, g:i A') }} · {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</p>
                @if($order->prints->isNotEmpty())
                  <span class="flex gap-1 mt-1">@include('admin.orders.partials.print-badges')</span>
                @endif
              </td>
              <td class="py-3.5 px-3 lg:px-4">
                <p class="font-semibold text-gray-900 leading-tight">{{ $order->customer_name }}</p>
                <p class="text-gray-500 text-[11.5px] whitespace-nowrap mt-0.5"><span class="tabular-nums">{{ $order->customer_phone }}</span>@if($order->city) · {{ $order->city }}@endif</p>
                @if($risk !== 'low')
                  <span class="mt-1 inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-semibold {{ $risk === 'high' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}" title="Fraud score {{ $order->fraud_score }}%">{{ ucfirst($risk) }} risk</span>
                @endif
              </td>
              <td class="py-3.5 px-3 lg:px-4 whitespace-nowrap text-right">
                <p class="font-semibold text-gray-900 tabular-nums">{{ money($order->total) }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ $order->paymentMethodLabel() }}</p>
              </td>
              <td class="py-3.5 px-3 lg:px-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
                <p class="mt-1 text-[11px] font-medium {{ $payTone }}">{{ $payText }}</p>
                @if($order->isAwaitingReview())
                  <div class="mt-1.5 flex items-center gap-1">
                    <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="{{ $order->acceptLabel() }}" class="h-7 px-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $order->payment_method === 'cod' ? 'Confirm' : 'Verify' }}
                      </button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline">
                      @csrf
                      <button type="submit" title="Reject order" class="h-7 px-2.5 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-[11px] font-semibold cursor-pointer">Reject</button>
                    </form>
                  </div>
                @endif
              </td>
              <td class="py-3.5 px-3 lg:px-4">
                @include('admin.orders.partials.courier-cell')
              </td>
              <td class="py-3.5 px-3 lg:px-4 pr-4 lg:pr-5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.orders.labels', ['orders' => [$order->order_number], 'print' => 1]) }}" target="_blank" data-print-link data-print-warning="{{ $order->printWarning('label') }}" title="Print parcel label" aria-label="Print parcel label" class="w-8 h-8 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 inline-flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5v14"/><path d="M8 5v14"/><path d="M12 5v14"/><path d="M17 5v14"/><path d="M21 5v14"/></svg>
                  </a>
                  <a href="{{ route('admin.orders.invoice', ['order' => $order, 'print' => 1]) }}" target="_blank" data-invoice-link data-print-link data-print-warning="{{ $order->printWarning('invoice') }}" title="Print invoice" aria-label="Print invoice" class="w-8 h-8 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 inline-flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                  </a>
                  <a href="{{ route('admin.orders.show', $order) }}" class="h-8 pl-3 pr-2 rounded-full text-white text-xs font-semibold inline-flex items-center gap-1" style="background: var(--brand-dark);">
                    Open
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-12 text-gray-500 text-sm">No orders match these filters.</td>
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
  <div class="text-white rounded-[22px] shadow-2xl px-3 py-2.5 sm:px-4 flex flex-wrap items-center gap-2 sm:gap-3" style="background: var(--brand-dark);">
    <div class="flex items-center gap-2 mr-auto">
      <span class="text-sm font-semibold"><span id="bulkCount">0</span> selected</span>
      <button type="button" id="bulkClear" class="text-xs text-gray-300 hover:text-white underline underline-offset-2">Clear</button>
    </div>
    <label class="sr-only" for="bulkFormat">Invoice format</label>
    <select id="bulkFormat" data-invoice-format class="h-9 rounded-xl bg-white/10 border border-white/15 text-xs font-semibold text-white pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-teal-500">
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

    // Only one ⋯ menu open at a time; a tap elsewhere closes it.
    document.addEventListener('click', function (e) {
      document.querySelectorAll('details.order-menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
    });
  })();
</script>
@endpush
