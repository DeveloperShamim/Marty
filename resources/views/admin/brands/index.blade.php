@extends('layouts.admin')
@section('title', 'Brands')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-xl font-bold">Brands</h2>
      <p class="text-xs text-gray-500 mt-0.5">Manage brand catalog, logos, banners and storefront visibility</p>
    </div>
    <a href="{{ route('admin.brands.create') }}" class="btn-primary">+ New Brand</a>
  </div>

  <!-- Homepage Featured Brands Section Status Banner -->
  @php $sectionEnabled = setting('show_featured_brands', '1') === '1'; @endphp
  <div class="card p-4 bg-white border border-gray-200/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
    <div class="space-y-0.5">
      <div class="flex items-center gap-2">
        <span class="font-extrabold text-sm text-slate-900">🏷️ Storefront Featured Brands Section</span>
        <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full {{ $sectionEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
          {{ $sectionEnabled ? '🟢 Visible on Home Page' : '🔴 Hidden on Home Page' }}
        </span>
      </div>
      <p class="text-xs text-slate-500 font-medium">
        "{{ setting('home_featured_brands_subtitle', 'Shop authentic products directly from leading brands') }}"
      </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}">
        @csrf @method('PUT')
        <input type="hidden" name="show_featured_brands" value="{{ $sectionEnabled ? '0' : '1' }}">
        <button type="submit" class="px-3.5 py-2 text-xs font-extrabold rounded-xl transition cursor-pointer {{ $sectionEnabled ? 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs' }}">
          {{ $sectionEnabled ? '🚫 Disable Homepage Section' : '✓ Enable Homepage Section' }}
        </button>
      </form>
      <a href="{{ route('admin.settings.edit') }}" class="px-3 py-2 text-xs font-bold text-slate-600 hover:text-brand-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition">
        ⚙️ Settings
      </a>
    </div>
  </div>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-gray-500 bg-gray-50">
        <tr>
          <th class="px-5 py-3 font-medium">Brand</th>
          <th class="px-5 py-3 font-medium">Slug</th>
          <th class="px-5 py-3 font-medium">Website</th>
          <th class="px-5 py-3 font-medium">Products</th>
          <th class="px-5 py-3 font-medium">Status</th>
          <th class="px-5 py-3 font-medium">Featured</th>
          <th class="px-5 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($brands as $brand)
          <tr class="hover:bg-gray-50">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <img src="{{ $brand->logoUrl() }}" class="h-10 w-10 object-contain rounded-lg border border-gray-100 bg-white p-1" alt="{{ $brand->name }}">
                <div>
                  <span class="font-medium text-gray-900 block">{{ $brand->name }}</span>
                  @if($brand->banner)
                    <span class="text-[10px] text-gray-400">Banner uploaded</span>
                  @endif
                </div>
              </div>
            </td>
            <td class="px-5 py-3 text-gray-500">{{ $brand->slug }}</td>
            <td class="px-5 py-3 text-gray-500">
              @if($brand->website)
                <a href="{{ $brand->website }}" target="_blank" class="text-indigo-600 hover:underline text-xs">{{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }} &nearr;</a>
              @else
                <span class="text-gray-300">&mdash;</span>
              @endif
            </td>
            <td class="px-5 py-3 font-medium">{{ $brand->products_count }}</td>
            <td class="px-5 py-3">
              @if($brand->is_active)
                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Active</span>
              @else
                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Hidden</span>
              @endif
            </td>
            <td class="px-5 py-3">
              <form method="POST" action="{{ route('admin.brands.toggle-featured', $brand) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-full transition {{ $brand->is_featured ? 'bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                  {{ $brand->is_featured ? '★ Featured' : '☆ Enable' }}
                </button>
              </form>
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap space-x-1">
              <a href="{{ route('admin.brands.edit', $brand) }}" class="px-2.5 py-1 text-xs rounded bg-gray-100 text-gray-600 hover:bg-gray-200">Edit</a>
              <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="inline" onsubmit="return confirm('Delete brand \'{{ $brand->name }}\'? Associated products will un-assign this brand.')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-5 py-10 text-center text-gray-400">No brands found. Create your first brand above!</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
