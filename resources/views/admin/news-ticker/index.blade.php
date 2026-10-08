@extends('layouts.admin')
@section('title', 'News ticker')
@section('subtitle', 'The scrolling headline bar at the top of every store page.')

@section('page-actions')
  <a href="{{ route('home') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@section('content')
@php
  $liveCodes = \App\Models\Coupon::where('is_active', true)->get()
      ->filter(fn ($c) => $c->isCurrentlyActive())->pluck('code')->values();
@endphp

<div class="space-y-4 max-w-4xl">

  {{-- Live preview --}}
  <section class="panel p-4 sm:p-5 space-y-2.5">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
      <h2 class="text-[15px] font-semibold text-gray-900">Preview</h2>
      <span class="text-xs text-gray-500">Updates as you type. Save to publish.</span>
    </div>
    <div id="tkPreview" class="tk-bar rounded-xl" style="--tk-from: {{ $theme['primary_hover'] }}; --tk-via: {{ $theme['primary'] }}; --tk-dark: {{ $theme['dark'] }};">
      <span id="tkLabel" class="tk-label"><span class="tk-dot"></span><span id="tkLabelText"></span></span>
      <span class="tk-window"><span id="tkTrack" class="tk-track"></span></span>
    </div>
    <p id="tkEmpty" class="hidden text-xs font-medium text-amber-700">No headlines and no countdown, so the bar is hidden on the store.</p>
  </section>

  <form method="POST" action="{{ route('admin.news-ticker.update') }}" class="panel p-4 sm:p-5 flex flex-col gap-4" id="tkForm">
    @csrf @method('PUT')

    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Ticker settings</h2>
      <p class="text-xs text-gray-500 mt-0.5">Headlines, label and link.</p>
    </div>

    <div>
      <label for="tkHeadlines" class="lbl">Headlines <span class="font-normal text-gray-400">(one per line)</span></label>
      <textarea id="tkHeadlines" name="header_promo_text" rows="5" maxlength="1000" class="inp leading-relaxed" placeholder="Cash on delivery all over Bangladesh&#10;Use code UNILIFE10 for 10% OFF&#10;bKash payment 10% cashback">{{ old('header_promo_text', $headlines) }}</textarea>
      <div class="mt-1.5 text-[11px] text-gray-500 space-y-1">
        <p>Write an active coupon code in a headline and customers can tap it to copy.
          @if($liveCodes->isNotEmpty())
            Active codes: @foreach($liveCodes as $code)<button type="button" data-insert="{{ $code }}" class="ml-1 px-2 py-0.5 rounded-full bg-gray-100 hover:bg-gray-200 font-mono font-medium text-gray-700">{{ $code }}</button>@endforeach
          @else
            You have no active coupons right now.
          @endif
        </p>
        <p>Wrap words in <code>&lt;b&gt;…&lt;/b&gt;</code> for bold. Bangla text works too.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label for="tkLabelInput" class="lbl">Label text</label>
        <input id="tkLabelInput" name="ticker_label" maxlength="24" value="{{ old('ticker_label', $label) }}" placeholder="Hot Deals" class="inp" />
        <p class="text-[11px] text-gray-500 mt-1">e.g. Hot Deals, Offers, Notice, অফার. Empty means no label.</p>
      </div>
      <div>
        <label for="tkStyle" class="lbl">Label colour</label>
        <select id="tkStyle" name="ticker_label_style" class="inp">
          <option value="dark" @selected(old('ticker_label_style', $labelStyle) === 'dark')>Dark (theme colour)</option>
          <option value="red" @selected(old('ticker_label_style', $labelStyle) === 'red')>Red (urgent notice)</option>
          <option value="white" @selected(old('ticker_label_style', $labelStyle) === 'white')>White</option>
        </select>
      </div>
      <div>
        <label for="tkLink" class="lbl">Headline link <span class="font-normal text-gray-400">(optional)</span></label>
        <input id="tkLink" name="header_promo_link" maxlength="255" value="{{ old('header_promo_link', $link) }}" placeholder="/shop or full URL" class="inp" />
        <p class="text-[11px] text-gray-500 mt-1">Where a tapped headline goes. Empty means no link.</p>
      </div>
    </div>

    <label class="flex items-start gap-3 cursor-pointer rounded-xl bg-gray-50 p-3.5">
      <span class="relative inline-flex items-center shrink-0 mt-0.5">
        <input id="tkCountdown" type="checkbox" name="ticker_show_countdown" value="1" @checked(old('ticker_show_countdown', $showCountdown)) class="sr-only peer">
        <span class="w-10 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-4 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></span>
      </span>
      <span>
        <span class="block text-[13px] font-semibold text-gray-900">Show flash sale countdown</span>
        <span class="block text-xs text-gray-500 mt-0.5">
          Adds “Flash Sale ends in …” as the first headline while a flash sale is running.
          @if($flashEnds)
            Current end time: <span class="font-medium text-gray-700">{{ $flashEnds->format('d M Y, g:i A') }}</span>.
          @else
            <span class="font-medium text-amber-700">No end time is set</span>, so it won't show yet.
          @endif
          Change it on <a href="{{ route('admin.flash-sale.index') }}" class="font-medium text-gray-800 underline">Flash sale</a>.
        </span>
      </span>
    </label>

    <div class="flex justify-end">
      <button type="submit" class="w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold" style="background: var(--brand-dark);">Save ticker</button>
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
