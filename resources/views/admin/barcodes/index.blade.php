@extends('layouts.admin')
@section('title', 'Barcodes')
@section('subtitle', 'Print scannable barcode stickers for products and packaging.')

@section('page-actions')
  <a href="{{ route('admin.pos.index') }}" class="pill-btn">
    Open POS
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="14" height="18" x="5" y="3" rx="2"/><path d="M9 7h6M9 11h.01M12 11h.01M15 11h.01M9 14h.01M12 14h.01M15 14h.01M9 17h.01M12 17h3"/></svg></span>
  </a>
@endsection

@section('content')
<div class="space-y-4">
  <form action="{{ route('admin.barcodes.print') }}" method="POST" target="_blank" id="barcodePrintForm" class="space-y-4">
    @csrf

    {{-- Print settings --}}
    <section class="panel p-4 sm:p-5">
      <div class="flex flex-col lg:flex-row lg:items-end gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
          <div>
            <label class="lbl" for="barcodeFormat">Sticker size</label>
            <select id="barcodeFormat" name="format" class="w-full h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm text-gray-800 focus:bg-white focus:border-gray-200 outline-none">
              <option value="thermal_50x30">Thermal Roll (50mm × 30mm) - Standard</option>
              <option value="thermal_40x25">Thermal Roll (40mm × 25mm) - Compact</option>
              <option value="a4_3col">A4 Sheet (3 Columns - 24 per page)</option>
              <option value="a4_4col">A4 Sheet (4 Columns - 40 per page)</option>
            </select>
          </div>
          <label class="flex items-center gap-2.5 h-10 px-4 rounded-full bg-gray-100 cursor-pointer sm:self-end">
            <input type="checkbox" name="show_store_name" value="1" checked class="w-4 h-4 rounded">
            <span class="text-[13px] text-gray-800">Print store name</span>
          </label>
          <label class="flex items-center gap-2.5 h-10 px-4 rounded-full bg-gray-100 cursor-pointer sm:self-end">
            <input type="checkbox" name="show_price" value="1" checked class="w-4 h-4 rounded">
            <span class="text-[13px] text-gray-800">Print selling price</span>
          </label>
        </div>
        <button type="submit" id="printSubmitBtn" class="h-10 px-5 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-2 cursor-pointer shrink-0" style="background: var(--brand-dark);">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
          <span>Print selected (<span id="selectedCountDisplay">0</span>)</span>
        </button>
      </div>
    </section>

    {{-- Products --}}
    <div class="card overflow-hidden">
      <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
        <label class="relative flex-1 sm:max-w-sm">
          <span class="sr-only">Search products</span>
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" id="filterSearch" placeholder="Search this page" onkeyup="filterTable(this.value)" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition">
        </label>
        <div class="flex items-center gap-1.5">
          <button type="button" onclick="toggleSelectAll(true)" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Select all</button>
          <button type="button" onclick="toggleSelectAll(false)" class="h-9 px-3.5 rounded-full text-gray-600 hover:bg-gray-100 text-[13px] font-medium">Clear</button>
        </div>
      </div>

      {{-- One table: rows stack into cards on phones (inputs must not be duplicated) --}}
      <div class="md:overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse" id="productsBarcodeTable">
          <thead class="hidden md:table-header-group">
            <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
              <th class="py-3 px-4 w-12 text-center">
                <input type="checkbox" id="masterCheckbox" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded" aria-label="Select all">
              </th>
              <th class="py-3 px-4">Product</th>
              <th class="py-3 px-4">SKU</th>
              <th class="py-3 px-4">Barcode</th>
              <th class="py-3 px-4 text-right">Price</th>
              <th class="py-3 px-4 text-center w-28">Stickers</th>
            </tr>
          </thead>
          <tbody class="block md:table-row-group px-3 pb-3 md:p-0 space-y-2.5 md:space-y-0 md:divide-y md:divide-gray-100">
            @forelse($products as $idx => $p)
              @php
                $barcode = $p->getBarcode();
                $price = $p->sale_price ?: $p->regular_price;
              @endphp
              <tr class="barcode-row grid grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-2.5 rounded-2xl bg-gray-50/80 p-3.5 md:table-row md:rounded-none md:bg-transparent md:p-0 hover:bg-gray-50/70 transition-colors" data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . $barcode) }}">
                <td class="row-start-1 col-start-1 md:py-3 md:px-4 text-center">
                  <input type="checkbox" name="items[{{ $idx }}][selected]" value="1" class="item-checkbox w-4 h-4 rounded" onchange="updateSelectedCount()" aria-label="Select {{ $p->name }}">
                  <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $p->id }}">
                  <input type="hidden" name="items[{{ $idx }}][type]" value="product">
                </td>
                <td class="row-start-1 col-start-2 min-w-0 md:py-3 md:px-4">
                  <div class="flex items-center gap-3">
                    <img src="{{ $p->imageUrl() }}" alt="" loading="lazy" onerror="this.onerror=null;this.removeAttribute('src')" class="w-10 h-10 rounded-xl object-cover bg-gray-100 shrink-0">
                    <div class="min-w-0">
                      <div class="font-semibold text-gray-900 truncate">{{ $p->name }}</div>
                      <div class="text-[11px] text-gray-500 truncate">{{ $p->category?->name ?? 'Uncategorized' }}<span class="md:hidden"> · <span class="font-mono">{{ $p->sku ?: 'No SKU' }}</span></span></div>
                    </div>
                  </div>
                </td>
                <td class="hidden md:table-cell md:py-3 md:px-4">
                  <span class="font-mono text-xs text-gray-700">{{ $p->sku ?: 'No SKU' }}</span>
                </td>
                <td class="row-start-2 col-start-1 col-span-2 md:py-3 md:px-4 overflow-hidden">
                  <div class="inline-block bg-white p-1 rounded-lg ring-1 ring-gray-100 max-w-full">
                    <svg class="barcode-svg max-w-full h-auto" data-barcode="{{ $barcode }}" jsbarcode-format="CODE128" jsbarcode-value="{{ $barcode }}" jsbarcode-textmargin="0" jsbarcode-fontoptions="bold" jsbarcode-width="1.2" jsbarcode-height="26" jsbarcode-fontsize="10"></svg>
                  </div>
                </td>
                <td class="row-start-1 col-start-3 text-right md:py-3 md:px-4 whitespace-nowrap">
                  <span class="font-semibold text-gray-900 tabular-nums">৳{{ number_format($price, 2) }}</span>
                </td>
                <td class="row-start-2 col-start-3 md:py-3 md:px-4 text-center">
                  <input type="number" name="items[{{ $idx }}][qty]" value="1" min="1" max="200" aria-label="Stickers for {{ $p->name }}" class="w-16 md:w-20 h-8 px-2 text-center text-xs font-semibold tabular-nums text-gray-800 bg-white border border-gray-200 rounded-full focus:outline-none">
                </td>
              </tr>
            @empty
              <tr class="block md:table-row">
                <td colspan="6" class="block md:table-cell py-12 text-center text-gray-500 text-sm">No products found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($products->hasPages())
        <div class="p-3.5 sm:p-4 border-t border-gray-100">
          {{ $products->links() }}
        </div>
      @endif
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
