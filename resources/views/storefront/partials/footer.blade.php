@php
  $site = site_name();
  $facebook = setting('facebook_url');
  $instagram = setting('instagram_url');
  $twitter = setting('twitter_url');
  $footerText = trim((string) setting('footer_text', ''));
  $phone = trim((string) setting('contact_phone', ''));
  $email = trim((string) setting('contact_email', ''));
  $address = trim((string) setting('contact_address', ''));
  $hours = trim((string) setting('contact_hours', ''));

  $paymentBadges = [];
  if ((string) setting('pay_cod_enabled', '1') === '1') {
      $paymentBadges[] = 'COD';
  }
  if ((string) setting('pay_bkash_enabled', '1') === '1' && setting('bkash_number')) {
      $paymentBadges[] = 'bKash';
  }
  if ((string) setting('pay_nagad_enabled', '1') === '1' && setting('nagad_number')) {
      $paymentBadges[] = 'Nagad';
  }
  if ((string) setting('pay_rocket_enabled', '1') === '1' && setting('rocket_number')) {
      $paymentBadges[] = 'Rocket';
  }
  if ((string) setting('show_cards_in_footer', '0') === '1') {
      $paymentBadges[] = 'Card';
  }

  $whatsapp = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: $phone));
  if (str_starts_with($whatsapp, '0')) {
      $whatsapp = '88' . $whatsapp;
  }
  $footerCats = ($navCategories ?? collect())->filter(fn ($c) => ($c->products_count ?? 1) > 0)->take(6);
  $badgeDots = ['COD' => 'bg-emerald-400', 'bKash' => 'bg-pink-400', 'Nagad' => 'bg-orange-400', 'Rocket' => 'bg-purple-400', 'Card' => 'bg-sky-400'];
  $badgeNames = ['COD' => 'Cash on Delivery', 'Card' => 'Cards'];
  $linkClass = 'block py-1 text-white/70 hover:text-white transition-colors';
  $headClass = 'text-[11px] font-semibold uppercase tracking-[0.14em] text-white/45 mb-3';
@endphp

{{-- Footer: dark brand panel. Brand and ways to reach the shop on the left; Shop, Categories and Help links
     on the right (Categories from sm up); payments and copyright in the bottom bar. --}}
<footer class="mt-16 sm:mt-20 text-white/80 font-sans overflow-hidden" style="background-color: var(--brand-dark, #1c1917);" data-footer>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 pb-8 sm:pt-14 sm:pb-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-9 lg:gap-10">

      <div class="lg:col-span-4 space-y-5">
        @if(has_custom_logo())
          <a href="{{ route('home') }}" class="inline-flex rounded-xl bg-white px-3 py-2" aria-label="{{ $site }}"><img src="{{ logo_url() }}" alt="{{ $site }}" class="h-9 w-auto max-w-[180px] object-contain" /></a>
        @else
          @include('partials.brand', ['size' => 'md', 'light' => true, 'class' => '[&_*]:!text-white'])
        @endif
        @if($footerText !== '')
          <p class="text-sm text-white/60 leading-relaxed max-w-sm">{{ $footerText }}</p>
        @endif

        {{-- Ways to reach the shop --}}
        <div class="flex flex-wrap gap-2">
          @if($phone !== '')
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="inline-flex items-center gap-2 h-10 pl-3 pr-4 rounded-full bg-white text-stone-900 text-sm font-semibold hover:bg-white/90 transition-colors" data-footer-call>
              <svg class="w-4 h-4" style="color: var(--brand-primary, #8B5A2B);" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
              <span class="tabular-nums">{{ $phone }}</span>
            </a>
          @endif
          @if($whatsapp !== '')
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 h-10 px-4 rounded-full border border-white/20 text-sm font-medium text-white hover:bg-white/10 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg>
              WhatsApp
            </a>
          @endif
        </div>

        <div class="space-y-1.5 text-sm text-white/60">
          @if($email !== '')
            <a href="mailto:{{ $email }}" class="block hover:text-white transition-colors">{{ $email }}</a>
          @endif
          @if($address !== '')
            <p class="leading-relaxed max-w-xs">{{ $address }}</p>
          @endif
          @if($hours !== '')
            <p>{{ $hours }}</p>
          @endif
        </div>

        @if($facebook || $instagram || $twitter)
          <div class="flex items-center gap-2">
            @if($facebook)
              <a href="{{ $facebook }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-full border border-white/15 text-white/75 hover:text-white hover:bg-white/10 grid place-items-center transition" aria-label="Facebook"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg></a>
            @endif
            @if($instagram)
              <a href="{{ $instagram }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-full border border-white/15 text-white/75 hover:text-white hover:bg-white/10 grid place-items-center transition" aria-label="Instagram"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg></a>
            @endif
            @if($twitter)
              <a href="{{ $twitter }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-full border border-white/15 text-white/75 hover:text-white hover:bg-white/10 grid place-items-center transition" aria-label="X (Twitter)"><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.59l-5.16-6.74L5.2 22H1.94l8.03-9.17L1.5 2h6.75l4.66 6.18L18.244 2Zm-1.16 18.1h1.83L7.05 3.79H5.09L17.084 20.1Z"/></svg></a>
            @endif
          </div>
        @endif
      </div>

      <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-8 text-sm lg:pl-10">
        <div>
          <p class="{{ $headClass }}">Shop</p>
          <ul>
            <li><a href="{{ route('shop') }}" class="{{ $linkClass }}">All products</a></li>
            <li><a href="{{ route('shop', ['new' => 1]) }}" class="{{ $linkClass }}">New arrivals</a></li>
            <li><a href="{{ route('shop', ['featured' => 1]) }}" class="{{ $linkClass }}">Top selling</a></li>
            @if($hasFlashSale ?? false)
              <li><a href="{{ route('shop', ['flash' => 1]) }}" class="{{ $linkClass }}">Flash deals</a></li>
            @endif
          </ul>
        </div>
        @if($footerCats->isNotEmpty())
          <div class="hidden sm:block">
            <p class="{{ $headClass }}">Categories</p>
            <ul>
              @foreach($footerCats as $cat)
                <li><a href="{{ route('shop.category', $cat) }}" class="{{ $linkClass }} truncate">{{ $cat->name }}</a></li>
              @endforeach
            </ul>
          </div>
        @endif
        <div>
          <p class="{{ $headClass }}">Help</p>
          <ul>
            <li><a href="{{ route('track') }}" class="{{ $linkClass }}">Track your order</a></li>
            <li><a href="{{ route('contact') }}" class="{{ $linkClass }}">Contact us</a></li>
            <li><a href="{{ route('login') }}" class="{{ $linkClass }}">My account</a></li>
            <li><a href="{{ route('terms') }}" class="{{ $linkClass }}">Terms &amp; returns</a></li>
            <li><a href="{{ route('privacy') }}" class="{{ $linkClass }}">Privacy policy</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  {{-- Bottom bar: payments, secure checkout, copyright --}}
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col-reverse lg:flex-row lg:items-center justify-between gap-4 text-xs text-white/50">
      <p>© {{ date('Y') }} {{ $site }}. All rights reserved.</p>
      <div class="flex flex-wrap items-center gap-2">
        @foreach($paymentBadges as $badge)
          <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-white/[0.07] border border-white/10 text-white/75 font-medium">
            <span class="h-1.5 w-1.5 rounded-full {{ $badgeDots[$badge] ?? 'bg-white/60' }}"></span>{{ $badgeNames[$badge] ?? $badge }}
          </span>
        @endforeach
        <span class="inline-flex items-center gap-1.5 h-7 px-2.5 text-white/60">
          <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6-4A12 12 0 0 1 12 2.9 12 12 0 0 1 3.4 6 12 12 0 0 0 3 9c0 5.6 3.8 10.3 9 11.6 5.2-1.3 9-6 9-11.6 0-1-.1-2-.4-3z"/></svg>
          Secure checkout
        </span>
      </div>
    </div>
  </div>

  @if(testing_mode())
    <div class="ws-dev-credit">
      <span>Developed by <strong>WaveSeller</strong></span>
    </div>
  @endif
</footer>
