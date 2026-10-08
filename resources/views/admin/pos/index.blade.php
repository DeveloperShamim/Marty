@extends('layouts.admin')
@section('title', 'Point of sale')

@section('content')
<div class="h-[calc(100dvh-8.5rem)] lg:h-[calc(100vh-9.75rem)] min-h-[560px] flex flex-col bg-white rounded-[18px] shadow-panel overflow-hidden select-none">

  {{-- Header bar --}}
  <div class="px-3 sm:px-4 h-12 flex items-center justify-between gap-2 border-b border-gray-100 shrink-0">
    <div class="flex items-center gap-2 min-w-0">
      <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Till open"></span>
      <span class="text-[13px] font-semibold text-gray-900 truncate">{{ $storeName }}</span>
      <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-[11px] font-medium text-gray-600">Cashier: {{ auth()->user()->name ?? 'Admin' }}</span>
    </div>

    <div class="flex items-center gap-1.5 shrink-0">
      <span class="text-xs text-gray-400 hidden xl:inline mr-1"><kbd class="px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-600 font-sans text-[11px]">F2</kbd> scan <kbd class="ml-1.5 px-1.5 py-0.5 rounded-md bg-gray-100 text-gray-600 font-sans text-[11px]">F4</kbd> customer</span>
      <a href="{{ route('admin.barcodes.index') }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M8 8v8M12 8v8M16 8v8"/></svg>
        <span class="hidden sm:inline">Barcodes</span>
      </a>
      <a href="{{ route('admin.dashboard') }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center transition-colors">
        Exit
      </a>
    </div>
  </div>

  {{-- Phone/tablet view switcher (< 1024px) --}}
  <div class="lg:hidden px-3 py-2 border-b border-gray-100 shrink-0">
    <div class="flex items-center gap-1 p-1 rounded-full bg-gray-100">
      <button type="button" onclick="setMobileView('catalog')" id="mobileTabCatalog" class="flex-1 h-8 rounded-full text-[13px] font-medium inline-flex items-center justify-center gap-1.5 transition-colors bg-white text-gray-900 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Products
      </button>
      <button type="button" onclick="setMobileView('cart')" id="mobileTabCart" class="flex-1 h-8 rounded-full text-[13px] font-medium inline-flex items-center justify-center gap-1.5 transition-colors text-gray-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.6l2.4 12h11.2l2-8.5H6.2"/></svg>
        <span>Cart</span>
        <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums bg-emerald-50 text-emerald-700" id="mobileCartCount">0</span>
        <span class="text-gray-900 font-semibold tabular-nums" id="mobileCartTotal">৳0</span>
      </button>
    </div>
  </div>

  {{-- Work area: two columns on desktop, switched on phones --}}
  <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

    {{-- LEFT: catalog & scanner --}}
    <div id="catalogColumn" class="w-full lg:w-7/12 flex flex-col lg:border-r border-gray-100 bg-white overflow-hidden h-full">

      {{-- Scan + search --}}
      <div class="p-3 space-y-2.5 shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color: var(--brand-dark);" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M8 8v8M12 8v8M16 8v8"/></svg>
            <input type="text" id="barcodeScanInput" autofocus placeholder="Scan barcode (F2)" class="w-full h-10 pl-10 pr-11 rounded-full bg-white border border-gray-200 text-sm font-mono text-gray-900 placeholder-gray-400 placeholder:font-sans focus:border-gray-300 outline-none">
            <button type="button" onclick="openPosCamera()" class="absolute right-1 top-1/2 -translate-y-1/2 h-8 w-8 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 grid place-items-center" title="Scan with camera" aria-label="Scan with camera">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            </button>
          </div>

          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="catalogSearchInput" placeholder="Search name or SKU" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none">
          </div>
        </div>

        {{-- Categories --}}
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar -mx-3 px-3" id="categoryPills">
          <button type="button" onclick="selectCategory('')" class="category-pill active h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors shrink-0 text-white" style="background: var(--brand-dark);" data-category="">
            All
          </button>
          @foreach($categories as $cat)
            <button type="button" onclick="selectCategory('{{ $cat->id }}')" class="category-pill h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors shrink-0 bg-gray-100 text-gray-600 hover:bg-gray-200" data-category="{{ $cat->id }}">
              {{ $cat->name }}
            </button>
          @endforeach
        </div>
      </div>

      {{-- Product grid --}}
      <div class="flex-1 overflow-y-auto px-3 pb-20 lg:pb-3">
        <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-2.5">
          {{-- Populated via JS --}}
        </div>
        <div id="catalogLoading" class="py-16 text-center text-gray-400 hidden">
          <div class="inline-block animate-spin w-6 h-6 border-2 border-gray-300 border-t-transparent rounded-full mb-2"></div>
          <div class="text-xs">Loading products...</div>
        </div>
        <div id="catalogEmpty" class="py-16 text-center hidden">
          <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <p class="text-[13px] font-medium text-gray-700">No products found</p>
          <p class="text-xs text-gray-500">Try another search or category</p>
        </div>
      </div>

      {{-- Phone: floating cart bar --}}
      <div id="mobileFloatingCartBar" class="lg:hidden hidden fixed bottom-3 inset-x-3 z-40 text-white pl-4 pr-1.5 py-1.5 rounded-full shadow-xl items-center justify-between gap-3" style="background: var(--brand-dark);">
        <div class="min-w-0 text-[13px]">
          <span class="text-white/70"><span id="floatCartItemCount">0</span> items</span>
          <span class="font-semibold tabular-nums ml-1.5" id="floatCartTotal">৳0.00</span>
        </div>
        <button type="button" onclick="setMobileView('cart')" class="h-9 px-4 rounded-full bg-white text-[13px] font-semibold shrink-0" style="color: var(--brand-dark);">
          View cart
        </button>
      </div>

    </div>

    {{-- RIGHT: cart & payment --}}
    <div id="cartColumn" class="w-full lg:w-5/12 hidden lg:flex flex-col bg-white overflow-hidden h-full">

      {{-- Customer --}}
      <div class="p-3 border-b border-gray-100 shrink-0">
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-1.5">
            <button type="button" onclick="setMobileView('catalog')" class="lg:hidden h-7 w-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 grid place-items-center" title="Back to products" aria-label="Back to products">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <span class="text-[13px] font-semibold text-gray-900">Customer</span>
          </div>
          <button type="button" onclick="clearCustomer()" class="text-xs text-gray-500 hover:text-gray-900">Reset</button>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <input type="text" id="customerPhone" placeholder="Phone (F4)" class="w-full h-9 px-3.5 rounded-full bg-gray-100 border border-transparent text-[13px] text-gray-900 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none" onkeyup="debounceCustomerLookup(this.value)">
          <input type="text" id="customerName" placeholder="Walk-in customer" class="w-full h-9 px-3.5 rounded-full bg-gray-100 border border-transparent text-[13px] text-gray-900 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none">
        </div>
      </div>

      {{-- Cart items --}}
      <div class="flex-1 overflow-y-auto divide-y divide-gray-100" id="cartItemsList">
        {{-- Populated via JS --}}
      </div>

      <div id="cartEmptyState" class="py-10 sm:py-14 text-center">
        <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.6l2.4 12h11.2l2-8.5H6.2"/></svg>
        <p class="text-[13px] font-medium text-gray-700">Cart is empty</p>
        <p class="text-xs text-gray-500">Scan a barcode or pick a product</p>
        <button type="button" onclick="setMobileView('catalog')" class="lg:hidden mt-3 h-8 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium">
          Browse products
        </button>
      </div>

      {{-- Totals & payment --}}
      <div class="p-3 sm:p-4 bg-gray-50/80 border-t border-gray-100 space-y-3 shrink-0">

        <div class="space-y-2 text-[13px] text-gray-600">
          <div class="flex justify-between">
            <span>Subtotal (<span id="cartTotalItems">0</span> items)</span>
            <span class="font-medium text-gray-900 tabular-nums" id="cartSubtotalDisplay">৳0.00</span>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <label class="flex items-center gap-2 h-9 bg-white rounded-full px-3.5 ring-1 ring-gray-200/70">
              <span class="text-xs text-gray-500">Discount</span>
              <input type="number" id="cartDiscountInput" value="0" min="0" step="1" oninput="renderCartSummary()" class="w-full min-w-0 text-right text-[13px] font-medium text-rose-600 bg-transparent border-0 p-0 focus:outline-none focus:shadow-none">
            </label>
            <label class="flex items-center gap-2 h-9 bg-white rounded-full px-3.5 ring-1 ring-gray-200/70">
              <span class="text-xs text-gray-500">Delivery</span>
              <input type="number" id="cartShippingInput" value="0" min="0" step="1" oninput="renderCartSummary()" class="w-full min-w-0 text-right text-[13px] font-medium text-gray-900 bg-transparent border-0 p-0 focus:outline-none focus:shadow-none">
            </label>
          </div>

          <div class="flex justify-between items-baseline pt-2 border-t border-gray-200/70">
            <span class="text-[13px] font-semibold text-gray-900">Total</span>
            <span class="text-xl font-semibold text-gray-900 tabular-nums" id="cartGrandTotalDisplay">৳0.00</span>
          </div>
        </div>

        {{-- Payment method --}}
        <div>
          <p class="text-xs text-gray-500 mb-1.5">Payment</p>
          <div class="grid grid-cols-4 gap-1 p-1 rounded-full bg-white ring-1 ring-gray-200/70" id="paymentMethodGroup">
            <button type="button" onclick="setPaymentMethod('cash')" class="pay-btn active h-8 rounded-full text-[13px] font-medium text-center transition-colors text-white" style="background: var(--brand-dark);" data-method="cash">Cash</button>
            <button type="button" onclick="setPaymentMethod('bkash')" class="pay-btn h-8 rounded-full text-[13px] font-medium text-center transition-colors text-gray-600 hover:bg-gray-100" data-method="bkash">bKash</button>
            <button type="button" onclick="setPaymentMethod('nagad')" class="pay-btn h-8 rounded-full text-[13px] font-medium text-center transition-colors text-gray-600 hover:bg-gray-100" data-method="nagad">Nagad</button>
            <button type="button" onclick="setPaymentMethod('card')" class="pay-btn h-8 rounded-full text-[13px] font-medium text-center transition-colors text-gray-600 hover:bg-gray-100" data-method="card">Card</button>
          </div>
        </div>

        {{-- Cash received & change (cash only) --}}
        <div id="cashCalculatorRow" class="grid grid-cols-2 gap-2 items-center">
          <label class="flex items-center gap-2 h-9 bg-white rounded-full px-3.5 ring-1 ring-gray-200/70">
            <span class="text-xs text-gray-500 shrink-0">Received</span>
            <input type="number" id="cashTenderedInput" placeholder="0" oninput="calculateChange()" class="w-full min-w-0 text-right text-[13px] font-medium text-gray-900 bg-transparent border-0 p-0 focus:outline-none focus:shadow-none">
          </label>
          <div class="flex items-center justify-between h-9 rounded-full px-3.5 bg-emerald-50">
            <span class="text-xs text-emerald-700">Change</span>
            <span id="changeReturnDisplay" class="text-[13px] font-semibold text-emerald-700 tabular-nums">৳0.00</span>
          </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
          <button type="button" onclick="resetCart()" class="h-10 px-4 rounded-full bg-gray-200/70 hover:bg-gray-200 text-gray-800 text-[13px] font-medium transition-colors shrink-0">
            Clear
          </button>
          <button type="button" id="submitOrderBtn" onclick="submitPosOrder()" class="flex-1 h-10 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 transition-opacity disabled:opacity-50" style="background: var(--brand-dark);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            Complete sale
          </button>
        </div>

      </div>

    </div>

  </div>

</div>

{{-- MODAL 1: Variant picker --}}
<div id="variantModal" class="fixed inset-0 z-50 bg-gray-900/50 flex items-end sm:items-center justify-center p-3 sm:p-4 hidden">
  <div class="bg-white rounded-[18px] max-w-md w-full p-4 sm:p-5 shadow-2xl space-y-3">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <h3 class="text-[15px] font-semibold text-gray-900 truncate" id="variantModalTitle">Select Variant</h3>
        <p class="text-xs text-gray-500 mt-0.5">Choose the option to add</p>
      </div>
      <button type="button" onclick="closeVariantModal()" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 grid place-items-center shrink-0" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <div id="variantModalList" class="space-y-1.5 max-h-72 overflow-y-auto">
      {{-- Populated dynamically --}}
    </div>
  </div>
</div>

{{-- MODAL 2: Receipt --}}
<div id="receiptModal" class="fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-[18px] max-w-sm w-full p-5 shadow-2xl text-center space-y-4">
    <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-full grid place-items-center mx-auto">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
    </div>

    <div>
      <h3 class="text-[15px] font-semibold text-gray-900">Sale completed</h3>
      <p class="text-xs text-gray-500 mt-0.5">Order <span id="completedOrderNumber" class="font-medium text-gray-800"></span> recorded</p>
    </div>

    <div class="bg-gray-50 p-3 rounded-2xl text-[13px] space-y-1">
      <div class="flex justify-between">
        <span class="text-gray-500">Total paid</span>
        <strong id="completedOrderTotal" class="font-semibold text-gray-900 tabular-nums"></strong>
      </div>
      <div class="flex justify-between" id="completedChangeRow">
        <span class="text-gray-500">Change</span>
        <strong id="completedOrderChange" class="font-semibold text-emerald-700 tabular-nums"></strong>
      </div>
    </div>

    <div class="space-y-2">
      <button type="button" id="printThermalBtn" onclick="printReceiptPopup()" class="w-full h-10 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5" style="background: var(--brand-dark);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
        Print receipt
      </button>
      <button type="button" onclick="closeReceiptModal()" class="w-full h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium transition-colors">
        New sale (Esc)
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script src="{{ asset('theme/js/camera-scanner.js') }}?v={{ @filemtime(public_path('theme/js/camera-scanner.js')) ?: '1' }}"></script>
<script>
  // POS State
  let cart = [];
  let currentCategory = '';
  let selectedPaymentMethod = 'cash';
  let activeReceiptUrl = null;
  let audioCtx = null;
  let activeMobileView = 'catalog'; // 'catalog' or 'cart'

  // Mobile View Switcher Function
  const TAB_BASE = "flex-1 h-8 rounded-full text-[13px] font-medium inline-flex items-center justify-center gap-1.5 transition-colors ";
  const TAB_ON = TAB_BASE + "bg-white text-gray-900 shadow-sm";
  const TAB_OFF = TAB_BASE + "text-gray-600";
  function setMobileView(view) {
    activeMobileView = view;
    const catCol = document.getElementById('catalogColumn');
    const cartCol = document.getElementById('cartColumn');
    const tabCat = document.getElementById('mobileTabCatalog');
    const tabCart = document.getElementById('mobileTabCart');

    if (view === 'cart') {
      catCol.classList.add('hidden');
      catCol.classList.remove('flex');
      cartCol.classList.remove('hidden');
      cartCol.classList.add('flex');

      tabCart.className = TAB_ON;
      tabCat.className = TAB_OFF;
    } else {
      cartCol.classList.add('hidden');
      cartCol.classList.remove('flex');
      catCol.classList.remove('hidden');
      catCol.classList.add('flex');

      tabCat.className = TAB_ON;
      tabCart.className = TAB_OFF;
    }
    renderCartSummary();
  }

  // Audio Synthesizer Beeps
  function playBeep(type = 'success') {
    try {
      if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      }
      if (audioCtx.state === 'suspended') {
        audioCtx.resume();
      }
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.connect(gain);
      gain.connect(audioCtx.destination);

      if (type === 'success') {
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
      } else {
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(220, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.3);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.3);
      }
    } catch(e){}
  }

  // DOM Loaded
  document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    const barcodeInput = document.getElementById('barcodeScanInput');
    
    // Only autofocus barcode on non-mobile devices to avoid unwanted keyboard popup on small phones
    if (window.innerWidth >= 1024) {
      barcodeInput.focus();
    }

    // Barcode Gun Listener
    barcodeInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const code = this.value.trim();
        if (code) {
          handleBarcodeScan(code);
          this.value = '';
        }
      }
    });

    // Catalog Keyword Search
    let searchDebounce = null;
    document.getElementById('catalogSearchInput').addEventListener('input', function() {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => {
        loadProducts(this.value.trim());
      }, 250);
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
      if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('barcodeScanInput').focus();
      } else if (e.key === 'F4') {
        e.preventDefault();
        setMobileView('cart');
        document.getElementById('customerPhone').focus();
      } else if (e.key === 'Escape') {
        if (!document.getElementById('receiptModal').classList.contains('hidden')) {
          closeReceiptModal();
        } else if (!document.getElementById('variantModal').classList.contains('hidden')) {
          closeVariantModal();
        }
      }
    });
  });

  // Fetch Products via Ajax
  function loadProducts(keyword = '') {
    const grid = document.getElementById('productGrid');
    const loading = document.getElementById('catalogLoading');
    const empty = document.getElementById('catalogEmpty');

    grid.innerHTML = '';
    loading.classList.remove('hidden');
    empty.classList.add('hidden');

    const params = new URLSearchParams({
      q: keyword,
      category_id: currentCategory
    });

    fetch(`{{ route('admin.pos.search') }}?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        loading.classList.add('hidden');
        if (!data.products || data.products.length === 0) {
          empty.classList.remove('hidden');
          return;
        }

        data.products.forEach(p => {
          const card = document.createElement('div');
          card.className = "bg-white p-2 rounded-2xl ring-1 ring-gray-100 hover:ring-gray-300 transition cursor-pointer flex flex-col justify-between group";
          card.onclick = () => onProductCardClick(p);

          card.innerHTML = `
            <div>
              <div class="aspect-square w-full rounded-xl bg-gray-100 overflow-hidden mb-2 relative">
                <img src="${p.image}" alt="${p.name.replace(/"/g, '&quot;')}" loading="lazy" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'300\\' height=\\'300\\' viewBox=\\'0 0 300 300\\'%3E%3Crect width=\\'300\\' height=\\'300\\' fill=\\'%23f1f5f9\\'/%3E%3Ctext x=\\'150\\' y=\\'155\\' text-anchor=\\'middle\\' fill=\\'%2394a3b8\\' font-family=\\'system-ui,sans-serif\\' font-size=\\'14\\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                ${p.has_skus ? '<span class="absolute top-1.5 right-1.5 bg-white/90 text-gray-700 text-[10px] font-medium px-1.5 py-0.5 rounded-full">Options</span>' : ''}
              </div>
              <div class="text-gray-800 text-xs font-medium line-clamp-2 leading-snug">${p.name}</div>
            </div>
            <div class="mt-1.5 flex items-end justify-between gap-1">
              <div class="min-w-0">
                <div class="font-semibold text-gray-900 text-[13px] tabular-nums">৳${p.price.toFixed(0)}</div>
                <div class="text-[11px] ${p.stock > 0 ? 'text-gray-400' : 'text-rose-600'}">${p.stock} in stock</div>
              </div>
              <span class="w-7 h-7 shrink-0 rounded-full bg-gray-100 text-gray-700 grid place-items-center group-hover:bg-gray-900 group-hover:text-white transition-colors" aria-hidden="true">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </span>
            </div>
          `;
          grid.appendChild(card);
        });
      })
      .catch(err => {
        loading.classList.add('hidden');
        console.error(err);
      });
  }

  function selectCategory(id) {
    currentCategory = id;
    document.querySelectorAll('.category-pill').forEach(btn => {
      if (btn.getAttribute('data-category') === id) {
        btn.className = "category-pill active h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors shrink-0 text-white";
        btn.style.background = 'var(--brand-dark)';
      } else {
        btn.className = "category-pill h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors shrink-0 bg-gray-100 text-gray-600 hover:bg-gray-200";
        btn.style.background = '';
      }
    });
    loadProducts(document.getElementById('catalogSearchInput').value.trim());
  }

  // Handle Barcode Scan from Gun
  // Returns { ok, message, close } so the camera scanner can show the result.
  function handleBarcodeScan(code, fromCamera = false) {
    return fetch(`{{ route('admin.pos.scan') }}?code=${encodeURIComponent(code)}`)
      .then(res => res.json())
      .then(data => {
        if (!data.found) {
          playBeep('error');
          const message = `No product found for barcode: "${code}"`;
          if (!fromCamera) alert(message);
          return { ok: false, message };
        }
        playBeep('success');

        if (data.needs_sku) {
          showVariantModal(data.product);
          // Close the camera so the size/colour picker is visible.
          return { ok: true, close: true, message: `${data.product.name}: choose an option` };
        }
        addToCart(data.item);
        return { ok: true, message: `Added: ${data.item.name}` };
      })
      .catch(err => {
        playBeep('error');
        console.error(err);
        return { ok: false, message: 'Could not reach the server. Try again.' };
      });
  }

  function openPosCamera() {
    CameraScanner.open({
      title: 'Scan products',
      continuous: true,
      onScan: code => handleBarcodeScan(code, true),
    });
  }

  // Product Click Handler
  function onProductCardClick(product) {
    if (product.has_skus && product.skus && product.skus.length > 0) {
      showVariantModal(product);
    } else {
      addToCart({
        product_id: product.id,
        product_sku_id: null,
        name: product.name,
        variant: null,
        price: product.price,
        cost_price: product.cost_price,
        stock: product.stock,
        image: product.image
      });
      playBeep('success');
    }
  }

  // Variant Modal
  function showVariantModal(product) {
    document.getElementById('variantModalTitle').innerText = product.name;
    const list = document.getElementById('variantModalList');
    list.innerHTML = '';

    product.skus.forEach(s => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = "w-full px-3.5 py-2.5 rounded-2xl bg-gray-50 hover:bg-gray-100 flex items-center justify-between gap-3 text-left transition-colors";
      btn.onclick = () => {
        addToCart({
          product_id: product.id,
          product_sku_id: s.id,
          name: `${product.name} (${s.name})`,
          variant: s.name,
          price: s.price,
          cost_price: s.cost_price,
          stock: s.stock,
          image: product.image || ''
        });
        playBeep('success');
        closeVariantModal();
      };

      btn.innerHTML = `
        <div>
          <div class="font-medium text-[13px] text-gray-900">${s.name}</div>
          <div class="text-[11px] text-gray-500">${s.stock} in stock</div>
        </div>
        <div class="font-semibold text-gray-900 text-[13px] tabular-nums">৳${s.price.toFixed(0)}</div>
      `;
      list.appendChild(btn);
    });

    document.getElementById('variantModal').classList.remove('hidden');
  }

  function closeVariantModal() {
    document.getElementById('variantModal').classList.add('hidden');
    if (window.innerWidth >= 1024) {
      document.getElementById('barcodeScanInput').focus();
    }
  }

  // Cart Management
  function addToCart(item) {
    const key = `${item.product_id}_${item.product_sku_id || 'base'}`;
    const existing = cart.find(c => c.key === key);

    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        key: key,
        product_id: item.product_id,
        product_sku_id: item.product_sku_id,
        name: item.name,
        variant: item.variant,
        price: parseFloat(item.price),
        cost_price: parseFloat(item.cost_price || 0),
        image: item.image || '',
        quantity: 1
      });
    }

    renderCart();
  }

  function updateItemQty(key, delta) {
    const item = cart.find(c => c.key === key);
    if (!item) return;

    item.quantity += delta;
    if (item.quantity <= 0) {
      cart = cart.filter(c => c.key !== key);
    }
    renderCart();
  }

  function removeItem(key) {
    cart = cart.filter(c => c.key !== key);
    renderCart();
  }

  function resetCart() {
    cart = [];
    document.getElementById('cartDiscountInput').value = 0;
    document.getElementById('cartShippingInput').value = 0;
    document.getElementById('cashTenderedInput').value = '';
    renderCart();
  }

  function renderCart() {
    const list = document.getElementById('cartItemsList');
    const empty = document.getElementById('cartEmptyState');
    const submitBtn = document.getElementById('submitOrderBtn');

    list.innerHTML = '';

    if (cart.length === 0) {
      empty.classList.remove('hidden');
      submitBtn.disabled = true;
      renderCartSummary();
      return;
    }

    empty.classList.add('hidden');
    submitBtn.disabled = false;

    cart.forEach(item => {
      const row = document.createElement('div');
      row.className = "px-3 py-2.5 flex items-center justify-between gap-2.5";

      const lineTotal = item.price * item.quantity;

      row.innerHTML = `
        <div class="flex items-center gap-2 flex-1 min-w-0">
          ${item.image ? `<img src="${item.image}" alt="" class="w-9 h-9 rounded-xl object-cover shrink-0 bg-gray-100">` : ''}
          <div class="min-w-0">
            <div class="text-[13px] font-medium text-gray-900 truncate">${item.name}</div>
            <div class="text-[11px] text-gray-500">৳${item.price.toFixed(0)} each</div>
          </div>
        </div>

        <div class="flex items-center gap-0.5 p-0.5 rounded-full bg-gray-100 shrink-0">
          <button type="button" onclick="updateItemQty('${item.key}', -1)" class="w-7 h-7 rounded-full hover:bg-white text-gray-700 text-sm flex items-center justify-center" aria-label="Decrease">&minus;</button>
          <span class="w-6 text-center font-medium text-[13px] text-gray-900 tabular-nums">${item.quantity}</span>
          <button type="button" onclick="updateItemQty('${item.key}', 1)" class="w-7 h-7 rounded-full hover:bg-white text-gray-700 text-sm flex items-center justify-center" aria-label="Increase">+</button>
        </div>

        <div class="text-right w-16 sm:w-20 shrink-0">
          <div class="font-semibold text-[13px] text-gray-900 tabular-nums">৳${lineTotal.toFixed(0)}</div>
          <button type="button" onclick="removeItem('${item.key}')" class="text-[11px] text-rose-600 hover:underline">Remove</button>
        </div>
      `;

      list.appendChild(row);
    });

    renderCartSummary();
  }

  function renderCartSummary() {
    let subtotal = 0;
    let totalItems = 0;

    cart.forEach(it => {
      subtotal += (it.price * it.quantity);
      totalItems += it.quantity;
    });

    const discount = parseFloat(document.getElementById('cartDiscountInput').value) || 0;
    const shipping = parseFloat(document.getElementById('cartShippingInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount + shipping);

    document.getElementById('cartTotalItems').innerText = totalItems;
    document.getElementById('cartSubtotalDisplay').innerText = `৳${subtotal.toFixed(2)}`;
    document.getElementById('cartGrandTotalDisplay').innerText = `৳${grandTotal.toFixed(2)}`;

    // Update Mobile Header Badge & Floating Bar
    document.getElementById('mobileCartCount').innerText = totalItems;
    document.getElementById('mobileCartTotal').innerText = `৳${grandTotal.toFixed(0)}`;

    const floatBar = document.getElementById('mobileFloatingCartBar');
    if (totalItems > 0 && activeMobileView === 'catalog') {
      floatBar.classList.remove('hidden');
      floatBar.classList.add('flex');
      document.getElementById('floatCartItemCount').innerText = totalItems;
      document.getElementById('floatCartTotal').innerText = `৳${grandTotal.toFixed(2)}`;
    } else {
      floatBar.classList.add('hidden');
      floatBar.classList.remove('flex');
    }

    calculateChange();
  }

  function setPaymentMethod(method) {
    selectedPaymentMethod = method;
    document.querySelectorAll('.pay-btn').forEach(btn => {
      if (btn.getAttribute('data-method') === method) {
        btn.className = "pay-btn active h-8 rounded-full text-[13px] font-medium text-center transition-colors text-white";
        btn.style.background = 'var(--brand-dark)';
      } else {
        btn.className = "pay-btn h-8 rounded-full text-[13px] font-medium text-center transition-colors text-gray-600 hover:bg-gray-100";
        btn.style.background = '';
      }
    });

    const cashRow = document.getElementById('cashCalculatorRow');
    if (method === 'cash') {
      cashRow.classList.remove('hidden');
    } else {
      cashRow.classList.add('hidden');
    }
  }

  function calculateChange() {
    let subtotal = cart.reduce((acc, it) => acc + (it.price * it.quantity), 0);
    const discount = parseFloat(document.getElementById('cartDiscountInput').value) || 0;
    const shipping = parseFloat(document.getElementById('cartShippingInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount + shipping);

    const tenderedInput = document.getElementById('cashTenderedInput');
    const tendered = parseFloat(tenderedInput.value) || 0;
    const change = Math.max(0, tendered - grandTotal);

    document.getElementById('changeReturnDisplay').innerText = `৳${change.toFixed(2)}`;
  }

  // Customer Lookup by Phone
  let custTimeout = null;
  function debounceCustomerLookup(phone) {
    clearTimeout(custTimeout);
    if (phone.length < 4) return;

    custTimeout = setTimeout(() => {
      fetch(`{{ route('admin.pos.customer') }}?phone=${encodeURIComponent(phone)}`)
        .then(res => res.json())
        .then(data => {
          if (data.found && data.customer) {
            document.getElementById('customerName').value = data.customer.name;
          }
        });
    }, 300);
  }

  function clearCustomer() {
    document.getElementById('customerPhone').value = '';
    document.getElementById('customerName').value = '';
  }

  // Submit POS Order
  const COMPLETE_SALE_HTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Complete sale`;
  function submitPosOrder() {
    if (cart.length === 0) {
      alert('Cart is empty.');
      return;
    }

    let subtotal = cart.reduce((acc, it) => acc + (it.price * it.quantity), 0);
    const discount = parseFloat(document.getElementById('cartDiscountInput').value) || 0;
    const shipping = parseFloat(document.getElementById('cartShippingInput').value) || 0;
    const grandTotal = Math.max(0, subtotal - discount + shipping);

    const cashTenderedVal = parseFloat(document.getElementById('cashTenderedInput').value);
    const cashTendered = selectedPaymentMethod === 'cash' ? (cashTenderedVal || grandTotal) : grandTotal;

    const payload = {
      _token: '{{ csrf_token() }}',
      items: cart.map(it => ({
        product_id: it.product_id,
        product_sku_id: it.product_sku_id,
        quantity: it.quantity,
        price: it.price
      })),
      customer_name: document.getElementById('customerName').value.trim() || 'Walk-in Customer',
      customer_phone: document.getElementById('customerPhone').value.trim() || '',
      discount: discount,
      shipping_charge: shipping,
      payment_method: selectedPaymentMethod,
      cash_tendered: cashTendered,
      note: 'POS Terminal Sale'
    };

    const submitBtn = document.getElementById('submitOrderBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span>Processing...</span>`;

    fetch("{{ route('admin.pos.order') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = COMPLETE_SALE_HTML;

      if (data.success) {
        playBeep('success');
        activeReceiptUrl = data.receipt_url;

        document.getElementById('completedOrderNumber').innerText = data.order_number;
        document.getElementById('completedOrderTotal').innerText = `৳${parseFloat(data.total).toFixed(2)}`;

        if (selectedPaymentMethod === 'cash') {
          document.getElementById('completedChangeRow').style.display = 'flex';
          document.getElementById('completedOrderChange').innerText = `৳${parseFloat(data.change || 0).toFixed(2)}`;
        } else {
          document.getElementById('completedChangeRow').style.display = 'none';
        }

        document.getElementById('receiptModal').classList.remove('hidden');

        // Automatically trigger print popup window
        printReceiptPopup();

        // Clear cart for next customer
        resetCart();
        clearCustomer();
        loadProducts(); // refresh catalog stock counts
        
        // Reset mobile view back to catalog
        if (window.innerWidth < 1024) {
          setMobileView('catalog');
        }
      } else {
        alert(data.message || 'Error completing sale.');
      }
    })
    .catch(err => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = COMPLETE_SALE_HTML;
      alert('Network or server error while placing order.');
      console.error(err);
    });
  }

  function printReceiptPopup() {
    if (activeReceiptUrl) {
      window.open(activeReceiptUrl, 'ReceiptPrint', 'width=420,height=600,scrollbars=yes');
    }
  }

  function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
    if (window.innerWidth >= 1024) {
      document.getElementById('barcodeScanInput').focus();
    }
  }
</script>
@endpush
@endsection
