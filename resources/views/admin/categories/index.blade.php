@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">Categories</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ New category</a>
  </div>

  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-gray-500 bg-gray-50">
        <tr>
          <th class="px-5 py-3 font-medium">Category</th>
          <th class="px-5 py-3 font-medium">Slug</th>
          <th class="px-5 py-3 font-medium">Products</th>
          <th class="px-5 py-3 font-medium">Status</th>
          <th class="px-5 py-3 font-medium">Home Featured</th>
          <th class="px-5 py-3 font-medium text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($categories as $cat)
          <tr class="hover:bg-gray-50">
            <td class="px-5 py-3"><div class="flex items-center gap-3"><img src="{{ $cat->imageUrl() }}" class="h-10 w-10 object-cover rounded-lg bg-gray-100" alt=""><span class="font-medium">{{ $cat->icon }} {{ $cat->name }}</span></div></td>
            <td class="px-5 py-3 text-gray-500">{{ $cat->slug }}</td>
            <td class="px-5 py-3">{{ $cat->products_count }}</td>
            <td class="px-5 py-3">@if($cat->is_active)<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Active</span>@else<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Hidden</span>@endif</td>
            <td class="px-5 py-3">
              <form method="POST" action="{{ route('admin.categories.toggle-featured', $cat) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-full transition {{ $cat->is_featured ? 'bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                  {{ $cat->is_featured ? '★ Featured on Home' : '☆ Enable on Home' }}
                </button>
              </form>
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
              <a href="{{ route('admin.categories.edit', $cat) }}" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Edit</a>
              <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete category and its products?')">@csrf @method('DELETE')<button class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200">Delete</button></form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No categories yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
