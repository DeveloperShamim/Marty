@extends('layouts.admin')
@section('title', 'Categories')
@section('subtitle', 'Organise the catalog, store menus and featured home page sections.')

@section('page-actions')
  <a href="{{ route('admin.categories.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    New category
  </a>
@endsection

@php
  $imgFallback = "this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';";
  $featuredCount = $categories->where('is_featured', true)->count();
@endphp

@section('content')
<div class="space-y-4 max-w-full">
  <div class="card overflow-hidden">
    <div class="px-4 py-3 sm:px-5 flex items-center justify-between gap-3">
      <h2 class="text-[15px] font-semibold text-gray-900">All categories</h2>
      <div class="flex items-center gap-1.5 text-[11px] font-semibold">
        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 tabular-nums">{{ $categories->count() }} total</span>
        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 tabular-nums">{{ $featuredCount }} featured</span>
      </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4 w-12 text-center">Pos</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Slug</th>
            <th class="py-3 px-4 text-right">Products</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Home page</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($categories as $cat)
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4 text-center text-gray-400 tabular-nums">{{ $cat->position ?? 0 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-gray-100 overflow-hidden grid place-items-center shrink-0">
                    <img src="{{ $cat->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $cat->name }}" onerror="{{ $imgFallback }}" />
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('admin.categories.edit', $cat) }}" class="font-semibold text-gray-900 hover:underline line-clamp-1">{{ $cat->name }}</a>
                    @if($cat->description)
                      <span class="text-[11px] text-gray-500 line-clamp-1 mt-0.5 max-w-sm">{{ $cat->description }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-500 font-mono text-xs">/{{ $cat->slug }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-900 tabular-nums">{{ number_format($cat->products_count) }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                @if($cat->is_active)
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Active</span>
                @else
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Hidden</span>
                @endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                <form method="POST" action="{{ route('admin.categories.toggle-featured', $cat) }}" class="inline-flex">
                  @csrf @method('PATCH')
                  <button type="submit" class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer {{ $cat->is_featured ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800' }}" title="{{ $cat->is_featured ? 'Click to remove from homepage showcase' : 'Click to feature on homepage' }}">
                    <span class="w-8 h-[18px] rounded-full p-0.5 transition-colors flex items-center {{ $cat->is_featured ? 'bg-emerald-600 justify-end' : 'bg-gray-300 justify-start' }}">
                      <span class="w-3.5 h-3.5 rounded-full bg-white shadow-sm block"></span>
                    </span>
                    {{ $cat->is_featured ? 'Featured' : 'Not featured' }}
                  </button>
                </form>
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.categories.edit', $cat) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">Edit</a>
                  <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete category {{ $cat->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 inline-flex items-center justify-center cursor-pointer" title="Delete" aria-label="Delete {{ $cat->name }}">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-gray-500 text-sm">No categories yet. Use "New category" to add the first one.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: one card per category --}}
    <div class="md:hidden px-3 pb-3 space-y-2.5">
      @forelse($categories as $cat)
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-3">
          <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-white overflow-hidden grid place-items-center shrink-0">
              <img src="{{ $cat->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $cat->name }}" onerror="{{ $imgFallback }}" />
            </div>
            <div class="min-w-0 flex-1">
              <a href="{{ route('admin.categories.edit', $cat) }}" class="font-semibold text-sm text-gray-900 truncate block">{{ $cat->name }}</a>
              <p class="text-[11px] text-gray-500 font-mono truncate mt-0.5">/{{ $cat->slug }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="text-[15px] font-semibold text-gray-900 tabular-nums">{{ $cat->products_count }}</p>
              <p class="text-[11px] text-gray-500">products</p>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            @if($cat->is_active)
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Active</span>
            @else
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Hidden</span>
            @endif
            <form method="POST" action="{{ route('admin.categories.toggle-featured', $cat) }}" class="inline">
              @csrf @method('PATCH')
              <button type="submit" class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer {{ $cat->is_featured ? 'text-gray-900' : 'text-gray-500' }}">
                <span class="w-8 h-[18px] rounded-full p-0.5 transition-colors flex items-center {{ $cat->is_featured ? 'bg-emerald-600 justify-end' : 'bg-gray-300 justify-start' }}">
                  <span class="w-3.5 h-3.5 rounded-full bg-white shadow-sm block"></span>
                </span>
                {{ $cat->is_featured ? 'Featured on home' : 'Not on home' }}
              </button>
            </form>
          </div>

          <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.categories.edit', $cat) }}" class="flex-1 h-9 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 text-gray-800 text-xs font-semibold inline-flex items-center justify-center">Edit</a>
            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete category {{ $cat->name }}?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Delete</button>
            </form>
          </div>
        </article>
      @empty
        <div class="text-center py-12 text-gray-500 text-sm">No categories yet.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
