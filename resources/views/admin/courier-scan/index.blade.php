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
          <select id="dispatchCourierSelect" class="w-full text-xs font-bold px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
            @foreach($courierOptions as $key => $name)
              <option value="{{ $key }}">{{ $name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Barcode Gun Input --}}
        <div class="md:col-span-8">
          <label class="block text-xs font-bold text-gray-700 mb-1">Scan Parcel Barcode / Invoice # (Gun Ready)</label>
          <div class="relative">
            <input type="text" id="dispatchScanInput" autofocus placeholder="Scan barcode with scanner gun or type order #..." class="w-full pl-10 pr-24 py-2.5 text-sm font-mono font-black bg-emerald-50/40 border-2 border-emerald-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 text-gray-900 placeholder-gray-400">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <button type="button" onclick="triggerDispatchScan()" class="absolute inset-y-1 right-1 px-4 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
              Enter
            </button>
          </div>
        </div>

      </div>

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
        <div class="relative">
          <input type="text" id="returnScanInput" placeholder="Scan barcode on returned package..." class="w-full pl-10 pr-24 py-2.5 text-sm font-mono font-black bg-amber-50/40 border-2 border-amber-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/30 text-gray-900 placeholder-gray-400">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-amber-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </div>
          <button type="button" onclick="triggerReturnLookup()" class="absolute inset-y-1 right-1 px-4 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors">
            Lookup
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

    {{-- Order Items Snapshot --}}
    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs text-gray-700 space-y-1">
      <div class="flex justify-between">
        <span class="text-gray-500">Items:</span>
        <span class="font-semibold" id="returnModalItemsSummary">Mustard Oil 1L</span>
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

    {{-- Restock Checkbox --}}
    <div class="pt-1">
      <label class="flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer">
        <input type="checkbox" id="modalRestockCheck" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
        <div>
          <span class="text-xs font-bold text-gray-800">Restock Product(s) into Inventory</span>
          <span class="text-[11px] text-gray-500 block">Increments available product/SKU quantity</span>
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

  // Initial
  document.addEventListener('DOMContentLoaded', function() {
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
  function triggerDispatchScan() {
    const input = document.getElementById('dispatchScanInput');
    const code = input.value.trim();
    if (!code) return;

    const courier = document.getElementById('dispatchCourierSelect').value;
    const alertBox = document.getElementById('dispatchAlertBox');

    fetch("{{ route('admin.courier-scan.dispatch') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ code: code, courier_name: courier })
    })
    .then(res => res.json())
    .then(data => {
      input.value = '';
      input.focus();

      if (data.success) {
        playSound('success');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 block";
        alertBox.innerText = data.message;

        addDispatchTableRow(data.order);
      } else {
        playSound('error');
        alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
        alertBox.innerText = data.message;
      }
    })
    .catch(err => {
      playSound('error');
      console.error(err);
    });
  }

  function addDispatchTableRow(ord) {
    const empty = document.getElementById('dispatchEmptyRow');
    if (empty) empty.remove();

    const tbody = document.getElementById('dispatchTableBody');
    const tr = document.createElement('tr');
    tr.className = "bg-emerald-50/40 hover:bg-emerald-50 transition-colors animate-pulse";

    tr.innerHTML = `
      <td class="py-2.5 px-4 font-mono font-bold text-brand-700">
        <a href="/admin/orders/${ord.order_number}" target="_blank" class="hover:underline">${ord.order_number}</a>
      </td>
      <td class="py-2.5 px-4">
        <span class="font-bold text-gray-800">${ord.customer_name}</span>
        <span class="text-[11px] text-gray-400 block">${ord.phone}</span>
      </td>
      <td class="py-2.5 px-4 text-gray-600">${ord.city || 'N/A'}</td>
      <td class="py-2.5 px-4">
        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
          ${ord.courier}
        </span>
      </td>
      <td class="py-2.5 px-4 text-right font-extrabold text-gray-900">৳${parseFloat(ord.total).toFixed(2)}</td>
      <td class="py-2.5 px-4 text-center text-gray-500">${ord.dispatched_at}</td>
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
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          playSound('error');
          alertBox.className = "p-3 rounded-xl text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 block";
          alertBox.innerText = data.message;
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

        document.getElementById('returnDecisionModal').classList.remove('hidden');
      })
      .catch(err => {
        playSound('error');
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
        restock: restock ? 1 : 0
      })
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerText = "Confirm Return & Save";

      if (data.success) {
        playSound('success');
        closeReturnModal();
        addReturnTableRow(data.order);
      } else {
        alert(data.message || 'Error updating return');
      }
    })
    .catch(err => {
      btn.disabled = false;
      btn.innerText = "Confirm Return & Save";
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
        <a href="/admin/orders/${ord.order_number}" target="_blank" class="hover:underline">${ord.order_number}</a>
      </td>
      <td class="py-2.5 px-4">
        <span class="font-bold text-gray-800">${ord.customer_name}</span>
      </td>
      <td class="py-2.5 px-4">${typeBadge}</td>
      <td class="py-2.5 px-4 font-bold ${ord.return_restocked ? 'text-emerald-600' : 'text-gray-400'}">
        ${ord.return_restocked ? '✓ Restocked' : 'Not restocked'}
      </td>
      <td class="py-2.5 px-4 text-right font-extrabold text-rose-600">${lossDisplay}</td>
      <td class="py-2.5 px-4 text-center text-gray-500">${ord.returned_at}</td>
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
