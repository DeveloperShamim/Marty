{{-- One menu group: a rail button with a flyout on desktop, a labelled list in the phone drawer. --}}
@php $flyId = 'fly-' . \Illuminate\Support\Str::slug($group); @endphp
<div class="sb-group mt-4 lg:mt-0" data-group="{{ $group }}" @if($groupActive) data-active @endif>
  <button type="button" class="{{ $railBtn }} {{ $groupActive ? 'text-white' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}"
          @if($groupActive) style="background: var(--brand-dark);" @endif
          aria-expanded="false" aria-controls="{{ $flyId }}" aria-label="{{ $group }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $groupIcons[$group] ?? '' !!}</svg>
    @if($groupCount > 0)
      <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full ring-2 ring-white" style="background: var(--brand);" aria-hidden="true"></span>
    @endif
  </button>
  <div id="{{ $flyId }}" class="sb-flyout" role="group" aria-label="{{ $group }}">
    <p class="px-3 mb-1 lg:mb-1.5 lg:pt-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-gray-400">{{ $group }}</p>
    <div class="sb-items space-y-0.5">
      @foreach($items as $i)
        @include('admin.partials.sidebar-item', ['i' => $i, 'direct' => false])
      @endforeach
      @if(!empty($extra))
        <a href="{{ route('shop') }}" target="_blank" rel="noopener" class="sb-item group flex items-center gap-3 h-10 lg:h-9 px-3 rounded-full text-[14px] lg:text-[13px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors" data-label="View store" data-search="view store shop website">
          <svg class="w-[18px] h-[18px] shrink-0 text-gray-400 group-hover:text-gray-700" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/></svg>
          <span class="sb-label flex-1">View store</span>
        </a>
      @endif
    </div>
  </div>
</div>
