<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85%] -translate-x-full flex-col bg-white shadow-2xl lg:hidden transition-transform duration-300">
  <div class="flex items-center justify-between p-5 bg-emerald-800 text-white shrink-0">
    @include('partials.brand', ['light' => true, 'size' => 'sm'])
    <button type="button" data-close-menu class="p-2 hover:bg-white/10 rounded-lg" aria-label="Close">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
  </div>

  <div class="border-b border-stone-100 bg-emerald-50/50 p-5">
    @auth
      <a href="{{ route('account') }}" class="flex items-center gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-emerald-700 text-white font-bold">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>
        <span class="min-w-0">
          <span class="block truncate font-bold text-ink">{{ auth()->user()->name }}</span>
          <span class="block truncate text-xs text-stone-500">{{ auth()->user()->email }}</span>
        </span>
      </a>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="{{ route('account') }}" class="rounded-xl bg-white px-3 py-2.5 text-center text-sm font-semibold text-ink ring-1 ring-stone-200 hover:bg-emerald-50">My Account</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full rounded-xl bg-emerald-700 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-800">Log out</button>
        </form>
      </div>
    @else
      <p class="font-bold text-stone-900">Welcome to {{ site_name() }} 🌿</p>
      <p class="mt-1 text-xs text-stone-500">100% Chemical-Free Organic Groceries</p>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="{{ route('login') }}" class="rounded-xl bg-emerald-700 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-800">Sign in</a>
        <a href="{{ route('register') }}" class="rounded-xl bg-white px-3 py-2.5 text-center text-sm font-semibold text-ink ring-1 ring-stone-200 hover:bg-emerald-50">Register</a>
      </div>
    @endauth
  </div>

  <nav class="flex-1 overflow-y-auto px-4 py-3 text-sm font-medium">
    <a href="{{ route('home') }}" class="block py-2.5 hover:text-emerald-700">🏠 Home</a>
    <a href="{{ route('shop') }}" class="block py-2.5 hover:text-emerald-700">🌿 Shop All Products</a>
    @if($hasFlashSale ?? false)
      <a href="{{ route('shop', ['flash' => 1]) }}" class="block py-2.5 text-amber-700 font-bold hover:text-amber-800">⚡ Deals &amp; Offers</a>
    @endif
    <a href="{{ route('contact') }}" class="block py-2.5 hover:text-emerald-700">Help &amp; Support</a>
    <a href="{{ route('login') }}" class="block py-2.5 hover:text-emerald-700 sm:hidden">Account</a>

    @if(isset($navCategories) && $navCategories->isNotEmpty())
      <div class="mt-4 pt-3 border-t border-stone-100">
        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-emerald-800">Organic Categories</p>
        <div class="space-y-1">
          @foreach($navCategories as $cat)
            <a href="{{ route('shop.category', $cat) }}" class="flex items-center justify-between py-2 px-2 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-colors">
              <span class="truncate">@if($cat->icon)<span class="mr-2">{{ $cat->icon }}</span>@endif{{ $cat->name }}</span>
              @if(isset($cat->products_count) && $cat->products_count > 0)
                <span class="text-[10px] text-stone-400 bg-stone-100 px-1.5 py-0.5 rounded-full font-mono">{{ $cat->products_count }}</span>
              @endif
            </a>
          @endforeach
        </div>
      </div>
    @endif

    @if(isset($navBrands) && $navBrands->isNotEmpty())
      <div class="mt-4 pt-3 border-t border-stone-100">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-800">Trusted Brands</p>
          <a href="{{ route('shop') }}" class="text-[11px] font-semibold text-emerald-700">View All &rarr;</a>
        </div>
        <div class="grid grid-cols-2 gap-2">
          @foreach($navBrands->take(8) as $b)
            <a href="{{ route('shop.brand', $b) }}" class="flex items-center gap-2 p-2 rounded-xl border border-stone-100 hover:border-emerald-500/40 hover:bg-emerald-50 transition-all">
              <img src="{{ $b->logoUrl() }}" class="h-6 w-6 object-contain rounded border border-stone-100 bg-white p-0.5 shrink-0" alt="">
              <span class="text-xs font-bold text-ink truncate">{{ $b->name }}</span>
            </a>
          @endforeach
        </div>
      </div>
    @endif

    <div class="mt-6 pt-4 border-t border-stone-100">
      <a href="{{ route('track') }}" class="flex items-center justify-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3 px-4 text-sm shadow-sm transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/></svg>
        Track Order
      </a>
    </div>
  </nav>
</aside>
