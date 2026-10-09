{{-- ⋯ menu for one product row: view in store and delete (with a confirm) --}}
<details class="order-menu relative" data-product-menu>
  <summary class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 hover:bg-gray-50 inline-flex items-center justify-center cursor-pointer" aria-label="More for {{ $product->name }}">
    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
  </summary>
  <div class="absolute right-0 {{ $up ? 'bottom-full mb-1.5' : 'top-full mt-1.5' }} z-20 w-44 rounded-2xl bg-white shadow-xl ring-1 ring-gray-100 p-1 text-[13px] text-left">
    <a href="{{ route('admin.products.edit', $product) }}" class="block px-3 py-2 rounded-xl hover:bg-gray-50 font-medium text-gray-800 md:hidden">Edit</a>
    @if($product->is_published)
      <a href="{{ route('product.show', $product) }}" target="_blank" class="block px-3 py-2 rounded-xl hover:bg-gray-50 font-medium text-gray-800">View in store</a>
    @endif
    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete {{ addslashes($product->name) }}? This can\'t be undone.')">
      @csrf
      @method('DELETE')
      <button class="w-full text-left px-3 py-2 rounded-xl hover:bg-rose-50 font-medium text-rose-700">Delete</button>
    </form>
  </div>
</details>
