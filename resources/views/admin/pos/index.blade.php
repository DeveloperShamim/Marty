@extends('layouts.admin')
@section('title', 'POS Cash Register')

@section('content')
<div class="h-[calc(100dvh-10.5rem)] lg:h-[calc(100vh-11.5rem)] min-h-[560px] flex flex-col bg-slate-100 rounded-[24px] shadow-panel overflow-hidden select-none">

  {{-- Top Navigation & Mobile View Switcher --}}
  <div class="bg-slate-900 text-white px-3 sm:px-4 py-2 sm:py-2.5 flex items-center justify-between shadow-md shrink-0">
    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
      <div class="flex items-center gap-1.5 sm:gap-2 font-bold text-xs sm:text-sm truncate">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
        <span class="tracking-wide text-white truncate">{{ $storeName }} &middot; POS</span>
      </div>
      <span class="text-[11px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded border border-slate-700 font-mono hidden md:inline-block">Cashier: {{ auth()->user()->name ?? 'Admin' }}</span>
    </div>

    {{-- Actions & Hotkey hints --}}
    <div class="flex items-center gap-2 text-xs shrink-0">
      <span class="text-slate-400 hidden xl:inline">Shortcuts: <kbd class="bg-slate-800 px-1 py-0.5 rounded text-amber-400 font-mono">F2</kbd> Scan | <kbd class="bg-slate-800 px-1 py-0.5 rounded text-amber-400 font-mono">F4</kbd> Customer</span>
      <a href="{{ route('admin.barcodes.index') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-[11px] sm:text-xs font-semibold flex items-center gap-1 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
        <span class="hidden sm:inline">Barcodes</span>
      </a>
      <a href="{{ route('admin.dashboard') }}" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-[11px] sm:text-xs font-semibold transition-colors">
        Exit
      </a>
    </div>
  </div>

  {{-- Mobile Screen Tab Switcher (< 1024px) --}}
  <div class="lg:hidden flex items-center bg-slate-900 border-t border-slate-800 px-3 py-1.5 gap-2 shrink-0">
    <button type="button" onclick="setMobileView('catalog')" id="mobileTabCatalog" class="flex-1 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white flex items-center justify-center gap-1.5 transition-all shadow-xs">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      Catalog / Items
    </button>
    <button type="button" onclick="setMobileView('cart')" id="mobileTabCart" class="flex-1 py-2 text-xs font-extrabold rounded-xl bg-slate-800 text-slate-300 hover:text-white flex items-center justify-center gap-1.5 transition-all border border-slate-700">
      <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      <span>Cart</span>
      <span class="px-1.5 py-0.2 bg-emerald-500 text-white rounded-full text-[10px] font-black" id="mobileCartCount">0</span>
      <span class="text-emerald-400 font-black ml-0.5" id="mobileCartTotal">৳0</span>
    </button>
  </div>

  {{-- Main Work Area: Dual Column on Desktop, Tabbed on Mobile --}}
  <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

    {{-- LEFT COLUMN: Catalog & Barcode Scanner --}}
    <div id="catalogColumn" class="w-full lg:w-7/12 flex flex-col border-r border-slate-200 bg-white overflow-hidden h-full">
      
      {{-- Barcode Scan Bar + Search Input --}}
      <div class="p-2.5 sm:p-3.5 bg-slate-50 border-b border-slate-200 space-y-2 shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
          
          {{-- Barcode Gun Input (Always primary) --}}
          <div class="sm:col-span-6 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-brand-600">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <input type="text" id="barcodeScanInput" autofocus placeholder="Scan Barcode / Press F2 (Gun Ready)" class="w-full pl-9 sm:pl-10 pr-12 py-2 sm:py-2.5 text-xs sm:text-sm font-mono font-bold bg-white border-2 border-brand-500 rounded-xl shadow-xs focus:outline-none focus:ring-2 focus:ring-brand-500/30 text-slate-900 placeholder-slate-400">
            <button type="button" onclick="openPosCamera()" class="absolute inset-y-1 right-1 w-10 rounded-lg text-brand-700 hover:bg-brand-50 flex items-center justify-center" title="Scan with camera" aria-label="Scan with camera">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            </button>
          </div>

          {{-- Name/SKU Keyword Search --}}
          <div class="sm:col-span-6 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" id="catalogSearchInput" placeholder="Search product name or SKU..." class="w-full pl-9 pr-3 py-2 sm:py-2.5 text-xs sm:text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:border-brand-500 text-slate-800">
          </div>

        </div>

        {{-- Categories Horizontal Scroll --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none" id="categoryPills">
          <button type="button" onclick="selectCategory('')" class="category-pill active px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 bg-brand-600 text-white shadow-xs" data-category="">
            All Items
          </button>
          @foreach($categories as $cat)
            <button type="button" onclick="selectCategory('{{ $cat->id }}')" class="category-pill px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200" data-category="{{ $cat->id }}">
              {{ $cat->name }}
            </button>
          @endforeach
        </div>
      </div>

      {{-- Product Cards Grid --}}
      <div class="flex-1 overflow-y-auto p-2.5 sm:p-3.5 bg-slate-50/50 pb-20 lg:pb-4">
        <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 sm:gap-3">
          {{-- Populated via JS --}}
        </div>
        <div id="catalogLoading" class="py-16 text-center text-slate-400 hidden">
          <div class="inline-block animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mb-2"></div>
          <div class="text-xs font-semibold">Loading catalog...</div>
        </div>
        <div id="catalogEmpty" class="py-16 text-center text-slate-400 hidden">
          <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
          <p class="text-sm font-bold text-slate-500">No products found</p>
          <p class="text-xs text-slate-400">Try adjusting your search or category filter</p>
        </div>
      </div>

      {{-- Sticky Mobile Floating Cart Bar (Appears when items are in cart on small screens) --}}
      <div id="mobileFloatingCartBar" class="lg:hidden hidden fixed bottom-2 inset-x-2 z-40 bg-slate-900 text-white p-3 rounded-2xl shadow-2xl border border-slate-700 items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div>
            <div class="text-xs font-bold text-slate-300"><span id="floatCartItemCount">0</span> Items in Cart</div>
            <div class="text-base font-black text-emerald-400" id="floatCartTotal">৳0.00</div>
          </div>
        </div>
        <button type="button" onclick="setMobileView('cart')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-sm transition-all flex items-center gap-1.5">
          Checkout &rarr;
        </button>
      </div>

    </div>

    {{-- RIGHT COLUMN: Active Cart & Cash Register --}}
    <div id="cartColumn" class="w-full lg:w-5/12 hidden lg:flex flex-col bg-white overflow-hidden shadow-lg z-10 h-full">

      {{-- Customer Quick Bar & Mobile Back to Catalog --}}
      <div class="p-3 bg-slate-900 text-white border-b border-slate-800 shrink-0">
        <div class="flex items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <button type="button" onclick="setMobileView('catalog')" class="lg:hidden p-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 mr-1" title="Back to items">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              Customer Info
            </span>
          </div>
          <button type="button" onclick="clearCustomer()" class="text-[11px] text-slate-400 hover:text-white transition-colors">Reset</button>
        </div>

        <div class="grid grid-cols-12 gap-2">
          <div class="col-span-6 relative">
            <input type="text" id="customerPhone" placeholder="Phone (F4)" class="w-full px-2.5 py-1.5 text-xs bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-brand-400" onkeyup="debounceCustomerLookup(this.value)">
          </div>
          <div class="col-span-6">
            <input type="text" id="customerName" placeholder="Walk-in Customer" class="w-full px-2.5 py-1.5 text-xs bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-brand-400">
          </div>
        </div>
      </div>

      {{-- Cart Items Table --}}
      <div class="flex-1 overflow-y-auto divide-y divide-slate-100" id="cartItemsList">
        {{-- Populated via JS --}}
      </div>

      <div id="cartEmptyState" class="py-12 sm:py-16 text-center text-slate-400">
        <svg class="w-12 h-12 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p class="text-sm font-bold text-slate-500">Cart is empty</p>
        <p class="text-xs text-slate-400">Scan barcode or pick products from catalog</p>
        <button type="button" onclick="setMobileView('catalog')" class="lg:hidden mt-3 px-3 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-bold">
          Open Catalog
        </button>
      </div>

      {{-- Summary, Discount & Payment Checkout Area --}}
      <div class="p-3 sm:p-4 bg-slate-50 border-t border-slate-200 space-y-2.5 sm:space-y-3 shrink-0">
        
        {{-- Calculations Row --}}
        <div class="space-y-1 text-xs text-slate-600">
          <div class="flex justify-between">
            <span>Subtotal (<span id="cartTotalItems">0</span> items):</span>
            <span class="font-bold text-slate-900" id="cartSubtotalDisplay">৳0.00</span>
          </div>

          {{-- Discount & Shipping Inputs --}}
          <div class="grid grid-cols-2 gap-2 pt-0.5">
            <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-lg px-2 py-1">
              <span class="text-[10px] sm:text-[11px] text-slate-400">Discount:</span>
              <input type="number" id="cartDiscountInput" value="0" min="0" step="1" oninput="renderCartSummary()" class="w-full text-right text-xs font-bold text-rose-600 focus:outline-none">
            </div>
            <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-lg px-2 py-1">
              <span class="text-[10px] sm:text-[11px] text-slate-400">Delivery:</span>
              <input type="number" id="cartShippingInput" value="0" min="0" step="1" oninput="renderCartSummary()" class="w-full text-right text-xs font-bold text-slate-800 focus:outline-none">
            </div>
          </div>

          <div class="flex justify-between items-baseline pt-1.5 border-t border-slate-200">
            <span class="text-xs sm:text-sm font-extrabold text-slate-900">NET PAYABLE:</span>
            <span class="text-xl sm:text-2xl font-black text-brand-700" id="cartGrandTotalDisplay">৳0.00</span>
          </div>
        </div>

        {{-- Payment Methods Pills (2x2 on mobile, 4-col on desktop) --}}
        <div>
          <label class="block text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5" id="paymentMethodGroup">
            <button type="button" onclick="setPaymentMethod('cash')" class="pay-btn active py-2 text-xs font-bold rounded-lg border text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs" data-method="cash">
              💵 Cash
            </button>
            <button type="button" onclick="setPaymentMethod('bkash')" class="pay-btn py-2 text-xs font-bold rounded-lg border text-center transition-all bg-white text-slate-700 border-slate-200 hover:bg-slate-100" data-method="bkash">
              📱 bKash
            </button>
            <button type="button" onclick="setPaymentMethod('nagad')" class="pay-btn py-2 text-xs font-bold rounded-lg border text-center transition-all bg-white text-slate-700 border-slate-200 hover:bg-slate-100" data-method="nagad">
              🟠 Nagad
            </button>
            <button type="button" onclick="setPaymentMethod('card')" class="pay-btn py-2 text-xs font-bold rounded-lg border text-center transition-all bg-white text-slate-700 border-slate-200 hover:bg-slate-100" data-method="card">
              💳 Card
            </button>
          </div>
        </div>

        {{-- Cash Tendered & Change (Visible when Cash selected) --}}
        <div id="cashCalculatorRow" class="grid grid-cols-2 gap-2 bg-emerald-50/60 p-2 rounded-xl border border-emerald-200">
          <div>
            <label class="block text-[10px] font-bold text-emerald-800 uppercase">Cash Tendered</label>
            <input type="number" id="cashTenderedInput" placeholder="Given amount" oninput="calculateChange()" class="w-full mt-0.5 px-2.5 py-1 text-xs sm:text-sm font-black text-emerald-900 bg-white border border-emerald-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block text-[10px] font-bold text-emerald-800 uppercase">Change Return</label>
            <div id="changeReturnDisplay" class="text-sm sm:text-base font-black text-emerald-700 mt-1">৳0.00</div>
          </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 pt-0.5">
          <button type="button" onclick="resetCart()" class="px-3.5 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs uppercase tracking-wider transition-colors shrink-0">
            Clear
          </button>
          <button type="button" id="submitOrderBtn" onclick="submitPosOrder()" class="flex-1 py-3 rounded-xl bg-primary hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm uppercase tracking-wider shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Complete Sale
          </button>
        </div>

      </div>

    </div>

  </div>

</div>

{{-- MODAL 1: Variant Selector Modal --}}
<div id="variantModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-md w-full p-4 sm:p-5 shadow-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div>
        <h3 class="font-bold text-slate-900 text-sm" id="variantModalTitle">Select Variant</h3>
        <p class="text-xs text-slate-500">Choose which variation to add to cart</p>
      </div>
      <button type="button" onclick="closeVariantModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">&times;</button>
    </div>

    <div id="variantModalList" class="space-y-2 max-h-72 overflow-y-auto pr-1">
      {{-- Populated dynamically --}}
    </div>
  </div>
</div>

{{-- MODAL 2: Receipt Modal & Auto Print --}}
<div id="receiptModal" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 shadow-2xl text-center space-y-4">
    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
      <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>

    <div>
      <h3 class="text-base sm:text-lg font-black text-slate-900">Sale Completed!</h3>
      <p class="text-xs text-slate-500 mt-0.5">Order <span id="completedOrderNumber" class="font-bold text-slate-800"></span> recorded</p>
    </div>

    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-1">
      <div class="flex justify-between">
        <span class="text-slate-500">Total Paid:</span>
        <strong id="completedOrderTotal" class="text-slate-900"></strong>
      </div>
      <div class="flex justify-between" id="completedChangeRow">
        <span class="text-slate-500">Change Returned:</span>
        <strong id="completedOrderChange" class="text-emerald-700"></strong>
      </div>
    </div>

    <div class="space-y-2 pt-2">
      <button type="button" id="printThermalBtn" onclick="printReceiptPopup()" class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider shadow-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print Thermal Receipt
      </button>
      <button type="button" onclick="closeReceiptModal()" class="w-full py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
        New Sale (Esc)
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

      tabCart.className = "flex-1 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white flex items-center justify-center gap-1.5 transition-all shadow-xs";
      tabCat.className = "flex-1 py-2 text-xs font-extrabold rounded-xl bg-slate-800 text-slate-300 hover:text-white flex items-center justify-center gap-1.5 transition-all border border-slate-700";
    } else {
      cartCol.classList.add('hidden');
      cartCol.classList.remove('flex');
      catCol.classList.remove('hidden');
      catCol.classList.add('flex');

      tabCat.className = "flex-1 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white flex items-center justify-center gap-1.5 transition-all shadow-xs";
      tabCart.className = "flex-1 py-2 text-xs font-extrabold rounded-xl bg-slate-800 text-slate-300 hover:text-white flex items-center justify-center gap-1.5 transition-all border border-slate-700";
    }
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
          card.className = "bg-white p-2 sm:p-3 rounded-2xl border border-slate-200/90 shadow-2xs hover:border-brand-500 hover:shadow-sm transition-all cursor-pointer flex flex-col justify-between group";
          card.onclick = () => onProductCardClick(p);

          card.innerHTML = `
            <div>
              <div class="aspect-square w-full rounded-xl bg-slate-100 overflow-hidden mb-1.5 sm:mb-2 relative">
                <img src="${p.image}" alt="${p.name.replace(/"/g, '&quot;')}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform" onerror="this.onerror=null;this.src='data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'300\\' height=\\'300\\' viewBox=\\'0 0 300 300\\'%3E%3Crect width=\\'300\\' height=\\'300\\' fill=\\'%23f1f5f9\\'/%3E%3Ctext x=\\'150\\' y=\\'155\\' text-anchor=\\'middle\\' fill=\\'%2394a3b8\\' font-family=\\'system-ui,sans-serif\\' font-size=\\'14\\'%3ENo Image%3C/text%3E%3C/svg%3E'">
                ${p.has_skus ? '<span class="absolute top-1 right-1 bg-indigo-600 text-white text-[8px] sm:text-[9px] font-black uppercase px-1.5 py-0.5 rounded shadow">Variants</span>' : ''}
                <span class="absolute bottom-1 left-1 bg-slate-900/80 text-white text-[9px] sm:text-[10px] font-bold px-1.5 py-0.5 rounded backdrop-blur-xs">Stock: ${p.stock}</span>
              </div>
              <div class="font-bold text-slate-800 text-[11px] sm:text-xs line-clamp-2 leading-tight">${p.name}</div>
            </div>
            <div class="mt-2 flex items-center justify-between">
              <span class="font-black text-brand-700 text-xs sm:text-sm">৳${p.price.toFixed(0)}</span>
              <button type="button" class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 font-bold flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition-colors text-xs">
                +
              </button>
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
        btn.className = "category-pill active px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 bg-brand-600 text-white shadow-xs";
      } else {
        btn.className = "category-pill px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200";
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
      btn.className = "w-full p-2.5 sm:p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 flex items-center justify-between text-left transition-all";
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
          <div class="font-bold text-xs text-slate-800">${s.name}</div>
          <div class="text-[10px] text-slate-400">Stock: ${s.stock}</div>
        </div>
        <div class="font-black text-brand-700 text-sm">৳${s.price.toFixed(0)}</div>
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
      row.className = "p-2.5 sm:p-3 flex items-center justify-between gap-2.5 hover:bg-slate-50 transition-colors";

      const lineTotal = item.price * item.quantity;

      row.innerHTML = `
        <div class="flex items-center gap-2 flex-1 min-w-0">
          ${item.image ? `<img src="${item.image}" alt="" class="w-8 h-8 rounded-lg object-cover shrink-0 bg-slate-100 border border-slate-200">` : ''}
          <div class="min-w-0">
            <div class="text-xs font-bold text-slate-800 truncate">${item.name}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400">৳${item.price.toFixed(0)} each</div>
          </div>
        </div>

        <div class="flex items-center gap-1 sm:gap-1.5">
          <button type="button" onclick="updateItemQty('${item.key}', -1)" class="w-6 h-6 sm:w-7 sm:h-7 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center">-</button>
          <span class="w-6 sm:w-7 text-center font-bold text-xs text-slate-900">${item.quantity}</span>
          <button type="button" onclick="updateItemQty('${item.key}', 1)" class="w-6 h-6 sm:w-7 sm:h-7 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs flex items-center justify-center">+</button>
        </div>

        <div class="text-right w-16 sm:w-20">
          <div class="font-extrabold text-xs sm:text-sm text-slate-900">৳${lineTotal.toFixed(0)}</div>
          <button type="button" onclick="removeItem('${item.key}')" class="text-[10px] text-rose-500 hover:underline">Remove</button>
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
        btn.className = "pay-btn active py-2 text-xs font-bold rounded-lg border text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs";
      } else {
        btn.className = "pay-btn py-2 text-xs font-bold rounded-lg border text-center transition-all bg-white text-slate-700 border-slate-200 hover:bg-slate-100";
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
    submitBtn.innerHTML = `<span>Processing Sale...</span>`;

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
      submitBtn.innerHTML = `<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Complete Sale`;

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
      submitBtn.innerHTML = `<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Complete Sale`;
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
