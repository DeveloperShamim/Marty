@extends('layouts.admin')
@section('title', 'Brands')
@section('subtitle', 'Brand logos, store brand filters and the home page brands bar.')

@section('page-actions')
  <a href="{{ route('admin.brands.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    New brand
  </a>
@endsection

@php
  $imgFallback = "this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';";
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Home page brands bar --}}
  @php $sectionEnabled = setting('show_featured_brands', '1') === '1'; @endphp
  <section class="panel p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="min-w-0">
      <h2 class="text-[15px] font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
        Home page brands bar
        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $sectionEnabled ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $sectionEnabled ? 'Showing' : 'Hidden' }}</span>
      </h2>
      <p class="text-xs text-gray-500 mt-0.5 truncate">Subtitle: "{{ setting('home_featured_brands_subtitle', 'Shop authentic products directly from leading brands') }}"</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}" class="flex-1 sm:flex-none">
        @csrf @method('PUT')
        <input type="hidden" name="show_featured_brands" value="{{ $sectionEnabled ? '0' : '1' }}">
        <button type="submit" class="w-full h-9 px-3.5 rounded-full text-[13px] font-medium cursor-pointer {{ $sectionEnabled ? 'bg-gray-100 hover:bg-gray-200 text-gray-800' : 'text-white' }}" @unless($sectionEnabled) style="background: var(--brand-dark);" @endunless>
          {{ $sectionEnabled ? 'Hide from home page' : 'Show on home page' }}
        </button>
      </form>
      <a href="{{ route('admin.settings.edit') }}" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center gap-1.5 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.2 2h-.4a2 2 0 0 0-2 2v.2a2 2 0 0 1-1 1.7l-.4.3a2 2 0 0 1-2 0l-.2-.1a2 2 0 0 0-2.7.7l-.2.4a2 2 0 0 0 .7 2.7l.2.1a2 2 0 0 1 1 1.7v.6a2 2 0 0 1-1 1.7l-.2.1a2 2 0 0 0-.7 2.7l.2.4a2 2 0 0 0 2.7.7l.2-.1a2 2 0 0 1 2 0l.4.3a2 2 0 0 1 1 1.7v.2a2 2 0 0 0 2 2h.4a2 2 0 0 0 2-2v-.2a2 2 0 0 1 1-1.7l.4-.3a2 2 0 0 1 2 0l.2.1a2 2 0 0 0 2.7-.7l.2-.4a2 2 0 0 0-.7-2.7l-.2-.1a2 2 0 0 1-1-1.7v-.6a2 2 0 0 1 1-1.7l.2-.1a2 2 0 0 0 .7-2.7l-.2-.4a2 2 0 0 0-2.7-.7l-.2.1a2 2 0 0 1-2 0l-.4-.3a2 2 0 0 1-1-1.7V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
        Settings
      </a>
    </div>
  </section>

  <div class="card overflow-hidden">
    <div class="px-4 py-3 sm:px-5 flex items-center justify-between gap-3">
      <h2 class="text-[15px] font-semibold text-gray-900">All brands</h2>
      <div class="flex items-center gap-1.5 text-[11px] font-semibold">
        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 tabular-nums">{{ $brands->count() }} total</span>
        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 tabular-nums">{{ $brands->where('is_featured', true)->count() }} featured</span>
      </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4 w-12 text-center">Pos</th>
            <th class="py-3 px-4">Brand</th>
            <th class="py-3 px-4">Website</th>
            <th class="py-3 px-4 text-right">Products</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Featured</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($brands as $brand)
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4 text-center text-gray-400 tabular-nums">{{ $brand->position ?? 0 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-gray-50 ring-1 ring-gray-100 p-1 grid place-items-center shrink-0 overflow-hidden">
                    <img src="{{ $brand->logoUrl() }}" class="h-full w-full object-contain" alt="{{ $brand->name }}" onerror="{{ $imgFallback }}" />
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('admin.brands.edit', $brand) }}" class="font-semibold text-gray-900 hover:underline block leading-tight">{{ $brand->name }}</a>
                    <span class="text-[11px] text-gray-500 font-mono">/brand/{{ $brand->slug }}</span>
                    @if($brand->banner)
                      <span class="ml-1 px-1.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 text-sky-700">Banner</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                @if($brand->website)
                  <a href="{{ $brand->website }}" target="_blank" class="text-gray-700 hover:underline text-xs inline-flex items-center gap-1">
                    {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                  </a>
                @else
                  <span class="text-gray-300">&mdash;</span>
                @endif
              </td>
              <td class="py-3 px-4 text-right font-medium text-gray-900 tabular-nums">{{ number_format($brand->products_count) }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                @if($brand->is_active)
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Active</span>
                @else
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Hidden</span>
                @endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline-flex">
                  @csrf @method('PATCH')
                  <button type="submit" class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer {{ $brand->is_featured ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800' }}" title="{{ $brand->is_featured ? 'Click to un-feature brand' : 'Click to feature brand' }}">
                    <span class="w-8 h-[18px] rounded-full p-0.5 transition-colors flex items-center {{ $brand->is_featured ? 'bg-emerald-600 justify-end' : 'bg-gray-300 justify-start' }}">
                      <span class="w-3.5 h-3.5 rounded-full bg-white shadow-sm block"></span>
                    </span>
                    {{ $brand->is_featured ? 'Featured' : 'Not featured' }}
                  </button>
                </form>
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.brands.edit', $brand) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">Edit</a>
                  <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="inline" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 inline-flex items-center justify-center cursor-pointer" title="Delete" aria-label="Delete {{ $brand->name }}">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-gray-500 text-sm">No brands yet. Use "New brand" to add the first one.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: one card per brand --}}
    <div class="md:hidden px-3 pb-3 space-y-2.5">
      @forelse($brands as $brand)
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-3">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-white p-1 grid place-items-center shrink-0 overflow-hidden">
              <img src="{{ $brand->logoUrl() }}" class="h-full w-full object-contain" alt="{{ $brand->name }}" onerror="{{ $imgFallback }}" />
            </div>
            <div class="min-w-0 flex-1">
              <a href="{{ route('admin.brands.edit', $brand) }}" class="font-semibold text-sm text-gray-900 truncate block">{{ $brand->name }}</a>
              <p class="text-[11px] text-gray-500 font-mono truncate mt-0.5">/brand/{{ $brand->slug }}</p>
              @if($brand->website)
                <a href="{{ $brand->website }}" target="_blank" class="text-[11px] text-gray-600 hover:underline truncate block">{{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}</a>
              @endif
            </div>
            <div class="text-right shrink-0">
              <p class="text-[15px] font-semibold text-gray-900 tabular-nums">{{ $brand->products_count }}</p>
              <p class="text-[11px] text-gray-500">products</p>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
              @if($brand->is_active)
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Active</span>
              @else
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Hidden</span>
              @endif
              <span class="text-[11px] text-gray-400 tabular-nums">Pos {{ $brand->position ?? 0 }}</span>
            </div>
            <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline">
              @csrf @method('PATCH')
              <button type="submit" class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer {{ $brand->is_featured ? 'text-gray-900' : 'text-gray-500' }}">
                <span class="w-8 h-[18px] rounded-full p-0.5 transition-colors flex items-center {{ $brand->is_featured ? 'bg-emerald-600 justify-end' : 'bg-gray-300 justify-start' }}">
                  <span class="w-3.5 h-3.5 rounded-full bg-white shadow-sm block"></span>
                </span>
                {{ $brand->is_featured ? 'Featured' : 'Not featured' }}
              </button>
            </form>
          </div>

          <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="flex-1 h-9 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 text-gray-800 text-xs font-semibold inline-flex items-center justify-center">Edit</a>
            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Delete</button>
            </form>
          </div>
        </article>
      @empty
        <div class="text-center py-12 text-gray-500 text-sm">No brands yet.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
