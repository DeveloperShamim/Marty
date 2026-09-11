{{-- Luxury Human-Designed Size Guide Modal --}}
<div id="sizeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 hidden opacity-0 pointer-events-none transition-all duration-200 ease-out" role="dialog" aria-modal="true" aria-labelledby="sgModalTitle">
  {{-- Refined Backdrop --}}
  <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity duration-200" data-close-size-guide></div>

  {{-- Modal Window --}}
  <div class="relative w-full max-w-2xl bg-white rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh] border border-stone-200/80 transform scale-95 transition-all duration-200 ease-out" id="sizeGuideDialog">

    {{-- Editorial Header --}}
    <div class="px-6 py-4.5 sm:px-7 sm:py-5 border-b border-stone-150 flex items-center justify-between shrink-0 bg-white">
      <div>
        <h2 id="sgModalTitle" class="text-lg sm:text-xl font-bold tracking-tight text-stone-900">
          Size Guide
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">International conversions &amp; measurement guide</p>
      </div>

      <div class="flex items-center gap-3">
        {{-- Minimalist Unit Toggle --}}
        <div class="inline-flex p-0.5 rounded-lg bg-stone-100 border border-stone-200/60 text-xs font-semibold text-stone-600">
          <button type="button" id="sgUnitCm" class="px-2.5 py-1 rounded-md bg-white text-stone-900 shadow-2xs font-bold transition-all">CM</button>
          <button type="button" id="sgUnitIn" class="px-2.5 py-1 rounded-md text-stone-500 hover:text-stone-900 transition-all">IN</button>
        </div>

        {{-- Minimal Close Button --}}
        <button type="button" data-close-size-guide class="w-8 h-8 rounded-full text-stone-400 hover:text-stone-800 hover:bg-stone-100 flex items-center justify-center transition-colors focus:outline-none" aria-label="Close size guide">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    {{-- Category Underline Navigation --}}
    <div class="px-6 sm:px-7 border-b border-stone-150 bg-white shrink-0 overflow-x-auto no-scrollbar">
      <nav class="flex space-x-6 sm:space-x-8" id="sgTabList" aria-label="Size Guide Tabs">
        <button type="button" data-sg-tab="shoes" class="sg-tab-btn py-3 text-xs sm:text-sm font-semibold border-b-2 border-stone-900 text-stone-900 whitespace-nowrap transition-colors">
          Footwear
        </button>
        <button type="button" data-sg-tab="belts" class="sg-tab-btn py-3 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-stone-500 hover:text-stone-800 whitespace-nowrap transition-colors">
          Belts &amp; Apparel
        </button>
        <button type="button" data-sg-tab="watches" class="sg-tab-btn py-3 text-xs sm:text-sm font-semibold border-b-2 border-transparent text-stone-500 hover:text-stone-800 whitespace-nowrap transition-colors">
          Watches &amp; Straps
        </button>
      </nav>
    </div>

    {{-- Modal Body (Scrollable) --}}
    <div class="p-6 sm:p-7 overflow-y-auto space-y-6 flex-1 text-stone-800">

      {{-- Discreet Fit Helper (Quiet, clean inline helper) --}}
      <div class="bg-stone-50 border border-stone-200/70 rounded-xl p-3.5 sm:p-4 transition-all">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <label id="sgCalcLabel" for="sgCalcInput" class="text-xs font-semibold text-stone-700 shrink-0">
              Find my fit (<span class="sg-unit-text">CM</span>):
            </label>
            <div class="relative w-28 sm:w-32">
              <input 
                type="number" 
                step="0.1" 
                id="sgCalcInput" 
                placeholder="e.g. 26.5" 
                class="w-full px-3 py-1.5 bg-white border border-stone-300 rounded-lg text-xs font-bold text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-stone-900 focus:ring-1 focus:ring-stone-900 transition-all" 
              />
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-stone-400 uppercase sg-unit-text">CM</span>
            </div>
          </div>

          <div id="sgResultBadge" class="hidden text-xs text-stone-600 sm:text-right">
            <span class="text-stone-500">Suggested size:</span>
            <span id="sgResultText" class="font-bold text-stone-950 ml-1"></span>
          </div>
        </div>
      </div>

      {{-- Tab 1: Footwear Chart --}}
      <div id="sgTabContent-shoes" class="sg-tab-content space-y-5">
        <div class="overflow-x-auto rounded-xl border border-stone-200/80">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-50/80 text-stone-500 font-semibold border-b border-stone-200/80 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 font-semibold">EU</th>
                <th class="py-3 px-4 font-semibold">US Men</th>
                <th class="py-3 px-4 font-semibold">US Women</th>
                <th class="py-3 px-4 font-semibold">UK</th>
                <th class="py-3 px-4 font-semibold">Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgShoesTableBody" class="divide-y divide-stone-150 font-normal">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Footwear Measurement Advice --}}
        <div class="rounded-xl border border-stone-200/80 p-4 bg-white flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center shrink-0 text-stone-600">
            {{-- Minimalist Foot Vector Icon --}}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
            </svg>
          </div>
          <div class="space-y-0.5 text-xs text-stone-600">
            <span class="font-bold text-stone-900 block">How to measure foot length:</span>
            <p class="leading-relaxed text-stone-600">
              Stand upright on a sheet of paper with your heel against a flat wall. Mark your longest toe and measure the distance to the edge. If you fall between sizes, we recommend ordering the larger size.
            </p>
          </div>
        </div>
      </div>

      {{-- Tab 2: Belts & Apparel Chart --}}
      <div id="sgTabContent-belts" class="sg-tab-content space-y-5 hidden">
        <div class="overflow-x-auto rounded-xl border border-stone-200/80">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-50/80 text-stone-500 font-semibold border-b border-stone-200/80 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 font-semibold">Belt Size</th>
                <th class="py-3 px-4 font-semibold">Waist (<span class="sg-unit-text">CM</span>)</th>
                <th class="py-3 px-4 font-semibold">Pants Size (Inches)</th>
                <th class="py-3 px-4 font-semibold">Strap Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgBeltsTableBody" class="divide-y divide-stone-150 font-normal">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Belt Fitting Advice --}}
        <div class="rounded-xl border border-stone-200/80 p-4 bg-white flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center shrink-0 text-stone-600">
            {{-- Minimalist Belt Vector Icon --}}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <rect x="3" y="8" width="18" height="8" rx="2" />
              <path d="M7 8v8M11 12h2" />
            </svg>
          </div>
          <div class="space-y-0.5 text-xs text-stone-600">
            <span class="font-bold text-stone-900 block">Sizing recommendation:</span>
            <p class="leading-relaxed text-stone-600">
              For a comfortable fit fastened at the center hole, choose a belt <strong>2 inches (5 cm) larger</strong> than your standard trouser waist size (e.g. Size 34 belt for Size 32 pants).
            </p>
          </div>
        </div>
      </div>

      {{-- Tab 3: Watches & Straps Chart --}}
      <div id="sgTabContent-watches" class="sg-tab-content space-y-5 hidden">
        <div class="overflow-x-auto rounded-xl border border-stone-200/80">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-50/80 text-stone-500 font-semibold border-b border-stone-200/80 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 font-semibold">Wrist Circumference</th>
                <th class="py-3 px-4 font-semibold">Case Diameter</th>
                <th class="py-3 px-4 font-semibold">Profile</th>
                <th class="py-3 px-4 font-semibold">Strap Width</th>
              </tr>
            </thead>
            <tbody id="sgWatchesTableBody" class="divide-y divide-stone-150 font-normal">
              {{-- Injected dynamically --}}
            </tbody>
          </table>
        </div>

        {{-- Watch Fitting Advice --}}
        <div class="rounded-xl border border-stone-200/80 p-4 bg-white flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center shrink-0 text-stone-600">
            {{-- Minimalist Watch Vector Icon --}}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="6" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3l2 2M9 3h6M9 21h6" />
            </svg>
          </div>
          <div class="space-y-0.5 text-xs text-stone-600">
            <span class="font-bold text-stone-900 block">Measuring your wrist:</span>
            <p class="leading-relaxed text-stone-600">
              Wrap a flexible tailor's tape around your wrist just below the wrist bone. 40mm–42mm case diameters represent the classic, versatile proportion for most average wrists (16–18 cm).
            </p>
          </div>
        </div>
      </div>

      {{-- Merchant Fitting Advice (If configured) --}}
      @if($customTip = setting('size_guide_custom_tip'))
        <div class="p-3.5 bg-stone-50 border border-stone-200/70 rounded-xl text-xs text-stone-700">
          <span class="font-semibold text-stone-900">Note:</span> {{ $customTip }}
        </div>
      @endif

    </div>

    {{-- Clean Minimalist Footer --}}
    <div class="px-6 py-4 sm:px-7 border-t border-stone-150 bg-stone-50/50 flex items-center justify-between shrink-0">
      <span class="text-xs text-stone-500">Questions? <a href="{{ route('contact') }}" class="text-stone-900 font-semibold underline underline-offset-2 hover:text-stone-600">Contact Support</a></span>
      <button type="button" data-close-size-guide class="px-5 py-2 rounded-lg bg-stone-900 hover:bg-stone-800 text-white font-medium text-xs transition-colors cursor-pointer">
        Close
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

  // Dynamic datasets
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

  function renderTables(highlightRowIndex = -1) {
    document.querySelectorAll('.sg-unit-text').forEach(el => el.textContent = currentUnit.toUpperCase());

    // 1. Render Shoes
    const shoesBody = document.getElementById('sgShoesTableBody');
    if (shoesBody) {
      shoesBody.innerHTML = shoesData.map((row, idx) => {
        const isMatch = idx === highlightRowIndex && currentTab === 'shoes';
        return `
          <tr data-row-idx="${idx}" class="sg-table-row transition-colors cursor-pointer ${isMatch ? 'bg-stone-100 font-bold' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/40')} hover:bg-stone-100/60">
            <td class="py-3 px-4 font-bold text-stone-900">${row.eu ?? ''}</td>
            <td class="py-3 px-4 text-stone-700">${row.usM ?? ''}</td>
            <td class="py-3 px-4 text-stone-700">${row.usW ?? ''}</td>
            <td class="py-3 px-4 text-stone-700">${row.uk ?? ''}</td>
            <td class="py-3 px-4 text-stone-900 font-medium">${formatVal(row.cm ?? 0)}</td>
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
          <tr data-row-idx="${idx}" class="sg-table-row transition-colors cursor-pointer ${isMatch ? 'bg-stone-100 font-bold' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/40')} hover:bg-stone-100/60">
            <td class="py-3 px-4 font-bold text-stone-900">${row.size ?? ''}</td>
            <td class="py-3 px-4 text-stone-900 font-medium">${formatVal(row.waistCm ?? 0)}</td>
            <td class="py-3 px-4 text-stone-700">${row.pants ?? ''}</td>
            <td class="py-3 px-4 text-stone-600">${formatVal(row.strapLengthCm ?? 0)}</td>
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

        return `
          <tr data-row-idx="${idx}" class="sg-table-row transition-colors cursor-pointer ${isMatch ? 'bg-stone-100 font-bold' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/40')} hover:bg-stone-100/60">
            <td class="py-3 px-4 font-semibold text-stone-900">${displayWrist}</td>
            <td class="py-3 px-4 font-bold text-stone-900">${row.caseSize ?? ''}</td>
            <td class="py-3 px-4 text-stone-700">${row.look ?? ''}</td>
            <td class="py-3 px-4 text-stone-600">${row.strap ?? ''}</td>
          </tr>
        `;
      }).join('');
    }

    // Click on row to select
    document.querySelectorAll('.sg-table-row').forEach(tr => {
      tr.addEventListener('click', function () {
        const idx = parseInt(this.getAttribute('data-row-idx'), 10);
        const input = document.getElementById('sgCalcInput');
        if (currentTab === 'shoes' && shoesData[idx]) {
          if (input) input.value = currentUnit === 'in' ? (parseFloat(shoesData[idx].cm) / 2.54).toFixed(1) : shoesData[idx].cm;
        } else if (currentTab === 'belts' && beltsData[idx]) {
          if (input) input.value = currentUnit === 'in' ? (parseFloat(beltsData[idx].waistCm) / 2.54).toFixed(1) : beltsData[idx].waistCm;
        } else if (currentTab === 'watches' && watchesData[idx]) {
          let firstVal = parseFloat(String(watchesData[idx].wristCm).split(' - ')[0]) || 16;
          if (input) input.value = currentUnit === 'in' ? (firstVal / 2.54).toFixed(1) : firstVal;
        }
        calculateFit();
      });
    });
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
    }, 200);
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

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal && !modal.classList.contains('opacity-0')) {
      closeSizeGuideModal();
    }
  });

  // Tab Switcher
  function switchTab(tabId) {
    currentTab = tabId;

    document.querySelectorAll('#sgTabList .sg-tab-btn').forEach(btn => {
      const isTarget = btn.getAttribute('data-sg-tab') === tabId;
      btn.classList.toggle('border-stone-900', isTarget);
      btn.classList.toggle('text-stone-900', isTarget);
      btn.classList.toggle('border-transparent', !isTarget);
      btn.classList.toggle('text-stone-500', !isTarget);
    });

    document.querySelectorAll('.sg-tab-content').forEach(content => {
      content.classList.toggle('hidden', content.id !== 'sgTabContent-' + tabId);
    });

    // Update fit input label
    const calcLabel = document.getElementById('sgCalcLabel');
    const calcInput = document.getElementById('sgCalcInput');
    const badge = document.getElementById('sgResultBadge');
    if (badge) badge.classList.add('hidden');

    if (calcLabel && calcInput) {
      calcInput.value = '';
      if (tabId === 'belts') {
        calcLabel.innerHTML = `Find my fit (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 86' : 'e.g. 34';
      } else if (tabId === 'watches') {
        calcLabel.innerHTML = `Find my fit (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 17.0' : 'e.g. 6.7';
      } else {
        calcLabel.innerHTML = `Find my fit (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 26.5' : 'e.g. 10.4';
      }
    }

    renderTables();
  }

  document.querySelectorAll('#sgTabList .sg-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      switchTab(this.getAttribute('data-sg-tab'));
    });
  });

  // Unit Switcher (CM / IN)
  const btnCm = document.getElementById('sgUnitCm');
  const btnIn = document.getElementById('sgUnitIn');

  function setUnit(unit) {
    currentUnit = unit;
    if (btnCm && btnIn) {
      if (unit === 'cm') {
        btnCm.className = 'px-2.5 py-1 rounded-md bg-white text-stone-900 shadow-2xs font-bold transition-all';
        btnIn.className = 'px-2.5 py-1 rounded-md text-stone-500 hover:text-stone-900 transition-all';
      } else {
        btnIn.className = 'px-2.5 py-1 rounded-md bg-white text-stone-900 shadow-2xs font-bold transition-all';
        btnCm.className = 'px-2.5 py-1 rounded-md text-stone-500 hover:text-stone-900 transition-all';
      }
    }
    switchTab(currentTab);
  }

  if (btnCm) btnCm.addEventListener('click', () => setUnit('cm'));
  if (btnIn) btnIn.addEventListener('click', () => setUnit('in'));

  // Fit Calculation Logic
  const calcInput = document.getElementById('sgCalcInput');
  const resultBadge = document.getElementById('sgResultBadge');
  const resultText = document.getElementById('sgResultText');

  function calculateFit() {
    if (!calcInput) return;
    const rawVal = parseFloat(calcInput.value);
    if (isNaN(rawVal) || rawVal <= 0) {
      if (resultBadge) resultBadge.classList.add('hidden');
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

      if (resultText && resultBadge) {
        resultText.textContent = `EU ${match.eu} / US Men ${match.usM}`;
        resultBadge.classList.remove('hidden');
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

      if (resultText && resultBadge) {
        resultText.textContent = `Size ${match.size} (Pants ${match.pants})`;
        resultBadge.classList.remove('hidden');
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

      if (resultText && resultBadge) {
        resultText.textContent = `${match.caseSize} (${match.look})`;
        resultBadge.classList.remove('hidden');
      }
      renderTables(matchIdx);
    }
  }

  if (calcInput) {
    calcInput.addEventListener('input', calculateFit);
  }

  // Initial render
  renderTables();
});
</script>
