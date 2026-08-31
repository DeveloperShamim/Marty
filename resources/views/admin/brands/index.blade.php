@extends('layouts.admin')
@section('title', 'Brands Management')

@section('content')
<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Stats Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>🏷️</span> Product Brands
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
          {{ $brands->count() }} Total
        </span>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
          {{ $brands->where('is_featured', true)->count() }} Featured
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Manage manufacturer brands, partner logos, storefront brand filters, and homepage showcases.
      </p>
    </div>

    <a href="{{ route('admin.brands.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto shrink-0 cursor-pointer">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      <span>Create Brand</span>
    </a>
  </div>

  {{-- Storefront Homepage Featured Section Quick Toggle Bar --}}
  @php $sectionEnabled = setting('show_featured_brands', '1') === '1'; @endphp
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">
    <div class="space-y-1">
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-extrabold text-xs sm:text-sm text-stone-900">🌟 Homepage Featured Brands Bar</span>
        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full {{ $sectionEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
          {{ $sectionEnabled ? '● Active on Homepage' : '○ Hidden on Homepage' }}
        </span>
      </div>
      <p class="text-xs text-stone-500 leading-snug">
        Subtitle: "{{ setting('home_featured_brands_subtitle', 'Shop authentic products directly from leading brands') }}"
      </p>
    </div>

    <div class="flex items-center gap-2 w-full md:w-auto">
      <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}" class="flex-1 md:flex-none">
        @csrf @method('PUT')
        <input type="hidden" name="show_featured_brands" value="{{ $sectionEnabled ? '0' : '1' }}">
        <button type="submit" class="w-full md:w-auto px-3.5 py-2 text-xs font-extrabold rounded-xl transition cursor-pointer text-center {{ $sectionEnabled ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-2xs' }}">
          {{ $sectionEnabled ? '🚫 Disable Homepage Bar' : '✓ Enable Homepage Bar' }}
        </button>
      </form>
      <a href="{{ route('admin.settings.edit') }}" class="px-3.5 py-2 text-xs font-bold text-stone-700 hover:text-brand-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition shrink-0">
        ⚙️ Settings
      </a>
    </div>
  </div>

  {{-- Main Container Card --}}
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden">
    
    {{-- Desktop & Tablet Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4 w-12 text-center">Pos</th>
            <th class="py-3 px-4">Brand Identity</th>
            <th class="py-3 px-4">URL Slug</th>
            <th class="py-3 px-4">Website</th>
            <th class="py-3 px-4 text-center">Assigned Products</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-center">Homepage Featured</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($brands as $brand)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4 text-center font-mono font-bold text-stone-400">
                #{{ $brand->position ?? 0 }}
              </td>
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-stone-50 border border-stone-200 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                    <img src="{{ $brand->logoUrl() }}" class="h-full w-full object-contain" alt="{{ $brand->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
                  </div>
                  <div>
                    <span class="font-extrabold text-stone-900 text-xs sm:text-sm block leading-tight">{{ $brand->name }}</span>
                    @if($brand->banner)
                      <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200/70 inline-block mt-0.5">Hero Banner Active</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-stone-500 font-mono text-xs">
                /brand/{{ $brand->slug }}
              </td>
              <td class="py-3.5 px-4 text-stone-500">
                @if($brand->website)
                  <a href="{{ $brand->website }}" target="_blank" class="text-brand-600 hover:underline font-bold text-xs inline-flex items-center gap-1">
                    {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }} &nearr;
                  </a>
                @else
                  <span class="text-stone-300">&mdash;</span>
                @endif
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-stone-100 text-stone-800 border border-stone-200 font-mono">
                  {{ number_format($brand->products_count) }} products
                </span>
              </td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                @if($brand->is_active)
                  <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Active
                  </span>
                @else
                  <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-stone-100 text-stone-500 border border-stone-200">
                    Hidden
                  </span>
                @endif
              </td>
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline-flex items-center justify-center">
                  @csrf @method('PATCH')
                  <button type="submit" class="group inline-flex items-center gap-2 px-2.5 py-1 rounded-full border transition-all cursor-pointer {{ $brand->is_featured ? 'bg-emerald-50 border-emerald-200/90 text-emerald-800 hover:bg-emerald-100/90 shadow-2xs' : 'bg-stone-50 border-stone-200 text-stone-400 hover:bg-stone-100 hover:text-stone-700' }}" title="{{ $brand->is_featured ? 'Click to un-feature brand' : 'Click to feature brand' }}">
                    <span class="w-6 h-3.5 rounded-full p-0.5 transition-colors flex items-center {{ $brand->is_featured ? 'bg-emerald-600 justify-end' : 'bg-stone-300 justify-start' }}">
                      <span class="w-2.5 h-2.5 rounded-full bg-white shadow-xs block"></span>
                    </span>
                    <span class="text-[11px] font-extrabold tracking-tight">
                      {{ $brand->is_featured ? 'Featured' : 'Not Featured' }}
                    </span>
                  </button>
                </form>
              </td>
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.brands.edit', $brand) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition shadow-2xs">
                    Edit
                  </a>
                  <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="inline" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition cursor-pointer">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-5 py-12 text-center text-stone-400 text-xs italic bg-stone-50/50">
                🏷️ No brands created yet. Click <strong>"Create Brand"</strong> above to start.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden divide-y divide-stone-100 bg-white">
      @forelse($brands as $brand)
        <div class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-xl border border-stone-200 bg-stone-50 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                <img src="{{ $brand->logoUrl() }}" class="h-full w-full object-contain" alt="{{ $brand->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';" />
              </div>
              <div>
                <h3 class="font-extrabold text-stone-900 text-sm leading-tight">{{ $brand->name }}</h3>
                <p class="text-[11px] text-stone-400 font-mono mt-0.5">/brand/{{ $brand->slug }}</p>
                @if($brand->website)
                  <a href="{{ $brand->website }}" target="_blank" class="text-[11px] font-bold text-brand-600 hover:underline inline-flex items-center gap-1 mt-0.5">
                    🌐 {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }} &nearr;
                  </a>
                @endif
              </div>
            </div>

            <div class="flex flex-col items-end gap-1 shrink-0">
              @if($brand->is_active)
                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
              @else
                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-stone-100 text-stone-500 border border-stone-200">Hidden</span>
              @endif

              <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-full bg-stone-100 text-stone-700 border border-stone-200">
                {{ $brand->products_count }} Products
              </span>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2 pt-2 border-t border-stone-100">
            <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline">
              @csrf @method('PATCH')
              <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-extrabold rounded-full border transition cursor-pointer {{ $brand->is_featured ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-stone-50 border-stone-200 text-stone-500' }}">
                <span class="w-5 h-3 rounded-full p-0.5 transition-colors flex items-center {{ $brand->is_featured ? 'bg-emerald-600 justify-end' : 'bg-stone-300 justify-start' }}">
                  <span class="w-2 h-2 rounded-full bg-white shadow-xs block"></span>
                </span>
                <span>{{ $brand->is_featured ? 'Featured' : 'Not Featured' }}</span>
              </button>
            </form>

            <span class="text-[11px] font-mono text-stone-400">#Pos: {{ $brand->position ?? 0 }}</span>
          </div>

          <div class="flex items-center justify-end gap-2 pt-1">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="flex-1 text-center py-2 px-3 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition shadow-2xs">
              Edit
            </a>
            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="flex-1" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="w-full py-2 px-3 text-xs font-bold rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition cursor-pointer">
                Delete
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400 bg-stone-50">
          No brands created yet.
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
