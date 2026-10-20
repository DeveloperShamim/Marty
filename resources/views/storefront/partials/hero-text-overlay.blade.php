{{-- Banner text drawn on top of a banner picture (Store Settings → Homepage → "Show banner text on the picture").
     For pictures without text of their own; a dark fade on the left keeps the words readable.
     The fade, shadow and width are inline styles because the store CSS is pre-built (public/theme/css/tw-store.css)
     and only holds the utility classes it was built with. --}}
<div class="absolute inset-0 flex items-center px-5 sm:px-20 pointer-events-none"
     style="background: linear-gradient(90deg, rgba(0,0,0,.7) 0%, rgba(0,0,0,.4) 45%, rgba(0,0,0,0) 80%)">
  <div class="text-white" style="max-width: min(70%, 28rem); text-shadow: 0 2px 12px rgba(0,0,0,.45)">
    @if($badge)<span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-brand-600 text-white inline-block mb-2" style="text-shadow: none">{{ $badge }}</span>@endif
    @if($title)<h2 class="text-xl sm:text-4xl font-extrabold leading-tight text-white">{{ $title }}</h2>@endif
    @if($subtitle)<p class="max-sm:hidden text-sm text-white mt-2 line-clamp-2">{{ $subtitle }}</p>@endif
    @if($button)<span class="inline-block mt-3 sm:mt-4 px-4 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-brand-600 text-white text-xs sm:text-sm font-bold" style="text-shadow: none">{{ $button }}</span>@endif
  </div>
</div>
