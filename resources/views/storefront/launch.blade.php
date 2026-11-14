@extends('layouts.storefront')

{{-- Launch mode homepage (Admin → Homepage → Launch mode, App\Support\LaunchMode). The normal homepage is
     storefront/home.blade.php and is left untouched; switching launch mode off shows it again. --}}

@section('body_class', 'lx-home bg-white text-ink antialiased')

@section('content')
@php
  $L = $launch;
  $count = $products->count();
  $lead = $products->first();
  $num = fn ($i) => str_pad($i + 1, 2, '0', STR_PAD_LEFT);
  $colorsOf = fn ($p) => \App\Support\ProductCardOptions::for($p)['colors'] ?? [];

  // Hero: the first live hero banner alone (no slider)
  $hb = $heroBanner;
  $heroTitle = $hb?->title ?: setting('hero_fallback_title') ?: ($lead?->name ?? site_name());
  $heroText = $hb?->subtitle ?: setting('hero_fallback_subtitle') ?: ($lead?->short_description ? \Illuminate\Support\Str::limit(strip_tags($lead->short_description), 180) : null);
  $heroBadge = $hb?->badge ?: setting('hero_fallback_badge') ?: ($L['collection']['label'] ?? null);

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

  {{-- 1. HERO: one banner, no slider --}}
  @if($hb && $hb->image)
    <section class="lx-wrap lx-hero-image">
      <a href="{{ $hb->linkHref() }}" class="lx-hero-pic" aria-label="{{ $hb->title ?: 'Banner' }}">
        <img src="{{ $hb->imageUrl() }}" alt="{{ $hb->title }}" loading="eager">
      </a>
    </section>
  @else
    <section class="lx-hero">
      <div class="lx-wrap lx-hero-grid">
        <div class="lx-hero-copy">
          @if($heroBadge)<span class="lx-chip">{{ $heroBadge }}</span>@endif
          <h1 class="lx-h1">{{ $heroTitle }}</h1>
          @if($heroText)<p class="lx-lead">{{ $heroText }}</p>@endif

          @if($lead && ($leadColors = $colorsOf($lead)))
            <div class="lx-finish">
              <span class="lx-mono lx-dim">Colours</span>
              <span class="lx-dots">
                @foreach(array_slice($leadColors, 0, 4) as $col)
                  <span class="lx-dot" title="{{ $col['name'] }}" @if($col['swatch']) style="background: {{ $col['swatch'] }}" @endif></span>
                @endforeach
              </span>
              <span class="lx-dim lx-small">{{ collect($leadColors)->pluck('name')->join(' · ') }}</span>
            </div>
          @endif

          <div class="lx-hero-cta">
            @if($lead && ! $lead->isOutOfStock())
              <button type="button" class="add-to-cart lx-btn lx-btn-main" {!! $dataAttrs($lead, true) !!}>
                <span>{{ $hb?->button_text ?: 'Order now' }} — <span class="lx-mono">{{ money($lead->price) }}</span></span>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0-5 5m5-5H6"/></svg>
              </button>
            @elseif($hb && $hb->button_text)
              <a href="{{ $hb->linkHref() }}" class="lx-btn lx-btn-main">{{ $hb->button_text }}</a>
            @endif
            @if($count > 1)
              <a href="#collection" class="lx-btn lx-btn-ghost">See all {{ $count }}</a>
            @elseif($lead)
              <a href="{{ route('product.show', $lead) }}" class="lx-btn lx-btn-ghost">View details</a>
            @endif
          </div>

          <div class="lx-meta lx-mono">
            <span>● Cash on delivery</span>
            @if($lead && ! $lead->isOutOfStock())<span>● In stock</span>@endif
          </div>
        </div>

        <div class="lx-stage">
          <div class="lx-stage-grid" aria-hidden="true"></div>
          @if($lead)
            <a href="{{ route('product.show', $lead) }}" class="lx-stage-img" aria-label="{{ $lead->name }}">
              <img src="{{ $lead->imageUrl() }}" alt="{{ $lead->name }}" loading="eager">
            </a>
            <div class="lx-stage-bar lx-mono">
              <span class="lx-truncate">{{ $lead->name }}</span>
              <span class="lx-accent">{{ money($lead->price) }}</span>
            </div>
          @endif
        </div>
      </div>
    </section>
  @endif

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
                <span class="lx-mono lx-num">{{ $num($i) }}.</span>
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
                <span class="lx-price lx-mono">
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
        <div>
          @if($L['material']['label'])<p class="lx-eyebrow">{{ $L['material']['label'] }}</p>@endif
          @if($L['material']['heading'])<h2 class="lx-h2 lx-h2-lg">{{ $L['material']['heading'] }}</h2>@endif
          <div class="lx-pillars">
            @foreach($pillars as $pi => $pl)
              <button type="button" class="lx-pillar {{ $pi === 0 ? 'is-on' : '' }}" data-lx-pin="{{ $pi }}">
                <span class="lx-pillar-n lx-mono">{{ $num($pi) }}</span>
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
                <th class="lx-mono lx-th-spec">Specification</th>
                @foreach($products as $i => $p)
                  <th><span class="lx-mono lx-num {{ $i === 0 ? 'lx-accent' : '' }}">{{ $num($i) }}</span><a href="{{ route('product.show', $p) }}">{{ $p->name }}</a></th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @php $anyColors = $products->contains(fn ($p) => ! empty($colorsOf($p))); @endphp
              @if($anyColors)
                <tr>
                  <td class="lx-mono lx-td-spec">Colours</td>
                  @foreach($products as $p)
                    <td>{{ collect($colorsOf($p))->pluck('name')->join(', ') ?: '—' }}</td>
                  @endforeach
                </tr>
              @endif
              @foreach($rows as $row)
                <tr>
                  <td class="lx-mono lx-td-spec">{{ $row['label'] }}</td>
                  @foreach($products as $p)
                    <td>{{ $row['values'][$p->id] ?? $row['values'][(string) $p->id] ?? '—' }}</td>
                  @endforeach
                </tr>
              @endforeach
              <tr class="lx-tr-price">
                <td class="lx-mono lx-td-spec">Price (BDT)</td>
                @foreach($products as $p)
                  <td>
                    <span class="lx-cell-price">
                      <span class="lx-mono lx-strong">{{ money($p->price) }}</span>
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
/* Material section: a pin and its point light up together */
document.addEventListener('click', function (e) {
  var hit = e.target.closest('[data-lx-pin]');
  if (!hit) return;
  var n = hit.getAttribute('data-lx-pin');
  document.querySelectorAll('[data-lx-pin]').forEach(function (el) {
    el.classList.toggle('is-on', el.getAttribute('data-lx-pin') === n);
  });
});
</script>
@endpush
