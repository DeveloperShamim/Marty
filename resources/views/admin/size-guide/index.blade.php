@extends('layouts.admin')

@section('title', 'Size Guide Settings & Chart Editor')

@section('content')
<div class="space-y-6 max-w-5xl">
  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-gray-200/80 shadow-xs">
    <div class="space-y-1">
      <div class="flex items-center gap-3 flex-wrap">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2.5">
          <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/70 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/>
              <path d="m14.5 12.5 2-2"/>
              <path d="m11.5 9.5 2-2"/>
              <path d="m8.5 6.5 2-2"/>
              <path d="m17.5 15.5 2-2"/>
            </svg>
          </span>
          <span>Size Guide Management &amp; Chart Editor</span>
        </h1>
        @if($settings['size_guide_enabled'] === '1')
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold shrink-0">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>Active on Storefront</span>
          </span>
        @else
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold shrink-0">
            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
            <span>Disabled on Storefront</span>
          </span>
        @endif
      </div>
      <p class="text-xs sm:text-sm text-gray-500">Configure storefront size guide settings, default units, and modify custom size chart rows.</p>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap self-start sm:self-auto">
      <button type="button" onclick="openSizeGuideModal('shoes')" class="px-4 py-2.5 text-xs font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        <span>Test Live Modal</span>
      </button>

      <a href="{{ route('shop') }}" target="_blank" class="px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all shadow-2xs inline-flex items-center gap-1.5">
        <span>View Storefront</span>
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 14 21 3"/></svg>
      </a>
    </div>
  </div>

  @if(session('status'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold px-4 py-3 rounded-xl flex items-center justify-between shadow-2xs gap-3">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
    </div>
  @endif

  {{-- Settings & Chart Editor Form --}}
  <form id="sizeGuideForm" action="{{ route('admin.size-guide.update') }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Hidden JSON inputs for serialized table data --}}
    <input type="hidden" name="shoes_data" id="shoesDataInput">
    <input type="hidden" name="belts_data" id="beltsDataInput">
    <input type="hidden" name="watches_data" id="watchesDataInput">

    {{-- Card 1: Configuration Controls --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6 space-y-6">
      <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-100 pb-3">Storefront Display Controls</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Size Guide Status on Product Pages</label>
          <select name="size_guide_enabled" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">
            <option value="1" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '1')>Enabled (Show Size Guide button next to product size options)</option>
            <option value="0" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '0')>Disabled (Hide Size Guide button across storefront)</option>
          </select>
          <p class="text-[11px] text-gray-500">Controls whether the Size Guide trigger link appears beside product size options on the storefront.</p>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Default Measurement Unit</label>
          <select name="size_guide_default_unit" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">
            <option value="cm" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'cm')>Centimeters (CM)</option>
            <option value="in" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'in')>Inches (IN)</option>
          </select>
          <p class="text-[11px] text-gray-500">Customers can switch units interactively inside the modal at any time.</p>
        </div>
      </div>

      <div class="space-y-2 pt-2 border-t border-gray-100">
        <label for="size_guide_custom_tip" class="block text-xs font-bold text-gray-700">Store Fit Advice Notice (Optional)</label>
        <textarea id="size_guide_custom_tip" name="size_guide_custom_tip" rows="2" placeholder="e.g. Our footwear runs true to size. If you have wider feet, we recommend picking 1 size up." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">{{ old('size_guide_custom_tip', $settings['size_guide_custom_tip']) }}</textarea>
        <p class="text-[11px] text-gray-500">Displayed at the bottom of the size guide modal as a special tip from your store team.</p>
      </div>
    </div>

    {{-- Card 2: Interactive Chart Sizing Editors --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Modify Sizing Chart Values</h2>
          <p class="text-xs text-gray-500 mt-0.5">Edit existing row values, add custom sizes, or delete unused rows. Changes update the storefront modal live.</p>
        </div>

        {{-- Tab Switcher --}}
        <div class="flex items-center gap-1.5 p-1 bg-gray-100 rounded-xl self-start sm:self-auto" id="editorTabNav">
          <button type="button" onclick="switchEditorTab('shoes')" id="tabBtnShoes" class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs bg-white text-teal-700">
            Footwear (Shoes)
          </button>
          <button type="button" onclick="switchEditorTab('belts')" id="tabBtnBelts" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-gray-600 hover:text-gray-900">
            Belts &amp; Apparel
          </button>
          <button type="button" onclick="switchEditorTab('watches')" id="tabBtnWatches" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-gray-600 hover:text-gray-900">
            Watches &amp; Straps
          </button>
        </div>
      </div>

      {{-- Tab 1: Shoes Table Editor --}}
      <div id="editorTabContent-shoes" class="p-6 space-y-4">
        <div class="overflow-x-auto border border-gray-200/80 rounded-xl">
          <table class="w-full text-left text-xs border-collapse" id="shoesTable">
            <thead class="bg-gray-50/80 text-gray-600 font-bold border-b border-gray-200">
              <tr>
                <th class="py-3 px-3">EU Size</th>
                <th class="py-3 px-3">US Men</th>
                <th class="py-3 px-3">US Women</th>
                <th class="py-3 px-3">UK Size</th>
                <th class="py-3 px-3">Foot Length (CM)</th>
                <th class="py-3 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="shoesTableBody">
              @foreach($shoesData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['eu'] ?? '' }}" class="shoe-eu w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['usM'] ?? '' }}" class="shoe-usM w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none"></td>
                  <td class="p-2"><input type="text" value="{{ $row['usW'] ?? '' }}" class="shoe-usW w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none"></td>
                  <td class="p-2"><input type="text" value="{{ $row['uk'] ?? '' }}" class="shoe-uk w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none"></td>
                  <td class="p-2"><input type="number" step="0.1" value="{{ $row['cm'] ?? '' }}" class="shoe-cm w-full font-semibold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" required></td>
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
        <button type="button" onclick="addShoeRow()" class="px-3.5 py-2 text-xs font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add Shoe Size Row</span>
        </button>
      </div>

      {{-- Tab 2: Belts Table Editor --}}
      <div id="editorTabContent-belts" class="p-6 space-y-4 hidden">
        <div class="overflow-x-auto border border-gray-200/80 rounded-xl">
          <table class="w-full text-left text-xs border-collapse" id="beltsTable">
            <thead class="bg-gray-50/80 text-gray-600 font-bold border-b border-gray-200">
              <tr>
                <th class="py-3 px-3">Size Name (e.g. 34 (M))</th>
                <th class="py-3 px-3">Waist (CM)</th>
                <th class="py-3 px-3">Pants Size (e.g. 30 - 32)</th>
                <th class="py-3 px-3">Strap Length (CM)</th>
                <th class="py-3 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="beltsTableBody">
              @foreach($beltsData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['size'] ?? '' }}" class="belt-size w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" required></td>
                  <td class="p-2"><input type="number" step="0.5" value="{{ $row['waistCm'] ?? '' }}" class="belt-waist w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['pants'] ?? '' }}" class="belt-pants w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none"></td>
                  <td class="p-2"><input type="number" step="0.5" value="{{ $row['strapLengthCm'] ?? '' }}" class="belt-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" required></td>
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
        <button type="button" onclick="addBeltRow()" class="px-3.5 py-2 text-xs font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add Belt Size Row</span>
        </button>
      </div>

      {{-- Tab 3: Watches Table Editor --}}
      <div id="editorTabContent-watches" class="p-6 space-y-4 hidden">
        <div class="overflow-x-auto border border-gray-200/80 rounded-xl">
          <table class="w-full text-left text-xs border-collapse" id="watchesTable">
            <thead class="bg-gray-50/80 text-gray-600 font-bold border-b border-gray-200">
              <tr>
                <th class="py-3 px-3">Wrist Circumference (CM Range)</th>
                <th class="py-3 px-3">Ideal Case Diameter</th>
                <th class="py-3 px-3">Fit / Aesthetic Style</th>
                <th class="py-3 px-3">Recommended Strap Width</th>
                <th class="py-3 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="watchesTableBody">
              @foreach($watchesData as $idx => $row)
                <tr class="hover:bg-gray-50/60 transition group">
                  <td class="p-2"><input type="text" value="{{ $row['wristCm'] ?? '' }}" class="watch-wrist w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 16.0 - 18.0" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['caseSize'] ?? '' }}" class="watch-case w-full font-semibold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 40mm - 42mm" required></td>
                  <td class="p-2"><input type="text" value="{{ $row['look'] ?? '' }}" class="watch-look w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. Classic / Versatile"></td>
                  <td class="p-2"><input type="text" value="{{ $row['strap'] ?? '' }}" class="watch-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 20mm - 22mm" required></td>
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
        <button type="button" onclick="addWatchRow()" class="px-3.5 py-2 text-xs font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200/80 rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span>Add Watch Size Row</span>
        </button>
      </div>

      {{-- Action Footer --}}
      <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <button type="button" onclick="document.getElementById('resetDefaultsForm').submit()" class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition flex items-center gap-1.5 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
          <span>Reset All Charts to Factory Defaults</span>
        </button>

        <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-teal-600 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <span>Save Changes &amp; Publish Sizes</span>
        </button>
      </div>
    </div>
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
          btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-xs bg-white text-teal-700';
        } else {
          content.classList.add('hidden');
          btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition text-gray-600 hover:text-gray-900';
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
      <td class="p-2"><input type="text" value="" class="shoe-eu w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 47" required></td>
      <td class="p-2"><input type="text" value="" class="shoe-usM w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 13.0"></td>
      <td class="p-2"><input type="text" value="" class="shoe-usW w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 14.5"></td>
      <td class="p-2"><input type="text" value="" class="shoe-uk w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 12.0"></td>
      <td class="p-2"><input type="number" step="0.1" value="" class="shoe-cm w-full font-semibold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 30.5" required></td>
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
      <td class="p-2"><input type="text" value="" class="belt-size w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 42 (3XL)" required></td>
      <td class="p-2"><input type="number" step="0.5" value="" class="belt-waist w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 107" required></td>
      <td class="p-2"><input type="text" value="" class="belt-pants w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 38 - 40"></td>
      <td class="p-2"><input type="number" step="0.5" value="" class="belt-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 120" required></td>
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
      <td class="p-2"><input type="text" value="" class="watch-wrist w-full font-bold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 20.0 - 22.0" required></td>
      <td class="p-2"><input type="text" value="" class="watch-case w-full font-semibold px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 46mm - 48mm" required></td>
      <td class="p-2"><input type="text" value="" class="watch-look w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. Extra Large"></td>
      <td class="p-2"><input type="text" value="" class="watch-strap w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:border-teal-500 focus:outline-none" placeholder="e.g. 24mm - 26mm" required></td>
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
