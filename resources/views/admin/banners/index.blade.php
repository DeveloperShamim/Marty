@extends('layouts.admin')
@section('title', 'Banners & Hero Management')

@section('content')
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Homepage Banners</h2>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary border border-primary/20">
          AppleGadgetsBD Layout
        </span>
      </div>
      <p class="text-xs sm:text-sm text-gray-500 mt-1">
        Manage rotating hero carousel slides and side/bottom promo cards. Changes reflect instantly on the storefront.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ url('/') }}" target="_blank" class="px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        View Storefront
      </a>
      <a href="{{ route('admin.banners.create', ['placement' => 'hero']) }}" class="btn-primary text-xs sm:text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Slide
      </a>
    </div>
  </div>

  @php
    $heroSlides = $banners->where('placement', 'hero');
    $promoCards = $banners->where('placement', 'hero_side');
    $activeHeroCount = $heroSlides->where('is_active', true)->count();
    $activePromoCount = $promoCards->where('is_active', true)->count();
  @endphp

  {{-- Visual Layout Blueprint Card --}}
  <div class="card p-5 sm:p-6 bg-gradient-to-br from-white to-gray-50 border-gray-200 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <div class="flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Live Layout Blueprint</h3>
      </div>
      <span class="text-xs text-gray-400">Desktop: 70% Slider + 30% Promo &middot; Mobile: Stacked Slider + 2-Col Cards</span>
    </div>

    {{-- Interactive Blueprint Wireframe --}}
    <div class="grid grid-cols-1 lg:grid-cols-10 gap-3 p-3 bg-gray-900/5 rounded-2xl border border-gray-200/80">
      
      {{-- Blueprint Left: Main Carousel --}}
      <div class="lg:col-span-7 bg-white rounded-xl border-2 border-dashed {{ $activeHeroCount > 0 ? 'border-primary/40' : 'border-amber-400/60' }} p-4 flex flex-col justify-between min-h-[140px] shadow-sm relative overflow-hidden group">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-primary/10 text-primary text-[11px] font-bold uppercase tracking-wider">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              Hero Main Carousel (70% Width)
            </div>
            <p class="text-xs text-gray-500 mt-1">Recommended size: <strong>1500 &times; 800 px</strong> (Aspect ratio 15:8)</p>
          </div>
          <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $activeHeroCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
            {{ $activeHeroCount }} Active / {{ $heroSlides->count() }} Total
          </span>
        </div>

        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400">
          <div class="flex items-center gap-1.5">
            <span class="w-5 h-1.5 rounded-full bg-orange-500"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            <span class="text-[11px] ml-1">Auto-plays with smooth fade & touch controls</span>
          </div>
          <a href="{{ route('admin.banners.create', ['placement' => 'hero']) }}" class="text-primary font-semibold hover:underline inline-flex items-center gap-1">
            + Add Slide &rarr;
          </a>
        </div>
      </div>

      {{-- Blueprint Right: Promo Cards --}}
      <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-1 gap-2">
        <div class="bg-white rounded-xl border-2 border-dashed {{ $activePromoCount >= 1 ? 'border-amber-500/40' : 'border-gray-300' }} p-3 flex flex-col justify-between min-h-[66px] shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-gray-700">Promo Slot 1 (Top)</span>
            <span class="text-[10px] text-gray-400">868 &times; 476 px</span>
          </div>
          <div class="text-[11px] text-gray-500 truncate mt-1">
            {{ $promoCards->firstWhere('position', 0)?->title ?? ($promoCards->values()->get(0)?->title ?? 'Empty slot') }}
          </div>
        </div>

        <div class="bg-white rounded-xl border-2 border-dashed {{ $activePromoCount >= 2 ? 'border-amber-500/40' : 'border-gray-300' }} p-3 flex flex-col justify-between min-h-[66px] shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-gray-700">Promo Slot 2 (Bottom)</span>
            <span class="text-[10px] text-gray-400">868 &times; 476 px</span>
          </div>
          <div class="text-[11px] text-gray-500 truncate mt-1">
            {{ $promoCards->firstWhere('position', 1)?->title ?? ($promoCards->values()->get(1)?->title ?? 'Empty slot') }}
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- SECTION 1: HERO CAROUSEL SLIDES --}}
  <div class="card p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-gray-100">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">1</span>
          <h3 class="text-base font-bold text-gray-900">Hero Main Carousel Slides</h3>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">These banners rotate automatically on the left side of desktop and top of mobile.</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg font-medium">
          {{ $activeHeroCount }} visible &middot; {{ $heroSlides->count() }} total
        </span>
        <a href="{{ route('admin.banners.create', ['placement' => 'hero']) }}" class="btn-primary text-xs py-1.5 px-3">
          + Add Slide
        </a>
      </div>
    </div>

    @if($heroSlides->isEmpty())
      <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="text-sm font-semibold text-gray-700">No Hero Carousel Slides Yet</p>
        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Add your first slide to showcase flagship promotions, new arrivals, or season deals.</p>
        <a href="{{ route('admin.banners.create', ['placement' => 'hero']) }}" class="btn-primary text-xs mt-4 inline-flex">
          + Create First Hero Slide
        </a>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($heroSlides as $slide)
          <div class="group border border-gray-200 hover:border-primary/40 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $slide->is_active ? '' : 'opacity-65 bg-gray-50/50' }}">
            <div>
              {{-- Banner Preview Aspect Ratio (15/8) --}}
              <div class="relative w-full aspect-[15/8] bg-gray-900 overflow-hidden">
                @if($slide->image)
                  <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                  <div class="w-full h-full bg-gradient-to-br from-brand-600 to-brand-800 flex items-center justify-center text-white text-xs font-semibold p-4 text-center">
                    No image &mdash; gradient fallback
                  </div>
                @endif

                {{-- Badges on preview --}}
                <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide uppercase bg-black/60 backdrop-blur-sm text-white">
                    Pos #{{ $slide->position }}
                  </span>
                  @if($slide->badge)
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary text-white shadow-sm">
                      {{ $slide->badge }}
                    </span>
                  @endif
                </div>

                <div class="absolute top-2.5 right-2.5">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shadow-sm {{ $slide->is_active ? 'bg-emerald-500 text-white' : 'bg-gray-600 text-white' }}">
                    {{ $slide->is_active ? '● Live' : '○ Hidden' }}
                  </span>
                </div>
              </div>

              {{-- Slide Details --}}
              <div class="p-4 space-y-2">
                <h4 class="font-bold text-sm text-gray-900 line-clamp-1" title="{{ $slide->title }}">
                  {{ $slide->title ?: 'Untitled Slide' }}
                </h4>
                @if($slide->subtitle)
                  <p class="text-xs text-gray-500 line-clamp-2">{{ $slide->subtitle }}</p>
                @endif

                <div class="pt-2 flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
                  @if($slide->link_url)
                    <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-md font-mono text-gray-600 truncate max-w-[200px]" title="{{ $slide->link_url }}">
                      <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                      {{ $slide->link_url }}
                    </span>
                  @endif
                  @if($slide->button_text)
                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md font-medium">
                      Btn: {{ $slide->button_text }}
                    </span>
                  @endif
                </div>
              </div>
            </div>

            {{-- Actions Footer --}}
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
              <span class="text-gray-400 font-medium">Order: {{ $slide->position }}</span>
              <div class="flex items-center gap-1.5">
                {{-- Quick Toggle Visibility --}}
                <form method="POST" action="{{ route('admin.banners.toggle', $slide) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $slide->is_active ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' }}">
                    {{ $slide->is_active ? 'Hide' : 'Show' }}
                  </button>
                </form>

                {{-- Edit --}}
                <a href="{{ route('admin.banners.edit', $slide) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 transition-colors inline-flex items-center gap-1">
                  <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  Edit
                </a>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.banners.destroy', $slide) }}" onsubmit="return confirm('Are you sure you want to delete this banner slide?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 transition-colors">
                    Del
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  {{-- SECTION 2: PROMO CARDS --}}
  <div class="card p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-gray-100">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xs">2</span>
          <h3 class="text-base font-bold text-gray-900">Featured Promo Cards (Side &amp; Bottom)</h3>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">Top 2 active cards display stacked beside carousel on desktop, or in a 2-column grid on mobile.</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg font-medium">
          {{ $activePromoCount }} visible &middot; {{ $promoCards->count() }} total
        </span>
        <a href="{{ route('admin.banners.create', ['placement' => 'hero_side']) }}" class="btn-primary text-xs py-1.5 px-3 bg-amber-600 hover:bg-amber-700">
          + Add Promo Card
        </a>
      </div>
    </div>

    @if($promoCards->isEmpty())
      <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <p class="text-sm font-semibold text-gray-700">No Featured Promo Cards Yet</p>
        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Add up to 2 promo cards (e.g. MacBook Neo or AirPods Pro offers) to flank the main hero carousel.</p>
        <a href="{{ route('admin.banners.create', ['placement' => 'hero_side']) }}" class="btn-primary text-xs mt-4 inline-flex bg-amber-600 hover:bg-amber-700">
          + Create First Promo Card
        </a>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($promoCards as $card)
          <div class="group border border-gray-200 hover:border-amber-400/60 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-all flex flex-col justify-between {{ $card->is_active ? '' : 'opacity-65 bg-gray-50/50' }}">
            <div>
              {{-- Banner Preview Aspect Ratio (1.82:1) --}}
              <div class="relative w-full aspect-[1.82/1] bg-gray-900 overflow-hidden">
                @if($card->image)
                  <img src="{{ $card->imageUrl() }}" alt="{{ $card->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                  <div class="w-full h-full bg-gradient-to-br from-amber-600 to-stone-800 flex items-center justify-center text-white text-xs font-semibold p-4 text-center">
                    No image &mdash; gradient fallback
                  </div>
                @endif

                {{-- Badges --}}
                <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide uppercase bg-black/60 backdrop-blur-sm text-white">
                    Slot Pos #{{ $card->position }}
                  </span>
                  @if($card->badge)
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500 text-white shadow-sm">
                      {{ $card->badge }}
                    </span>
                  @endif
                </div>

                <div class="absolute top-2.5 right-2.5">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shadow-sm {{ $card->is_active ? 'bg-emerald-500 text-white' : 'bg-gray-600 text-white' }}">
                    {{ $card->is_active ? '● Live' : '○ Hidden' }}
                  </span>
                </div>
              </div>

              {{-- Card Details --}}
              <div class="p-4 space-y-2">
                <h4 class="font-bold text-sm text-gray-900 line-clamp-1" title="{{ $card->title }}">
                  {{ $card->title ?: 'Untitled Promo Card' }}
                </h4>
                @if($card->subtitle)
                  <p class="text-xs text-gray-500 line-clamp-2">{{ $card->subtitle }}</p>
                @endif

                <div class="pt-2 flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
                  @if($card->link_url)
                    <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-md font-mono text-gray-600 truncate max-w-[200px]" title="{{ $card->link_url }}">
                      <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                      {{ $card->link_url }}
                    </span>
                  @endif
                  @if($card->button_text)
                    <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-medium border border-amber-200">
                      Btn: {{ $card->button_text }}
                    </span>
                  @endif
                </div>
              </div>
            </div>

            {{-- Actions Footer --}}
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
              <span class="text-gray-400 font-medium">Order: {{ $card->position }}</span>
              <div class="flex items-center gap-1.5">
                {{-- Quick Toggle Visibility --}}
                <form method="POST" action="{{ route('admin.banners.toggle', $card) }}">
                  @csrf @method('PATCH')
                  <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $card->is_active ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' }}">
                    {{ $card->is_active ? 'Hide' : 'Show' }}
                  </button>
                </form>

                {{-- Edit --}}
                <a href="{{ route('admin.banners.edit', $card) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 transition-colors inline-flex items-center gap-1">
                  <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  Edit
                </a>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.banners.destroy', $card) }}" onsubmit="return confirm('Are you sure you want to delete this promo card?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 transition-colors">
                    Del
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>
@endsection
