@php
  $on = request()->routeIs($i['pattern']);
  $badge = $badges[$i['key']] ?? null;
  $direct = $direct ?? false;
@endphp
<a href="{{ route($i['route']) }}"
   class="sb-item {{ $direct ? 'sb-direct' : '' }} group relative flex items-center gap-3 h-10 lg:h-9 px-3 rounded-full text-[14px] lg:text-[13px] transition-colors {{ $itemClass($on) }}"
   data-label="{{ $i['label'] }}" data-search="{{ strtolower($i['label'] . ' ' . $i['keywords']) }}"
   @if($on) aria-current="page" style="background: var(--brand-dark);" @endif>
  <svg class="w-[18px] h-[18px] shrink-0 {{ $on ? 'text-white' : 'text-gray-400 group-hover:text-gray-700' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $i['icon'] !!}</svg>
  <span class="sb-label flex-1 truncate">{{ $i['label'] }}</span>
  {{-- The orders count is always rendered (hidden at 0) so the new-order poller can update it. --}}
  @if($badge && ($badge['count'] > 0 || $i['key'] === 'orders'))
    <span data-live-badge="{{ $i['key'] }}" style="display: {{ $badge['count'] > 0 ? 'contents' : 'none' }}">
      <span class="sb-badge sb-label text-[11px] font-semibold tabular-nums min-w-[22px] h-5 px-1.5 rounded-full inline-flex items-center justify-center {{ $on ? 'bg-white/20 text-white' : ($i['key'] === 'orders' ? 'text-white' : $badge['class']) }}" @if(!$on && $i['key'] === 'orders') style="background: var(--brand);" @endif title="{{ $badge['count'] }} {{ $badge['hint'] }}">{{ $badge['count'] > 99 ? '99+' : $badge['count'] }}</span>
      <span class="sr-only">({{ $badge['count'] }} {{ $badge['hint'] }})</span>
    </span>
  @endif
</a>
