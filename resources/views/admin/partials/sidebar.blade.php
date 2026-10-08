@php
    $user = auth()->user();

    // key, label, route, active pattern, icon (Lucide paths), search keywords
    $item = fn ($key, $label, $route, $icon, $keywords = '') => [
        'key' => $key, 'label' => $label, 'route' => $route,
        // admin.orders.index → admin.orders.* (covers show/edit/create); admin.dashboard stays exact.
        'pattern' => preg_match('/\.(index|edit)$/', $route) ? \Illuminate\Support\Str::beforeLast($route, '.') . '.*' : $route,
        'icon' => $icon, 'keywords' => $keywords,
    ];

    $nav = [
        '' => [
            $item('dashboard', 'Dashboard', 'admin.dashboard', '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>', 'home overview'),
        ],
        'Sales' => [
            $item('orders', 'Orders', 'admin.orders.index', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>', 'sales invoice'),
            $item('courier-scan', 'Courier Scan', 'admin.courier-scan.index', '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>', 'delivery dispatch return shipping in out'),
            $item('pos', 'POS Register', 'admin.pos.index', '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8"/><path d="M16 14v4"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/>', 'cash register point of sale counter'),
        ],
        'Catalog' => [
            $item('products', 'Products', 'admin.products.index', '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>', 'items add product'),
            $item('inventory', 'Inventory', 'admin.inventory.index', '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/>', 'stock restock low stock warehouse'),
            $item('categories', 'Categories', 'admin.categories.index', '<path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>', 'collections'),
            $item('brands', 'Brands', 'admin.brands.index', '<path d="M12.59 2.59A2 2 0 0 0 11.17 2H4a2 2 0 0 0-2 2v7.17a2 2 0 0 0 .59 1.42l8.7 8.7a2.43 2.43 0 0 0 3.42 0l6.58-6.58a2.43 2.43 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>', 'manufacturer producer'),
            $item('variations', 'Variations', 'admin.variations.index', '<path d="M21 4h-7"/><path d="M10 4H3"/><path d="M21 12h-9"/><path d="M8 12H3"/><path d="M21 20h-5"/><path d="M12 20H3"/><path d="M14 2v4"/><path d="M8 10v4"/><path d="M16 18v4"/>', 'size color attributes options'),
            $item('barcodes', 'Product Barcodes', 'admin.barcodes.index', '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 7v10"/><path d="M12 7v10"/><path d="M17 7v10"/>', 'barcode labels print sticker'),
            $item('reviews', 'Reviews', 'admin.reviews.index', '<path d="M11.52 2.3a.53.53 0 0 1 .95 0l2.31 4.68a2.12 2.12 0 0 0 1.6 1.16l5.16.76a.53.53 0 0 1 .3.9l-3.74 3.64a2.12 2.12 0 0 0-.61 1.88l.88 5.14a.53.53 0 0 1-.77.56l-4.62-2.43a2.12 2.12 0 0 0-1.97 0L6.4 21.01a.53.53 0 0 1-.77-.56l.88-5.14a2.12 2.12 0 0 0-.61-1.88L2.16 9.79a.53.53 0 0 1 .3-.9l5.16-.76a2.12 2.12 0 0 0 1.6-1.16z"/>', 'customer ratings feedback'),
        ],
        'Storefront' => [
            $item('banners', 'Hero Banners', 'admin.banners.index', '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="m2 15 5-4 4 3 4-5 7 6"/>', 'slider homepage'),
            $item('features', 'Trust Strip', 'admin.features.index', '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>', 'trust features badges'),
            $item('size-guide', 'Size Guide', 'admin.size-guide.index', '<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>', 'chart measurement'),
            $item('media', 'Media Library', 'admin.media.index', '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>', 'images photos uploads files'),
        ],
        'Marketing' => [
            $item('coupons', 'Coupons', 'admin.coupons.index', '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>', 'discount promo code'),
            $item('flash-sale', 'Flash Sale', 'admin.flash-sale.index', '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>', 'deal offer countdown'),
            $item('free-delivery', 'Free Delivery', 'admin.free-delivery.index', '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>', 'shipping offer courier charge waive'),
            $item('news-ticker', 'News Ticker', 'admin.news-ticker.index', '<path d="M15 18h-5"/><path d="M18 14h-8"/><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="10" y="6" rx="1"/>', 'headline announcement promo bar top banner'),
            $item('abandoned-carts', 'Abandoned Carts', 'admin.abandoned-carts.index', '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>', 'cart recovery'),
        ],
        'Finance' => [
            $item('analytics', 'Profit & Analytics', 'admin.analytics.index', '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/>', 'report aov revenue sales profit'),
            $item('expenses', 'Expenses', 'admin.expenses.index', '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>', 'costs facebook ads fb spend'),
        ],
        'People' => [
            $item('customers', 'Customers', 'admin.customers.index', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'buyers clients'),
            $item('staff', 'Staff & Roles', 'admin.staff.index', '<path d="M2 21a8 8 0 0 1 13.29-6"/><circle cx="10" cy="8" r="5"/><path d="m16 19 2 2 4-4"/>', 'team users permissions'),
            $item('activity-logs', 'Audit Log', 'admin.activity-logs.index', '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>', 'activity history staff logs'),
            $item('blacklist', 'Blacklist', 'admin.blacklist.index', '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>', 'fraud block ban phone'),
        ],
        'Settings' => [
            $item('settings', 'Store Settings', 'admin.settings.edit', '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>', 'configuration logo shipping payment'),
            $item('integrations', 'Integrations', 'admin.integrations.index', '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"/>', 'api courier steadfast pathao sms pixel'),
        ],
    ];

    // Each role only sees the pages it may open (same rules as the routes: App\Support\StaffAccess).
    $allowed = fn ($key) => \App\Support\StaffAccess::allows($user, $key);
    $nav = array_filter(array_map(fn ($items) => array_values(array_filter($items, fn ($i) => $allowed($i['key']))), $nav));

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
    $reviewWaiting = ($badges['orders']['count'] ?? 0);
    $itemClass = fn ($on) => $on
        ? 'text-white font-semibold shadow-[0_8px_18px_-10px_rgba(0,0,0,.7)]'
        : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-medium';
@endphp
<aside id="sidebar" class="sb-cards fixed lg:sticky inset-y-0 left-0 lg:top-[96px] z-50 lg:z-auto w-[272px] lg:w-[236px] xl:w-[248px] max-w-[85vw] h-dvh lg:h-auto lg:max-h-[calc(100dvh-112px)] shrink-0 overflow-y-auto overscroll-contain no-scrollbar bg-[#F0EFED] lg:bg-transparent p-3 lg:p-0 flex flex-col gap-3 -translate-x-full lg:translate-x-0 transition-transform duration-200 shadow-2xl lg:shadow-none" aria-label="Admin navigation">
  <div class="lg:hidden flex items-center justify-between px-2 pt-1 pb-1">
    <span class="text-[15px] font-semibold text-gray-900 truncate">{{ $site }}</span>
    <button type="button" id="sidebarClose" class="h-9 w-9 rounded-full bg-white text-gray-600 hover:text-gray-900 flex items-center justify-center shadow-sm" aria-label="Close menu">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Main menu --}}
  <nav class="sidebar-nav bg-white rounded-[24px] shadow-panel px-3 py-4">
    @foreach($nav as $group => $items)
      @php $groupActive = collect($items)->contains(fn ($i) => request()->routeIs($i['pattern'])); @endphp
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

  {{-- Highlight card --}}
  @if(isset($badges['orders']))
    <div class="relative overflow-hidden rounded-[24px] p-4 text-white shadow-panel" style="background: linear-gradient(145deg, var(--brand) 0%, color-mix(in srgb, var(--brand) 70%, var(--brand-dark)) 100%);">
      <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></div>
      <div class="pointer-events-none absolute right-6 -bottom-10 h-20 w-20 rounded-full bg-white/[0.07]"></div>
      <div class="relative flex items-center gap-2.5">
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/15 ring-2 ring-white/25">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </span>
        <span class="min-w-0">
          <span class="block text-sm font-semibold leading-tight">{{ $reviewWaiting > 0 ? 'Orders to review' : 'All caught up' }}</span>
          <span class="block text-[11px] text-white/70">{{ $adminName }}</span>
        </span>
      </div>
      <p class="relative mt-3 text-xs leading-relaxed text-white/85">
        {{ $reviewWaiting > 0 ? 'You have ' . $reviewWaiting . ' ' . \Illuminate\Support\Str::plural('order', $reviewWaiting) . ' waiting for payment or phone verification.' : 'No orders are waiting for verification right now.' }}
      </p>
      <a href="{{ route('admin.orders.index', $reviewWaiting > 0 ? ['status' => 'pending_verification'] : []) }}" class="relative mt-3 inline-flex items-center h-8 px-3.5 rounded-full text-[11px] font-semibold text-white transition hover:opacity-90" style="background: var(--brand-dark);">
        {{ $reviewWaiting > 0 ? 'Review orders' : 'Open orders' }}
      </a>
    </div>
  @endif
</aside>
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
