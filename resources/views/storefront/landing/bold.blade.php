{{-- Bold dark: full-width hero with the photo beside a huge headline, then cards, tiles and a review wall --}}
@php($featureImg = fn ($f, $i) => $f['image'] ? image_url($f['image'], $f['title'] ?? '') : ($gallery[$i] ?? null))

<section class="relative overflow-hidden" data-lp-section="gallery">
  <div class="pointer-events-none absolute -right-40 -top-40 h-[34rem] w-[34rem] rounded-full blur-3xl opacity-30" style="background: var(--lp);"></div>
  <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-10 lg:py-16 grid gap-8 lg:grid-cols-2 lg:items-center">
    <div class="lg:order-2">
      @include('storefront.landing.parts.gallery')
    </div>
    <div class="lg:order-1">
      <div class="lp-logo-light">@include('partials.brand', ['size' => 'md'])</div>
      @if(filled($hero['badge']))
        <p class="mt-6 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider" style="background: var(--lp); color: var(--lp-on);">⚡ {{ $hero['badge'] }}</p>
      @endif
      <h1 class="mt-4 text-[34px] leading-[1.1] sm:text-5xl lg:text-6xl font-extrabold">
        {{ $hero['part1'] }} @if(filled($hero['part2']))<span class="lp-accent">{{ $hero['part2'] }}</span>@endif {{ $hero['part3'] }}
      </h1>
      <div class="mt-6 flex flex-wrap items-end gap-x-4 gap-y-1">
        <p class="text-4xl sm:text-5xl font-extrabold lp-accent" data-lp-price>{{ money($price) }}</p>
        @if($compare)<p class="pb-1 text-xl font-bold text-gray-500 line-through">{{ money($compare) }}</p>@endif
        @if($discountText)<p class="pb-1.5 rounded-md bg-rose-600 px-2 py-0.5 text-sm font-bold text-white">{{ $discountText }}</p>@endif
      </div>
      <a href="#order" class="lp-btn mt-6 inline-flex h-14 w-full sm:w-auto items-center justify-center gap-2 rounded-full px-10 text-lg font-extrabold" data-lp-cta>{{ $buttonText }} →</a>
      <ul class="mt-6 grid grid-cols-3 gap-3 text-center text-[12px] sm:text-[13px] text-gray-400">
        <li class="rounded-xl border border-gray-200 px-2 py-3">🚚<br>সারাদেশে ডেলিভারি</li>
        <li class="rounded-xl border border-gray-200 px-2 py-3">💵<br>ক্যাশ অন ডেলিভারি</li>
        <li class="rounded-xl border border-gray-200 px-2 py-3">✅<br>দেখে মূল্য পরিশোধ</li>
      </ul>
    </div>
  </div>
</section>
@include('storefront.landing.parts.custom', ['at' => 'hero'])

{{-- Feature cards: picture on top, three across --}}
@if($page->sectionOn('features') && $features->isNotEmpty())
  <section class="py-12 sm:py-16 border-t border-gray-200" data-lp-section="features">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      @if(filled($d['features']['heading'] ?? null))<h2 class="text-2xl sm:text-4xl font-extrabold">{{ $d['features']['heading'] }}</h2>@endif
      <div class="mt-8 grid gap-4 sm:grid-cols-3">
        @foreach($features as $i => $f)
          <article class="rounded-3xl bg-white border border-gray-200 overflow-hidden">
            @if($img = $featureImg($f, $i))<img src="{{ $img }}" alt="{{ $f['title'] }}" class="aspect-[4/3] w-full object-cover" loading="lazy">@endif
            <div class="p-5">
              <p class="text-xs font-bold lp-accent">0{{ $i + 1 }}</p>
              <h3 class="mt-1 text-lg font-bold">{{ $f['title'] }}</h3>
              <p class="mt-1.5 text-[15px] leading-relaxed text-gray-400">{{ $f['text'] }}</p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'features'])

@include('storefront.landing.parts.video')
@include('storefront.landing.parts.custom', ['at' => 'video'])

{{-- Details as tiles --}}
@if($page->sectionOn('specs') && count($specRows))
  <section class="py-12 sm:py-16" data-lp-section="specs">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      @if(filled($d['specs']['heading'] ?? null))<h2 class="text-2xl sm:text-4xl font-extrabold">{{ $d['specs']['heading'] }}</h2>@endif
      <dl class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($specRows as $row)
          <div class="rounded-2xl bg-white border border-gray-200 p-4">
            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ $row['label'] }}</dt>
            <dd class="mt-1 text-[15px] font-bold">{{ $row['value'] }}</dd>
          </div>
        @endforeach
      </dl>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'specs'])

{{-- Review wall --}}
@if($page->sectionOn('reviews') && $reviewImages->isNotEmpty())
  <section class="py-12 sm:py-16 border-t border-gray-200" data-lp-section="reviews">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      @if(filled($d['reviews']['heading'] ?? null))<h2 class="text-2xl sm:text-4xl font-extrabold">{{ $d['reviews']['heading'] }}</h2>@endif
      @if(filled($d['reviews']['subtitle'] ?? null))<p class="mt-1 text-gray-500">{{ $d['reviews']['subtitle'] }}</p>@endif
      <div class="mt-8 columns-2 sm:columns-3 lg:columns-4 gap-3 [&>img]:mb-3">
        @foreach($reviewImages as $img)
          <img src="{{ image_url($img, 'review') }}" alt="কাস্টমার রিভিউ" class="w-full break-inside-avoid rounded-2xl border border-gray-200" loading="lazy">
        @endforeach
      </div>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'reviews'])

{{-- Questions: all open, two columns --}}
@if($page->sectionOn('faq') && $faqs->isNotEmpty())
  <section class="py-12 sm:py-16" data-lp-section="faq">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      @if(filled($d['faq']['heading'] ?? null))<h2 class="text-2xl sm:text-4xl font-extrabold">{{ $d['faq']['heading'] }}</h2>@endif
      <div class="mt-8 grid gap-3 sm:grid-cols-2">
        @foreach($faqs as $f)
          <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <p class="font-bold"><span class="lp-accent">Q.</span> {{ $f['q'] }}</p>
            <p class="mt-2 text-[15px] leading-relaxed text-gray-400">{{ $f['a'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif
@include('storefront.landing.parts.custom', ['at' => 'faq'])

@include('storefront.landing.parts.contact')
@include('storefront.landing.parts.custom', ['at' => 'contact'])

@include('storefront.landing.parts.form', ['formSection' => 'py-12 sm:py-16 border-t border-gray-200'])
@include('storefront.landing.parts.custom', ['at' => 'form'])
