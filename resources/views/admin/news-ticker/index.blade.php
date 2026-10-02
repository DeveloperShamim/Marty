@extends('layouts.admin')
@section('title', 'News Ticker')

@section('content')
@php
  $liveCodes = \App\Models\Coupon::where('is_active', true)->get()
      ->filter(fn ($c) => $c->isCurrentlyActive())->pluck('code')->values();
@endphp

<div class="space-y-5 sm:space-y-6 max-w-4xl">

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2"><span>📰</span> News Ticker</h1>
      <p class="text-xs text-stone-500 mt-1">The scrolling headline bar at the very top of every storefront page, on phones, tablets and computers.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white hover:bg-stone-50 text-stone-800 border border-stone-200 font-extrabold text-xs shadow-2xs flex items-center justify-center gap-1.5 self-start sm:self-auto shrink-0">
      View on storefront <span class="text-stone-400">↗</span>
    </a>
  </div>

  {{-- Live preview --}}
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-2">
    <div class="flex items-center justify-between gap-2">
      <span class="text-xs font-black text-stone-800">Preview</span>
      <span class="text-[11px] text-stone-400">Updates as you type — click Save to publish</span>
    </div>
    <div id="tkPreview" class="tk-bar rounded-xl" style="--tk-from: {{ $theme['primary_hover'] }}; --tk-via: {{ $theme['primary'] }}; --tk-dark: {{ $theme['dark'] }};">
      <span id="tkLabel" class="tk-label"><span class="tk-dot"></span><span id="tkLabelText"></span></span>
      <span class="tk-window"><span id="tkTrack" class="tk-track"></span></span>
    </div>
    <p id="tkEmpty" class="hidden text-[11px] font-bold text-amber-700">No headlines and no countdown — the bar is hidden on the storefront.</p>
  </div>

  <form method="POST" action="{{ route('admin.news-ticker.update') }}" class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-5" id="tkForm">
    @csrf @method('PUT')

    <div>
      <label for="tkHeadlines" class="text-xs font-black text-stone-800 block mb-1">Headlines <span class="font-medium text-stone-400">— one per line</span></label>
      <textarea id="tkHeadlines" name="header_promo_text" rows="5" maxlength="1000" class="w-full text-sm font-semibold px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl leading-relaxed focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Cash on delivery all over Bangladesh&#10;Use code UNILIFE10 for 10% OFF&#10;bKash payment 10% cashback">{{ old('header_promo_text', $headlines) }}</textarea>
      <div class="mt-1.5 text-[11px] text-stone-500 space-y-0.5">
        <p>Write an active coupon code in a headline and customers can <b>tap it to copy</b>.
          @if($liveCodes->isNotEmpty())
            Active codes: @foreach($liveCodes as $code)<button type="button" data-insert="{{ $code }}" class="ml-1 px-1.5 py-0.5 rounded-md border border-dashed border-stone-300 font-mono font-bold text-stone-700 hover:bg-stone-100">{{ $code }}</button>@endforeach
          @else
            You have no active coupons right now.
          @endif
        </p>
        <p>Wrap words in <code>&lt;b&gt;…&lt;/b&gt;</code> for bold. Bangla text works too.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label for="tkLabelInput" class="text-xs font-black text-stone-800 block mb-1">Label text</label>
        <input id="tkLabelInput" name="ticker_label" maxlength="24" value="{{ old('ticker_label', $label) }}" placeholder="Hot Deals" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
        <p class="text-[11px] text-stone-500 mt-1">e.g. Hot Deals, Offers, Notice, অফার. Empty = no label.</p>
      </div>
      <div>
        <label for="tkStyle" class="text-xs font-black text-stone-800 block mb-1">Label colour</label>
        <select id="tkStyle" name="ticker_label_style" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
          <option value="dark" @selected(old('ticker_label_style', $labelStyle) === 'dark')>Dark (theme colour)</option>
          <option value="red" @selected(old('ticker_label_style', $labelStyle) === 'red')>Red (urgent notice)</option>
          <option value="white" @selected(old('ticker_label_style', $labelStyle) === 'white')>White</option>
        </select>
      </div>
      <div>
        <label for="tkLink" class="text-xs font-black text-stone-800 block mb-1">Headline link <span class="font-medium text-stone-400">(optional)</span></label>
        <input id="tkLink" name="header_promo_link" maxlength="255" value="{{ old('header_promo_link', $link) }}" placeholder="/shop or full URL" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
        <p class="text-[11px] text-stone-500 mt-1">Where a tapped headline goes. Empty = headlines are not links.</p>
      </div>
    </div>

    <label class="flex items-start gap-3 cursor-pointer rounded-xl border border-stone-200 bg-stone-50 p-3">
      <span class="relative inline-flex items-center shrink-0 mt-0.5">
        <input id="tkCountdown" type="checkbox" name="ticker_show_countdown" value="1" @checked(old('ticker_show_countdown', $showCountdown)) class="sr-only peer">
        <span class="w-11 h-6 bg-stone-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></span>
      </span>
      <span>
        <span class="block text-xs font-extrabold text-stone-800">Show flash sale countdown</span>
        <span class="block text-[11px] text-stone-500">
          Adds “⚡ Flash Sale ends in …” as the first headline while a flash sale is running.
          @if($flashEnds)
            Current end time: <b>{{ $flashEnds->format('d M Y, g:i A') }}</b>.
          @else
            <b class="text-amber-700">No end time is set</b>, so it won't show yet.
          @endif
          Change it on <a href="{{ route('admin.flash-sale.index') }}" class="text-brand-700 font-bold underline">Flash Sale</a>.
        </span>
      </span>
    </label>

    <div class="flex justify-end pt-1">
      <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all">Save News Ticker</button>
    </div>
  </form>
</div>

<style>
  .tk-bar { display: flex; align-items: stretch; height: 40px; overflow: hidden; color: #fff; background: linear-gradient(90deg, var(--tk-from), var(--tk-via), var(--tk-from)); }
  .tk-label { flex-shrink: 0; display: flex; align-items: center; gap: 6px; padding: 0 26px 0 16px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; background: var(--tk-dark); color: #fff; clip-path: polygon(0 0, 100% 0, calc(100% - 10px) 100%, 0 100%); position: relative; z-index: 1; }
  .tk-label.is-red { background: #dc2626; } .tk-label.is-white { background: #fff; color: var(--tk-dark); }
  .tk-dot { width: 8px; height: 8px; border-radius: 9999px; background: #ef4444; animation: tk-blink 1.2s ease-in-out infinite; }
  .tk-label.is-red .tk-dot { background: #fff; }
  @keyframes tk-blink { 50% { opacity: .4; } }
  .tk-window { flex: 1; min-width: 0; display: flex; align-items: center; overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 14px, #000 calc(100% - 18px), transparent 100%); mask-image: linear-gradient(90deg, transparent 0, #000 14px, #000 calc(100% - 18px), transparent 100%); }
  .tk-track { display: flex; width: max-content; font-size: 14px; font-weight: 700; white-space: nowrap; animation: tk-scroll var(--tk-dur, 20s) linear infinite; }
  .tk-track b, .tk-track strong { font-weight: 800; }
  .tk-sep { margin: 0 24px; font-size: 9px; opacity: .7; }
  .tk-code { padding: 1px 7px; margin: 0 2px; border: 1.5px dashed rgba(255,255,255,.75); border-radius: 6px; background: rgba(255,255,255,.14); font-weight: 800; }
  .tk-clock { margin-left: 4px; padding: 1px 6px; border-radius: 6px; background: rgba(0,0,0,.28); font-variant-numeric: tabular-nums; font-weight: 800; }
  @keyframes tk-scroll { to { transform: translateX(-50%); } }
</style>

<script>
(function () {
  const $ = (id) => document.getElementById(id);
  const codes = @json($liveCodes);
  const flashEnds = @json($flashEnds?->toIso8601String());
  const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  // Same rules as the storefront: only <b>/<strong> survive; live coupon codes get the "tap to copy" look.
  const format = (line) => {
    // Keep <b>/<strong> (even with attributes, e.g. <b class="…">) but drop their attributes; escape everything else.
    const marked = line.replace(/<(\/?)(b|strong)\b[^>]*>/gi, (m, slash, tag) => '\u0001' + slash + tag.toLowerCase() + '\u0002');
    let html = esc(marked).replace(/\u0001(\/?)(b|strong)\u0002/g, '<$1$2>');
    codes.forEach((code) => {
      const re = new RegExp('(^|[^\\w-])(' + code.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')(?![\\w-])', 'gi');
      html = html.replace(re, '$1<span class="tk-code">$2</span>');
    });
    return html;
  };
  const clock = () => {
    let diff = Math.max(0, Date.parse(flashEnds) - Date.now());
    const d = Math.floor(diff / 8.64e7); diff -= d * 8.64e7;
    const pad = (n) => String(n).padStart(2, '0');
    const h = Math.floor(diff / 3.6e6), m = Math.floor(diff % 3.6e6 / 6e4), s = Math.floor(diff % 6e4 / 1e3);
    return (d ? d + 'd ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
  };

  function render() {
    const items = $('tkHeadlines').value.split(/\r?\n|\|\|/).map((l) => l.trim()).filter(Boolean).map(format);
    if ($('tkCountdown').checked && flashEnds && Date.parse(flashEnds) > Date.now()) {
      items.unshift('⚡ Flash Sale ends in <span class="tk-clock" data-tk-clock>' + clock() + '</span>');
    }
    $('tkEmpty').classList.toggle('hidden', items.length > 0);
    $('tkPreview').style.display = items.length ? '' : 'none';

    const label = $('tkLabelInput').value.trim();
    $('tkLabel').style.display = label ? '' : 'none';
    $('tkLabelText').textContent = label;
    $('tkLabel').className = 'tk-label' + ($('tkStyle').value === 'red' ? ' is-red' : $('tkStyle').value === 'white' ? ' is-white' : '');

    if (!items.length) { $('tkTrack').innerHTML = ''; return; }
    const chars = items.join(' ').replace(/<[^>]+>/g, '').length + 6 * items.length;
    const loop = Array(Math.max(1, Math.ceil(280 / chars))).fill(items).flat();
    const copy = '<span style="display:flex">' + loop.map((h) => '<span>' + h + '</span><span class="tk-sep">&#9670;</span>').join('') + '</span>';
    $('tkTrack').innerHTML = copy + copy;
    $('tkTrack').style.setProperty('--tk-dur', Math.max(14, Math.round(loop.join(' ').replace(/<[^>]+>/g, '').length * 0.16)) + 's');
  }

  ['tkHeadlines', 'tkLabelInput'].forEach((id) => $(id).addEventListener('input', render));
  ['tkStyle', 'tkCountdown'].forEach((id) => $(id).addEventListener('change', render));
  document.querySelectorAll('[data-insert]').forEach((btn) => btn.addEventListener('click', () => {
    const ta = $('tkHeadlines'), code = btn.dataset.insert;
    const at = ta.selectionStart ?? ta.value.length;
    ta.value = ta.value.slice(0, at) + code + ta.value.slice(ta.selectionEnd ?? at);
    ta.focus(); ta.selectionStart = ta.selectionEnd = at + code.length;
    render();
  }));
  setInterval(() => document.querySelectorAll('[data-tk-clock]').forEach((el) => { el.textContent = clock(); }), 1000);
  render();
})();
</script>
@endsection
