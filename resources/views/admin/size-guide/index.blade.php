@extends('layouts.admin')

@section('title', 'Size Guide Settings')

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
          <span>Size Guide Management</span>
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
      <p class="text-xs sm:text-sm text-gray-500">Configure storefront size guide visibility, default measurement units, and store advice notes.</p>
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

  {{-- Settings Form --}}
  <form action="{{ route('admin.size-guide.update') }}" method="POST" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
    @csrf
    @method('PUT')

    <div class="p-6 space-y-6">
      <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-100 pb-3">Configuration &amp; Controls</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Enable / Disable Select --}}
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Size Guide Status on Product Pages</label>
          <select name="size_guide_enabled" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">
            <option value="1" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '1')>Enabled (Show Size Guide button next to product size options)</option>
            <option value="0" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '0')>Disabled (Hide Size Guide button across storefront)</option>
          </select>
          <p class="text-[11px] text-gray-500">Controls whether the Size Guide trigger link appears beside product size options on the storefront.</p>
        </div>

        {{-- Default Unit --}}
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Default Measurement Unit</label>
          <select name="size_guide_default_unit" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">
            <option value="cm" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'cm')>Centimeters (CM)</option>
            <option value="in" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'in')>Inches (IN)</option>
          </select>
          <p class="text-[11px] text-gray-500">Customers can also switch units interactively inside the size guide modal at any time.</p>
        </div>
      </div>

      {{-- Custom Advice Tip --}}
      <div class="space-y-2 pt-2 border-t border-gray-100">
        <label for="size_guide_custom_tip" class="block text-xs font-bold text-gray-700">Store Fit Advice Notice (Optional)</label>
        <textarea id="size_guide_custom_tip" name="size_guide_custom_tip" rows="3" placeholder="e.g. Our footwear runs true to size. If you have wider feet, we recommend picking 1 size up." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 focus:bg-white shadow-2xs">{{ old('size_guide_custom_tip', $settings['size_guide_custom_tip']) }}</textarea>
        <p class="text-[11px] text-gray-500">Displayed at the bottom of the size guide modal as a special tip from your store team.</p>
      </div>

    </div>

    {{-- Form Action Footer --}}
    <div class="px-6 py-4 bg-gray-50/70 border-t border-gray-200/80 flex items-center justify-end">
      <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-teal-600 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center gap-2 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>Save Changes</span>
      </button>
    </div>
  </form>

  {{-- Interactive Sizing Charts Summary Preview --}}
  <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <h2 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Active Sizing Charts Preview</h2>
      <span class="text-[11px] text-teal-700 font-semibold bg-teal-50 px-2.5 py-0.5 rounded-full border border-teal-200">Interactive Preview</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/80 space-y-3 flex flex-col justify-between">
        <div class="space-y-2">
          <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/70 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/>
            </svg>
          </div>
          <h3 class="font-bold text-sm text-gray-900">Footwear (Shoes)</h3>
          <p class="text-xs text-gray-500 leading-relaxed">Includes EU (39–46), US Men, US Women, UK, and exact Foot Length conversions.</p>
        </div>
        <button type="button" onclick="openSizeGuideModal('shoes')" class="w-full py-2 px-3 bg-white hover:bg-teal-50 border border-gray-200 hover:border-teal-300 rounded-xl text-teal-700 font-semibold text-xs transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
          <span>Preview Shoes Chart</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>

      <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/80 space-y-3 flex flex-col justify-between">
        <div class="space-y-2">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/70 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/>
              <path d="m14.5 12.5 2-2"/>
            </svg>
          </div>
          <h3 class="font-bold text-sm text-gray-900">Belts &amp; Apparel</h3>
          <p class="text-xs text-gray-500 leading-relaxed">Includes S to XXL sizes, waist measurements, pants sizes, and total belt strap lengths.</p>
        </div>
        <button type="button" onclick="openSizeGuideModal('belts')" class="w-full py-2 px-3 bg-white hover:bg-amber-50 border border-gray-200 hover:border-amber-300 rounded-xl text-amber-800 font-semibold text-xs transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
          <span>Preview Belts Chart</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>

      <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/80 space-y-3 flex flex-col justify-between">
        <div class="space-y-2">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 border border-sky-200/70 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="7"/>
              <polyline points="12 9 12 12 13.5 13.5"/>
              <path d="M16.51 17.35l-.35 3.83A2 2 0 0 1 14.17 23H9.83a2 2 0 0 1-1.99-1.82l-.35-3.83m.02-10.7l.35-3.83A2 2 0 0 1 9.83 1h4.34a2 2 0 0 1 1.99 1.82l.35 3.83"/>
            </svg>
          </div>
          <h3 class="font-bold text-sm text-gray-900">Watches &amp; Straps</h3>
          <p class="text-xs text-gray-500 leading-relaxed">Includes wrist size ranges, ideal case diameters (36mm–46mm), and recommended strap widths.</p>
        </div>
        <button type="button" onclick="openSizeGuideModal('watches')" class="w-full py-2 px-3 bg-white hover:bg-sky-50 border border-gray-200 hover:border-sky-300 rounded-xl text-sky-800 font-semibold text-xs transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
          <span>Preview Watches Chart</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Include Storefront Size Guide Modal for Live In-Admin Testing --}}
@include('storefront.partials.size-guide-modal')
@endsection
