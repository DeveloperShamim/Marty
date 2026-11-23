@php
  /** @var \App\Models\LandingPage $page */
  $theme = generate_3_color_matching_theme();
  $accent = $page->accent();
  $template = $page->template();
  $split = $template === 'split';
  // Dark text on a light accent (e.g. yellow), white text on a dark one
  [$ar, $ag, $ab] = sscanf($accent, '#%02x%02x%02x');
  $onAccent = (0.299 * $ar + 0.587 * $ag + 0.114 * $ab) > 170 ? '#111111' : '#ffffff';
  $price = $page->price();
  $compare = $page->comparePrice();
  $savings = $page->savings();
  $discountText = $page->discount_text ?: ($savings > 0 ? money($savings) . ' ছাড়' : null);
  $discountBadge = $page->discount_badge ?: ($compare ? '-' . round($savings / $compare * 100) . '%' : null);
  $hero = $d['hero'];
  $form = $d['form'];
  $videoId = \App\Models\LandingPage::youtubeId($d['video']['url'] ?? '');
  $features = collect($d['features']['items'] ?? [])->filter(fn ($f) => filled($f['title'] ?? null) || filled($f['text'] ?? null))->values();
  $reviewImages = collect($d['reviews']['images'] ?? [])->filter()->values();
  $faqs = collect($d['faq']['items'] ?? [])->filter(fn ($f) => filled($f['q'] ?? null))->values();
  $ticker = collect($d['ticker'] ?? [])->filter()->values();
  $contact = $d['contact'];
  $wa = preg_replace('/\D/', '', (string) ($contact['whatsapp'] ?? ''));
  $wa = $wa !== '' && str_starts_with($wa, '01') ? '88' . $wa : $wa;
  $messenger = trim((string) ($contact['messenger'] ?? ''));
  $messengerUrl = $messenger === '' ? null : (str_starts_with($messenger, 'http') ? $messenger : 'https://m.me/' . ltrim($messenger, '@/'));
  $hasOptions = ! empty($groups);
  $oldZone = old('shipping_zone', '');
  $oldQty = max(1, min($maxQty, (int) old('qty', 1)));
  $shipInside = $quotes[1]['inside_dhaka']['shipping'];
  $shipOutside = $quotes[1]['outside_dhaka']['shipping'];
  $buttonText = $hero['button'] ?: 'অর্ডার করতে চাই';
  $soldOut = $hasOptions ? collect($skus)->sum('stock') < 1 : (int) $product->stock_quantity < 1;
@endphp
<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @include('partials.seo', [
    'rawTitle' => $page->meta_title ?: $page->title . ' | ' . site_name(),
    'metaDescription' => $page->meta_description ?: trim(($hero['part1'] ?? '') . ' ' . ($hero['part2'] ?? '') . ' ' . ($hero['part3'] ?? '')),
    'ogImage' => $gallery[0] ?? null,
    'ogType' => 'product',
  ])
  @if($preview)<meta name="robots" content="noindex, nofollow">@endif
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="{{ \App\Support\StoreStyle::fontsUrl() }}" rel="stylesheet" />
  <style>
    :root { {!! \App\Support\StoreStyle::cssVars() !!} --lp: {{ $accent }}; --lp-on: {{ $onAccent }}; --lp-base: #fff; }
    body { font-family: 'Hind Siliguri', var(--font-body), sans-serif; }
    h1, h2, h3 { font-family: 'Hind Siliguri', var(--font-display), sans-serif; }
    .lp-accent { color: var(--lp); }
    .lp-bg { background: var(--lp); }
    .lp-ring { box-shadow: 0 0 0 2px var(--lp); }
    .lp-btn { background: var(--lp); color: var(--lp-on); transition: filter .15s, transform .15s; }
    .lp-btn:hover { filter: brightness(.95); }
    .lp-btn:active { transform: scale(.98); }
    .lp-ticker { display: flex; width: max-content; animation: lpTicker 30s linear infinite; }
    .lp-ticker:hover { animation-play-state: paused; }
    @keyframes lpTicker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @media (prefers-reduced-motion: reduce) { .lp-ticker { animation: none; } }
    .lp-opt[aria-pressed="true"] { border-color: var(--lp); color: var(--lp); background: color-mix(in srgb, var(--lp) 8%, var(--lp-base)); }
    .lp-opt:disabled { opacity: .4; text-decoration: line-through; cursor: not-allowed; }
    .lp-zone:has(input:checked) { border-color: var(--lp); background: color-mix(in srgb, var(--lp) 7%, var(--lp-base)); }
    details[open] .lp-plus { transform: rotate(45deg); }
    .lp-input { width: 100%; border: 1px solid #e5e7eb; border-radius: 10px; padding: .7rem .85rem; font-size: 15px; background: #fff; }
    .lp-input:focus { outline: none; border-color: var(--lp); box-shadow: 0 0 0 3px color-mix(in srgb, var(--lp) 18%, transparent); }

    /* ---- Templates (LandingPage::TEMPLATES): same markup, restyled ---- */
    /* Bold dark */
    body.lp-t-bold { --lp-base: #0b0b0f; background: #0b0b0f; color: #f4f4f5; }
    .lp-t-bold .bg-white { background-color: #17171d; }
    .lp-t-bold .bg-gray-50 { background-color: #111116; }
    .lp-t-bold .bg-gray-100 { background-color: #22222b; }
    .lp-t-bold .text-gray-900, .lp-t-bold h1, .lp-t-bold h2, .lp-t-bold h3 { color: #fff; }
    .lp-t-bold .text-gray-700, .lp-t-bold .text-gray-600 { color: #c8c8d0; }
    .lp-t-bold .text-gray-500, .lp-t-bold .text-gray-400 { color: #8d8d98; }
    .lp-t-bold .border-gray-100, .lp-t-bold .border-gray-200, .lp-t-bold .border-gray-300, .lp-t-bold .divide-gray-100 > * { border-color: #2a2a33; }
    .lp-t-bold .lp-input { background: #0f0f14; border-color: #2a2a33; color: #fff; }
    .lp-t-bold h1 { font-size: clamp(30px, 6vw, 50px); line-height: 1.15; font-weight: 800; }
    .lp-t-bold [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-bold [data-lp-section="ticker"] span { color: var(--lp-on); }
    .lp-t-bold [data-lp-cta] { box-shadow: 0 10px 30px -10px var(--lp); }
    .lp-t-bold [data-lp-sticky] { box-shadow: 0 -4px 16px rgba(0,0,0,.5); }
    .lp-t-bold th { color: #fff; }
    .lp-t-bold [data-lp-step] { color: #111; }
    .lp-t-bold header img { filter: brightness(0) invert(1); }
    /* Clean minimal */
    .lp-t-minimal .rounded-2xl, .lp-t-minimal .rounded-xl, .lp-t-minimal .rounded-lg, .lp-t-minimal .lp-input { border-radius: 3px; }
    .lp-t-minimal .shadow, .lp-t-minimal .shadow-sm { box-shadow: none; }
    .lp-t-minimal .bg-gray-50 { background-color: #fafaf9; }
    .lp-t-minimal h1 { font-weight: 600; letter-spacing: -.01em; }
    .lp-t-minimal h2 { font-size: 15px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .lp-t-minimal h2::after { content: ''; display: block; width: 32px; height: 2px; margin: 10px auto 0; background: var(--lp); }
    .lp-t-minimal .lp-btn { letter-spacing: .06em; text-transform: uppercase; font-size: 14px; }
    .lp-t-minimal [data-lp-section="ticker"] { background: #111; border: 0; }
    .lp-t-minimal [data-lp-section="ticker"] span { color: #fff; }
    /* Festive */
    body.lp-t-festive { --lp-base: #fff8ec; background: #fff8ec; }
    .lp-t-festive .bg-white { background-color: #fffdf7; }
    .lp-t-festive .bg-gray-50 { background-color: #fdf0d5; }
    .lp-t-festive .border-gray-100, .lp-t-festive .border-gray-200, .lp-t-festive .border-gray-300 { border-color: #ecd49a; }
    .lp-t-festive h1, .lp-t-festive h2, .lp-t-festive h3, .lp-t-festive .text-gray-900 { color: #3b0a0a; }
    .lp-t-festive h2::before, .lp-t-festive h2::after { content: '✦'; color: #c9a227; font-size: .7em; margin: 0 .6em; vertical-align: middle; }
    .lp-t-festive header { background: radial-gradient(ellipse at top, color-mix(in srgb, var(--lp) 14%, transparent), transparent 70%); border-radius: 0 0 2rem 2rem; padding-bottom: 1.25rem; }
    .lp-t-festive [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-festive [data-lp-section="ticker"] span { color: #fde68a; }
    .lp-t-festive .lp-btn { background: linear-gradient(180deg, color-mix(in srgb, var(--lp) 85%, #fff), var(--lp)); box-shadow: 0 0 0 2px #e7c77a inset; }
    /* Split hero: photos beside the headline on computers */
    @media (min-width: 1024px) {
      .lp-t-split .lp-split { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; max-width: 72rem; margin: 0 auto; padding: 2.5rem 1.5rem 0; }
      .lp-t-split .lp-split > header { order: 2; text-align: left; max-width: none; padding: 0; margin: 0; }
      .lp-t-split .lp-split > header > div:first-child { justify-content: flex-start; }
      .lp-t-split .lp-split > header h1 { font-size: 44px; line-height: 1.15; }
      .lp-t-split .lp-split > section { order: 1; max-width: none; padding: 0; margin: 0; }
    }
    .lp-t-split [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-split [data-lp-section="ticker"] span { color: var(--lp-on); }
  </style>
  @include('partials.tracking-head')
  {{ tailwind_css('store', [
    'primary' => $theme['primary'], 'hover' => $theme['primary_hover'], 'soft' => $theme['primary_soft_bg'],
    'border' => $theme['primary_border'], 'dark' => $theme['dark'], 'surface' => $theme['surface'],
  ]) }}
</head>
<body class="lp-t-{{ $template }} bg-white text-gray-900 antialiased {{ $page->sectionOn('sticky_bar') ? 'pb-20 sm:pb-0' : '' }}">
  @include('partials.tracking-body')

  @if($trying ?? false)
    <div class="bg-sky-100 text-sky-900 text-center text-sm font-semibold py-2 px-4" data-lp-trying>
      Trying the “{{ \App\Models\LandingPage::TEMPLATES[$template][0] }}” template. Nothing is saved; pick it in the editor to use it.
    </div>
  @endif
  @if($preview)
    <div class="bg-amber-100 text-amber-900 text-center text-sm font-semibold py-2 px-4" data-lp-preview>
      Draft preview: only staff can see this page. Publish it in Admin → Landing pages.
    </div>
  @endif

  {{-- Top announcement bar --}}
  @if($page->sectionOn('ticker') && $ticker->isNotEmpty())
    <div class="overflow-hidden border-b border-gray-100 bg-gray-50 py-2" data-lp-section="ticker">
      <div class="lp-ticker">
        @for($copy = 0; $copy < 2; $copy++)
          <div class="flex shrink-0 items-center" @if($copy) aria-hidden="true" @endif>
            @for($rep = 0; $rep < 2; $rep++)
              @foreach($ticker as $line)
                <span class="px-5 text-[13px] sm:text-sm font-medium text-gray-700 whitespace-nowrap">{{ $line }}</span>
                <span class="lp-accent text-xs">●</span>
              @endforeach
            @endfor
          </div>
        @endfor
      </div>
    </div>
  @endif

  {{-- Hero (the Split template puts it beside the photos on computers) --}}
  <div class="{{ $split ? 'lp-split' : '' }}">
  <header class="max-w-3xl mx-auto px-4 pt-6 sm:pt-8 text-center">
    <div class="flex justify-center">@include('partials.brand', ['size' => 'md'])</div>
    @if(filled($hero['badge']))
      <span class="mt-5 inline-block rounded-full border px-4 py-1 text-[13px] sm:text-sm font-semibold lp-accent" style="border-color: var(--lp);">{{ $hero['badge'] }}</span>
    @endif
    <h1 class="mt-4 text-[26px] leading-snug sm:text-4xl sm:leading-tight font-bold text-gray-900">
      {{ $hero['part1'] }}
      @if(filled($hero['part2']))<span class="lp-accent">{{ $hero['part2'] }}</span>@endif
      {{ $hero['part3'] }}
    </h1>
  </header>

  {{-- Gallery + price --}}
  <section class="max-w-xl mx-auto px-4 mt-6" data-lp-section="gallery">
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

    <div class="relative mt-5 grid {{ $compare ? 'grid-cols-2' : 'grid-cols-1' }} rounded-2xl border border-gray-200 bg-white text-center">
      @if($discountText)
        <span class="absolute -top-3 right-4 rounded-full bg-rose-600 px-3 py-0.5 text-xs font-bold text-white">{{ $discountText }}</span>
      @endif
      @if($compare)
        <div class="p-4 border-r border-gray-100">
          <p class="text-xs text-gray-500">রেগুলার প্রাইস</p>
          <p class="mt-1 text-xl font-bold text-gray-400 line-through">{{ money($compare) }}</p>
        </div>
      @endif
      <div class="p-4">
        <p class="text-xs text-gray-500">অফার প্রাইস</p>
        <p class="mt-1 text-2xl font-extrabold lp-accent" data-lp-price>{{ money($price) }}</p>
      </div>
    </div>
    <a href="#order" class="lp-btn mt-4 flex h-12 items-center justify-center gap-2 rounded-xl text-base font-bold shadow-sm" data-lp-cta>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
      {{ $buttonText }}
    </a>
    <p class="mt-3 flex flex-wrap justify-center gap-x-4 gap-y-1 text-[13px] text-gray-600">
      <span>✓ ক্যাশ অন ডেলিভারি</span>
      <span>✓ পণ্য দেখে মূল্য পরিশোধ</span>
    </p>
  </section>
  </div>

  {{-- Video --}}
  @if($page->sectionOn('video') && $videoId)
    <section class="mt-12 bg-gray-50 py-10 sm:py-14" data-lp-section="video">
      <div class="max-w-3xl mx-auto px-4">
        @if(filled($d['video']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['video']['heading'] }}</h2>@endif
        <div class="mt-6 aspect-video overflow-hidden rounded-2xl bg-black shadow">
          <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0" title="{{ $d['video']['heading'] ?: $page->title }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
    </section>
  @endif

  {{-- Feature blocks --}}
  @if($page->sectionOn('features') && $features->isNotEmpty())
    <section class="py-10 sm:py-14" style="background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));" data-lp-section="features">
      <div class="max-w-4xl mx-auto px-4">
        @if(filled($d['features']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['features']['heading'] }}</h2>@endif
        <div class="mt-8 space-y-8">
          @foreach($features as $i => $f)
            @php($img = $f['image'] ? image_url($f['image'], $f['title'] ?? '') : ($gallery[$i] ?? null))
            <div class="grid items-center gap-5 sm:grid-cols-2 sm:gap-10">
              @if($img)
                <div class="{{ $i % 2 ? 'sm:order-2' : '' }} aspect-square overflow-hidden rounded-2xl bg-white shadow-sm">
                  <img src="{{ $img }}" alt="{{ $f['title'] }}" class="h-full w-full object-cover" loading="lazy">
                </div>
              @endif
              <div class="{{ $img ? '' : 'sm:col-span-2' }}">
                <h3 class="text-lg sm:text-xl font-bold">{{ $f['title'] }}</h3>
                <p class="mt-2 text-[15px] leading-relaxed text-gray-600">{{ $f['text'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- Specifications --}}
  @if($page->sectionOn('specs') && count($specRows))
    <section class="py-10 sm:py-14" data-lp-section="specs">
      <div class="max-w-2xl mx-auto px-4">
        @if(filled($d['specs']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['specs']['heading'] }}</h2>@endif
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
          <table class="w-full text-[15px]">
            @foreach($specRows as $row)
              <tr class="border-b border-gray-100 last:border-0">
                <th class="w-2/5 bg-gray-50 px-4 py-3 text-left font-semibold align-top">{{ $row['label'] }}</th>
                <td class="px-4 py-3 text-gray-700">{{ $row['value'] }}</td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </section>
  @endif

  {{-- Customer review pictures --}}
  @if($page->sectionOn('reviews') && $reviewImages->isNotEmpty())
    <section class="bg-gray-50 py-10 sm:py-14" data-lp-section="reviews">
      <div class="max-w-5xl mx-auto px-4">
        @if(filled($d['reviews']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['reviews']['heading'] }}</h2>@endif
        @if(filled($d['reviews']['subtitle'] ?? null))<p class="mt-1 text-center text-sm text-gray-500">{{ $d['reviews']['subtitle'] }}</p>@endif
        <div class="mt-6 flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2">
          @foreach($reviewImages as $img)
            <img src="{{ image_url($img, 'review') }}" alt="কাস্টমার রিভিউ" class="h-72 w-auto shrink-0 snap-center rounded-xl border border-gray-200 bg-white object-contain" loading="lazy">
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- FAQ --}}
  @if($page->sectionOn('faq') && $faqs->isNotEmpty())
    <section class="py-10 sm:py-14" style="background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));" data-lp-section="faq">
      <div class="max-w-2xl mx-auto px-4">
        @if(filled($d['faq']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['faq']['heading'] }}</h2>@endif
        @if(filled($d['faq']['subtitle'] ?? null))<p class="mt-1 text-center text-sm text-gray-500">{{ $d['faq']['subtitle'] }}</p>@endif
        <div class="mt-6 space-y-3">
          @foreach($faqs as $f)
            <details class="group rounded-xl border border-gray-200 bg-white px-4 py-3">
              <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-semibold">
                <span>{{ $f['q'] }}</span>
                <span class="lp-plus lp-accent text-xl leading-none transition-transform">+</span>
              </summary>
              <p class="mt-2 text-[15px] leading-relaxed text-gray-600">{{ $f['a'] }}</p>
            </details>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- Call / WhatsApp --}}
  @if($page->sectionOn('contact') && (filled($contact['phone'] ?? null) || $wa !== '' || $messengerUrl))
    <section class="py-10" data-lp-section="contact">
      <div class="max-w-xl mx-auto px-4">
        <div class="rounded-2xl border border-gray-200 p-5 text-center">
          @if(filled($contact['heading'] ?? null))<h2 class="text-lg sm:text-xl font-bold">{{ $contact['heading'] }}</h2>@endif
          <div class="mt-4 flex flex-wrap justify-center gap-3">
            @if(filled($contact['phone'] ?? null))
              <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-gray-300 px-4 font-semibold">📞 {{ $contact['phone'] }}</a>
            @endif
            @if($wa !== '')
              <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 font-semibold text-emerald-700">WhatsApp</a>
            @endif
            @if($messengerUrl)
              <a href="{{ $messengerUrl }}" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-xl border border-blue-300 bg-blue-50 px-4 font-semibold text-blue-700">Messenger</a>
            @endif
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- Order form --}}
  <section id="order" class="scroll-mt-4 bg-gray-50 py-10 sm:py-14" data-lp-section="form">
    <div class="max-w-5xl mx-auto px-4">
      @if(filled($form['heading']))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $form['heading'] }}</h2>@endif
      @if(filled($form['subheading']))<p class="mt-1 text-center text-sm text-gray-500">{{ $form['subheading'] }}</p>@endif

      @if($errors->any())
        <div class="mx-auto mt-5 max-w-xl rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert" data-lp-errors>
          @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('landing.order', $page->slug) }}" id="lpForm" class="mt-6 grid gap-5 lg:grid-cols-5" novalidate>
        @csrf
        <input type="hidden" name="sku_id" id="lpSku" value="{{ old('sku_id') }}">
        <input type="hidden" name="qty" id="lpQty" value="{{ $oldQty }}">

        <div class="space-y-5 lg:col-span-3">
          <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="font-bold">{{ $form['delivery_heading'] }}</h3>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <label class="block">
                <span class="text-sm font-medium">আপনার নাম <span class="text-rose-600">*</span></span>
                <input name="customer_name" value="{{ old('customer_name') }}" class="lp-input mt-1" required maxlength="120" autocomplete="name">
              </label>
              <label class="block">
                <span class="text-sm font-medium">মোবাইল নম্বর <span class="text-rose-600">*</span></span>
                <input name="customer_phone" value="{{ old('customer_phone') }}" class="lp-input mt-1" required inputmode="tel" placeholder="01XXXXXXXXX" maxlength="16" autocomplete="tel">
              </label>
              <label class="block sm:col-span-2">
                <span class="text-sm font-medium">সম্পূর্ণ ঠিকানা <span class="text-rose-600">*</span></span>
                <textarea name="shipping_address" rows="2" class="lp-input mt-1" required maxlength="255" placeholder="বাসা, রোড, এলাকা, থানা, জেলা" autocomplete="street-address">{{ old('shipping_address') }}</textarea>
              </label>
            </div>
            <p class="mt-4 text-sm font-medium">ডেলিভারি এলাকা <span class="text-rose-600">*</span></p>
            <div class="mt-2 grid grid-cols-2 gap-3">
              <label class="lp-zone flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 p-3 text-sm">
                <input type="radio" name="shipping_zone" value="inside_dhaka" @checked($oldZone === 'inside_dhaka') required>
                <span>ঢাকার ভিতরে <b class="lp-accent">{{ $shipInside > 0 ? money($shipInside) : 'ফ্রি' }}</b></span>
              </label>
              <label class="lp-zone flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 p-3 text-sm">
                <input type="radio" name="shipping_zone" value="outside_dhaka" @checked($oldZone === 'outside_dhaka')>
                <span>ঢাকার বাইরে <b class="lp-accent">{{ $shipOutside > 0 ? money($shipOutside) : 'ফ্রি' }}</b></span>
              </label>
            </div>
            <label class="mt-4 block">
              <span class="text-sm font-medium">অর্ডার নোট (ঐচ্ছিক)</span>
              <textarea name="delivery_note" rows="2" class="lp-input mt-1" maxlength="500">{{ old('delivery_note') }}</textarea>
            </label>
          </div>

          <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="font-bold">পেমেন্ট</h3>
            <div class="mt-3 flex items-center gap-3 rounded-xl border p-3 text-sm" style="border-color: var(--lp); background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));">
              <span class="lp-bg h-4 w-4 shrink-0 rounded-full ring-4 ring-white" style="box-shadow: 0 0 0 1px var(--lp);"></span>
              <span><b>ক্যাশ অন ডেলিভারি</b><br><span class="text-gray-500">পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন</span></span>
            </div>
          </div>
        </div>

        <div class="lg:col-span-2">
          <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:sticky lg:top-4">
            <h3 class="font-bold">{{ $form['summary_heading'] }}</h3>
            <div class="mt-4 flex gap-3">
              @if(count($gallery))<img src="{{ $gallery[0] }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-gray-100 object-cover">@endif
              <div class="min-w-0 flex-1">
                <p class="font-semibold leading-snug">{{ $product->name }}</p>
                <p class="text-sm lp-accent font-bold mt-0.5">{{ money($price) }}</p>
              </div>
            </div>

            @if($hasOptions)
              <div class="mt-4 space-y-3" id="lpOptions">
                @foreach($groups as $name => $values)
                  <div>
                    <p class="text-sm font-medium">{{ $name }} <span class="text-rose-600">*</span></p>
                    <div class="mt-1.5 flex flex-wrap gap-2">
                      @foreach($values as $value)
                        <button type="button" class="lp-opt h-9 min-w-[2.75rem] rounded-lg border border-gray-300 px-3 text-sm font-semibold" data-lp-group="{{ $name }}" data-lp-value="{{ $value }}" aria-pressed="false">{{ $value }}</button>
                      @endforeach
                    </div>
                  </div>
                @endforeach
                <p class="hidden text-xs text-rose-600" id="lpOptionNote">এই সাইজ / কালারটি স্টকে নেই।</p>
              </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
              <span class="text-sm font-medium">পরিমাণ</span>
              <div class="flex items-center rounded-lg border border-gray-300">
                <button type="button" class="h-9 w-9 text-lg" data-lp-qty="-1" aria-label="কমান">−</button>
                <span class="w-8 text-center font-semibold" id="lpQtyText">{{ $oldQty }}</span>
                <button type="button" class="h-9 w-9 text-lg" data-lp-qty="1" aria-label="বাড়ান">+</button>
              </div>
            </div>

            <dl class="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm">
              <div class="flex justify-between"><dt class="text-gray-600">{{ $form['subtotal_label'] }}</dt><dd class="font-semibold" id="lpSubtotal">{{ money($quotes[$oldQty]['inside_dhaka']['subtotal']) }}</dd></div>
              <div class="flex justify-between"><dt class="text-gray-600">{{ $form['delivery_label'] }}</dt><dd class="font-semibold" id="lpShipping">{{ $oldZone ? money($quotes[$oldQty][$oldZone]['shipping']) : 'এলাকা বেছে নিন' }}</dd></div>
              <div class="flex justify-between {{ ($quotes[1]['inside_dhaka']['tax'] ?? 0) > 0 ? '' : 'hidden' }}"><dt class="text-gray-600">ট্যাক্স</dt><dd class="font-semibold" id="lpTax">{{ money($quotes[$oldQty][$oldZone ?: 'inside_dhaka']['tax']) }}</dd></div>
              <div class="flex justify-between border-t border-gray-100 pt-3 text-base"><dt class="font-bold">{{ $form['total_label'] }}</dt><dd class="font-extrabold" id="lpTotal">{{ money($oldZone ? $quotes[$oldQty][$oldZone]['total'] : $quotes[$oldQty]['inside_dhaka']['subtotal']) }}</dd></div>
            </dl>

            @if($soldOut)
              <p class="mt-5 rounded-xl bg-gray-100 py-3 text-center font-bold text-gray-600" data-lp-soldout>দুঃখিত, পণ্যটি এখন স্টকে নেই</p>
            @else
              <button type="submit" class="lp-btn mt-5 flex h-12 w-full items-center justify-center rounded-xl text-base font-bold" id="lpSubmit">
                {{ $form['button'] }}
              </button>
            @endif
          </div>
        </div>
      </form>
    </div>
  </section>

  <footer class="py-6 text-center text-xs text-gray-500">
    © {{ date('Y') }} {{ site_name() }} · <a href="{{ route('home') }}" class="underline">ওয়েবসাইট দেখুন</a>
  </footer>

  {{-- Phone order bar --}}
  @if($page->sectionOn('sticky_bar'))
    <div class="fixed inset-x-0 bottom-0 z-40 flex items-center gap-3 border-t border-gray-200 bg-white px-4 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] sm:hidden" data-lp-sticky style="padding-bottom: max(.75rem, env(safe-area-inset-bottom));">
      <div class="min-w-0">
        <p class="text-[11px] text-gray-500">মোট মূল্য</p>
        <p class="text-lg font-extrabold leading-tight" id="lpStickyTotal">{{ money($price) }}</p>
      </div>
      <a href="#order" class="lp-btn ml-auto flex h-11 flex-1 items-center justify-center rounded-xl text-[15px] font-bold">{{ $buttonText }}</a>
    </div>
  @endif

  <script>
  (function () {
    var quotes = @json($quotes);
    var skus = @json($skus);
    var groups = @json(array_keys($groups));
    var maxQty = {{ (int) $maxQty }};
    var productStock = {{ max(0, (int) $product->stock_quantity) }};
    var symbol = @json(currency_symbol());
    var form = document.getElementById('lpForm');
    var fmt = function (n) { return symbol + Math.round(n).toLocaleString('en-US'); };

    // Gallery
    var thumbs = document.querySelectorAll('[data-lp-thumb]');
    var main = document.getElementById('lpMain');
    var current = 0;
    function show(i) {
      if (!thumbs.length) return;
      current = (i + thumbs.length) % thumbs.length;
      main.src = thumbs[current].querySelector('img').src;
      thumbs.forEach(function (t, k) {
        t.style.borderColor = k === current ? 'var(--lp)' : 'transparent';
      });
    }
    thumbs.forEach(function (t) { t.addEventListener('click', function () { show(+t.dataset.lpThumb); }); });
    document.querySelectorAll('[data-lp-step]').forEach(function (b) {
      b.addEventListener('click', function () { show(current + (+b.dataset.lpStep)); });
    });

    // Options (size / colour) → SKU
    var chosen = {};
    var skuInput = document.getElementById('lpSku');
    var note = document.getElementById('lpOptionNote');
    var qtyInput = document.getElementById('lpQty');
    var qty = +qtyInput.value || 1;
    function matchSku() {
      if (!groups.length) return null;
      for (var i = 0; i < skus.length; i++) {
        var ok = groups.every(function (g) { return chosen[g] !== undefined && skus[i].attrs[g] === chosen[g]; });
        if (ok) return skus[i];
      }
      return null;
    }
    function stockLimit() {
      var s = matchSku();
      if (!groups.length) return Math.min(maxQty, productStock);
      return s ? Math.min(maxQty, s.stock) : maxQty;
    }
    function refreshOptions() {
      document.querySelectorAll('[data-lp-group]').forEach(function (btn) {
        var g = btn.dataset.lpGroup, v = btn.dataset.lpValue;
        btn.setAttribute('aria-pressed', chosen[g] === v ? 'true' : 'false');
        // Out of stock with the other choices made so far
        var available = skus.some(function (s) {
          if (s.attrs[g] !== v || s.stock < 1) return false;
          return groups.every(function (o) { return o === g || chosen[o] === undefined || s.attrs[o] === chosen[o]; });
        });
        btn.disabled = !available;
      });
      var s = matchSku();
      skuInput.value = s ? s.id : '';
      if (note) note.classList.toggle('hidden', !(groups.every(function (g) { return chosen[g] !== undefined; }) && (!s || s.stock < 1)));
      if (qty > stockLimit()) setQty(Math.max(1, stockLimit()));
    }
    document.querySelectorAll('[data-lp-group]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var g = btn.dataset.lpGroup;
        chosen[g] = chosen[g] === btn.dataset.lpValue ? undefined : btn.dataset.lpValue;
        refreshOptions();
        started();
      });
    });
    // Restore the old choice after a validation error
    if (skuInput.value) {
      var old = skus.find(function (s) { return String(s.id) === String(skuInput.value); });
      if (old) groups.forEach(function (g) { chosen[g] = old.attrs[g]; });
    }

    // Quantity and totals
    function zone() {
      var z = form.querySelector('input[name="shipping_zone"]:checked');
      return z ? z.value : '';
    }
    function totals() {
      var z = zone(), q = quotes[qty][z || 'inside_dhaka'];
      document.getElementById('lpSubtotal').textContent = fmt(q.subtotal);
      document.getElementById('lpShipping').textContent = z ? (q.shipping > 0 ? fmt(q.shipping) : 'ফ্রি') : 'এলাকা বেছে নিন';
      document.getElementById('lpTax').textContent = fmt(q.tax);
      var total = z ? q.total : q.subtotal + q.tax;
      document.getElementById('lpTotal').textContent = fmt(total);
      var btn = document.getElementById('lpSubmit');
      if (btn) btn.textContent = @json($form['button']) + ' · ' + fmt(total);
      var sticky = document.getElementById('lpStickyTotal');
      if (sticky) sticky.textContent = fmt(total);
    }
    function setQty(n) {
      qty = Math.max(1, Math.min(n, stockLimit() || 1));
      qtyInput.value = qty;
      document.getElementById('lpQtyText').textContent = qty;
      totals();
    }
    document.querySelectorAll('[data-lp-qty]').forEach(function (b) {
      b.addEventListener('click', function () { setQty(qty + (+b.dataset.lpQty)); });
    });
    form.querySelectorAll('input[name="shipping_zone"]').forEach(function (r) { r.addEventListener('change', totals); });

    // Tracking: product view now, checkout started on the first touch of the form
    var item = { item_id: @json((string) $product->id), item_name: @json($product->name), price: {{ $price }}, quantity: 1 };
    window.vtTrack && window.vtTrack('view_item', { items: [item] });
    var didStart = false;
    function started() {
      if (didStart) return;
      didStart = true;
      window.vtTrack && window.vtTrack('begin_checkout', { items: [Object.assign({}, item, { quantity: qty })], value: {{ $price }} * qty });
    }
    form.addEventListener('focusin', started);

    form.addEventListener('submit', function (e) {
      if (groups.length && !skuInput.value) {
        e.preventDefault();
        if (note) { note.textContent = 'সাইজ / কালার বেছে নিন।'; note.classList.remove('hidden'); }
        document.getElementById('lpOptions').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      var btn = document.getElementById('lpSubmit');
      if (!btn) { e.preventDefault(); return; }
      btn.disabled = true;
    });

    // The phone order bar steps aside while the order form is on screen
    var stickyBar = document.querySelector('[data-lp-sticky]');
    if (stickyBar && 'IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        stickyBar.style.display = entries[0].isIntersecting ? 'none' : '';
      }).observe(form);
    }

    refreshOptions();
    totals();
    @if($errors->any())
      document.getElementById('order').scrollIntoView();
    @endif
  })();
  </script>
</body>
</html>
