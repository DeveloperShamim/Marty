<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85%] -translate-x-full flex-col bg-white shadow-2xl lg:hidden transition-transform duration-300">
  {{-- Header: Clean White matching Main Header --}}
  <div class="flex items-center justify-between p-4 sm:p-5 bg-white border-b border-slate-100 shrink-0">
    @include('partials.brand', ['light' => false, 'size' => 'sm'])
    <button type="button" data-close-menu class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer" aria-label="Close">
      <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- User Profile / Auth State (Clean Light Card) --}}
  <div class="border-b border-slate-100 bg-slate-50/80 p-4 sm:p-5">
    @auth
      <a href="{{ route('account') }}" class="flex items-center gap-3">
        @php
          $initials = collect(preg_split('/\s+/', trim((string) auth()->user()->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
        @endphp
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 border border-brand-200 text-brand-700 font-extrabold text-sm shadow-2xs">
          {{ $initials ?: 'U' }}
        </span>
        <span class="min-w-0 flex-1">
          <span class="block truncate font-extrabold text-sm text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
          <span class="block truncate text-xs text-slate-500 mt-0.5">{{ auth()->user()->email }}</span>
        </span>
      </a>
      <div class="mt-3.5 grid grid-cols-2 gap-2">
        <a href="{{ route('account') }}" class="rounded-xl bg-white px-3 py-2 text-center text-xs font-bold text-slate-800 border border-slate-200 hover:border-brand-500 hover:text-brand-600 shadow-2xs transition-colors">My Account</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full rounded-xl bg-slate-200/80 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 border border-transparent px-3 py-2 text-center text-xs font-bold text-slate-700 transition-colors cursor-pointer">Log out</button>
        </form>
      </div>
    @else
      <div class="space-y-1">
        <p class="font-extrabold text-sm text-slate-900">Welcome to {{ site_name() }} 👋</p>
        <p class="text-xs text-slate-500">{{ setting('store_tagline', 'Discover quality products at the best prices') }}</p>
      </div>
      <div class="mt-3.5 grid grid-cols-2 gap-2">
        <a href="{{ route('login') }}" class="btn-shine rounded-xl bg-brand-600 px-3 py-2 text-center text-xs font-bold text-white hover:bg-brand-700 shadow-2xs transition-colors">Sign in</a>
        <a href="{{ route('register') }}" class="rounded-xl bg-white px-3 py-2 text-center text-xs font-bold text-slate-800 border border-slate-200 hover:bg-slate-50 shadow-2xs transition-colors">Register</a>
      </div>
    @endauth
  </div>

  {{-- Navigation Links & Categories --}}
  <nav class="flex-1 overflow-y-auto px-4 py-3 text-xs sm:text-sm font-semibold space-y-0.5">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 hover:text-brand-600 hover:bg-brand-50/60 transition-colors">
      <span>🏠</span> <span>Home</span>
    </a>
    <a href="{{ route('shop') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 hover:text-brand-600 hover:bg-brand-50/60 transition-colors">
      <span>🛍️</span> <span>Shop All Products</span>
    </a>
    @if($hasFlashSale ?? false)
      <a href="{{ route('shop', ['flash' => 1]) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-amber-700 font-extrabold hover:bg-amber-50 transition-colors">
        <span>⚡</span> <span>Deals &amp; Offers</span>
      </a>
    @endif
    <a href="{{ route('contact') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 hover:text-brand-600 hover:bg-brand-50/60 transition-colors">
      <span>💬</span> <span>Help &amp; Support</span>
    </a>
    <a href="{{ route('account') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-700 hover:text-brand-600 hover:bg-brand-50/60 transition-colors">
      <span>👤</span> <span>My Account</span>
    </a>

    @if(isset($navCategories) && $navCategories->isNotEmpty())
      <div class="mt-4 pt-3 border-t border-slate-100">
        <p class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Main Categories</p>
        <div class="space-y-0.5">
          @foreach($navCategories as $cat)
            <a href="{{ route('shop.category', $cat) }}" class="flex items-center justify-between py-2 px-3 hover:bg-brand-50/70 hover:text-brand-700 rounded-xl text-slate-700 transition-colors">
              <span class="truncate flex items-center gap-2">
                @if($cat->icon)<span>{{ $cat->icon }}</span>@endif
                <span>{{ $cat->name }}</span>
              </span>
              @if(isset($cat->products_count) && $cat->products_count > 0)
                <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full font-mono font-bold">{{ $cat->products_count }}</span>
              @endif
            </a>
          @endforeach
        </div>
      </div>
    @endif

    <div class="mt-6 pt-4 border-t border-slate-100">
      <a href="{{ route('track') }}" class="btn-shine flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-4 text-xs sm:text-sm shadow-2xs transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a2 2 0 104 0m-5-8h2.5"/></svg>
        <span>Track Order</span>
      </a>
    </div>
  </nav>
</aside>
