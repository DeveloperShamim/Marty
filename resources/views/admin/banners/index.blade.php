@extends('layouts.admin')
@section('title', 'Banners')
@section('subtitle', 'Homepage carousel slides and the promo cards beside them.')

@section('page-actions')
  <a href="{{ url('/') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
  <a href="{{ route('admin.banners.create', ['placement' => 'hero_side']) }}" class="pill-btn">Add promo card</a>
  <a href="{{ route('admin.banners.create', ['placement' => 'hero']) }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    Add slide
  </a>
@endsection

@section('content')
@php
  $heroSlides = $banners->where('placement', 'hero');
  $promoCards = $banners->where('placement', 'hero_side');
  $activeHeroCount = $heroSlides->where('is_active', true)->count();
  $activePromoCount = $promoCards->where('is_active', true)->count();

  $sections = [
    [
      'items' => $heroSlides, 'placement' => 'hero', 'active' => $activeHeroCount,
      'title' => 'Hero carousel', 'hint' => 'Main rotating slider at the top of the homepage. Best size 1500 × 800 px (15:8).',
      'aspect' => 'aspect-[15/8]', 'add' => 'Add slide', 'untitled' => 'Untitled slide',
      'empty' => 'No carousel slides yet', 'emptyHint' => 'Add a slide to show promotions, new arrivals or seasonal deals.',
      'confirm' => 'Are you sure you want to delete this banner slide?',
      'icon' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5L5 20"/>',
    ],
    [
      'items' => $promoCards, 'placement' => 'hero_side', 'active' => $activePromoCount,
      'title' => 'Promo cards', 'hint' => 'Cards beside the carousel on desktop, two per row on phones. Best size 868 × 476 px.',
      'aspect' => 'aspect-[1.82/1]', 'add' => 'Add card', 'untitled' => 'Untitled promo card',
      'empty' => 'No promo cards yet', 'emptyHint' => 'Add promo cards to sit beside the main carousel.',
      'confirm' => 'Are you sure you want to delete this promo card?',
      'icon' => '<rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13M3 12h18M7.5 8a2.5 2.5 0 0 1 0-5C10 3 12 8 12 8s2-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
    ],
  ];
@endphp

<div class="space-y-4">

  {{-- Summary --}}
  <div class="grid grid-cols-2 gap-3">
    @foreach($sections as $s)
      <div class="panel p-3.5 sm:p-4 flex items-center gap-3">
        <span class="hidden sm:grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-gray-100 text-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
        </span>
        <div class="min-w-0 flex-1">
          <p class="text-xs text-gray-500">{{ $s['title'] }}</p>
          <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $s['active'] }} <span class="text-sm font-normal text-gray-400">/ {{ $s['items']->count() }} live</span></p>
        </div>
        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $s['active'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $s['active'] > 0 ? 'Showing' : 'Nothing live' }}</span>
      </div>
    @endforeach
  </div>

  @foreach($sections as $s)
    <section class="panel p-4 sm:p-5">
      <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
          <h2 class="text-[15px] font-semibold text-gray-900">{{ $s['title'] }}</h2>
          <p class="text-xs text-gray-500 mt-0.5">{{ $s['hint'] }}</p>
        </div>
        <a href="{{ route('admin.banners.create', ['placement' => $s['placement']]) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1 shrink-0">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
          {{ $s['add'] }}
        </a>
      </div>

      @if($s['items']->isEmpty())
        <div class="text-center py-8 rounded-2xl bg-gray-50">
          <p class="text-sm font-medium text-gray-700">{{ $s['empty'] }}</p>
          <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">{{ $s['emptyHint'] }}</p>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
          @foreach($s['items'] as $banner)
            <article class="rounded-2xl bg-gray-50 overflow-hidden flex flex-col {{ $banner->is_active ? '' : 'opacity-70' }}">
              <div class="relative w-full {{ $s['aspect'] }} bg-gray-200 overflow-hidden">
                @if($banner->image)
                  <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                @else
                  <div class="w-full h-full flex items-center justify-center text-gray-500 text-xs">No image uploaded</div>
                @endif
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/90 text-gray-800">#{{ $banner->position }}</span>
                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $banner->is_active ? 'Live' : 'Hidden' }}</span>
              </div>

              <div class="p-3 flex-1 min-w-0">
                <h3 class="text-[13px] font-semibold text-gray-900 truncate" title="{{ $banner->title }}">{{ $banner->title ?: $s['untitled'] }}</h3>
                <a href="{{ $banner->linkHref() }}" target="_blank" class="mt-1 flex items-center gap-1 text-[11px] text-gray-500 hover:text-gray-800 min-w-0" title="{{ $banner->link_url ?: route('shop') }}">
                  <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
                  <span class="truncate">{{ $banner->link_url ?: '/shop' }}</span>
                </a>
              </div>

              <div class="px-3 pb-3 flex items-center gap-1.5">
                <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="h-8 px-3 rounded-full bg-white hover:bg-gray-100 text-gray-700 text-xs font-medium">
                    {{ $banner->is_active ? 'Hide' : 'Show' }}
                  </button>
                </form>
                <a href="{{ route('admin.banners.edit', $banner) }}" class="h-8 px-3 rounded-full text-white text-xs font-semibold inline-flex items-center" style="background: var(--brand-dark);">Edit</a>
                <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="ml-auto" onsubmit="return confirm('{{ $s['confirm'] }}');">
                  @csrf @method('DELETE')
                  <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 inline-flex items-center justify-center" title="Delete" aria-label="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                  </button>
                </form>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </section>
  @endforeach

</div>
@endsection
