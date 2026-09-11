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
@endphp

<footer class="bg-white text-stone-700 mt-16 sm:mt-20 border-t border-stone-200/90 font-sans">
  {{-- 1. Trust & Value Proposition Bar --}}
  <div class="border-b border-stone-200/80 bg-stone-50/70 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        {{-- Feature 1: Fast Delivery --}}
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-xl bg-brand-50 border border-brand-200/90 text-brand-600 flex items-center justify-center shrink-0 shadow-2xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
          </div>
          <div>
            <h5 class="font-extrabold text-sm text-stone-900 leading-snug">Nationwide Delivery</h5>
            <p class="text-xs text-stone-500 mt-0.5">Fast, tracked shipping in Bangladesh</p>
          </div>
        </div>

        {{-- Feature 2: 100% Genuine --}}
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-xl bg-emerald-50 border border-emerald-200/90 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h5 class="font-extrabold text-sm text-stone-900 leading-snug">100% Genuine Products</h5>
            <p class="text-xs text-stone-500 mt-0.5">Direct source &amp; genuine warranty</p>
          </div>
        </div>

        {{-- Feature 3: Flexible Payments --}}
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-xl bg-amber-50 border border-amber-200/90 text-amber-600 flex items-center justify-center shrink-0 shadow-2xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <div>
            <h5 class="font-extrabold text-sm text-stone-900 leading-snug">Flexible Payments</h5>
            <p class="text-xs text-stone-500 mt-0.5">Cash on delivery &amp; mobile banking</p>
          </div>
        </div>

        {{-- Feature 4: Dedicated Support --}}
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-xl bg-sky-50 border border-sky-200/90 text-sky-600 flex items-center justify-center shrink-0 shadow-2xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <div>
            <h5 class="font-extrabold text-sm text-stone-900 leading-snug">Dedicated Support</h5>
            <p class="text-xs text-stone-500 mt-0.5">Sat–Thu: 10:00 AM – 9:00 PM</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. Main Footer Body --}}
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">
      
      {{-- Brand Profile & Direct Contacts --}}
      <div class="lg:col-span-4 space-y-4">
        @include('partials.brand', ['size' => 'lg'])
        
        @if($footerText !== '')
          <p class="text-stone-500 text-xs sm:text-sm leading-relaxed max-w-sm">
            {{ $footerText }}
          </p>
        @endif

        {{-- Contact Details --}}
        <div class="space-y-3 pt-2">
          @if($phone !== '')
            <div>
              <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="inline-flex items-center gap-3 text-stone-700 hover:text-brand-600 transition-colors group">
                <span class="w-8 h-8 rounded-xl bg-stone-100 group-hover:bg-brand-50 border border-stone-200/80 group-hover:border-brand-200 text-stone-600 group-hover:text-brand-600 flex items-center justify-center shrink-0 transition shadow-2xs">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </span>
                <div>
                  <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400">Order Hotline</span>
                  <span class="text-xs sm:text-sm font-bold text-stone-800 group-hover:text-brand-600 transition-colors">{{ $phone }}</span>
                </div>
              </a>
            </div>
          @endif

          @if($email !== '')
            <div>
              <a href="mailto:{{ $email }}" class="inline-flex items-center gap-3 text-stone-700 hover:text-brand-600 transition-colors group">
                <span class="w-8 h-8 rounded-xl bg-stone-100 group-hover:bg-brand-50 border border-stone-200/80 group-hover:border-brand-200 text-stone-600 group-hover:text-brand-600 flex items-center justify-center shrink-0 transition shadow-2xs">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </span>
                <div>
                  <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400">Support Email</span>
                  <span class="text-xs sm:text-sm font-semibold text-stone-800 group-hover:text-brand-600 transition-colors">{{ $email }}</span>
                </div>
              </a>
            </div>
          @endif

          @if($address !== '')
            <div class="flex items-start gap-3 pt-0.5">
              <span class="w-8 h-8 rounded-xl bg-stone-100 border border-stone-200/80 text-stone-600 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </span>
              <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400">Store Address</span>
                <span class="text-xs text-stone-500 leading-relaxed max-w-xs block">{{ $address }}</span>
              </div>
            </div>
          @endif
        </div>

        {{-- Social Links --}}
        @if($facebook || $instagram || $twitter)
          <div class="pt-2">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-2.5">Follow Our Socials</span>
            <div class="flex items-center gap-2">
              @if($facebook)
                <a href="{{ $facebook }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-stone-100 hover:bg-[#1877F2] border border-stone-200/90 text-stone-600 hover:text-white flex items-center justify-center transition shadow-2xs hover:scale-105" aria-label="Facebook">
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>
                </a>
              @endif
              @if($instagram)
                <a href="{{ $instagram }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-stone-100 hover:bg-gradient-to-tr hover:from-amber-500 hover:via-pink-500 hover:to-purple-600 border border-stone-200/90 text-stone-600 hover:text-white flex items-center justify-center transition shadow-2xs hover:scale-105" aria-label="Instagram">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg>
                </a>
              @endif
              @if($twitter)
                <a href="{{ $twitter }}" target="_blank" rel="noopener" class="h-9 w-9 rounded-xl bg-stone-100 hover:bg-black border border-stone-200/90 text-stone-600 hover:text-white flex items-center justify-center transition shadow-2xs hover:scale-105" aria-label="X (Twitter)">
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.59l-5.16-6.74L5.2 22H1.94l8.03-9.17L1.5 2h6.75l4.66 6.18L18.244 2Zm-1.16 18.1h1.83L7.05 3.79H5.09L17.084 20.1Z"/></svg>
                </a>
              @endif
            </div>
          </div>
        @endif
      </div>

      {{-- Column 2: Explore Shop --}}
      <div class="lg:col-span-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 mb-4 pb-1 inline-block border-b-2 border-brand-500">Explore Shop</h4>
        <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
          <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Home</a></li>
          <li><a href="{{ route('shop') }}" class="hover:text-brand-600 transition-colors">All Products</a></li>
          @if($hasFlashSale ?? false)
            <li>
              <a href="{{ route('shop', ['flash' => 1]) }}" class="hover:text-brand-600 transition-colors inline-flex items-center gap-1.5 font-medium text-amber-700">
                <span>Flash Deals</span>
                <span class="px-1.5 py-0.5 text-[9px] font-extrabold bg-amber-100 text-amber-800 rounded uppercase tracking-wider">Hot</span>
              </a>
            </li>
          @endif
          <li><a href="{{ route('shop') }}?sort=newest" class="hover:text-brand-600 transition-colors">New Arrivals</a></li>
          <li><a href="{{ route('track') }}" class="hover:text-brand-600 transition-colors">Track Order</a></li>
          <li><a href="{{ route('contact') }}" class="hover:text-brand-600 transition-colors">Store Locator</a></li>
        </ul>
      </div>

      {{-- Column 3: Customer Care --}}
      <div class="lg:col-span-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 mb-4 pb-1 inline-block border-b-2 border-brand-500">Customer Care</h4>
        <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
          <li><a href="{{ route('contact') }}" class="hover:text-brand-600 transition-colors">Help &amp; Contact Us</a></li>
          <li><a href="{{ route('track') }}" class="hover:text-brand-600 transition-colors">Track My Order</a></li>
          <li><a href="{{ route('login') }}" class="hover:text-brand-600 transition-colors">My Account / Login</a></li>
          <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Shipping Information</a></li>
          <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Returns &amp; Exchange</a></li>
        </ul>
      </div>

      {{-- Column 4: Policies & Trust --}}
      <div class="lg:col-span-2">
        <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 mb-4 pb-1 inline-block border-b-2 border-brand-500">Policies</h4>
        <ul class="space-y-2.5 text-xs sm:text-sm text-stone-600">
          <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Terms of Service</a></li>
          <li><a href="{{ route('privacy') }}" class="hover:text-brand-600 transition-colors">Privacy Policy</a></li>
          <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Authenticity Guarantee</a></li>
          <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Warranty &amp; Support</a></li>
        </ul>
      </div>

      {{-- Column 5: Stay Updated & Payments --}}
      <div class="lg:col-span-2 space-y-6">
        <div>
          <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 mb-2 pb-1 inline-block border-b-2 border-brand-500">Stay Updated</h4>
          <p class="text-xs text-stone-500 mb-3 leading-relaxed">Subscribe for latest drops, exclusive coupons &amp; tech deals.</p>
          <form onsubmit="event.preventDefault(); var btn = this.querySelector('button'); btn.innerText = 'Subscribed!'; btn.disabled = true; this.querySelector('input').value = '';" class="space-y-2">
            <div class="relative">
              <input type="email" placeholder="Enter your email" required class="w-full text-xs bg-stone-50 border border-stone-200/90 rounded-xl px-3.5 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-stone-800 placeholder:text-stone-400 transition shadow-2xs" />
            </div>
            <button type="submit" class="w-full bg-stone-900 hover:bg-brand-600 text-white text-xs font-bold py-2.5 px-3 rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
              <span>Subscribe</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
            </button>
          </form>
        </div>

        {{-- Accepted Payment Methods --}}
        @if(count($paymentBadges))
          <div>
            <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-2.5">We Accept</span>
            <div class="flex flex-wrap gap-1.5">
              @foreach($paymentBadges as $badge)
                @if($badge === 'bKash')
                  <span class="inline-flex items-center gap-1.5 bg-[#e2136e]/10 text-[#c2185b] border border-[#e2136e]/25 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#e2136e]"></span>
                    bKash
                  </span>
                @elseif($badge === 'Nagad')
                  <span class="inline-flex items-center gap-1.5 bg-[#f7941d]/10 text-[#d97706] border border-[#f7941d]/25 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#f7941d]"></span>
                    Nagad
                  </span>
                @elseif($badge === 'Rocket')
                  <span class="inline-flex items-center gap-1.5 bg-[#8c3494]/10 text-[#7b1fa2] border border-[#8c3494]/25 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#8c3494]"></span>
                    Rocket
                  </span>
                @elseif($badge === 'COD')
                  <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Cash on Delivery
                  </span>
                @elseif($badge === 'Card')
                  <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200/80 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Cards
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 bg-stone-100 text-stone-800 border border-stone-200 px-2.5 py-1 rounded-lg font-bold text-[11px] shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    {{ $badge }}
                  </span>
                @endif
              @endforeach
            </div>
          </div>
        @endif
      </div>

    </div>
  </div>

  {{-- 3. Bottom Bar / Sub-footer --}}
  <div class="border-t border-stone-200/80 bg-stone-50/60 py-5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-stone-500">
      <div>
        <span>© {{ date('Y') }} <strong class="text-stone-800 font-bold">{{ $site }}</strong>. All rights reserved.</span>
      </div>
      <div class="flex items-center gap-2 text-stone-500 text-[11px]">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <span class="font-medium text-stone-600">100% Secure Checkout &amp; SSL Encrypted</span>
      </div>
    </div>
  </div>

  @if(testing_mode())
    <div class="ws-dev-credit">
      <span>Developed by <strong>WaveSeller</strong></span>
    </div>
  @endif
</footer>
