{{-- Phone tab bar, in the admin bar's style. Not on the product page or checkout, which have their own buy bars. --}}
@php
  // As on ghorerbazar.com: Home, Menu (the category menu), Cart, Search (the search box under the header), Account
  $tabs = [
    ['key' => 'home', 'label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home'),
     'icon' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>'],
    ['key' => 'menu', 'label' => 'Menu', 'href' => route('shop'), 'active' => false, 'attr' => 'data-open-menu',
     'icon' => '<rect x="3.5" y="3.5" width="7" height="7" rx="3.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="3.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="3.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="3.5"/>'],
    ['key' => 'cart', 'label' => 'Cart', 'href' => route('cart.index'), 'active' => request()->routeIs('cart.index'), 'cart' => true,
     'icon' => '<path d="M16 11V7a4 4 0 0 0-8 0v4"/><path d="M5 9h14l1 12H4L5 9z"/>'],
    ['key' => 'search', 'label' => 'Search', 'href' => route('shop'), 'active' => false, 'attr' => 'data-toggle-search',
     'icon' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>'],
    ['key' => 'account', 'label' => 'Account', 'href' => auth()->check() ? route('account') : route('login'),
     'active' => request()->routeIs('account', 'account.*', 'login', 'register'),
     'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/>'],
  ];
  $activeIndex = collect($tabs)->search(fn ($t) => $t['active']);
@endphp
<style>
  /* Orange bar with white icons and capital labels, like ghorerbazar.com */
  .tabbar-tab .tabbar-icon{transition:transform .3s cubic-bezier(.34,1.5,.5,1)}
  .tabbar-tab{color:rgba(255,255,255,.9)}
  .tabbar-tab.is-on{color:#fff;font-weight:600}
  .tabbar-tab:active .tabbar-icon{transform:scale(.9)}
  @keyframes tabbar-bump{0%{transform:scale(1)}40%{transform:scale(1.3)}100%{transform:scale(1)}}
  .tabbar-bump{animation:tabbar-bump .45s ease}
  @media (prefers-reduced-motion:reduce){.tabbar-tab .tabbar-icon{transition:none}}
</style>
<nav data-tabbar class="md:hidden fixed inset-x-0 bottom-0 z-40 shadow-[0_-6px_20px_-12px_rgba(0,0,0,.3)]"
     style="padding-bottom: env(safe-area-inset-bottom, 0px); background: var(--brand-primary, #F68822);" aria-label="Shop navigation">
  <ul class="grid grid-cols-5 h-16 max-w-xl mx-auto">
    @foreach($tabs as $i => $tab)
      <li>
        <a href="{{ $tab['href'] }}" data-tab-index="{{ $i }}" @if(!empty($tab['cart'])) data-open-cart @endif @if(!empty($tab['attr'])) {{ $tab['attr'] }} @endif
           class="tabbar-tab {{ $tab['active'] ? 'is-on' : '' }} h-full flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium uppercase tracking-wide"
           @if($tab['active']) aria-current="page" @endif>
          <span class="tabbar-icon relative grid h-8 w-12 place-items-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $tab['icon'] !!}</svg>
            @if(!empty($tab['cart']))
              <span class="cart-count absolute -top-1.5 right-1 text-white text-[10px] font-bold h-[18px] min-w-[18px] px-1 rounded-full flex items-center justify-center leading-none" style="background: #fff; color: #1f1f1f;">{{ $cartCount ?? 0 }}</span>
            @endif
          </span>
          <span class="tabbar-label leading-none">{{ $tab['label'] }}</span>
        </a>
      </li>
    @endforeach
  </ul>
</nav>
{{-- Keeps the footer's last lines clear of the tab bar --}}
<div class="md:hidden h-16" style="margin-bottom: env(safe-area-inset-bottom, 0px);" aria-hidden="true"></div>
