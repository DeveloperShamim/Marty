{{-- Main photo with arrows and thumbnails; the page script drives #lpMain / data-lp-thumb / data-lp-step --}}
@if(count($gallery))
  <div class="relative rounded-2xl border border-gray-200 bg-white overflow-hidden">
    @if($discountBadge)
      <span class="absolute left-3 top-3 z-10 rounded-md bg-gray-900 px-2 py-1 text-xs font-bold text-white">{{ $discountBadge }}</span>
    @endif
    <div class="aspect-square bg-white">
      <img id="lpMain" src="{{ $gallery[0] }}" alt="{{ $page->title }}" class="h-full w-full object-contain" fetchpriority="high">
    </div>
    @if(count($gallery) > 1)
      <button type="button" data-lp-step="-1" class="absolute left-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white/90 shadow flex items-center justify-center" aria-label="আগের ছবি">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
      </button>
      <button type="button" data-lp-step="1" class="absolute right-3 top-1/2 -translate-y-1/2 h-9 w-9 rounded-full bg-white/90 shadow flex items-center justify-center" aria-label="পরের ছবি">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </button>
      <div class="flex justify-center gap-2 border-t border-gray-100 p-3">
        @foreach($gallery as $i => $src)
          <button type="button" data-lp-thumb="{{ $i }}" class="h-14 w-14 rounded-lg border-2 overflow-hidden {{ $i === 0 ? '' : 'border-transparent' }}" style="{{ $i === 0 ? 'border-color: var(--lp)' : '' }}" aria-label="ছবি {{ $i + 1 }}">
            <img src="{{ $src }}" alt="" class="h-full w-full object-cover" loading="lazy">
          </button>
        @endforeach
      </div>
    @endif
  </div>
@endif
