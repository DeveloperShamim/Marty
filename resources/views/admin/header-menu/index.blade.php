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
        <p class="text-xs text-gray-500 mt-0.5">Use the arrows to change the order. Give any item a dropdown with “Add dropdown link”.</p>
      </div>
      @unless($isCustom)
        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold">Showing the default menu</span>
      @endunless
    </div>
    @error('menu')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

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
    <div class="flex items-center gap-2">
      <span class="h-6 min-w-6 px-1.5 rounded-full bg-white ring-1 ring-gray-200 grid place-items-center text-[11px] font-bold text-gray-500 tabular-nums" data-num></span>
      <select class="inp !py-2 flex-1 min-w-0" data-type aria-label="What this item opens"></select>
      <div class="flex items-center gap-1 shrink-0">
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
    <p class="text-[11px] text-gray-500" data-hint></p>
    <div class="pl-3 sm:pl-5 border-l-2 border-gray-200 space-y-2" data-children-box>
      <ol class="space-y-2" data-children></ol>
      <button type="button" data-add-child class="h-8 px-3 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 text-xs font-semibold text-gray-700 inline-flex items-center gap-1 cursor-pointer">
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

    li.querySelector('[data-up]').disabled = index === 0;
    li.querySelector('[data-down]').disabled = index === list.length - 1;
    li.querySelector('[data-up]').onclick = () => { [list[index - 1], list[index]] = [list[index], list[index - 1]]; render(); };
    li.querySelector('[data-down]').onclick = () => { [list[index + 1], list[index]] = [list[index], list[index + 1]]; render(); };
    li.querySelector('[data-remove]').onclick = () => { list.splice(index, 1); render(); };

    type.onchange = () => { it.type = type.value; delete it.ref; delete it.url; if (['categories', 'brands'].includes(it.type)) { delete it.label; delete it.children; } render(); };
    cat.onchange = () => { it.ref = +cat.value || null; render(); };
    brand.onchange = () => { it.ref = +brand.value || null; render(); };
    url.oninput = () => { it.url = url.value.trim(); };
    label.oninput = () => { it.label = label.value.trim(); preview(); };

    const box = li.querySelector('[data-children-box]');
    show(box, !isChild && !fixed);
    if (!isChild && !fixed) {
      it.children = it.children || [];
      const ol = li.querySelector('[data-children]');
      it.children.forEach((child, ci) => ol.appendChild(makeRow(child, it.children, ci, true)));
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
    $('hmPreview').innerHTML = items.map((it) => {
      if (it.type === 'categories') return '<span class="px-2.5 py-1 rounded-lg opacity-60 italic">Categories… · More ' + caret + '</span>';
      if (it.type === 'brands') return '<span class="px-2.5 py-1">Brands ' + caret + '</span>';
      const drop = (it.children && it.children.length) || (it.type === 'category' && CATS.some((c) => c.name.startsWith(nameOf(it) + ' › ')));
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
    const problem = items.concat(...items.map((it) => it.children || [])).find((it) =>
      (it.type === 'category' || it.type === 'brand') && !it.ref || it.type === 'link' && (!it.url || !it.label));
    if (problem) {
      e.preventDefault();
      alert(problem.type === 'link' ? 'Each custom link needs a name and a link.' : 'Choose the ' + problem.type + ' for each ' + problem.type + ' item.');
      return;
    }
    $('hmJson').value = JSON.stringify(items);
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
