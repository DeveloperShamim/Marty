@php
    $pendingBadge = \App\Models\Order::where('payment_status', 'pending')->count();
    $pendingReviews = \App\Models\ProductReview::pending()->count();
    $abandonedBadge = \App\Models\AbandonedCart::abandoned()->count();
    $unreadChatBadge = \App\Models\Conversation::where('status', 'open')->sum('unread_admin_count');
    $lowStockBadge = \App\Models\ProductSku::where('stock_quantity', '<=', 3)->count()
        + \App\Models\Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 3)->count();

    $nav = [
        'Operations' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => '<path d="M4 13h6v8H4zM14 3h6v18h-6zM4 3h6v6H4z"/>'],
            ['key' => 'conversations', 'label' => 'Live Support Chat', 'route' => 'admin.conversations.index', 'pattern' => 'admin.conversations.*', 'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>', 'badge' => $unreadChatBadge, 'badge_color' => 'bg-indigo-100 text-indigo-800 border border-indigo-200'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => '<circle cx="6" cy="19" r="2"/><circle cx="17" cy="19" r="2"/><path d="M17 17h-11v-14h-2M6 5l14 1l-1 7h-13"/>', 'badge' => $pendingBadge, 'badge_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200'],
            ['key' => 'abandoned-carts', 'label' => 'Abandoned Carts', 'route' => 'admin.abandoned-carts.index', 'pattern' => 'admin.abandoned-carts.*', 'icon' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>', 'badge' => $abandonedBadge, 'badge_color' => 'bg-amber-100 text-amber-800 border border-amber-200'],
            ['key' => 'reviews', 'label' => 'Customer Reviews', 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'icon' => '<path d="M12 3l2.5 5.5L20 9l-4 4l1 6l-5-3l-5 3l1-6l-4-4l5.5-.5z"/>', 'badge' => $pendingReviews, 'badge_color' => 'bg-sky-100 text-sky-800 border border-sky-200'],
        ],
        'Products & Inventory' => [
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'icon' => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z M12 12l8-4.5M12 12v9M12 12L4 7.5"/>'],
            ['key' => 'inventory', 'label' => 'Inventory', 'route' => 'admin.inventory.index', 'pattern' => 'admin.inventory.*', 'icon' => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>', 'badge' => $lowStockBadge, 'badge_color' => 'bg-rose-100 text-rose-800 border border-rose-200'],
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'icon' => '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>'],
            ['key' => 'brands', 'label' => 'Brands', 'route' => 'admin.brands.index', 'pattern' => 'admin.brands.*', 'icon' => '<path d="M12 2L2 7l10 5 10-5 10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>'],
            ['key' => 'size-guide', 'label' => 'Size Guide', 'route' => 'admin.size-guide.index', 'pattern' => 'admin.size-guide.*', 'icon' => '<path d="M21 3H3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2V5a2 2 0 0 0-2-2zM3 9h18M9 21V9M15 21V9"/>'],
            ['key' => 'features', 'label' => 'Product Highlights', 'route' => 'admin.features.index', 'pattern' => 'admin.features.*', 'icon' => '<path d="M12 3l2.5 5.5L20 9l-4 4l1 6l-5-3l-5 3l1-6l-4-4l5.5-.5z"/>'],
        ],
        'Marketing' => [
            ['key' => 'coupons', 'label' => 'Discount Coupons', 'route' => 'admin.coupons.index', 'pattern' => 'admin.coupons.*', 'icon' => '<path d="M21 5H3a2 2 0 0 0-2 2v3a2 2 0 0 1 0 4v3a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2v-3a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z"/>'],
            ['key' => 'flash-sale', 'label' => 'Flash Sale', 'route' => 'admin.flash-sale.index', 'pattern' => 'admin.flash-sale.*', 'icon' => '<path d="M13 2 3 14h8l-1 8 10-12h-8l1-8z"/>'],
            ['key' => 'banners', 'label' => 'Hero Banners', 'route' => 'admin.banners.index', 'pattern' => 'admin.banners.*', 'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/>'],
        ],
        'People & Security' => [
            ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => '<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['key' => 'staff', 'label' => 'Staff & Roles', 'route' => 'admin.staff.index', 'pattern' => 'admin.staff.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['key' => 'activity-logs', 'label' => 'Staff Audit Logs', 'route' => 'admin.activity-logs.index', 'pattern' => 'admin.activity-logs.*', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>'],
            ['key' => 'visitors', 'label' => 'Visitor Traffic', 'route' => 'admin.visitors.index', 'pattern' => 'admin.visitors.*', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['key' => 'blacklist', 'label' => 'Fraud Blacklist', 'route' => 'admin.blacklist.index', 'pattern' => 'admin.blacklist.*', 'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
        ],
        'System Settings' => [
            ['key' => 'profile', 'label' => 'My Account & Security', 'route' => 'admin.profile.edit', 'pattern' => 'admin.profile.*', 'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
            ['key' => 'integrations', 'label' => 'API Integrations', 'route' => 'admin.integrations.index', 'pattern' => 'admin.integrations.*', 'icon' => '<path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>'],
            ['key' => 'settings', 'label' => 'Site & Theme Settings', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
        ],
    ];
    $user = auth()->user();
    
    // Role-based navigation filtering
    if ($user && !$user->isAdmin()) {
        if ($user->role === 'store_manager') {
            unset($nav['System Settings']);
            $nav['People & Security'] = array_filter($nav['People & Security'], fn($i) => in_array($i['key'], ['customers', 'visitors', 'blacklist'], true));
        } elseif ($user->role === 'order_manager') {
            unset($nav['Products & Inventory'], $nav['Marketing'], $nav['System Settings']);
            $nav['People & Security'] = array_filter($nav['People & Security'], fn($i) => in_array($i['key'], ['customers', 'visitors', 'blacklist'], true));
        } elseif ($user->role === 'inventory_manager') {
            unset($nav['Marketing'], $nav['System Settings'], $nav['People & Security']);
            $nav['Operations'] = array_filter($nav['Operations'], fn($i) => in_array($i['key'], ['dashboard', 'reviews'], true));
        }
    }

    $site = site_name();
    $adminName = $user->name ?? 'Admin';
    $adminEmail = $user->email ?? '';
    $userRoleTitle = match($user->role ?? '') {
        'admin' => 'Super Admin',
        'store_manager' => 'Store Manager',
        'order_manager' => 'Order Manager',
        'inventory_manager' => 'Inventory Manager',
        default => 'Staff Member',
    };
@endphp

<aside id="sidebar" class="fixed lg:sticky lg:top-0 lg:self-start inset-y-0 left-0 z-50 w-64 max-w-[85vw] h-dvh bg-white border-r border-stone-200/80 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 select-none">
  {{-- Header: Brand & Live Status --}}
  <div class="h-16 flex items-center justify-between px-4 border-b border-stone-100 shrink-0 bg-white">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0 group" aria-label="{{ $site }}">
      @if(has_custom_logo())
        <img src="{{ logo_url() }}" alt="{{ $site }}" class="max-h-9 max-w-[140px] w-auto object-contain shrink-0 group-hover:scale-105 transition-transform" />
      @else
        <div class="h-9 w-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-200/50 shadow-xs shrink-0 group-hover:scale-105 transition-transform overflow-hidden p-1">
          <img src="{{ logo_url() }}" alt="{{ $site }}" class="h-full w-full object-contain" />
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-extrabold text-sm text-stone-900 truncate tracking-tight leading-none">{{ $site }}</span>
          <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 mt-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Online Store</span>
          </span>
        </div>
      @endif
    </a>

    <button type="button" id="sidebarClose" class="lg:hidden h-8 w-8 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 flex items-center justify-center transition-colors" aria-label="Close menu">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Navigation Menu (Light Mode Modern E-Commerce) --}}
  <nav class="sidebar-nav flex-1 overflow-y-auto py-4 px-3 space-y-5 text-[13px] font-medium no-scrollbar">
    @foreach($nav as $group => $items)
      <div class="space-y-1">
        <p class="px-3 text-[10px] font-extrabold tracking-widest text-stone-400 uppercase mb-1.5">{{ $group }}</p>
        @foreach($items as $item)
          @php 
            $on = request()->routeIs($item['pattern']);
            $badgeColor = $item['badge_color'] ?? 'bg-stone-100 text-stone-700 border border-stone-200';
          @endphp
          <a href="{{ route($item['route']) }}" class="group relative flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all duration-150 {{ $on ? 'bg-brand-50 text-brand-700 font-bold border-l-3 border-brand-600' : 'text-stone-700 hover:bg-stone-100/80 hover:text-stone-900' }}">
            <div class="flex items-center gap-3 min-w-0">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 transition-colors {{ $on ? 'text-brand-600' : 'text-stone-400 group-hover:text-stone-700' }}">{!! $item['icon'] !!}</svg>
              <span class="truncate tracking-tight">{{ $item['label'] }}</span>
            </div>

            @if(!empty($item['badge']) && $item['badge'] > 0)
              <span class="{{ $badgeColor }} text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 leading-none shadow-xs">
                {{ $item['badge'] }}
              </span>
            @endif
          </a>
        @endforeach
      </div>
    @endforeach
  </nav>

  {{-- Footer: Quick Storefront & Profile Card --}}
  <div class="p-3 border-t border-stone-100 bg-stone-50/60 shrink-0 space-y-2">
    <a href="{{ route('shop') }}" target="_blank" class="w-full py-2 px-3 bg-white hover:bg-stone-100/90 border border-stone-200 rounded-xl text-stone-700 text-xs font-bold transition-all shadow-xs flex items-center justify-between group">
      <span class="flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span>View Public Website</span>
      </span>
      <span class="text-xs font-bold text-stone-400 group-hover:text-brand-600 transition-colors">↗</span>
    </a>

    <div class="flex items-center gap-2.5 p-2 bg-white rounded-xl border border-stone-200/80 shadow-xs">
      <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 flex-1 min-w-0 group/prof" title="Edit Profile & Password">
        <img src="https://ui-avatars.com/api/?name={{ urlencode($adminName) }}&background=0f766e&color=fff" class="h-8 w-8 rounded-lg shrink-0 object-cover group-hover/prof:scale-105 transition-transform" alt="" />
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold text-stone-900 truncate leading-tight group-hover/prof:text-brand-600 transition-colors">{{ $adminName }}</p>
          <p class="text-[10px] font-medium text-stone-400 truncate">{{ $userRoleTitle }}</p>
        </div>
      </a>

      <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
        @csrf
        <button type="submit" class="h-7 w-7 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors" title="Log out" aria-label="Log out">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </button>
      </form>
    </div>
  </div>
</aside>
