@extends('layouts.admin')
@php
  $editing = $block->exists;
  $type = $block->type;
  $d = fn ($k, $def = null) => old($k, $block->get($k, $def));
  $file = 'w-full text-xs text-gray-500 file:mr-3 file:h-8 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200 cursor-pointer rounded-xl border border-gray-200 p-1';
@endphp
@section('title', ($editing ? 'Edit ' : 'New ') . strtolower($block->typeName()))
@section('subtitle', \App\Models\HomeBlock::TYPES[$type][1])

@section('page-actions')
  <a href="{{ route('admin.home-sections.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="hbForm" class="pill-btn pill-btn-dark">{{ $editing ? 'Save section' : 'Add to homepage' }}</button>
@endsection

@section('content')
<div class="max-w-3xl space-y-4">
  <form method="POST" enctype="multipart/form-data" id="hbForm" data-home-block-form="{{ $type }}"
        action="{{ $editing ? route('admin.home-blocks.update', $block) : route('admin.home-blocks.store') }}" class="space-y-4">
    @csrf
    @if($editing) @method('PUT') @else <input type="hidden" name="type" value="{{ $type }}"> @endif

    @if($errors->any())
      <div class="rounded-xl bg-rose-50 text-rose-700 text-xs px-3.5 py-2.5">{{ $errors->first() }}</div>
    @endif

    <section class="panel p-4 sm:p-5 space-y-3.5">
      <h2 class="text-[15px] font-semibold text-gray-900">{{ $type === 'image_banner' ? 'Name' : 'Heading' }}</h2>
      <div>
        <label class="lbl" for="hbTitle">{{ match($type) { 'promo_band' => 'Headline', 'image_banner' => 'Name (also the picture’s alt text)', default => 'Section title' } }}</label>
        <input id="hbTitle" name="title" class="inp" maxlength="120" value="{{ old('title', $block->title) }}"
               placeholder="{{ match($type) { 'products' => 'e.g. Winter collection', 'promo_band' => 'e.g. Free delivery over ৳1,500', 'card_grid' => 'e.g. Shop by occasion', 'stats_row' => 'e.g. Trusted across Bangladesh', default => 'e.g. Eid sale banner' } }}">
        @if($type === 'products')<p class="text-[11px] text-gray-500 mt-1">Left empty, the row takes the category’s name.</p>@endif
      </div>
      @if(in_array($type, ['promo_band', 'card_grid', 'stats_row', 'products'], true))
        <div>
          <label class="lbl" for="hbSub">{{ $type === 'promo_band' ? 'Text under the headline' : 'Line under the title (optional)' }}</label>
          <input id="hbSub" name="subtitle" class="inp" maxlength="300" value="{{ old('subtitle', $block->subtitle) }}">
        </div>
      @endif
    </section>

    @if($type === 'products')
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <h2 class="text-[15px] font-semibold text-gray-900">Which products</h2>
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="lbl" for="hbCat">From</label>
            <select id="hbCat" name="category_id" class="inp">
              <option value="">All products</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected((string) $d('category_id') === (string) $c->id)>{{ $c->parent_id ? '— ' : '' }}{{ $c->name }}</option>
              @endforeach
            </select>
            <p class="text-[11px] text-gray-500 mt-1">A main category includes its sub-categories.</p>
          </div>
          <div>
            <label class="lbl" for="hbSort">Order</label>
            <select id="hbSort" name="sort" class="inp">
              @foreach(\App\Models\HomeBlock::SORTS as $k => $label)
                <option value="{{ $k }}" @selected($d('sort', 'latest') === $k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="lbl" for="hbLimit">How many products</label>
            <input id="hbLimit" type="number" name="limit" min="2" max="24" class="inp" value="{{ $d('limit', 8) }}">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="lbl" for="hbLinkLabel">“See all” button text</label>
            <input id="hbLinkLabel" name="link_label" class="inp" maxlength="40" value="{{ $d('link_label') }}" placeholder="See all">
          </div>
          <div>
            <label class="lbl" for="hbLink">“See all” goes to</label>
            <input id="hbLink" name="link_url" class="inp" value="{{ $d('link_url') }}" placeholder="Left empty: the category page">
          </div>
        </div>
      </section>
    @endif

    @if($type === 'promo_band')
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <h2 class="text-[15px] font-semibold text-gray-900">Button and colours</h2>
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="lbl" for="hbBtn">Button text</label>
            <input id="hbBtn" name="button_text" class="inp" maxlength="40" value="{{ $d('button_text') }}" placeholder="Shop now">
            <p class="text-[11px] text-gray-500 mt-1">Leave empty for no button.</p>
          </div>
          <div>
            <label class="lbl" for="hbLink">Button goes to</label>
            <input id="hbLink" name="link_url" class="inp" value="{{ $d('link_url') }}" placeholder="/shop or /category/… or https://…">
          </div>
          <div>
            <label class="lbl" for="hbBg">Background</label>
            <input id="hbBg" type="color" name="bg" class="h-10 w-full rounded-xl border border-gray-200 p-1 cursor-pointer" value="{{ $d('bg', '#111827') }}">
          </div>
          <div>
            <label class="lbl" for="hbFg">Text colour</label>
            <input id="hbFg" type="color" name="fg" class="h-10 w-full rounded-xl border border-gray-200 p-1 cursor-pointer" value="{{ $d('fg', '#ffffff') }}">
          </div>
        </div>
      </section>
    @endif

    @if($type === 'image_banner')
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <h2 class="text-[15px] font-semibold text-gray-900">Pictures</h2>
        @foreach([['image', 'image_file', 'remove_image', 'Desktop picture', 'Best 1920 × 480 px (4:1).'], ['image_mobile', 'image_mobile_file', 'remove_image_mobile', 'Phone picture (optional)', 'Best 800 × 500 px. Left empty, phones show the desktop picture.']] as [$key, $field, $remove, $label, $hint])
          <div>
            <span class="lbl">{{ $label }}</span>
            @if($block->get($key))
              <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-xl mb-2">
                <img src="{{ image_url($block->get($key)) }}" alt="" class="h-14 w-28 rounded-lg object-cover bg-gray-100">
                <label class="inline-flex items-center gap-1.5 text-xs text-rose-700 cursor-pointer">
                  <input type="checkbox" name="{{ $remove }}" value="1" class="accent-red-600"> Remove
                </label>
              </div>
            @endif
            <input type="file" name="{{ $field }}" accept="image/*" class="{{ $file }}">
            <p class="text-[11px] text-gray-500 mt-1">{{ $hint }} JPG, PNG or WebP up to 4MB.</p>
          </div>
        @endforeach
        <div>
          <label class="lbl" for="hbLink">Picture links to</label>
          <input id="hbLink" name="link_url" class="inp" value="{{ $d('link_url') }}" placeholder="/shop or /category/… or https://…">
        </div>
      </section>
    @endif

    @if($type === 'card_grid')
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <div class="flex items-end justify-between gap-3">
          <div>
            <h2 class="text-[15px] font-semibold text-gray-900">Cards</h2>
            <p class="text-xs text-gray-500 mt-0.5">Up to {{ \App\Models\HomeBlock::MAX_CARDS }}. A card needs a title or a picture; empty ones are left out.</p>
          </div>
          <div class="w-36 shrink-0">
            <label class="lbl" for="hbCols">Across on desktop</label>
            <select id="hbCols" name="columns" class="inp">
              @foreach([2, 3, 4] as $n)<option value="{{ $n }}" @selected((int) $d('columns', 3) === $n)>{{ $n }} cards</option>@endforeach
            </select>
          </div>
        </div>
        @php $cards = $block->cards(); @endphp
        @for($i = 0; $i < \App\Models\HomeBlock::MAX_CARDS; $i++)
          @php $c = $cards[$i] ?? []; @endphp
          <div class="rounded-2xl ring-1 ring-gray-200 p-3 grid sm:grid-cols-[7rem_1fr] gap-3 {{ $i > count($cards) ? 'hidden' : '' }}" data-hb-card="{{ $i }}">
            <div>
              @if(! empty($c['image']))
                <img src="{{ image_url($c['image']) }}" alt="" class="w-full aspect-[4/3] rounded-lg object-cover bg-gray-100">
                <label class="mt-1 inline-flex items-center gap-1 text-[11px] text-rose-700 cursor-pointer"><input type="checkbox" name="cards[{{ $i }}][remove_image]" value="1" class="accent-red-600"> Remove</label>
              @else
                <div class="w-full aspect-[4/3] rounded-lg bg-gray-50 grid place-items-center text-[11px] text-gray-400">Card {{ $i + 1 }}</div>
              @endif
            </div>
            <div class="space-y-2 min-w-0">
              <input name="cards[{{ $i }}][title]" class="inp" maxlength="80" placeholder="Title" value="{{ old("cards.$i.title", $c['title'] ?? '') }}">
              <input name="cards[{{ $i }}][text]" class="inp" maxlength="200" placeholder="Short text (optional)" value="{{ old("cards.$i.text", $c['text'] ?? '') }}">
              <input name="cards[{{ $i }}][link]" class="inp" placeholder="Link, e.g. /category/shirts" value="{{ old("cards.$i.link", $c['link'] ?? '') }}">
              <input type="file" name="cards[{{ $i }}][image_file]" accept="image/*" class="{{ $file }}">
            </div>
          </div>
        @endfor
        <button type="button" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium" data-hb-more="card">+ Add a card</button>
      </section>
    @endif

    @if($type === 'stats_row')
      <section class="panel p-4 sm:p-5 space-y-3">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Numbers</h2>
          <p class="text-xs text-gray-500 mt-0.5">Up to {{ \App\Models\HomeBlock::MAX_STATS }}. Rows without a number are left out.</p>
        </div>
        @php $stats = old('stats', $block->stats()); @endphp
        @for($i = 0; $i < \App\Models\HomeBlock::MAX_STATS; $i++)
          <div class="grid grid-cols-[8rem_1fr] gap-2 {{ $i > count($stats) ? 'hidden' : '' }}" data-hb-stat="{{ $i }}">
            <input name="stats[{{ $i }}][value]" class="inp" maxlength="20" placeholder="10,000+" value="{{ $stats[$i]['value'] ?? '' }}">
            <input name="stats[{{ $i }}][label]" class="inp" maxlength="60" placeholder="Happy customers" value="{{ $stats[$i]['label'] ?? '' }}">
          </div>
        @endfor
        <button type="button" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium" data-hb-more="stat">+ Add a number</button>
      </section>
    @endif

    <div class="flex items-center justify-between gap-2">
      @if($editing)
        <button type="submit" form="hbDelete" class="h-9 px-3.5 rounded-full text-[13px] font-medium text-rose-700 hover:bg-rose-50">Delete section</button>
      @else
        <span></span>
      @endif
      <button type="submit" class="h-10 px-5 rounded-full text-[13px] font-semibold text-white" style="background: var(--brand-dark);">{{ $editing ? 'Save section' : 'Add to homepage' }}</button>
    </div>
  </form>

  @if($editing)
    <form method="POST" action="{{ route('admin.home-blocks.destroy', $block) }}" id="hbDelete" onsubmit="return confirm('Delete this section from the homepage?')">
      @csrf @method('DELETE')
    </form>
  @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-hb-more]');
  if (!btn) return;
  var next = document.querySelector('[data-hb-' + btn.dataset.hbMore + '].hidden');
  if (next) next.classList.remove('hidden');
  if (!document.querySelector('[data-hb-' + btn.dataset.hbMore + '].hidden')) btn.classList.add('hidden');
});
</script>
@endpush
