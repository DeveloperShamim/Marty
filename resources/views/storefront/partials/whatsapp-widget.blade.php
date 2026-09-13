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
<aside id="quickSupportChatRoot" aria-label="Customer Support Chat" class="fixed {{ request()->routeIs('checkout.*') ? 'bottom-24 right-4 sm:right-6' : (request()->routeIs('product.show') ? 'bottom-28 right-4 sm:right-6' : 'bottom-20 right-5 sm:right-6') }} z-50 select-none font-sans">
  
  {{-- Support Popup Modal Window --}}
  <div id="quickSupportPopup" class="hidden absolute bottom-14 right-0 w-[calc(100vw-32px)] max-w-[320px] bg-white rounded-3xl shadow-2xl border border-stone-200/90 overflow-hidden transform origin-bottom-right transition-all duration-300 scale-95 opacity-0 pointer-events-none z-50">
    
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white p-4 relative">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="relative">
            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-xs flex items-center justify-center text-white border border-white/30 text-lg shadow-inner">
              💬
            </div>
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-300 border-2 border-emerald-700 rounded-full"></span>
          </div>
          <div>
            <h3 class="font-extrabold text-sm tracking-tight text-white leading-snug">{{ site_name() }} Support</h3>
            <p class="text-[11px] text-emerald-100 flex items-center gap-1.5 font-medium mt-0.5">
              <span class="inline-block w-1.5 h-1.5 bg-emerald-300 rounded-full animate-ping"></span>
              <span>Online · Instant Replies</span>
            </p>
          </div>
        </div>
        <button type="button" id="closeQuickSupportBtn" class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center text-xs transition cursor-pointer" aria-label="Close Chat Popup">
          ✕
        </button>
      </div>
      <p class="text-[11px] text-white/85 mt-2.5 leading-relaxed font-medium">
        Choose your preferred platform to start a direct message with our team:
      </p>
    </div>

    {{-- Chat Options List --}}
    <div class="p-3.5 space-y-2.5 bg-stone-50/80">
      
      {{-- 1. WhatsApp Option --}}
      @if($waUrl)
        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="group flex items-center justify-between p-3 rounded-2xl bg-white hover:bg-emerald-50/60 border border-stone-200/90 hover:border-emerald-300 shadow-2xs hover:shadow-md transition-all active:scale-[0.98]">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
              <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.796.947 3.179 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.767-5.767-5.767zm3.391 8.248c-.146.415-.758.78-1.053.829-.283.048-.648.077-1.04-.049-.24-.078-.553-.191-.951-.362-1.684-.726-2.775-2.457-2.859-2.569-.084-.112-.686-.913-.686-1.741 0-.829.434-1.237.589-1.408.155-.171.339-.214.452-.214.113 0 .226.002.325.007.104.005.244-.04.38.29.146.353.498 1.214.542 1.303.044.089.073.193.014.309-.059.117-.089.19-.176.293-.087.103-.183.23-.262.309-.088.088-.18.184-.077.361.103.177.458.756.983 1.223.676.602 1.246.788 1.423.876.177.088.281.077.386-.044.105-.121.452-.527.573-.707.121-.18.242-.151.407-.089.165.062 1.047.494 1.227.584.18.09.3.134.344.209.044.075.044.436-.102.851z"/>
                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.385C8.423 21.499 10.155 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.2c-1.637 0-3.17-.488-4.46-1.328l-.32-.208-2.964.825.834-2.894-.227-.338C3.963 14.887 3.45 13.483 3.45 12 3.45 7.286 7.286 3.45 12 3.45s8.55 3.836 8.55 8.55-3.836 8.2-8.55 8.2z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="font-extrabold text-xs text-stone-900 group-hover:text-emerald-700 transition">WhatsApp</span>
                <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-700">Online</span>
              </div>
              <p class="text-[11px] text-stone-500 truncate mt-0.5">Chat directly on WhatsApp</p>
            </div>
          </div>
          <span class="text-stone-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0 ml-2">➔</span>
        </a>
      @endif

      {{-- 2. Facebook Messenger Option --}}
      @if($messengerUrl)
        <a href="{{ $messengerUrl }}" target="_blank" rel="noopener noreferrer" class="group flex items-center justify-between p-3 rounded-2xl bg-white hover:bg-blue-50/60 border border-stone-200/90 hover:border-blue-300 shadow-2xs hover:shadow-md transition-all active:scale-[0.98]">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#00B2FF] via-[#006AFF] to-[#9900FF] text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-105 transition-transform">
              <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.36 2 2 6.13 2 11.7c0 2.91 1.19 5.43 3.14 7.15.16.14.26.35.27.57l.08 1.78a.8.8 0 001.12.71l1.98-.87c.18-.08.38-.09.57-.03.9.25 1.86.39 2.84.39 5.64 0 10-4.13 10-9.7S17.64 2 12 2zm1.03 13.06l-2.56-2.73-5 2.73 5.5-5.84 2.62 2.73 4.94-2.73-5.5 5.84z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="font-extrabold text-xs text-stone-900 group-hover:text-blue-700 transition">Messenger</span>
                <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.2 rounded-full bg-blue-100 text-blue-700">Facebook</span>
              </div>
              <p class="text-[11px] text-stone-500 truncate mt-0.5">Chat on Facebook Messenger</p>
            </div>
          </div>
          <span class="text-stone-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all text-xs font-bold shrink-0 ml-2">➔</span>
        </a>
      @endif

    </div>

    {{-- Footer --}}
    <div class="px-4 py-2.5 bg-stone-100/70 border-t border-stone-200/60 text-center">
      <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">
        ⚡ Guaranteed fast response
      </span>
    </div>
  </div>

  {{-- Floating Action Trigger Button --}}
  <button type="button" id="quickSupportToggleBtn" class="group relative flex items-center justify-center w-12 h-12 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer" aria-label="Open Chat Options" aria-expanded="false">
    
    <!-- Pulse ring (shown when closed) -->
    <span id="quickSupportPulseRing" class="absolute -inset-0.5 rounded-full bg-emerald-400 opacity-40 animate-ping pointer-events-none"></span>

    <!-- Chat/WhatsApp Icon (When Closed) -->
    <div id="quickSupportIconNormal" class="relative z-10 transition-transform duration-200 group-hover:scale-110">
      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.796.947 3.179 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.767-5.767-5.767zm3.391 8.248c-.146.415-.758.78-1.053.829-.283.048-.648.077-1.04-.049-.24-.078-.553-.191-.951-.362-1.684-.726-2.775-2.457-2.859-2.569-.084-.112-.686-.913-.686-1.741 0-.829.434-1.237.589-1.408.155-.171.339-.214.452-.214.113 0 .226.002.325.007.104.005.244-.04.38.29.146.353.498 1.214.542 1.303.044.089.073.193.014.309-.059.117-.089.19-.176.293-.087.103-.183.23-.262.309-.088.088-.18.184-.077.361.103.177.458.756.983 1.223.676.602 1.246.788 1.423.876.177.088.281.077.386-.044.105-.121.452-.527.573-.707.121-.18.242-.151.407-.089.165.062 1.047.494 1.227.584.18.09.3.134.344.209.044.075.044.436-.102.851z"/>
        <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.385C8.423 21.499 10.155 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.2c-1.637 0-3.17-.488-4.46-1.328l-.32-.208-2.964.825.834-2.894-.227-.338C3.963 14.887 3.45 13.483 3.45 12 3.45 7.286 7.286 3.45 12 3.45s8.55 3.836 8.55 8.55-3.836 8.2-8.55 8.2z"/>
      </svg>
    </div>

    <!-- Close '✕' Icon (When Opened) -->
    <div id="quickSupportIconClose" class="hidden relative z-10 transition-transform duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </div>

    <!-- Online green dot -->
    <span id="quickSupportOnlineDot" class="absolute top-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>

    <!-- Hover tooltip (desktop) -->
    <span class="hidden sm:block absolute right-14 top-1/2 -translate-y-1/2 px-2.5 py-1 text-[11px] font-extrabold bg-stone-900 text-white rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-md">
      Chat with us
    </span>
  </button>
</aside>

<script>
(function() {
  const root = document.getElementById('quickSupportChatRoot');
  const toggleBtn = document.getElementById('quickSupportToggleBtn');
  const popup = document.getElementById('quickSupportPopup');
  const closeBtn = document.getElementById('closeQuickSupportBtn');
  const iconNormal = document.getElementById('quickSupportIconNormal');
  const iconClose = document.getElementById('quickSupportIconClose');
  const pulseRing = document.getElementById('quickSupportPulseRing');
  const onlineDot = document.getElementById('quickSupportOnlineDot');

  if (!toggleBtn || !popup) return;

  let isOpen = false;

  function openPopup() {
    isOpen = true;
    popup.classList.remove('hidden');
    requestAnimationFrame(() => {
      popup.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
      popup.classList.add('scale-100', 'opacity-100', 'pointer-events-auto');
    });
    iconNormal.classList.add('hidden');
    iconClose.classList.remove('hidden');
    if (pulseRing) pulseRing.classList.add('hidden');
    if (onlineDot) onlineDot.classList.add('hidden');
    toggleBtn.setAttribute('aria-expanded', 'true');
  }

  function closePopup() {
    isOpen = false;
    popup.classList.remove('scale-100', 'opacity-100', 'pointer-events-auto');
    popup.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
    setTimeout(() => {
      if (!isOpen) popup.classList.add('hidden');
    }, 300);
    iconNormal.classList.remove('hidden');
    iconClose.classList.add('hidden');
    if (pulseRing) pulseRing.classList.remove('hidden');
    if (onlineDot) onlineDot.classList.remove('hidden');
    toggleBtn.setAttribute('aria-expanded', 'false');
  }

  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    if (isOpen) {
      closePopup();
    } else {
      openPopup();
    }
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      closePopup();
    });
  }

  // Close when clicking anywhere outside
  document.addEventListener('click', (e) => {
    if (isOpen && root && !root.contains(e.target)) {
      closePopup();
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) {
      closePopup();
    }
  });
})();
</script>
@endif
