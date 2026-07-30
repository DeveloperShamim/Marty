@extends('layouts.storefront')

@php $title = 'Shopping Cart'; @endphp

@section('content')
<main class="max-w-[1440px] mx-auto px-5 py-6">
  <h1 class="text-2xl font-extrabold mb-5">Shopping Cart</h1>

  @if($items->isEmpty())
    <div class="bg-white rounded-md p-16 text-center">
      <p class="text-gray-500">Your cart is empty.</p>
      <a href="{{ route('shop') }}" class="inline-block mt-4 text-brand-600 font-medium text-sm">← Continue shopping</a>
    </div>
  @else
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="bg-white rounded-md divide-y divide-gray-100">
          @foreach($items as $item)
            <div class="flex items-center gap-4 p-4">
              <img src="{{ $item->image }}" loading="lazy" decoding="async" class="h-20 w-20 rounded-md object-contain border border-gray-100" alt="{{ $item->name }}">
              <div class="flex-1 min-w-0">
                <a href="{{ route('product.show', $item->slug) }}" class="font-medium hover:text-brand-600">{{ $item->name }}</a>
                <p class="text-accent-600 font-bold mt-1">{{ money($item->price) }}</p>
              </div>
              <!-- <form method="POST" action="{{ route('cart.update') }}" class="flex items-center border border-gray-300 rounded overflow-hidden">
                @csrf
                <input type="hidden" name="key" value="{{ $item->key }}">
                <button name="qty" value="{{ max(0, $item->qty - 1) }}" class="px-3 py-2 text-gray-600">−</button>
                <input value="{{ $item->qty }}" readonly class="w-10 text-center border-0 focus:ring-0 bg-transparent">
                <button name="qty" value="{{ $item->qty + 1 }}" class="px-3 py-2 text-gray-600">+</button>
              </form> -->

<form method="POST"
      action="{{ route('cart.update') }}"
      class="flex items-center rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden h-10">

    @csrf
    <input type="hidden" name="key" value="{{ $item->key }}">

    <button
        type="submit"
        name="qty"
        value="{{ max(0, $item->qty - 1) }}"
        class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-4 h-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
        </svg>
    </button>

    <div class="w-10 h-10 flex items-center justify-center border-x border-gray-200 font-semibold text-gray-800">
        {{ $item->qty }}
    </div>

    <button
        type="submit"
        name="qty"
        value="{{ $item->qty + 1 }}"
        class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-4 h-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
        </svg>
    </button>

</form>


              <div class="w-16 sm:w-20 text-right font-semibold">{{ money($item->line_total) }}</div>
              <form method="POST" action="{{ route('cart.remove') }}">
                @csrf
                <input type="hidden" name="key" value="{{ $item->key }}">
                <button class="text-gray-400 hover:text-accent-500" aria-label="Remove">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2l1 -12M9 7V4h6v3"/></svg>
                </button>
              </form>
            </div>
          @endforeach
        </div>
        <a href="{{ route('shop') }}" class="inline-block mt-4 text-brand-600 font-medium text-sm">← Continue shopping</a>
      </div>
      <div>
        <div class="bg-white rounded-md p-6 sticky top-4">
          <h2 class="font-bold mb-4">Order Summary</h2>
          <div class="flex justify-between text-gray-600 mb-2"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
          <div class="flex justify-between text-gray-600 mb-2"><span>Shipping</span><span>Calculated at checkout</span></div>
          <div class="flex justify-between font-bold text-lg border-t pt-4"><span>Total</span><span class="text-accent-600">{{ money($subtotal) }}</span></div>
          <a href="{{ route('checkout.show') }}" class="block text-center bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-md mt-6">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  @endif
</main>
@endsection
