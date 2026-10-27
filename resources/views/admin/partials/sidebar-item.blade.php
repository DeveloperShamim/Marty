@php
  $on = \App\Support\AdminNav::isOn($i);
  $badge = $badges[$i['key']] ?? null;
@endphp
<a href="{{ route($i['route']) }}"
   class="sb-item group relative flex items-center gap-3 h-10 px-3 rounded-xl text-[14px] transition-colors {{ $itemClass($on) }}"
   data-label="{{ $i['label'] }}" data-search="{{ strtolower($i['label'] . ' ' . $i['keywords']) }}"
   @if($on) aria-current="page" @endif>
  <svg class="w-[18px] h-[18px] shrink-0 {{ $on ? 'text-gray-900' : 'text-gray-400 group-hover:text-gray-700' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $i['icon'] !!}</svg>
  <span class="sb-label flex-1 truncate">{{ $i['label'] }}</span>
  {{-- The orders count is always rendered (hidden at 0) so the new-order poller can update it.
       Orders to review stand out in the brand colour; other counts are plain grey numbers. --}}
  @if($badge && ($badge['count'] > 0 || $i['key'] === 'orders'))
    <span data-live-badge="{{ $i['key'] }}" style="display: {{ $badge['count'] > 0 ? 'contents' : 'none' }}">
      @if($i['key'] === 'orders')
        <span class="sb-badge sb-label text-[11px] font-semibold tabular-nums min-w-[24px] h-5 px-1.5 rounded-full inline-flex items-center justify-center text-white" style="background: var(--brand);" title="{{ $badge['count'] }} {{ $badge['hint'] }}">{{ $badge['count'] > 99 ? '99+' : $badge['count'] }}</span>
      @else
        <span class="sb-badge sb-label text-[13px] font-medium tabular-nums text-gray-400" title="{{ $badge['count'] }} {{ $badge['hint'] }}">{{ $badge['count'] > 99 ? '99+' : $badge['count'] }}</span>
      @endif
      <span class="sr-only">({{ $badge['count'] }} {{ $badge['hint'] }})</span>
    </span>
  @endif
</a>
{{-- Pages that share this entry, as a small tree under the open page --}}
@if($on && ! empty($i['tabs']))
  <div class="sb-tree sb-label">
    @foreach($i['tabs'] as $tab)
      @php $tabOn = request()->routeIs($tab['pattern']); @endphp
      <a href="{{ route($tab['route']) }}" class="sb-sub flex items-center h-8 px-2.5 rounded-lg text-[13px] transition-colors {{ $tabOn ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900 font-medium' }}" @if($tabOn) aria-current="true" @endif>{{ $tab['label'] }}</a>
    @endforeach
  </div>
@endif
