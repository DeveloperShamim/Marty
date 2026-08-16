@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="space-y-4 sm:space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-3.5">
    <div>
      <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
        <span>📂</span> Store Categories
      </h2>
      <p class="text-xs text-slate-500 mt-0.5">Organize product catalog, home page featured sections, and menus.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary shrink-0 self-start sm:self-auto flex items-center justify-center gap-1">
      <span>+ New Category</span>
    </a>
  </div>

  <div class="card bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-xs text-slate-500 bg-slate-50 font-bold uppercase tracking-wider border-b border-gray-200">
          <tr>
            <th class="px-5 py-3.5">Category</th>
            <th class="px-5 py-3.5">Slug</th>
            <th class="px-5 py-3.5 text-center">Products</th>
            <th class="px-5 py-3.5">Status</th>
            <th class="px-5 py-3.5">Home Featured</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-slate-700">
          @forelse($categories as $cat)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <img src="{{ $cat->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl bg-gray-100 border border-gray-200 shrink-0" alt="">
                  <span class="font-bold text-slate-900">{{ $cat->icon }} {{ $cat->name }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">{{ $cat->slug }}</td>
              <td class="px-5 py-3.5 text-center font-extrabold text-slate-800 font-mono">{{ $cat->products_count }}</td>
              <td class="px-5 py-3.5">
                @if($cat->is_active)
                  <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                @else
                  <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-gray-100 text-gray-500 border border-gray-200">Hidden</span>
                @endif
              </td>
              <td class="px-5 py-3.5">
                <form method="POST" action="{{ route('admin.categories.toggle-featured', $cat) }}" class="inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="px-3 py-1 text-xs font-bold rounded-full transition cursor-pointer {{ $cat->is_featured ? 'bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200' : 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200' }}">
                    {{ $cat->is_featured ? '★ Featured on Home' : '☆ Enable on Home' }}
                  </button>
                </form>
              </td>
              <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <a href="{{ route('admin.categories.edit', $cat) }}" class="px-3 py-1 text-xs font-bold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 transition">Edit</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline ml-1" onsubmit="return confirm('Delete category {{ $cat->name }} and its product associations?')">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1 text-xs font-bold rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition cursor-pointer">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400 text-xs">No categories created yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card List View --}}
    <div class="block sm:hidden divide-y divide-gray-100 bg-white">
      @forelse($categories as $cat)
        <div class="p-3.5 space-y-3">
          <div class="flex items-center gap-3">
            <img src="{{ $cat->imageUrl() }}" class="h-12 w-12 object-cover rounded-xl bg-gray-100 border border-gray-200 shrink-0" alt="">
            <div class="min-w-0 flex-1">
              <h4 class="font-extrabold text-sm text-slate-900 truncate">{{ $cat->icon }} {{ $cat->name }}</h4>
              <p class="text-[11px] text-slate-400 font-mono truncate mt-0.5">/{{ $cat->slug }}</p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-xs font-extrabold font-mono text-slate-900 block">{{ $cat->products_count }}</span>
              <span class="text-[10px] text-slate-400 font-semibold block">Products</span>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
            <div>
              @if($cat->is_active)
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
              @else
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-500 border border-gray-200">Hidden</span>
              @endif
            </div>

            <form method="POST" action="{{ route('admin.categories.toggle-featured', $cat) }}" class="inline">
              @csrf @method('PATCH')
              <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-full transition cursor-pointer {{ $cat->is_featured ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                {{ $cat->is_featured ? '★ Featured Home' : '☆ Enable Home' }}
              </button>
            </form>
          </div>

          <div class="flex items-center justify-end gap-1.5 pt-1">
            <a href="{{ route('admin.categories.edit', $cat) }}" class="px-3.5 py-1 text-xs font-bold rounded-xl bg-gray-100 hover:bg-gray-200 text-slate-700 transition">Edit</a>
            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete category {{ $cat->name }}?')">
              @csrf @method('DELETE')
              <button class="px-3.5 py-1 text-xs font-bold rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">Delete</button>
            </form>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-slate-400">No categories created yet.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
