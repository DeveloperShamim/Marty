<aside id="cartDrawer" data-cart-drawer class="fixed top-0 right-0 h-full w-full max-w-sm bg-white z-[70] shadow-2xl translate-x-full transition-transform duration-300 flex flex-col">
  <div class="flex items-center justify-between p-5 bg-brand-600 text-white shrink-0">
    <h2 class="font-bold">Your Cart</h2>
    <button type="button" data-close-cart class="p-1 hover:bg-white/10 rounded-lg" aria-label="Close">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>

  <div id="cartItems" class="flex-1 overflow-y-auto p-5 text-sm {{ $cartItems->isEmpty() ? 'hidden' : '' }}">
    @include('storefront.partials.cart-drawer-items', ['items' => $cartItems, 'subtotal' => $cartSubtotal, 'footer' => false])
  </div>

  <div id="cartEmpty" class="flex-1 overflow-y-auto p-5 text-sm text-stone-500 {{ $cartItems->isNotEmpty() ? 'hidden' : '' }}">
    <div class="h-full grid place-items-center text-center">
      <div>
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-brand-50 text-brand-600 mb-4">
          <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h15l-1.5 9h-12L6 6Zm0 0-.7-3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg>
        </div>
        <p class="font-semibold text-ink">Your cart is empty.</p>
        <p class="text-sm mt-1">Add products to get started.</p>
        <button type="button" data-close-cart class="mt-5 rounded-xl bg-brand-600 text-white text-sm font-semibold px-5 py-2.5 hover:bg-brand-700 transition">Continue shopping</button>
      </div>
    </div>
  </div>

  <div class="p-5 border-t border-stone-100 space-y-2 shrink-0">
    <div class="flex justify-between font-bold text-ink">
      <span>Subtotal</span>
      <span id="cartSubtotal" class="cart-total">{{ money($cartSubtotal) }}</span>
    </div>
    <a href="{{ route('checkout.show') }}" class="block text-center bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 rounded-lg transition">Checkout</a>
  </div>
</aside>
