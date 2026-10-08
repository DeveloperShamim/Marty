@extends('layouts.storefront')

@php $title = 'Shopping Cart'; @endphp

@section('content')
<main class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-5 sm:py-8">
  <nav class="text-xs sm:text-sm text-stone-500 mb-2 flex items-center gap-1.5">
    <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
    <span class="text-stone-300">/</span>
    <span class="text-stone-800 font-semibold">Cart</span>
  </nav>
  <div class="flex items-end justify-between gap-3 mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">Shopping Cart</h1>
    @if($items->isNotEmpty())
      <span class="text-xs sm:text-sm font-semibold text-stone-500">{{ $items->sum('qty') }} {{ Str::plural('item', $items->sum('qty')) }}</span>
    @endif
  </div>

  @if($items->isEmpty())
    <div class="bg-white rounded-2xl border border-stone-200/90 px-6 py-14 sm:py-20 text-center">
      <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      </div>
      <h2 class="text-lg sm:text-xl font-extrabold text-stone-900">Your cart is empty</h2>
      <p class="text-sm text-stone-500 mt-1.5 max-w-sm mx-auto">Looks like you haven't added anything yet. Browse the collection and pick something you'll love.</p>
      <a href="{{ route('shop') }}" class="btn-primary inline-flex items-center gap-2 mt-6 px-6 h-11 rounded-lg text-sm font-bold">
        Start shopping
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
  @else
    <div class="grid lg:grid-cols-3 gap-4 sm:gap-6 items-start">
      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-stone-200/90 divide-y divide-stone-100">
          @foreach($items as $item)
            <div class="flex gap-3 sm:gap-4 p-3 sm:p-5">
              <a href="{{ route('product.show', $item->slug) }}" class="shrink-0">
                <img src="{{ $item->image }}" loading="lazy" decoding="async" class="h-20 w-20 sm:h-24 sm:w-24 rounded-lg object-cover bg-stone-100 border border-stone-100" alt="{{ $item->name }}">
              </a>
              <div class="flex-1 min-w-0 flex flex-col">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <a href="{{ route('product.show', $item->slug) }}" class="font-bold text-sm sm:text-base text-stone-900 hover:text-brand-600 line-clamp-2 leading-snug">{{ $item->name }}</a>
                    @if($item->variant)
                      <p class="text-xs text-stone-500 mt-0.5">{{ $item->variant }}</p>
                    @endif
                    <p class="text-xs sm:text-sm text-stone-500 mt-1">{{ money($item->price) }} each</p>
                  </div>
                  <form method="POST" action="{{ route('cart.remove') }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="key" value="{{ $item->key }}">
                    <button class="h-8 w-8 grid place-items-center rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" aria-label="Remove {{ $item->name }}">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2 -2l1 -12M9 7V4h6v3"/></svg>
                    </button>
                  </form>
                </div>

                <div class="mt-auto pt-3 flex items-center justify-between gap-3">
                  <form method="POST" action="{{ route('cart.update') }}" class="flex items-center rounded-lg border border-stone-200 bg-white overflow-hidden h-9 sm:h-10">
                    @csrf
                    <input type="hidden" name="key" value="{{ $item->key }}">
                    <button type="submit" name="qty" value="{{ max(0, $item->qty - 1) }}" class="w-9 sm:w-10 h-full grid place-items-center text-stone-600 hover:bg-brand-50 hover:text-brand-600 transition-colors cursor-pointer" aria-label="Decrease quantity">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                    </button>
                    <div class="w-9 sm:w-10 h-full grid place-items-center border-x border-stone-200 text-sm font-bold text-stone-800">{{ $item->qty }}</div>
                    <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="w-9 sm:w-10 h-full grid place-items-center text-stone-600 hover:bg-brand-50 hover:text-brand-600 transition-colors cursor-pointer" aria-label="Increase quantity">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
                    </button>
                  </form>
                  <div class="text-right font-extrabold text-sm sm:text-base text-stone-900">{{ money($item->line_total) }}</div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 mt-4 text-brand-600 font-semibold text-sm hover:underline">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 18l-6-6 6-6"/></svg>
          Continue shopping
        </a>
      </div>

      <aside class="lg:sticky lg:top-36">
        <div class="bg-white rounded-2xl border border-stone-200/90 p-5 sm:p-6">
          <h2 class="font-extrabold text-stone-900 text-base sm:text-lg mb-4">Order Summary</h2>
          <div class="space-y-2.5 text-sm">
            <div class="flex justify-between text-stone-600"><span>Subtotal</span><span class="font-semibold text-stone-900">{{ money($subtotal) }}</span></div>
            <div class="flex justify-between text-stone-600"><span>Delivery</span><span class="text-stone-500">Calculated at checkout</span></div>
          </div>
          <div class="flex justify-between items-baseline border-t border-stone-100 mt-4 pt-4">
            <span class="font-bold text-stone-900">Total</span>
            <span class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight">{{ money($subtotal) }}</span>
          </div>
          <a href="{{ route('checkout.show') }}" class="btn-primary flex items-center justify-center gap-2 w-full h-12 rounded-lg mt-5 text-sm sm:text-base font-bold">
            Proceed to Checkout
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <ul class="mt-4 space-y-2 text-xs text-stone-500">
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Secure checkout, your details stay private
            </li>
          </ul>
        </div>
      </aside>
    </div>
  @endif
</main>
@endsection
