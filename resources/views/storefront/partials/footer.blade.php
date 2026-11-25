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

{{-- Footer, as on ghorerbazar.com: white panel. Logo, a line about the shop, address, phone, email, social icons on the
     left; Information, Shop By, Support and Consumer Policy links on the right; copyright and "Pay With" in the bottom bar. --}}
@php
  $footerPages = \App\Models\Page::inFooter()->get(['title', 'slug']);
  $gbLink = 'block py-[5px] text-[15px] text-stone-600 gb-hov transition-colors';
  $gbHead = 'text-[18px] font-semibold text-stone-900 mb-3';
  $gbIcon = 'h-10 w-10 rounded-full grid place-items-center transition-colors';
@endphp
<footer class="gb-footer mt-16 sm:mt-20 bg-white border-t border-stone-100 text-stone-700 font-sans overflow-hidden" data-footer>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 pb-8 sm:pt-12 sm:pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-9 lg:gap-6">

      <div class="lg:col-span-4 space-y-5">
        <a href="{{ route('home') }}" class="inline-flex" aria-label="{{ $site }}">
          @if(has_custom_logo())
            <img src="{{ logo_url() }}" alt="{{ $site }}" class="h-12 w-auto max-w-[200px] object-contain" />
          @else
            @include('partials.brand', ['size' => 'md'])
          @endif
        </a>
        @if($footerText !== '')
          <p class="text-[15px] text-stone-600 leading-relaxed max-w-sm">{{ $footerText }}</p>
        @endif

        <ul class="space-y-2.5 text-[16px] text-stone-700">
          @if($address !== '')
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-0.5 shrink-0 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
              <span>{{ $address }}</span>
            </li>
          @endif
          @if($phone !== '')
            <li>
              <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="flex items-center gap-3 gb-hov" data-footer-call>
                <svg class="w-5 h-5 shrink-0 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                <span class="tabular-nums">{{ $phone }}</span>
              </a>
            </li>
          @endif
          @if($email !== '')
            <li>
              <a href="mailto:{{ $email }}" class="flex items-center gap-3 gb-hov">
                <svg class="w-5 h-5 shrink-0 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.5 6.5 8.5 7 8.5-7"/></svg>
                <span>{{ $email }}</span>
              </a>
            </li>
          @endif
          @if($hours !== '')
            <li class="text-[15px] text-stone-500">{{ $hours }}</li>
          @endif
        </ul>

        <div class="flex items-center gap-3">
          <a href="{{ $facebook ?: '#' }}" @if($facebook) target="_blank" rel="noopener" @endif class="gb-social {{ $gbIcon }}" aria-label="Facebook"><svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-8h2.7l.4-3.2h-3.1V7.8c0-.9.3-1.5 1.6-1.5h1.7V3.4c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1v2.4H7.6V13h2.8v8h3.1z"/></svg></a>
          <a href="{{ $twitter ?: '#' }}" @if($twitter) target="_blank" rel="noopener" @endif class="gb-social {{ $gbIcon }}" aria-label="Twitter"><svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M22 5.9c-.7.3-1.5.5-2.4.6.9-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 0 0-7 3.7A11.6 11.6 0 0 1 3.4 4.6a4.1 4.1 0 0 0 1.3 5.5c-.7 0-1.3-.2-1.9-.5 0 2 1.4 3.7 3.3 4.1-.6.2-1.2.2-1.9.1a4.1 4.1 0 0 0 3.8 2.8A8.2 8.2 0 0 1 2 18.3 11.6 11.6 0 0 0 8.3 20c7.5 0 11.7-6.2 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
          <a href="{{ $instagram ?: '#' }}" @if($instagram) target="_blank" rel="noopener" @endif class="gb-social {{ $gbIcon }}" aria-label="Instagram"><svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
          @if($whatsapp !== '')
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="gb-social {{ $gbIcon }}" aria-label="WhatsApp"><svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg></a>
          @endif
        </div>
      </div>

      <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-8" data-footer-links>
        <div>
          <p class="{{ $gbHead }}">Information</p>
          <ul>
            @foreach($footerPages as $footerPage)
              <li><a href="{{ $footerPage->url() }}" class="{{ $gbLink }}">{{ $footerPage->title }}</a></li>
            @endforeach
            <li><a href="{{ route('contact') }}" class="{{ $gbLink }}">Contact us</a></li>
            <li><a href="{{ route('terms') }}" class="{{ $gbLink }}">Terms &amp; Conditions</a></li>
            <li><a href="{{ route('privacy') }}" class="{{ $gbLink }}">Privacy Policy</a></li>
          </ul>
        </div>
        <div>
          <p class="{{ $gbHead }}">Shop By</p>
          <ul>
            @foreach(($navCategories ?? collect())->filter(fn ($c) => ($c->products_count ?? 1) > 0)->take(7) as $cat)
              <li><a href="{{ route('shop.category', $cat) }}" class="{{ $gbLink }} truncate">{{ $cat->name }}</a></li>
            @endforeach
          </ul>
        </div>
        <div>
          <p class="{{ $gbHead }}">Support</p>
          <ul>
            <li><a href="{{ route('contact') }}" class="{{ $gbLink }}">Support Center</a></li>
            <li><a href="{{ route('track') }}" class="{{ $gbLink }}">Order Tracking</a></li>
            <li><a href="{{ route('login') }}" class="{{ $gbLink }}">My Account</a></li>
            <li><a href="{{ route('shop') }}" class="{{ $gbLink }}">All Products</a></li>
          </ul>
        </div>
        <div>
          <p class="{{ $gbHead }}">Consumer Policy</p>
          <ul>
            <li><a href="{{ route('terms') }}" class="{{ $gbLink }}">Happy Return</a></li>
            <li><a href="{{ route('terms') }}" class="{{ $gbLink }}">Refund Policy</a></li>
            <li><a href="{{ route('terms') }}" class="{{ $gbLink }}">Exchange</a></li>
            <li><a href="{{ route('terms') }}" class="{{ $gbLink }}">Cancellation</a></li>
            <li><a href="{{ route('shop', ['on_sale' => 1]) }}" class="{{ $gbLink }}">Extra Discount</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  {{-- Bottom bar: copyright on the left, Pay With and the payment methods on the right --}}
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="border-t border-stone-200 py-6 flex flex-col-reverse lg:flex-row lg:items-center justify-between gap-4 text-[15px] text-stone-700">
      <p>{{ setting('footer_copyright') ?: 'Copyright © ' . date('Y') . ' ' . $site }}</p>
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-semibold text-stone-800 mr-1">Pay With</span>
        @foreach($paymentBadges as $badge)
          <span class="gb-pay">{{ $badgeNames[$badge] ?? $badge }}</span>
        @endforeach
        @foreach(['VISA', 'Mastercard', 'AMEX', 'Nagad', 'Rocket', 'Upay'] as $card)
          @unless(in_array($card, $paymentBadges, true))
            <span class="gb-pay">{{ $card }}</span>
          @endunless
        @endforeach
      </div>
    </div>
  </div>

  @if(testing_mode())
    <div class="ws-dev-credit">
      <span>Developed by <strong>WaveSeller</strong></span>
    </div>
  @endif
</footer>
