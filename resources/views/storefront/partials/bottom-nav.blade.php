{{-- Phone tab bar: Home · Shop · Track · Cart · Account. Not on the product page or checkout, which have their own buy bars. --}}
@php
  $tabs = [
    ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home'),
     'icon' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>'],
    ['label' => 'Shop', 'href' => route('shop'), 'active' => request()->routeIs('shop', 'shop.category', 'shop.brand'),
     'icon' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>'],
    ['label' => 'Track', 'href' => route('track'), 'active' => request()->routeIs('track'),
     'icon' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
  ];
  $accountActive = request()->routeIs('account', 'account.*', 'login', 'register');
@endphp
<nav class="bottom-nav md:hidden fixed inset-x-0 bottom-0 z-40 bg-white/95 backdrop-blur-md border-t border-stone-200/90 shadow-[0_-4px_16px_-8px_rgba(0,0,0,0.12)]" style="padding-bottom: env(safe-area-inset-bottom, 0px);" aria-label="Shop navigation">
  <ul class="grid grid-cols-5 h-[60px]">
    @foreach($tabs as $tab)
      <li>
        <a href="{{ $tab['href'] }}" class="relative h-full flex flex-col items-center justify-center gap-0.5 text-[10.5px] font-semibold transition-colors {{ $tab['active'] ? 'text-brand-600' : 'text-stone-500 hover:text-stone-800' }}" @if($tab['active']) aria-current="page" @endif>
          @if($tab['active'])<span class="absolute top-0 inset-x-5 h-[3px] rounded-b-full bg-brand-600"></span>@endif
          <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $tab['active'] ? '2.1' : '1.7' }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $tab['icon'] !!}</svg>
          <span>{{ $tab['label'] }}</span>
        </a>
      </li>
    @endforeach
    <li>
      <button type="button" data-open-cart class="relative h-full w-full flex flex-col items-center justify-center gap-0.5 text-[10.5px] font-semibold text-stone-500 hover:text-stone-800 transition-colors cursor-pointer" aria-label="Cart">
        <span class="relative">
          <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 11V7a4 4 0 0 0-8 0v4"/><path d="M5 9h14l1 12H4L5 9z"/></svg>
          <span class="cart-count absolute -top-1.5 -right-2.5 bg-brand-600 text-white text-[10px] font-black h-[18px] min-w-[18px] px-1 rounded-full flex items-center justify-center ring-2 ring-white leading-none {{ ($cartCount ?? 0) ? '' : 'hidden' }}">{{ $cartCount ?? 0 }}</span>
        </span>
        <span>Cart</span>
      </button>
    </li>
    <li>
      <a href="{{ auth()->check() ? route('account') : route('login') }}" class="relative h-full flex flex-col items-center justify-center gap-0.5 text-[10.5px] font-semibold transition-colors {{ $accountActive ? 'text-brand-600' : 'text-stone-500 hover:text-stone-800' }}" @if($accountActive) aria-current="page" @endif>
        @if($accountActive)<span class="absolute top-0 inset-x-5 h-[3px] rounded-b-full bg-brand-600"></span>@endif
        <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="{{ auth()->check() ? 'currentColor' : 'none' }}" stroke="{{ auth()->check() ? 'none' : 'currentColor' }}" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          @auth
            <circle cx="12" cy="7" r="4.25"/><path d="M4.5 20.25c0-3.9 3.36-6.75 7.5-6.75s7.5 2.85 7.5 6.75c0 .41-.34.75-.75.75H5.25a.75.75 0 0 1-.75-.75Z"/>
          @else
            <circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.5 8.5 0 0 1 13 0"/>
          @endauth
        </svg>
        <span>{{ auth()->check() ? 'Account' : 'Sign in' }}</span>
      </a>
    </li>
  </ul>
</nav>
{{-- Keeps the footer's last lines clear of the tab bar --}}
<div class="md:hidden h-[60px]" style="margin-bottom: env(safe-area-inset-bottom, 0px);" aria-hidden="true"></div>
