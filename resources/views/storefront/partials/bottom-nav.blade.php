{{-- Phone tab bar: a floating dark capsule with a pill that slides to the active tab. Not on the product page or checkout, which have their own buy bars. --}}
@php
  $tabs = [
    ['key' => 'home', 'label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home'),
     'icon' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>'],
    ['key' => 'shop', 'label' => 'Shop', 'href' => route('shop'), 'active' => request()->routeIs('shop', 'shop.category', 'shop.brand'),
     'icon' => '<rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/>'],
    ['key' => 'cart', 'label' => 'Cart', 'href' => route('cart.index'), 'active' => request()->routeIs('cart.index'), 'cart' => true,
     'icon' => '<path d="M16 11V7a4 4 0 0 0-8 0v4"/><path d="M5 9h14l1 12H4L5 9z"/>'],
    ['key' => 'track', 'label' => 'Track', 'href' => route('track'), 'active' => request()->routeIs('track'),
     'icon' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
    ['key' => 'account', 'label' => auth()->check() ? 'Account' : 'Sign in', 'href' => auth()->check() ? route('account') : route('login'),
     'active' => request()->routeIs('account', 'account.*', 'login', 'register'),
     'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/>'],
  ];
  $activeIndex = collect($tabs)->search(fn ($t) => $t['active']);
@endphp
<style>
  .tabbar{transition:transform .35s cubic-bezier(.2,.8,.2,1),opacity .25s}
  .tabbar.is-tucked{transform:translateY(calc(100% + 24px));opacity:0}
  .tabbar-pill{left:6px;width:calc((100% - 12px) / 5);transform:translateX(calc(var(--i) * 100%));transition:transform .45s cubic-bezier(.34,1.4,.5,1),opacity .2s;will-change:transform}
  .tabbar-tab svg{transition:transform .35s cubic-bezier(.34,1.5,.5,1),color .2s}
  .tabbar-tab .tabbar-label{transition:opacity .2s,transform .3s}
  .tabbar-tab[aria-current] svg{transform:translateY(-1px) scale(1.08)}
  .tabbar-tab:active svg{transform:scale(.86)}
  @keyframes tabbar-bump{0%{transform:scale(1)}40%{transform:scale(1.35)}100%{transform:scale(1)}}
  .tabbar-bump{animation:tabbar-bump .45s ease}
  @media (prefers-reduced-motion:reduce){.tabbar,.tabbar-pill,.tabbar-tab svg{transition:none}}
</style>
<nav data-tabbar class="tabbar md:hidden fixed inset-x-3 z-40 rounded-[22px] text-white shadow-[0_14px_34px_-10px_rgba(43,29,20,.65)] ring-1 ring-white/10"
     style="bottom: calc(12px + env(safe-area-inset-bottom, 0px)); background: var(--brand-dark, #2B1D14);" aria-label="Shop navigation">
  <ul class="relative grid grid-cols-5 h-16 px-1.5">
    <li aria-hidden="true" data-tabbar-pill class="tabbar-pill absolute top-1.5 bottom-1.5 rounded-2xl {{ $activeIndex === false ? 'opacity-0' : '' }}"
        style="--i: {{ $activeIndex === false ? 0 : $activeIndex }}; background: var(--brand-primary, #8B5A2B); box-shadow: inset 0 1px 0 rgba(255,255,255,.18);"></li>
    @foreach($tabs as $i => $tab)
      <li class="relative">
        <a href="{{ $tab['href'] }}" data-tab-index="{{ $i }}" @if(!empty($tab['cart'])) data-open-cart @endif
           class="tabbar-tab relative h-full flex flex-col items-center justify-center gap-1 text-[10.5px] font-semibold tracking-wide {{ $tab['active'] ? 'text-white' : 'text-white/55' }}"
           @if($tab['active']) aria-current="page" @endif>
          <span class="relative">
            <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $tab['active'] ? '2' : '1.7' }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $tab['icon'] !!}</svg>
            @if(!empty($tab['cart']))
              <span class="cart-count absolute -top-1.5 -right-2.5 bg-white text-[10px] font-black h-[18px] min-w-[18px] px-1 rounded-full flex items-center justify-center leading-none {{ ($cartCount ?? 0) ? '' : 'hidden' }}" style="color: var(--brand-dark, #2B1D14); box-shadow: 0 0 0 2px var(--brand-dark, #2B1D14);">{{ $cartCount ?? 0 }}</span>
            @endif
          </span>
          <span class="tabbar-label leading-none">{{ $tab['label'] }}</span>
        </a>
      </li>
    @endforeach
  </ul>
</nav>
{{-- Keeps the footer's last lines clear of the floating tab bar --}}
<div class="md:hidden h-[88px]" style="margin-bottom: env(safe-area-inset-bottom, 0px);" aria-hidden="true"></div>
