@php
    $pendingBadge = \App\Models\Order::where('payment_status', 'pending')->count();
    $pendingReviews = \App\Models\ProductReview::pending()->count();
    $abandonedBadge = \App\Models\AbandonedCart::abandoned()->count();
    $lowStockBadge = \App\Models\ProductSku::where('stock_quantity', '<=', 3)->count()
        + \App\Models\Product::whereDoesntHave('skus')->where('stock_quantity', '<=', 3)->count();

    $nav = [
        'Operations' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>', 'badge' => $pendingBadge, 'badge_color' => 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
            ['key' => 'abandoned-carts', 'label' => 'Abandoned Carts', 'route' => 'admin.abandoned-carts.index', 'pattern' => 'admin.abandoned-carts.*', 'icon' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>', 'badge' => $abandonedBadge, 'badge_color' => 'bg-amber-50 text-amber-700 border border-amber-200'],
            ['key' => 'reviews', 'label' => 'Customer Reviews', 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 'badge' => $pendingReviews, 'badge_color' => 'bg-sky-50 text-sky-700 border border-sky-200'],
        ],
        'Products & Catalog' => [
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'icon' => '<path d="m7.5 4.27 9 5.15"/><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>'],
            ['key' => 'variations', 'label' => 'Product Variations', 'route' => 'admin.variations.index', 'pattern' => 'admin.variations.*', 'icon' => '<line x1="4" x2="20" y1="9" y2="9"/><line x1="4" x2="20" y1="15" y2="15"/><line x1="10" x2="10" y1="3" y2="21"/><line x1="16" x2="16" y1="3" y2="21"/>'],
            ['key' => 'inventory', 'label' => 'Inventory', 'route' => 'admin.inventory.index', 'pattern' => 'admin.inventory.*', 'icon' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>', 'badge' => $lowStockBadge, 'badge_color' => 'bg-rose-50 text-rose-700 border border-rose-200'],
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'icon' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>'],
            ['key' => 'brands', 'label' => 'Brands', 'route' => 'admin.brands.index', 'pattern' => 'admin.brands.*', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/>'],
            ['key' => 'media', 'label' => 'Media Library', 'route' => 'admin.media.index', 'pattern' => 'admin.media.*', 'icon' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'],
            ['key' => 'size-guide', 'label' => 'Size Guide', 'route' => 'admin.size-guide.index', 'pattern' => 'admin.size-guide.*', 'icon' => '<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>'],
            ['key' => 'features', 'label' => 'Trust Features Strip', 'route' => 'admin.features.index', 'pattern' => 'admin.features.*', 'icon' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>'],
        ],
        'Marketing' => [
            ['key' => 'coupons', 'label' => 'Discount Coupons', 'route' => 'admin.coupons.index', 'pattern' => 'admin.coupons.*', 'icon' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>'],
            ['key' => 'flash-sale', 'label' => 'Flash Sale', 'route' => 'admin.flash-sale.index', 'pattern' => 'admin.flash-sale.*', 'icon' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>'],
            ['key' => 'banners', 'label' => 'Hero Banners', 'route' => 'admin.banners.index', 'pattern' => 'admin.banners.*', 'icon' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>'],
        ],
        'People & Security' => [
            ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['key' => 'staff', 'label' => 'Staff & Roles', 'route' => 'admin.staff.index', 'pattern' => 'admin.staff.*', 'icon' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
            ['key' => 'activity-logs', 'label' => 'Staff Audit Logs', 'route' => 'admin.activity-logs.index', 'pattern' => 'admin.activity-logs.*', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/>'],
            ['key' => 'blacklist', 'label' => 'Fraud Blacklist', 'route' => 'admin.blacklist.index', 'pattern' => 'admin.blacklist.*', 'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/>'],
        ],
        'System Settings' => [
            ['key' => 'profile', 'label' => 'Account & Security', 'route' => 'admin.profile.edit', 'pattern' => 'admin.profile.*', 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
            ['key' => 'integrations', 'label' => 'API Integrations', 'route' => 'admin.integrations.index', 'pattern' => 'admin.integrations.*', 'icon' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'],
            ['key' => 'settings', 'label' => 'Store Configuration', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'icon' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>'],
        ],
    ];
    $user = auth()->user();
    
    // Role-based navigation filtering
    if ($user && !$user->isAdmin()) {
        if ($user->role === 'store_manager') {
            unset($nav['System Settings']);
            $nav['People & Security'] = array_filter($nav['People & Security'], fn($i) => in_array($i['key'], ['customers', 'blacklist'], true));
        } elseif ($user->role === 'order_manager') {
            unset($nav['Products & Catalog'], $nav['Marketing'], $nav['System Settings']);
            $nav['Operations'] = array_filter($nav['Operations'], fn($i) => in_array($i['key'], ['orders', 'abandoned-carts', 'reviews'], true));
            $nav['People & Security'] = array_filter($nav['People & Security'], fn($i) => in_array($i['key'], ['customers', 'blacklist'], true));
        } elseif ($user->role === 'inventory_manager') {
            unset($nav['Marketing'], $nav['System Settings'], $nav['People & Security']);
            $nav['Operations'] = array_filter($nav['Operations'], fn($i) => in_array($i['key'], ['dashboard', 'reviews'], true));
        }
    }

    $site = site_name();
    $adminName = $user->name ?? 'Admin';
    $userRoleTitle = match($user->role ?? '') {
        'admin' => 'Administrator',
        'store_manager' => 'Store Manager',
        'order_manager' => 'Order Manager',
        'inventory_manager' => 'Inventory Manager',
        default => 'Staff Member',
    };
@endphp

<aside id="sidebar" class="fixed lg:sticky lg:top-0 lg:self-start inset-y-0 left-0 z-50 w-64 max-w-[85vw] h-dvh bg-white border-r border-gray-200/80 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 select-none shadow-xl lg:shadow-none">
  {{-- Header: Brand Logo & Live Badge --}}
  <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100 shrink-0 bg-white">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0 group" aria-label="{{ $site }}">
      @if(has_custom_logo())
        <div class="p-1 rounded-xl bg-gray-50/80 border border-gray-100 flex items-center justify-center">
          <img src="{{ logo_url() }}" alt="{{ $site }}" class="max-h-8 max-w-[130px] w-auto object-contain shrink-0 group-hover:scale-105 transition-transform" />
        </div>
      @else
        <div class="h-9 w-9 rounded-xl bg-teal-50 text-teal-800 flex items-center justify-center border border-teal-200/60 shadow-xs shrink-0 group-hover:scale-105 transition-transform overflow-hidden p-1">
          <img src="{{ logo_url() }}" alt="{{ $site }}" class="h-full w-full object-contain" />
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-bold text-sm text-gray-900 truncate tracking-tight leading-none">{{ $site }}</span>
          <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-teal-700 mt-1">
            <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
            <span>Admin Panel</span>
          </span>
        </div>
      @endif
    </a>

    <button type="button" id="sidebarClose" class="lg:hidden h-8 w-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer" aria-label="Close menu">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Navigation Menu (Clean, Modern SaaS Layout) --}}
  <nav class="sidebar-nav flex-1 overflow-y-auto py-3 px-3 space-y-4 text-[13px] font-medium no-scrollbar">
    @foreach($nav as $group => $items)
      <div class="space-y-0.5">
        <div class="px-3 pt-2 pb-1">
          <span class="text-[10.5px] font-bold tracking-wider text-gray-400 uppercase">{{ $group }}</span>
        </div>

        @foreach($items as $item)
          @php 
            $on = request()->routeIs($item['pattern']);
            $badgeColor = $item['badge_color'] ?? 'bg-gray-100 text-gray-700 border border-gray-200';
          @endphp
          <a href="{{ route($item['route']) }}" class="group relative flex items-center justify-between px-3 py-2 rounded-xl transition-all duration-150 {{ $on ? 'bg-teal-50 text-teal-900 font-semibold border border-teal-200/70 shadow-2xs' : 'text-gray-600 hover:bg-gray-100/80 hover:text-gray-900 font-medium' }}">
            <div class="flex items-center gap-2.5 min-w-0">
              <svg class="w-4 h-4 shrink-0 transition-colors {{ $on ? 'text-teal-700' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
              <span class="truncate tracking-tight text-[13px]">{{ $item['label'] }}</span>
            </div>

            @if(!empty($item['badge']) && $item['badge'] > 0)
              <span class="{{ $badgeColor }} text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0 leading-none">
                {{ $item['badge'] }}
              </span>
            @endif
          </a>
        @endforeach
      </div>
    @endforeach
  </nav>

  {{-- Footer: Quick Storefront & Profile Card --}}
  <div class="p-3 border-t border-gray-100 bg-gray-50/50 shrink-0 space-y-2">
    <a href="{{ route('shop') }}" target="_blank" class="w-full py-2 px-3 bg-white hover:bg-gray-50 border border-gray-200/80 rounded-xl text-gray-700 hover:text-gray-900 text-xs font-semibold transition-all shadow-2xs flex items-center justify-between group">
      <span class="flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 14 21 3"/></svg>
        <span>View Online Store</span>
      </span>
      <svg class="w-3 h-3 text-gray-400 group-hover:text-teal-600 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m7 17 10-10M17 17V7H7"/></svg>
    </a>

    <div class="flex items-center gap-2 p-2 bg-white rounded-xl border border-gray-200/80 shadow-2xs">
      <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 flex-1 min-w-0 group/prof" title="Edit Profile & Password">
        @if(auth()->user()?->avatarUrl())
          <img src="{{ auth()->user()->avatarUrl() }}" class="w-8 h-8 rounded-lg object-cover border border-gray-200 shrink-0" alt="{{ $adminName }}">
        @else
          <div class="w-8 h-8 rounded-lg bg-teal-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
            {{ strtoupper(substr($adminName, 0, 2)) }}
          </div>
        @endif
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold text-gray-900 truncate leading-tight group-hover/prof:text-teal-700 transition-colors">{{ $adminName }}</p>
          <p class="text-[10px] font-medium text-gray-500 truncate">{{ $userRoleTitle }}</p>
        </div>
      </a>

      <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
        @csrf
        <button type="submit" class="h-7 w-7 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition-colors cursor-pointer" title="Log out" aria-label="Log out">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </button>
      </form>
    </div>
  </div>
</aside>
