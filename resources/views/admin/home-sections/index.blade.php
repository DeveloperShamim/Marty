@extends('layouts.admin')
@section('title', 'Homepage sections')
@section('subtitle', 'Choose which sections your homepage shows and in what order.')

@section('page-actions')
  <a href="{{ route('home') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@section('content')
<div class="space-y-4 max-w-3xl">
  <form method="POST" action="{{ route('admin.home-sections.update') }}" class="panel p-4 sm:p-5 flex flex-col gap-4" id="hsForm">
    @csrf @method('PUT')

    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Sections</h2>
      <p class="text-xs text-gray-500 mt-0.5">Drag <span class="hidden sm:inline">the dots</span><span class="sm:hidden">or use the arrows</span> to change the order. Switch a section off to hide it. The hero banner always comes first. A section with nothing to show (for example, no coupons) hides itself.</p>
    </div>
    @error('order')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

    <div class="rounded-2xl bg-gray-50 px-3.5 py-3 flex items-center gap-3">
      <span class="h-8 w-8 shrink-0 rounded-lg bg-white ring-1 ring-gray-200 grid place-items-center text-gray-400" aria-hidden="true">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="m2 15 5-4 4 3 4-5 7 6"/></svg>
      </span>
      <span class="min-w-0 flex-1">
        <span class="block text-[13px] font-medium text-gray-900">Hero banner</span>
        <span class="block text-[11px] text-gray-500">Always at the top. Edit it in <a href="{{ route('admin.banners.index') }}" class="underline">Hero banners</a>.</span>
      </span>
    </div>

    <ol id="hsList" class="space-y-2">
      @foreach($sections as $s)
        <li class="rounded-2xl ring-1 ring-gray-200 bg-white px-3 py-2.5 flex items-center gap-2.5 {{ $s['on'] ? '' : 'opacity-60' }}" data-key="{{ $s['key'] }}" @if($s['fixed']) data-fixed @endif>
          <input type="hidden" name="order[]" value="{{ $s['key'] }}">
          @if($s['fixed'])
            <span class="h-8 w-6 shrink-0" aria-hidden="true"></span>
          @else
            <span class="hs-handle hidden sm:grid h-8 w-6 shrink-0 place-items-center text-gray-400 cursor-grab" title="Drag to move" aria-hidden="true">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
            </span>
          @endif
          <span class="min-w-0 flex-1">
            <span class="block text-[13px] font-medium text-gray-900">{{ $s['name'] }}</span>
            <span class="block text-[11px] text-gray-500 leading-snug">{{ $s['hint'] }}{{ $s['fixed'] ? ' · stays under the banner' : '' }}</span>
          </span>
          @unless($s['fixed'])
            <span class="flex items-center gap-1 shrink-0">
              <button type="button" class="hs-up h-8 w-8 grid place-items-center rounded-full hover:bg-gray-100 text-gray-500" aria-label="Move {{ $s['name'] }} up">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
              </button>
              <button type="button" class="hs-down h-8 w-8 grid place-items-center rounded-full hover:bg-gray-100 text-gray-500" aria-label="Move {{ $s['name'] }} down">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
              </button>
            </span>
          @endunless
          <label class="shrink-0 inline-flex cursor-pointer" title="Show on homepage">
            <input type="checkbox" name="on[]" value="{{ $s['key'] }}" class="peer sr-only hs-on" @checked($s['on'])>
            <span class="relative h-6 w-10 rounded-full bg-gray-200 transition-colors peer-checked:bg-gray-900 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-4"></span>
            <span class="sr-only">Show {{ $s['name'] }}</span>
          </label>
        </li>
      @endforeach
    </ol>

    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
      <button type="submit" name="reset" value="1" class="h-9 px-3.5 rounded-full text-[13px] font-medium text-gray-600 hover:bg-gray-100" onclick="return confirm('Show every section in the default order?')">Reset to default</button>
      <button type="submit" class="h-10 px-5 rounded-full text-[13px] font-semibold text-white" style="background: var(--brand-dark);">Save sections</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('theme/vendor/sortable/Sortable.min.js') }}"></script>
<script>
(function () {
  const list = document.getElementById('hsList');
  if (!list) return;
  const movable = () => [...list.querySelectorAll('li:not([data-fixed])')];

  list.addEventListener('click', (e) => {
    const up = e.target.closest('.hs-up'), down = e.target.closest('.hs-down');
    if (!up && !down) return;
    const li = e.target.closest('li');
    const items = movable(), i = items.indexOf(li);
    if (up && i > 0) items[i - 1].before(li);
    if (down && i < items.length - 1) items[i + 1].after(li);
    li.querySelector(up ? '.hs-up' : '.hs-down').focus();
  });

  list.addEventListener('change', (e) => {
    if (e.target.classList.contains('hs-on')) e.target.closest('li').classList.toggle('opacity-60', !e.target.checked);
  });

  if (window.Sortable) {
    Sortable.create(list, { handle: '.hs-handle', animation: 150, filter: '[data-fixed]', onMove: (ev) => !ev.related.hasAttribute('data-fixed') });
  }
})();
</script>
@endpush
