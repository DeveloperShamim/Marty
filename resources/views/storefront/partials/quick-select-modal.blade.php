{{-- Quick Select Variation Modal --}}
{{-- A sheet that slides up from the bottom on phones, a centred dialog from tablets up --}}
<div id="quickSelectModal" class="fixed inset-0 z-[80] bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 hidden opacity-0 transition-all duration-300 pointer-events-none" aria-hidden="true">
  <div class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl overflow-hidden border border-stone-200 transform scale-95 transition-all duration-300 flex flex-col max-h-[88dvh] sm:max-h-[90vh] pb-[env(safe-area-inset-bottom)]" data-modal-container role="dialog" aria-modal="true" aria-labelledby="qmHeading">
    {{-- Drag handle on phones, then the title and close button --}}
    <div class="sm:hidden pt-2.5 flex justify-center" aria-hidden="true"><span class="h-1 w-10 rounded-full bg-stone-300"></span></div>
    <div class="px-5 pt-2 pb-3 sm:pt-4 border-b border-stone-100 flex items-center justify-between">
      <h3 id="qmHeading" class="font-extrabold text-stone-900 text-base">Choose your options</h3>
      <button type="button" id="closeQuickModal" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold flex items-center justify-center text-sm transition-colors" aria-label="Close">✕</button>
    </div>

    {{-- Modal Body --}}
    <div class="p-5 overflow-y-auto space-y-4 flex-1">
      {{-- Product, price, live status and quantity --}}
      <div class="flex gap-4 items-start pb-4 border-b border-stone-100">
        <div class="w-20 h-20 rounded-xl border border-stone-200 shrink-0 bg-stone-50 overflow-hidden relative">
          <img id="qmProductImg" src="" alt="Product image" class="w-full h-full object-cover" />
          <span id="qmDiscountBadge" class="absolute top-1 left-1 text-white font-extrabold text-[9px] px-1.5 py-0.5 rounded shadow-xs hidden" style="background-color: var(--brand-dark, #1c1917);"></span>
        </div>
        <div class="flex-1 min-w-0">
          <h4 id="qmProductTitle" class="font-bold text-stone-900 text-sm sm:text-base leading-tight line-clamp-2"></h4>
          <div class="flex items-center justify-between gap-3 mt-1.5">
            <div class="flex items-baseline gap-2 min-w-0">
              <span id="qmPrice" class="text-xl font-extrabold text-brand-600 tabular-nums"></span>
              <span id="qmRegularPrice" class="text-xs text-stone-400 line-through font-normal tabular-nums hidden"></span>
            </div>
            <div class="inline-flex items-center border border-stone-200 rounded-lg overflow-hidden bg-white shrink-0" aria-label="Quantity">
              <button type="button" id="qmQtyDec" class="w-8 h-8 text-stone-500 hover:bg-stone-100 font-bold text-sm" aria-label="One less">−</button>
              <input id="qmQty" value="1" class="w-7 text-center border-0 font-bold text-stone-800 focus:outline-none text-sm bg-transparent tabular-nums" readonly aria-label="Quantity" />
              <button type="button" id="qmQtyInc" class="w-8 h-8 text-stone-500 hover:bg-stone-100 font-bold text-sm" aria-label="One more">+</button>
            </div>
          </div>
          <p id="qmSelectedNotice" class="text-xs font-semibold text-stone-500 mt-1.5 truncate" aria-live="polite"></p>
        </div>
      </div>

      {{-- Variant Groups Container --}}
      <div id="qmVariantsContainer" class="space-y-4">
        {{-- Injected dynamically via JS --}}
      </div>

      {{-- Validation Error Alert Box --}}
      <div id="qmErrorAlert" class="hidden bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3 rounded-xl flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="qmErrorMessage">Please select a variation before continuing.</span>
      </div>
    </div>

    {{-- Order now leads with the total; Add to cart is the smaller button beside it --}}
    <div class="p-4 bg-stone-50 border-t border-stone-100 flex gap-3">
      <button type="button" id="qmAddToCartBtn" class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center transition-all cursor-pointer select-none touch-manipulation disabled:cursor-not-allowed" aria-label="Add to cart" title="Add to cart">
        <svg class="w-5 h-5 shrink-0 relative z-10 pointer-events-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span class="sr-only">Add to cart</span>
      </button>

      <button type="button" id="qmBuyNowBtn" style="background-color: var(--brand-dark, #1c1917);" class="flex-1 min-w-0 h-12 hover:brightness-95 text-white font-extrabold px-4 rounded-xl shadow transition-all flex items-center justify-center gap-1.5 text-sm cursor-pointer select-none touch-manipulation disabled:cursor-not-allowed">
        <span class="relative z-10 pointer-events-auto select-none">Order now</span>
        <span id="qmBuyTotal" class="relative z-10 pointer-events-auto select-none font-semibold opacity-90 tabular-nums"></span>
      </button>
    </div>
  </div>
</div>
