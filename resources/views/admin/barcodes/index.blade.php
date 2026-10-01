@extends('layouts.admin')
@section('title', 'Barcode Label Generator')

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 rounded-xl bg-indigo-50 text-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
          </span>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Barcode Label Generator</h1>
            <p class="text-xs sm:text-sm text-gray-500">Generate & print scannable barcode stickers for products and packaging</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.pos.index') }}" class="px-4 py-2.5 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition-colors flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          Open POS Counter
        </a>
      </div>
    </div>
  </div>

  <form action="{{ route('admin.barcodes.print') }}" method="POST" target="_blank" id="barcodePrintForm">
    @csrf

    {{-- Print Settings Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-5 mb-6 space-y-4">
      <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Sticker / Paper Format</label>
            <select name="format" class="w-full text-xs font-semibold px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
              <option value="thermal_50x30">Thermal Roll (50mm × 30mm) - Standard</option>
              <option value="thermal_40x25">Thermal Roll (40mm × 25mm) - Compact</option>
              <option value="a4_3col">A4 Sheet (3 Columns - 24 per page)</option>
              <option value="a4_4col">A4 Sheet (4 Columns - 40 per page)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Store Branding</label>
            <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
              <input type="checkbox" name="show_store_name" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
              <span class="text-xs font-semibold text-gray-700">Print Store Name</span>
            </label>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Price Tag</label>
            <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
              <input type="checkbox" name="show_price" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
              <span class="text-xs font-semibold text-gray-700">Print Selling Price (৳)</span>
            </label>
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2 lg:pt-0 shrink-0">
          <button type="submit" id="printSubmitBtn" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-primary hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Selected Stickers (<span id="selectedCountDisplay">0</span>)
          </button>
        </div>

      </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-4">
      <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="toggleSelectAll(true)" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Select All On Page</button>
        <button type="button" onclick="toggleSelectAll(false)" class="px-3 py-1.5 text-xs font-bold text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Deselect All</button>
      </div>

      <div class="flex items-center gap-2">
        <input type="text" id="filterSearch" placeholder="Quick search in table..." onkeyup="filterTable(this.value)" class="text-xs px-3.5 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 w-full sm:w-64">
      </div>
    </div>

    {{-- Table of Products --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="productsBarcodeTable">
          <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
            <tr>
              <th class="py-3.5 px-4 w-12 text-center">
                <input type="checkbox" id="masterCheckbox" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
              </th>
              <th class="py-3.5 px-4">Product Info</th>
              <th class="py-3.5 px-4">SKU / Code</th>
              <th class="py-3.5 px-4">Barcode Preview</th>
              <th class="py-3.5 px-4 text-right">Price</th>
              <th class="py-3.5 px-4 text-center w-32">Stickers to Print</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($products as $idx => $p)
              @php
                $barcode = $p->getBarcode();
                $price = $p->sale_price ?: $p->regular_price;
              @endphp
              <tr class="hover:bg-gray-50/80 transition-colors barcode-row" data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . $barcode) }}">
                <td class="py-3 px-4 text-center">
                  <input type="checkbox" name="items[{{ $idx }}][selected]" value="1" class="item-checkbox w-4 h-4 rounded text-brand-600 focus:ring-brand-500" onchange="updateSelectedCount()">
                  <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $p->id }}">
                  <input type="hidden" name="items[{{ $idx }}][type]" value="product">
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img src="{{ $p->imageUrl() }}" alt="" loading="lazy" onerror="this.onerror=null;this.removeAttribute('src')" class="w-10 h-10 rounded-lg object-cover bg-gray-100 shrink-0 border border-gray-200">
                    <div>
                      <div class="font-bold text-gray-900 text-sm">{{ $p->name }}</div>
                      <div class="text-[11px] text-gray-500">{{ $p->category?->name ?? 'Uncategorized' }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <span class="font-mono text-xs font-bold text-gray-700 bg-gray-100 px-2 py-1 rounded-md">{{ $p->sku ?: 'No SKU' }}</span>
                </td>
                <td class="py-3 px-4">
                  <div class="inline-block bg-white p-1 rounded border border-gray-200">
                    <svg class="barcode-svg" data-barcode="{{ $barcode }}" jsbarcode-format="CODE128" jsbarcode-value="{{ $barcode }}" jsbarcode-textmargin="0" jsbarcode-fontoptions="bold" jsbarcode-width="1.2" jsbarcode-height="26" jsbarcode-fontsize="10"></svg>
                  </div>
                </td>
                <td class="py-3 px-4 text-right">
                  <span class="font-extrabold text-gray-900 text-sm">৳{{ number_format($price, 2) }}</span>
                </td>
                <td class="py-3 px-4 text-center">
                  <input type="number" name="items[{{ $idx }}][qty]" value="1" min="1" max="200" class="w-20 px-2.5 py-1.5 text-center text-xs font-bold text-gray-800 border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500">
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="py-8 text-center text-gray-400">No products found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-4 border-t border-gray-100">
        {{ $products->links() }}
      </div>
    </div>
  </form>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    JsBarcode(".barcode-svg").init();
    updateSelectedCount();
  });

  function updateSelectedCount() {
    let checkedCount = 0;
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    allCheckboxes.forEach(cb => {
      const row = cb.closest('tr');
      if (cb.checked) {
        checkedCount++;
        row.classList.add('bg-indigo-50/50');
      } else {
        row.classList.remove('bg-indigo-50/50');
      }
    });
    const countDisplay = document.getElementById('selectedCountDisplay');
    if (countDisplay) {
      countDisplay.innerText = checkedCount;
    }
    const master = document.getElementById('masterCheckbox');
    if (master && allCheckboxes.length > 0) {
      master.checked = (checkedCount === allCheckboxes.length);
    }
  }

  function toggleSelectAll(status) {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
      // Only select visible rows
      if (cb.closest('tr').style.display !== 'none') {
        cb.checked = status;
      }
    });
    const master = document.getElementById('masterCheckbox');
    if (master) master.checked = status;
    updateSelectedCount();
  }

  function filterTable(val) {
    const q = val.toLowerCase().trim();
    document.querySelectorAll('.barcode-row').forEach(row => {
      const text = row.getAttribute('data-search') || '';
      row.style.display = text.includes(q) ? '' : 'none';
    });
  }

  document.getElementById('barcodePrintForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
      e.preventDefault();
      alert('Please check at least one product to print barcodes.');
      return false;
    }

    // Disable inputs in unselected rows so only checked rows are submitted in POST payload
    document.querySelectorAll('.barcode-row').forEach(row => {
      const cb = row.querySelector('.item-checkbox');
      if (!cb || !cb.checked) {
        row.querySelectorAll('input').forEach(inp => inp.disabled = true);
      }
    });

    // Re-enable inputs after a short delay so user can continue using the form without reloading
    setTimeout(() => {
      document.querySelectorAll('.barcode-row input').forEach(inp => inp.disabled = false);
    }, 1200);
  });
</script>
@endpush
@endsection
