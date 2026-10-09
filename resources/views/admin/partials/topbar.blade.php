@php
  $topUser = auth()->user();
  $topName = $topUser->name ?? 'Admin';
  $topInitials = collect(explode(' ', trim($topName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'A';
  $topRole = match ($topUser->role ?? '') {
      'admin' => 'Administrator',
      'store_manager' => 'Store Manager',
      'order_manager' => 'Order Manager',
      'inventory_manager' => 'Inventory Manager',
      default => 'Staff Member',
  };
@endphp
<header class="sticky top-0 lg:top-3 z-30 -mx-3 sm:mx-0 lg:mt-3 bg-white/90 backdrop-blur-xl sm:rounded-b-[18px] lg:rounded-[18px] shadow-panel flex items-center gap-2 sm:gap-3 h-14 px-3 sm:px-4">
  <button type="button" id="menuBtn" class="hidden h-9 w-9 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center shrink-0 transition-colors" aria-label="Open menu">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h10M4 17h16"/></svg>
  </button>

  <a href="{{ \App\Support\StaffAccess::home(auth()->user()) }}" class="flex items-center gap-2.5 min-w-0 shrink-0" aria-label="{{ site_name() }} admin home">
    @if(has_custom_logo())
      <img src="{{ logo_url() }}" alt="{{ site_name() }}" class="max-h-7 max-w-[140px] w-auto object-contain" />
    @else
      <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-white text-sm font-extrabold shadow-sm" style="background: var(--brand);">{{ mb_strtoupper(mb_substr(site_name(), 0, 1)) }}</span>
      <span class="hidden sm:block text-[15px] font-semibold tracking-tight text-gray-900 truncate">{{ site_name() }}</span>
    @endif
  </a>

  <div class="ml-auto flex items-center gap-1.5 sm:gap-2.5 min-w-0">
    {{-- Page search: filters the menu as you type, Enter opens the first match (admin-shell.js) --}}
    <label class="relative hidden md:block w-56 xl:w-72">
      <span class="sr-only">Search pages</span>
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="search" id="sidebarSearch" autocomplete="off" placeholder="Search pages..."
             class="w-full h-9 pl-10 pr-10 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 focus:ring-4 focus:ring-gray-900/5 outline-none transition" />
      <kbd class="hidden lg:flex absolute right-3 top-1/2 -translate-y-1/2 h-5 min-w-5 px-1 items-center justify-center rounded-md bg-white text-[10px] font-semibold text-gray-400 shadow-sm pointer-events-none">/</kbd>
    </label>

    @if(\App\Support\StaffAccess::allows($topUser, 'orders'))
      @php $reviewCount = \App\Models\Order::needsReview()->count(); @endphp
      <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="relative h-9 w-9 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-800 shrink-0 transition-colors" aria-label="Orders to review" title="Orders to review">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        {{-- Kept up to date by order-alerts.js --}}
        <span data-live-count class="absolute -top-1 -right-1 min-w-[19px] h-[19px] px-1 rounded-full text-white text-[10px] font-bold leading-[19px] text-center ring-2 ring-white" style="background: var(--brand);{{ $reviewCount ? '' : ' display:none' }}">{{ $reviewCount > 99 ? '99+' : $reviewCount }}</span>
      </a>
    @endif

    {{-- Account menu --}}
    <details class="relative group shrink-0" data-account-menu>
      <summary class="list-none [&::-webkit-details-marker]:hidden flex items-center gap-2 h-9 pl-0.5 pr-0.5 sm:pr-2.5 rounded-full border border-gray-200 hover:bg-gray-50 cursor-pointer select-none transition-colors">
        @if($topUser?->avatarUrl())
          <img src="{{ $topUser->avatarUrl() }}" class="h-8 w-8 rounded-full object-cover" alt="">
        @else
          <span class="h-8 w-8 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background: var(--brand-dark);" aria-hidden="true">{{ $topInitials }}</span>
        @endif
        <span class="hidden sm:block text-left leading-tight max-w-[140px]">
          <span class="block text-[12.5px] font-semibold text-gray-900 truncate">{{ $topName }}</span>
          <span class="block text-[10.5px] text-gray-500 truncate">{{ $topRole }}</span>
        </span>
        <svg class="hidden sm:block w-4 h-4 text-gray-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
      </summary>
      <div class="absolute right-0 mt-2 w-56 rounded-2xl bg-white p-1.5 shadow-[0_18px_40px_-16px_rgba(28,25,23,.35)] ring-1 ring-black/5 z-50">
        <div class="px-3 py-2.5 sm:hidden border-b border-gray-100 mb-1">
          <p class="text-sm font-semibold text-gray-900 truncate">{{ $topName }}</p>
          <p class="text-xs text-gray-500">{{ $topRole }}</p>
        </div>
        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
          Profile &amp; password
        </a>
        <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/></svg>
          View store
        </a>
        @if(\App\Support\StaffAccess::allows($topUser, 'cache'))
          <form method="POST" action="{{ route('admin.cache.clear') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 cursor-pointer">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
              Clear cache
            </button>
          </form>
        @endif
        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-gray-100 mt-1 pt-1">
          @csrf
          <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
            Log out
          </button>
        </form>
      </div>
    </details>
  </div>
</header>
