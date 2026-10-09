@php
    $user = auth()->user();

    // Menu sections for this role; small related pages share one entry and show as tabs (App\Support\AdminNav).
    $nav = \App\Support\AdminNav::sections($user);

    // Counters, only queried for items this user can see.
    $badgeSources = [
        'orders' => [fn () => \App\Models\Order::needsReview()->count(), 'bg-amber-100 text-amber-800', 'to review'],
        'reviews' => [fn () => \App\Models\ProductReview::pending()->count(), 'bg-sky-100 text-sky-800', 'awaiting approval'],
        'abandoned-carts' => [fn () => \App\Models\AbandonedCart::abandoned()->count(), 'bg-gray-100 text-gray-700', 'to recover'],
        'courier-scan' => [fn () => \App\Models\Order::where('status', 'shipped')
            ->whereIn('courier_status', \App\Services\Courier\CourierStatusUpdater::ATTENTION)->count(), 'bg-amber-100 text-amber-800', 'courier updates need you'],
        'inventory' => [fn () => \App\Models\ProductSku::where('stock_quantity', '<=', 3)->count()
            + \App\Models\Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 3)->count(), 'bg-rose-100 text-rose-800', 'low on stock'],
    ];
    $visibleKeys = collect($nav)->flatten(1)->pluck('key');
    $badges = collect($badgeSources)->only($visibleKeys)->map(fn ($b) => ['count' => ($b[0])(), 'class' => $b[1], 'hint' => $b[2]]);

    $site = site_name();
    $adminName = $user->name ?? 'Admin';
    $initials = collect(explode(' ', trim($adminName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $userRoleTitle = match ($user->role ?? '') {
        'admin' => 'Administrator',
        'store_manager' => 'Store Manager',
        'order_manager' => 'Order Manager',
        'inventory_manager' => 'Inventory Manager',
        default => 'Staff Member',
    };
@endphp

@php
    // "Settings" moves into the Support card; everything else stays in the main menu card.
    $support = $nav['Settings'] ?? [];
    unset($nav['Settings']);
    $itemClass = fn ($on) => $on
        ? 'text-white font-semibold shadow-[0_8px_18px_-10px_rgba(0,0,0,.7)]'
        : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-medium';
@endphp
<aside id="sidebar" class="sb-cards fixed lg:sticky inset-y-0 left-0 lg:top-[84px] z-50 lg:z-auto w-[272px] lg:w-[236px] xl:w-[248px] max-w-[85vw] h-dvh lg:h-auto lg:max-h-[calc(100dvh-100px)] shrink-0 overflow-y-auto overscroll-contain no-scrollbar bg-[#F0EFED] lg:bg-transparent p-3 lg:p-0 flex flex-col gap-3 -translate-x-full lg:translate-x-0 transition-transform duration-200 shadow-2xl lg:shadow-none" aria-label="Admin navigation">
  <div class="lg:hidden flex items-center justify-between px-2 pt-1 pb-1">
    <span class="text-[15px] font-semibold text-gray-900 truncate">{{ $site }}</span>
    <button type="button" id="sidebarClose" class="h-9 w-9 rounded-full bg-white text-gray-600 hover:text-gray-900 flex items-center justify-center shadow-sm" aria-label="Close menu">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Main menu --}}
  <nav class="sidebar-nav bg-white rounded-[24px] shadow-panel px-3 py-4">
    @foreach($nav as $group => $items)
      @php $groupActive = collect($items)->contains(fn ($i) => \App\Support\AdminNav::isOn($i)); @endphp
      <div class="sb-group {{ $loop->first ? '' : 'mt-4' }}" data-group="{{ $group }}" @if($groupActive) data-active @endif>
        <button type="button" class="sb-group-toggle w-full flex items-center justify-between h-6 px-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-gray-400 hover:text-gray-600" aria-expanded="true">
          <span class="sb-label">{{ $group === '' ? 'Home' : $group }}</span>
          <svg class="sb-chevron sb-label w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="sb-items space-y-1">
          @foreach($items as $i)
            @include('admin.partials.sidebar-item', ['i' => $i])
          @endforeach
        </div>
      </div>
    @endforeach
    <p id="sidebarNoResults" class="hidden px-3 py-6 text-center text-[13px] text-gray-400">No matching pages</p>
  </nav>

  {{-- Support --}}
  <div class="bg-white rounded-[24px] shadow-panel px-3 py-4">
    <div class="sb-group" data-group="Support">
      <p class="h-6 px-3 mb-1 flex items-center text-[11px] font-semibold uppercase tracking-[0.1em] text-gray-400">Support</p>
      <div class="sb-items space-y-1">
        @foreach($support as $i)
          @include('admin.partials.sidebar-item', ['i' => $i])
        @endforeach
        <a href="{{ route('shop') }}" target="_blank" rel="noopener" class="sb-item group flex items-center gap-3 h-10 px-3 rounded-full text-[14px] font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" data-label="View store" data-search="view store shop website support">
          <svg class="w-[18px] h-[18px] shrink-0 text-gray-400 group-hover:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/></svg>
          <span class="sb-label flex-1">View store</span>
        </a>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit" class="sb-item group w-full flex items-center gap-3 h-10 px-3 rounded-full text-[14px] font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors cursor-pointer" data-label="Log out" data-search="log out sign out logout support">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
            <span class="sb-label flex-1 text-left">Log out</span>
          </button>
        </form>
      </div>
    </div>
  </div>

</aside>

{{-- Phones and tablets: a bottom bar with four everyday pages for the role and "More", which opens the full menu as a sheet.
     Hidden on POS and the product form, which have their own bottom bars. --}}
@php
  $barItems = collect(\App\Support\AdminNav::bottomBar($user));
  $barOn = $barItems->contains(fn ($i) => \App\Support\AdminNav::isOn($i));
  $showBar = ! request()->routeIs('admin.pos.*', 'admin.products.create', 'admin.products.edit');
@endphp
@if($showBar)
<nav id="bottomNav" class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur-xl border-t border-gray-200/70 pb-[env(safe-area-inset-bottom)]" aria-label="Quick navigation">
  <div class="grid grid-cols-5 h-16 max-w-xl mx-auto">
    @foreach($barItems as $i)
      @php $on = \App\Support\AdminNav::isOn($i); $badge = $badges[$i['key']] ?? null; @endphp
      <a href="{{ route($i['route']) }}" class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium {{ $on ? 'text-gray-900' : 'text-gray-500' }}"
         @if($on) aria-current="page" data-bar-current @endif>
        <span class="relative grid h-8 w-12 place-items-center rounded-full transition-colors {{ $on ? 'text-white' : '' }}" @if($on) style="background: var(--brand-dark);" @endif>
          <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $i['icon'] !!}</svg>
          @if($badge && ($badge['count'] > 0 || $i['key'] === 'orders'))
            <span @if($i['key'] === 'orders') data-live-badge="orders" @endif style="display: {{ $badge['count'] > 0 ? 'contents' : 'none' }}">
              <span class="absolute top-0.5 right-2 h-2 w-2 rounded-full ring-2 ring-white" style="background: var(--brand);" aria-hidden="true"></span>
            </span>
          @endif
        </span>
        <span class="{{ $on ? 'font-semibold' : '' }}">{{ \App\Support\AdminNav::SHORT[$i['key']] ?? $i['label'] }}</span>
      </a>
    @endforeach
    <button type="button" data-bar-more onclick="document.getElementById('menuBtn').click()" class="flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium {{ $barOn ? 'text-gray-500' : 'text-gray-900' }}" aria-label="More pages">
      <span class="grid h-8 w-12 place-items-center rounded-full {{ $barOn ? '' : 'text-white' }}" @unless($barOn) style="background: var(--brand-dark);" @endunless>
        <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </span>
      <span class="{{ $barOn ? '' : 'font-semibold' }}">More</span>
    </button>
  </div>
</nav>
@endif
<div id="sidebarTip" class="hidden fixed z-[60] px-2 py-1 rounded-md bg-gray-900 text-white text-xs font-medium pointer-events-none whitespace-nowrap" role="tooltip"></div>
<script>
  {{-- Restore folded groups before first paint so the menu doesn't jump. --}}
  (function () {
    var folded = [];
    try { folded = JSON.parse(localStorage.getItem('admin.sidebar.folded') || '[]'); } catch (e) {}
    document.querySelectorAll('#sidebar .sb-group[data-group]').forEach(function (g) {
      if (g.dataset.group && folded.indexOf(g.dataset.group) !== -1 && !g.hasAttribute('data-active')) {
        g.classList.add('is-folded');
        var t = g.querySelector('.sb-group-toggle');
        if (t) t.setAttribute('aria-expanded', 'false');
      }
    });
  })();
</script>
