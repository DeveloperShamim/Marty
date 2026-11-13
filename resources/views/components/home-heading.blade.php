{{-- The one heading used by every homepage section: title on the left, anything passed in (arrows, tabs, a badge) on the right --}}
@props(['title'])
<div {{ $attributes->merge(['class' => 'flex items-end justify-between gap-3 mb-4 sm:mb-6']) }} data-home-heading>
  <h2 class="text-base sm:text-xl font-medium text-stone-900 leading-tight min-w-0">{{ $title }}</h2>
  @if(trim($slot) !== '')
    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">{{ $slot }}</div>
  @endif
</div>
