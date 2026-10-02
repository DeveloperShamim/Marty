@extends('layouts.admin')
@section('title', 'Courier In/Out Scan Station')

@section('content')
<div class="space-y-6">

  {{-- Top Header --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 rounded-xl bg-blue-50 text-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
          </span>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Courier In/Out Scan Station</h1>
            <p class="text-xs sm:text-sm text-gray-500">Barcode-driven courier dispatch handover &amp; return processing station</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.courier-scan.manifest') }}" target="_blank" class="px-4 py-2 text-xs font-bold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 transition-colors flex items-center gap-1.5 shadow-2xs">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Today's Handover Manifest
        </a>
      </div>
    </div>
  </div>

  {{-- Courier reports that need staff action (from the API sync / webhooks). --}}
  <div class="bg-white rounded-2xl border {{ $courierAttention->isNotEmpty() ? 'border-amber-300' : 'border-gray-200/90' }} shadow-2xs p-4 sm:p-5 space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="text-sm font-bold text-gray-900">Courier updates
          @if($courierAttention->isNotEmpty())<span class="ml-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px]">{{ $courierAttention->count() }} need you</span>@endif
        </h2>
        <p class="text-xs text-gray-500">
          Delivered parcels are marked delivered automatically. Returns, holds and part deliveries are listed here.
          @if($lastSync) Last checked {{ \Illuminate\Support\Carbon::parse($lastSync['at'])->diffForHumans() }}.@endif
        </p>
      </div>
      <form method="POST" action="{{ route('admin.courier-scan.sync') }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
          Sync now
        </button>
      </form>
    </div>
    @if($courierAttention->isNotEmpty())
      <ul class="divide-y divide-gray-100 border border-gray-100 rounded-xl text-xs">
        @foreach($courierAttention as $o)
          <li class="flex flex-wrap items-center gap-x-3 gap-y-1 px-3 py-2">
            <a href="{{ route('admin.orders.show', $o) }}" class="font-mono font-bold text-brand-700 hover:underline">{{ $o->order_number }}</a>
            <span class="text-gray-700">{{ $o->customer_name }}</span>
            <span class="text-gray-400">{{ $o->courierLabel() }}</span>
            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 font-semibold">{{ \App\Services\Courier\CourierStatusUpdater::label($o->courier_status) }}</span>
            @if($o->courier_status_message)<span class="text-gray-500 truncate max-w-xs" title="{{ $o->courier_status_message }}">{{ $o->courier_status_message }}</span>@endif
            <span class="ml-auto text-gray-400">{{ $o->courier_synced_at?->diffForHumans() }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </div>

  {{-- Station Tabs --}}
  <div class="flex border-b border-gray-200 gap-4">
    <button type="button" onclick="switchScanTab('dispatch')" id="tabBtnDispatch" class="pb-3 text-sm font-bold border-b-2 border-brand-600 text-brand-600 flex items-center gap-2 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
      🚚 Courier OUT (Dispatch Handover)
      <span class="px-2 py-0.5 text-xs rounded-full bg-brand-50 text-brand-700 font-extrabold" id="dispatchTodayBadge">{{ $dispatchedToday->count() }}</span>
    </button>

    <button type="button" onclick="switchScanTab('return')" id="tabBtnReturn" class="pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 flex items-center gap-2 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
      🔄 Courier IN (Return Scan &amp; Restock)
      <span class="px-2 py-0.5 text-xs rounded-full bg-amber-50 text-amber-700 font-extrabold" id="returnTodayBadge">{{ $returnedToday->count() }}</span>
    </button>
  </div>

  {{-- TAB 1: DISPATCH SCAN (OUT) --}}
  <div id="tabContentDispatch" class="space-y-6">
    
    {{-- Dispatch Scanner Bar --}}
    <div class="bg-white rounded-2xl border-2 border-brand-500/80 shadow-sm p-5 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
        
        {{-- Courier Provider Selector --}}
        <div class="md:col-span-4">
          <label class="block text-xs font-bold text-gray-700 mb-1">Select Pickup Courier</label>
          <select id="dispatchCourierSelect" onchange="onCourierChange(this.value)" class="w-full text-xs font-bold px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
            @foreach($courierOptions as $key => $name)
              <option value="{{ $key }}" @selected($key === $lastCourier)>{{ $name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Barcode Gun Input --}}
        <div class="md:col-span-8">
          <label class="block text-xs font-bold text-gray-700 mb-1">Scan Parcel Barcode / Invoice # (Gun Ready)</label>
          <div class="flex gap-2">
          <div class="relative flex-1 min-w-0">
            <input type="text" id="dispatchScanInput" autofocus placeholder="Scan barcode with scanner gun or type order #..." class="w-full pl-10 pr-24 py-2.5 text-sm font-mono font-black bg-emerald-50/40 border-2 border-emerald-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-gray-900 placeholder-gray-400">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <button type="button" onclick="triggerDispatchScan()" class="absolute inset-y-1 right-1 px-4 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
              Enter
            </button>
          </div>
          <button type="button" onclick="openDispatchCamera()" class="shrink-0 w-11 rounded-xl border-2 border-emerald-500 bg-white text-emerald-700 hover:bg-emerald-50 flex items-center justify-center" title="Scan with camera" aria-label="Scan with camera">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
          </button>
          </div>
        </div>

      </div>

      {{-- Quick Pick / Awaiting Dispatch Helper --}}
      @if($awaitingDispatch->isNotEmpty())
        <div id="awaitingDispatchWrapper" class="pt-3 border-t border-gray-100 space-y-2">
          <div class="flex flex-wrap items-center justify-between gap-1">
            <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Awaiting Dispatch Handover (<span id="awaitingDispatchCount">{{ $awaitingDispatch->count() }}</span>):
            </span>
            <span class="flex items-center gap-3">
              <span class="text-[11px] text-gray-400">Click any order to dispatch instantly or scan with barcode gun</span>
              <a href="{{ route('admin.orders.labels', ['ready' => 1]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-[11px] font-bold text-gray-700" title="Print parcel labels for all confirmed and processing orders">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5v14"/><path d="M8 5v14"/><path d="M12 5v14"/><path d="M17 5v14"/><path d="M21 5v14"/></svg>
                Print labels
              </a>
            </span>
          </div>
          <div class="flex flex-wrap gap-2" id="awaitingDispatchList">
            @foreach($awaitingDispatch as $o)
              <button type="button" 
                onclick="quickDispatchOrder('{{ $o->order_number }}')" 
                id="awaiting-order-{{ $o->id }}"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-mono font-bold bg-gray-50 hover:bg-emerald-50 hover:text-emerald-800 hover:border-emerald-300 border border-gray-200 transition-all text-gray-700 shadow-2xs group cursor-pointer"
                title="Click to dispatch #{{ $o->order_number }} ({{ $o->customer_name }} - ৳{{ number_format($o->total) }})">
                <span class="text-emerald-600 group-hover:scale-110 transition-transform">🚚</span>
                <span>#{{ $o->order_number }}</span>
                <span class="font-sans font-medium text-gray-500 text-[11px]">৳{{ number_format($o->total) }}</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-sans font-extrabold {{ $o->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">{{ $o->status }}</span>
              </button>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Audio & Live Status Banner --}}
      <div id="dispatchAlertBox" class="hidden p-3 rounded-xl text-xs font-bold transition-all"></div>
    </div>

    {{-- Today's Dispatched Table --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-gray-900">Parcels Handed Over to Courier Today (<span id="dispatchCountHeader">{{ $dispatchedToday->count() }}</span>)</h3>
        <span class="text-xs text-gray-400">Automatic real-time log</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="dispatchTable">
          <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
            <tr>
              <th class="py-3 px-4">Order #</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">City</th>
              <th class="py-3 px-4">Courier</th>
              <th class="py-3 px-4 text-right">COD Total</th>
              <th class="py-3 px-4 text-center">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100" id="dispatchTableBody">
            @forelse($dispatchedToday as $ord)
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-2.5 px-4 font-mono font-bold text-brand-700">
                  <a href="{{ route('admin.orders.show', $ord) }}" target="_blank" class="hover:underline">{{ $ord->order_number }}</a>
                </td>
                <td class="py-2.5 px-4">
                  <span class="font-bold text-gray-800">{{ $ord->customer_name }}</span>
                  <span class="text-[11px] text-gray-400 block">{{ $ord->customer_phone }}</span>
                </td>
                <td class="py-2.5 px-4 text-gray-600">{{ $ord->city ?: 'N/A' }}</td>
                <td class="py-2.5 px-4">
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ ucfirst($ord->courier_name) }}
                  </span>
                </td>
                <td class="py-2.5 px-4 text-right font-extrabold text-gray-900">৳{{ number_format($ord->total, 2) }}</td>
                <td class="py-2.5 px-4 text-center text-gray-500">{{ $ord->courier_sent_at?->format('h:i A') }}</td>
              </tr>
            @empty
              <tr id="dispatchEmptyRow">
                <td colspan="6" class="py-8 text-center text-gray-400">No parcels dispatched yet today. Start scanning above!</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  {{-- TAB 2: RETURN SCAN (IN) --}}
  <div id="tabContentReturn" class="space-y-6 hidden">

    {{-- Return Scanner Bar --}}
    <div class="bg-white rounded-2xl border-2 border-amber-500 shadow-sm p-5 space-y-4">
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Scan Returned Parcel Barcode / Invoice #</label>
        <div class="flex gap-2">
        <div class="relative flex-1 min-w-0">
          <input type="text" id="returnScanInput" placeholder="Scan barcode on returned package..." class="w-full pl-10 pr-24 py-2.5 text-sm font-mono font-black bg-amber-50/40 border-2 border-amber-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/30 text-gray-900 placeholder-gray-400">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </div>
          <button type="button" onclick="triggerReturnLookup()" class="absolute inset-y-1 right-1 px-4 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors">
            Lookup
          </button>
        </div>
        <button type="button" onclick="openReturnCamera()" class="shrink-0 w-11 rounded-xl border-2 border-amber-500 bg-white text-amber-700 hover:bg-amber-50 flex items-center justify-center" title="Scan with camera" aria-label="Scan with camera">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
          </button>
        </div>
      </div>

      <div id="returnAlertBox" class="hidden p-3 rounded-xl text-xs font-bold transition-all"></div>
    </div>

    {{-- Returned Today Table --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-gray-900">Parcels Returned Today (<span id="returnCountHeader">{{ $returnedToday->count() }}</span>)</h3>
        <span class="text-xs text-gray-400">Audited returns with delivery fee impact</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="returnTable">
          <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
            <tr>
              <th class="py-3 px-4">Order #</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">Return Classification</th>
              <th class="py-3 px-4">Restocked?</th>
              <th class="py-3 px-4 text-right">Store Courier Loss</th>
              <th class="py-3 px-4 text-center">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100" id="returnTableBody">
            @forelse($returnedToday as $ret)
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-2.5 px-4 font-mono font-bold text-brand-700">
                  <a href="{{ route('admin.orders.show', $ret) }}" target="_blank" class="hover:underline">{{ $ret->order_number }}</a>
                </td>
                <td class="py-2.5 px-4">
                  <span class="font-bold text-gray-800">{{ $ret->customer_name }}</span>
                  <span class="text-[11px] text-gray-400 block">{{ $ret->customer_phone }}</span>
                </td>
                <td class="py-2.5 px-4">
                  @if($ret->return_type === 'paid_delivery')
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      ✓ Buyer Paid Delivery Charge
                    </span>
                  @else
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                      ❌ Failed Pickup (Unpaid Delivery)
                    </span>
                  @endif
                </td>
                <td class="py-2.5 px-4">
                  @if($ret->return_restocked)
                    <span class="text-emerald-600 font-bold">✓ Restocked</span>
                  @else
                    <span class="text-gray-400">Not restocked</span>
                  @endif
                </td>
                <td class="py-2.5 px-4 text-right font-extrabold text-rose-600">
                  @if($ret->courier_loss_amount > 0)
                    -৳{{ number_format($ret->courier_loss_amount, 2) }}
                  @else
                    <span class="text-emerald-700">৳0.00 (No Loss)</span>
                  @endif
                </td>
                <td class="py-2.5 px-4 text-center text-gray-500">{{ $ret->courier_returned_at?->format('h:i A') }}</td>
              </tr>
            @empty
              <tr id="returnEmptyRow">
                <td colspan="6" class="py-8 text-center text-gray-400">No returns processed yet today.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

{{-- MODAL: Return Confirmation & Delivery Fee Decision --}}
<div id="returnDecisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h3 class="font-extrabold text-gray-900 text-base" id="returnModalOrderNumber">Process Returned Parcel</h3>
        <p class="text-xs text-gray-500" id="returnModalCustomerInfo">Customer: Tanvir Ahmed (017XXXXXXXX)</p>
      </div>
      <button type="button" onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
    </div>

    <input type="hidden" id="returnModalOrderId">

    {{-- Already Returned Warning Banner --}}
    <div id="returnModalAlreadyReturnedAlert" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-800 text-xs font-bold">
      ⚠️ Notice: This order is already marked as RETURNED. Confirming will update the return details.
    </div>

    {{-- Order Items Snapshot --}}
    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs text-gray-700 space-y-1">
      <div class="flex justify-between">
        <span class="text-gray-500">Items:</span>
        <span class="font-semibold text-right max-w-[280px] truncate" id="returnModalItemsSummary">Mustard Oil 1L</span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Order Total:</span>
        <span class="font-bold text-gray-900" id="returnModalOrderTotal">৳850.00</span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Courier Shipping Fee:</span>
        <span class="font-bold text-gray-900" id="returnModalShippingFee">৳130.00</span>
      </div>
    </div>

    {{-- Select Return Type (The 2 Crucial Types) --}}
    <div class="space-y-2">
      <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider">Select Return Circumstance <span class="text-rose-500">*</span></label>
      
      {{-- Type 1: Buyer Paid Delivery Charge --}}
      <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-emerald-200 bg-emerald-50/40 hover:bg-emerald-50 cursor-pointer transition-colors">
        <input type="radio" name="modal_return_type" value="paid_delivery" checked class="mt-1 w-4 h-4 text-emerald-600 focus:ring-emerald-500">
        <div>
          <div class="font-extrabold text-xs text-emerald-900">Type 1: Buyer PAID Delivery Charge</div>
          <div class="text-[11px] text-emerald-700 mt-0.5 leading-relaxed">
            Buyer paid delivery fee at doorstep or in advance. <strong class="underline">Your business has ৳0 courier loss.</strong>
          </div>
        </div>
      </label>

      {{-- Type 2: Buyer Ghosted / Didn't Pickup --}}
      <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-rose-200 bg-rose-50/40 hover:bg-rose-50 cursor-pointer transition-colors">
        <input type="radio" name="modal_return_type" value="unpaid_delivery" class="mt-1 w-4 h-4 text-rose-600 focus:ring-rose-500">
        <div>
          <div class="font-extrabold text-xs text-rose-900">Type 2: Buyer DID NOT Pick Up / Refused All</div>
          <div class="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
            Unreachable phone, fake address, or customer ghosted. <strong class="underline">Store bears courier delivery fee loss.</strong>
          </div>
        </div>
      </label>
    </div>

    {{-- Return Reason / Note --}}
    <div>
      <label class="block text-xs font-bold text-gray-700 mb-1">Return Reason / Note (Optional)</label>
      <input type="text" id="modalReturnReason" list="commonReturnReasons" placeholder="e.g. Unreachable phone, refused package, wrong size..." class="w-full text-xs px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-amber-500">
      <datalist id="commonReturnReasons">
        <option value="Customer unreachable / phone switched off">
        <option value="Customer refused delivery at doorstep">
        <option value="Wrong product or size requested exchange">
        <option value="Damaged in transit / courier delay">
        <option value="Customer cancelled order on arrival">
        <option value="Customer returned item (delivery charge paid)">
      </datalist>
    </div>

    {{-- Restock Checkbox --}}
    <div class="pt-1">
      <label class="flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer">
        <input type="checkbox" id="modalRestockCheck" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
        <div>
          <span class="text-xs font-bold text-gray-800">Restock Product(s) into Inventory</span>
          <span class="text-[11px] text-gray-500 block" id="modalRestockSubtext">Increments available product/SKU quantity</span>
        </div>
      </label>
    </div>

    {{-- Submit Buttons --}}
    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
      <button type="button" onclick="closeReturnModal()" class="flex-1 py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors">
        Cancel
      </button>
      <button type="button" id="confirmReturnBtn" onclick="submitReturnConfirm()" class="flex-1 py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition-all">
        Confirm Return &amp; Save
      </button>
    </div>

  </div>
</div>

@push('scripts')
<script src="{{ asset('theme/js/camera-scanner.js') }}?v={{ @filemtime(public_path('theme/js/camera-scanner.js')) ?: '1' }}"></script>
<script>
  let scanAudioCtx = null;

  function playSound(type = 'success') {
    try {
      if (!scanAudioCtx) {
        scanAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
      }
      if (scanAudioCtx.state === 'suspended') scanAudioCtx.resume();

      const osc = scanAudioCtx.createOscillator();
      const gain = scanAudioCtx.createGain();
      osc.connect(gain);
      gain.connect(scanAudioCtx.destination);

      if (type === 'success') {
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, scanAudioCtx.currentTime);
        gain.gain.setValueAtTime(0.2, scanAudioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, scanAudioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(scanAudioCtx.currentTime + 0.15);
      } else {
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(200, scanAudioCtx.currentTime);
        gain.gain.setValueAtTime(0.35, scanAudioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, scanAudioCtx.currentTime + 0.35);
        osc.start();
        osc.stop(scanAudioCtx.currentTime + 0.35);
      }
    } catch(e){}
  }

  // Switch Tabs
  function switchScanTab(tab) {
    const btnDispatch = document.getElementById('tabBtnDispatch');
    const btnReturn = document.getElementById('tabBtnReturn');
    const contentDispatch = document.getElementById('tabContentDispatch');
    const contentReturn = document.getElementById('tabContentReturn');

    if (tab === 'dispatch') {
      btnDispatch.className = "pb-3 text-sm font-bold border-b-2 border-brand-600 text-brand-600 flex items-center gap-2 transition-all";
      btnReturn.className = "pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 flex items-center gap-2 transition-all";
      contentDispatch.classList.remove('hidden');
      contentReturn.classList.add('hidden');
      document.getElementById('dispatchScanInput').focus();
    } else {
      btnReturn.className = "pb-3 text-sm font-bold border-b-2 border-amber-600 text-amber-600 flex items-center gap-2 transition-all";
      btnDispatch.className = "pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 flex items-center gap-2 transition-all";
      contentReturn.classList.remove('hidden');
      contentDispatch.classList.add('hidden');
      document.getElementById('returnScanInput').focus();
    }
  }

  function onCourierChange(val) {
    try {
      if (val) {
        localStorage.setItem('marty_last_pickup_courier', val);
      }
    } catch(e) {}
  }

  // Initial
  document.addEventListener('DOMContentLoaded', function() {
    // Restore last selected pickup courier if saved in browser
    try {
      const savedCourier = localStorage.getItem('marty_last_pickup_courier');
      if (savedCourier) {
        const select = document.getElementById('dispatchCourierSelect');
        if (select && select.querySelector(`option[value="${savedCourier}"]`)) {
          select.value = savedCourier;
        }
      }
    } catch(e) {}

    const dispatchInput = document.getElementById('dispatchScanInput');
    dispatchInput.focus();

    dispatchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        triggerDispatchScan();
      }
    });

    const returnInput = document.getElementById('returnScanInput');
    returnInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        triggerReturnLookup();
      }
    });
  });

  // DISPATCH SCAN LOGIC
  // Returns { ok, message } so the camera scanner can show the result.
  async function triggerDispatchScan(forcedCode = null, fromCamera = false) {
    const input = document.getElementById('dispatchScanInput');
    const code = (forcedCode || input.value || '').trim();
    if (!code) {
      input.focus();
      return;
    }

    const courier = document.getElementById('dispatchCourierSelect').value;
    onCourierChange(courier);
    const alertBox = document.getElementById('dispatchAlertBox');

    // Show processing indicator
    alertBox.className = "p-3 rounded-xl text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 block";
    alertBox.innerText = `⏳ Processing dispatch scan for: "${code}"...`;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    try {
      const res = await fetch("{{ route('admin.courier-scan.dispatch') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
          _token: token,
          code: code,
          courier_name: courier
        })
      });

      const data = await res.json().catch(() => null);

      input.value = '';
      if (!fromCamera) input.focus();

      if (!res.ok || !data) {
        playSound('error');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
        alertBox.innerText = (data && data.message) ? data.message : `HTTP ${res.status}: Failed to communicate with dispatch server.`;
        return { ok: false, message: alertBox.innerText };
      }

      if (data.success) {
        playSound('success');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 block";
        alertBox.innerText = data.message;

        addDispatchTableRow(data.order);

        // Remove from awaiting dispatch list if present
        if (data.order && data.order.id) {
          const pill = document.getElementById(`awaiting-order-${data.order.id}`);
          if (pill) {
            pill.remove();
            const countEl = document.getElementById('awaitingDispatchCount');
            if (countEl) {
              const remaining = parseInt(countEl.innerText) - 1;
              countEl.innerText = Math.max(0, remaining);
              if (remaining <= 0) {
                document.getElementById('awaitingDispatchWrapper')?.remove();
              }
            }
          }
        }
        return { ok: true, message: data.message };
      } else {
        playSound('error');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
        alertBox.innerText = data.message || 'Dispatch scan rejected.';
        return { ok: false, message: alertBox.innerText };
      }
    } catch (err) {
      playSound('error');
      alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
      alertBox.innerText = "Network or script error: " + (err.message || 'Unknown error');
      console.error(err);
      return { ok: false, message: alertBox.innerText };
    }
  }

  // Several parcels can be dispatched in a row without closing the camera.
  function openDispatchCamera() {
    CameraScanner.open({
      title: 'Dispatch parcels',
      continuous: true,
      onScan: code => triggerDispatchScan(code, true),
    });
  }

  // A return is looked up one parcel at a time, so the camera closes to show the details.
  function openReturnCamera() {
    CameraScanner.open({
      title: 'Scan returned parcel',
      onScan: code => {
        document.getElementById('returnScanInput').value = code;
        triggerReturnLookup();
        return { ok: true, message: `Looking up ${code}` };
      },
    });
  }

  // Order data includes what customers typed at checkout, so it is escaped before going into HTML.
  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch]);
  }

  function quickDispatchOrder(orderNum) {
    const input = document.getElementById('dispatchScanInput');
    input.value = orderNum;
    triggerDispatchScan(orderNum);
  }

  function addDispatchTableRow(ord) {
    const empty = document.getElementById('dispatchEmptyRow');
    if (empty) empty.remove();

    const tbody = document.getElementById('dispatchTableBody');
    const tr = document.createElement('tr');
    tr.className = "bg-emerald-50/40 hover:bg-emerald-50 transition-colors animate-pulse";

    tr.innerHTML = `
      <td class="py-2.5 px-4 font-mono font-bold text-brand-700">
        <a href="/admin/orders/${encodeURIComponent(ord.order_number)}" target="_blank" class="hover:underline">${escapeHtml(ord.order_number)}</a>
      </td>
      <td class="py-2.5 px-4">
        <span class="font-bold text-gray-800">${escapeHtml(ord.customer_name)}</span>
        <span class="text-[11px] text-gray-400 block">${escapeHtml(ord.phone)}</span>
      </td>
      <td class="py-2.5 px-4 text-gray-600">${escapeHtml(ord.city || 'N/A')}</td>
      <td class="py-2.5 px-4">
        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
          ${escapeHtml(ord.courier)}
        </span>
      </td>
      <td class="py-2.5 px-4 text-right font-extrabold text-gray-900">৳${parseFloat(ord.total).toFixed(2)}</td>
      <td class="py-2.5 px-4 text-center text-gray-500">${escapeHtml(ord.dispatched_at)}</td>
    `;

    tbody.insertBefore(tr, tbody.firstChild);

    // Update count
    const header = document.getElementById('dispatchCountHeader');
    const badge = document.getElementById('dispatchTodayBadge');
    const current = parseInt(header.innerText) || 0;
    header.innerText = current + 1;
    badge.innerText = current + 1;
  }

  // RETURN SCAN LOGIC
  function triggerReturnLookup() {
    const input = document.getElementById('returnScanInput');
    const code = input.value.trim();
    if (!code) return;

    const alertBox = document.getElementById('returnAlertBox');

    fetch(`{{ route('admin.courier-scan.return-lookup') }}?code=${encodeURIComponent(code)}`)
      .then(async res => {
        const data = await res.json().catch(() => null);
        if (!res.ok || !data) {
          throw new Error((data && data.message) || 'Error looking up parcel (HTTP ' + res.status + ')');
        }
        return data;
      })
      .then(data => {
        if (!data.success) {
          playSound('error');
          alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
          alertBox.innerText = data.message;
          input.select();
          return;
        }

        playSound('success');
        alertBox.classList.add('hidden');
        input.value = '';

        // Open Decision Modal
        const ord = data.order;
        document.getElementById('returnModalOrderId').value = ord.id;
        document.getElementById('returnModalOrderNumber').innerText = `Process Return: ${ord.order_number}`;
        document.getElementById('returnModalCustomerInfo').innerText = `Customer: ${ord.customer_name} (${ord.phone}) | Courier: ${ord.courier_name}`;
        document.getElementById('returnModalItemsSummary').innerText = ord.items_summary;
        document.getElementById('returnModalOrderTotal').innerText = `৳${ord.total.toFixed(2)}`;
        document.getElementById('returnModalShippingFee').innerText = `৳${ord.shipping_charge.toFixed(2)}`;

        // Warning if already returned
        const alreadyAlert = document.getElementById('returnModalAlreadyReturnedAlert');
        if (ord.already_returned) {
          alreadyAlert.classList.remove('hidden');
        } else {
          alreadyAlert.classList.add('hidden');
        }

        // Restock checkbox configuration
        const restockCheck = document.getElementById('modalRestockCheck');
        const restockSubtext = document.getElementById('modalRestockSubtext');
        if (ord.return_restocked) {
          restockCheck.checked = false;
          restockSubtext.innerText = "✓ Items were already restocked previously (uncheck to avoid duplicate)";
        } else {
          restockCheck.checked = true;
          restockSubtext.innerText = "Increments available product/SKU quantity";
        }

        document.getElementById('modalReturnReason').value = '';
        document.getElementById('returnDecisionModal').classList.remove('hidden');
      })
      .catch(err => {
        playSound('error');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
        alertBox.innerText = err.message || 'Error looking up parcel.';
        console.error(err);
      });
  }

  function closeReturnModal() {
    document.getElementById('returnDecisionModal').classList.add('hidden');
    document.getElementById('returnScanInput').focus();
  }

  function submitReturnConfirm() {
    const orderId = document.getElementById('returnModalOrderId').value;
    const returnType = document.querySelector('input[name="modal_return_type"]:checked').value;
    const restock = document.getElementById('modalRestockCheck').checked;
    const returnReason = document.getElementById('modalReturnReason').value.trim();

    const btn = document.getElementById('confirmReturnBtn');
    btn.disabled = true;
    btn.innerText = "Processing...";

    fetch("{{ route('admin.courier-scan.return-confirm') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        order_id: orderId,
        return_type: returnType,
        restock: restock ? 1 : 0,
        return_reason: returnReason
      })
    })
    .then(async res => {
      const data = await res.json().catch(() => null);
      if (!res.ok || !data) {
        throw new Error((data && data.message) || 'Error saving return (HTTP ' + res.status + ')');
      }
      return data;
    })
    .then(data => {
      btn.disabled = false;
      btn.innerText = "Confirm Return & Save";

      if (data.success) {
        playSound('success');
        closeReturnModal();
        addReturnTableRow(data.order);

        const alertBox = document.getElementById('returnAlertBox');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 block";
        alertBox.innerText = data.message;
      } else {
        playSound('error');
        alert(data.message || 'Error updating return');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerText = "Confirm Return & Save";
      playSound('error');
      alert(err.message || 'Server error while processing return.');
      console.error(err);
    });
  }

  function addReturnTableRow(ord) {
    const empty = document.getElementById('returnEmptyRow');
    if (empty) empty.remove();

    const tbody = document.getElementById('returnTableBody');
    const tr = document.createElement('tr');
    tr.className = "bg-amber-50/40 hover:bg-amber-50 transition-colors";

    const typeBadge = ord.return_type === 'paid_delivery'
      ? `<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ Buyer Paid Delivery Charge</span>`
      : `<span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">❌ Failed Pickup (Unpaid Delivery)</span>`;

    const lossDisplay = ord.courier_loss_amount > 0
      ? `-৳${parseFloat(ord.courier_loss_amount).toFixed(2)}`
      : `<span class="text-emerald-700">৳0.00 (No Loss)</span>`;

    tr.innerHTML = `
      <td class="py-2.5 px-4 font-mono font-bold text-brand-700">
        <a href="/admin/orders/${encodeURIComponent(ord.order_number)}" target="_blank" class="hover:underline">${escapeHtml(ord.order_number)}</a>
      </td>
      <td class="py-2.5 px-4">
        <span class="font-bold text-gray-800">${escapeHtml(ord.customer_name)}</span>
      </td>
      <td class="py-2.5 px-4">${typeBadge}</td>
      <td class="py-2.5 px-4 font-bold ${ord.return_restocked ? 'text-emerald-600' : 'text-gray-400'}">
        ${ord.return_restocked ? '✓ Restocked' : 'Not restocked'}
      </td>
      <td class="py-2.5 px-4 text-right font-extrabold text-rose-600">${lossDisplay}</td>
      <td class="py-2.5 px-4 text-center text-gray-500">${escapeHtml(ord.returned_at)}</td>
    `;

    tbody.insertBefore(tr, tbody.firstChild);

    // Update count
    const header = document.getElementById('returnCountHeader');
    const badge = document.getElementById('returnTodayBadge');
    const current = parseInt(header.innerText) || 0;
    header.innerText = current + 1;
    badge.innerText = current + 1;
  }
</script>
@endpush
@endsection
