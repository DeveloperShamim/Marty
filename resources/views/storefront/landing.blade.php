@php
  /** @var \App\Models\LandingPage $page */
  $theme = generate_3_color_matching_theme();
  $accent = $page->accent();
  $template = $page->template();
  $customBlocks = collect($d['custom'] ?? [])->filter(fn ($b) => is_array($b) && trim($b['html'] ?? '') !== '')->all();
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
    .lp-t-bold [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-bold [data-lp-section="ticker"] span { color: var(--lp-on); }
    .lp-t-bold [data-lp-cta] { box-shadow: 0 10px 30px -10px var(--lp); }
    .lp-t-bold [data-lp-sticky] { box-shadow: 0 -4px 16px rgba(0,0,0,.5); }
    .lp-t-bold th { color: #fff; }
    .lp-t-bold [data-lp-step] { color: #111; }
    .lp-logo-light img { filter: brightness(0) invert(1); }
    /* Clean minimal */
    .lp-t-minimal .rounded-2xl, .lp-t-minimal .rounded-xl, .lp-t-minimal .rounded-lg, .lp-t-minimal .lp-input { border-radius: 3px; }
    .lp-t-minimal .shadow, .lp-t-minimal .shadow-sm { box-shadow: none; }
    .lp-t-minimal .bg-gray-50 { background-color: #fafaf9; }
    .lp-t-minimal h1 { font-weight: 600; letter-spacing: -.01em; }
    .lp-t-minimal h2 { font-size: 15px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .lp-t-minimal h2::after { content: ''; display: block; width: 32px; height: 2px; margin: 10px auto 0; background: var(--lp); }
    .lp-t-minimal h2.text-left::after { margin-left: 0; }
    .lp-t-minimal .lp-btn { letter-spacing: .06em; text-transform: uppercase; font-size: 14px; }
    .lp-t-minimal [data-lp-section="ticker"] { background: #111; border: 0; }
    .lp-t-minimal [data-lp-section="ticker"] span { color: #fff; }
    /* Festive */
    body.lp-t-festive { --lp-base: #fff8ec; background: #fff8ec; }
    .lp-t-festive .bg-white { background-color: #fffdf7; }
    .lp-t-festive .bg-gray-50 { background-color: #fdf0d5; }
    .lp-t-festive .border-gray-100, .lp-t-festive .border-gray-200, .lp-t-festive .border-gray-300 { border-color: #ecd49a; }
    .lp-t-festive h2, .lp-t-festive h3, .lp-t-festive .text-gray-900 { color: #3b0a0a; }
    .lp-t-festive h2::before, .lp-t-festive h2::after { content: '✦'; color: #c9a227; font-size: .7em; margin: 0 .6em; vertical-align: middle; }
    .lp-t-festive [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-festive [data-lp-section="ticker"] span { color: #fde68a; }
    .lp-t-festive .lp-btn { background: linear-gradient(180deg, color-mix(in srgb, var(--lp) 85%, #fff), var(--lp)); box-shadow: 0 0 0 2px #e7c77a inset; }
    .lp-t-split [data-lp-section="ticker"] { background: var(--lp); border: 0; }
    .lp-t-split [data-lp-section="ticker"] span { color: var(--lp-on); }
    .lp-quick-form { border-color: var(--lp); background: color-mix(in srgb, var(--lp) 4%, #fff); }
    /* Bold: the festive banner rule must not reach other templates' headers */
    .lp-t-bold [data-lp-cta] { box-shadow: 0 12px 34px -12px var(--lp); }
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

  @include('storefront.landing.parts.ticker')
  @include('storefront.landing.parts.custom', ['at' => 'top'])

  {{-- The picked template lays out the sections (resources/views/storefront/landing/{template}.blade.php) --}}
  @include('storefront.landing.' . $template)


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
  // Custom HTML frames report their height; only messages from those frames are used
  window.addEventListener('message', function (e) {
    var d = e.data;
    if (!d || typeof d.lpCustomHeight !== 'number') return;
    document.querySelectorAll('iframe[data-lp-custom]').forEach(function (f) {
      if (f.contentWindow === e.source) f.style.height = Math.min(6000, Math.max(20, Math.ceil(d.lpCustomHeight))) + 'px';
    });
  });
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
