@extends('layouts.admin')

@section('title', 'Size Guide Management')
@section('subtitle', 'Size chart shown next to size options on product pages.')

@section('page-actions')
  <button type="button" onclick="openSizeGuideModal('shoes')" class="pill-btn cursor-pointer">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
    Preview
  </button>
  <a href="{{ route('shop') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@section('content')
<div class="space-y-4 max-w-5xl">

  {{-- Settings & Chart Editor Form --}}
  <form id="sizeGuideForm" action="{{ route('admin.size-guide.update') }}" method="POST" class="flex flex-col gap-4">
    @csrf
    @method('PUT')

    {{-- Hidden JSON inputs for serialized table data --}}
    <input type="hidden" name="shoes_data" id="shoesDataInput">
    <input type="hidden" name="belts_data" id="beltsDataInput">
    <input type="hidden" name="watches_data" id="watchesDataInput">

    {{-- Display settings --}}
    <section class="panel p-4 sm:p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Display settings</h2>
          <p class="text-xs text-gray-500 mt-0.5">Turn the guide on or off and pick the default unit.</p>
        </div>
        @if($settings['size_guide_enabled'] === '1')
          <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold shrink-0">On</span>
        @else
          <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold shrink-0">Off</span>
        @endif
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="lbl" for="size_guide_enabled">Size guide on product pages</label>
          <select name="size_guide_enabled" id="size_guide_enabled" class="inp">
            <option value="1" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '1')>Enabled (show the size guide link)</option>
            <option value="0" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '0')>Disabled (hide it everywhere)</option>
          </select>
          <p class="text-[11px] text-gray-500 mt-1">Shows a size guide link beside the product size options.</p>
        </div>

        <div>
          <label class="lbl" for="size_guide_default_unit">Default unit</label>
          <select name="size_guide_default_unit" id="size_guide_default_unit" class="inp">
            <option value="cm" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'cm')>Centimeters (CM)</option>
            <option value="in" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'in')>Inches (IN)</option>
          </select>
          <p class="text-[11px] text-gray-500 mt-1">Customers can switch units inside the guide.</p>
        </div>
      </div>

      <div>
        <label for="size_guide_custom_tip" class="lbl">Fit advice (optional)</label>
        <textarea id="size_guide_custom_tip" name="size_guide_custom_tip" rows="2" placeholder="e.g. Our footwear runs true to size. If you have wider feet, we recommend picking 1 size up." class="inp">{{ old('size_guide_custom_tip', $settings['size_guide_custom_tip']) }}</textarea>
        <p class="text-[11px] text-gray-500 mt-1">Shown at the bottom of the size guide as a tip from your store.</p>
      </div>
    </section>

    {{-- Size charts --}}
    <section class="panel overflow-hidden">
      <div class="p-4 sm:p-5 pb-0 sm:pb-0 space-y-3">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Size charts</h2>
          <p class="text-xs text-gray-500 mt-0.5">Edit values, add sizes or remove rows you do not use.</p>
        </div>

        <nav class="-mx-4 sm:mx-0 px-4 sm:px-0 overflow-x-auto no-scrollbar">
          <div class="inline-flex items-center gap-1 p-1 rounded-full bg-gray-100 whitespace-nowrap" id="editorTabNav">
            <button type="button" onclick="switchEditorTab('shoes')" id="tabBtnShoes" class="h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors bg-white text-gray-900 shadow-sm">Shoes</button>
            <button type="button" onclick="switchEditorTab('belts')" id="tabBtnBelts" class="h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:text-gray-900">Belts and apparel</button>
            <button type="button" onclick="switchEditorTab('watches')" id="tabBtnWatches" class="h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:text-gray-900">Watches and straps</button>
          </div>
        </nav>
      </div>

      {{-- Tab 1: Shoes Table Editor --}}
      <div id="editorTabContent-shoes" class="p-4 sm:p-5 space-y-3">
        <div class="overflow-x-auto rounded-xl ring-1 ring-gray-100" style="contain: paint;">
          <table class="w-full text-left text-xs border-collapse" id="shoesTable">
            <thead class="bg-gray-50/80 text-gray-500 font-medium border-b border-gray-100">
              <tr>
                <th class="py-2.5 px-3 whitespace-nowrap">EU</th>
                <th class="py-2.5 px-3 whitespace-nowrap">US Men</th>
                <th class="py-2.5 px-3 whitespace-nowrap">US Women</th>
                <th class="py-2.5 px-3 whitespace-nowrap">UK</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Foot length (cm)</th>
                <th class="py-2.5 px-3 text-right"><span class="sr-only">Action</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="shoesTableBody">
              @foreach($shoesData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['eu'] ?? '' }}" class="shoe-eu w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['usM'] ?? '' }}" class="shoe-usM w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none"></td>
                  <td class="p-2"><input type="text" value="{{ $row['usW'] ?? '' }}" class="shoe-usW w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none"></td>
                  <td class="p-2"><input type="text" value="{{ $row['uk'] ?? '' }}" class="shoe-uk w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none"></td>
                  <td class="p-2"><input type="number" step="0.1" value="{{ $row['cm'] ?? '' }}" class="shoe-cm w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" required></td>
                  <td class="p-2 text-right">
                    <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete size row">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <button type="button" onclick="addShoeRow()" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add shoe size</span>
        </button>
      </div>

      {{-- Tab 2: Belts Table Editor --}}
      <div id="editorTabContent-belts" class="p-4 sm:p-5 space-y-3 hidden">
        <div class="overflow-x-auto rounded-xl ring-1 ring-gray-100" style="contain: paint;">
          <table class="w-full text-left text-xs border-collapse" id="beltsTable">
            <thead class="bg-gray-50/80 text-gray-500 font-medium border-b border-gray-100">
              <tr>
                <th class="py-2.5 px-3 whitespace-nowrap">Size</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Waist (cm)</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Pants size</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Strap length (cm)</th>
                <th class="py-2.5 px-3 text-right"><span class="sr-only">Action</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="beltsTableBody">
              @foreach($beltsData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['size'] ?? '' }}" class="belt-size w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" required></td>
                  <td class="p-2"><input type="number" step="0.5" value="{{ $row['waistCm'] ?? '' }}" class="belt-waist w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['pants'] ?? '' }}" class="belt-pants w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none"></td>
                  <td class="p-2"><input type="number" step="0.5" value="{{ $row['strapLengthCm'] ?? '' }}" class="belt-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" required></td>
                  <td class="p-2 text-right">
                    <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete belt row">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <button type="button" onclick="addBeltRow()" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add belt size</span>
        </button>
      </div>

      {{-- Tab 3: Watches Table Editor --}}
      <div id="editorTabContent-watches" class="p-4 sm:p-5 space-y-3 hidden">
        <div class="overflow-x-auto rounded-xl ring-1 ring-gray-100" style="contain: paint;">
          <table class="w-full text-left text-xs border-collapse" id="watchesTable">
            <thead class="bg-gray-50/80 text-gray-500 font-medium border-b border-gray-100">
              <tr>
                <th class="py-2.5 px-3 whitespace-nowrap">Wrist (cm)</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Case size</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Look</th>
                <th class="py-2.5 px-3 whitespace-nowrap">Strap width</th>
                <th class="py-2.5 px-3 text-right"><span class="sr-only">Action</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="watchesTableBody">
              @foreach($watchesData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['wristCm'] ?? '' }}" class="watch-wrist w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 16.0 - 18.0" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['caseSize'] ?? '' }}" class="watch-case w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 40mm - 42mm" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['look'] ?? '' }}" class="watch-look w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. Classic / Versatile"></td>
                  <td class="p-2"><input type="text" value="{{ $row['strap'] ?? '' }}" class="watch-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 20mm - 22mm" required></td>
                  <td class="p-2 text-right">
                    <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete watch row">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <button type="button" onclick="addWatchRow()" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add watch size</span>
        </button>
      </div>

      {{-- Actions --}}
      <div class="px-4 sm:px-5 py-3.5 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2.5">
        <button type="button" onclick="document.getElementById('resetDefaultsForm').submit()" class="h-9 px-3.5 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-[13px] font-medium inline-flex items-center justify-center gap-1.5 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
          <span>Reset charts to defaults</span>
        </button>

        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer" style="background: var(--brand-dark);">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>Save changes</span>
        </button>
      </div>
    </section>
  </form>

  {{-- Hidden Reset Defaults Form --}}
  <form id="resetDefaultsForm" action="{{ route('admin.size-guide.update') }}" method="POST" class="hidden" onsubmit="return confirm('Are you sure you want to reset all size charts to the default international sizing? Any custom size edits will be cleared.')">
    @csrf
    @method('PUT')
    <input type="hidden" name="reset_defaults" value="1">
    <input type="hidden" name="size_guide_enabled" value="{{ $settings['size_guide_enabled'] }}">
    <input type="hidden" name="size_guide_default_unit" value="{{ $settings['size_guide_default_unit'] }}">
  </form>

</div>

{{-- Include Storefront Size Guide Modal for Live In-Admin Testing --}}
@include('storefront.partials.size-guide-modal')

<script>
  // Tab Switcher for Editors
  function switchEditorTab(tabId) {
    const tabs = ['shoes', 'belts', 'watches'];
    tabs.forEach(t => {
      const content = document.getElementById('editorTabContent-' + t);
      const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
      if (content && btn) {
        if (t === tabId) {
          content.classList.remove('hidden');
          btn.className = 'h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors bg-white text-gray-900 shadow-sm';
        } else {
          content.classList.add('hidden');
          btn.className = 'h-8 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:text-gray-900';
        }
      }
    });
  }

  // Row Adders
  function addShoeRow() {
    const tbody = document.getElementById('shoesTableBody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50/60 transition group';
    tr.innerHTML = `
      <td class="p-2"><input type="text" value="" class="shoe-eu w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 47" required></td>
      <td class="p-2"><input type="text" value="" class="shoe-usM w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 13.0"></td>
      <td class="p-2"><input type="text" value="" class="shoe-usW w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 14.5"></td>
      <td class="p-2"><input type="text" value="" class="shoe-uk w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 12.0"></td>
      <td class="p-2"><input type="number" step="0.1" value="" class="shoe-cm w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 30.5" required></td>
      <td class="p-2 text-right">
        <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  }

  function addBeltRow() {
    const tbody = document.getElementById('beltsTableBody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50/60 transition group';
    tr.innerHTML = `
      <td class="p-2"><input type="text" value="" class="belt-size w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 42 (3XL)" required></td>
      <td class="p-2"><input type="number" step="0.5" value="" class="belt-waist w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 107" required></td>
      <td class="p-2"><input type="text" value="" class="belt-pants w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 38 - 40"></td>
      <td class="p-2"><input type="number" step="0.5" value="" class="belt-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 120" required></td>
      <td class="p-2 text-right">
        <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  }

  function addWatchRow() {
    const tbody = document.getElementById('watchesTableBody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50/60 transition group';
    tr.innerHTML = `
      <td class="p-2"><input type="text" value="" class="watch-wrist w-full font-medium px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 20.0 - 22.0" required></td>
      <td class="p-2"><input type="text" value="" class="watch-case w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 46mm - 48mm" required></td>
      <td class="p-2"><input type="text" value="" class="watch-look w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. Extra Large"></td>
      <td class="p-2"><input type="text" value="" class="watch-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-gray-400 focus:outline-none" placeholder="e.g. 24mm - 26mm" required></td>
      <td class="p-2 text-right">
        <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  }

  // Serialize Table inputs to JSON before form submission
  document.getElementById('sizeGuideForm').addEventListener('submit', function (e) {
    // 1. Serialize Shoes
    const shoes = [];
    document.querySelectorAll('#shoesTableBody tr').forEach(tr => {
      const eu = tr.querySelector('.shoe-eu')?.value.trim();
      const usM = tr.querySelector('.shoe-usM')?.value.trim();
      const usW = tr.querySelector('.shoe-usW')?.value.trim();
      const uk = tr.querySelector('.shoe-uk')?.value.trim();
      const cm = tr.querySelector('.shoe-cm')?.value.trim();
      if (eu && cm) {
        shoes.push({ eu, usM, usW, uk, cm: parseFloat(cm) });
      }
    });
    document.getElementById('shoesDataInput').value = JSON.stringify(shoes);

    // 2. Serialize Belts
    const belts = [];
    document.querySelectorAll('#beltsTableBody tr').forEach(tr => {
      const size = tr.querySelector('.belt-size')?.value.trim();
      const waistCm = tr.querySelector('.belt-waist')?.value.trim();
      const pants = tr.querySelector('.belt-pants')?.value.trim();
      const strapLengthCm = tr.querySelector('.belt-strap')?.value.trim();
      if (size && waistCm && strapLengthCm) {
        belts.push({ size, waistCm: parseFloat(waistCm), pants, strapLengthCm: parseFloat(strapLengthCm) });
      }
    });
    document.getElementById('beltsDataInput').value = JSON.stringify(belts);

    // 3. Serialize Watches
    const watches = [];
    document.querySelectorAll('#watchesTableBody tr').forEach(tr => {
      const wristCm = tr.querySelector('.watch-wrist')?.value.trim();
      const caseSize = tr.querySelector('.watch-case')?.value.trim();
      const look = tr.querySelector('.watch-look')?.value.trim();
      const strap = tr.querySelector('.watch-strap')?.value.trim();
      if (wristCm && caseSize && strap) {
        watches.push({ wristCm, caseSize, look, strap });
      }
    });
    document.getElementById('watchesDataInput').value = JSON.stringify(watches);
  });
</script>
@endsection
