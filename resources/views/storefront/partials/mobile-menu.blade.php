<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85%] -translate-x-full flex-col bg-white shadow-2xl lg:hidden transition-transform duration-300">
  <div class="relative border-b border-stone-100 bg-stone-50 p-5">
    <button type="button" data-close-menu class="absolute top-4 right-4 p-1.5 text-stone-400 hover:text-stone-700 hover:bg-stone-200/60 rounded-full transition" aria-label="Close Menu">
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    @auth
      <a href="{{ route('account') }}" class="flex items-center gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-600 text-white font-bold">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>
        <span class="min-w-0">
          <span class="block truncate font-bold text-ink">{{ auth()->user()->name }}</span>
          <span class="block truncate text-xs text-stone-500">{{ auth()->user()->email }}</span>
        </span>
      </a>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="{{ route('account') }}" class="btn-account-outline rounded-xl bg-white px-3 py-2.5 text-center text-sm font-bold text-stone-800 border border-stone-300 hover:bg-brand-600 hover:border-brand-600 hover:!text-white transition-all duration-200 shadow-2xs hover:shadow-md">My Account</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 !text-white hover:!text-white px-3 py-2.5 text-center text-sm font-bold transition-all duration-200 shadow-2xs hover:shadow-md">Log out</button>
        </form>
      </div>
    @else
      <p class="font-bold text-ink">Welcome to {{ site_name() }}</p>
      <p class="mt-1 text-sm text-stone-500">Sign in to continue</p>
      <div class="mt-4 grid grid-cols-2 gap-2">
        <a href="{{ route('login') }}" class="rounded-xl bg-brand-600 hover:bg-brand-700 !text-white hover:!text-white px-3 py-2.5 text-center text-sm font-bold transition-all duration-200 shadow-2xs hover:shadow-md">Sign in</a>
        <a href="{{ route('register') }}" class="btn-account-outline rounded-xl bg-white px-3 py-2.5 text-center text-sm font-bold text-stone-800 border border-stone-300 hover:bg-brand-600 hover:border-brand-600 hover:!text-white transition-all duration-200 shadow-2xs hover:shadow-md">Register</a>
      </div>
    @endauth
  </div>

  <nav class="flex-1 overflow-y-auto px-4 py-3 text-sm font-medium">
    <a href="{{ route('home') }}" class="block py-2.5 px-2.5 rounded-xl font-semibold text-stone-800 hover:text-brand-600 hover:bg-stone-100 transition-all">Home</a>
    <a href="{{ route('shop') }}" class="block py-2.5 px-2.5 rounded-xl font-semibold text-stone-800 hover:text-brand-600 hover:bg-stone-100 transition-all">Shop</a>
    @if($hasFlashSale ?? false)
      <a href="{{ route('shop', ['flash' => 1]) }}" class="block py-2.5 px-2.5 rounded-xl font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 transition-all">⚡ Deals &amp; Offers</a>
    @endif
    <a href="{{ route('login') }}" class="block py-2.5 px-2.5 rounded-xl font-semibold text-stone-800 hover:text-brand-600 hover:bg-stone-100 sm:hidden transition-all">Account</a>

    @if(isset($navCategories) && $navCategories->isNotEmpty())
      <div class="mt-4 pt-3 border-t border-stone-100">
        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-stone-400">Categories</p>
        <div class="space-y-1">
          @foreach($navCategories as $cat)
            <a href="{{ route('shop.category', $cat) }}" class="group flex items-center justify-between py-2 px-2.5 rounded-xl text-stone-800 hover:text-brand-600 hover:bg-stone-100 transition-all">
              <span class="truncate font-semibold text-stone-800 group-hover:text-brand-600">@if($cat->icon)<span class="mr-2">{{ $cat->icon }}</span>@endif{{ $cat->name }}</span>
              @if(isset($cat->products_count) && $cat->products_count > 0)
                <span class="text-[11px] font-bold text-stone-500 bg-stone-100 group-hover:bg-white group-hover:text-stone-800 group-hover:shadow-2xs px-2 py-0.5 rounded-full font-mono transition-all">{{ $cat->products_count }}</span>
              @endif
            </a>
          @endforeach
        </div>
      </div>
    @endif

    @if(isset($navBrands) && $navBrands->isNotEmpty())
      <div class="mt-4 pt-3 border-t border-stone-100">
        <div class="flex items-center justify-between mb-2">
          <p class="text-[11px] font-extrabold uppercase tracking-wider text-stone-400">Featured Brands</p>
          <a href="{{ route('shop') }}" class="text-[11px] font-semibold text-brand-600">View All &rarr;</a>
        </div>
        <div class="grid grid-cols-2 gap-2">
          @foreach($navBrands->take(8) as $b)
            <a href="{{ route('shop.brand', $b) }}" class="group flex items-center gap-2 p-2 rounded-xl border border-stone-200 bg-white hover:border-brand-500 hover:bg-stone-50 hover:shadow-2xs transition-all">
              <img src="{{ $b->logoUrl() }}" class="h-6 w-6 object-contain rounded border border-stone-100 bg-white p-0.5 shrink-0" alt="">
              <span class="text-xs font-bold text-stone-800 group-hover:text-brand-600 truncate">{{ $b->name }}</span>
            </a>
          @endforeach
        </div>
      </div>
    @endif

    <div class="mt-6 pt-4 border-t border-stone-100">
      <a href="{{ route('track') }}" class="btn-track-order flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 !text-white hover:!text-white font-bold py-3 px-4 text-sm shadow-sm transition">
        <svg class="h-4 w-4 !text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/></svg>
        <span class="!text-white">Track Order</span>
      </a>
    </div>
  </nav>
</aside>
