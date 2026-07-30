
<div id="quickSelectModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden opacity-0 transition-all duration-300 pointer-events-none" aria-hidden="true">
  <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-stone-200 transform scale-95 transition-all duration-300 flex flex-col max-h-[90vh]" data-modal-container>
    
    <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between bg-stone-50/80">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
        <h3 class="font-extrabold text-stone-900 text-base">Select Options</h3>
      </div>
      <button type="button" id="closeQuickModal" class="w-8 h-8 rounded-full bg-stone-200/60 hover:bg-stone-200 text-stone-600 font-bold flex items-center justify-center text-sm transition-colors focus:outline-none" aria-label="Close Modal">✕</button>
    </div>

    
    <div class="p-5 overflow-y-auto space-y-4 flex-1">
      
      <div class="flex gap-4 items-center pb-4 border-b border-stone-100">
        <div class="w-20 h-20 rounded-xl border border-stone-200 shrink-0 bg-stone-50 overflow-hidden relative">
          <img id="qmProductImg" src="" alt="Product image" class="w-full h-full object-cover" />
          <span id="qmDiscountBadge" class="absolute top-1 left-1 bg-red-500 text-white font-extrabold text-[9px] px-1.5 py-0.5 rounded shadow-xs hidden"></span>
        </div>
        <div class="flex-1 min-w-0">
          <h4 id="qmProductTitle" class="font-bold text-stone-900 text-sm sm:text-base leading-tight truncate"></h4>
          <div class="flex items-baseline gap-2 mt-1.5">
            <span id="qmPrice" class="text-xl font-extrabold text-brand-500"></span>
            <span id="qmRegularPrice" class="text-xs text-stone-400 line-through font-normal hidden"></span>
          </div>
          <p id="qmSelectedNotice" class="text-xs text-stone-500 mt-1 truncate">Please choose your options below:</p>
        </div>
      </div>

      
      <div id="qmVariantsContainer" class="space-y-4">
        
      </div>

      
      <div id="qmErrorAlert" class="hidden bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3 rounded-xl flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="qmErrorMessage">Please select a variation before continuing.</span>
      </div>

      
      <div class="flex items-center justify-between pt-2 border-t border-stone-100">
        <span class="text-xs font-bold uppercase tracking-wider text-stone-600">Quantity</span>
        <div class="inline-flex items-center border border-stone-300 rounded-lg overflow-hidden bg-white shadow-xs">
          <button type="button" id="qmQtyDec" class="px-3 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">−</button>
          <input id="qmQty" value="1" class="w-10 text-center border-0 font-bold text-stone-800 focus:outline-none text-sm bg-transparent" readonly />
          <button type="button" id="qmQtyInc" class="px-3 py-1.5 text-stone-500 hover:bg-stone-100 font-bold text-sm transition-colors">+</button>
        </div>
      </div>
    </div>

    
    <div class="p-4 bg-stone-50 border-t border-stone-100 grid grid-cols-2 gap-3">
      <button type="button" id="qmAddToCartBtn" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-3 px-4 rounded-xl shadow transition-all flex items-center justify-center gap-1.5 text-xs sm:text-sm uppercase tracking-wide cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span>ADD TO CART</span>
      </button>

      <button type="button" id="qmBuyNowBtn" class="w-full bg-[#0B2523] hover:bg-black text-white font-extrabold py-3 px-4 rounded-xl shadow transition-all flex items-center justify-center text-xs sm:text-sm uppercase tracking-wide cursor-pointer">
        <span>BUY NOW</span>
      </button>
    </div>
  </div>
</div>
<?php /**PATH /Users/mohammadshamimhossain/Desktop/appFinal/resources/views/storefront/partials/quick-select-modal.blade.php ENDPATH**/ ?>