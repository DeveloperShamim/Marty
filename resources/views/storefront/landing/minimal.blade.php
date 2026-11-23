{{-- Editorial: photos stacked on the left (sticky on computers), the story on the right like a magazine page --}}
@php($featureImg = fn ($f, $i) => $f['image'] ? image_url($f['image'], $f['title'] ?? '') : null)

<div class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 flex items-center justify-between border-b border-gray-200 pb-4">
  @include('partials.brand', ['size' => 'sm'])
  <a href="#order" class="text-[13px] font-semibold tracking-wide underline underline-offset-4">{{ $buttonText }}</a>
</div>

<section class="max-w-6xl mx-auto px-4 sm:px-6 py-8 lg:py-12 grid gap-8 lg:grid-cols-[1.1fr_1fr] lg:gap-14" data-lp-section="gallery">
  {{-- Photos: swipe on phones, stacked and sticky on computers --}}
  <div class="min-w-0">
    <div class="lg:sticky lg:top-6">
      {{-- Phones swipe through them; computers show the first large and the rest two by two --}}
      <div class="flex lg:grid lg:grid-cols-2 gap-3 overflow-x-auto snap-x snap-mandatory -mx-4 px-4 lg:mx-0 lg:px-0 lg:overflow-visible">
        @foreach($gallery as $i => $src)
          <img src="{{ $src }}" alt="{{ $page->title }}" class="w-[82%] sm:w-[60%] lg:w-full shrink-0 snap-center object-cover bg-gray-50 {{ $i === 0 ? 'aspect-[4/5] lg:col-span-2 lg:aspect-[5/4]' : 'aspect-[4/5] lg:aspect-square' }}" @if($i === 0) id="lpMain" fetchpriority="high" @else loading="lazy" @endif>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Story --}}
  <div class="min-w-0">
    @if(filled($hero['badge']))<p class="text-[12px] font-semibold uppercase tracking-[0.2em] text-gray-500">{{ $hero['badge'] }}</p>@endif
    <h1 class="mt-3 text-[30px] leading-tight sm:text-[42px] font-semibold">
      {{ $hero['part1'] }} @if(filled($hero['part2']))<em class="not-italic underline decoration-2 underline-offset-8" style="text-decoration-color: var(--lp);">{{ $hero['part2'] }}</em>@endif {{ $hero['part3'] }}
    </h1>
    <div class="mt-6 flex items-baseline gap-3 border-y border-gray-200 py-4">
      <p class="text-3xl font-semibold" data-lp-price>{{ money($price) }}</p>
      @if($compare)<p class="text-lg text-gray-400 line-through">{{ money($compare) }}</p>@endif
      @if($discountBadge)<p class="ml-auto text-[13px] font-semibold uppercase tracking-widest">{{ $discountBadge }}</p>@endif
    </div>
    <a href="#order" class="lp-btn mt-5 flex py-4 items-center justify-center text-[15px] font-semibold" data-lp-cta>{{ $buttonText }}</a>
    <p class="mt-3 text-[13px] text-gray-500">ক্যাশ অন ডেলিভারি · পণ্য দেখে মূল্য পরিশোধ</p>

    {{-- Numbered points --}}
    @if($page->sectionOn('features') && $features->isNotEmpty())
      <div class="mt-10" data-lp-section="features">
        @if(filled($d['features']['heading'] ?? null))<h2 class="text-left">{{ $d['features']['heading'] }}</h2>@endif
        <ol class="mt-5 space-y-6">
          @foreach($features as $i => $f)
            <li class="grid grid-cols-[3rem_1fr] gap-3">
              <span class="text-3xl font-light text-gray-300 tabular-nums">{{ sprintf('%02d', $i + 1) }}</span>
              <div>
                <h3 class="font-semibold text-[17px]">{{ $f['title'] }}</h3>
                <p class="mt-1 text-[15px] leading-relaxed text-gray-600">{{ $f['text'] }}</p>
                @if($img = $featureImg($f, $i))<img src="{{ $img }}" alt="{{ $f['title'] }}" class="mt-3 w-full aspect-[16/10] object-cover" loading="lazy">@endif
              </div>
            </li>
          @endforeach
        </ol>
      </div>
    @endif

    {{-- Details as a quiet list --}}
    @if($page->sectionOn('specs') && count($specRows))
      <div class="mt-10" data-lp-section="specs">
        @if(filled($d['specs']['heading'] ?? null))<h2 class="text-left">{{ $d['specs']['heading'] }}</h2>@endif
        <dl class="mt-5 divide-y divide-gray-200 border-y border-gray-200 text-[15px]">
          @foreach($specRows as $row)
            <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">{{ $row['label'] }}</dt><dd class="text-right font-medium">{{ $row['value'] }}</dd></div>
          @endforeach
        </dl>
      </div>
    @endif

    {{-- Plain questions --}}
    @if($page->sectionOn('faq') && $faqs->isNotEmpty())
      <div class="mt-10" data-lp-section="faq">
        @if(filled($d['faq']['heading'] ?? null))<h2 class="text-left">{{ $d['faq']['heading'] }}</h2>@endif
        <div class="mt-5 divide-y divide-gray-200 border-y border-gray-200">
          @foreach($faqs as $f)
            <details class="py-3">
              <summary class="flex cursor-pointer list-none justify-between gap-3 font-medium"><span>{{ $f['q'] }}</span><span class="lp-plus transition-transform">+</span></summary>
              <p class="mt-2 text-[15px] leading-relaxed text-gray-600">{{ $f['a'] }}</p>
            </details>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</section>
@foreach(['hero', 'features', 'specs', 'faq'] as $spot)
  @include('storefront.landing.parts.custom', ['at' => $spot])
@endforeach

@include('storefront.landing.parts.video')
@include('storefront.landing.parts.custom', ['at' => 'video'])

{{-- Reviews as a quiet strip --}}
@if($page->sectionOn('reviews') && $reviewImages->isNotEmpty())
  <section class="py-12 border-t border-gray-200" data-lp-section="reviews">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      @if(filled($d['reviews']['heading'] ?? null))<h2>{{ $d['reviews']['heading'] }}</h2>@endif
      <div class="mt-6 flex gap-4 overflow-x-auto snap-x pb-2">
        @foreach($reviewImages as $img)
          <img src="{{ image_url($img, 'review') }}" alt="কাস্টমার রিভিউ" class="h-80 w-auto shrink-0 snap-start border border-gray-200" loading="lazy">
        @endforeach
      </div>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'reviews'])

@include('storefront.landing.parts.contact')
@include('storefront.landing.parts.custom', ['at' => 'contact'])

@include('storefront.landing.parts.form', ['formSection' => 'py-12 sm:py-16 border-t border-gray-200', 'formWidth' => 'max-w-6xl mx-auto px-4 sm:px-6'])
@include('storefront.landing.parts.custom', ['at' => 'form'])
