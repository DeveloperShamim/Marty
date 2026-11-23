@extends('layouts.admin')
@section('title', 'Edit landing page')
@section('subtitle', $page->title)

@section('page-actions')
  <a href="{{ route('admin.landing-pages.index') }}" class="pill-btn">All landing pages</a>
  <a href="{{ $page->url() }}" target="_blank" class="pill-btn">
    {{ $page->is_published ? 'View live' : 'Preview' }}
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@php
  $max = fn ($n, $limit) => min($limit, $n);
  $ticker = collect($d['ticker'] ?? [])->pad(\App\Models\LandingPage::MAX_TICKER, '');
  $specRows = collect($d['specs']['rows'] ?? []);
  $specRows = $specRows->pad($max($specRows->count() + 3, \App\Models\LandingPage::MAX_SPECS), ['label' => '', 'value' => '']);
  $faqItems = collect($d['faq']['items'] ?? []);
  $faqItems = $faqItems->pad($max($faqItems->count() + 2, \App\Models\LandingPage::MAX_FAQ), ['q' => '', 'a' => '']);
  $features = collect($d['features']['items'] ?? [])->pad(3, ['image' => null, 'title' => '', 'text' => '']);
  $gallery = collect($d['gallery'] ?? []);
  $reviewImages = collect($d['reviews']['images'] ?? []);
  $switch = 'relative h-6 w-10 shrink-0 rounded-full bg-gray-200 transition-colors peer-checked:bg-gray-900 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-4';
  $card = 'panel p-4 sm:p-5 space-y-4';
  $h = 'text-[15px] font-semibold text-gray-900';
@endphp

@section('content')
<form method="POST" action="{{ route('admin.landing-pages.update', $page) }}" enctype="multipart/form-data" id="lpEdit" class="grid lg:grid-cols-3 gap-4 items-start">
  @csrf @method('PUT')

  <div class="lg:col-span-2 space-y-4 min-w-0">
    @if($errors->any())
      <div class="panel p-4 text-sm text-rose-700" data-lp-admin-errors>
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
      </div>
    @endif

    {{-- 1. Product and price --}}
    <section class="{{ $card }}">
      <h2 class="{{ $h }}">1. Product and price</h2>
      <div>
        <label class="lbl" for="lpProduct">Product</label>
        <div class="flex gap-2">
          <select id="lpProduct" name="product_id" class="inp flex-1" required>
            @foreach($products as $p)
              <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ (float) $p->price }}" data-regular="{{ (float) $p->regular_price }}" @selected(old('product_id', $page->product_id) == $p->id)>{{ $p->name }} — {{ money($p->price) }}{{ $p->is_published ? '' : ' (hidden in shop)' }}</option>
            @endforeach
          </select>
          <button type="button" class="pill-btn shrink-0" id="lpSync" title="Copy the product's name and prices into this page">Copy from product</button>
        </div>
        <p class="text-[11px] text-gray-500 mt-1">Orders take stock from this product. Its sizes and colours show on the order form.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        <div class="sm:col-span-2">
          <label class="lbl" for="lpTitle">Page title</label>
          <input id="lpTitle" name="title" class="inp" value="{{ old('title', $page->title) }}" required maxlength="160">
        </div>
        <div class="sm:col-span-2">
          <label class="lbl" for="lpSlug">Link</label>
          <div class="flex items-center gap-1">
            <span class="text-xs text-gray-500 shrink-0">{{ url('/offer') }}/</span>
            <input id="lpSlug" name="slug" class="inp" value="{{ old('slug', $page->slug) }}" required maxlength="120">
          </div>
        </div>
        <div>
          <label class="lbl" for="lpRegular">Regular price (shown crossed out)</label>
          <input id="lpRegular" name="regular_price" type="number" step="1" min="0" class="inp" value="{{ old('regular_price', $page->regular_price !== null ? (float) $page->regular_price : '') }}">
        </div>
        <div>
          <label class="lbl" for="lpOffer">Offer price (what the customer pays)</label>
          <input id="lpOffer" name="offer_price" type="number" step="1" min="0" class="inp" value="{{ old('offer_price', $page->offer_price !== null ? (float) $page->offer_price : '') }}">
        </div>
        <div>
          <label class="lbl" for="lpDiscText">Discount text</label>
          <input id="lpDiscText" name="discount_text" class="inp" value="{{ old('discount_text', $page->discount_text) }}" maxlength="120" placeholder="Empty: “৳500 ছাড়” worked out for you">
        </div>
        <div>
          <label class="lbl" for="lpDiscBadge">Discount badge on the photo</label>
          <input id="lpDiscBadge" name="discount_badge" class="inp" value="{{ old('discount_badge', $page->discount_badge) }}" maxlength="40" placeholder="Empty: “-20%” worked out for you">
        </div>
      </div>
    </section>

    {{-- 2. Ticker --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">2. Top announcement bar</h2>
        <p class="text-xs text-gray-500 mt-0.5">Short lines that scroll across the top. Empty lines are skipped.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-2">
        @foreach($ticker as $i => $line)
          <input name="ticker[]" class="inp" value="{{ old("ticker.$i", $line) }}" maxlength="160" placeholder="Line {{ $i + 1 }}">
        @endforeach
      </div>
    </section>

    {{-- 3. Headline --}}
    <section class="{{ $card }}">
      <h2 class="{{ $h }}">3. Headline</h2>
      <div>
        <label class="lbl" for="lpBadge">Small badge above the headline</label>
        <input id="lpBadge" name="hero[badge]" class="inp" value="{{ old('hero.badge', $d['hero']['badge']) }}" maxlength="200">
      </div>
      <div class="grid sm:grid-cols-3 gap-3">
        <div><label class="lbl">Headline part 1</label><input name="hero[part1]" class="inp" data-lp-head value="{{ old('hero.part1', $d['hero']['part1']) }}" maxlength="200"></div>
        <div><label class="lbl">Part 2 (accent colour)</label><input name="hero[part2]" class="inp" data-lp-head value="{{ old('hero.part2', $d['hero']['part2']) }}" maxlength="200"></div>
        <div><label class="lbl">Part 3</label><input name="hero[part3]" class="inp" data-lp-head value="{{ old('hero.part3', $d['hero']['part3']) }}" maxlength="200"></div>
      </div>
      <div class="rounded-xl bg-gray-50 p-4 text-center">
        <p class="text-[11px] text-gray-500">Preview</p>
        <p class="mt-1 text-lg font-bold text-gray-900" id="lpHeadPreview"></p>
      </div>
      <div>
        <label class="lbl" for="lpButton">Order button text</label>
        <input id="lpButton" name="hero[button]" class="inp" value="{{ old('hero.button', $d['hero']['button']) }}" maxlength="60">
      </div>
    </section>

    {{-- 4. Photos --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">4. Photos</h2>
        <p class="text-xs text-gray-500 mt-0.5">Up to {{ \App\Models\LandingPage::MAX_GALLERY }} photos. With none here, the page shows the product's own photos.</p>
      </div>
      @if($gallery->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3" data-lp-gallery>
          @foreach($gallery as $i => $path)
            <div class="rounded-xl ring-1 ring-gray-200 p-2 space-y-1.5">
              <img src="{{ image_url($path) }}" alt="" class="aspect-square w-full rounded-lg object-cover">
              <label class="flex items-center gap-1.5 text-[11px]"><input type="radio" name="gallery_main" value="{{ $path }}" @checked($i === 0)> Main</label>
              <label class="flex items-center gap-1.5 text-[11px] text-rose-700"><input type="checkbox" name="gallery_remove[]" value="{{ $path }}"> Remove</label>
            </div>
          @endforeach
        </div>
      @elseif($page->product && $page->product->images->isNotEmpty())
        <div class="flex gap-2 opacity-70">
          @foreach($page->product->images->take(5) as $img)
            <img src="{{ $img->url() }}" alt="" class="h-14 w-14 rounded-lg object-cover">
          @endforeach
          <span class="text-[11px] text-gray-500 self-center">Product photos (used now)</span>
        </div>
      @endif
      @if($gallery->count() < \App\Models\LandingPage::MAX_GALLERY)
        <input type="file" name="gallery_upload[]" accept="image/*" multiple class="block text-sm">
      @endif
    </section>

    {{-- 5. Video --}}
    <section class="{{ $card }}">
      <h2 class="{{ $h }}">5. Video</h2>
      <div class="grid sm:grid-cols-2 gap-3">
        <div><label class="lbl">Heading</label><input name="video[heading]" class="inp" value="{{ old('video.heading', $d['video']['heading']) }}" maxlength="200"></div>
        <div><label class="lbl">YouTube link</label><input name="video[url]" class="inp" value="{{ old('video.url', $d['video']['url']) }}" maxlength="300" placeholder="https://youtu.be/…"></div>
      </div>
    </section>

    {{-- 6. Feature blocks --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">6. Three feature blocks</h2>
        <p class="text-xs text-gray-500 mt-0.5">Square picture, title and a few lines each. A block with no picture uses the matching gallery photo.</p>
      </div>
      <div><label class="lbl">Section heading</label><input name="features[heading]" class="inp" value="{{ old('features.heading', $d['features']['heading']) }}" maxlength="200"></div>
      @foreach($features as $i => $f)
        <div class="rounded-xl ring-1 ring-gray-200 p-3 grid sm:grid-cols-[96px_1fr] gap-3">
          <div class="space-y-1.5">
            @if($f['image'])
              <img src="{{ image_url($f['image']) }}" alt="" class="h-24 w-24 rounded-lg object-cover">
              <label class="flex items-center gap-1.5 text-[11px] text-rose-700"><input type="checkbox" name="feature_image_remove[{{ $i }}]" value="1"> Remove</label>
            @else
              <div class="h-24 w-24 rounded-lg bg-gray-100 flex items-center justify-center text-[11px] text-gray-400">No picture</div>
            @endif
            <input type="file" name="feature_image[{{ $i }}]" accept="image/*" class="block w-24 text-[11px]">
          </div>
          <div class="space-y-2">
            <input name="features[items][{{ $i }}][title]" class="inp" value="{{ old("features.items.$i.title", $f['title']) }}" maxlength="120" placeholder="Title {{ $i + 1 }}">
            <textarea name="features[items][{{ $i }}][text]" class="inp" rows="2" maxlength="600" placeholder="Description">{{ old("features.items.$i.text", $f['text']) }}</textarea>
          </div>
        </div>
      @endforeach
    </section>

    {{-- 7. Specs --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">7. Specifications table</h2>
        <p class="text-xs text-gray-500 mt-0.5">With no rows here, the product's own specifications show.</p>
      </div>
      <div><label class="lbl">Heading</label><input name="specs[heading]" class="inp" value="{{ old('specs.heading', $d['specs']['heading']) }}" maxlength="200"></div>
      <div class="space-y-2">
        @foreach($specRows as $i => $row)
          <div class="grid grid-cols-[2fr_3fr] gap-2">
            <input name="specs[rows][{{ $i }}][label]" class="inp" value="{{ old("specs.rows.$i.label", $row['label']) }}" maxlength="160" placeholder="e.g. কাপড়">
            <input name="specs[rows][{{ $i }}][value]" class="inp" value="{{ old("specs.rows.$i.value", $row['value']) }}" maxlength="300" placeholder="e.g. ১০০% কটন">
          </div>
        @endforeach
      </div>
    </section>

    {{-- 8. Review pictures --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">8. Customer review pictures</h2>
        <p class="text-xs text-gray-500 mt-0.5">Screenshots of happy customers' messages or photos, up to {{ \App\Models\LandingPage::MAX_REVIEW_IMAGES }}.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        <div><label class="lbl">Heading</label><input name="reviews[heading]" class="inp" value="{{ old('reviews.heading', $d['reviews']['heading']) }}" maxlength="200"></div>
        <div><label class="lbl">Subtitle</label><input name="reviews[subtitle]" class="inp" value="{{ old('reviews.subtitle', $d['reviews']['subtitle']) }}" maxlength="200"></div>
      </div>
      @if($reviewImages->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
          @foreach($reviewImages as $path)
            <div class="space-y-1">
              <img src="{{ image_url($path) }}" alt="" class="aspect-square w-full rounded-lg object-cover ring-1 ring-gray-200">
              <label class="flex items-center gap-1.5 text-[11px] text-rose-700"><input type="checkbox" name="review_remove[]" value="{{ $path }}"> Remove</label>
            </div>
          @endforeach
        </div>
      @endif
      @if($reviewImages->count() < \App\Models\LandingPage::MAX_REVIEW_IMAGES)
        <input type="file" name="review_upload[]" accept="image/*" multiple class="block text-sm">
      @endif
    </section>

    {{-- 9. FAQ --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">9. Questions and answers</h2>
        <p class="text-xs text-gray-500 mt-0.5">Questions left empty are skipped.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        <div><label class="lbl">Heading</label><input name="faq[heading]" class="inp" value="{{ old('faq.heading', $d['faq']['heading']) }}" maxlength="200"></div>
        <div><label class="lbl">Subtitle</label><input name="faq[subtitle]" class="inp" value="{{ old('faq.subtitle', $d['faq']['subtitle']) }}" maxlength="200"></div>
      </div>
      @foreach($faqItems as $i => $f)
        <div class="rounded-xl ring-1 ring-gray-200 p-3 space-y-2">
          <input name="faq[items][{{ $i }}][q]" class="inp" value="{{ old("faq.items.$i.q", $f['q']) }}" maxlength="200" placeholder="Question {{ $i + 1 }}">
          <textarea name="faq[items][{{ $i }}][a]" class="inp" rows="2" maxlength="1000" placeholder="Answer">{{ old("faq.items.$i.a", $f['a']) }}</textarea>
        </div>
      @endforeach
    </section>

    {{-- 10. Contact --}}
    <section class="{{ $card }}">
      <h2 class="{{ $h }}">10. Call and WhatsApp box</h2>
      <div><label class="lbl">Heading</label><input name="contact[heading]" class="inp" value="{{ old('contact.heading', $d['contact']['heading']) }}" maxlength="200"></div>
      <div class="grid sm:grid-cols-3 gap-3">
        <div><label class="lbl">Phone</label><input name="contact[phone]" class="inp" value="{{ old('contact.phone', $d['contact']['phone']) }}" maxlength="40"></div>
        <div><label class="lbl">WhatsApp number</label><input name="contact[whatsapp]" class="inp" value="{{ old('contact.whatsapp', $d['contact']['whatsapp']) }}" maxlength="40"></div>
        <div><label class="lbl">Messenger (page name or link)</label><input name="contact[messenger]" class="inp" value="{{ old('contact.messenger', $d['contact']['messenger']) }}" maxlength="200"></div>
      </div>
    </section>

    {{-- 11. Order form texts --}}
    <section class="{{ $card }}">
      <div>
        <h2 class="{{ $h }}">11. Order form texts</h2>
        <p class="text-xs text-gray-500 mt-0.5">Orders are cash on delivery, with your normal Inside / Outside Dhaka delivery charges and free-delivery offers.</p>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        @foreach([
          'heading' => 'Form heading', 'subheading' => 'Line under the heading', 'delivery_heading' => 'Delivery box heading',
          'summary_heading' => 'Order box heading', 'subtotal_label' => 'Subtotal label', 'delivery_label' => 'Delivery charge label',
          'total_label' => 'Total label', 'button' => 'Order button',
        ] as $key => $label)
          <div><label class="lbl">{{ $label }}</label><input name="form[{{ $key }}]" class="inp" value="{{ old("form.$key", $d['form'][$key]) }}" maxlength="200"></div>
        @endforeach
      </div>
    </section>
  </div>

  {{-- Right side: status, sections, look --}}
  <aside class="space-y-4 lg:sticky lg:top-4">
    <section class="{{ $card }}" data-lp-status>
      <div>
        <h2 class="{{ $h }}">Status</h2>
        <p class="mt-1 text-xs">
          @if($page->is_published)
            <span class="px-2 py-0.5 font-semibold rounded-full bg-emerald-50 text-emerald-700">Published</span> Live for customers.
          @else
            <span class="px-2 py-0.5 font-semibold rounded-full bg-gray-100 text-gray-600">Draft</span> Only staff can open it.
          @endif
        </p>
        <p class="mt-2 text-xs text-gray-500">{{ number_format($page->views) }} views · {{ $orders }} {{ \Illuminate\Support\Str::plural('order', $orders) }} · {{ $page->conversionRate($orders) }}% conversion</p>
      </div>
      <div class="flex flex-col gap-2">
        @if($page->is_published)
          <button name="action" value="save" class="pill-btn pill-btn-dark justify-center">Save changes</button>
          <button name="action" value="unpublish" class="pill-btn justify-center">Unpublish (move to draft)</button>
        @else
          <button name="action" value="publish" class="pill-btn pill-btn-dark justify-center">Save and publish</button>
          <button name="action" value="save" class="pill-btn justify-center">Save draft</button>
        @endif
      </div>
    </section>

    <section class="{{ $card }}">
      <h2 class="{{ $h }}">Sections</h2>
      @foreach(\App\Models\LandingPage::SECTIONS as $key => $label)
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="hidden" name="sections[{{ $key }}]" value="0">
          <input type="checkbox" name="sections[{{ $key }}]" value="1" class="peer sr-only" @checked(old("sections.$key", $d['sections'][$key] ?? true))>
          <span class="{{ $switch }}"></span>
          <span class="text-[13px] text-gray-800">{{ $label }}</span>
        </label>
      @endforeach
      <p class="text-[11px] text-gray-500">The photos, price and order form always show.</p>
    </section>

    <section class="{{ $card }}">
      <h2 class="{{ $h }}">Look and search</h2>
      <div>
        <label class="lbl" for="lpAccent">Accent colour</label>
        <div class="flex items-center gap-2">
          <input type="color" id="lpAccentPick" value="{{ $page->accent() }}" class="h-9 w-12 rounded border border-gray-200">
          <input id="lpAccent" name="accent_color" class="inp" value="{{ old('accent_color', $page->accent_color ?: $page->accent()) }}" maxlength="7">
        </div>
      </div>
      <div>
        <label class="lbl" for="lpMetaTitle">Search / share title</label>
        <input id="lpMetaTitle" name="meta_title" class="inp" value="{{ old('meta_title', $page->meta_title) }}" maxlength="160" placeholder="Empty: page title">
      </div>
      <div>
        <label class="lbl" for="lpMetaDesc">Search / share description</label>
        <textarea id="lpMetaDesc" name="meta_description" class="inp" rows="3" maxlength="300">{{ old('meta_description', $page->meta_description) }}</textarea>
      </div>
    </section>
  </aside>
</form>

<form method="POST" action="{{ route('admin.landing-pages.destroy', $page) }}" class="mt-4 max-w-md" onsubmit="return confirm('Delete this landing page? Its orders stay, but the link stops working.')">
  @csrf @method('DELETE')
  <button class="text-xs text-rose-700 underline cursor-pointer">Delete this landing page</button>
</form>

<script>
(function () {
  var parts = document.querySelectorAll('[data-lp-head]');
  var preview = document.getElementById('lpHeadPreview');
  var accent = document.getElementById('lpAccent');
  var pick = document.getElementById('lpAccentPick');
  function draw() {
    preview.textContent = '';
    parts.forEach(function (p, i) {
      if (!p.value.trim()) return;
      var s = document.createElement('span');
      s.textContent = p.value.trim() + ' ';
      if (i === 1) s.style.color = accent.value;
      preview.appendChild(s);
    });
  }
  parts.forEach(function (p) { p.addEventListener('input', draw); });
  pick.addEventListener('input', function () { accent.value = pick.value; draw(); });
  accent.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(accent.value)) pick.value = accent.value; draw(); });
  draw();

  document.getElementById('lpSync').addEventListener('click', function () {
    var o = document.getElementById('lpProduct').selectedOptions[0];
    if (!o) return;
    document.getElementById('lpTitle').value = o.dataset.name;
    document.getElementById('lpOffer').value = Math.round(+o.dataset.price);
    var reg = Math.round(+o.dataset.regular);
    document.getElementById('lpRegular').value = reg > +o.dataset.price ? reg : '';
    parts[1].value = o.dataset.name;
    draw();
  });
})();
</script>
@endsection
