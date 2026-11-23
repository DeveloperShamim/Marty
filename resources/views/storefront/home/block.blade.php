{{-- A section the owner built in Admin → Homepage → Sections (App\Models\HomeBlock), placed in the saved order --}}
@php $href = fn ($l) => \App\Models\HomeBlock::href($l); @endphp

@if($block->type === 'products')
  @php $rowProducts = $block->relationLoaded('rowProducts') ? $block->getRelation('rowProducts') : $block->products(); $rowTitle = $block->title ?: ($block->category()?->name ?? 'Products'); @endphp
  @if($rowProducts->isNotEmpty())
    <section class="mt-10 sm:mt-14" data-reveal data-home-block="products">
      <x-home-heading :title="$rowTitle" />
      @if($block->subtitle)<p class="-mt-3 sm:-mt-4 mb-4 text-xs sm:text-sm text-stone-500">{{ $block->subtitle }}</p>@endif
      <div class="flex gap-2.5 sm:gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth scroll-px-3 sm:scroll-px-0 -mx-3 px-3 sm:mx-0 sm:px-0 pb-1" data-auto-row>
        @foreach($rowProducts as $product)
          <div class="prow-item w-[calc((100%-0.625rem)/2)] shrink-0 snap-start sm:w-[calc((100%-3rem)/4)] grid min-w-0">
            @include('storefront.partials.product-card', ['product' => $product])
          </div>
        @endforeach
      </div>
      @include('storefront.partials.see-all', ['href' => $block->seeAllHref(), 'label' => $block->get('link_label') ? null : $rowTitle, 'text' => $block->get('link_label')])
    </section>
  @endif

@elseif($block->type === 'promo_band')
  <section class="mt-10 sm:mt-14" data-reveal data-home-block="promo_band">
    <div class="rounded-2xl px-5 py-6 sm:px-10 sm:py-9 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-8" style="background: {{ $block->get('bg', '#111827') }}; color: {{ $block->get('fg', '#ffffff') }};">
      <div class="min-w-0 flex-1">
        @if($block->title)<h2 class="text-lg sm:text-2xl font-extrabold leading-tight">{{ $block->title }}</h2>@endif
        @if($block->subtitle)<p class="mt-1.5 text-[13px] sm:text-[15px] opacity-80">{{ $block->subtitle }}</p>@endif
      </div>
      @if($block->get('button_text'))
        <a href="{{ $href($block->get('link_url')) }}" class="shrink-0 inline-flex items-center justify-center h-11 px-6 rounded-xl text-sm font-bold active:scale-[0.98] transition-transform" style="background: {{ $block->get('fg', '#ffffff') }}; color: {{ $block->get('bg', '#111827') }};">{{ $block->get('button_text') }}</a>
      @endif
    </div>
  </section>

@elseif($block->type === 'image_banner' && $block->get('image'))
  <section class="mt-10 sm:mt-14" data-reveal data-home-block="image_banner">
    <a href="{{ $href($block->get('link_url')) }}" class="block rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" aria-label="{{ $block->title ?: 'Banner' }}">
      <picture>
        @if($block->get('image_mobile'))<source media="(max-width: 639px)" srcset="{{ image_url($block->get('image_mobile')) }}">@endif
        <img src="{{ image_url($block->get('image')) }}" alt="{{ $block->title }}" class="w-full h-auto block" loading="lazy">
      </picture>
    </a>
  </section>

@elseif($block->type === 'card_grid' && count($block->cards()))
  @php $cols = ['2' => 'lg:grid-cols-2', '3' => 'lg:grid-cols-3', '4' => 'lg:grid-cols-4'][(string) $block->get('columns', 3)] ?? 'lg:grid-cols-3'; @endphp
  <section class="mt-10 sm:mt-14" data-reveal data-home-block="card_grid">
    @if($block->title)<x-home-heading :title="$block->title" />@endif
    @if($block->subtitle)<p class="-mt-3 sm:-mt-4 mb-4 text-xs sm:text-sm text-stone-500">{{ $block->subtitle }}</p>@endif
    <div class="grid grid-cols-2 {{ $cols }} gap-2.5 sm:gap-4">
      @foreach($block->cards() as $card)
        <a href="{{ $href($card['link'] ?? '') }}" class="group rounded-2xl overflow-hidden bg-white border border-stone-200/80 shadow-2xs hover:shadow-md transition-shadow flex flex-col">
          @if(! empty($card['image']))
            <span class="block aspect-[4/3] overflow-hidden bg-stone-100"><img src="{{ image_url($card['image']) }}" alt="{{ $card['title'] }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500" loading="lazy"></span>
          @endif
          @if($card['title'] !== '' || $card['text'] !== '')
            <span class="block p-3 sm:p-4">
              @if($card['title'] !== '')<span class="block text-[13px] sm:text-[15px] font-bold text-stone-900 leading-snug">{{ $card['title'] }}</span>@endif
              @if($card['text'] !== '')<span class="block mt-0.5 text-[11px] sm:text-[13px] text-stone-500 line-clamp-2">{{ $card['text'] }}</span>@endif
            </span>
          @endif
        </a>
      @endforeach
    </div>
  </section>

@elseif($block->type === 'stats_row' && count($block->stats()))
  @php $n = count($block->stats()); @endphp
  <section class="mt-10 sm:mt-14" data-reveal data-home-block="stats_row">
    @if($block->title)<x-home-heading :title="$block->title" />@endif
    @if($block->subtitle)<p class="-mt-3 sm:-mt-4 mb-4 text-xs sm:text-sm text-stone-500">{{ $block->subtitle }}</p>@endif
    <div class="grid grid-cols-2 {{ $n >= 3 ? 'sm:grid-cols-3' : '' }} {{ [4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5', 6 => 'lg:grid-cols-6'][$n] ?? '' }} gap-2.5 sm:gap-4">
      @foreach($block->stats() as $stat)
        <div class="rounded-2xl bg-white border border-stone-200/80 shadow-2xs px-4 py-5 text-center">
          <p class="text-2xl sm:text-3xl font-extrabold text-brand-600 tabular-nums leading-none">{{ $stat['value'] }}</p>
          @if($stat['label'] !== '')<p class="mt-1.5 text-[11px] sm:text-[13px] text-stone-600">{{ $stat['label'] }}</p>@endif
        </div>
      @endforeach
    </div>
  </section>
@endif
