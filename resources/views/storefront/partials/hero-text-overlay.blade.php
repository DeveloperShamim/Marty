{{-- Banner text drawn on top of a banner picture (Store Settings → Homepage → "Show banner text on the picture").
     For pictures without text of their own; a dark fade on the left keeps the words readable. --}}
<div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-black/0 flex items-center px-5 sm:px-12 lg:px-16 pointer-events-none">
  <div class="max-w-[70%] sm:max-w-md text-white">
    @if($badge)<span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase bg-brand-600 text-white inline-block mb-1.5 sm:mb-2">{{ $badge }}</span>@endif
    @if($title)<h2 class="text-lg sm:text-4xl font-extrabold leading-tight text-white [text-shadow:0_2px_12px_rgba(0,0,0,.45)]">{{ $title }}</h2>@endif
    @if($subtitle)<p class="max-sm:hidden text-sm text-white mt-2 line-clamp-2 [text-shadow:0_1px_8px_rgba(0,0,0,.5)]">{{ $subtitle }}</p>@endif
    @if($button)<span class="inline-block mt-2.5 sm:mt-4 px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-brand-600 text-white text-xs sm:text-sm font-bold">{{ $button }}</span>@endif
  </div>
</div>
