@php
  $waPhone = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: setting('contact_phone') ?: ''));
  if (str_starts_with($waPhone, '0')) {
      $waPhone = '88' . $waPhone;
  } elseif (!str_starts_with($waPhone, '880') && strlen($waPhone) === 10) {
      $waPhone = '880' . $waPhone;
  }
  $waUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode('Hello ' . site_name() . ', I have an inquiry.') : null;
@endphp

@if($waUrl)
<aside aria-label="WhatsApp Support" class="fixed bottom-20 right-5 sm:right-6 z-40 select-none">
  <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="group relative flex items-center justify-center w-12 h-12 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer" title="Chat with us on WhatsApp" aria-label="Chat on WhatsApp">
    <!-- Pulse ring -->
    <span class="absolute -inset-0.5 rounded-full bg-emerald-400 opacity-40 animate-ping pointer-events-none"></span>
    
    <!-- WhatsApp SVG Icon -->
    <svg class="w-6 h-6 fill-current relative z-10 transition-transform duration-200 group-hover:scale-110" viewBox="0 0 24 24">
      <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.796.947 3.179 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.767-5.767-5.767zm3.391 8.248c-.146.415-.758.78-1.053.829-.283.048-.648.077-1.04-.049-.24-.078-.553-.191-.951-.362-1.684-.726-2.775-2.457-2.859-2.569-.084-.112-.686-.913-.686-1.741 0-.829.434-1.237.589-1.408.155-.171.339-.214.452-.214.113 0 .226.002.325.007.104.005.244-.04.38.29.146.353.498 1.214.542 1.303.044.089.073.193.014.309-.059.117-.089.19-.176.293-.087.103-.183.23-.262.309-.088.088-.18.184-.077.361.103.177.458.756.983 1.223.676.602 1.246.788 1.423.876.177.088.281.077.386-.044.105-.121.452-.527.573-.707.121-.18.242-.151.407-.089.165.062 1.047.494 1.227.584.18.09.3.134.344.209.044.075.044.436-.102.851z"/>
      <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.661 1.435 5.176L2 22l4.981-1.385C8.423 21.499 10.155 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.2c-1.637 0-3.17-.488-4.46-1.328l-.32-.208-2.964.825.834-2.894-.227-.338C3.963 14.887 3.45 13.483 3.45 12 3.45 7.286 7.286 3.45 12 3.45s8.55 3.836 8.55 8.55-3.836 8.2-8.55 8.2z"/>
    </svg>

    <!-- Online dot -->
    <span class="absolute top-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>

    <!-- Hover tooltip -->
    <span class="hidden sm:block absolute right-14 top-1/2 -translate-y-1/2 px-2.5 py-1 text-[11px] font-extrabold bg-stone-900 text-white rounded-lg opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-md">
      Chat on WhatsApp
    </span>
  </a>
</aside>
@endif
