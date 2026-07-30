@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-bold">Products</h2>
    <a href="{{ route('admin.products.create') }}" class="btn-primary">+ New product</a>
  </div>

  <div class="card">
    <form method="GET" class="p-4 border-b border-gray-200 flex flex-wrap gap-3">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search name or SKU…" class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
      <select name="category" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">All categories</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
      <button class="btn-primary">Search</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500 bg-gray-50">
          <tr><th class="px-5 py-3 font-medium">Product</th><th class="px-5 py-3 font-medium">Category</th><th class="px-5 py-3 font-medium">Price</th><th class="px-5 py-3 font-medium">Stock</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium text-right">Actions</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($products as $product)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-lg bg-gray-100" alt="">
                  <div><p class="font-medium">{{ $product->name }}</p><p class="text-gray-400 text-xs">{{ $product->sku }}</p></div>
                </div>
              </td>
              <td class="px-5 py-3 text-gray-600">{{ $product->category?->name }}</td>
              <td class="px-5 py-3">{{ money($product->price) }} @if($product->on_sale)<span class="text-gray-400 line-through text-xs">{{ money($product->regular_price) }}</span>@endif</td>
              <td class="px-5 py-3">{{ $product->stock_quantity }}</td>
              <td class="px-5 py-3">
                @if($product->is_published)<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Published</span>
                @else<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Draft</span>@endif
              </td>
              <td class="px-5 py-3 text-right whitespace-nowrap">
                <a href="{{ route('admin.products.edit', $product) }}" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Edit</a>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="px-2 py-1 text-xs rounded bg-red-50 text-red-600 border border-red-200">Delete</button></form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No products found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4 border-t border-gray-200">{{ $products->links() }}</div>
  </div>
</div>
@endsection
