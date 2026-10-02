<header class="h-16 bg-white border-b border-gray-200 flex items-center gap-4 px-4 lg:px-6 sticky top-0 z-20 shrink-0">
  <button type="button" id="menuBtn" class="lg:hidden text-gray-600 -ml-1 h-10 w-10 rounded-lg hover:bg-gray-100 flex items-center justify-center shrink-0" aria-label="Open menu">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
  </button>
  <h1 class="text-lg font-semibold text-ink truncate min-w-0">@yield('title', 'Dashboard')</h1>
  <div class="ml-auto flex items-center gap-3 shrink-0">
    <a href="{{ route('home') }}" target="_blank" class="hidden lg:inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14L21 3"/></svg>
      View store
    </a>
    @if(\App\Support\StaffAccess::allows(auth()->user(), 'orders'))
    @php $reviewCount = \App\Models\Order::needsReview()->count(); @endphp
    <a href="{{ route('admin.orders.index', ['status' => 'pending_verification']) }}" class="relative h-10 w-10 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-600" aria-label="Orders to review" title="Orders to review">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
      {{-- Kept up to date by order-alerts.js --}}
      <span data-live-count class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-amber-500 text-white text-[10px] font-bold leading-4 text-center ring-2 ring-white" @if(! $reviewCount) style="display:none" @endif>{{ $reviewCount > 99 ? '99+' : $reviewCount }}</span>
    </a>
    @endif
    <a href="{{ route('admin.profile.edit') }}" class="hidden sm:inline-flex items-center gap-2 text-sm text-gray-700 hover:text-brand-600 font-medium py-1 px-2 rounded-xl hover:bg-gray-100 transition-colors" title="Edit Profile & Password">
      <span class="hidden xl:inline truncate max-w-[140px] font-bold">{{ auth()->user()->name ?? 'Admin' }}</span>
      <span class="h-8 w-8 shrink-0 bg-teal-700 text-white text-xs font-semibold rounded-full flex items-center justify-center uppercase" aria-hidden="true">{{ collect(explode(' ', trim(auth()->user()->name ?? 'Admin')))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('') }}</span>
    </a>
    @if(\App\Support\StaffAccess::allows(auth()->user(), 'cache'))
    <form method="POST" action="{{ route('admin.cache.clear') }}" class="shrink-0">
      @csrf
      <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-amber-600 border border-gray-200 hover:border-amber-200 rounded-lg px-2.5 sm:px-3 py-1.5 transition-colors bg-white" title="Clear Cache">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
        <span class="hidden xl:inline">Clear Cache</span>
      </button>
    </form>
    @endif
    <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
      @csrf
      <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-red-600 border border-gray-200 hover:border-red-200 rounded-lg px-2.5 sm:px-3 py-1.5 transition-colors bg-white" title="Log out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        <span class="hidden xl:inline">Log out</span>
      </button>
    </form>
  </div>
</header>
