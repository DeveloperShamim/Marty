@php
  $menuPhone = trim((string) setting('contact_phone', ''));
  $menuWa = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: $menuPhone));
  if (str_starts_with($menuWa, '0')) {
      $menuWa = '88' . $menuWa;
  } elseif ($menuWa !== '' && !str_starts_with($menuWa, '880') && strlen($menuWa) === 10) {
      $menuWa = '880' . $menuWa;
  }
  $quickLinks = [
    ['label' => 'Shop all', 'sub' => 'Every product', 'href' => route('shop'),
     'icon' => '<rect x="3.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="3.5" y="13.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/>'],
    ['label' => 'New in', 'sub' => 'Latest drops', 'href' => route('shop', ['new' => 1]),
     'icon' => '<path d="M12 3l2.2 5.3L20 9l-4.4 3.8L17 18.5 12 15.6 7 18.5l1.4-5.7L4 9l5.8-.7L12 3z"/>'],
    ($hasFlashSale ?? false)
      ? ['label' => 'Flash deals', 'sub' => 'Limited time', 'href' => route('shop', ['flash' => 1]),
         'icon' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/>']
      : ['label' => 'Top selling', 'sub' => 'Most loved picks', 'href' => route('shop', ['featured' => 1]),
         'icon' => '<path d="M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 16.4 6.7 19.4l1.2-6L3.4 9.3l6-.7L12 3z"/>'],
    ['label' => 'Track order', 'sub' => 'Where is my parcel', 'href' => route('track'),
     'icon' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
  ];
  $d = 0;
@endphp
<style>
  .mobile-menu .mm-item{opacity:0;transform:translateX(-14px);transition:opacity .35s ease,transform .45s cubic-bezier(.2,.8,.2,1);transition-delay:0s}
  .mobile-menu:not(.-translate-x-full) .mm-item{opacity:1;transform:none;transition-delay:calc(90ms + var(--d,0) * 35ms)}
  @media (prefers-reduced-motion:reduce){.mobile-menu .mm-item{opacity:1;transform:none;transition:none}}
</style>
<aside id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 z-[60] flex w-[88vw] max-w-[380px] -translate-x-full flex-col overflow-hidden rounded-r-[28px] bg-[#FBF8F5] shadow-2xl lg:hidden transition-transform duration-[450ms] ease-[cubic-bezier(.2,.8,.2,1)] overscroll-contain" aria-label="Menu">
  {{-- Header: leather-dark card with the shopper --}}
  <div class="relative shrink-0 px-5 pt-5 pb-6 text-white" style="background: linear-gradient(140deg, var(--brand-dark, #2B1D14) 0%, var(--brand-dark, #2B1D14) 45%, var(--brand-primary, #8B5A2B) 140%);">
    <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full bg-white/[0.06]"></div>
    <div class="pointer-events-none absolute right-10 -bottom-16 h-32 w-32 rounded-full bg-white/[0.05]"></div>
    <div class="relative flex items-center justify-between">
      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/60">{{ site_name() }}</span>
      <button type="button" data-close-menu class="grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20 ring-1 ring-white/15 transition cursor-pointer" aria-label="Close menu">
        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    @auth
      <a href="{{ route('account') }}" class="mm-item relative mt-5 flex items-center gap-3" style="--d:{{ $d++ }}">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-lg font-extrabold" style="color: var(--brand-dark, #2B1D14);">
          {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>
        <span class="min-w-0 flex-1">
          <span class="block text-xs text-white/60">Hello,</span>
          <span class="block truncate text-lg font-extrabold leading-tight">{{ auth()->user()->name }}</span>
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/15">
          Account
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
        </span>
      </a>
    @else
      <div class="mm-item relative mt-5" style="--d:{{ $d++ }}">
        <p class="text-xl font-extrabold leading-tight">Welcome</p>
        <p class="mt-1 text-sm text-white/65">Sign in to track orders and check out faster.</p>
        <div class="mt-4 grid grid-cols-2 gap-2">
          <a href="{{ route('login') }}" class="h-11 grid place-items-center rounded-xl bg-white text-sm font-bold transition hover:bg-white/90" style="color: var(--brand-dark, #2B1D14);">Sign in</a>
          <a href="{{ route('register') }}" class="h-11 grid place-items-center rounded-xl text-sm font-bold text-white ring-1 ring-white/30 transition hover:bg-white/10">Create account</a>
        </div>
      </div>
    @endauth
  </div>

  <nav class="flex-1 overflow-y-auto overscroll-contain px-4 pt-4 pb-8">
    {{-- Quick tiles --}}
    <div class="grid grid-cols-2 gap-2.5">
      @foreach($quickLinks as $link)
        <a href="{{ $link['href'] }}" class="mm-item group rounded-2xl bg-white p-3.5 ring-1 ring-stone-200/80 shadow-[0_1px_2px_rgba(0,0,0,.04)] transition active:scale-[0.97] hover:ring-brand-300" style="--d:{{ $d++ }}">
          <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $link['icon'] !!}</svg>
          </span>
          <span class="mt-2.5 block text-sm font-bold text-stone-900">{{ $link['label'] }}</span>
          <span class="block text-[11px] text-stone-500">{{ $link['sub'] }}</span>
        </a>
      @endforeach
    </div>

    @if(isset($navCategories) && $navCategories->isNotEmpty())
      <p class="mm-item mt-6 mb-2 px-1 text-[11px] font-extrabold uppercase tracking-[0.14em] text-stone-400" style="--d:{{ $d++ }}">Categories</p>
      <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200/80 divide-y divide-stone-100">
        @foreach($navCategories as $cat)
          <a href="{{ route('shop.category', $cat) }}" class="mm-item group flex items-center gap-3 px-3.5 py-3 transition hover:bg-stone-50 active:bg-brand-50" style="--d:{{ $d++ }}">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-stone-100 text-base">{{ $cat->icon ?: strtoupper(mb_substr($cat->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-stone-800 group-hover:text-brand-600">{{ $cat->name }}</span>
            @if(isset($cat->products_count) && $cat->products_count > 0)
              <span class="text-[11px] font-semibold text-stone-400">{{ $cat->products_count }}</span>
            @endif
            <svg class="h-4 w-4 text-stone-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
          </a>
        @endforeach
      </div>
    @endif

    @if(setting('show_featured_brands', '1') === '1' && isset($navBrands) && $navBrands->isNotEmpty())
      <div class="mm-item mt-6 mb-2 flex items-center justify-between px-1" style="--d:{{ $d++ }}">
        <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-stone-400">Brands</p>
        <a href="{{ route('shop') }}" class="text-xs font-semibold text-brand-600">View all</a>
      </div>
      <div class="mm-item -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none]" style="--d:{{ $d++ }}">
        @foreach($navBrands->take(10) as $b)
          <a href="{{ route('shop.brand', $b) }}" class="flex shrink-0 items-center gap-2 rounded-full bg-white py-1.5 pl-1.5 pr-3.5 ring-1 ring-stone-200/80 transition hover:ring-brand-400">
            <img src="{{ $b->logoUrl() }}" class="h-7 w-7 rounded-full bg-white object-contain p-0.5 ring-1 ring-stone-100" alt="">
            <span class="text-xs font-bold text-stone-800">{{ $b->name }}</span>
          </a>
        @endforeach
      </div>
    @endif

    {{-- Help --}}
    <div class="mm-item mt-6 rounded-2xl bg-brand-50 p-4 ring-1 ring-brand-100" style="--d:{{ $d++ }}">
      <p class="text-sm font-bold text-stone-900">Need help with an order?</p>
      <p class="mt-0.5 text-xs text-stone-600">We reply on WhatsApp and phone.</p>
      <div class="mt-3 grid grid-cols-2 gap-2">
        @if($menuPhone !== '')
          <a href="tel:{{ preg_replace('/\s+/', '', $menuPhone) }}" class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-white text-xs font-bold text-stone-800 ring-1 ring-stone-200 transition hover:ring-brand-400">
            <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.3a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.2l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.2-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.7 21 3 14.3 3 6V5z"/></svg>
            Call
          </a>
        @endif
        @if($menuWa !== '')
          <a href="https://wa.me/{{ $menuWa }}" target="_blank" rel="noopener" class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-white text-xs font-bold text-emerald-700 ring-1 ring-stone-200 transition hover:ring-emerald-300">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3 3 3 0 00-.9 2.2 5.2 5.2 0 001.1 2.7 11.8 11.8 0 004.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 001.8-1.3 2.2 2.2 0 00.2-1.3c-.1-.1-.3-.2-.6-.3z"/></svg>
            WhatsApp
          </a>
        @endif
        @if($menuPhone === '' && $menuWa === '')
          <a href="{{ route('contact') }}" class="col-span-2 inline-flex h-10 items-center justify-center rounded-xl bg-white text-xs font-bold text-stone-800 ring-1 ring-stone-200">Contact us</a>
        @endif
      </div>
    </div>

    @auth
      <form method="POST" action="{{ route('logout') }}" class="mm-item mt-4" style="--d:{{ $d++ }}">
        @csrf
        <button type="submit" class="w-full h-10 rounded-xl text-sm font-semibold text-stone-500 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">Log out</button>
      </form>
    @endauth
  </nav>
</aside>
