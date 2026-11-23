{{-- Quick order: photos and price on the left, the order form right beside them at the top; details below --}}
<div class="border-b border-gray-200">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
    @include('partials.brand', ['size' => 'sm'])
    @if(filled($contact['phone'] ?? null))
      <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}" class="text-[13px] font-semibold lp-accent">📞 {{ $contact['phone'] }}</a>
    @endif
  </div>
</div>

<section class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 lg:pt-8 grid gap-6 lg:grid-cols-[1fr_1.05fr] lg:gap-10 items-start" data-lp-section="gallery">
  <div class="min-w-0 lg:sticky lg:top-4">
    @if(filled($hero['badge']))<p class="text-[13px] font-bold lp-accent">● {{ $hero['badge'] }}</p>@endif
    <h1 class="mt-1 text-[24px] leading-snug sm:text-3xl font-bold">
      {{ $hero['part1'] }} @if(filled($hero['part2']))<span class="lp-accent">{{ $hero['part2'] }}</span>@endif {{ $hero['part3'] }}
    </h1>
    <div class="mt-4">@include('storefront.landing.parts.gallery')</div>
    <div class="mt-4 flex items-center gap-3 rounded-2xl p-4" style="background: color-mix(in srgb, var(--lp) 8%, var(--lp-base));">
      <p class="text-3xl font-extrabold lp-accent" data-lp-price>{{ money($price) }}</p>
      @if($compare)<p class="text-lg text-gray-400 line-through">{{ money($compare) }}</p>@endif
      @if($discountText)<p class="ml-auto rounded-full bg-rose-600 px-3 py-1 text-xs font-bold text-white">{{ $discountText }}</p>@endif
    </div>
    @if($page->sectionOn('features') && $features->isNotEmpty())
      <ul class="mt-4 space-y-2" data-lp-section="features">
        @foreach($features as $f)
          <li class="flex gap-2.5 text-[15px]"><span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full text-[11px] font-bold" style="background: var(--lp); color: var(--lp-on);">✓</span><span><b>{{ $f['title'] }}</b>@if(filled($f['text'])) <span class="text-gray-600">· {{ \Illuminate\Support\Str::limit($f['text'], 90) }}</span>@endif</span></li>
        @endforeach
      </ul>
    @endif
  </div>

  <div class="min-w-0">
    @include('storefront.landing.parts.form', ['formSection' => 'rounded-3xl border-2 py-6 sm:py-8 lp-quick-form', 'formWidth' => 'px-4 sm:px-6', 'formStacked' => true])
  </div>
</section>
@foreach(['hero', 'features', 'form'] as $spot)
  @include('storefront.landing.parts.custom', ['at' => $spot])
@endforeach

<div class="mt-10 sm:mt-14 border-t border-gray-200"></div>
@foreach(['video', 'specs', 'reviews', 'faq', 'contact'] as $part)
  @include('storefront.landing.parts.' . $part)
  @include('storefront.landing.parts.custom', ['at' => $part])
@endforeach

<div class="max-w-xl mx-auto px-4 pb-4">
  <a href="#order" class="lp-btn flex h-12 items-center justify-center rounded-xl text-base font-bold">{{ $buttonText }} ↑</a>
</div>
