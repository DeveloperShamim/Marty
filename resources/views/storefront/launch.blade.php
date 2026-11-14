@extends('layouts.storefront')

{{-- Launch mode homepage (Admin → Homepage → Launch mode, App\Support\LaunchMode). The normal homepage is
     storefront/home.blade.php and is left untouched; switching launch mode off shows it again. --}}

@section('content')
@php
  $L = $launch;
  $count = $products->count();
  $lead = $products->first();
  $num = fn ($i) => str_pad($i + 1, 2, '0', STR_PAD_LEFT);
  $colorsOf = fn ($p) => \App\Support\ProductCardOptions::for($p)['colors'] ?? [];

  // Hero text comes from Admin → Homepage → Launch mode; the photo is the first collection product
  $H = $L['hero'] ?? [];
  $heroTitle = ($H['title'] ?? '') ?: ($lead?->name ?? site_name());
  $heroText = $H['text'] ?? '';
  $heroBadge = $H['badge'] ?? '';
  $heroBtn = ($H['button'] ?? '') ?: 'Shop now';
  // Rotating card: with 2+ products the photo becomes one product card that cycles through the collection
  // (Admin → Homepage → Launch mode → Rotating product card). It replaces the hero picture and colour dots.
  $rotate = ($H['rotate'] ?? true) && $count >= 2;
  // The hero picture uploaded in Launch mode, else the first product's photo
  $heroImg = ! empty($H['image']) ? image_url($H['image']) : $lead?->imageUrl();
  // Colour dots swap the photo: the colour's picture from Launch mode, else a product photo tagged with that
  // colour (Admin → Products → photo colour), else the main hero picture
  $heroColors = $lead && ! $rotate ? collect($colorsOf($lead))->take(5)->map(function ($c) use ($lead, $H) {
    $key = mb_strtolower(trim($c['name']));
    if (! empty($H['color_images'][$key])) {
      return $c + ['image' => image_url($H['color_images'][$key])];
    }
    $img = $lead->images->first(fn ($i) => mb_strtolower(trim((string) $i->color)) === $key);
    return $c + ['image' => $img ? image_url($img->path, $lead->slug) : null];
  })->values() : collect();

  $gridClass = match (true) {
    $count <= 1 => 'lx-grid-1',
    $count === 2 => 'lx-grid-2',
    $count === 3 => 'lx-grid-3',
    default => 'lx-grid-4',
  };
  $pillars = collect($L['material']['pillars'] ?? [])->take(3)->values();
  $rows = collect($L['compare']['rows'] ?? []);
  $trust = collect($L['trust']['items'] ?? [])->take(3)->values();
  $icons = [
    'cash' => '<path d="M17 9V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2m2 4h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm7-5a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>',
    'bolt' => '<path d="M13 10V3L4 14h7v7l9-11h-7z"/>',
    'shield' => '<path d="M9 12l2 2 4-4m5.62-4.02A11.96 11.96 0 0 1 12 2.94a11.96 11.96 0 0 1-8.62 3.04A12.02 12.02 0 0 0 3 9c0 5.59 3.82 10.29 9 11.62 5.18-1.33 9-6.03 9-11.62 0-1.04-.13-2.05-.38-3.02z"/>',
    'truck' => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2m10 0H9m10 0h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
    'gift' => '<path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7m0 0H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
    'star' => '<path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38a.56.56 0 0 1-.84.61L12 16.73l-4.73 2.9a.56.56 0 0 1-.84-.61l1.28-5.38-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44 2.13-5.11z"/>',
  ];
  $dataAttrs = function ($p, bool $orderNow) {
    $out = 'data-order-now="' . ($orderNow ? 'true' : 'false') . '"';
    foreach (\App\Support\LaunchMode::cartData($p) as $k => $v) {
      $out .= ' data-' . $k . '="' . e($v) . '"';
    }
    return $out;
  };
@endphp

<div class="lx-page">

  {{-- 1. HERO: text on the left, the product photo filling a framed card on the right (on phones: photo first) --}}
  <section class="lx-hero">
    <div class="lx-wrap lx-hero-grid">
    <div class="lx-hero-copy">
      <div class="lx-hero-inner">
        @if($heroBadge)<p class="lx-eyebrow">{{ $heroBadge }}</p>@endif
        <h1 class="lx-h1">{{ $heroTitle }}</h1>
        @if($heroText)<p class="lx-lead">{{ $heroText }}</p>@endif

        @if($heroColors->isNotEmpty())
          <div class="lx-finish" data-lx-swatches>
            @foreach($heroColors as $ci => $col)
              <button type="button" class="lx-swatch {{ $ci === 0 ? 'is-on' : '' }}" data-name="{{ $col['name'] }}" data-img="{{ $col['image'] }}" title="{{ $col['name'] }}" aria-label="{{ $col['name'] }}" aria-pressed="{{ $ci === 0 ? 'true' : 'false' }}"><span @if($col['swatch']) style="background: {{ $col['swatch'] }}" @endif></span></button>
            @endforeach
            <span class="lx-swatch-name" data-lx-swatch-name>{{ $heroColors[0]['name'] }}</span>
          </div>
        @endif

        <div class="lx-hero-cta">
          @if($rotate)
            {{-- Each product in the card has its own Buy now, so this one takes the customer to the whole batch --}}
            <a href="#collection" class="lx-btn lx-btn-main">{{ $heroBtn }}</a>
          @elseif($lead && ! $lead->isOutOfStock())
            <button type="button" class="add-to-cart lx-btn lx-btn-main" {!! $dataAttrs($lead, true) !!}>{{ $heroBtn }}</button>
          @elseif($lead)
            <a href="{{ route('product.show', $lead) }}" class="lx-btn lx-btn-main">{{ $heroBtn }}</a>
          @endif
          @if($count > 0 && ! $rotate)
            <a href="#collection" class="lx-btn lx-btn-ghost lx-hide-phone">View collection</a>
          @endif
        </div>

        @if($rotate)
          <p class="lx-meta">
            <span>{{ $count }} {{ $count === 1 ? 'product' : 'products' }} in this batch</span>
            <span aria-hidden="true">·</span><span>Cash on delivery</span>
          </p>
        @elseif($lead)
          <p class="lx-meta">
            <span>{{ money($lead->price) }}</span>
            <span aria-hidden="true">·</span><span>Cash on delivery</span>
            @unless($lead->isOutOfStock())<span aria-hidden="true">·</span><span>In stock</span>@endunless
          </p>
        @endif
      </div>
    </div>
    <div class="lx-hero-media">
      @if($rotate)
        <div class="lx-rot" data-lx-rot role="region" aria-roledescription="carousel" aria-label="Products in this batch">
          <div class="lx-rot-track" data-lx-rot-track aria-live="off">
            @foreach($products as $i => $p)
              @php $out = $p->isOutOfStock(); @endphp
              <article class="lx-rot-slide" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $count }}: {{ $p->name }}" @if($i > 0) aria-hidden="true" @endif>
                <a href="{{ route('product.show', $p) }}" class="lx-rot-img" tabindex="{{ $i === 0 ? '0' : '-1' }}" aria-label="{{ $p->name }}">
                  <img src="{{ $p->imageUrl() }}" alt="{{ $p->name }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" draggable="false">
                  @if($out)
                    <span class="lx-tag lx-rot-tag">Sold out</span>
                  @elseif($p->discount_percent > 0)
                    <span class="lx-tag lx-tag-accent lx-rot-tag">-{{ $p->discount_percent }}%</span>
                  @endif
                </a>
                <div class="lx-rot-body">
                  <div class="lx-rot-info">
                    <a href="{{ route('product.show', $p) }}" class="lx-rot-name" tabindex="{{ $i === 0 ? '0' : '-1' }}">{{ $p->name }}</a>
                    <span class="lx-price lx-rot-price">{{ money($p->price) }}@if($p->on_sale)<s>{{ money($p->regular_price) }}</s>@endif</span>
                  </div>
                  <div class="lx-rot-btns">
                    <a href="{{ route('product.show', $p) }}" class="lx-btn lx-btn-ghost" tabindex="{{ $i === 0 ? '0' : '-1' }}">View product</a>
                    @if($out)
                      <button type="button" class="lx-btn lx-btn-off" disabled>Sold out</button>
                    @else
                      <button type="button" class="add-to-cart lx-btn lx-btn-main" tabindex="{{ $i === 0 ? '0' : '-1' }}" {!! $dataAttrs($p, true) !!}>Buy now</button>
                    @endif
                  </div>
                </div>
              </article>
            @endforeach
          </div>
          {{-- Arrows and dots sit over the square photo --}}
          <div class="lx-rot-over">
            <button type="button" class="lx-rot-arrow lx-rot-prev" data-lx-rot-prev aria-label="Previous product"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
            <button type="button" class="lx-rot-arrow lx-rot-next" data-lx-rot-next aria-label="Next product"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
            <div class="lx-rot-dots">
              @foreach($products as $i => $p)
                <button type="button" class="lx-rot-dot {{ $i === 0 ? 'is-on' : '' }}" data-lx-rot-dot="{{ $i }}" aria-label="Show {{ $p->name }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
              @endforeach
            </div>
          </div>
        </div>
      @elseif($lead)
        <a href="{{ route('product.show', $lead) }}" class="lx-hero-card" aria-label="{{ $lead->name }}">
          <img src="{{ $heroImg }}" alt="{{ $lead->name }}" loading="eager" data-lx-hero-img data-default="{{ $heroImg }}">
        </a>
      @endif
    </div>
    </div>
  </section>

  {{-- 2. THE COLLECTION --}}
  <section id="collection" class="lx-section">
    <div class="lx-wrap">
      <div class="lx-head">
        <div>
          @if($L['collection']['label'])<p class="lx-eyebrow">{{ $L['collection']['label'] }}</p>@endif
          @if($L['collection']['heading'])<h2 class="lx-h2">{{ $L['collection']['heading'] }}</h2>@endif
        </div>
        @if($L['collection']['intro'])<p class="lx-intro">{{ $L['collection']['intro'] }}</p>@endif
      </div>

      @if($count === 0)
        <div class="lx-empty">The first batch is on its way. Check back soon.</div>
      @else
        <div class="lx-grid {{ $gridClass }}">
          @foreach($products as $i => $p)
            @php
              $colors = $colorsOf($p);
              $out = $p->isOutOfStock();
              $summary = $p->short_description ? \Illuminate\Support\Str::limit(strip_tags($p->short_description), 110) : null;
            @endphp
            <article class="lx-card">
              <div class="lx-card-top">
                <span class="lx-num">{{ $num($i) }}.</span>
                @if($out)
                  <span class="lx-tag">Sold out</span>
                @elseif($p->discount_percent > 0)
                  <span class="lx-tag lx-tag-accent">-{{ $p->discount_percent }}%</span>
                @endif
              </div>
              <a href="{{ route('product.show', $p) }}" class="lx-card-img">
                <img src="{{ $p->imageUrl() }}" alt="{{ $p->name }}" loading="{{ $i < 4 ? 'eager' : 'lazy' }}">
              </a>
              <div class="lx-card-body">
                <a href="{{ route('product.show', $p) }}" class="lx-card-name">{{ $p->name }}</a>
                @if($summary)<p class="lx-card-sum">{{ $summary }}</p>@endif
                @if($colors)
                  <div class="lx-card-colors">
                    <span class="lx-dots">
                      @foreach(array_slice($colors, 0, 4) as $col)
                        <span class="lx-dot lx-dot-sm" title="{{ $col['name'] }}" @if($col['swatch']) style="background: {{ $col['swatch'] }}" @endif></span>
                      @endforeach
                    </span>
                    <span class="lx-small lx-dim">{{ count($colors) }} {{ count($colors) === 1 ? 'colour' : 'colours' }}</span>
                  </div>
                @endif
              </div>
              <div class="lx-card-foot">
                <span class="lx-price">
                  {{ money($p->price) }}
                  @if($p->on_sale)<s>{{ money($p->regular_price) }}</s>@endif
                </span>
                @if($out)
                  <button type="button" class="lx-btn lx-btn-sm lx-btn-off" disabled>Sold out</button>
                @else
                  <span class="lx-card-btns">
                    <button type="button" class="add-to-cart lx-btn lx-btn-sm lx-btn-ghost" {!! $dataAttrs($p, false) !!} aria-label="Add {{ $p->name }} to bag">+ Bag</button>
                    <button type="button" class="add-to-cart lx-btn lx-btn-sm lx-btn-main" {!! $dataAttrs($p, true) !!}>Order now</button>
                  </span>
                @endif
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  {{-- 3. MATERIAL ARCHITECTURE --}}
  @if(($L['material']['on'] ?? true) && $pillars->isNotEmpty())
    <section id="craftsmanship" class="lx-section lx-alt">
      <div class="lx-wrap lx-split">
        @php
          $matMain = ! empty($L['material']['image']) ? image_url($L['material']['image']) : null;
          $pillarImgs = $pillars->map(fn ($pl) => ! empty($pl['image']) ? image_url($pl['image']) : null);
          $matFirst = $pillarImgs->first() ?: $matMain ?: $pillarImgs->filter()->first();
        @endphp
        @if($matFirst)
          {{-- Admin pictures: the main one, or the tapped point's own picture --}}
          <div class="lx-macro lx-macro-photo">
            <img src="{{ $matFirst }}" alt="{{ $L['material']['heading'] }}" loading="lazy" data-lx-mat-img data-default="{{ $matMain ?: $matFirst }}">
          </div>
        @else
          <div class="lx-macro" data-lx-macro>
          {{-- Leather corner illustration with numbered pins; the pins and the points highlight each other --}}
          <svg viewBox="0 0 400 400" aria-hidden="true">
            <defs>
              <linearGradient id="lxLeather" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#29201B"/><stop offset=".5" stop-color="#1C1613"/><stop offset="1" stop-color="#110E0C"/></linearGradient>
              <linearGradient id="lxEdge" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#D4A373"/><stop offset=".5" stop-color="#8C5828"/><stop offset="1" stop-color="#D4A373"/></linearGradient>
              <pattern id="lxGrain" width="12" height="12" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r=".7" fill="rgba(212,163,115,.12)"/><circle cx="8" cy="7" r=".6" fill="rgba(255,255,255,.05)"/></pattern>
            </defs>
            <rect x="35" y="35" width="330" height="330" rx="36" fill="url(#lxLeather)" stroke="url(#lxEdge)" stroke-width="5"/>
            <rect x="35" y="35" width="330" height="330" rx="36" fill="url(#lxGrain)"/>
            <path d="M38 155Q200 185 362 155V329a34 34 0 0 1-34 34H72a34 34 0 0 1-34-34Z" fill="#161210" stroke="rgba(212,163,115,.35)" stroke-width="2"/>
            <path d="M38 235Q200 265 362 235V329a34 34 0 0 1-34 34H72a34 34 0 0 1-34-34Z" fill="#1F1916" stroke="rgba(212,163,115,.45)" stroke-width="2"/>
            <rect x="52" y="52" width="296" height="296" rx="24" fill="none" stroke="#D4A373" stroke-width="2.5" stroke-dasharray="9 6" stroke-linecap="round"/>
            <text x="200" y="315" text-anchor="middle" fill="rgba(212,163,115,.35)" font-family="Inter, sans-serif" font-weight="600" font-size="14" letter-spacing="6">{{ mb_strtoupper(\Illuminate\Support\Str::limit(\Illuminate\Support\Str::before(site_name() . ' ', ' '), 8, '')) }} • FULL GRAIN</text>
          </svg>
          @foreach($pillars as $pi => $pl)
            <button type="button" class="lx-pin lx-pin-{{ $pi }} {{ $pi === 0 ? 'is-on' : '' }}" data-lx-pin="{{ $pi }}" aria-label="{{ $pl['title'] }}">{{ $pi + 1 }}</button>
          @endforeach
        </div>
        @endif
        <div>
          @if($L['material']['label'])<p class="lx-eyebrow">{{ $L['material']['label'] }}</p>@endif
          @if($L['material']['heading'])<h2 class="lx-h2 lx-h2-lg">{{ $L['material']['heading'] }}</h2>@endif
          <div class="lx-pillars">
            @foreach($pillars as $pi => $pl)
              <button type="button" class="lx-pillar {{ $pi === 0 ? 'is-on' : '' }}" data-lx-pin="{{ $pi }}" data-img="{{ $pillarImgs[$pi] ?? '' }}">
                <span class="lx-pillar-n">{{ $num($pi) }}</span>
                <span>
                  <span class="lx-pillar-t">{{ $pl['title'] }}</span>
                  @if($pl['text'])<span class="lx-pillar-d">{{ $pl['text'] }}</span>@endif
                </span>
              </button>
            @endforeach
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- 4. FIND YOUR CARRY PROFILE: the same products side by side --}}
  @if(($L['compare']['on'] ?? true) && $count >= 2)
    <section id="compare" class="lx-section">
      <div class="lx-wrap">
        <div class="lx-head">
          <div>
            @if($L['compare']['label'])<p class="lx-eyebrow">{{ $L['compare']['label'] }}</p>@endif
            @if($L['compare']['heading'])<h2 class="lx-h2">{{ $L['compare']['heading'] }}</h2>@endif
          </div>
          <p class="lx-intro lx-phone-only">Swipe the table sideways to see every wallet.</p>
        </div>
        <div class="lx-table-wrap">
          <table class="lx-table" style="--lx-cols: {{ $count }}">
            <thead>
              <tr>
                <th class="lx-th-spec">Specification</th>
                @foreach($products as $i => $p)
                  <th><span class="lx-num {{ $i === 0 ? 'lx-accent' : '' }}">{{ $num($i) }}</span><a href="{{ route('product.show', $p) }}">{{ $p->name }}</a></th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @php $anyColors = $products->contains(fn ($p) => ! empty($colorsOf($p))); @endphp
              @if($anyColors)
                <tr>
                  <td class="lx-td-spec">Colours</td>
                  @foreach($products as $p)
                    <td>{{ collect($colorsOf($p))->pluck('name')->join(', ') ?: '—' }}</td>
                  @endforeach
                </tr>
              @endif
              @foreach($rows as $row)
                <tr>
                  <td class="lx-td-spec">{{ $row['label'] }}</td>
                  @foreach($products as $p)
                    <td>{{ $row['values'][$p->id] ?? $row['values'][(string) $p->id] ?? '—' }}</td>
                  @endforeach
                </tr>
              @endforeach
              <tr class="lx-tr-price">
                <td class="lx-td-spec">Price (BDT)</td>
                @foreach($products as $p)
                  <td>
                    <span class="lx-cell-price">
                      <span class="lx-strong">{{ money($p->price) }}</span>
                      @if($p->isOutOfStock())
                        <span class="lx-small lx-dim">Sold out</span>
                      @else
                        <button type="button" class="add-to-cart lx-btn lx-btn-xs lx-btn-ghost" {!! $dataAttrs($p, false) !!}>Add</button>
                      @endif
                    </span>
                  </td>
                @endforeach
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  @endif

  {{-- 5. REVIEWS, once there are some --}}
  @if($homeReviews->isNotEmpty())
    <section class="lx-section">
      <div class="lx-wrap">
        <div class="lx-head"><div><p class="lx-eyebrow">From our customers</p><h2 class="lx-h2">{{ setting('home_reviews_title', 'Customer Feedback') }}</h2></div></div>
        <div class="lx-reviews">
          @foreach($homeReviews as $r)
            <figure class="lx-review">
              <span class="lx-stars" aria-label="{{ (int) ($r->rating ?: 5) }} out of 5">{{ str_repeat('★', (int) ($r->rating ?: 5)) }}<span class="lx-dim">{{ str_repeat('★', 5 - (int) ($r->rating ?: 5)) }}</span></span>
              <blockquote>“{{ \Illuminate\Support\Str::limit($r->body, 220) }}”</blockquote>
              <figcaption><span>{{ $r->author_name }}</span>@if($r->product)<span class="lx-dim"> · {{ $r->product->name }}</span>@endif</figcaption>
            </figure>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- 6. TRUST ROW --}}
  @if(($L['trust']['on'] ?? true) && $trust->isNotEmpty())
    <section class="lx-section lx-alt lx-section-tight">
      <div class="lx-wrap lx-trust">
        @foreach($trust as $t)
          <div class="lx-trust-item">
            <span class="lx-trust-ico"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$t['icon'] ?? 'shield'] ?? $icons['shield'] !!}</svg></span>
            <span>
              <span class="lx-trust-t">{{ $t['title'] }}</span>
              @if($t['text'])<span class="lx-trust-d">{{ $t['text'] }}</span>@endif
            </span>
          </div>
        @endforeach
      </div>
    </section>
  @endif

</div>
@endsection

@push('scripts')
<script>
/* Hero colour dots: pick a colour, show its photo */
document.addEventListener('click', function (e) {
  var sw = e.target.closest('[data-lx-swatches] .lx-swatch');
  if (!sw) return;
  sw.parentNode.querySelectorAll('.lx-swatch').forEach(function (b) {
    b.classList.toggle('is-on', b === sw);
    b.setAttribute('aria-pressed', b === sw ? 'true' : 'false');
  });
  var name = sw.parentNode.querySelector('[data-lx-swatch-name]');
  if (name) name.textContent = sw.getAttribute('data-name');
  var img = document.querySelector('[data-lx-hero-img]');
  if (img) {
    var src = sw.getAttribute('data-img') || img.getAttribute('data-default');
    if (img.getAttribute('src') !== src) { img.style.opacity = '0'; setTimeout(function () { img.src = src; img.style.opacity = '1'; }, 180); }
  }
});

/* Hero product card: swipes on phones (native scroll-snap), changes by itself every 5 seconds, and stops
   while the pointer is over it, something in it has focus, a finger is on it, or the tab is in the background */
(function () {
  var root = document.querySelector('[data-lx-rot]');
  if (!root) return;
  var track = root.querySelector('[data-lx-rot-track]');
  var slides = track.children;
  var dots = root.querySelectorAll('[data-lx-rot-dot]');
  var n = slides.length, cur = 0, timer = null, hover = false, focus = false, touchUntil = 0;
  var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function mark(i) {
    if (i === cur) return;
    cur = i;
    dots.forEach(function (d, k) { d.classList.toggle('is-on', k === i); d.setAttribute('aria-current', k === i ? 'true' : 'false'); });
    Array.prototype.forEach.call(slides, function (s, k) {
      if (k === i) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
      s.querySelectorAll('a, button:not([disabled])').forEach(function (el) { el.tabIndex = k === i ? 0 : -1; });
    });
  }
  function go(i) {
    i = (i + n) % n;
    track.scrollTo({ left: i * track.clientWidth, behavior: still ? 'auto' : 'smooth' });
    mark(i);
  }
  var t;
  track.addEventListener('scroll', function () {
    clearTimeout(t);
    t = setTimeout(function () { mark(Math.max(0, Math.min(n - 1, Math.round(track.scrollLeft / track.clientWidth)))); }, 60);
  }, { passive: true });

  function paused() { return hover || focus || document.hidden || Date.now() < touchUntil; }
  function start() { if (still) return; clearInterval(timer); timer = setInterval(function () { if (!paused()) go(cur + 1); }, 5000); }

  root.addEventListener('mouseenter', function () { hover = true; });
  root.addEventListener('mouseleave', function () { hover = false; });
  // Only keyboard focus pauses it; a mouse click on a dot or arrow shouldn't stop it for good
  root.addEventListener('focusin', function (e) { focus = e.target.matches(':focus-visible'); });
  root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) focus = false; });
  root.addEventListener('touchstart', function () { touchUntil = Date.now() + 10000; }, { passive: true });
  root.addEventListener('click', function (e) {
    var d = e.target.closest('[data-lx-rot-dot]');
    if (d) { go(+d.getAttribute('data-lx-rot-dot')); start(); return; }
    if (e.target.closest('[data-lx-rot-prev]')) { go(cur - 1); start(); return; }
    if (e.target.closest('[data-lx-rot-next]')) { go(cur + 1); start(); return; }
    // Buy now opens the colour picker or checkout: keep this product in view meanwhile
    if (e.target.closest('.add-to-cart')) touchUntil = Date.now() + 15000;
  });
  root.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') { go(cur - 1); start(); }
    if (e.key === 'ArrowRight') { go(cur + 1); start(); }
  });
  window.addEventListener('resize', function () { track.scrollLeft = cur * track.clientWidth; });
  start();
})();

/* Material section: a pin and its point light up together */
document.addEventListener('click', function (e) {
  var hit = e.target.closest('[data-lx-pin]');
  if (!hit) return;
  var n = hit.getAttribute('data-lx-pin');
  document.querySelectorAll('[data-lx-pin]').forEach(function (el) {
    el.classList.toggle('is-on', el.getAttribute('data-lx-pin') === n);
  });
  // With admin pictures, show the tapped point's own picture, or the main picture
  var img = document.querySelector('[data-lx-mat-img]');
  if (img) {
    var src = hit.getAttribute('data-img') || img.getAttribute('data-default');
    if (src && img.getAttribute('src') !== src) { img.style.opacity = '0'; setTimeout(function () { img.src = src; img.style.opacity = '1'; }, 180); }
  }
});
</script>
@endpush
