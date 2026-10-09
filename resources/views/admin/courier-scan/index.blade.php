@extends('layouts.admin')
@section('title', 'Courier scan')
@section('subtitle', 'Scan parcels out to the courier and scan returns back in.')

@section('page-actions')
  @php
    $syncTitle = 'Ask Steadfast, Pathao and RedX for the latest parcel status. Delivered parcels are marked delivered automatically.'
      . ($lastSync ? ' Last checked ' . \Illuminate\Support\Carbon::parse($lastSync['at'])->diffForHumans() . '.' : ' Not checked yet.');
  @endphp
  <form method="POST" action="{{ route('admin.courier-scan.sync') }}" class="shrink-0" onsubmit="this.querySelector('button').disabled = true; this.querySelector('[data-label]').textContent = 'Syncing...';">
    @csrf
    <button type="submit" class="pill-btn disabled:opacity-60" title="{{ $syncTitle }}" aria-label="Sync courier status now">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
      <span data-label>Sync now</span>
      @if($lastSync)<span class="hidden sm:inline text-[11px] font-normal text-gray-400">· {{ \Illuminate\Support\Carbon::parse($lastSync['at'])->diffForHumans(null, true, true) }} ago</span>@endif
    </button>
  </form>
  <a href="{{ route('admin.courier-scan.manifest') }}" target="_blank" class="pill-btn">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
    Today's manifest
  </a>
@endsection

@section('content')
<div class="space-y-4">

  {{-- Only shown when a courier reported something staff must act on (returns, holds, part deliveries). --}}
  @if($courierAttention->isNotEmpty())
    <details class="rounded-2xl bg-amber-50/80 ring-1 ring-amber-100 px-3.5 py-2.5 group" @if($courierAttention->count() <= 3) open @endif>
      <summary class="flex items-center justify-between gap-2 cursor-pointer text-[13px]">
        <span class="font-semibold text-amber-900">{{ $courierAttention->count() }} need you <span class="font-normal text-amber-800">· returns, holds and part deliveries reported by the courier</span></span>
        <svg class="w-4 h-4 text-amber-700 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
      </summary>
      <ul class="mt-2 divide-y divide-amber-100 text-xs">
        @foreach($courierAttention as $o)
          <li class="flex flex-wrap items-center gap-x-3 gap-y-1 py-2">
            <a href="{{ route('admin.orders.show', $o) }}" class="font-semibold text-gray-900 hover:underline">{{ $o->order_number }}</a>
            <span class="text-gray-700">{{ $o->customer_name }}</span>
            <span class="text-gray-500">{{ $o->courierLabel() }}</span>
            <span class="px-2 py-0.5 rounded-full bg-white text-amber-700 text-[11px] font-semibold">{{ \App\Services\Courier\CourierStatusUpdater::label($o->courier_status) }}</span>
            @if($o->courier_status_message)<span class="text-gray-500 truncate max-w-full sm:max-w-xs" title="{{ $o->courier_status_message }}">{{ $o->courier_status_message }}</span>@endif
            <span class="sm:ml-auto text-gray-400">{{ $o->courier_synced_at?->diffForHumans() }}</span>
          </li>
        @endforeach
      </ul>
    </details>
  @endif

  {{-- Station tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Scan station">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      <button type="button" onclick="switchScanTab('dispatch')" id="tabBtnDispatch" class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-white bg-[var(--brand-dark)]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
        Courier out
        <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums bg-gray-100 text-gray-700" id="dispatchTodayBadge">{{ $dispatchedToday->count() }}</span>
      </button>
      <button type="button" onclick="switchScanTab('return')" id="tabBtnReturn" class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-gray-600 hover:bg-gray-100">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
        Courier in (returns)
        <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums bg-gray-100 text-gray-700" id="returnTodayBadge">{{ $returnedToday->count() }}</span>
      </button>
    </div>
  </nav>

  {{-- TAB 1: DISPATCH SCAN (OUT) --}}
  <div id="tabContentDispatch" class="space-y-4">

    <section class="panel p-4 sm:p-5 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
        <div class="md:col-span-4">
          <label class="lbl" for="dispatchCourierSelect">Pickup courier</label>
          <select id="dispatchCourierSelect" onchange="onCourierChange(this.value)" class="w-full h-11 rounded-xl border border-gray-200 bg-white px-3.5 text-sm cursor-pointer">
            @foreach($courierOptions as $key => $name)
              <option value="{{ $key }}" @selected($key === $lastCourier)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-8">
          <label class="lbl" for="dispatchScanInput">Scan parcel barcode or order number</label>
          <div class="flex gap-2">
            <div class="relative flex-1 min-w-0">
              <input type="text" id="dispatchScanInput" placeholder="Scan or type order number" class="w-full h-11 pl-10 pr-20 rounded-xl border border-gray-200 bg-white text-sm font-mono font-medium text-gray-900 placeholder-gray-400">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M8 8v8M12 8v8M16 8v8"/></svg>
              </div>
              <button type="button" onclick="triggerDispatchScan()" class="absolute inset-y-1.5 right-1.5 px-3.5 rounded-lg text-white text-xs font-semibold" style="background: var(--brand-dark);">Enter</button>
            </div>
            <button type="button" onclick="openDispatchCamera()" class="shrink-0 h-11 w-11 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center" title="Scan with camera" aria-label="Scan with camera">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            </button>
          </div>
        </div>
      </div>

      {{-- Awaiting dispatch quick picks --}}
      @if($awaitingDispatch->isNotEmpty())
        <div id="awaitingDispatchWrapper" class="pt-3 border-t border-gray-100 space-y-2.5">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
              <p class="text-[13px] font-semibold text-gray-900 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                Waiting for pickup (<span id="awaitingDispatchCount">{{ $awaitingDispatch->count() }}</span>)
              </p>
              <p class="text-[11px] text-gray-500">Tap an order to dispatch it, or scan its label.</p>
            </div>
            <a href="{{ route('admin.orders.labels', ['ready' => 1]) }}" target="_blank" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5" title="Print parcel labels for all confirmed and processing orders">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5v14"/><path d="M8 5v14"/><path d="M12 5v14"/><path d="M17 5v14"/><path d="M21 5v14"/></svg>
              Print labels
            </a>
          </div>
          <div class="flex flex-wrap gap-1.5" id="awaitingDispatchList">
            @foreach($awaitingDispatch as $o)
              <button type="button"
                onclick="quickDispatchOrder('{{ $o->order_number }}')"
                id="awaiting-order-{{ $o->id }}"
                class="inline-flex items-center gap-2 h-8 px-3 rounded-full text-xs whitespace-nowrap bg-gray-50 hover:bg-emerald-50 ring-1 ring-gray-200 hover:ring-emerald-200 text-gray-800 transition-colors cursor-pointer"
                title="Click to dispatch #{{ $o->order_number }} ({{ $o->customer_name }} - ৳{{ number_format($o->total) }})">
                <span class="font-semibold">#{{ $o->order_number }}</span>
                <span class="text-gray-500 tabular-nums">৳{{ number_format($o->total) }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $o->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' }}">{{ ucfirst($o->status) }}</span>
              </button>
            @endforeach
          </div>
        </div>
      @endif

      <div id="dispatchAlertBox" class="hidden p-3 rounded-xl text-xs font-medium transition-all"></div>
    </section>

    {{-- Dispatched today --}}
    <div class="card overflow-hidden">
      <div class="p-4 sm:p-5 flex items-center justify-between gap-3">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Handed over today (<span id="dispatchCountHeader">{{ $dispatchedToday->count() }}</span>)</h2>
          <p class="text-xs text-gray-500 mt-0.5">Updates as you scan.</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px]" id="dispatchTable">
          <thead>
            <tr class="whitespace-nowrap border-y border-gray-100">
              <th class="py-3 px-4">Order</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">City</th>
              <th class="py-3 px-4">Courier</th>
              <th class="py-3 px-4 text-right">COD total</th>
              <th class="py-3 px-4 text-center">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100" id="dispatchTableBody">
            @forelse($dispatchedToday as $ord)
              <tr>
                <td class="py-3 px-4 font-semibold text-gray-900 whitespace-nowrap">
                  <a href="{{ route('admin.orders.show', $ord) }}" target="_blank" class="hover:underline">{{ $ord->order_number }}</a>
                </td>
                <td class="py-3 px-4">
                  <span class="font-medium text-gray-900">{{ $ord->customer_name }}</span>
                  <span class="text-[11px] text-gray-500 block tabular-nums">{{ $ord->customer_phone }}</span>
                </td>
                <td class="py-3 px-4 text-gray-600">{{ $ord->city ?: 'N/A' }}</td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700">{{ ucfirst($ord->courier_name) }}</span>
                </td>
                <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">৳{{ number_format($ord->total, 2) }}</td>
                <td class="py-3 px-4 text-center text-gray-500 whitespace-nowrap">{{ $ord->courier_sent_at?->format('h:i A') }}</td>
              </tr>
            @empty
              <tr id="dispatchEmptyRow">
                <td colspan="6" class="py-10 text-center text-gray-500 text-sm">No parcels handed over yet today.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  {{-- TAB 2: RETURN SCAN (IN) --}}
  <div id="tabContentReturn" class="space-y-4 hidden">

    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <label class="lbl" for="returnScanInput">Scan returned parcel barcode or order number</label>
        <div class="flex gap-2">
          <div class="relative flex-1 min-w-0">
            <input type="text" id="returnScanInput" placeholder="Scan the returned package" class="w-full h-11 pl-10 pr-24 rounded-xl border border-gray-200 bg-white text-sm font-mono font-medium text-gray-900 placeholder-gray-400">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M8 8v8M12 8v8M16 8v8"/></svg>
            </div>
            <button type="button" onclick="triggerReturnLookup()" class="absolute inset-y-1.5 right-1.5 px-3.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">Look up</button>
          </div>
          <button type="button" onclick="openReturnCamera()" class="shrink-0 h-11 w-11 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center" title="Scan with camera" aria-label="Scan with camera">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
          </button>
        </div>
      </div>
      <div id="returnAlertBox" class="hidden p-3 rounded-xl text-xs font-medium transition-all"></div>
    </section>

    {{-- Returned today --}}
    <div class="card overflow-hidden">
      <div class="p-4 sm:p-5">
        <h2 class="text-[15px] font-semibold text-gray-900">Returned today (<span id="returnCountHeader">{{ $returnedToday->count() }}</span>)</h2>
        <p class="text-xs text-gray-500 mt-0.5">With the delivery fee each return cost you.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px]" id="returnTable">
          <thead>
            <tr class="whitespace-nowrap border-y border-gray-100">
              <th class="py-3 px-4">Order</th>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4">Return type</th>
              <th class="py-3 px-4">Restocked</th>
              <th class="py-3 px-4 text-right">Courier loss</th>
              <th class="py-3 px-4 text-center">Time</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100" id="returnTableBody">
            @forelse($returnedToday as $ret)
              <tr>
                <td class="py-3 px-4 font-semibold text-gray-900 whitespace-nowrap">
                  <a href="{{ route('admin.orders.show', $ret) }}" target="_blank" class="hover:underline">{{ $ret->order_number }}</a>
                </td>
                <td class="py-3 px-4">
                  <span class="font-medium text-gray-900">{{ $ret->customer_name }}</span>
                  <span class="text-[11px] text-gray-500 block tabular-nums">{{ $ret->customer_phone }}</span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  @if($ret->return_type === 'paid_delivery')
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">Buyer paid delivery</span>
                  @else
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700">Failed pickup (unpaid)</span>
                  @endif
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  @if($ret->return_restocked)
                    <span class="text-emerald-700 font-medium">Restocked</span>
                  @else
                    <span class="text-gray-400">Not restocked</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-right font-semibold text-rose-600 tabular-nums whitespace-nowrap">
                  @if($ret->courier_loss_amount > 0)
                    -৳{{ number_format($ret->courier_loss_amount, 2) }}
                  @else
                    <span class="text-emerald-700">৳0.00 (no loss)</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-center text-gray-500 whitespace-nowrap">{{ $ret->courier_returned_at?->format('h:i A') }}</td>
              </tr>
            @empty
              <tr id="returnEmptyRow">
                <td colspan="6" class="py-10 text-center text-gray-500 text-sm">No returns processed yet today.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

{{-- MODAL: Return Confirmation & Delivery Fee Decision --}}
<div id="returnDecisionModal" class="fixed inset-0 z-50 bg-gray-950/50 flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[calc(100vh-2rem)] overflow-y-auto">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <h3 class="text-[15px] font-semibold text-gray-900" id="returnModalOrderNumber">Process returned parcel</h3>
        <p class="text-xs text-gray-500 mt-0.5" id="returnModalCustomerInfo">Customer: Tanvir Ahmed (017XXXXXXXX)</p>
      </div>
      <button type="button" onclick="closeReturnModal()" class="h-8 w-8 grid place-items-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 shrink-0" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <input type="hidden" id="returnModalOrderId">

    <div id="returnModalAlreadyReturnedAlert" class="hidden p-3 rounded-xl bg-amber-50 text-amber-800 text-xs font-medium">
      This order is already marked as returned. Confirming will update the return details.
    </div>

    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-700 space-y-1.5">
      <div class="flex justify-between gap-3">
        <span class="text-gray-500">Items</span>
        <span class="font-medium text-right max-w-[240px] truncate" id="returnModalItemsSummary">Mustard Oil 1L</span>
      </div>
      <div class="flex justify-between gap-3">
        <span class="text-gray-500">Order total</span>
        <span class="font-semibold text-gray-900 tabular-nums" id="returnModalOrderTotal">৳850.00</span>
      </div>
      <div class="flex justify-between gap-3">
        <span class="text-gray-500">Courier shipping fee</span>
        <span class="font-semibold text-gray-900 tabular-nums" id="returnModalShippingFee">৳130.00</span>
      </div>
    </div>

    <div class="space-y-2">
      <p class="text-xs font-semibold text-gray-700">What happened? <span class="text-rose-500">*</span></p>
      <label class="flex items-start gap-3 p-3 rounded-xl ring-1 ring-gray-200 hover:bg-gray-50 cursor-pointer has-[:checked]:ring-emerald-300 has-[:checked]:bg-emerald-50/50">
        <input type="radio" name="modal_return_type" value="paid_delivery" checked class="mt-0.5 w-4 h-4">
        <div>
          <p class="text-[13px] font-semibold text-gray-900">Buyer paid the delivery charge</p>
          <p class="text-[11px] text-gray-500 mt-0.5">Delivery fee was paid at the door or in advance. No courier loss.</p>
        </div>
      </label>
      <label class="flex items-start gap-3 p-3 rounded-xl ring-1 ring-gray-200 hover:bg-gray-50 cursor-pointer has-[:checked]:ring-rose-300 has-[:checked]:bg-rose-50/50">
        <input type="radio" name="modal_return_type" value="unpaid_delivery" class="mt-0.5 w-4 h-4">
        <div>
          <p class="text-[13px] font-semibold text-gray-900">Buyer did not pick up or refused</p>
          <p class="text-[11px] text-gray-500 mt-0.5">Unreachable phone, fake address or no show. The store pays the delivery fee.</p>
        </div>
      </label>
    </div>

    <div>
      <label class="lbl" for="modalReturnReason">Reason or note (optional)</label>
      <input type="text" id="modalReturnReason" list="commonReturnReasons" placeholder="e.g. Unreachable phone, refused package" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm">
      <datalist id="commonReturnReasons">
        <option value="Customer unreachable / phone switched off">
        <option value="Customer refused delivery at doorstep">
        <option value="Wrong product or size requested exchange">
        <option value="Damaged in transit / courier delay">
        <option value="Customer cancelled order on arrival">
        <option value="Customer returned item (delivery charge paid)">
      </datalist>
    </div>

    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 cursor-pointer">
      <input type="checkbox" id="modalRestockCheck" checked class="w-4 h-4 rounded">
      <div>
        <span class="text-[13px] font-medium text-gray-900">Put items back in stock</span>
        <span class="text-[11px] text-gray-500 block" id="modalRestockSubtext">Increments available product/SKU quantity</span>
      </div>
    </label>

    <div class="flex items-center justify-end gap-2 pt-1">
      <button type="button" onclick="closeReturnModal()" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Cancel</button>
      <button type="button" id="confirmReturnBtn" onclick="submitReturnConfirm()" class="h-9 px-4 rounded-full bg-amber-600 hover:bg-amber-700 text-white text-[13px] font-semibold disabled:opacity-60">Confirm return</button>
    </div>
  </div>
</div>

@push('scripts')
<script src="{{ asset('theme/js/camera-scanner.js') }}?v={{ @filemtime(public_path('theme/js/camera-scanner.js')) ?: '1' }}"></script>
<script>
  // Only jump into the scan box on a desktop with a mouse; on phones and tablets it would pop the keyboard open.
  const canAutoFocus = () => window.matchMedia('(min-width: 1024px) and (hover: hover)').matches;

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
      btnDispatch.className = "h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-white bg-[var(--brand-dark)]";
      btnReturn.className = "h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-gray-600 hover:bg-gray-100";
      contentDispatch.classList.remove('hidden');
      contentReturn.classList.add('hidden');
      if (canAutoFocus()) document.getElementById('dispatchScanInput').focus();
    } else {
      btnReturn.className = "h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-white bg-amber-600";
      btnDispatch.className = "h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors text-gray-600 hover:bg-gray-100";
      contentReturn.classList.remove('hidden');
      contentDispatch.classList.add('hidden');
      if (canAutoFocus()) document.getElementById('returnScanInput').focus();
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
    if (canAutoFocus()) dispatchInput.focus();

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
    alertBox.className = "p-3 rounded-xl text-xs font-medium bg-blue-50 text-blue-800 block";
    alertBox.innerText = `Processing dispatch scan for "${code}"...`;

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
        alertBox.className = "p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-800 block";
        alertBox.innerText = (data && data.message) ? data.message : `HTTP ${res.status}: Failed to communicate with dispatch server.`;
        return { ok: false, message: alertBox.innerText };
      }

      if (data.success) {
        playSound('success');
        alertBox.className = "p-3 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-800 block";
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
        alertBox.className = "p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-800 block";
        alertBox.innerText = data.message || 'Dispatch scan rejected.';
        return { ok: false, message: alertBox.innerText };
      }
    } catch (err) {
      playSound('error');
      alertBox.className = "p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-800 block";
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
    tr.className = "bg-emerald-50/50 transition-colors";

    tr.innerHTML = `
      <td class="py-3 px-4 font-semibold text-gray-900 whitespace-nowrap">
        <a href="/admin/orders/${encodeURIComponent(ord.order_number)}" target="_blank" class="hover:underline">${escapeHtml(ord.order_number)}</a>
      </td>
      <td class="py-3 px-4">
        <span class="font-medium text-gray-900">${escapeHtml(ord.customer_name)}</span>
        <span class="text-[11px] text-gray-400 block">${escapeHtml(ord.phone)}</span>
      </td>
      <td class="py-3 px-4 text-gray-600">${escapeHtml(ord.city || 'N/A')}</td>
      <td class="py-3 px-4">
        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700">
          ${escapeHtml(ord.courier)}
        </span>
      </td>
      <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums">৳${parseFloat(ord.total).toFixed(2)}</td>
      <td class="py-3 px-4 text-center text-gray-500">${escapeHtml(ord.dispatched_at)}</td>
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
          alertBox.className = "p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-800 block";
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
          restockSubtext.innerText = "Items were already restocked before (leave unchecked to avoid counting twice)";
        } else {
          restockCheck.checked = true;
          restockSubtext.innerText = "Increments available product/SKU quantity";
        }

        document.getElementById('modalReturnReason').value = '';
        document.getElementById('returnDecisionModal').classList.remove('hidden');
      })
      .catch(err => {
        playSound('error');
        alertBox.className = "p-3 rounded-xl text-xs font-medium bg-rose-50 text-rose-800 block";
        alertBox.innerText = err.message || 'Error looking up parcel.';
        console.error(err);
      });
  }

  function closeReturnModal() {
    document.getElementById('returnDecisionModal').classList.add('hidden');
    if (canAutoFocus()) document.getElementById('returnScanInput').focus();
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
      btn.innerText = "Confirm return";

      if (data.success) {
        playSound('success');
        closeReturnModal();
        addReturnTableRow(data.order);

        const alertBox = document.getElementById('returnAlertBox');
        alertBox.className = "p-3 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-800 block";
        alertBox.innerText = data.message;
      } else {
        playSound('error');
        alert(data.message || 'Error updating return');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerText = "Confirm return";
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
      ? `<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">Buyer paid delivery</span>`
      : `<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700">Failed pickup (unpaid)</span>`;

    const lossDisplay = ord.courier_loss_amount > 0
      ? `-৳${parseFloat(ord.courier_loss_amount).toFixed(2)}`
      : `<span class="text-emerald-700">৳0.00 (no loss)</span>`;

    tr.innerHTML = `
      <td class="py-3 px-4 font-semibold text-gray-900 whitespace-nowrap">
        <a href="/admin/orders/${encodeURIComponent(ord.order_number)}" target="_blank" class="hover:underline">${escapeHtml(ord.order_number)}</a>
      </td>
      <td class="py-3 px-4">
        <span class="font-medium text-gray-900">${escapeHtml(ord.customer_name)}</span>
      </td>
      <td class="py-3 px-4">${typeBadge}</td>
      <td class="py-3 px-4 font-medium ${ord.return_restocked ? 'text-emerald-600' : 'text-gray-400'}">
        ${ord.return_restocked ? 'Restocked' : 'Not restocked'}
      </td>
      <td class="py-3 px-4 text-right font-semibold text-rose-600 tabular-nums">${lossDisplay}</td>
      <td class="py-3 px-4 text-center text-gray-500">${escapeHtml(ord.returned_at)}</td>
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
