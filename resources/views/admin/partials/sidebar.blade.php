@php
    $pendingBadge = \App\Models\Order::where('payment_status', 'pending')->count();
    $pendingReviews = \App\Models\ProductReview::pending()->count();
    $lowStockBadge = \App\Models\ProductSku::where('stock_quantity', '<=', 3)->count()
        + \App\Models\Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 3)->count();

    $nav = [
        'Main' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => '<path d="M4 13h6v8H4zM14 3h6v18h-6zM4 3h6v6H4z"/>'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => '<circle cx="6" cy="19" r="2"/><circle cx="17" cy="19" r="2"/><path d="M17 17h-11v-14h-2M6 5l14 1l-1 7h-13"/>', 'badge' => $pendingBadge],
            ['key' => 'reviews', 'label' => 'Reviews', 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'icon' => '<path d="M12 3l2.5 5.5L20 9l-4 4l1 6l-5-3l-5 3l1-6l-4-4l5.5-.5z"/>', 'badge' => $pendingReviews],
        ],
        'Catalog' => [
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'icon' => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z M12 12l8-4.5M12 12v9M12 12L4 7.5"/>'],
            ['key' => 'inventory', 'label' => 'Inventory', 'route' => 'admin.inventory.index', 'pattern' => 'admin.inventory.*', 'icon' => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>', 'badge' => $lowStockBadge],
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'icon' => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>'],
            ['key' => 'banners', 'label' => 'Banners', 'route' => 'admin.banners.index', 'pattern' => 'admin.banners.*', 'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/>'],
            ['key' => 'features', 'label' => 'Features', 'route' => 'admin.features.index', 'pattern' => 'admin.features.*', 'icon' => '<path d="M12 3l2.5 5.5L20 9l-4 4l1 6l-5-3l-5 3l1-6l-4-4l5.5-.5z"/>'],
            ['key' => 'coupons', 'label' => 'Coupons', 'route' => 'admin.coupons.index', 'pattern' => 'admin.coupons.*', 'icon' => '<path d="M21 5H3a2 2 0 0 0-2 2v3a2 2 0 0 1 0 4v3a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2v-3a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z"/>'],
            ['key' => 'flash-sale', 'label' => 'Flash Sale', 'route' => 'admin.flash-sale.index', 'pattern' => 'admin.flash-sale.*', 'icon' => '<path d="M13 2 3 14h8l-1 8 10-12h-8l1-8z"/>'],
        ],
        'People' => [
            ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => '<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ],
        'System' => [
            ['key' => 'settings', 'label' => 'Settings', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
        ],
    ];
    $site = site_name();
    $adminName = auth()->user()->name ?? 'Admin';
    $adminEmail = auth()->user()->email ?? '';
@endphp
<aside id="sidebar" class="fixed lg:sticky lg:top-0 lg:self-start inset-y-0 left-0 z-50 w-64 max-w-[88vw] h-dvh bg-white border-r border-gray-200 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200">
  <div class="h-16 flex items-center gap-2 px-5 sm:px-6 border-b border-gray-200 shrink-0 relative">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0 pr-10" aria-label="{{ $site }}">
      <img src="{{ logo_url() }}" alt="" class="h-9 w-9 rounded-lg object-contain shrink-0" width="36" height="36" />
      <span class="font-bold text-lg truncate">{{ $site }}</span>
    </a>
    <button type="button" id="sidebarClose" class="lg:hidden absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100 flex items-center justify-center" aria-label="Close menu">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
  </div>

  <nav class="sidebar-nav flex-1 overflow-y-auto py-4 px-3 text-sm min-h-0">
    @foreach($nav as $group => $items)
      <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2 mt-5 first:mt-0">{{ $group }}</p>
      @foreach($items as $item)
        @php $on = request()->routeIs($item['pattern']); @endphp
        <a href="{{ route($item['route']) }}" class="admin-nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 {{ $on ? 'active bg-primary/10 text-primary font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">{!! $item['icon'] !!}</svg>
          <span>{{ $item['label'] }}</span>
          @if(!empty($item['badge']) && $item['badge'] > 0)
            <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">{{ $item['badge'] }}</span>
          @endif
        </a>
      @endforeach
    @endforeach
  </nav>

  <div class="border-t border-gray-200 p-4 flex items-center gap-3 shrink-0">
    <img src="https://ui-avatars.com/api/?name={{ urlencode($adminName) }}&background=2540e0&color=fff" class="h-9 w-9 rounded-full shrink-0" alt="">
    <div class="min-w-0 flex-1">
      <p class="text-sm font-medium truncate">{{ $adminName }}</p>
      <p class="text-xs text-gray-400 truncate">{{ $adminEmail }}</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
      @csrf
      <button type="submit" class="h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-gray-100 flex items-center justify-center" title="Log out" aria-label="Log out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      </button>
    </form>
  </div>
</aside>
