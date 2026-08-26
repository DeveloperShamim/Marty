@php
  $accountUser = auth()->user();
  $lightHeader = $lightHeader ?? false;
@endphp
<div class="relative" data-account-menu>
  <button type="button" data-account-toggle class="flex flex-col items-center justify-center text-center group cursor-pointer focus:outline-none py-0.5 px-1 min-w-[44px]" aria-label="Account menu" aria-expanded="false" aria-haspopup="true">
    @auth
      <div class="relative inline-flex items-center justify-center">
        <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.5 8.5 0 0 1 13 0"/>
        </svg>
        <span class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
      </div>
      <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Account</span>
    @else
      <svg class="w-6 h-6 text-stone-800 group-hover:text-brand-600 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.5 8.5 0 0 1 13 0"/>
      </svg>
      <span class="text-[11px] sm:text-xs font-medium text-stone-700 group-hover:text-brand-600 mt-0.5 tracking-tight whitespace-nowrap">Sign In</span>
    @endauth
  </button>

  <div data-account-dropdown class="hidden absolute right-0 top-full mt-2 w-72 max-w-[calc(100vw-1.5rem)] rounded-2xl border border-slate-100 bg-white shadow-soft z-50 overflow-hidden" role="menu">
    @auth
      <div class="px-4 py-3.5 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-full bg-brand-600 text-white font-display font-bold text-sm shrink-0">
            {{ strtoupper(substr($accountUser->name, 0, 1)) }}
          </span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-ink truncate">{{ $accountUser->name }}</p>
            <p class="text-xs text-slate-500 truncate">{{ $accountUser->email }}</p>
          </div>
        </div>
      </div>
      <div class="p-1.5">
        <a href="{{ route('account') }}" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
          My account
        </a>
        <a href="{{ route('account') }}#orders" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5h11M9 12h11M9 19h11M4 5h.01M4 12h.01M4 19h.01"/></svg>
          My orders
        </a>
        <a href="{{ route('track') }}" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
          Track order
        </a>
      </div>
      <div class="border-t border-slate-100 p-1.5">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" role="menuitem" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-red-600 hover:bg-red-50">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Log out
          </button>
        </form>
      </div>
    @else
      <div class="px-4 py-3.5 bg-gradient-to-br from-brand-50 to-white border-b border-slate-100">
        <p class="text-sm font-semibold text-ink">Welcome to {{ site_name() }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Sign in to continue</p>
      </div>
      <div class="p-3 space-y-2">
        <a href="{{ route('login') }}" role="menuitem" class="flex items-center justify-center rounded-xl bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Sign in</a>
        <a href="{{ route('register') }}" role="menuitem" class="flex items-center justify-center rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-ink ring-1 ring-slate-200 hover:bg-brand-50">Create account</a>
      </div>
      <div class="border-t border-slate-100 p-1.5">
        <a href="{{ route('track') }}" role="menuitem" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-800">
          <svg class="h-4 w-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
          Track order
        </a>
      </div>
    @endauth
  </div>
</div>
