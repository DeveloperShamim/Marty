<header class="h-16 bg-[#F6F3EF]/80 backdrop-blur-xl border-b border-[#ebe5de] flex items-center gap-3 sm:gap-4 px-3 sm:px-4 lg:px-6 sticky top-0 z-20 shrink-0">
  <button type="button" id="menuBtn" class="lg:hidden text-gray-700 h-10 w-10 rounded-xl bg-white border border-[#ebe5de] shadow-sm hover:bg-gray-50 flex items-center justify-center shrink-0" aria-label="Open menu">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h10M4 17h16"/></svg>
  </button>
  <div class="min-w-0">
    <p class="hidden sm:block text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 leading-none mb-1">{{ site_name() }} admin</p>
    <h1 class="text-[17px] sm:text-lg font-bold text-gray-900 tracking-tight truncate leading-tight">@yield('title', 'Dashboard')</h1>
  </div>
  <div class="ml-auto flex items-center gap-1.5 sm:gap-2 shrink-0">
    <a href="{{ route('home') }}" target="_blank" class="hidden lg:inline-flex items-center gap-1.5 h-10 px-3.5 rounded-xl bg-white border border-[#ebe5de] shadow-sm text-sm font-semibold text-gray-700 hover:text-gray-900 hover:border-gray-300 transition-colors">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14L21 3"/></svg>
      View store
    </a>
    @if(\App\Support\StaffAccess::allows(auth()->user(), 'orders'))
    @php $reviewCount = \App\Models\Order::needsReview()->count(); @endphp
    <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="relative h-10 w-10 rounded-xl bg-white border border-[#ebe5de] shadow-sm hover:border-gray-300 flex items-center justify-center text-gray-700 transition-colors" aria-label="Orders to review" title="Orders to review">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
      {{-- Kept up to date by order-alerts.js --}}
      <span data-live-count class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-500 text-white text-[10px] font-bold leading-[18px] text-center ring-2 ring-[#F6F3EF]" @if(! $reviewCount) style="display:none" @endif>{{ $reviewCount > 99 ? '99+' : $reviewCount }}</span>
    </a>
    @endif
    <a href="{{ route('admin.profile.edit') }}" class="hidden sm:inline-flex items-center gap-2 h-10 pl-1 pr-1 xl:pr-3 rounded-xl bg-white border border-[#ebe5de] shadow-sm text-sm text-gray-800 hover:border-gray-300 transition-colors" title="Edit Profile & Password">
      <span class="h-8 w-8 shrink-0 bg-teal-600 text-white text-xs font-bold rounded-lg flex items-center justify-center uppercase" aria-hidden="true">{{ collect(explode(' ', trim(auth()->user()->name ?? 'Admin')))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}</span>
      <span class="hidden xl:inline truncate max-w-[140px] font-semibold">{{ auth()->user()->name ?? 'Admin' }}</span>
    </a>
    @if(\App\Support\StaffAccess::allows(auth()->user(), 'cache'))
    <form method="POST" action="{{ route('admin.cache.clear') }}" class="shrink-0">
      @csrf
      <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 min-w-10 px-2.5 xl:px-3.5 text-sm font-semibold text-gray-700 hover:text-amber-600 bg-white border border-[#ebe5de] shadow-sm hover:border-amber-200 rounded-xl transition-colors" title="Clear Cache">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
        <span class="hidden xl:inline">Clear Cache</span>
      </button>
    </form>
    @endif
    <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
      @csrf
      <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 min-w-10 px-2.5 xl:px-3.5 text-sm font-semibold text-gray-700 hover:text-red-600 bg-white border border-[#ebe5de] shadow-sm hover:border-red-200 rounded-xl transition-colors" title="Log out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        <span class="hidden xl:inline">Log out</span>
      </button>
    </form>
  </div>
</header>
