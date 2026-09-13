@php
  $rawWa = setting('whatsapp_number') ?: setting('contact_phone') ?: '';
  $waPhone = preg_replace('/[^0-9]/', '', (string) $rawWa);
  if (str_starts_with($waPhone, '0')) {
      $waPhone = '88' . $waPhone;
  } elseif (!str_starts_with($waPhone, '880') && strlen($waPhone) === 10) {
      $waPhone = '880' . $waPhone;
  }
  $waUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode('Hello ' . site_name() . ', I have an inquiry.') : null;

  $messengerSetting = trim((string) setting('messenger_url', ''));
  $fbUrl = trim((string) setting('facebook_url', ''));

  if ($messengerSetting) {
      if (str_starts_with($messengerSetting, 'http://') || str_starts_with($messengerSetting, 'https://')) {
          $messengerUrl = $messengerSetting;
      } else {
          $messengerUrl = 'https://m.me/' . ltrim($messengerSetting, '@/ ');
      }
  } elseif ($fbUrl && ! in_array($fbUrl, ['https://facebook.com/', 'https://www.facebook.com/', 'https://facebook.com', 'https://www.facebook.com'])) {
      $path = trim(parse_url($fbUrl, PHP_URL_PATH) ?? '', '/');
      if ($path && ! in_array(strtolower($path), ['home', 'profile.php', 'pages', 'groups'])) {
          $messengerUrl = 'https://m.me/' . $path;
      } else {
          $messengerUrl = $fbUrl;
      }
  } else {
      $messengerUrl = 'https://m.me/';
  }

  $hasChat = $waUrl || $messengerUrl;
@endphp

@if($hasChat)
<aside id="chatSpeedDialRoot" aria-label="Customer Support Chat" class="fixed {{ request()->routeIs('checkout.*') ? 'bottom-24 right-4 sm:right-6' : (request()->routeIs('product.show') ? 'bottom-28 right-4 sm:right-6' : 'bottom-20 right-5 sm:right-6') }} z-50 select-none font-sans flex flex-col items-end gap-2.5">
  
  {{-- Popped Icons Container (Vertically stacked above the trigger button) --}}
  <div id="chatSpeedDialIcons" class="flex flex-col items-end gap-2.5 pointer-events-none">

    {{-- 1. Facebook Messenger Floating Action Icon (Pops top) --}}
    @if($messengerUrl)
      <a href="{{ $messengerUrl }}" target="_blank" rel="noopener noreferrer" id="messengerPopBtn" class="group flex items-center gap-2 transform translate-y-6 scale-50 opacity-0 pointer-events-none transition-all duration-300 ease-out" title="Chat on Messenger" aria-label="Chat on Messenger">
        <span class="hidden sm:inline-block px-2.5 py-1 text-xs font-bold text-stone-800 bg-white border border-stone-200/90 rounded-xl shadow-md group-hover:bg-blue-50 transition whitespace-nowrap">
          Messenger
        </span>
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-gradient-to-tr from-[#00B2FF] via-[#006AFF] to-[#9900FF] hover:scale-110 active:scale-95 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all cursor-pointer border-2 border-white">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 fill-current" viewBox="0 0 24 24">
            <path d="M12 2C6.36 2 2 6.13 2 11.7c0 2.91 1.19 5.43 3.14 7.15.16.14.26.35.27.57l.08 1.78a.8.8 0 001.12.71l1.98-.87c.18-.08.38-.09.57-.03.9.25 1.86.39 2.84.39 5.64 0 10-4.13 10-9.7S17.64 2 12 2zm1.03 13.06l-2.56-2.73-5 2.73 5.5-5.84 2.62 2.73 4.94-2.73-5.5 5.84z"/>
          </svg>
        </div>
      </a>
    @endif

    {{-- 2. WhatsApp Floating Action Icon (Pops above trigger) --}}
    @if($waUrl)
      <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" id="whatsappPopBtn" class="group flex items-center gap-2 transform translate-y-6 scale-50 opacity-0 pointer-events-none transition-all duration-300 ease-out" title="Chat on WhatsApp" aria-label="Chat on WhatsApp">
        <span class="hidden sm:inline-block px-2.5 py-1 text-xs font-bold text-stone-800 bg-white border border-stone-200/90 rounded-xl shadow-md group-hover:bg-emerald-50 transition whitespace-nowrap">
          WhatsApp
        </span>
        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#25D366] hover:bg-[#20ba59] hover:scale-110 active:scale-95 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all cursor-pointer border-2 border-white">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 fill-current" viewBox="0 0 24 24">
            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.796.947 3.179 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.767-5.767-5.767zm3.391 8.248c-.146.415-.758.78-1.053.829-.283.048-.648.077-1.04-.049-.24-.078-.553-.191-.951-.362-1.684-.726-2.775-2.457-2.859-2.569-.084-.112-.686-.913-.686-1.741 0-.829.434-1.237.589-1.408.155-.171.339-.214.452-.214.113 0 .226.002.325.007.104.005.244-.04.38.29.146.353.498 1.214.542 1.303.044.089.073.193.014.309-.059.117-.089.19-.176.293-.087.103-.183.23-.262.309-.088.088-.18.184-.077.361.103.177.458.756.983 1.223.676.602 1.246.788 1.423.876.177.088.281.077.386-.044.105-.121.452-.527.573-.707.121-.18.242-.151.407-.089.165.062 1.047.494 1.227.584.18.09.3.134.344.209.044.075.044.436-.102.851z"/>
            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.385C8.423 21.499 10.155 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.2c-1.637 0-3.17-.488-4.46-1.328l-.32-.208-2.964.825.834-2.894-.227-.338C3.963 14.887 3.45 13.483 3.45 12 3.45 7.286 7.286 3.45 12 3.45s8.55 3.836 8.55 8.55-3.836 8.2-8.55 8.2z"/>
          </svg>
        </div>
      </a>
    @endif

  </div>

  {{-- Main Floating Trigger Button (Universal Customer Support Chat Theme) --}}
  <button type="button" id="chatSpeedDialToggleBtn" class="group relative flex items-center justify-center w-12 h-12 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer border-2 border-white/80" aria-label="Toggle Customer Support Chat" aria-expanded="false">
    
    <!-- Pulse ring (visible when closed) -->
    <span id="chatSpeedDialPulseRing" class="absolute -inset-0.5 rounded-full bg-brand-500 opacity-40 animate-ping pointer-events-none"></span>

    <!-- Modern Universal Chat Bubble with Conversation Dots (Closed state) -->
    <div id="chatSpeedDialIconNormal" class="relative z-10 transition-all duration-300 transform group-hover:scale-110 flex items-center justify-center">
      <svg class="w-6 h-6 fill-none stroke-current" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
        <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
        <circle cx="8" cy="11.5" r="1" fill="currentColor"/>
        <circle cx="12" cy="11.5" r="1" fill="currentColor"/>
        <circle cx="16" cy="11.5" r="1" fill="currentColor"/>
      </svg>
    </div>

    <!-- Close '✕' Icon (Opened state) -->
    <div id="chatSpeedDialIconClose" class="hidden relative z-10 transition-all duration-300 transform">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </div>

    <!-- Online green indicator dot -->
    <span id="chatSpeedDialOnlineDot" class="absolute top-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>

    <!-- Hover tooltip on desktop -->
    <span class="hidden sm:block absolute right-14 top-1/2 -translate-y-1/2 px-2.5 py-1 text-[11px] font-extrabold bg-stone-900 text-white rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-md">
      Live Support Chat
    </span>
  </button>
</aside>

<script>
(function() {
  const root = document.getElementById('chatSpeedDialRoot');
  const toggleBtn = document.getElementById('chatSpeedDialToggleBtn');
  const iconsContainer = document.getElementById('chatSpeedDialIcons');
  const popBtns = [
    document.getElementById('whatsappPopBtn'),
    document.getElementById('messengerPopBtn')
  ].filter(Boolean);

  const iconNormal = document.getElementById('chatSpeedDialIconNormal');
  const iconClose = document.getElementById('chatSpeedDialIconClose');
  const pulseRing = document.getElementById('chatSpeedDialPulseRing');
  const onlineDot = document.getElementById('chatSpeedDialOnlineDot');

  if (!toggleBtn || popBtns.length === 0) return;

  let isOpen = false;

  function openSpeedDial() {
    isOpen = true;
    iconsContainer.classList.remove('pointer-events-none');
    
    popBtns.forEach((btn, idx) => {
      setTimeout(() => {
        if (isOpen) {
          btn.classList.remove('translate-y-6', 'scale-50', 'opacity-0', 'pointer-events-none');
          btn.classList.add('translate-y-0', 'scale-100', 'opacity-100', 'pointer-events-auto');
        }
      }, idx * 60);
    });

    iconNormal.classList.add('hidden');
    iconClose.classList.remove('hidden');
    if (pulseRing) pulseRing.classList.add('hidden');
    if (onlineDot) onlineDot.classList.add('hidden');
    toggleBtn.setAttribute('aria-expanded', 'true');
  }

  function closeSpeedDial() {
    isOpen = false;
    iconsContainer.classList.add('pointer-events-none');

    popBtns.slice().reverse().forEach((btn, idx) => {
      setTimeout(() => {
        btn.classList.remove('translate-y-0', 'scale-100', 'opacity-100', 'pointer-events-auto');
        btn.classList.add('translate-y-6', 'scale-50', 'opacity-0', 'pointer-events-none');
      }, idx * 40);
    });

    iconNormal.classList.remove('hidden');
    iconClose.classList.add('hidden');
    if (pulseRing) pulseRing.classList.remove('hidden');
    if (onlineDot) onlineDot.classList.remove('hidden');
    toggleBtn.setAttribute('aria-expanded', 'false');
  }

  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    if (isOpen) {
      closeSpeedDial();
    } else {
      openSpeedDial();
    }
  });

  // Automatically hide popped icons when visitor scrolls
  window.addEventListener('scroll', () => {
    if (isOpen) {
      closeSpeedDial();
    }
  }, { passive: true });

  // Close when clicking outside
  document.addEventListener('click', (e) => {
    if (isOpen && root && !root.contains(e.target)) {
      closeSpeedDial();
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) {
      closeSpeedDial();
    }
  });
})();
</script>
@endif
