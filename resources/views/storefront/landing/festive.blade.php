{{-- Festive: a framed banner with a discount seal, a coupon-style offer box, round feature pictures --}}
@php($featureImg = fn ($f, $i) => $f['image'] ? image_url($f['image'], $f['title'] ?? '') : ($gallery[$i] ?? null))

<header class="relative px-4 pt-6 pb-10 sm:pb-14 text-center overflow-hidden" style="background: linear-gradient(180deg, var(--lp), color-mix(in srgb, var(--lp) 70%, #000));">
  <div class="pointer-events-none absolute inset-3 sm:inset-5 rounded-[2rem] border-2 border-dashed" style="border-color: #e7c77a;"></div>
  <div class="relative">
    <div class="inline-flex rounded-2xl bg-white/95 px-4 py-2">@include('partials.brand', ['size' => 'md'])</div>
    <p class="mt-5 text-[#fde68a] text-lg tracking-[0.5em]" aria-hidden="true">✦ ✦ ✦</p>
    @if(filled($hero['badge']))<p class="mt-2 text-sm sm:text-base font-bold text-[#fde68a]">{{ $hero['badge'] }}</p>@endif
    <h1 class="lp-fest-title mt-2 max-w-3xl mx-auto text-[28px] leading-snug sm:text-5xl sm:leading-tight font-extrabold text-white">
      {{ $hero['part1'] }} @if(filled($hero['part2']))<span class="text-[#fde68a]">{{ $hero['part2'] }}</span>@endif {{ $hero['part3'] }}
    </h1>
  </div>
</header>

<section class="relative max-w-4xl mx-auto px-4 -mt-6 sm:-mt-8" data-lp-section="gallery">
  <div class="grid gap-6 md:grid-cols-[1.1fr_1fr] md:items-center">
    <div class="relative rounded-[1.75rem] p-2 bg-white shadow-xl" style="box-shadow: 0 0 0 3px #e7c77a, 0 20px 40px -20px rgba(0,0,0,.35);">
      @if($discountBadge)
        <div class="absolute -right-3 -top-3 z-20 grid h-20 w-20 sm:h-24 sm:w-24 place-items-center rounded-full text-center font-extrabold text-white rotate-12 shadow-lg" style="background: var(--lp); box-shadow: 0 0 0 4px #e7c77a;">
          <span class="leading-tight"><span class="block text-xl sm:text-2xl">{{ $discountBadge }}</span><span class="block text-[11px]">ছাড়</span></span>
        </div>
      @endif
      @include('storefront.landing.parts.gallery')
    </div>

    {{-- Coupon-style offer box --}}
    <div class="relative rounded-3xl border-2 border-dashed bg-white p-6 text-center" style="border-color: #c9a227;">
      <span class="absolute -left-3 top-1/2 h-6 w-6 -translate-y-1/2 rounded-full" style="background: var(--lp-base);"></span>
      <span class="absolute -right-3 top-1/2 h-6 w-6 -translate-y-1/2 rounded-full" style="background: var(--lp-base);"></span>
      <p class="text-sm font-bold tracking-widest" style="color: #a16207;">✦ বিশেষ উৎসব অফার ✦</p>
      @if($compare)<p class="mt-3 text-lg text-gray-500 line-through">{{ money($compare) }}</p>@endif
      <p class="text-5xl font-extrabold lp-accent" data-lp-price>{{ money($price) }}</p>
      @if($discountText)<p class="mt-2 inline-block rounded-full px-3 py-1 text-sm font-bold" style="background: #fde68a; color: #713f12;">{{ $discountText }}</p>@endif
      <a href="#order" class="lp-btn mt-5 flex py-3.5 items-center justify-center rounded-full text-lg font-extrabold" data-lp-cta>{{ $buttonText }}</a>
      <p class="mt-3 text-[13px] text-gray-600">ক্যাশ অন ডেলিভারি · পণ্য দেখে মূল্য পরিশোধ</p>
      <p class="mt-1 text-[12px] font-semibold" style="color: #a16207;">স্টক সীমিত, আজই অর্ডার করুন</p>
    </div>
  </div>
</section>
@include('storefront.landing.parts.custom', ['at' => 'hero'])

{{-- Round feature pictures --}}
@if($page->sectionOn('features') && $features->isNotEmpty())
  <section class="py-12 sm:py-16" data-lp-section="features">
    <div class="max-w-5xl mx-auto px-4">
      @if(filled($d['features']['heading'] ?? null))<h2 class="text-center text-xl sm:text-3xl font-extrabold">{{ $d['features']['heading'] }}</h2>@endif
      <div class="mt-10 grid gap-10 sm:grid-cols-3">
        @foreach($features as $i => $f)
          <div class="text-center">
            @if($img = $featureImg($f, $i))
              <img src="{{ $img }}" alt="{{ $f['title'] }}" class="mx-auto h-36 w-36 rounded-full object-cover" style="box-shadow: 0 0 0 4px #fff, 0 0 0 7px #e7c77a;" loading="lazy">
            @endif
            <h3 class="mt-5 text-lg font-bold">{{ $f['title'] }}</h3>
            <p class="mt-1.5 text-[15px] leading-relaxed text-gray-600">{{ $f['text'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'features'])

@foreach(['video', 'reviews', 'specs', 'faq', 'contact'] as $part)
  @include('storefront.landing.parts.' . $part)
  @include('storefront.landing.parts.custom', ['at' => $part])
@endforeach

<div class="max-w-5xl mx-auto px-4 pb-6">
  <div class="rounded-[2rem] p-1.5" style="background: linear-gradient(135deg, #e7c77a, #fff3c4, #c9a227);">
    @include('storefront.landing.parts.form', ['formSection' => 'rounded-[1.7rem] bg-gray-50 py-10 sm:py-12', 'formWidth' => 'px-4 sm:px-8'])
  </div>
</div>
@include('storefront.landing.parts.custom', ['at' => 'form'])
