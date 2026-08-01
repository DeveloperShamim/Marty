@extends('layouts.admin')

@section('title', 'Size Guide Settings')

@section('content')
<div class="space-y-6 max-w-5xl">
  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-xs">
    <div>
      <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
        <span>📏</span>
        <span>Size Guide Management</span>
      </h1>
      <p class="text-xs text-gray-500 mt-1">Configure storefront size guide visibility, default measurement units, and store advice notes.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('shop') }}" target="_blank" class="px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors inline-flex items-center gap-1.5">
        <span>View Storefront</span>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      </a>
    </div>
  </div>

  @if(session('status'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold px-4 py-3 rounded-xl flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  {{-- Settings Form --}}
  <form action="{{ route('admin.size-guide.update') }}" method="POST" class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
    @csrf
    @method('PUT')

    <div class="p-6 space-y-6">
      <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3">Configuration &amp; Controls</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Enable / Disable Toggle --}}
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Size Guide Status on Product Pages</label>
          <select name="size_guide_enabled" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="1" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '1')>Enabled (Show Size Guide button on product details)</option>
            <option value="0" @selected(old('size_guide_enabled', $settings['size_guide_enabled']) === '0')>Disabled (Hide Size Guide button across storefront)</option>
          </select>
          <p class="text-[11px] text-gray-500">Controls whether the Size Guide trigger link appears next to product size options.</p>
        </div>

        {{-- Default Unit --}}
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-700">Default Measurement Unit</label>
          <select name="size_guide_default_unit" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="cm" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'cm')>Centimeters (CM)</option>
            <option value="in" @selected(old('size_guide_default_unit', $settings['size_guide_default_unit']) === 'in')>Inches (IN)</option>
          </select>
          <p class="text-[11px] text-gray-500">Customers can switch units interactively inside the size modal at any time.</p>
        </div>
      </div>

      {{-- Custom Advice Tip --}}
      <div class="space-y-2 pt-2 border-t border-gray-100">
        <label for="size_guide_custom_tip" class="block text-xs font-bold text-gray-700">Store Fit Advice Notice (Optional)</label>
        <textarea id="size_guide_custom_tip" name="size_guide_custom_tip" rows="3" placeholder="e.g. Our footwear runs true to size. If you have wider feet, we recommend picking 1 size up." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('size_guide_custom_tip', $settings['size_guide_custom_tip']) }}</textarea>
        <p class="text-[11px] text-gray-500">Displayed at the bottom of the size guide modal as a special tip from your store staff.</p>
      </div>

    </div>

    {{-- Form Action Footer --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end">
      <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>Save Changes</span>
      </button>
    </div>
  </form>

  {{-- Interactive Sizing Charts Summary Preview --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-4">
    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3">Active Sizing Categories Preview</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
        <div class="text-xl">👟</div>
        <h3 class="font-bold text-sm text-gray-900">Footwear (Shoes)</h3>
        <p class="text-xs text-gray-500 leading-relaxed">Includes EU (39–46), US Men, US Women, UK, and exact Foot Length conversions.</p>
      </div>

      <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
        <div class="text-xl">🥋</div>
        <h3 class="font-bold text-sm text-gray-900">Belts &amp; Apparel</h3>
        <p class="text-xs text-gray-500 leading-relaxed">Includes S to XXL sizes, waist measurements, pants sizes, and total belt strap lengths.</p>
      </div>

      <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
        <div class="text-xl">⌚</div>
        <h3 class="font-bold text-sm text-gray-900">Watches &amp; Straps</h3>
        <p class="text-xs text-gray-500 leading-relaxed">Includes wrist size ranges, ideal case diameters (36mm–46mm), and recommended strap widths.</p>
      </div>
    </div>
  </div>
</div>
@endsection
