@extends('layouts.admin')
@section('title', 'Menu bar')
@section('subtitle', 'The menu under the logo on every store page: what it shows, in which order, and each dropdown.')

@section('page-actions')
  <a href="{{ route('home') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@section('content')
<div class="space-y-4 max-w-4xl">

  {{-- Live preview of the desktop bar --}}
  <section class="panel p-4 sm:p-5 space-y-2.5">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
      <h2 class="text-[15px] font-semibold text-gray-900">Preview</h2>
      <span class="text-xs text-gray-500">Updates as you edit. Save to publish.</span>
    </div>
    <div id="hmPreviewBar" class="rounded-xl ring-1 px-2 py-2 overflow-x-auto {{ $style === 'dark' ? 'ring-transparent text-white/90' : 'ring-gray-200 bg-white text-gray-700' }}" @if($style === 'dark') style="background: var(--brand-dark);" @endif>
      <div id="hmPreview" class="flex items-center gap-1 text-[13px] font-semibold whitespace-nowrap"></div>
    </div>
    <p class="text-[11px] text-gray-500">On phones the same links appear in the side menu. Home and All Products are already there as the logo and the "Shop all" tile.</p>
  </section>

  <form method="POST" action="{{ route('admin.header-menu.update') }}" class="panel p-4 sm:p-5 flex flex-col gap-4" id="hmForm">
    @csrf @method('PUT')
    <input type="hidden" name="menu" id="hmJson">

    <fieldset>
      <legend class="text-[15px] font-semibold text-gray-900">Bar style</legend>
      <div class="mt-2 grid grid-cols-2 gap-2.5 max-w-md">
        @foreach(['light' => 'Light', 'dark' => 'Dark'] as $key => $name)
          <label class="cursor-pointer rounded-2xl ring-1 ring-gray-200 p-2.5 has-[:checked]:ring-2 has-[:checked]:ring-gray-900 transition">
            <input type="radio" name="menu_style" value="{{ $key }}" class="sr-only" data-style-pick @checked($style === $key)>
            <span class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-[11px] font-semibold {{ $key === 'dark' ? 'text-white/90' : 'bg-white ring-1 ring-gray-200 text-gray-700' }}" @if($key === 'dark') style="background: var(--brand-dark);" @endif>
              <span>Home</span><span>Shoes</span><span>Watches</span><span class="hidden sm:inline">Belts</span>
            </span>
            <span class="mt-2 block text-[13px] font-semibold text-gray-900">{{ $name }}</span>
            <span class="block text-[11px] text-gray-500">{{ $key === 'dark' ? 'Your theme’s dark colour with white text' : 'White bar with dark text' }}</span>
          </label>
        @endforeach
      </div>
    </fieldset>

    <div class="flex flex-wrap items-start justify-between gap-2">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Menu items</h2>
        <p class="text-xs text-gray-500 mt-0.5">Drag <span class="hidden sm:inline">the dots</span><span class="sm:hidden">or use the arrows</span> to change the order. The eye hides an item without deleting it.</p>
      </div>
      @unless($isCustom)
        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold">Showing the default menu</span>
      @endunless
    </div>
    @error('menu')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
    <p id="hmNoAuto" class="hidden rounded-xl bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">“All categories” isn’t in your menu, so a new category won’t show in the bar until you add it here.</p>

    <ol id="hmList" class="space-y-2.5"></ol>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2">
      <button type="button" id="hmAdd" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Add menu item
      </button>
      <button type="submit" class="w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold cursor-pointer" style="background: var(--brand-dark);">Save menu</button>
    </div>
  </form>

  @if($isCustom)
    <form method="POST" action="{{ route('admin.header-menu.update') }}" class="panel p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3" onsubmit="return confirm('Reset the menu bar to the default?')">
      @csrf @method('PUT')
      <input type="hidden" name="reset" value="1">
      <div>
        <h2 class="text-[13px] font-semibold text-gray-900">Start over</h2>
        <p class="text-xs text-gray-500 mt-0.5">Go back to Home, All Products, every category and Brands.</p>
      </div>
      <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-[13px] font-semibold cursor-pointer shrink-0">Reset to default</button>
    </form>
  @endif
</div>

<template id="hmRowTpl">
  <li class="rounded-2xl bg-gray-50/80 ring-1 ring-gray-200/70 p-3 sm:p-3.5 space-y-2.5" data-row>
    <div class="flex flex-wrap items-center gap-2">
      <span class="hidden sm:grid h-8 w-5 -mx-1 place-items-center text-gray-400 cursor-grab active:cursor-grabbing" data-grip title="Drag to reorder" aria-hidden="true"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></span>
      <span class="h-6 min-w-6 px-1.5 rounded-full bg-white ring-1 ring-gray-200 grid place-items-center text-[11px] font-bold text-gray-500 tabular-nums" data-num></span>
      <select class="inp !py-2 flex-1 min-w-[11rem]" data-type aria-label="What this item opens"></select>
      <span class="hidden px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 text-[10px] font-bold uppercase tracking-wide shrink-0" data-hidden-badge>Hidden</span>
      <div class="flex items-center gap-1 shrink-0 ml-auto">
        <button type="button" data-toggle class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 grid place-items-center cursor-pointer" aria-label="Hide from the store"></button>
        <button type="button" data-up class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 grid place-items-center cursor-pointer disabled:opacity-30 disabled:cursor-default" aria-label="Move up"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg></button>
        <button type="button" data-down class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 grid place-items-center cursor-pointer disabled:opacity-30 disabled:cursor-default" aria-label="Move down"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
        <button type="button" data-remove class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 grid place-items-center cursor-pointer" aria-label="Remove"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg></button>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" data-fields>
      <select class="inp !py-2" data-ref-category aria-label="Category"></select>
      <select class="inp !py-2" data-ref-brand aria-label="Brand"></select>
      <input class="inp !py-2" data-url maxlength="255" placeholder="Link, e.g. /page/about or https://…" aria-label="Link">
      <input class="inp !py-2" data-label maxlength="40" aria-label="Name in the menu">
    </div>
    <p class="hidden text-[11px] font-medium text-rose-600" data-error></p>
    <p class="text-[11px] text-gray-500" data-hint></p>
    <div class="space-y-2" data-children-box>
      <ol class="space-y-2 pl-3 sm:pl-5 border-l-2 border-gray-200 empty:hidden" data-children></ol>
      <button type="button" data-add-child class="text-xs font-semibold text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Add dropdown link
      </button>
    </div>
  </li>
</template>

<script>
(function () {
  const TYPES = @json($types);
  const CATS = @json($categories);
  const BRANDS = @json($brands);
  let items = @json($items);
  let dragging = null;

  const $ = (id) => document.getElementById(id);
  const typeOf = (key) => TYPES.find((t) => t.key === key) || TYPES[0];
  const AUTO = { home: 'Home', shop: 'All Products', new: 'New Arrivals', best: 'Best Sellers', featured: 'Top Selling', flash: 'Flash Sale', track: 'Track Order' };
  const nameOf = (it) => {
    if (it.label) return it.label;
    if (it.type === 'category') return (CATS.find((c) => c.id === +it.ref) || {}).name?.split(' › ').pop()?.replace(' (hidden)', '') || 'Category';
    if (it.type === 'brand') return (BRANDS.find((b) => b.id === +it.ref) || {}).name?.replace(' (hidden)', '') || 'Brand';
    return AUTO[it.type] || 'Link';
  };
  const options = (sel, list, value, blank) => {
    sel.innerHTML = (blank ? '<option value="">' + blank + '</option>' : '') + list.map((o) => '<option value="' + o.id + '">' + o.name.replace(/</g, '&lt;') + '</option>').join('');
    sel.value = value ?? '';
  };

  function bindRow(li, it, list, index, isChild) {
    const show = (el, on) => el.classList.toggle('hidden', !on);
    const type = li.querySelector('[data-type]');
    const allowed = TYPES.filter((t) => !isChild || t.child)
      // "All categories" and "Brands dropdown" can each be used once
      .filter((t) => isChild || !['categories', 'brands'].includes(t.key) || t.key === it.type || !items.some((x) => x.type === t.key));
    type.innerHTML = allowed.map((t) => '<option value="' + t.key + '">' + t.label + '</option>').join('');
    type.value = it.type;
    if (isChild) type.classList.add('!text-[13px]');

    li.querySelector('[data-num]').textContent = isChild ? '↳' : index + 1;
    const cat = li.querySelector('[data-ref-category]'), brand = li.querySelector('[data-ref-brand]');
    const url = li.querySelector('[data-url]'), label = li.querySelector('[data-label]');
    options(cat, CATS, it.type === 'category' ? it.ref : '', 'Choose a category');
    options(brand, BRANDS, it.type === 'brand' ? it.ref : '', 'Choose a brand');
    url.value = it.url || '';
    label.value = it.label || '';
    show(cat, it.type === 'category'); show(brand, it.type === 'brand'); show(url, it.type === 'link');
    const fixed = ['categories', 'brands'].includes(it.type);
    show(label, !fixed);
    show(li.querySelector('[data-fields]'), !fixed);
    label.placeholder = it.type === 'link' ? 'Name in the menu (required)' : 'Name in the menu (optional): ' + nameOf({ ...it, label: '' });

    const hints = {
      categories: 'Every category that has products, as many as fit on one line; the rest go under “More”. Categories with sub-categories open them on hover.',
      brands: 'A Brands dropdown with every brand’s logo (shows when you have more than one brand).',
      category: isChild ? '' : 'Its sub-categories show in its dropdown automatically. Links you add below come after them.',
      flash: 'Only shows while a flash sale with an end time is running.',
      link: 'A page on this site (start with /) or a full web address.',
    };
    const hint = li.querySelector('[data-hint]');
    hint.textContent = hints[it.type] || '';
    show(hint, !!hint.textContent);

    const eyeOpen = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeShut = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10.7 5.1A10 10 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-2.2 3.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="m2 2 20 20"/></svg>';
    const toggle = li.querySelector(':scope > div > div > [data-toggle]');
    toggle.innerHTML = it.hidden ? eyeShut : eyeOpen;
    toggle.setAttribute('aria-label', it.hidden ? 'Show on the store' : 'Hide from the store');
    toggle.title = toggle.getAttribute('aria-label');
    toggle.onclick = () => { it.hidden = !it.hidden; if (!it.hidden) delete it.hidden; render(); };
    show(li.querySelector('[data-hidden-badge]'), !!it.hidden);
    li.classList.toggle('opacity-60', !!it.hidden);

    li.querySelector('[data-up]').disabled = index === 0;
    li.querySelector('[data-down]').disabled = index === list.length - 1;
    li.querySelector('[data-up]').onclick = () => { [list[index - 1], list[index]] = [list[index], list[index - 1]]; render(); };
    li.querySelector('[data-down]').onclick = () => { [list[index + 1], list[index]] = [list[index], list[index + 1]]; render(); };
    li.querySelector('[data-remove]').onclick = () => { list.splice(index, 1); render(); };

    type.onchange = () => { it.type = type.value; delete it.ref; delete it.url; if (['categories', 'brands'].includes(it.type)) { delete it.label; delete it.children; } render(); };
    cat.onchange = () => { it.ref = +cat.value || null; render(); };
    brand.onchange = () => { it.ref = +brand.value || null; render(); };
    const clearError = () => { [cat, brand, url, label].forEach((el) => el.classList.remove('!ring-2', '!ring-rose-400')); show(li.querySelector('[data-error]'), false); };
    url.oninput = () => { it.url = url.value.trim(); clearError(); };
    label.oninput = () => { it.label = label.value.trim(); clearError(); preview(); };
    it._mark = (msg, el) => { el.classList.add('!ring-2', '!ring-rose-400'); const p = li.querySelector('[data-error]'); p.textContent = msg; show(p, true); };
    it._fields = { cat, brand, url, label };

    // Drag to reorder (desktop): grab the dots, drop on another item of the same list
    const grip = li.querySelector(':scope > div > [data-grip]');
    grip.onmousedown = () => { li.draggable = true; };
    li.ondragstart = (e) => { e.stopPropagation(); dragging = { list, index }; e.dataTransfer.effectAllowed = 'move'; li.classList.add('opacity-40'); };
    li.ondragend = () => { li.draggable = false; li.classList.remove('opacity-40'); dragging = null; };
    li.ondragover = (e) => { if (dragging && dragging.list === list) { e.preventDefault(); e.stopPropagation(); li.classList.add('ring-2', 'ring-gray-900'); } };
    li.ondragleave = () => li.classList.remove('ring-2', 'ring-gray-900');
    li.ondrop = (e) => {
      if (!dragging || dragging.list !== list) return;
      e.preventDefault(); e.stopPropagation();
      const [moved] = list.splice(dragging.index, 1);
      list.splice(index, 0, moved);
      dragging = null; render();
    };

    const box = li.querySelector('[data-children-box]');
    show(box, !isChild && !fixed);
    if (!isChild && !fixed) {
      it.children = it.children || [];
      const ol = li.querySelector('[data-children]');
      it.children.forEach((child, ci) => ol.appendChild(makeRow(child, it.children, ci, true)));
      // Home, All Products and the like rarely need a dropdown: keep the button quiet until there is one
      box.querySelector(':scope > [data-add-child]').classList.toggle('opacity-70', !it.children.length);
      box.querySelector(':scope > [data-add-child]').onclick = () => { it.children.push({ type: 'link' }); render(); };
    }
  }

  function makeRow(it, list, index, isChild) {
    const li = $('hmRowTpl').content.firstElementChild.cloneNode(true);
    if (isChild) { li.classList.remove('bg-gray-50/80', 'p-3', 'sm:p-3.5'); li.classList.add('bg-white', 'p-2.5'); }
    bindRow(li, it, list, index, isChild);
    return li;
  }

  function preview() {
    const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
    const caret = '<svg class="w-3 h-3 inline -mt-px" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path d="m19 9-7 7-7-7"/></svg>';
    $('hmNoAuto').classList.toggle('hidden', items.some((it) => it.type === 'categories' && !it.hidden));
    $('hmPreview').innerHTML = items.filter((it) => !it.hidden).map((it) => {
      if (it.type === 'categories') return '<span class="px-2.5 py-1 rounded-lg opacity-60 italic">Categories… · More ' + caret + '</span>';
      if (it.type === 'brands') return '<span class="px-2.5 py-1">Brands ' + caret + '</span>';
      const drop = (it.children && it.children.some((c) => !c.hidden)) || (it.type === 'category' && CATS.some((c) => c.name.startsWith(nameOf(it) + ' › ')));
      return '<span class="px-2.5 py-1">' + esc(nameOf(it)) + (drop ? ' ' + caret : '') + '</span>';
    }).join('') || '<span class="px-2 text-gray-400">Empty. Add a menu item below.</span>';
  }

  function render() {
    const list = $('hmList');
    list.innerHTML = '';
    items.forEach((it, i) => list.appendChild(makeRow(it, items, i, false)));
    preview();
  }

  $('hmAdd').onclick = () => { items.push({ type: 'category' }); render(); list_scroll(); };
  const list_scroll = () => $('hmList').lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'center' });

  $('hmForm').addEventListener('submit', (e) => {
    let first = null;
    items.concat(...items.map((it) => it.children || [])).forEach((it) => {
      const f = it._fields;
      if (!f) return;
      let msg = null, el = null;
      if (it.type === 'category' && !it.ref) { msg = 'Choose a category.'; el = f.cat; }
      else if (it.type === 'brand' && !it.ref) { msg = 'Choose a brand.'; el = f.brand; }
      else if (it.type === 'link' && !it.label) { msg = 'Give this link a name.'; el = f.label; }
      else if (it.type === 'link' && !/^(\/(?!\/)|https?:\/\/|tel:|mailto:)/i.test(it.url || '')) { msg = 'Enter a link that starts with / or https://'; el = f.url; }
      if (msg) { it._mark(msg, el); first = first || el; }
    });
    if (first) {
      e.preventDefault();
      first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      first.focus({ preventScroll: true });
      return;
    }
    $('hmJson').value = JSON.stringify(items, (k, val) => (k.startsWith('_') ? undefined : val));
  });

  document.querySelectorAll('[data-style-pick]').forEach((r) => r.addEventListener('change', () => {
    const dark = r.value === 'dark' && r.checked, bar = $('hmPreviewBar');
    bar.className = 'rounded-xl ring-1 px-2 py-2 overflow-x-auto ' + (dark ? 'ring-transparent text-white/90' : 'ring-gray-200 bg-white text-gray-700');
    bar.style.background = dark ? 'var(--brand-dark)' : '';
  }));

  render();
})();
</script>
@endsection
