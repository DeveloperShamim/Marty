{{-- Interactive Luxury Size Guide Modal Partial --}}
<div id="sizeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 hidden opacity-0 pointer-events-none transition-all duration-300 ease-out" role="dialog" aria-modal="true" aria-labelledby="sgModalTitle">
  {{-- Frosted Glass Backdrop --}}
  <div class="fixed inset-0 bg-stone-950/70 backdrop-blur-md transition-opacity duration-300" data-close-size-guide></div>

  {{-- Modal Dialog Container --}}
  <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden z-10 flex flex-col max-h-[92vh] border border-stone-100/80 transform scale-95 transition-all duration-300 ease-out" id="sizeGuideDialog">
    
    {{-- Top Subtle Accent Line --}}
    <div class="h-1.5 w-full bg-gradient-to-r from-brand-500 via-amber-500 to-brand-600"></div>

    {{-- Premium Header --}}
    <div class="px-6 py-4.5 sm:px-8 sm:py-5 bg-white border-b border-stone-100 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-2xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center shadow-xs">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
          </svg>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 id="sgModalTitle" class="text-lg sm:text-xl font-extrabold tracking-tight text-stone-900">
              Interactive Fit &amp; Size Guide
            </h2>
            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-brand-50 text-brand-700 border border-brand-200">
              Live Assistant
            </span>
          </div>
          <p class="text-xs text-stone-500 font-medium">Find your exact measurements &amp; international size conversion</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        {{-- Close Button --}}
        <button type="button" data-close-size-guide class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 hover:text-stone-900 flex items-center justify-center transition-all focus:outline-none focus:ring-2 focus:ring-brand-500/30" aria-label="Close modal">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    {{-- Controls Bar: Segmented Tabs & Unit Pill --}}
    <div class="px-6 py-3 sm:px-8 bg-stone-50/70 border-b border-stone-200/60 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shrink-0">
      {{-- Category Navigation Pills --}}
      <div class="inline-flex p-1 bg-stone-200/60 rounded-2xl gap-1 overflow-x-auto no-scrollbar" id="sgTabList">
        <button type="button" data-sg-tab="shoes" class="sg-tab-btn active px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-stone-900 shadow-xs">
          <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
          </svg>
          <span>Footwear</span>
        </button>

        <button type="button" data-sg-tab="belts" class="sg-tab-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-stone-600 hover:text-stone-900">
          <svg class="w-4 h-4 text-stone-400 group-hover:text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
          </svg>
          <span>Belts &amp; Apparel</span>
        </button>

        <button type="button" data-sg-tab="watches" class="sg-tab-btn px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-stone-600 hover:text-stone-900">
          <svg class="w-4 h-4 text-stone-400 group-hover:text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3l2 2M9 2h6M9 22h6" />
          </svg>
          <span>Watches &amp; Straps</span>
        </button>
      </div>

      {{-- Unit Switcher (CM / Inches) --}}
      <div class="flex items-center justify-end gap-2 shrink-0">
        <span class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Unit:</span>
        <div class="inline-flex p-0.5 bg-stone-200/70 rounded-xl text-xs font-bold">
          <button type="button" id="sgUnitCm" class="px-3 py-1.5 rounded-lg bg-white text-stone-900 shadow-xs transition-all">CM</button>
          <button type="button" id="sgUnitIn" class="px-3 py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all">Inches</button>
        </div>
      </div>
    </div>

    {{-- Modal Body (Scrollable with smooth modern typography) --}}
    <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1 text-stone-800 custom-scrollbar">

      {{-- "Find My Precise Size" Interactive Calculator Card --}}
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-stone-50 via-white to-brand-50/40 border border-stone-200/80 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
          <div class="flex items-center gap-2.5">
            <span class="flex h-2.5 w-2.5 rounded-full bg-brand-500 animate-ping"></span>
            <span class="text-xs font-extrabold uppercase tracking-wider text-stone-900">Instant Fit Calculator</span>
          </div>
          <span class="text-[11px] font-semibold text-stone-500 bg-stone-100 px-2.5 py-1 rounded-full">
            Calculates in real-time
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
          <div class="sm:col-span-8 space-y-1.5">
            <label id="sgCalcLabel" for="sgCalcInput" class="block text-xs font-bold text-stone-700">
              Enter Foot Length (<span class="sg-unit-text">CM</span>):
            </label>
            <div class="relative">
              <input 
                type="number" 
                step="0.1" 
                id="sgCalcInput" 
                placeholder="e.g. 26.5" 
                class="w-full pl-4 pr-16 py-2.5 bg-white border border-stone-300 rounded-xl text-sm font-extrabold text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all shadow-2xs" 
              />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 px-2 py-0.5 rounded-md bg-stone-100 text-[11px] font-bold text-stone-600 uppercase tracking-wider sg-unit-text">
                CM
              </span>
            </div>
          </div>

          <div class="sm:col-span-4">
            <button type="button" id="sgCalcBtn" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs sm:text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 group">
              <span>Calculate Size</span>
              <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
          </div>
        </div>

        {{-- Quick Selection Presets for Fast Testing --}}
        <div class="mt-3.5 flex flex-wrap items-center gap-1.5 text-xs text-stone-500">
          <span class="text-[11px] font-medium mr-1">Quick pick:</span>
          <div id="sgQuickPills" class="flex flex-wrap gap-1.5">
            {{-- Injected dynamically --}}
          </div>
        </div>

        {{-- Recommendation Output Card (Animated upon match) --}}
        <div id="sgResultBox" class="hidden mt-4 p-4 bg-white/95 rounded-xl border border-brand-200 text-stone-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs transition-all">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <div class="text-[11px] uppercase tracking-wider font-bold text-stone-400">Your Recommended Fit</div>
              <div id="sgResultText" class="text-sm sm:text-base font-black text-stone-900"></div>
            </div>
          </div>
          <div class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Table Row Highlighted
          </div>
        </div>
      </div>

      {{-- Tab 1: Shoes / Footwear Table --}}
      <div id="sgTabContent-shoes" class="sg-tab-content space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-extrabold text-sm sm:text-base text-stone-900">Footwear Size Chart</h4>
            <p class="text-xs text-stone-500">Standard international conversions across EU, US, and UK</p>
          </div>
          <span class="text-[11px] font-semibold text-stone-500 bg-stone-100 px-2.5 py-1 rounded-lg">Click row to select</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200/90 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100/80 text-stone-600 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 sm:px-5">EU Size</th>
                <th class="py-3 px-4">US (Men)</th>
                <th class="py-3 px-4">US (Women)</th>
                <th class="py-3 px-4">UK Size</th>
                <th class="py-3 px-4 sm:px-5">Foot Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgShoesTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Visual 3-Step Measuring Guide for Shoes --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
          <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-1">
            <div class="flex items-center gap-2">
              <span class="w-5 h-5 rounded-full bg-stone-900 text-white font-black text-[10px] flex items-center justify-center">1</span>
              <span class="font-extrabold text-xs text-stone-900">Heel Against Wall</span>
            </div>
            <p class="text-[11px] text-stone-600 leading-relaxed">Place a sheet of paper on the floor against a flat wall. Stand upright on it.</p>
          </div>
          <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-1">
            <div class="flex items-center gap-2">
              <span class="w-5 h-5 rounded-full bg-stone-900 text-white font-black text-[10px] flex items-center justify-center">2</span>
              <span class="font-extrabold text-xs text-stone-900">Mark Longest Toe</span>
            </div>
            <p class="text-[11px] text-stone-600 leading-relaxed">Mark the furthest tip of your foot with a pencil perpendicular to the paper.</p>
          </div>
          <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-1">
            <div class="flex items-center gap-2">
              <span class="w-5 h-5 rounded-full bg-stone-900 text-white font-black text-[10px] flex items-center justify-center">3</span>
              <span class="font-extrabold text-xs text-stone-900">Measure Distance</span>
            </div>
            <p class="text-[11px] text-stone-600 leading-relaxed">Measure the length in centimeters. If between sizes, choose the larger size.</p>
          </div>
        </div>
      </div>

      {{-- Tab 2: Belts & Apparel Table --}}
      <div id="sgTabContent-belts" class="sg-tab-content space-y-4 hidden">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-extrabold text-sm sm:text-base text-stone-900">Belts &amp; Apparel Sizing</h4>
            <p class="text-xs text-stone-500">Waist circumference and corresponding strap lengths</p>
          </div>
          <span class="text-[11px] font-semibold text-stone-500 bg-stone-100 px-2.5 py-1 rounded-lg">Click row to select</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200/90 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100/80 text-stone-600 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 sm:px-5">Belt Size</th>
                <th class="py-3 px-4">Waist Circumference (<span class="sg-unit-text">CM</span>)</th>
                <th class="py-3 px-4">Pants Size (Inches)</th>
                <th class="py-3 px-4 sm:px-5">Total Strap Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgBeltsTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Belt Pro Fit Rule --}}
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-3">
          <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 font-bold">
            💡
          </div>
          <div class="space-y-0.5 text-xs text-amber-950">
            <span class="font-extrabold block">The Golden Belt Rule:</span>
            <p class="text-amber-900/90 leading-relaxed">
              Order your belt size <strong>2 inches (5 cm) larger</strong> than your standard pants waist. For example, if you wear size 32 pants, select a size 34 belt so the buckle secures cleanly at the center hole.
            </p>
          </div>
        </div>
      </div>

      {{-- Tab 3: Watches & Straps Table --}}
      <div id="sgTabContent-watches" class="sg-tab-content space-y-4 hidden">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-extrabold text-sm sm:text-base text-stone-900">Watch Case Diameter &amp; Strap Sizing</h4>
            <p class="text-xs text-stone-500">Proportional case diameters matched to wrist circumference</p>
          </div>
          <span class="text-[11px] font-semibold text-stone-500 bg-stone-100 px-2.5 py-1 rounded-lg">Click row to select</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200/90 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100/80 text-stone-600 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 sm:px-5">Wrist Circumference</th>
                <th class="py-3 px-4">Ideal Case Diameter</th>
                <th class="py-3 px-4">Style &amp; Profile</th>
                <th class="py-3 px-4 sm:px-5">Strap Width</th>
              </tr>
            </thead>
            <tbody id="sgWatchesTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Measuring Guide for Wrist --}}
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-start gap-3">
          <div class="w-8 h-8 rounded-xl bg-stone-200/70 text-stone-700 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3l2 2" /></svg>
          </div>
          <div class="space-y-0.5 text-xs text-stone-800">
            <span class="font-extrabold block">How to Measure Your Wrist:</span>
            <p class="text-stone-600 leading-relaxed">
              Wrap a flexible tailor's tape or a strip of paper around your wrist just below the wrist bone where you would naturally wear your watch. Mark the overlap point and measure against a flat ruler.
            </p>
          </div>
        </div>
      </div>

      {{-- Dynamic Merchant Tip (If configured by admin) --}}
      @if($customTip = setting('size_guide_custom_tip'))
        <div class="p-4 bg-brand-50/60 border border-brand-200/80 rounded-2xl text-xs text-stone-900 flex items-center gap-3">
          <span class="text-base">📢</span>
          <div>
            <span class="font-bold text-brand-900">Store Fit Note:</span> 
            <span class="text-stone-700">{{ $customTip }}</span>
          </div>
        </div>
      @endif

    </div>

    {{-- Premium Modern Footer --}}
    <div class="px-6 py-4 sm:px-8 bg-stone-50/90 border-t border-stone-200/70 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
      <div class="flex items-center gap-2 text-xs text-stone-500 font-medium">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
        </svg>
        <span>100% Fit Guarantee • Need help? <a href="{{ route('contact') }}" class="text-brand-600 font-bold hover:underline">Contact Support</a></span>
      </div>

      <button type="button" data-close-size-guide class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-stone-900 hover:bg-black text-white font-extrabold text-xs shadow-sm hover:shadow transition-all">
        Got It, Continue Shopping
      </button>
    </div>

  </div>
</div>

@php
  $customShoes = json_decode((string) setting('size_guide_shoes_data'), true);
  $shoesData = (!empty($customShoes) && is_array($customShoes)) ? $customShoes : \App\Http\Controllers\Admin\SizeGuideController::defaultShoes();

  $customBelts = json_decode((string) setting('size_guide_belts_data'), true);
  $beltsData = (!empty($customBelts) && is_array($customBelts)) ? $customBelts : \App\Http\Controllers\Admin\SizeGuideController::defaultBelts();

  $customWatches = json_decode((string) setting('size_guide_watches_data'), true);
  $watchesData = (!empty($customWatches) && is_array($customWatches)) ? $customWatches : \App\Http\Controllers\Admin\SizeGuideController::defaultWatches();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('sizeGuideModal');
  const dialog = document.getElementById('sizeGuideDialog');
  let currentUnit = '{{ setting("size_guide_default_unit", "cm") }}';
  let currentTab = 'shoes';
  let selectedRowIdx = -1;

  // Dynamic size datasets
  const shoesData = {!! json_encode($shoesData) !!};
  const beltsData = {!! json_encode($beltsData) !!};
  const watchesData = {!! json_encode($watchesData) !!};

  function formatVal(valCm) {
    const num = parseFloat(valCm);
    if (isNaN(num)) return valCm;
    if (currentUnit === 'in') {
      return (num / 2.54).toFixed(1) + '"';
    }
    return num.toFixed(1) + ' cm';
  }

  // Quick Pick Presets
  const quickPresets = {
    shoes: {
      cm: [24.5, 25.5, 26.5, 27.5, 28.5],
      in: [9.6, 10.0, 10.4, 10.8, 11.2]
    },
    belts: {
      cm: [80, 85, 90, 95, 100],
      in: [32, 34, 36, 38, 40]
    },
    watches: {
      cm: [15.0, 16.5, 17.5, 19.0],
      in: [6.0, 6.5, 7.0, 7.5]
    }
  };

  function renderQuickPills() {
    const container = document.getElementById('sgQuickPills');
    if (!container) return;
    const list = (quickPresets[currentTab] && quickPresets[currentTab][currentUnit]) || [];
    const unitLabel = currentUnit === 'in' ? '"' : ' cm';
    
    container.innerHTML = list.map(val => `
      <button type="button" class="sg-quick-btn px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 border border-stone-200/80 text-[11px] font-bold text-stone-700 transition-all cursor-pointer" data-val="${val}">
        ${val}${unitLabel}
      </button>
    `).join('');

    container.querySelectorAll('.sg-quick-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const input = document.getElementById('sgCalcInput');
        if (input) {
          input.value = this.getAttribute('data-val');
          calculateFit();
        }
      });
    });
  }

  function renderTables(highlightRowIndex = -1) {
    selectedRowIdx = highlightRowIndex;
    // Update all unit labels across the modal
    document.querySelectorAll('.sg-unit-text').forEach(el => el.textContent = currentUnit.toUpperCase());

    // 1. Render Shoes
    const shoesBody = document.getElementById('sgShoesTableBody');
    if (shoesBody) {
      shoesBody.innerHTML = shoesData.map((row, idx) => {
        const isMatch = idx === highlightRowIndex && currentTab === 'shoes';
        return `
          <tr data-row-idx="${idx}" class="sg-interactive-row cursor-pointer transition-all duration-150 ${isMatch ? 'bg-brand-50/90 font-bold border-l-4 border-brand-500 shadow-2xs' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/50')} hover:bg-stone-100/70">
            <td class="py-3 px-4 sm:px-5 font-black text-stone-900">
              <div class="flex items-center gap-2">
                <span>${row.eu ?? ''}</span>
                ${isMatch ? '<span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-brand-500 text-white uppercase tracking-wider">Fit</span>' : ''}
              </div>
            </td>
            <td class="py-3 px-4 text-stone-700 font-semibold">${row.usM ?? ''}</td>
            <td class="py-3 px-4 text-stone-700 font-semibold">${row.usW ?? ''}</td>
            <td class="py-3 px-4 text-stone-700 font-semibold">${row.uk ?? ''}</td>
            <td class="py-3 px-4 sm:px-5 font-bold text-stone-900">${formatVal(row.cm ?? 0)}</td>
          </tr>
        `;
      }).join('');
    }

    // 2. Render Belts
    const beltsBody = document.getElementById('sgBeltsTableBody');
    if (beltsBody) {
      beltsBody.innerHTML = beltsData.map((row, idx) => {
        const isMatch = idx === highlightRowIndex && currentTab === 'belts';
        return `
          <tr data-row-idx="${idx}" class="sg-interactive-row cursor-pointer transition-all duration-150 ${isMatch ? 'bg-brand-50/90 font-bold border-l-4 border-brand-500 shadow-2xs' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/50')} hover:bg-stone-100/70">
            <td class="py-3 px-4 sm:px-5 font-black text-stone-900">
              <div class="flex items-center gap-2">
                <span>${row.size ?? ''}</span>
                ${isMatch ? '<span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-brand-500 text-white uppercase tracking-wider">Fit</span>' : ''}
              </div>
            </td>
            <td class="py-3 px-4 text-stone-900 font-bold">${formatVal(row.waistCm ?? 0)}</td>
            <td class="py-3 px-4 text-stone-700 font-semibold">${row.pants ?? ''}</td>
            <td class="py-3 px-4 sm:px-5 font-medium text-stone-600">${formatVal(row.strapLengthCm ?? 0)}</td>
          </tr>
        `;
      }).join('');
    }

    // 3. Render Watches
    const watchesBody = document.getElementById('sgWatchesTableBody');
    if (watchesBody) {
      watchesBody.innerHTML = watchesData.map((row, idx) => {
        const isMatch = idx === highlightRowIndex && currentTab === 'watches';
        let displayWrist = row.wristCm ?? '';
        if (currentUnit === 'in' && String(row.wristCm).includes(' - ')) {
          displayWrist = row.wristCm.split(' - ').map(v => (parseFloat(v) / 2.54).toFixed(1)).join(' - ') + '"';
        } else {
          displayWrist = row.wristCm + ' cm';
        }

        let stylePillClass = 'bg-stone-100 text-stone-700';
        if (String(row.look).toLowerCase().includes('bold') || String(row.look).toLowerCase().includes('oversized')) {
          stylePillClass = 'bg-amber-50 text-amber-800 border border-amber-200/70';
        } else if (String(row.look).toLowerCase().includes('versatile') || String(row.look).toLowerCase().includes('standard')) {
          stylePillClass = 'bg-blue-50 text-blue-800 border border-blue-200/70';
        } else {
          stylePillClass = 'bg-stone-100 text-stone-800 border border-stone-200/70';
        }

        return `
          <tr data-row-idx="${idx}" class="sg-interactive-row cursor-pointer transition-all duration-150 ${isMatch ? 'bg-brand-50/90 font-bold border-l-4 border-brand-500 shadow-2xs' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/50')} hover:bg-stone-100/70">
            <td class="py-3 px-4 sm:px-5 font-black text-stone-900">
              <div class="flex items-center gap-2">
                <span>${displayWrist}</span>
                ${isMatch ? '<span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-brand-500 text-white uppercase tracking-wider">Fit</span>' : ''}
              </div>
            </td>
            <td class="py-3 px-4 text-stone-900 font-extrabold">${row.caseSize ?? ''}</td>
            <td class="py-3 px-4">
              <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold ${stylePillClass}">
                ${row.look ?? ''}
              </span>
            </td>
            <td class="py-3 px-4 sm:px-5 font-medium text-stone-600">${row.strap ?? ''}</td>
          </tr>
        `;
      }).join('');
    }

    // Attach click listeners to rows so user can tap any row
    document.querySelectorAll('.sg-interactive-row').forEach(rowEl => {
      rowEl.addEventListener('click', function () {
        const rowIdx = parseInt(this.getAttribute('data-row-idx'), 10);
        selectTableRow(rowIdx);
      });
    });
  }

  function selectTableRow(idx) {
    renderTables(idx);
    const resultBox = document.getElementById('sgResultBox');
    const resultText = document.getElementById('sgResultText');
    const input = document.getElementById('sgCalcInput');

    if (currentTab === 'shoes' && shoesData[idx]) {
      const match = shoesData[idx];
      if (input) input.value = currentUnit === 'in' ? (parseFloat(match.cm) / 2.54).toFixed(1) : match.cm;
      if (resultText && resultBox) {
        resultText.textContent = `EU ${match.eu} • US Men ${match.usM} (US Women ${match.usW} / UK ${match.uk})`;
        resultBox.classList.remove('hidden');
      }
    } else if (currentTab === 'belts' && beltsData[idx]) {
      const match = beltsData[idx];
      if (input) input.value = currentUnit === 'in' ? (parseFloat(match.waistCm) / 2.54).toFixed(1) : match.waistCm;
      if (resultText && resultBox) {
        resultText.textContent = `Belt Size ${match.size} • Suits Pants Waist ${match.pants} (Strap ${formatVal(match.strapLengthCm)})`;
        resultBox.classList.remove('hidden');
      }
    } else if (currentTab === 'watches' && watchesData[idx]) {
      const match = watchesData[idx];
      let firstNum = parseFloat(String(match.wristCm).split(' - ')[0]) || 16;
      if (input) input.value = currentUnit === 'in' ? (firstNum / 2.54).toFixed(1) : firstNum;
      if (resultText && resultBox) {
        resultText.textContent = `${match.caseSize} Case • ${match.look} (${match.strap} Strap)`;
        resultBox.classList.remove('hidden');
      }
    }
  }

  // Global Triggers
  window.openSizeGuideModal = function (categoryHint) {
    if (!modal || !dialog) return;

    if (categoryHint) {
      const catLower = categoryHint.toLowerCase();
      if (catLower.includes('shoe') || catLower.includes('foot') || catLower.includes('sneaker') || catLower.includes('boot')) {
        switchTab('shoes');
      } else if (catLower.includes('belt') || catLower.includes('apparel') || catLower.includes('pant') || catLower.includes('cloth')) {
        switchTab('belts');
      } else if (catLower.includes('watch') || catLower.includes('strap') || catLower.includes('accessory')) {
        switchTab('watches');
      }
    }

    renderQuickPills();
    renderTables();
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    dialog.classList.remove('scale-95');
    dialog.classList.add('scale-100');
    document.body.style.overflow = 'hidden';
  };

  function closeSizeGuideModal() {
    if (!modal || !dialog) return;
    modal.classList.add('opacity-0', 'pointer-events-none');
    dialog.classList.remove('scale-100');
    dialog.classList.add('scale-95');
    document.body.style.overflow = '';
    setTimeout(() => {
      if (modal.classList.contains('opacity-0')) {
        modal.classList.add('hidden');
      }
    }, 300);
  }

  // Event Listeners for Open/Close
  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-open-size-guide]');
    if (trigger) {
      e.preventDefault();
      const catHint = trigger.getAttribute('data-category-hint') || '';
      openSizeGuideModal(catHint);
    }

    if (e.target.closest('[data-close-size-guide]')) {
      closeSizeGuideModal();
    }
  });

  // ESC Key listener
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && !modal.classList.contains('opacity-0')) {
      closeSizeGuideModal();
    }
  });

  // Tab Switcher
  function switchTab(tabId) {
    currentTab = tabId;
    selectedRowIdx = -1;

    document.querySelectorAll('#sgTabList .sg-tab-btn').forEach(btn => {
      const isTarget = btn.getAttribute('data-sg-tab') === tabId;
      btn.classList.toggle('bg-white', isTarget);
      btn.classList.toggle('text-stone-900', isTarget);
      btn.classList.toggle('shadow-xs', isTarget);
      btn.classList.toggle('text-stone-600', !isTarget);
      
      const icon = btn.querySelector('svg');
      if (icon) {
        if (isTarget) {
          icon.classList.add('text-brand-500');
          icon.classList.remove('text-stone-400');
        } else {
          icon.classList.remove('text-brand-500');
          icon.classList.add('text-stone-400');
        }
      }
    });

    document.querySelectorAll('.sg-tab-content').forEach(content => {
      content.classList.toggle('hidden', content.id !== 'sgTabContent-' + tabId);
    });

    // Update calculator label based on tab
    const calcLabel = document.getElementById('sgCalcLabel');
    const calcInput = document.getElementById('sgCalcInput');
    const resultBox = document.getElementById('sgResultBox');
    if (resultBox) resultBox.classList.add('hidden');

    if (calcLabel && calcInput) {
      calcInput.value = '';
      if (tabId === 'belts') {
        calcLabel.innerHTML = `Enter Waist Circumference (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 86' : 'e.g. 34';
      } else if (tabId === 'watches') {
        calcLabel.innerHTML = `Enter Wrist Circumference (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 17.0' : 'e.g. 6.7';
      } else {
        calcLabel.innerHTML = `Enter Foot Length (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 26.5' : 'e.g. 10.4';
      }
    }

    renderQuickPills();
    renderTables();
  }

  document.querySelectorAll('#sgTabList .sg-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      switchTab(this.getAttribute('data-sg-tab'));
    });
  });

  // Unit Switcher (CM / Inches)
  const btnCm = document.getElementById('sgUnitCm');
  const btnIn = document.getElementById('sgUnitIn');

  function setUnit(unit) {
    currentUnit = unit;
    if (btnCm && btnIn) {
      if (unit === 'cm') {
        btnCm.className = 'px-3 py-1.5 rounded-lg bg-white text-stone-900 shadow-xs transition-all';
        btnIn.className = 'px-3 py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all';
      } else {
        btnIn.className = 'px-3 py-1.5 rounded-lg bg-white text-stone-900 shadow-xs transition-all';
        btnCm.className = 'px-3 py-1.5 rounded-lg text-stone-500 hover:text-stone-900 transition-all';
      }
    }
    renderQuickPills();
    switchTab(currentTab);
  }

  if (btnCm) btnCm.addEventListener('click', () => setUnit('cm'));
  if (btnIn) btnIn.addEventListener('click', () => setUnit('in'));

  // Fit Calculator Logic
  const calcBtn = document.getElementById('sgCalcBtn');
  const calcInput = document.getElementById('sgCalcInput');
  const resultBox = document.getElementById('sgResultBox');
  const resultText = document.getElementById('sgResultText');

  function calculateFit() {
    if (!calcInput) return;
    const rawVal = parseFloat(calcInput.value);
    if (isNaN(rawVal) || rawVal <= 0) {
      if (resultBox) resultBox.classList.add('hidden');
      renderTables(-1);
      return;
    }

    let valCm = rawVal;
    if (currentUnit === 'in') {
      valCm = rawVal * 2.54;
    }

    if (currentTab === 'shoes' && shoesData.length > 0) {
      let match = shoesData[0];
      let matchIdx = 0;
      for (let i = 0; i < shoesData.length; i++) {
        if (valCm <= parseFloat(shoesData[i].cm) + 0.3) {
          match = shoesData[i];
          matchIdx = i;
          break;
        }
      }
      if (valCm > parseFloat(shoesData[shoesData.length - 1].cm) + 0.3) {
        match = shoesData[shoesData.length - 1];
        matchIdx = shoesData.length - 1;
      }

      if (resultText && resultBox) {
        resultText.textContent = `EU ${match.eu} • US Men ${match.usM} (US Women ${match.usW} / UK ${match.uk})`;
        resultBox.classList.remove('hidden');
      }
      renderTables(matchIdx);

    } else if (currentTab === 'belts' && beltsData.length > 0) {
      let match = beltsData[0];
      let matchIdx = 0;
      for (let i = 0; i < beltsData.length; i++) {
        if (valCm <= parseFloat(beltsData[i].waistCm) + 3) {
          match = beltsData[i];
          matchIdx = i;
          break;
        }
      }
      if (valCm > parseFloat(beltsData[beltsData.length - 1].waistCm) + 3) {
        match = beltsData[beltsData.length - 1];
        matchIdx = beltsData.length - 1;
      }

      if (resultText && resultBox) {
        resultText.textContent = `Belt Size ${match.size} (Best for Pants Waist ${match.pants})`;
        resultBox.classList.remove('hidden');
      }
      renderTables(matchIdx);

    } else if (currentTab === 'watches' && watchesData.length > 0) {
      let match = watchesData[0];
      let matchIdx = 0;
      for (let i = 0; i < watchesData.length; i++) {
        let parts = String(watchesData[i].wristCm).replace('+', '').split(' - ').map(parseFloat);
        let upper = parts[1] || (parts[0] + 2);
        if (valCm <= upper + 0.5) {
          match = watchesData[i];
          matchIdx = i;
          break;
        }
      }
      if (valCm > 18) {
        match = watchesData[watchesData.length - 1];
        matchIdx = watchesData.length - 1;
      }

      if (resultText && resultBox) {
        resultText.textContent = `${match.caseSize} Case Diameter • ${match.look} (${match.strap} Strap)`;
        resultBox.classList.remove('hidden');
      }
      renderTables(matchIdx);
    }
  }

  if (calcBtn) calcBtn.addEventListener('click', calculateFit);
  if (calcInput) {
    calcInput.addEventListener('input', calculateFit);
    calcInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        calculateFit();
      }
    });
  }

  // Initial render
  renderQuickPills();
  renderTables();
});
</script>

