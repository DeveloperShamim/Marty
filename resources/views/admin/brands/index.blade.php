@extends('layouts.admin')
@section('title', 'Brands Management')

@section('content')
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>🏷️ Brand Management</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Manage brand catalog, logos, banners, homepage features and storefront visibility</p>
    </div>
    <a href="{{ route('admin.brands.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all">
      <span>+ Add New Brand</span>
    </a>
  </div>

  {{-- Homepage Featured Brands Section Status Banner --}}
  @php $sectionEnabled = setting('show_featured_brands', '1') === '1'; @endphp
  <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="space-y-1">
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-extrabold text-sm text-stone-900">🌟 Storefront Featured Brands Section</span>
        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full {{ $sectionEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
          {{ $sectionEnabled ? '🟢 Active on Home Page' : '🔴 Hidden on Home Page' }}
        </span>
      </div>
      <p class="text-xs text-stone-500 font-medium leading-relaxed">
        Subtitle: "{{ setting('home_featured_brands_subtitle', 'Shop authentic products directly from leading brands') }}"
      </p>
    </div>

    <div class="flex items-center gap-2 w-full md:w-auto">
      <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}" class="flex-1 md:flex-none">
        @csrf @method('PUT')
        <input type="hidden" name="show_featured_brands" value="{{ $sectionEnabled ? '0' : '1' }}">
        <button type="submit" class="w-full md:w-auto px-3.5 py-2 text-xs font-extrabold rounded-xl transition cursor-pointer text-center {{ $sectionEnabled ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-2xs' }}">
          {{ $sectionEnabled ? '🚫 Disable Homepage Section' : '✓ Enable Homepage Section' }}
        </button>
      </form>
      <a href="{{ route('admin.settings.edit') }}" class="px-3.5 py-2 text-xs font-bold text-stone-700 hover:text-brand-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition shrink-0">
        ⚙️ Settings
      </a>
    </div>
  </div>

  {{-- Mobile Cards Layout (Visible on Small Screens) --}}
  <div class="grid grid-cols-1 gap-3.5 md:hidden">
    @forelse($brands as $brand)
      <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs space-y-3.5">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-xl border border-stone-200 bg-stone-50 p-1 flex items-center justify-center shrink-0 overflow-hidden">
              <img src="{{ $brand->logoUrl() }}" class="max-h-full max-w-full object-contain" alt="{{ $brand->name }}">
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
              <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
            @else
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-stone-100 text-stone-500 border border-stone-200">Hidden</span>
            @endif

            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
              {{ $brand->products_count }} {{ Str::plural('Product', $brand->products_count) }}
            </span>
          </div>
        </div>

        <div class="pt-3 border-t border-stone-100 flex items-center justify-between gap-2">
          <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}">
            @csrf @method('PATCH')
            <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer {{ $brand->is_featured ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-stone-100 text-stone-600 border border-stone-200 hover:bg-stone-200' }}">
              {{ $brand->is_featured ? '★ Featured Brand' : '☆ Make Featured' }}
            </button>
          </form>

          <div class="flex items-center gap-2">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200">
              Edit
            </a>
            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer">
                Delete
              </button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white p-8 text-center rounded-2xl border border-stone-200">
        <div class="text-3xl mb-2">🏷️</div>
        <p class="text-xs font-bold text-stone-600">No brands found.</p>
        <a href="{{ route('admin.brands.create') }}" class="mt-2 inline-block text-xs font-bold text-brand-600 hover:underline">+ Add First Brand</a>
      </div>
    @endforelse
  </div>

  {{-- Desktop Table View (Hidden on Small Screens) --}}
  <div class="hidden md:block bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-stone-200 font-extrabold text-stone-600 uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3.5">Brand Identity</th>
          <th class="px-5 py-3.5">Slug / URL</th>
          <th class="px-5 py-3.5">Website</th>
          <th class="px-5 py-3.5 text-center">Products</th>
          <th class="px-5 py-3.5 text-center">Status</th>
          <th class="px-5 py-3.5 text-center">Homepage Featured</th>
          <th class="px-5 py-3.5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-100 font-medium">
        @forelse($brands as $brand)
          <tr class="hover:bg-stone-50/70 transition-colors">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <img src="{{ $brand->logoUrl() }}" class="h-10 w-10 object-contain rounded-xl border border-stone-200 bg-white p-1 shadow-2xs" alt="{{ $brand->name }}">
                <div>
                  <span class="font-extrabold text-stone-900 text-sm block">{{ $brand->name }}</span>
                  @if($brand->banner)
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">Hero Banner Uploaded</span>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5 text-stone-500 font-mono text-[11px]">{{ $brand->slug }}</td>
            <td class="px-5 py-3.5 text-stone-500">
              @if($brand->website)
                <a href="{{ $brand->website }}" target="_blank" class="text-brand-600 hover:underline font-bold text-xs inline-flex items-center gap-1">
                  {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }} &nearr;
                </a>
              @else
                <span class="text-stone-300">&mdash;</span>
              @endif
            </td>
            <td class="px-5 py-3.5 text-center">
              <span class="px-2.5 py-1 text-xs font-extrabold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                {{ $brand->products_count }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-center">
              @if($brand->is_active)
                <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
              @else
                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-stone-100 text-stone-500 border border-stone-200">Hidden</span>
              @endif
            </td>
            <td class="px-5 py-3.5 text-center">
              <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="px-3 py-1 text-xs font-extrabold rounded-full transition cursor-pointer {{ $brand->is_featured ? 'bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200' : 'bg-stone-100 text-stone-500 border border-stone-200 hover:bg-stone-200' }}">
                  {{ $brand->is_featured ? '★ Featured' : '☆ Enable' }}
                </button>
              </form>
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
              <a href="{{ route('admin.brands.edit', $brand) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200 transition">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="inline" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer transition">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-5 py-12 text-center text-stone-400 font-bold">No brands found. Create your first brand above!</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
