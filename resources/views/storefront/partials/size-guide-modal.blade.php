{{-- Interactive Size Guide Modal Partial --}}
<div id="sizeGuideModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 hidden opacity-0 pointer-events-none transition-all duration-300">
  {{-- Backdrop --}}
  <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity" data-close-size-guide></div>

  {{-- Modal Dialog --}}
  <div class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh] border border-stone-100 transform scale-95 transition-transform duration-300" id="sizeGuideDialog">
    
    {{-- Header --}}
    <div class="px-6 py-5 bg-gradient-to-r from-stone-900 via-stone-800 to-stone-900 text-white flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-xl shadow-inner">
          📏
        </div>
        <div>
          <h2 class="text-lg sm:text-xl font-bold tracking-tight text-white flex items-center gap-2">
            Interactive Size Guide &amp; Chart
          </h2>
          <p class="text-xs text-stone-300 font-medium">Find your perfect fit across shoes, belts, and watches</p>
        </div>
      </div>

      <button type="button" data-close-size-guide class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-stone-300 hover:text-white flex items-center justify-center transition-colors focus:outline-none" aria-label="Close modal">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Tabs & Unit Controls Bar --}}
    <div class="px-6 py-3 bg-stone-50 border-b border-stone-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
      {{-- Category Tabs --}}
      <div class="flex items-center gap-1.5 p-1 bg-stone-200/60 rounded-xl w-full sm:w-auto overflow-x-auto no-scrollbar" id="sgTabList">
        <button type="button" data-sg-tab="shoes" class="sg-tab-btn active px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs bg-white text-brand-600">
          👟 Footwear (Shoes)
        </button>
        <button type="button" data-sg-tab="belts" class="sg-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-stone-600 hover:text-stone-900">
          🥋 Belts &amp; Apparel
        </button>
        <button type="button" data-sg-tab="watches" class="sg-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-stone-600 hover:text-stone-900">
          ⌚ Watches &amp; Straps
        </button>
      </div>

      {{-- Unit Switcher (CM / Inches) --}}
      <div class="flex items-center gap-2 self-end sm:self-auto">
        <span class="text-xs font-semibold text-stone-500">Unit:</span>
        <div class="inline-flex p-1 bg-stone-200/60 rounded-lg text-xs font-bold">
          <button type="button" id="sgUnitCm" class="px-2.5 py-1 rounded-md bg-brand-500 text-white shadow-2xs transition-all">CM</button>
          <button type="button" id="sgUnitIn" class="px-2.5 py-1 rounded-md text-stone-600 hover:text-stone-900 transition-all">Inches</button>
        </div>
      </div>
    </div>

    {{-- Modal Body (Scrollable) --}}
    <div class="p-6 overflow-y-auto space-y-6 flex-1 text-stone-800">

      {{-- "Find My Size" Interactive Calculator Card --}}
      <div class="bg-gradient-to-br from-brand-50/60 via-stone-50 to-amber-50/40 rounded-2xl border border-brand-500/20 p-4 sm:p-5 shadow-xs">
        <div class="flex items-center justify-between gap-2 mb-3">
          <div class="flex items-center gap-2">
            <span class="flex h-2.5 w-2.5 rounded-full bg-brand-500 animate-pulse"></span>
            <h3 class="text-sm font-bold text-stone-900 uppercase tracking-wider">Find My Size Calculator</h3>
          </div>
          <span class="text-[11px] font-semibold text-stone-500 bg-white/80 px-2.5 py-0.5 rounded-full border border-stone-200">Instant Estimate</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
          <div class="sm:col-span-7 space-y-1">
            <label id="sgCalcLabel" for="sgCalcInput" class="block text-xs font-semibold text-stone-600">Enter Foot Length (<span class="sg-unit-text">CM</span>):</label>
            <div class="relative">
              <input type="number" step="0.1" id="sgCalcInput" placeholder="e.g. 26.5" class="w-full pl-3 pr-14 py-2 bg-white border border-stone-300 rounded-xl text-sm font-bold text-stone-900 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all" />
              <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400 uppercase sg-unit-text">CM</span>
            </div>
          </div>

          <div class="sm:col-span-5">
            <button type="button" id="sgCalcBtn" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-2 px-4 rounded-xl text-xs sm:text-sm shadow-xs transition-colors flex items-center justify-center gap-1.5">
              <span>Calculate My Size</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
          </div>
        </div>

        {{-- Recommendation Output Box --}}
        <div id="sgResultBox" class="hidden mt-4 p-3.5 bg-white rounded-xl border border-brand-500/30 text-xs sm:text-sm text-stone-800 flex items-center justify-between gap-3 shadow-2xs">
          <div class="flex items-center gap-2">
            <span class="text-base">🎯</span>
            <div>
              <span class="text-stone-500 font-medium">Recommended Fit:</span>
              <span id="sgResultText" class="font-extrabold text-brand-600 ml-1"></span>
            </div>
          </div>
          <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Match Highlighted</span>
        </div>
      </div>

      {{-- Tab 1: Shoes / Footwear --}}
      <div id="sgTabContent-shoes" class="sg-tab-content space-y-4">
        <div class="flex items-center justify-between">
          <h4 class="font-bold text-sm text-stone-900 flex items-center gap-2">
            <span>👟</span> Shoes &amp; Footwear Size Conversion Chart
          </h4>
          <span class="text-xs text-stone-500">Standard International Sizing</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100 text-stone-700 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">EU Size</th>
                <th class="py-3 px-4">US (Men)</th>
                <th class="py-3 px-4">US (Women)</th>
                <th class="py-3 px-4">UK Size</th>
                <th class="py-3 px-4">Foot Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgShoesTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Dynamic JS content --}}
            </tbody>
          </table>
        </div>

        {{-- Measuring Tip Card --}}
        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/80 text-xs text-amber-900 flex gap-3 items-start">
          <span class="text-lg leading-none shrink-0">💡</span>
          <div class="space-y-1">
            <span class="font-bold text-amber-950 block">How to Measure Your Foot Length:</span>
            <p class="leading-relaxed text-amber-900/90">
              Place a piece of paper against a wall. Stand straight with your heel lightly touching the wall, mark the tip of your longest toe, and measure the distance with a ruler. If between sizes, choose the larger size for comfort.
            </p>
          </div>
        </div>
      </div>

      {{-- Tab 2: Belts & Apparel --}}
      <div id="sgTabContent-belts" class="sg-tab-content space-y-4 hidden">
        <div class="flex items-center justify-between">
          <h4 class="font-bold text-sm text-stone-900 flex items-center gap-2">
            <span>🥋</span> Leather Belts &amp; Apparel Sizing Guide
          </h4>
          <span class="text-xs text-stone-500">Waist &amp; Belt Length Standard</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100 text-stone-700 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Belt Size</th>
                <th class="py-3 px-4">Waist Size (<span class="sg-unit-text">CM</span>)</th>
                <th class="py-3 px-4">Pants Size (Inches)</th>
                <th class="py-3 px-4">Total Strap Length (<span class="sg-unit-text">CM</span>)</th>
              </tr>
            </thead>
            <tbody id="sgBeltsTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Dynamic JS content --}}
            </tbody>
          </table>
        </div>

        <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 text-xs text-blue-900 flex gap-3 items-start">
          <span class="text-lg leading-none shrink-0">ℹ️</span>
          <div class="space-y-1">
            <span class="font-bold text-blue-950 block">Belt Fitting Rule:</span>
            <p class="leading-relaxed text-blue-900/90">
              Order your belt size 2 inches (5 cm) larger than your current pants waist size. For example, if you wear size 32 pants, order a size 34 belt so it fastens comfortably on the center hole.
            </p>
          </div>
        </div>
      </div>

      {{-- Tab 3: Watches & Straps --}}
      <div id="sgTabContent-watches" class="sg-tab-content space-y-4 hidden">
        <div class="flex items-center justify-between">
          <h4 class="font-bold text-sm text-stone-900 flex items-center gap-2">
            <span>⌚</span> Watch Case Diameter &amp; Wrist Sizing Guide
          </h4>
          <span class="text-xs text-stone-500">Wrist Proportion Standard</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 shadow-2xs">
          <table class="w-full text-xs sm:text-sm text-left border-collapse">
            <thead>
              <tr class="bg-stone-100 text-stone-700 font-extrabold border-b border-stone-200 uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4">Wrist Circumference</th>
                <th class="py-3 px-4">Ideal Case Diameter</th>
                <th class="py-3 px-4">Recommended Style</th>
                <th class="py-3 px-4">Strap Width</th>
              </tr>
            </thead>
            <tbody id="sgWatchesTableBody" class="divide-y divide-stone-100 font-medium">
              {{-- Dynamic JS content --}}
            </tbody>
          </table>
        </div>

        <div class="p-4 rounded-2xl bg-stone-100 border border-stone-200 text-xs text-stone-700 flex gap-3 items-start">
          <span class="text-lg leading-none shrink-0">📏</span>
          <div class="space-y-1">
            <span class="font-bold text-stone-900 block">Measuring Wrist Size:</span>
            <p class="leading-relaxed">
              Wrap a flexible measuring tape or strip of paper around your wrist right below the wrist bone. Mark the overlap point and measure against a ruler.
            </p>
          </div>
        </div>
      </div>

      @if($customTip = setting('size_guide_custom_tip'))
        <div class="p-3.5 bg-brand-50 border border-brand-200 rounded-xl text-xs text-brand-900 flex items-center gap-2">
          <span class="text-base">📢</span>
          <div>
            <span class="font-bold">Store Fit Advice:</span> {{ $customTip }}
          </div>
        </div>
      @endif

    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 bg-stone-50 border-t border-stone-200/80 flex items-center justify-between shrink-0">
      <span class="text-xs text-stone-500 font-medium">Need custom assistance? <a href="{{ route('contact') }}" class="text-brand-600 underline font-bold">Contact Support</a></span>
      <button type="button" data-close-size-guide class="px-5 py-2 rounded-xl bg-stone-900 text-white hover:bg-stone-800 font-bold text-xs shadow-xs transition-colors">
        Done
      </button>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('sizeGuideModal');
  const dialog = document.getElementById('sizeGuideDialog');
  let currentUnit = '{{ setting("size_guide_default_unit", "cm") }}';
  let currentTab = 'shoes';

  // Size data definitions in CM
  const shoesData = [
    { eu: '39', usM: '6.5', usW: '8.0', uk: '5.5', cm: 24.5 },
    { eu: '40', usM: '7.5', usW: '9.0', uk: '6.5', cm: 25.0 },
    { eu: '41', usM: '8.0', usW: '9.5', uk: '7.0', cm: 25.8 },
    { eu: '42', usM: '8.5', usW: '10.0', uk: '7.5', cm: 26.5 },
    { eu: '43', usM: '9.5', usW: '11.0', uk: '8.5', cm: 27.3 },
    { eu: '44', usM: '10.5', usW: '12.0', uk: '9.5', cm: 28.0 },
    { eu: '45', usM: '11.5', usW: '13.0', uk: '10.5', cm: 28.8 },
    { eu: '46', usM: '12.0', usW: '13.5', uk: '11.0', cm: 29.5 }
  ];

  const beltsData = [
    { size: '32 (S)', waistCm: 81, pants: '28 - 30', strapLengthCm: 95 },
    { size: '34 (M)', waistCm: 86, pants: '30 - 32', strapLengthCm: 100 },
    { size: '36 (L)', waistCm: 91, pants: '32 - 34', strapLengthCm: 105 },
    { size: '38 (XL)', waistCm: 97, pants: '34 - 36', strapLengthCm: 110 },
    { size: '40 (XXL)', waistCm: 102, pants: '36 - 38', strapLengthCm: 115 }
  ];

  const watchesData = [
    { wristCm: '14.0 - 16.0', caseSize: '36mm - 38mm', look: 'Classic / Minimalist', strap: '18mm - 20mm' },
    { wristCm: '16.0 - 18.0', caseSize: '40mm - 42mm', look: 'Standard / Versatile', strap: '20mm - 22mm' },
    { wristCm: '18.0 - 20.0+', caseSize: '44mm - 46mm', look: 'Bold / Oversized', strap: '22mm - 24mm' }
  ];

  function formatVal(valCm) {
    if (currentUnit === 'in') {
      return (valCm / 2.54).toFixed(1) + '"';
    }
    return valCm.toFixed(1) + ' cm';
  }

  function renderTables(highlightRowIndex = -1) {
    // Update Unit labels
    document.querySelectorAll('.sg-unit-text').forEach(el => el.textContent = currentUnit.toUpperCase());

    // 1. Render Shoes
    const shoesBody = document.getElementById('sgShoesTableBody');
    if (shoesBody) {
      shoesBody.innerHTML = shoesData.map((row, idx) => `
        <tr class="transition-colors ${idx === highlightRowIndex && currentTab === 'shoes' ? 'bg-brand-100/60 font-bold text-brand-900' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/60')} hover:bg-brand-50">
          <td class="py-2.5 px-4 font-bold text-brand-600">${row.eu}</td>
          <td class="py-2.5 px-4">${row.usM}</td>
          <td class="py-2.5 px-4">${row.usW}</td>
          <td class="py-2.5 px-4">${row.uk}</td>
          <td class="py-2.5 px-4 font-semibold">${formatVal(row.cm)}</td>
        </tr>
      `).join('');
    }

    // 2. Render Belts
    const beltsBody = document.getElementById('sgBeltsTableBody');
    if (beltsBody) {
      beltsBody.innerHTML = beltsData.map((row, idx) => `
        <tr class="transition-colors ${idx === highlightRowIndex && currentTab === 'belts' ? 'bg-brand-100/60 font-bold text-brand-900' : (idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/60')} hover:bg-brand-50">
          <td class="py-2.5 px-4 font-bold text-brand-600">${row.size}</td>
          <td class="py-2.5 px-4 font-semibold">${formatVal(row.waistCm)}</td>
          <td class="py-2.5 px-4">${row.pants}</td>
          <td class="py-2.5 px-4">${formatVal(row.strapLengthCm)}</td>
        </tr>
      `).join('');
    }

    // 3. Render Watches
    const watchesBody = document.getElementById('sgWatchesTableBody');
    if (watchesBody) {
      watchesBody.innerHTML = watchesData.map((row, idx) => {
        let displayWrist = row.wristCm;
        if (currentUnit === 'in') {
          displayWrist = row.wristCm.split(' - ').map(v => (parseFloat(v) / 2.54).toFixed(1)).join(' - ') + '"';
        } else {
          displayWrist = row.wristCm + ' cm';
        }
        return `
          <tr class="transition-colors ${idx % 2 === 0 ? 'bg-white' : 'bg-stone-50/60'} hover:bg-brand-50">
            <td class="py-2.5 px-4 font-bold text-brand-600">${displayWrist}</td>
            <td class="py-2.5 px-4 font-bold text-stone-900">${row.caseSize}</td>
            <td class="py-2.5 px-4">${row.look}</td>
            <td class="py-2.5 px-4 font-medium text-stone-600">${row.strap}</td>
          </tr>
        `;
      }).join('');
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
    document.querySelectorAll('#sgTabList .sg-tab-btn').forEach(btn => {
      const isTarget = btn.getAttribute('data-sg-tab') === tabId;
      btn.classList.toggle('bg-white', isTarget);
      btn.classList.toggle('text-brand-600', isTarget);
      btn.classList.toggle('shadow-xs', isTarget);
      btn.classList.toggle('text-stone-600', !isTarget);
    });

    document.querySelectorAll('.sg-tab-content').forEach(content => {
      content.classList.toggle('hidden', content.id !== 'sgTabContent-' + tabId);
    });

    // Update calculator label based on tab
    const calcLabel = document.getElementById('sgCalcLabel');
    const calcInput = document.getElementById('sgCalcInput');
    if (calcLabel && calcInput) {
      if (tabId === 'belts') {
        calcLabel.innerHTML = `Enter Waist Size (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
        calcInput.placeholder = currentUnit === 'cm' ? 'e.g. 86' : 'e.g. 34';
      } else {
        calcLabel.innerHTML = `Enter Foot Length (<span class="sg-unit-text">${currentUnit.toUpperCase()}</span>):`;
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

  // Unit Switcher (CM / Inches)
  const btnCm = document.getElementById('sgUnitCm');
  const btnIn = document.getElementById('sgUnitIn');

  function setUnit(unit) {
    currentUnit = unit;
    if (btnCm && btnIn) {
      if (unit === 'cm') {
        btnCm.className = 'px-2.5 py-1 rounded-md bg-brand-500 text-white shadow-2xs transition-all';
        btnIn.className = 'px-2.5 py-1 rounded-md text-stone-600 hover:text-stone-900 transition-all';
      } else {
        btnIn.className = 'px-2.5 py-1 rounded-md bg-brand-500 text-white shadow-2xs transition-all';
        btnCm.className = 'px-2.5 py-1 rounded-md text-stone-600 hover:text-stone-900 transition-all';
      }
    }
    switchTab(currentTab);
  }

  if (btnCm) btnCm.addEventListener('click', () => setUnit('cm'));
  if (btnIn) btnIn.addEventListener('click', () => setUnit('in'));

  // Size Calculator
  const calcBtn = document.getElementById('sgCalcBtn');
  const calcInput = document.getElementById('sgCalcInput');
  const resultBox = document.getElementById('sgResultBox');
  const resultText = document.getElementById('sgResultText');

  if (calcBtn && calcInput) {
    calcBtn.addEventListener('click', function () {
      const rawVal = parseFloat(calcInput.value);
      if (isNaN(rawVal) || rawVal <= 0) return;

      let valCm = rawVal;
      if (currentUnit === 'in') {
        valCm = rawVal * 2.54;
      }

      if (currentTab === 'shoes') {
        let match = shoesData[0];
        let matchIdx = 0;
        for (let i = 0; i < shoesData.length; i++) {
          if (valCm <= shoesData[i].cm + 0.3) {
            match = shoesData[i];
            matchIdx = i;
            break;
          }
        }
        if (valCm > shoesData[shoesData.length - 1].cm + 0.3) {
          match = shoesData[shoesData.length - 1];
          matchIdx = shoesData.length - 1;
        }

        if (resultText && resultBox) {
          resultText.textContent = `EU ${match.eu} (US Men ${match.usM} / UK ${match.uk})`;
          resultBox.classList.remove('hidden');
        }
        renderTables(matchIdx);

      } else if (currentTab === 'belts') {
        let match = beltsData[0];
        let matchIdx = 0;
        for (let i = 0; i < beltsData.length; i++) {
          if (valCm <= beltsData[i].waistCm + 3) {
            match = beltsData[i];
            matchIdx = i;
            break;
          }
        }
        if (valCm > beltsData[beltsData.length - 1].waistCm + 3) {
          match = beltsData[beltsData.length - 1];
          matchIdx = beltsData.length - 1;
        }

        if (resultText && resultBox) {
          resultText.textContent = `Belt Size ${match.size} (Pants ${match.pants})`;
          resultBox.classList.remove('hidden');
        }
        renderTables(matchIdx);
      }
    });
  }

  // Initial render
  renderTables();
});
</script>
