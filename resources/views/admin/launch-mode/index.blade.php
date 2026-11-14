@extends('layouts.admin')
@section('title', 'Launch mode')
@section('subtitle', 'A homepage for a small first collection. Switch it off any time to get the normal homepage back.')

@section('page-actions')
  <a href="{{ route('home') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@php
  $c = $config;
  $pickedIds = collect($c['product_ids'] ?? [])->values();
  $max = \App\Support\LaunchMode::MAX_PRODUCTS;
  $rows = collect($c['compare']['rows'] ?? []);
  // Always offer at least one empty row to add a new comparison line
  $rowSlots = $rows->count() < \App\Support\LaunchMode::MAX_COMPARE_ROWS ? $rows->push(['label' => '', 'values' => []]) : $rows;
  $pillars = collect($c['material']['pillars'] ?? [])->pad(3, ['title' => '', 'text' => '', 'image' => null]);
  $trustItems = collect($c['trust']['items'] ?? [])->pad(3, ['icon' => 'shield', 'title' => '', 'text' => '']);
  $icons = ['cash' => 'Cash', 'bolt' => 'Fast', 'shield' => 'Warranty', 'truck' => 'Delivery', 'gift' => 'Gift', 'star' => 'Star'];
  $switch = 'relative h-6 w-10 shrink-0 rounded-full bg-gray-200 transition-colors peer-checked:bg-gray-900 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-4';
@endphp

@section('content')
<form method="POST" action="{{ route('admin.launch-mode.update') }}" class="space-y-4 max-w-4xl" id="lmForm" enctype="multipart/form-data">
  @csrf @method('PUT')

  @if($errors->any())
    <div class="panel p-4 text-sm text-rose-700">{{ $errors->first() }}</div>
  @endif

  {{-- The switch --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <label class="flex items-start gap-3 cursor-pointer">
      <input type="hidden" name="launch_mode" value="0">
      <input type="checkbox" name="launch_mode" value="1" class="peer sr-only" @checked($on)>
      <span class="{{ $switch }} mt-0.5"></span>
      <span class="min-w-0">
        <span class="block text-[15px] font-semibold text-gray-900">Launch mode {{ $on ? 'is on' : 'is off' }}</span>
        <span class="block text-xs text-gray-500 mt-0.5">On: the homepage shows a big photo of your first product, your collection, the material story, the comparison table and the trust row. Category rows, brands, flash sale, best sellers, "Just for you" and coupons are hidden so the store doesn't look empty. Off: your normal homepage comes back exactly as it was. Nothing in your products or other settings changes either way.</span>
      </span>
    </label>


    <label class="flex items-start gap-3 cursor-pointer">
      <input type="hidden" name="show_reviews" value="0">
      <input type="checkbox" name="show_reviews" value="1" class="peer sr-only" @checked($c['show_reviews'] ?? true)>
      <span class="{{ $switch }} mt-0.5"></span>
      <span class="min-w-0">
        <span class="block text-[13px] font-medium text-gray-900">Show customer reviews</span>
        <span class="block text-[11px] text-gray-500 mt-0.5">Approved reviews show under the collection once you have some. With none, nothing shows.</span>
      </span>
    </label>

  </section>

  {{-- Hero --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Top of the page</h2>
      <p class="text-xs text-gray-500 mt-0.5">The big photo is your hero picture below, or else the first product in your collection. Its colour dots switch to that colour's picture.</p>
    </div>
    <label class="flex items-start gap-3 cursor-pointer rounded-2xl ring-1 ring-gray-200 p-3">
      <input type="hidden" name="hero[rotate]" value="0">
      <input type="checkbox" name="hero[rotate]" value="1" class="peer sr-only" @checked($c['hero']['rotate'] ?? true)>
      <span class="{{ $switch }} mt-0.5"></span>
      <span class="min-w-0">
        <span class="block text-[13px] font-medium text-gray-900">Rotating product card</span>
        <span class="block text-[11px] text-gray-500 mt-0.5">With 2 or more products in your collection, the photo becomes one product card that changes every few seconds: photo, name, price, View product and Buy now. Customers can also swipe it or tap the dots. It replaces the hero picture and colour dots below; with only 1 product, or with this off, they show as before.</span>
      </span>
    </label>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl" for="lmHeroBadge">Small label</label>
        <input id="lmHeroBadge" class="inp" name="hero[badge]" value="{{ old('hero.badge', $c['hero']['badge'] ?? '') }}" maxlength="300">
      </div>
      <div>
        <label class="lbl" for="lmHeroTitle">Headline</label>
        <input id="lmHeroTitle" class="inp" name="hero[title]" value="{{ old('hero.title', $c['hero']['title'] ?? '') }}" maxlength="300">
      </div>
    </div>
    <div>
      <label class="lbl" for="lmHeroText">Text</label>
      <textarea id="lmHeroText" class="inp" rows="2" name="hero[text]" maxlength="600">{{ old('hero.text', $c['hero']['text'] ?? '') }}</textarea>
    </div>
    <div class="sm:w-1/2">
      <label class="lbl" for="lmHeroBtn">Button</label>
      <input id="lmHeroBtn" class="inp" name="hero[button]" value="{{ old('hero.button', $c['hero']['button'] ?? 'Shop now') }}" maxlength="40">
    </div>

    @php
      $heroImg = $c['hero']['image'] ?? null;
      $heroColorImgs = $c['hero']['color_images'] ?? [];
      $leadColors = $picked->first() ? (\App\Support\ProductCardOptions::for($picked->first())['colors'] ?? []) : [];
    @endphp
    <div class="rounded-2xl ring-1 ring-gray-200 p-3 space-y-3">
      <div class="flex items-center gap-3">
        <span class="h-16 w-16 shrink-0 rounded-xl bg-gray-100 overflow-hidden grid place-items-center text-[10px] text-gray-400 text-center">
          @if($heroImg)<img src="{{ image_url($heroImg) }}" alt="" class="h-full w-full object-cover">@else Product photo @endif
        </span>
        <span class="min-w-0 flex-1">
          <label class="lbl" for="lmHeroImg">Hero picture <span class="font-normal text-gray-400">(optional)</span></label>
          <input id="lmHeroImg" type="file" name="hero_image" accept="image/*" class="block w-full text-xs text-gray-600">
          <span class="block text-[11px] text-gray-500 mt-1">Square (1:1) fits the card exactly, e.g. 1200 × 1200. Without one, the first product's photo shows. Up to 4 MB.</span>
          @if($heroImg)<label class="mt-1 inline-flex items-center gap-1.5 text-[11px] text-gray-600"><input type="checkbox" name="hero_image_remove" value="1"> Remove</label>@endif
        </span>
      </div>
      @if($leadColors)
        <div>
          <p class="text-[11px] text-gray-500 mb-2">A picture for each colour (optional). It shows when a customer taps that colour's dot.</p>
          <div class="grid sm:grid-cols-2 gap-2">
            @foreach($leadColors as $col)
              @php $ck = mb_strtolower(trim($col['name'])); $cimg = $heroColorImgs[$ck] ?? null; @endphp
              <div class="flex items-center gap-2.5 rounded-xl bg-gray-50 p-2">
                <span class="h-10 w-10 shrink-0 rounded-lg overflow-hidden ring-1 ring-gray-200" @if($col['swatch']) style="background: {{ $col['swatch'] }}" @endif>
                  @if($cimg)<img src="{{ image_url($cimg) }}" alt="" class="h-full w-full object-cover">@endif
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block text-[12px] font-medium text-gray-800">{{ $col['name'] }}</span>
                  <input type="file" name="hero_color_image[{{ $ck }}]" accept="image/*" class="block w-full text-[11px] text-gray-600" aria-label="Picture for {{ $col['name'] }}">
                  @if($cimg)<label class="inline-flex items-center gap-1.5 text-[11px] text-gray-600"><input type="checkbox" name="hero_color_image_remove[{{ $ck }}]" value="1"> Remove</label>@endif
                </span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  {{-- Collection --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Collection</h2>
      <p class="text-xs text-gray-500 mt-0.5">Change the label for your next drop, for example "Batch 02 — Limited Release".</p>
    </div>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl" for="lmLabel">Small label</label>
        <input id="lmLabel" class="inp" name="collection[label]" value="{{ old('collection.label', $c['collection']['label']) }}" maxlength="300">
      </div>
      <div>
        <label class="lbl" for="lmHeading">Heading</label>
        <input id="lmHeading" class="inp" name="collection[heading]" value="{{ old('collection.heading', $c['collection']['heading']) }}" maxlength="300">
      </div>
    </div>
    <div>
      <label class="lbl" for="lmIntro">Short text <span class="font-normal text-gray-400">(optional)</span></label>
      <textarea id="lmIntro" class="inp" rows="2" name="collection[intro]" maxlength="600">{{ old('collection.intro', $c['collection']['intro']) }}</textarea>
    </div>

    <div>
      <p class="lbl">Products, in order <span class="font-normal text-gray-400">(1 to {{ $max }})</span></p>
      <p class="text-[11px] text-gray-500 mb-2">Each wallet is a normal product. Its colours come from the product's colour options, so customers pick a colour when they order. 1 to 2 products show large, 3 show 3 across, 4 or more show 4 across.</p>
      <div class="grid sm:grid-cols-2 gap-2">
        @for($i = 0; $i < $max; $i++)
          <div class="flex items-center gap-2">
            <span class="w-6 text-[11px] font-semibold text-gray-400 tabular-nums shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
            <select class="inp" name="product_ids[]" aria-label="Product {{ $i + 1 }}">
              <option value="">Empty</option>
              @foreach($allProducts as $p)
                <option value="{{ $p->id }}" @selected((int) ($pickedIds[$i] ?? 0) === $p->id)>{{ $p->name }}{{ $p->is_published ? '' : ' (hidden)' }}{{ $p->stock_quantity > 0 ? '' : ' (out of stock)' }}</option>
              @endforeach
            </select>
          </div>
        @endfor
      </div>
      @if($pickedIds->isNotEmpty() && $picked->count() < $pickedIds->count())
        <p class="text-[11px] text-amber-700 mt-2">A picked product is hidden from the store, so it doesn't show on the homepage.</p>
      @endif
    </div>
  </section>

  {{-- Material --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <label class="flex items-center justify-between gap-3 cursor-pointer">
      <span>
        <span class="block text-[15px] font-semibold text-gray-900">Material section</span>
        <span class="block text-xs text-gray-500 mt-0.5">Three points about how the wallets are made, beside a picture. Without pictures a leather illustration shows.</span>
      </span>
      <input type="hidden" name="material[on]" value="0">
      <input type="checkbox" name="material[on]" value="1" class="peer sr-only" @checked($c['material']['on'] ?? true)>
      <span class="{{ $switch }}"></span>
    </label>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl">Small label</label>
        <input class="inp" name="material[label]" value="{{ $c['material']['label'] }}" maxlength="300">
      </div>
      <div>
        <label class="lbl">Heading</label>
        <input class="inp" name="material[heading]" value="{{ $c['material']['heading'] }}" maxlength="300">
      </div>
    </div>
    @php $matImg = $c['material']['image'] ?? null; @endphp
    <div class="rounded-2xl ring-1 ring-gray-200 p-3 flex items-center gap-3">
      <span class="h-16 w-16 shrink-0 rounded-xl bg-gray-100 overflow-hidden grid place-items-center text-[10px] text-gray-400">
        @if($matImg)<img src="{{ image_url($matImg) }}" alt="" class="h-full w-full object-cover">@else No picture @endif
      </span>
      <span class="min-w-0 flex-1">
        <label class="lbl" for="lmMatImg">Main picture</label>
        <input id="lmMatImg" type="file" name="material_image" accept="image/*" class="block w-full text-xs text-gray-600">
        <span class="block text-[11px] text-gray-500 mt-1">Square works best, e.g. a close-up of the leather or stitching. Up to 4 MB.</span>
        @if($matImg)<label class="mt-1 inline-flex items-center gap-1.5 text-[11px] text-gray-600"><input type="checkbox" name="material_image_remove" value="1"> Remove</label>@endif
      </span>
    </div>
    @foreach($pillars->take(3) as $i => $p)
      <div class="rounded-2xl ring-1 ring-gray-200 p-3 space-y-2">
        <div class="grid sm:grid-cols-[1fr_2fr] gap-2">
          <input class="inp" name="material[pillars][{{ $i }}][title]" value="{{ $p['title'] }}" placeholder="Point {{ $i + 1 }} title" maxlength="300" aria-label="Point {{ $i + 1 }} title">
          <input class="inp" name="material[pillars][{{ $i }}][text]" value="{{ $p['text'] }}" placeholder="One or two sentences" maxlength="600" aria-label="Point {{ $i + 1 }} text">
        </div>
        <div class="flex items-center gap-3">
          <span class="h-10 w-10 shrink-0 rounded-lg bg-gray-100 overflow-hidden">
            @if(! empty($p['image']))<img src="{{ image_url($p['image']) }}" alt="" class="h-full w-full object-cover">@endif
          </span>
          <span class="min-w-0 flex-1">
            <span class="block text-[11px] text-gray-500">Picture for point {{ $i + 1 }} (optional): shows when a customer taps this point.</span>
            <input type="file" name="pillar_image[{{ $i }}]" accept="image/*" class="block w-full text-xs text-gray-600 mt-0.5" aria-label="Picture for point {{ $i + 1 }}">
            @if(! empty($p['image']))<label class="mt-1 inline-flex items-center gap-1.5 text-[11px] text-gray-600"><input type="checkbox" name="pillar_image_remove[{{ $i }}]" value="1"> Remove</label>@endif
          </span>
        </div>
      </div>
    @endforeach
  </section>

  {{-- Compare --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <label class="flex items-center justify-between gap-3 cursor-pointer">
      <span>
        <span class="block text-[15px] font-semibold text-gray-900">Comparison table</span>
        <span class="block text-xs text-gray-500 mt-0.5">Your collection side by side. Colours and price fill in by themselves, with an Add button under each price. It shows when you have 2 or more products.</span>
      </span>
      <input type="hidden" name="compare[on]" value="0">
      <input type="checkbox" name="compare[on]" value="1" class="peer sr-only" @checked($c['compare']['on'] ?? true)>
      <span class="{{ $switch }}"></span>
    </label>
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl">Small label</label>
        <input class="inp" name="compare[label]" value="{{ $c['compare']['label'] }}" maxlength="300">
      </div>
      <div>
        <label class="lbl">Heading</label>
        <input class="inp" name="compare[heading]" value="{{ $c['compare']['heading'] }}" maxlength="300">
      </div>
    </div>

    @if($picked->isEmpty())
      <p class="text-[11px] text-gray-500">Pick your products above and save. Then a box for each product shows here for every line.</p>
    @endif
    <div class="space-y-3">
      @foreach($rowSlots as $ri => $row)
        <div class="rounded-2xl ring-1 ring-gray-200 p-3 space-y-2">
          <input class="inp" name="compare[rows][{{ $ri }}][label]" value="{{ $row['label'] }}" placeholder="{{ $row['label'] === '' ? 'Add a line, e.g. Weight' : '' }}" maxlength="300" aria-label="Line {{ $ri + 1 }} name">
          @if($picked->isNotEmpty())
            <div class="grid sm:grid-cols-2 gap-2">
              @foreach($picked as $p)
                <input class="inp" name="compare[rows][{{ $ri }}][values][{{ $p->id }}]" value="{{ $row['values'][$p->id] ?? '' }}" placeholder="{{ $p->name }}" maxlength="300" aria-label="{{ $p->name }}">
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
    <p class="text-[11px] text-gray-500">Clear a line's name to remove it. Up to {{ \App\Support\LaunchMode::MAX_COMPARE_ROWS }} lines.</p>
  </section>

  {{-- Trust --}}
  <section class="panel p-4 sm:p-5 flex flex-col gap-4">
    <label class="flex items-center justify-between gap-3 cursor-pointer">
      <span>
        <span class="block text-[15px] font-semibold text-gray-900">Trust row</span>
        <span class="block text-xs text-gray-500 mt-0.5">Three promises near the bottom of the page.</span>
      </span>
      <input type="hidden" name="trust[on]" value="0">
      <input type="checkbox" name="trust[on]" value="1" class="peer sr-only" @checked($c['trust']['on'] ?? true)>
      <span class="{{ $switch }}"></span>
    </label>
    @foreach($trustItems->take(3) as $i => $t)
      <div class="grid sm:grid-cols-[7rem_1fr_2fr] gap-2">
        <select class="inp" name="trust[items][{{ $i }}][icon]" aria-label="Promise {{ $i + 1 }} icon">
          @foreach($icons as $k => $label)
            <option value="{{ $k }}" @selected(($t['icon'] ?? '') === $k)>{{ $label }}</option>
          @endforeach
        </select>
        <input class="inp" name="trust[items][{{ $i }}][title]" value="{{ $t['title'] }}" placeholder="Promise {{ $i + 1 }}" maxlength="300" aria-label="Promise {{ $i + 1 }} title">
        <input class="inp" name="trust[items][{{ $i }}][text]" value="{{ $t['text'] }}" placeholder="One sentence" maxlength="600" aria-label="Promise {{ $i + 1 }} text">
      </div>
    @endforeach
  </section>

  <div class="flex justify-end pb-6">
    <button type="submit" class="h-10 px-5 rounded-full text-[13px] font-semibold text-white" style="background: var(--brand-dark);">Save launch mode</button>
  </div>
</form>
@endsection
