@extends('layouts.admin')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit product' : 'New product')
@section('subtitle', $editing ? 'Update details, photos, pricing and variants.' : 'Add a new item to your catalog.')

@section('page-actions')
  @if($editing && $product->is_published)
    <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="pill-btn">
      View on store
      <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9"/></svg></span>
    </a>
  @endif
  <a href="{{ route('admin.products.index') }}" class="pill-btn">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    All products
  </a>
@endsection

@section('content')
@php
  if ($editing) {
    $sizes = collect();
    $colors = collect();

    foreach ($product->skus as $sku) {
      foreach ($sku->getAttributesData() as $k => $v) {
        $kLower = strtolower(trim((string)$k));
        $vTrim = trim((string)$v);
        if ($vTrim === '') continue;
        if (in_array($kLower, ['size', 'weight', 'volume', 'unit'])) {
          $sizes->push($vTrim);
        } elseif (in_array($kLower, ['color', 'packaging', 'flavor', 'type', 'container'])) {
          $colors->push($vTrim);
        } elseif (preg_match('/\d+\s*(g|kg|l|ml|oz|lb|liter|litre|gm|gram)/i', $vTrim)) {
          $sizes->push($vTrim);
        } else {
          $colors->push($vTrim);
        }
      }
    }

    foreach ($product->variants as $v) {
      $kLower = strtolower(trim((string)$v->type));
      $vTrim = trim((string)$v->value);
      if ($vTrim === '') continue;
      if (in_array($kLower, ['size', 'weight', 'volume', 'unit'])) {
        $sizes->push($vTrim);
      } elseif (in_array($kLower, ['color', 'packaging', 'flavor', 'type', 'container'])) {
        $colors->push($vTrim);
      } elseif (preg_match('/\d+\s*(g|kg|l|ml|oz|lb|liter|litre|gm|gram)/i', $vTrim)) {
        $sizes->push($vTrim);
      } else {
        $colors->push($vTrim);
      }
    }

    $sizeValues = $sizes->unique()->filter()->implode(', ');
    $colorValues = $colors->unique()->filter()->implode(', ');
  } else {
    $sizeValues = '';
    $colorValues = '';
  }
@endphp

<form id="productForm" method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4 max-w-full pb-20 sm:pb-8">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Sticky action bar: one Save, one Cancel --}}
  <div class="hidden sm:flex sticky top-[64px] lg:top-[80px] z-10 bg-white/90 backdrop-blur-xl rounded-full shadow-panel pl-4 pr-1.5 py-1.5 items-center justify-between gap-3">
    <div class="flex items-center gap-2 min-w-0">
      @if($editing)
        <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $product->name }}</p>
      @else
        <p class="text-[13px] text-gray-500 truncate">Not saved yet. Press Ctrl+S or click Save when you are done.</p>
      @endif
      @if($editing)
        <span class="shrink-0 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $product->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $product->is_published ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>{{ $product->is_published ? 'Published' : 'Draft' }}
        </span>
      @endif
    </div>
    <div class="flex items-center gap-1.5 shrink-0">
      <a href="{{ route('admin.products.index') }}" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center">Cancel</a>
      <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center gap-1.5 cursor-pointer" style="background: var(--brand-dark);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        {{ $editing ? 'Save changes' : 'Save product' }}
      </button>
    </div>
  </div>

  {{-- Main Layout Grid: Full Responsive Desktop (12-Col) & Tablet/Mobile --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

    {{-- Left Column (Main Form Content - 8 cols on Desktop) --}}
    <div class="lg:col-span-8 space-y-4">

      {{-- Card 1: Basic Product Information --}}
      <section class="panel bg-white p-4 sm:p-5 space-y-5">
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3.5">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">1</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Basic information</h2>
              <p class="text-xs text-gray-500 mt-0.5">Name, category, brand and description</p>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-[13px] font-medium text-stone-700">Product Name <span class="text-rose-500">*</span></label>
              <span id="nameCharCount" class="text-[10px] font-mono text-stone-400">0 chars</span>
            </div>
            <input type="text" id="productNameInput" name="name" class="w-full px-3.5 py-2.5 sm:py-3 text-sm text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition-all" value="{{ old('name', $product->name) }}" placeholder="e.g. Nike Air Zoom Pegasus 40 Running Shoes" required />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-[13px] font-medium text-stone-700">URL Slug</label>
                <button type="button" id="autoSlugBtn" class="text-[10px] font-bold text-brand-600 hover:text-brand-800 hover:underline cursor-pointer">Auto Generate</button>
              </div>
              <input type="text" id="productSlugInput" name="slug" class="w-full px-3.5 py-2.5 text-sm font-mono text-stone-700 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('slug', $product->slug) }}" placeholder="e.g. nike-air-zoom-pegasus-40" />
            </div>

            <div>
              <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Base SKU Code</label>
              <input type="text" name="sku" class="w-full px-3.5 py-2.5 text-sm font-mono text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('sku', $product->sku) }}" placeholder="e.g. NIKE-PEG40" />
            </div>
            <div>
              <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Barcode / EAN (Optional)</label>
              <input type="text" name="barcode" class="w-full px-3.5 py-2.5 text-sm font-mono text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('barcode', $product->barcode) }}" placeholder="Auto-generated if blank" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-[13px] font-medium text-stone-700">Brand / Producer</label>
                <a href="{{ route('admin.brands.create') }}" target="_blank" class="text-[10px] font-bold text-brand-600 hover:underline">+ New Brand</a>
              </div>
              <select name="brand_id" class="w-full px-3.5 py-2.5 text-sm text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20">
                <option value="">-- Direct Brand / In-House --</option>
                @foreach($brands as $b)
                  <option value="{{ $b->id }}" @selected((int) old('brand_id', $product->brand_id) === (int) $b->id)>
                    {{ $b->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Product Category <span class="text-rose-500">*</span></label>
              <select name="category_id" class="w-full px-3.5 py-2.5 text-sm text-stone-800 rounded-xl border border-stone-200 bg-white focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" @selected((int) old('category_id', $product->category_id) === (int) $cat->id)>
                    {{ $cat->icon }} {{ $cat->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Short Tagline Summary <span class="text-stone-400 font-normal">(Shown on catalog cards)</span></label>
            <textarea name="short_description" rows="2" class="w-full px-3.5 py-2.5 text-sm font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" placeholder="e.g. Lightweight daily running shoe with responsive cushioning.">{{ old('short_description', $product->short_description) }}</textarea>
          </div>

          <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
              <label class="text-[13px] font-medium text-stone-700 flex items-center gap-1.5">
                <span>Full Detailed Description &amp; Highlights</span>
              </label>
              <div class="flex items-center gap-1 bg-stone-100 p-0.5 rounded-lg text-xs font-semibold">
                <button type="button" id="descModeVisualBtn" class="px-2.5 py-1 rounded-md bg-white text-stone-900 shadow-2xs font-bold text-[11px] transition-all cursor-pointer">Visual</button>
                <button type="button" id="descModeHtmlBtn" class="px-2.5 py-1 rounded-md text-stone-600 hover:text-stone-900 font-bold text-[11px] transition-all cursor-pointer">&lt;&gt; HTML</button>
                <button type="button" id="descModePreviewBtn" class="px-2.5 py-1 rounded-md text-stone-600 hover:text-stone-900 font-bold text-[11px] transition-all cursor-pointer">Preview</button>
              </div>
            </div>

            {{-- Rich Editor Frame --}}
            <div class="border border-stone-200 rounded-2xl overflow-hidden bg-white shadow-2xs focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all">
              {{-- Toolbar --}}
              <div id="descEditorToolbar" class="flex flex-wrap items-center gap-1 p-2 bg-stone-50/90 border-b border-stone-200 text-xs text-stone-700">
                {{-- Format / Heading select --}}
                <select id="descHeadingSelect" class="h-7 px-2 text-sm font-semibold bg-white border border-stone-200 rounded-lg focus:outline-none cursor-pointer text-stone-900">
                  <option value="p">Paragraph</option>
                  <option value="h2">Heading 2</option>
                  <option value="h3">Heading 3</option>
                  <option value="h4">Heading 4</option>
                </select>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                {{-- Inline formatting --}}
                <button type="button" data-cmd="bold" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center font-black transition-colors cursor-pointer" title="Bold (Ctrl+B)">B</button>
                <button type="button" data-cmd="italic" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center italic font-serif font-bold transition-colors cursor-pointer" title="Italic (Ctrl+I)">I</button>
                <button type="button" data-cmd="underline" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center underline font-bold transition-colors cursor-pointer" title="Underline (Ctrl+U)">U</button>
                <button type="button" data-cmd="strikeThrough" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center line-through font-bold transition-colors cursor-pointer" title="Strikethrough">S</button>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                {{-- Lists --}}
                <button type="button" data-cmd="insertUnorderedList" class="desc-tool-btn h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center gap-1 text-[11px] font-bold transition-colors cursor-pointer" title="Bullet List">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
                </button>
                <button type="button" data-cmd="insertOrderedList" class="desc-tool-btn h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center gap-1 text-[11px] font-bold transition-colors cursor-pointer" title="Numbered List">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 6h14M7 12h14M7 18h14M3 6h1M3 12h1M3 18h1"/></svg>
                </button>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                {{-- Alignment --}}
                <button type="button" data-cmd="justifyLeft" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center transition-colors cursor-pointer" title="Align Left">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16"/></svg>
                </button>
                <button type="button" data-cmd="justifyCenter" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center transition-colors cursor-pointer" title="Align Center">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M4 18h16"/></svg>
                </button>
                <button type="button" data-cmd="justifyRight" class="desc-tool-btn h-7 w-7 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center justify-center transition-colors cursor-pointer" title="Align Right">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M10 12h10M4 18h16"/></svg>
                </button>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                {{-- Quote & Table --}}
                <button type="button" id="descInsertQuoteBtn" class="h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center gap-1 text-[11px] font-bold transition-colors cursor-pointer" title="Blockquote">
                  <span>Quote</span>
                </button>
                <button type="button" id="descInsertTableBtn" class="h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center gap-1 text-[11px] font-bold transition-colors cursor-pointer" title="Insert Spec Table">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  <span>Table</span>
                </button>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                {{-- Media insertion buttons --}}
                <button type="button" id="descOpenImageModalBtn" class="h-7 px-2.5 rounded-lg bg-white hover:bg-stone-100 text-stone-700 border border-stone-200 flex items-center gap-1.5 font-medium text-xs transition-colors cursor-pointer" title="Add Image (Upload or URL)">
                  <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span>Image</span>
                </button>

                <button type="button" id="descOpenVideoModalBtn" class="h-7 px-2.5 rounded-lg bg-white hover:bg-stone-100 text-stone-700 border border-stone-200 flex items-center gap-1.5 font-medium text-xs transition-colors cursor-pointer" title="Add Video (YouTube, Vimeo, MP4)">
                  <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>Video</span>
                </button>

                <button type="button" id="descInsertLinkBtn" class="h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 flex items-center gap-1 text-[11px] font-bold transition-colors cursor-pointer" title="Insert Link">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                  <span>Link</span>
                </button>

                <div class="w-px h-5 bg-stone-200 mx-1"></div>

                <button type="button" data-cmd="removeFormat" class="desc-tool-btn h-7 px-2 rounded-lg hover:bg-stone-200 active:scale-95 text-[11px] text-stone-500 font-semibold transition-colors cursor-pointer" title="Clear Formatting">✕ Clear</button>
              </div>

              {{-- Editor Viewport 1: Visual WYSIWYG --}}
              <div id="descVisualEditor" contenteditable="true" class="min-h-[220px] max-h-[500px] overflow-y-auto p-4 text-xs sm:text-sm text-stone-800 focus:outline-none leading-relaxed prose prose-stone max-w-none">
                {!! old('description', $product->description) !!}
              </div>

              {{-- Editor Viewport 2: Custom HTML Code View --}}
              <textarea id="descHtmlEditor" name="description" rows="12" class="hidden w-full p-4 font-mono text-sm text-stone-100 bg-stone-900 border-0 focus:outline-none resize-y leading-relaxed" placeholder="Type or paste custom HTML, iframes, styles, video tags, or tables here...">{{ old('description', $product->description) }}</textarea>

              {{-- Editor Viewport 3: Real-Time Live Preview --}}
              <div id="descPreviewEditor" class="hidden min-h-[220px] max-h-[500px] overflow-y-auto p-4 bg-stone-50/50 text-xs sm:text-sm text-stone-800 leading-relaxed prose prose-stone max-w-none">
              </div>

              {{-- Status Footer --}}
              <div class="flex items-center justify-between px-3.5 py-1.5 bg-stone-50 border-t border-stone-200 text-[11px] text-stone-500">
                <span id="descWordCount">0 words · 0 chars</span>
                <span class="text-stone-400 hidden sm:inline">Tip: Switch to &lt;&gt; HTML mode to paste custom embed codes or CSS</span>
              </div>
            </div>

            <style>
              #descVisualEditor img, #descVisualEditor iframe, #descVisualEditor video, #descVisualEditor .aspect-video,
              #descPreviewEditor img, #descPreviewEditor iframe, #descPreviewEditor video, #descPreviewEditor .aspect-video {
                border-radius: 0 !important;
              }
            </style>
          </div>
        </div>
      </section>

      {{-- Card 5: Media Gallery & Drag-and-Drop Image Uploader --}}
      <section class="panel bg-white p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3.5">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">2</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Photos</h2>
              <p class="text-xs text-gray-500 mt-0.5">The first photo is the main image. JPG, PNG or WebP, up to 4&nbsp;MB each.</p>
            </div>
          </div>
        </div>

        {{-- Existing Uploaded Product Photos --}}
        @if($editing && $product->images->isNotEmpty())
          <div>
            <div class="flex items-center justify-between mb-2.5">
              <label class="text-[13px] font-medium text-stone-700">Existing Uploaded Images ({{ $product->images->count() }})</label>
              <span class="text-xs text-stone-500">Drag or use the arrows to reorder</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3" id="productImagesGrid">
              @foreach($product->images as $imgIndex => $img)
                <div class="existing-image-card relative group bg-stone-50 border border-stone-200 rounded-xl p-1.5 flex flex-col items-center gap-1 shadow-2xs cursor-grab active:cursor-grabbing hover:border-brand-300 transition-all" id="imgcard-{{ $img->id }}" draggable="true" data-image-id="{{ $img->id }}">
                  <input type="hidden" name="image_positions[{{ $img->id }}]" class="image-position-input" value="{{ $imgIndex }}" />
                  <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden flex items-center justify-center border border-stone-100">
                    <img src="{{ $img->url() }}" class="max-h-full max-w-full object-contain p-1 pointer-events-none" alt="{{ $img->alt }}" />
                    <span class="main-badge absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-semibold px-1.5 py-0.5 rounded shadow-2xs {{ $loop->first ? '' : 'hidden' }}">Main</span>
                    <button type="button" onclick="deleteProductImage({{ $product->id }}, {{ $img->id }})" class="absolute top-1 right-1 bg-rose-600 text-white h-5 w-5 rounded-full text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-md cursor-pointer" title="Delete Image">&times;</button>
                  </div>

                  {{-- Reorder Control Arrows --}}
                  <div class="flex items-center justify-between w-full px-1 py-0.5 bg-stone-100/80 rounded-md border border-stone-200 text-[10px] font-bold text-stone-600">
                    <button type="button" class="move-image-btn hover:text-stone-900 px-1 cursor-pointer" data-dir="left" title="Move Left">◄</button>
                    <span class="text-[9px] text-stone-400 uppercase tracking-tighter">Order</span>
                    <button type="button" class="move-image-btn hover:text-stone-900 px-1 cursor-pointer" data-dir="right" title="Move Right">►</button>
                  </div>

                  <div class="w-full mt-0.5">
                    <label class="text-[9px] font-bold text-stone-500 block text-center mb-0.5 uppercase tracking-wider">Variation Tag</label>
                    <input type="text" name="image_colors[{{ $img->id }}]" value="{{ old("image_colors.{$img->id}", $img->color) }}" placeholder="e.g. Black" class="w-full text-xs px-1.5 py-1 bg-white border border-stone-200 rounded-lg text-center text-stone-800 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-500 shadow-2xs" />
                  </div>
                  <div class="w-full text-[8.5px] font-semibold text-stone-500 bg-stone-100/70 p-1 rounded-md border border-stone-200/60 truncate" title="SEO Alt: {{ $img->alt }}">
                    Alt: <span class="text-stone-700">{{ $img->alt }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Upload Drag-and-Drop Area --}}
        <div class="border-2 border-dashed border-stone-300 hover:border-brand-600 rounded-xl p-8 text-center bg-stone-50 hover:bg-brand-50 transition-colors cursor-pointer relative">
          <input id="imageFileInput" name="images[]" type="file" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
          <div class="flex flex-col items-center gap-2">
            <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.16-5.16a2.25 2.25 0 013.18 0l5.16 5.16m-1.5-1.5l1.41-1.41a2.25 2.25 0 013.18 0l2.91 2.91M3.75 21h16.5A1.5 1.5 0 0021.75 19.5V4.5A1.5 1.5 0 0020.25 3H3.75A1.5 1.5 0 002.25 4.5v15A1.5 1.5 0 003.75 21zM15 8.25h.008v.008H15V8.25z"/></svg>
            <p class="text-sm font-medium text-stone-700">Click to upload or drag photos here</p>
            <p class="text-xs text-stone-500">A plain white background looks best</p>
          </div>
        </div>

        {{-- Live New Uploads Preview Grid --}}
        <div id="newImagesPreview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3"></div>
      </section>

      {{-- Card 2: Pricing & General Inventory --}}
      <section class="panel bg-white p-4 sm:p-5 space-y-5">
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3.5">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">3</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Pricing &amp; stock</h2>
              <p class="text-xs text-gray-500 mt-0.5">Prices in Taka. Buying cost is private and used for profit reports.</p>
            </div>
          </div>
          <span id="autoStockNoticeHeader" class="hidden text-xs text-stone-500"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 2xl:grid-cols-5 gap-4">
          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Regular Price (৳) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" id="regPriceInput" name="regular_price" class="w-full px-3.5 py-2.5 text-sm text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('regular_price', $product->regular_price) }}" placeholder="e.g. 850" required />
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Sale Price (৳)</label>
            <input type="number" step="0.01" id="salePriceInput" name="sale_price" class="w-full px-3.5 py-2.5 text-sm text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('sale_price', $product->sale_price) }}" placeholder="e.g. 750 (optional)" />
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Buying Cost (৳)</label>
            <input type="number" step="0.01" id="costPriceInput" name="cost_price" class="w-full px-3.5 py-2.5 text-sm text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('cost_price', $product->cost_price) }}" placeholder="e.g. 550 (for profit reports)" />
          </div>

          <div id="stockQuantityFieldGroup">
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-[13px] font-medium text-stone-700">Total Stock <span class="text-rose-500">*</span></label>
              <span id="autoStockNotice" class="hidden text-xs text-stone-500">From variants</span>
            </div>
            <input type="number" id="mainStockInput" name="stock_quantity" class="w-full px-3.5 py-2.5 text-sm text-stone-900 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 transition-all" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required />
            <div id="variantStockLinkHint" class="hidden mt-1.5 flex items-center justify-between text-[11px]">
              <span class="text-stone-500">Auto-sum of active variants</span>
              <a href="#skuMatrixSection" class="font-medium text-brand-700 hover:underline">Edit in variants</a>
            </div>
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Unit / Pack Size</label>
            <input type="text" name="unit" class="w-full px-3.5 py-2.5 text-sm text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20" value="{{ old('unit', $product->unit) }}" placeholder="e.g. 500ml, 1 Liter" />
          </div>
        </div>

        <p id="discountBadgePreview" class="hidden text-[13px] text-stone-600">Shoppers will see <span id="discountPercentText" class="font-semibold text-stone-900"></span> on this product.</p>
      </section>

      {{-- Card 3: Variants & Weight Pack Options --}}
      <section id="skuMatrixSection" class="panel bg-white p-4 sm:p-5 space-y-5">
        <input type="hidden" name="sku_matrix_submitted" value="1" />
        <div class="!mt-0 flex items-start justify-between gap-3 border-b border-gray-100 pb-3.5">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">4</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Variants</h2>
              <p class="text-xs text-gray-500 mt-0.5">Sizes, colours or other options, each with its own price and stock</p>
            </div>
          </div>
        </div>

        @php
          $skus = $editing ? $product->skus : collect();
          // Saved attributes (Settings → Product Variations) and what each category already uses,
          // so the picker suggests options that fit this product.
          $attributeLibrary = $attributeTypes->mapWithKeys(fn ($t) => [$t->name => $t->values->sortBy('position')->pluck('value')->values()]);
          $categoryAttributeUsage = \App\Models\ProductSku::with('product:id,category_id')->get(['id', 'product_id', 'attributes'])
            ->groupBy(fn ($s) => (string) $s->product?->category_id)
            ->map(fn ($group) => $group
              ->flatMap(fn ($s) => collect($s->getAttributesData())->map(fn ($v, $k) => ['name' => (string) $k, 'value' => (string) $v])->values())
              ->groupBy('name')
              ->map(fn ($rows, $name) => ['name' => $name, 'count' => $rows->count(), 'values' => $rows->pluck('value')->unique()->values()])
              ->sortByDesc('count')->values());
        @endphp
        <script type="application/json" id="variantPickerData">@json(['library' => $attributeLibrary, 'usage' => $categoryAttributeUsage])</script>

        {{-- Legacy fields: kept empty so old size/colour lists are rebuilt from the variant table on save --}}
        <input type="hidden" name="sizes" value="" />
        <input type="hidden" name="colors" value="" />

        {{-- Option picker (filled by JavaScript) --}}
        <div class="space-y-3">
          <div id="optionGroups" class="space-y-3"></div>
          <div id="optionSuggestions" class="flex flex-wrap items-center gap-2"></div>
          <p class="text-xs text-stone-500">
            Suggestions come from <a href="{{ route('admin.variations.index') }}" target="_blank" class="text-brand-700 hover:underline">Product variations</a> and other products in this category.
          </p>
        </div>

        {{-- Variant table --}}
        <div id="variantTableWrap" class="space-y-3 {{ $skus->isEmpty() ? 'hidden' : '' }}">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="text-sm font-semibold text-stone-900">Variants</span>
              <span id="matrixActiveCountBadge" class="px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 text-stone-600">{{ $skus->count() }}</span>
            </div>
            <div class="flex items-center gap-2">
              <label for="bulkStockQtyInput" class="text-[13px] text-stone-600">Set all stock to</label>
              <input type="number" id="bulkStockQtyInput" min="0" placeholder="Qty" class="w-20 px-2.5 py-1.5 text-sm text-center rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" />
              <button type="button" id="applyBulkStockBtn" class="px-3 py-1.5 border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 text-sm font-medium rounded-lg cursor-pointer">Apply</button>
            </div>
          </div>

          <div class="overflow-x-auto w-full border border-stone-200 rounded-xl">
            <table class="w-full text-left text-sm min-w-[640px]">
              <thead class="bg-stone-50 text-stone-500 text-xs font-medium border-b border-stone-200 whitespace-nowrap">
                <tr>
                  <th class="py-2.5 px-3">Variant</th>
                  <th class="py-2.5 px-3">SKU</th>
                  <th class="py-2.5 px-3 w-32">Price (৳)</th>
                  <th class="py-2.5 px-3 w-32">Sale price (৳)</th>
                  <th class="py-2.5 px-3 w-24">Stock</th>
                  <th class="py-2.5 px-3 w-16 text-center">Active</th>
                  <th class="py-2.5 px-2 w-10"><span class="sr-only">Remove</span></th>
                </tr>
              </thead>
              <tbody id="skuMatrixBody" class="divide-y divide-stone-100 bg-white">
                @foreach($skus as $index => $sku)
                  @php
                    $isCustomReg = $sku->regular_price !== null && abs((float) $sku->regular_price - (float) $product->regular_price) > 0.01;
                    $isCustomSale = $sku->sale_price !== null && abs((float) $sku->sale_price - (float) ($product->sale_price ?? $product->regular_price)) > 0.01;
                    $regValue = $isCustomReg ? $sku->regular_price : '';
                    $saleValue = $isCustomSale ? $sku->sale_price : '';
                  @endphp
                  <tr class="sku-row">
                    <td class="py-2.5 px-3">
                      <input type="hidden" name="sku_matrix[{{ $index }}][id]" value="{{ $sku->id }}" />
                      <div class="flex items-center gap-1 flex-wrap">
                        @foreach($sku->getAttributesData() as $k => $v)
                          <input type="hidden" name="sku_matrix[{{ $index }}][attributes][{{ $k }}]" value="{{ $v }}" />
                          <span class="px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 text-xs font-medium whitespace-nowrap">{{ $v }}</span>
                        @endforeach
                      </div>
                    </td>
                    <td class="py-2.5 px-3"><input name="sku_matrix[{{ $index }}][sku]" value="{{ $sku->sku }}" placeholder="Auto" class="w-full min-w-[170px] px-2.5 py-1.5 text-sm font-mono rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" /></td>
                    <td class="py-2.5 px-3"><input name="sku_matrix[{{ $index }}][regular_price]" type="number" step="0.01" value="{{ $regValue }}" placeholder="Base" class="sku-regular-price-input w-full px-2.5 py-1.5 text-sm rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" /></td>
                    <td class="py-2.5 px-3"><input name="sku_matrix[{{ $index }}][sale_price]" type="number" step="0.01" value="{{ $saleValue }}" placeholder="Base" class="sku-sale-price-input w-full px-2.5 py-1.5 text-sm rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" /></td>
                    <td class="py-2.5 px-3"><input name="sku_matrix[{{ $index }}][stock]" type="number" min="0" value="{{ $sku->stock_quantity }}" class="sku-stock-input w-full px-2.5 py-1.5 text-sm rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" required /></td>
                    <td class="py-2.5 px-3 text-center"><input type="checkbox" name="sku_matrix[{{ $index }}][is_active]" value="1" @checked($sku->is_active) class="sku-active-check accent-brand-600 h-4 w-4 cursor-pointer" /></td>
                    <td class="py-2.5 px-2 text-center"><button type="button" class="sku-remove-btn h-8 w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer" aria-label="Remove variant">&times;</button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <p class="text-xs text-stone-500">Leave price empty to use the product price. Total stock is the sum of active variants. Removed variants are deleted when you save.</p>
        </div>
      </section>

      {{-- Card 4: Product Specifications Builder --}}
      @php
        $oldLabels = old('spec_labels');
        $oldValues = old('spec_values');
        if (is_array($oldLabels)) {
          $specRows = [];
          foreach ($oldLabels as $i => $label) {
            $specRows[] = ['label' => $label, 'value' => $oldValues[$i] ?? ''];
          }
        } else {
          $specRows = $editing ? $product->specificationRows() : [];
        }
        if (empty($specRows)) {
          $specRows = [['label' => '', 'value' => '']];
        }
      @endphp
      <section class="panel bg-white p-4 sm:p-5 space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 pb-3.5">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">5</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Specifications</h2>
              <p class="text-xs text-gray-500 mt-0.5">Shown as a feature table on the product page</p>
            </div>
          </div>
          <button type="button" id="addSpecRow" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 cursor-pointer shrink-0">+ Add row</button>
        </div>

        <div id="specRows" class="space-y-3">
          @foreach($specRows as $row)
            <div class="spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-3 items-end">
              <div>
                <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Name</label>
                <input name="spec_labels[]" class="w-full px-3 py-2 text-sm text-stone-800 rounded-lg border border-stone-200 bg-white focus:outline-none focus:border-brand-600" value="{{ $row['label'] ?? '' }}" placeholder="e.g. Material" />
              </div>
              <div>
                <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Value</label>
                <input name="spec_values[]" class="w-full px-3 py-2 text-sm text-stone-800 rounded-lg border border-stone-200 bg-white focus:outline-none focus:border-brand-600" value="{{ $row['value'] ?? '' }}" placeholder="e.g. Breathable Mesh" />
              </div>
              <button type="button" class="remove-spec-row h-10 w-10 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors self-end sm:self-center cursor-pointer" title="Remove Feature">×</button>
            </div>
          @endforeach
        </div>
      </section>

      {{-- Card 6: Search engine listing (collapsed) --}}
      <details class="group panel bg-white p-4 sm:p-5">
        <summary class="flex items-start justify-between gap-3 cursor-pointer list-none">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold flex items-center justify-center shrink-0">6</div>
            <div>
              <h2 class="text-[15px] font-semibold text-gray-900">Search engine listing</h2>
              <p class="text-xs text-gray-500 mt-0.5">How this product appears on Google. Optional.</p>
            </div>
          </div>
          <svg class="w-5 h-5 text-stone-400 mt-1 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="space-y-4 pt-4 mt-3.5 border-t border-gray-100">
        {{-- Live Google Search Preview --}}
        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200 text-xs space-y-1">
          <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Google Search Preview</span>
          <p id="seoPreviewTitle" class="text-xs sm:text-sm font-bold text-blue-700 truncate hover:underline cursor-pointer">
            {{ old('meta_title', $product->meta_title) ?: ($editing ? $product->name . ' — ' . site_name() : 'Product Name — ' . site_name()) }}
          </p>
          <p class="text-[11px] text-emerald-700 truncate font-mono">
            {{ url('/product') }}/<span id="seoPreviewSlug">{{ old('slug', $product->slug) ?: 'product-slug' }}</span>
          </p>
          <p id="seoPreviewDesc" class="text-[11px] text-stone-600 line-clamp-2">
            {{ old('meta_description', $product->meta_description) ?: ($product->short_description ?: 'Add a meta description to control what Google shows here.') }}
          </p>
        </div>

        <div class="space-y-3">
          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1">Meta Title</label>
            <input type="text" id="metaTitleInput" name="meta_title" class="w-full px-3 py-2 text-sm font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600" value="{{ old('meta_title', $product->meta_title) }}" placeholder="e.g. Buy Nike Pegasus 40 Online — {{ site_name() }}" />
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1">Meta Description</label>
            <textarea id="metaDescInput" name="meta_description" rows="2" class="w-full px-3 py-2 text-sm font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600" placeholder="Short description for Google search results...">{{ old('meta_description', $product->meta_description) }}</textarea>
          </div>

          <div>
            <label class="text-[13px] font-medium text-stone-700 block mb-1">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="w-full px-3 py-2 text-sm font-medium text-stone-800 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="e.g. running shoes, nike, sneakers bd" />
          </div>
        </div>
      
        </div>
      </details>
    </div>

    {{-- Right Sidebar Column (Sticky on Desktop: Publish Box, Organization, Badges & SEO - 4 cols on Desktop) --}}
    <div class="lg:col-span-4 lg:sticky lg:top-[140px] space-y-4">

      {{-- Status & storefront badges --}}
      <section class="panel bg-white p-4 sm:p-5 space-y-1">
        <h2 class="text-[15px] font-semibold text-gray-900 pb-1">Status</h2>
        @php
          $toggles = [
            'is_published'   => ['Published', 'Visible to shoppers'],
            'is_featured'    => ['Featured', 'Shown in Our picks on the home page'],
            'is_flash_sale'  => ['Flash sale', 'Listed in the flash sale section'],
            'is_best_seller' => ['Best seller', 'Best seller badge on the card'],
            'is_new_arrival' => ['New arrival', 'New arrival badge on the card'],
          ];
          if (\App\Support\StaffAccess::allows(auth()->user(), 'free-delivery')) {
            $toggles['free_delivery'] = ['Free delivery', 'Orders with this product ship free'];
          }
        @endphp
        <div class="divide-y divide-stone-100">
          @foreach($toggles as $field => [$label, $hint])
            <label class="flex items-center justify-between gap-3 py-3 cursor-pointer">
              <span>
                <span class="block text-sm font-medium text-stone-900">{{ $label }}</span>
                <span class="block text-xs text-stone-500">{{ $hint }}</span>
              </span>
              <span class="relative inline-flex shrink-0">
                <input type="checkbox" name="{{ $field }}" value="1" class="peer sr-only" @checked(old($field, $product->$field)) />
                <span class="w-10 h-6 rounded-full bg-stone-300 transition-colors peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600/40"></span>
                <span class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
              </span>
            </label>
          @endforeach
        </div>
        @if($editing)
          <dl class="pt-3 mt-1 border-t border-stone-100 text-xs text-stone-500 space-y-1">
            <div class="flex justify-between"><dt>Created</dt><dd class="text-stone-700">{{ $product->created_at?->format('d M Y') ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt>Last updated</dt><dd class="text-stone-700">{{ $product->updated_at?->diffForHumans() ?? '—' }}</dd></div>
          </dl>
        @endif
      </section>
    </div>
  </div>

  {{-- Floating action bar on phones --}}
  <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-gray-100 px-3 py-2.5 sm:hidden flex items-center gap-2">
    <a href="{{ route('admin.products.index') }}" class="h-10 px-4 rounded-full bg-gray-100 text-gray-800 text-[13px] font-medium inline-flex items-center">Cancel</a>
    <button type="submit" class="flex-1 h-10 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer" style="background: var(--brand-dark);">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      <span>{{ $editing ? 'Save changes' : 'Save product' }}</span>
    </button>
  </div>
</form>

@if($editing)
  @foreach($product->images as $img)
    <form id="delimg{{ $img->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $img]) }}" onsubmit="return confirm('Remove this image?')">@csrf @method('DELETE')</form>
  @endforeach
@endif

{{-- Rich Description Insert Image Modal --}}
<div id="descImageModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity" aria-hidden="true">
  <div class="relative max-w-lg w-full bg-white rounded-2xl shadow-2xl border border-stone-200 overflow-hidden flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between p-4 border-b border-stone-100 bg-stone-50/50">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg></span>
        <div>
          <h3 class="text-[15px] font-semibold text-gray-900">Insert image</h3>
          <p class="text-[11px] text-stone-500">Upload an image file or paste a web image link</p>
        </div>
      </div>
      <button type="button" id="descCloseImageModal" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer" aria-label="Close"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-stone-200 bg-stone-50 px-4 pt-2 gap-2 text-xs font-bold">
      <button type="button" id="descImgTabUploadBtn" class="px-3 py-2 border-b-2 border-emerald-600 text-emerald-800 transition-colors cursor-pointer">Upload File</button>
      <button type="button" id="descImgTabUrlBtn" class="px-3 py-2 border-b-2 border-transparent text-stone-500 hover:text-stone-800 transition-colors cursor-pointer">Image URL</button>
    </div>

    <div class="p-5 overflow-y-auto space-y-4">
      {{-- Tab 1: Upload File --}}
      <div id="descImgUploadPanel" class="space-y-3">
        <label class="block border-2 border-dashed border-stone-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition-colors bg-stone-50/50 hover:bg-emerald-50/20">
          <input type="file" id="descImgFileInput" accept="image/*" class="hidden" />
          <div class="space-y-1.5">
            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">↑</div>
            <p class="text-xs font-bold text-stone-800">Click to choose image or drag &amp; drop</p>
            <p class="text-[11px] text-stone-400">JPG, PNG, WebP, GIF, SVG (up to 50MB)</p>
          </div>
        </label>
        <div id="descImgUploadStatus" class="hidden text-xs text-stone-600 flex items-center gap-2 p-2.5 rounded-xl bg-stone-100">
          <span class="animate-spin text-emerald-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg></span>
          <span id="descImgUploadStatusText">Uploading image...</span>
        </div>
      </div>

      {{-- Tab 2: URL --}}
      <div id="descImgUrlPanel" class="hidden space-y-3">
        <div>
          <label class="block text-xs font-bold text-stone-800 mb-1">Image URL (https://...)</label>
          <input type="url" id="descImgUrlInput" placeholder="https://example.com/photo.jpg" class="w-full px-3.5 py-2.5 text-sm font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" />
        </div>
      </div>

      {{-- Common Image Options --}}
      <div class="pt-2 border-t border-stone-100 space-y-3">
        <div>
          <label class="block text-xs font-bold text-stone-800 mb-1">Alt Text / Caption (Optional)</label>
          <input type="text" id="descImgAltInput" placeholder="Describe the image for customers &amp; SEO..." class="w-full px-3.5 py-2 text-sm font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-stone-800 mb-1">Width / Layout</label>
            <select id="descImgWidthSelect" class="w-full px-3 py-2 text-sm font-semibold rounded-xl border border-stone-200 focus:outline-none bg-white text-stone-900">
              <option value="100%">100% Full Width</option>
              <option value="75%">75% Large</option>
              <option value="50%">50% Medium</option>
              <option value="auto">Original Size</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-stone-800 mb-1">Alignment</label>
            <select id="descImgAlignSelect" class="w-full px-3 py-2 text-sm font-semibold rounded-xl border border-stone-200 focus:outline-none bg-white text-stone-900">
              <option value="center">Centered</option>
              <option value="left">Left</option>
              <option value="right">Right</option>
            </select>
          </div>
        </div>

        <div id="descImgPreviewBox" class="hidden p-2 bg-stone-50 rounded-xl border border-stone-200 text-center">
          <p class="text-[10px] text-stone-400 font-bold mb-1">Preview</p>
          <img id="descImgPreview" src="" class="max-h-40 mx-auto rounded-lg object-contain shadow-2xs" alt="Preview" />
        </div>
      </div>
    </div>

    <div class="p-4 border-t border-stone-100 bg-stone-50/50 flex items-center justify-end gap-2">
      <button type="button" id="descCancelImgBtn" class="px-4 py-2 rounded-xl text-xs font-bold text-stone-600 hover:bg-stone-200 transition-colors cursor-pointer">Cancel</button>
      <button type="button" id="descConfirmImgBtn" class="h-9 px-4 rounded-full text-[13px] font-semibold bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-xs transition-all cursor-pointer">Insert Image</button>
    </div>
  </div>
</div>

{{-- Rich Description Insert Video Modal --}}
<div id="descVideoModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity" aria-hidden="true">
  <div class="relative max-w-lg w-full bg-white rounded-2xl shadow-2xl border border-stone-200 overflow-hidden flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between p-4 border-b border-stone-100 bg-stone-50/50">
      <div class="flex items-center gap-2">
        <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="15" height="14" x="2" y="5" rx="2"/><path d="m17 10 5-3v10l-5-3"/></svg></span>
        <div>
          <h3 class="text-[15px] font-semibold text-gray-900">Insert video</h3>
          <p class="text-[11px] text-stone-500">Embed YouTube, Vimeo or direct MP4 video files</p>
        </div>
      </div>
      <button type="button" id="descCloseVideoModal" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer" aria-label="Close"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>

    {{-- Tabs --}}
    <div class="flex border-b border-stone-200 bg-stone-50 px-4 pt-2 gap-2 text-xs font-bold">
      <button type="button" id="descVidTabLinkBtn" class="px-3 py-2 border-b-2 border-red-600 text-red-800 transition-colors cursor-pointer">YouTube / Vimeo Link</button>
      <button type="button" id="descVidTabDirectBtn" class="px-3 py-2 border-b-2 border-transparent text-stone-500 hover:text-stone-800 transition-colors cursor-pointer">Direct MP4 / Upload</button>
    </div>

    <div class="p-5 overflow-y-auto space-y-4">
      {{-- Tab 1: Video Link --}}
      <div id="descVidLinkPanel" class="space-y-3">
        <div>
          <label class="block text-xs font-bold text-stone-800 mb-1">YouTube or Vimeo Video URL</label>
          <input type="url" id="descVidUrlInput" placeholder="e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ or https://youtu.be/..." class="w-full px-3.5 py-2.5 text-sm font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-red-500 text-stone-900" />
          <p class="text-[11px] text-stone-400 mt-1">Supports standard YouTube, Shorts, youtu.be, and Vimeo URLs. Responsive 16:9 player generated automatically.</p>
        </div>
      </div>

      {{-- Tab 2: Direct MP4 --}}
      <div id="descVidDirectPanel" class="hidden space-y-3">
        <div>
          <label class="block text-xs font-bold text-stone-800 mb-1">Direct Video URL (.mp4 / .webm)</label>
          <input type="url" id="descVidDirectUrlInput" placeholder="https://example.com/video.mp4" class="w-full px-3.5 py-2.5 text-sm font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-red-500 text-stone-900" />
        </div>
        <div class="text-center text-stone-400 text-xs font-bold">OR</div>
        <label class="block border-2 border-dashed border-stone-300 hover:border-red-500 rounded-2xl p-4 text-center cursor-pointer transition-colors bg-stone-50/50 hover:bg-red-50/20">
          <input type="file" id="descVidFileInput" accept="video/mp4,video/webm,video/ogg" class="hidden" />
          <div class="space-y-1">
            <span class="text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect width="15" height="14" x="2" y="5" rx="2"/><path d="m17 10 5-3v10l-5-3"/></svg></span>
            <p class="text-xs font-bold text-stone-800">Upload MP4 Video File</p>
            <p class="text-[10px] text-stone-400">MP4, WebM (up to 50MB)</p>
          </div>
        </label>
        <div id="descVidUploadStatus" class="hidden text-xs text-stone-600 flex items-center gap-2 p-2.5 rounded-xl bg-stone-100">
          <span class="animate-spin text-rose-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg></span>
          <span id="descVidUploadStatusText">Uploading video...</span>
        </div>
      </div>

      {{-- Common Video Options --}}
      <div class="pt-2 border-t border-stone-100 space-y-3">
        <div>
          <label class="block text-xs font-bold text-stone-800 mb-1">Optional Caption or Title</label>
          <input type="text" id="descVidCaptionInput" placeholder="e.g. Official Product Showcase Video" class="w-full px-3.5 py-2 text-sm font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-red-500 text-stone-900" />
        </div>
      </div>
    </div>

    <div class="p-4 border-t border-stone-100 bg-stone-50/50 flex items-center justify-end gap-2">
      <button type="button" id="descCancelVidBtn" class="px-4 py-2 rounded-xl text-xs font-bold text-stone-600 hover:bg-stone-200 transition-colors cursor-pointer">Cancel</button>
      <button type="button" id="descConfirmVidBtn" class="h-9 px-4 rounded-full text-[13px] font-semibold bg-red-600 hover:bg-red-700 active:scale-95 text-white shadow-xs transition-all cursor-pointer">Insert Video</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
  const SITE_NAME = @json(site_name());
// ==========================================
// Rich Product Description Editor Controller
// ==========================================
(function() {
  const visualEl = document.getElementById('descVisualEditor');
  const htmlEl = document.getElementById('descHtmlEditor');
  const previewEl = document.getElementById('descPreviewEditor');
  const toolbarEl = document.getElementById('descEditorToolbar');

  const modeVisualBtn = document.getElementById('descModeVisualBtn');
  const modeHtmlBtn = document.getElementById('descModeHtmlBtn');
  const modePreviewBtn = document.getElementById('descModePreviewBtn');
  const wordCountEl = document.getElementById('descWordCount');
  const headingSelect = document.getElementById('descHeadingSelect');

  let currentMode = 'visual'; // 'visual' | 'html' | 'preview'
  let savedRange = null;

  if (!visualEl || !htmlEl) return;

  function saveSelection() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
      savedRange = sel.getRangeAt(0).cloneRange();
    }
  }

  function restoreSelection() {
    if (savedRange) {
      const sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(savedRange);
    } else {
      visualEl.focus();
    }
  }

  function getCleanHtmlFromVisual() {
    const clone = visualEl.cloneNode(true);
    clone.querySelectorAll('.desc-media-toolbar').forEach(el => el.remove());
    clone.querySelectorAll('.desc-media-block').forEach(el => {
      el.removeAttribute('contenteditable');
      el.removeAttribute('tabindex');
      el.classList.remove('border-2', 'border-dashed', 'border-stone-300', 'hover:border-red-400', 'p-2', 'rounded-2xl', 'transition-all');
    });
    // Remove any ghost media blocks or empty aspect-video containers without iframe or video
    clone.querySelectorAll('.aspect-video, [data-media-type="video"]').forEach(el => {
      if (!el.querySelector('iframe, video')) {
        const block = el.closest('.desc-media-block') || el;
        block.remove();
      }
    });
    return clone.innerHTML;
  }

  function removeMediaBlock(mediaBlock) {
    if (!mediaBlock || mediaBlock === visualEl) return;
    const nextEl = mediaBlock.nextElementSibling;
    if (nextEl && nextEl.tagName === 'P' && (!nextEl.textContent.trim() || nextEl.innerHTML === '<br>')) {
      nextEl.remove();
    }
    mediaBlock.remove();
    syncContent('visual');
    visualEl.focus();
  }

  function enhanceVisualMediaBlocks() {
    // Wrap and attach removal toolbar to any iframe or video
    visualEl.querySelectorAll('iframe, video').forEach(media => {
      let block = media.closest('.desc-media-block');
      if (block === visualEl) block = null;

      if (!block) {
        let curr = media;
        while (curr.parentElement && curr.parentElement !== visualEl && !curr.parentElement.classList.contains('desc-media-block')) {
          curr = curr.parentElement;
        }

        if (curr && curr !== visualEl && curr.nodeType === 1 && (curr.classList.contains('aspect-video') || curr.querySelector('iframe, video'))) {
          block = curr;
        } else {
          block = document.createElement('div');
          media.parentNode.insertBefore(block, media);
          block.appendChild(media);
        }
      }

      if (block && block !== visualEl) {
        block.classList.add('desc-media-block', 'relative', 'group', 'my-6', 'w-full', 'max-w-3xl', 'mx-auto', 'border-2', 'border-dashed', 'border-stone-300', 'hover:border-red-400', 'p-2', 'rounded-2xl', 'transition-all');
        block.setAttribute('contenteditable', 'false');
        block.setAttribute('tabindex', '0');

        if (!block.querySelector('.desc-media-toolbar')) {
          const isIframe = !!block.querySelector('iframe');
          const toolbar = document.createElement('div');
          toolbar.className = 'desc-media-toolbar flex items-center justify-between bg-stone-900 text-white px-3 py-1.5 rounded-xl text-xs font-bold mb-2 shadow-sm select-none';
          toolbar.innerHTML = `
            <span class="flex items-center gap-1.5 text-stone-300">
              <span>🎬</span>
              <span>${isIframe ? 'Embedded Video (YouTube/Vimeo)' : 'HTML5 Video Player'}</span>
            </span>
            <div class="flex items-center gap-2">
              <span class="text-[10px] text-stone-400 hidden sm:inline">Click to delete</span>
              <button type="button" class="desc-remove-media-btn bg-red-600 hover:bg-red-700 active:scale-95 text-white px-2.5 py-1 rounded-lg text-xs font-black flex items-center gap-1 transition-all cursor-pointer" title="Delete this video completely">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>✕ Remove Video</span>
              </button>
            </div>
          `;
          block.insertBefore(toolbar, block.firstChild);
        }
      }
    });

    // Also wrap images with quick removal bar
    visualEl.querySelectorAll('figure').forEach(figure => {
      if (figure === visualEl) return;
      figure.classList.add('desc-media-block', 'relative');
      figure.setAttribute('contenteditable', 'false');
      figure.setAttribute('tabindex', '0');

      if (!figure.querySelector('.desc-media-toolbar')) {
        const toolbar = document.createElement('div');
        toolbar.className = 'desc-media-toolbar flex items-center justify-between bg-stone-900/85 text-white px-2.5 py-1 rounded-lg text-[10px] font-bold mb-1 shadow-2xs select-none';
        toolbar.innerHTML = `
          <span class="text-stone-300">🖼 Image</span>
          <button type="button" class="desc-remove-media-btn bg-red-600 hover:bg-red-700 active:scale-95 text-white px-2 py-0.5 rounded text-[10px] font-bold cursor-pointer" title="Delete image">✕ Remove Image</button>
        `;
        figure.insertBefore(toolbar, figure.firstChild);
      }
    });
  }

  function syncContent(from) {
    if (from === 'visual') {
      htmlEl.value = getCleanHtmlFromVisual();
    } else if (from === 'html') {
      visualEl.innerHTML = htmlEl.value;
      enhanceVisualMediaBlocks();
    }
    updateWordCount();
  }

  function updateWordCount() {
    if (!wordCountEl) return;
    const text = visualEl.innerText || '';
    const words = text.trim() ? text.trim().split(/\s+/).length : 0;
    const chars = text.length;
    wordCountEl.textContent = `${words} words · ${chars} chars`;
  }

  visualEl.addEventListener('input', () => syncContent('visual'));
  visualEl.addEventListener('blur', () => syncContent('visual'));
  visualEl.addEventListener('keyup', saveSelection);
  visualEl.addEventListener('mouseup', saveSelection);
  htmlEl.addEventListener('input', () => syncContent('html'));

  // Click delegation for instant media removal
  visualEl.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.desc-remove-media-btn');
    if (removeBtn) {
      e.preventDefault();
      e.stopPropagation();
      const mediaBlock = removeBtn.closest('.desc-media-block');
      if (mediaBlock) {
        removeMediaBlock(mediaBlock);
      }
    }
  });

  // Keydown delegation: delete focused media block on Backspace or Delete
  visualEl.addEventListener('keydown', (e) => {
    if (e.key === 'Delete' || e.key === 'Backspace') {
      const activeEl = document.activeElement;
      if (activeEl && activeEl.classList.contains('desc-media-block') && visualEl.contains(activeEl)) {
        e.preventDefault();
        removeMediaBlock(activeEl);
      }
    }
  });

  enhanceVisualMediaBlocks();

  updateWordCount();

  // Mode switching
  function switchMode(newMode) {
    if (newMode === currentMode) return;

    if (currentMode === 'visual') {
      syncContent('visual');
    } else if (currentMode === 'html') {
      syncContent('html');
    }

    [modeVisualBtn, modeHtmlBtn, modePreviewBtn].forEach(btn => {
      btn?.classList.remove('bg-white', 'text-stone-900', 'shadow-2xs');
      btn?.classList.add('text-stone-600');
    });

    visualEl.classList.add('hidden');
    htmlEl.classList.add('hidden');
    previewEl.classList.add('hidden');

    if (newMode === 'visual') {
      visualEl.classList.remove('hidden');
      toolbarEl?.classList.remove('opacity-40', 'pointer-events-none');
      modeVisualBtn?.classList.add('bg-white', 'text-stone-900', 'shadow-2xs');
      modeVisualBtn?.classList.remove('text-stone-600');
      visualEl.focus();
    } else if (newMode === 'html') {
      htmlEl.classList.remove('hidden');
      toolbarEl?.classList.add('opacity-40', 'pointer-events-none');
      modeHtmlBtn?.classList.add('bg-white', 'text-stone-900', 'shadow-2xs');
      modeHtmlBtn?.classList.remove('text-stone-600');
      htmlEl.focus();
    } else if (newMode === 'preview') {
      previewEl.innerHTML = htmlEl.value;
      previewEl.classList.remove('hidden');
      toolbarEl?.classList.add('opacity-40', 'pointer-events-none');
      modePreviewBtn?.classList.add('bg-white', 'text-stone-900', 'shadow-2xs');
      modePreviewBtn?.classList.remove('text-stone-600');
    }

    currentMode = newMode;
  }

  modeVisualBtn?.addEventListener('click', () => switchMode('visual'));
  modeHtmlBtn?.addEventListener('click', () => switchMode('html'));
  modePreviewBtn?.addEventListener('click', () => switchMode('preview'));

  // Form submission: ensure htmlEl always has latest content
  const productForm = document.getElementById('productForm');
  if (productForm) {
    productForm.addEventListener('submit', () => {
      if (currentMode === 'visual') {
        htmlEl.value = getCleanHtmlFromVisual();
      }
    });
  }

  // Toolbar action buttons
  document.querySelectorAll('.desc-tool-btn[data-cmd]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (currentMode !== 'visual') switchMode('visual');
      visualEl.focus();
      restoreSelection();
      document.execCommand(btn.dataset.cmd, false, null);
      saveSelection();
      syncContent('visual');
    });
  });

  headingSelect?.addEventListener('change', (e) => {
    if (currentMode !== 'visual') switchMode('visual');
    visualEl.focus();
    restoreSelection();
    document.execCommand('formatBlock', false, e.target.value);
    saveSelection();
    syncContent('visual');
  });

  // Link button
  document.getElementById('descInsertLinkBtn')?.addEventListener('click', () => {
    if (currentMode !== 'visual') switchMode('visual');
    const url = prompt('Enter destination link URL (https://...):', 'https://');
    if (url && url.trim()) {
      visualEl.focus();
      restoreSelection();
      document.execCommand('createLink', false, url.trim());
      saveSelection();
      syncContent('visual');
    }
  });

  // Blockquote button
  document.getElementById('descInsertQuoteBtn')?.addEventListener('click', () => {
    if (currentMode !== 'visual') switchMode('visual');
    visualEl.focus();
    restoreSelection();
    document.execCommand('formatBlock', false, 'blockquote');
    saveSelection();
    syncContent('visual');
  });

  // Spec Table button
  document.getElementById('descInsertTableBtn')?.addEventListener('click', () => {
    if (currentMode !== 'visual') switchMode('visual');
    const tableHtml = `
      <table class="w-full text-xs my-4 border border-stone-200 rounded-xl overflow-hidden">
        <thead>
          <tr class="bg-stone-100 text-stone-800">
            <th class="p-2.5 text-left font-bold border-b border-stone-200">Specification</th>
            <th class="p-2.5 text-left font-bold border-b border-stone-200">Details</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-b border-stone-100"><td class="p-2.5 text-stone-600 font-semibold">Feature</td><td class="p-2.5 text-stone-800">High Quality</td></tr>
          <tr class="border-b border-stone-100"><td class="p-2.5 text-stone-600 font-semibold">Warranty</td><td class="p-2.5 text-stone-800">100% Guaranteed</td></tr>
          <tr><td class="p-2.5 text-stone-600 font-semibold">Origin</td><td class="p-2.5 text-stone-800">Authentic</td></tr>
        </tbody>
      </table>
      <p><br></p>
    `;
    insertHtmlAtCursor(tableHtml);
  });

  function insertHtmlAtCursor(html) {
    if (currentMode !== 'visual') {
      const start = htmlEl.selectionStart || htmlEl.value.length;
      const end = htmlEl.selectionEnd || htmlEl.value.length;
      htmlEl.value = htmlEl.value.substring(0, start) + html + htmlEl.value.substring(end);
      syncContent('html');
      return;
    }

    visualEl.focus();
    restoreSelection();

    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
      const range = sel.getRangeAt(0);
      range.deleteContents();

      const el = document.createElement('div');
      el.innerHTML = html;
      const frag = document.createDocumentFragment();
      let node, lastNode;
      while ((node = el.firstChild)) {
        lastNode = frag.appendChild(node);
      }
      range.insertNode(frag);

      if (lastNode) {
        range.setStartAfter(lastNode);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
      }
    } else {
      visualEl.innerHTML += html;
    }
    enhanceVisualMediaBlocks();
    saveSelection();
    syncContent('visual');
  }

  // -------------------------------------------------------------
  // Image Modal Controller
  // -------------------------------------------------------------
  const imageModal = document.getElementById('descImageModal');
  const openImgBtn = document.getElementById('descOpenImageModalBtn');
  const closeImgBtn = document.getElementById('descCloseImageModal');
  const cancelImgBtn = document.getElementById('descCancelImgBtn');
  const confirmImgBtn = document.getElementById('descConfirmImgBtn');

  const imgTabUploadBtn = document.getElementById('descImgTabUploadBtn');
  const imgTabUrlBtn = document.getElementById('descImgTabUrlBtn');
  const imgUploadPanel = document.getElementById('descImgUploadPanel');
  const imgUrlPanel = document.getElementById('descImgUrlPanel');

  const imgFileInput = document.getElementById('descImgFileInput');
  const imgUploadStatus = document.getElementById('descImgUploadStatus');
  const imgUploadStatusText = document.getElementById('descImgUploadStatusText');
  const imgUrlInput = document.getElementById('descImgUrlInput');
  const imgAltInput = document.getElementById('descImgAltInput');
  const imgWidthSelect = document.getElementById('descImgWidthSelect');
  const imgAlignSelect = document.getElementById('descImgAlignSelect');
  const imgPreviewBox = document.getElementById('descImgPreviewBox');
  const imgPreview = document.getElementById('descImgPreview');

  let activeImgUrl = '';

  function openImageModal() {
    saveSelection();
    imageModal?.classList.remove('hidden');
    activeImgUrl = '';
    if (imgUrlInput) imgUrlInput.value = '';
    if (imgAltInput) imgAltInput.value = '';
    imgPreviewBox?.classList.add('hidden');
    if (imgPreview) imgPreview.src = '';
    switchImgTab('upload');
  }

  function closeImageModal() {
    imageModal?.classList.add('hidden');
  }

  function switchImgTab(tab) {
    if (tab === 'upload') {
      imgUploadPanel?.classList.remove('hidden');
      imgUrlPanel?.classList.add('hidden');
      imgTabUploadBtn?.classList.add('border-emerald-600', 'text-emerald-800');
      imgTabUploadBtn?.classList.remove('border-transparent', 'text-stone-500');
      imgTabUrlBtn?.classList.remove('border-emerald-600', 'text-emerald-800');
      imgTabUrlBtn?.classList.add('border-transparent', 'text-stone-500');
    } else {
      imgUploadPanel?.classList.add('hidden');
      imgUrlPanel?.classList.remove('hidden');
      imgTabUrlBtn?.classList.add('border-emerald-600', 'text-emerald-800');
      imgTabUrlBtn?.classList.remove('border-transparent', 'text-stone-500');
      imgTabUploadBtn?.classList.remove('border-emerald-600', 'text-emerald-800');
      imgTabUploadBtn?.classList.add('border-transparent', 'text-stone-500');
      imgUrlInput?.focus();
    }
  }

  openImgBtn?.addEventListener('click', openImageModal);
  closeImgBtn?.addEventListener('click', closeImageModal);
  cancelImgBtn?.addEventListener('click', closeImageModal);
  imgTabUploadBtn?.addEventListener('click', () => switchImgTab('upload'));
  imgTabUrlBtn?.addEventListener('click', () => switchImgTab('url'));

  imgUrlInput?.addEventListener('input', (e) => {
    activeImgUrl = e.target.value.trim();
    if (activeImgUrl && imgPreview) {
      imgPreview.src = activeImgUrl;
      imgPreviewBox?.classList.remove('hidden');
    } else {
      imgPreviewBox?.classList.add('hidden');
    }
  });

  // AJAX Image Upload
  imgFileInput?.addEventListener('change', async () => {
    const file = imgFileInput.files && imgFileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    imgUploadStatus?.classList.remove('hidden');
    if (imgUploadStatusText) imgUploadStatusText.textContent = `Uploading ${file.name}...`;

    try {
      const res = await fetch('{{ route('admin.products.upload-description-media') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success && data.url) {
        activeImgUrl = data.url;
        if (imgPreview) imgPreview.src = data.url;
        imgPreviewBox?.classList.remove('hidden');
        if (imgUploadStatusText) imgUploadStatusText.textContent = '✓ Upload complete!';
        setTimeout(() => imgUploadStatus?.classList.add('hidden'), 1500);
      } else {
        alert(data.message || 'Upload failed. Please try again.');
        imgUploadStatus?.classList.add('hidden');
      }
    } catch (err) {
      console.error(err);
      alert('Upload error. Please check your network and try again.');
      imgUploadStatus?.classList.add('hidden');
    }
  });

  confirmImgBtn?.addEventListener('click', () => {
    const url = activeImgUrl || (imgUrlInput ? imgUrlInput.value.trim() : '');
    if (!url) {
      alert('Please select an image file or enter an image URL.');
      return;
    }

    const alt = (imgAltInput ? imgAltInput.value : '').trim();
    const width = imgWidthSelect ? imgWidthSelect.value : '100%';
    const align = imgAlignSelect ? imgAlignSelect.value : 'center';

    let alignClass = 'text-center my-4';
    let floatStyle = '';
    if (align === 'left') {
      alignClass = 'float-left mr-4 mb-4 my-2';
      floatStyle = 'float: left; margin: 0 1rem 1rem 0;';
    } else if (align === 'right') {
      alignClass = 'float-right ml-4 mb-4 my-2';
      floatStyle = 'float: right; margin: 0 0 1rem 1rem;';
    }

    const widthStyle = width === 'auto' ? 'max-width: 100%;' : `width: ${width}; max-width: 100%;`;

    const imgTag = `
      <figure class="${alignClass}" style="${floatStyle}">
        <img src="${url}" alt="${alt.replace(/"/g, '&quot;')}" class="rounded-none shadow-xs" style="${widthStyle} height: auto; display: inline-block; border-radius: 0 !important;" />
        ${alt ? `<figcaption class="text-xs text-stone-500 mt-1 text-center font-medium">${alt}</figcaption>` : ''}
      </figure>
      <p><br></p>
    `;

    closeImageModal();
    insertHtmlAtCursor(imgTag);
  });

  // -------------------------------------------------------------
  // Video Modal Controller
  // -------------------------------------------------------------
  const videoModal = document.getElementById('descVideoModal');
  const openVidBtn = document.getElementById('descOpenVideoModalBtn');
  const closeVidBtn = document.getElementById('descCloseVideoModal');
  const cancelVidBtn = document.getElementById('descCancelVidBtn');
  const confirmVidBtn = document.getElementById('descConfirmVidBtn');

  const vidTabLinkBtn = document.getElementById('descVidTabLinkBtn');
  const vidTabDirectBtn = document.getElementById('descVidTabDirectBtn');
  const vidLinkPanel = document.getElementById('descVidLinkPanel');
  const vidDirectPanel = document.getElementById('descVidDirectPanel');

  const vidUrlInput = document.getElementById('descVidUrlInput');
  const vidDirectUrlInput = document.getElementById('descVidDirectUrlInput');
  const vidFileInput = document.getElementById('descVidFileInput');
  const vidUploadStatus = document.getElementById('descVidUploadStatus');
  const vidUploadStatusText = document.getElementById('descVidUploadStatusText');
  const vidCaptionInput = document.getElementById('descVidCaptionInput');

  let activeVideoType = 'link'; // 'link' | 'direct'
  let uploadedVideoUrl = '';

  function openVideoModal() {
    saveSelection();
    videoModal?.classList.remove('hidden');
    if (vidUrlInput) vidUrlInput.value = '';
    if (vidDirectUrlInput) vidDirectUrlInput.value = '';
    if (vidCaptionInput) vidCaptionInput.value = '';
    uploadedVideoUrl = '';
    switchVidTab('link');
  }

  function closeVideoModal() {
    videoModal?.classList.add('hidden');
  }

  function switchVidTab(tab) {
    activeVideoType = tab;
    if (tab === 'link') {
      vidLinkPanel?.classList.remove('hidden');
      vidDirectPanel?.classList.add('hidden');
      vidTabLinkBtn?.classList.add('border-red-600', 'text-red-800');
      vidTabLinkBtn?.classList.remove('border-transparent', 'text-stone-500');
      vidTabDirectBtn?.classList.remove('border-red-600', 'text-red-800');
      vidTabDirectBtn?.classList.add('border-transparent', 'text-stone-500');
      vidUrlInput?.focus();
    } else {
      vidLinkPanel?.classList.add('hidden');
      vidDirectPanel?.classList.remove('hidden');
      vidTabDirectBtn?.classList.add('border-red-600', 'text-red-800');
      vidTabDirectBtn?.classList.remove('border-transparent', 'text-stone-500');
      vidTabLinkBtn?.classList.remove('border-red-600', 'text-red-800');
      vidTabLinkBtn?.classList.add('border-transparent', 'text-stone-500');
      vidDirectUrlInput?.focus();
    }
  }

  openVidBtn?.addEventListener('click', openVideoModal);
  closeVidBtn?.addEventListener('click', closeVideoModal);
  cancelVidBtn?.addEventListener('click', closeVideoModal);
  vidTabLinkBtn?.addEventListener('click', () => switchVidTab('link'));
  vidTabDirectBtn?.addEventListener('click', () => switchVidTab('direct'));

  // Video Upload
  vidFileInput?.addEventListener('change', async () => {
    const file = vidFileInput.files && vidFileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    vidUploadStatus?.classList.remove('hidden');
    if (vidUploadStatusText) vidUploadStatusText.textContent = `Uploading ${file.name}...`;

    try {
      const res = await fetch('{{ route('admin.products.upload-description-media') }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await res.json();
      if (res.ok && data.success && data.url) {
        uploadedVideoUrl = data.url;
        if (vidDirectUrlInput) vidDirectUrlInput.value = data.url;
        if (vidUploadStatusText) vidUploadStatusText.textContent = '✓ Video uploaded successfully!';
        setTimeout(() => vidUploadStatus?.classList.add('hidden'), 2000);
      } else {
        alert(data.message || 'Video upload failed. Check file size (max 50MB).');
        vidUploadStatus?.classList.add('hidden');
      }
    } catch (err) {
      console.error(err);
      alert('Upload error. Please try again.');
      vidUploadStatus?.classList.add('hidden');
    }
  });

  // Parse YouTube & Vimeo URLs into embed codes
  function parseVideoEmbed(url) {
    if (!url) return null;
    const cleanUrl = url.trim();

    // YouTube: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/shorts/ID, youtube.com/embed/ID
    const ytMatch = cleanUrl.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch && ytMatch[1]) {
      return `https://www.youtube-nocookie.com/embed/${ytMatch[1]}`;
    }

    // Vimeo: vimeo.com/ID
    const vimeoMatch = cleanUrl.match(/vimeo\.com\/(?:video\/)?([0-9]+)/);
    if (vimeoMatch && vimeoMatch[1]) {
      return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
    }

    return null;
  }

  confirmVidBtn?.addEventListener('click', () => {
    const caption = (vidCaptionInput ? vidCaptionInput.value : '').trim();

    if (activeVideoType === 'link') {
      const inputUrl = vidUrlInput ? vidUrlInput.value.trim() : '';
      if (!inputUrl) {
        alert('Please enter a YouTube or Vimeo link.');
        return;
      }
      const embedUrl = parseVideoEmbed(inputUrl);
      if (!embedUrl) {
        alert('Could not detect a valid YouTube or Vimeo video ID from that link. Please check the URL.');
        return;
      }

      const videoSnippet = `
        <div class="desc-media-block relative group my-6 w-full max-w-3xl mx-auto border-2 border-dashed border-stone-300 hover:border-red-400 p-2 rounded-2xl transition-all" contenteditable="false" data-media-type="video">
          <div class="desc-media-toolbar flex items-center justify-between bg-stone-900 text-white px-3 py-1.5 rounded-xl text-xs font-bold mb-2 shadow-sm select-none">
            <span class="flex items-center gap-1.5 text-stone-300">
              <span>🎬</span>
              <span>Embedded Video (YouTube/Vimeo)</span>
            </span>
            <div class="flex items-center gap-2">
              <span class="text-[10px] text-stone-400 hidden sm:inline">Click to delete</span>
              <button type="button" class="desc-remove-media-btn bg-red-600 hover:bg-red-700 active:scale-95 text-white px-2.5 py-1 rounded-lg text-xs font-black flex items-center gap-1 transition-all cursor-pointer" title="Delete this video completely">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>✕ Remove Video</span>
              </button>
            </div>
          </div>
          <div class="relative w-full aspect-video rounded-none overflow-hidden shadow-md bg-stone-900 border border-stone-200" style="border-radius: 0 !important;">
            <iframe src="${embedUrl}" class="absolute inset-0 w-full h-full border-0" style="border-radius: 0 !important;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
          </div>
          ${caption ? `<p class="text-xs text-stone-500 mt-2 text-center font-medium">${caption}</p>` : ''}
        </div>
        <p><br></p>
      `;

      closeVideoModal();
      insertHtmlAtCursor(videoSnippet);
    } else {
      const videoSrc = uploadedVideoUrl || (vidDirectUrlInput ? vidDirectUrlInput.value.trim() : '');
      if (!videoSrc) {
        alert('Please select an MP4 video to upload or paste a direct video link.');
        return;
      }

      const videoSnippet = `
        <div class="desc-media-block relative group my-6 w-full max-w-3xl mx-auto border-2 border-dashed border-stone-300 hover:border-red-400 p-2 rounded-2xl transition-all" contenteditable="false" data-media-type="video">
          <div class="desc-media-toolbar flex items-center justify-between bg-stone-900 text-white px-3 py-1.5 rounded-xl text-xs font-bold mb-2 shadow-sm select-none">
            <span class="flex items-center gap-1.5 text-stone-300">
              <span>🎬</span>
              <span>HTML5 Video Player</span>
            </span>
            <div class="flex items-center gap-2">
              <span class="text-[10px] text-stone-400 hidden sm:inline">Click to delete</span>
              <button type="button" class="desc-remove-media-btn bg-red-600 hover:bg-red-700 active:scale-95 text-white px-2.5 py-1 rounded-lg text-xs font-black flex items-center gap-1 transition-all cursor-pointer" title="Delete this video completely">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>✕ Remove Video</span>
              </button>
            </div>
          </div>
          <video controls playsinline preload="metadata" class="w-full rounded-none shadow-md border border-stone-200 bg-black" style="border-radius: 0 !important;">
            <source src="${videoSrc}" type="video/mp4">
            Your browser does not support the video tag.
          </video>
          ${caption ? `<p class="text-xs text-stone-500 mt-2 text-center font-medium">${caption}</p>` : ''}
        </div>
        <p><br></p>
      `;

      closeVideoModal();
      insertHtmlAtCursor(videoSnippet);
    }
  });

  // Close modals on backdrop click
  [imageModal, videoModal].forEach(modal => {
    modal?.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.add('hidden');
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (!imageModal?.classList.contains('hidden')) closeImageModal();
      if (!videoModal?.classList.contains('hidden')) closeVideoModal();
    }
  });
})();

(function () {
  // Live Name & Slug & SEO Binding
  const nameInput = document.getElementById('productNameInput');
  const slugInput = document.getElementById('productSlugInput');
  const nameCount = document.getElementById('nameCharCount');
  const autoSlugBtn = document.getElementById('autoSlugBtn');

  const seoTitle = document.getElementById('seoPreviewTitle');
  const seoSlug = document.getElementById('seoPreviewSlug');
  const seoDesc = document.getElementById('seoPreviewDesc');
  const metaTitle = document.getElementById('metaTitleInput');
  const metaDesc = document.getElementById('metaDescInput');

  function slugify(text) {
    return text.toString().toLowerCase().trim()
      .replace(/[\s\W-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (nameInput) {
    nameInput.addEventListener('input', () => {
      const len = nameInput.value.length;
      if (nameCount) nameCount.innerText = `${len} chars`;

      if (seoTitle && (!metaTitle || !metaTitle.value)) {
        seoTitle.innerText = nameInput.value ? `${nameInput.value} — ${SITE_NAME}` : `Product Name — ${SITE_NAME}`;
      }
    });
    if (nameCount) nameCount.innerText = `${nameInput.value.length} chars`;
  }

  if (autoSlugBtn && nameInput && slugInput) {
    autoSlugBtn.addEventListener('click', () => {
      slugInput.value = slugify(nameInput.value);
      if (seoSlug) seoSlug.innerText = slugInput.value;
    });
  }

  if (slugInput && seoSlug) {
    slugInput.addEventListener('input', () => {
      seoSlug.innerText = slugInput.value || 'product-slug';
    });
  }

  if (metaTitle && seoTitle) {
    metaTitle.addEventListener('input', () => {
      seoTitle.innerText = metaTitle.value || (nameInput.value ? `${nameInput.value} — ${SITE_NAME}` : `Product Name — ${SITE_NAME}`);
    });
  }

  if (metaDesc && seoDesc) {
    metaDesc.addEventListener('input', () => {
      seoDesc.innerText = metaDesc.value || 'Add a meta description to control what Google shows here.';
    });
  }

  // Live Price & Discount Calculator
  const regInput = document.getElementById('regPriceInput');
  const saleInput = document.getElementById('salePriceInput');
  const discountBox = document.getElementById('discountBadgePreview');
  const discountText = document.getElementById('discountPercentText');

  function updateDiscountBadge() {
    const reg = parseFloat(regInput ? regInput.value : 0) || 0;
    const sale = parseFloat(saleInput ? saleInput.value : 0) || 0;

    if (reg > 0 && sale > 0 && sale < reg) {
      const diff = reg - sale;
      const pct = Math.round((diff / reg) * 100);
      if (discountText) discountText.innerText = `${pct}% OFF (Save ৳${diff.toFixed(0)})`;
      if (discountBox) discountBox.classList.remove('hidden');
    } else if (discountBox) {
      discountBox.classList.add('hidden');
    }
  }

  if (regInput) regInput.addEventListener('input', updateDiscountBadge);
  if (saleInput) saleInput.addEventListener('input', updateDiscountBadge);
  updateDiscountBadge();
})();

// Specification Builder Script
(function () {
  const list = document.getElementById('specRows');
  const addBtn = document.getElementById('addSpecRow');
  if (!list || !addBtn) return;

  function bindRemove(btn) {
    btn.addEventListener('click', () => {
      const rows = list.querySelectorAll('.spec-row');
      if (rows.length <= 1) {
        rows[0].querySelectorAll('input').forEach((i) => { i.value = ''; });
        return;
      }
      btn.closest('.spec-row')?.remove();
    });
  }

  list.querySelectorAll('.remove-spec-row').forEach(bindRemove);

  addBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'spec-row grid grid-cols-1 sm:grid-cols-[1fr_1.5fr_auto] gap-2.5 sm:gap-3 items-center p-3 bg-stone-50 rounded-xl border border-stone-200/80';
    row.innerHTML = `
      <div>
        <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Name</label>
        <input name="spec_labels[]" class="w-full px-3 py-1.5 text-sm text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. Material" />
      </div>
      <div>
        <label class="text-[13px] font-medium text-stone-700 block mb-1.5">Value</label>
        <input name="spec_values[]" class="w-full px-3 py-1.5 text-sm text-stone-800 rounded-lg border border-stone-200 bg-white" placeholder="e.g. Breathable Mesh" />
      </div>
      <button type="button" class="remove-spec-row sm:mt-4 h-7 w-7 sm:h-8 sm:w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center font-bold text-base transition-colors self-end sm:self-center cursor-pointer" title="Remove Feature">×</button>
    `;
    list.appendChild(row);
    bindRemove(row.querySelector('.remove-spec-row'));
  });
})();

// Variant table totals & bulk stock
(function () {
  const body = document.getElementById('skuMatrixBody');

  function updateMatrixCalculations() {
    const regInput = document.getElementById('regPriceInput');
    const saleInput = document.getElementById('salePriceInput');
    const stockInput = document.getElementById('mainStockInput') || document.querySelector('input[name="stock_quantity"]');
    const autoStockBadge = document.getElementById('autoStockNotice');
    const autoStockHeader = document.getElementById('autoStockNoticeHeader');
    const variantStockLinkHint = document.getElementById('variantStockLinkHint');
    const matrixCountBadge = document.getElementById('matrixActiveCountBadge');

    const regPrice = parseFloat(regInput ? regInput.value : 0) || 0;
    const salePrice = parseFloat(saleInput ? saleInput.value : 0) || 0;
    const baseSale = salePrice > 0 ? salePrice : regPrice;

    let totalStockSum = 0;
    let activeSkusCount = 0;
    let totalRowsCount = 0;

    document.querySelectorAll('.sku-row').forEach(row => {
      totalRowsCount++;
      const activeCheck = row.querySelector('.sku-active-check');
      const isActive = !activeCheck || activeCheck.checked;

      const regPriceInput = row.querySelector('.sku-regular-price-input');
      const salePriceInput = row.querySelector('.sku-sale-price-input');
      const stockItemInput = row.querySelector('.sku-stock-input');

      if (regPriceInput && (!regPriceInput.value || regPriceInput.value === '0')) {
        regPriceInput.placeholder = regPrice > 0 ? '৳' + regPrice.toFixed(2) : 'Auto Base';
      }
      if (salePriceInput && (!salePriceInput.value || salePriceInput.value === '0')) {
        salePriceInput.placeholder = baseSale > 0 ? '৳' + baseSale.toFixed(2) : 'Auto Base';
      }

      if (stockItemInput) {
        if (isActive) {
          totalStockSum += Math.max(0, parseInt(stockItemInput.value, 10) || 0);
          activeSkusCount++;
        }
      }
    });

    if (matrixCountBadge) {
      matrixCountBadge.textContent = activeSkusCount === totalRowsCount ? `${totalRowsCount}` : `${activeSkusCount} of ${totalRowsCount} active`;
    }

    if (totalRowsCount > 0 && stockInput) {
      stockInput.value = totalStockSum;
      stockInput.readOnly = true;
      stockInput.classList.add('bg-stone-50', 'text-stone-700', 'cursor-pointer');
      stockInput.title = 'Total stock is auto-calculated from variations below. Click to jump to variations.';
      if (autoStockBadge) {
        autoStockBadge.textContent = `From variants`;
        autoStockBadge.classList.remove('hidden');
      }
      if (autoStockHeader) {
        autoStockHeader.textContent = `${totalStockSum} in stock across ${activeSkusCount} variants`;
        autoStockHeader.classList.remove('hidden');
      }
      if (variantStockLinkHint) {
        variantStockLinkHint.classList.remove('hidden');
      }
    } else if (stockInput) {
      stockInput.readOnly = false;
      stockInput.classList.remove('bg-stone-50', 'text-stone-700', 'cursor-pointer');
      stockInput.removeAttribute('title');
      if (autoStockBadge) autoStockBadge.classList.add('hidden');
      if (autoStockHeader) autoStockHeader.classList.add('hidden');
      if (variantStockLinkHint) variantStockLinkHint.classList.add('hidden');
    }
  }

  // Real-time listener for stock inputs, prices, and checkboxes
  if (body) {
    body.addEventListener('input', (e) => {
      if (e.target && (e.target.classList.contains('sku-stock-input') || e.target.classList.contains('sku-regular-price-input') || e.target.classList.contains('sku-sale-price-input'))) {
        updateMatrixCalculations();
      }
    });
    body.addEventListener('change', (e) => {
      if (e.target && e.target.classList.contains('sku-active-check')) {
        updateMatrixCalculations();
      }
    });
  }

  // Smooth scroll to matrix when user clicks readonly Total Stock
  const mainStockInp = document.getElementById('mainStockInput');
  if (mainStockInp) {
    mainStockInp.addEventListener('click', () => {
      if (mainStockInp.readOnly) {
        const matrixSec = document.getElementById('skuMatrixSection');
        if (matrixSec) {
          matrixSec.scrollIntoView({ behavior: 'smooth' });
          const firstStockInp = matrixSec.querySelector('.sku-stock-input');
          if (firstStockInp) {
            firstStockInp.focus();
            firstStockInp.classList.add('ring-2', 'ring-brand-600');
            setTimeout(() => firstStockInp.classList.remove('ring-2', 'ring-brand-600'), 1200);
          }
        }
      }
    });
  }

  // Quick Bulk Stock Setter
  const applyBulkBtn = document.getElementById('applyBulkStockBtn');
  const bulkStockInput = document.getElementById('bulkStockQtyInput');
  if (applyBulkBtn && bulkStockInput) {
    applyBulkBtn.addEventListener('click', () => {
      const val = parseInt(bulkStockInput.value, 10);
      if (isNaN(val) || val < 0) {
        alert('Please enter a valid stock number (0 or higher).');
        bulkStockInput.focus();
        return;
      }
      let updated = 0;
      document.querySelectorAll('.sku-stock-input').forEach(inp => {
        inp.value = val;
        updated++;
      });
      updateMatrixCalculations();
      const origText = applyBulkBtn.textContent;
      applyBulkBtn.textContent = '✓ Updated!';
      setTimeout(() => { applyBulkBtn.textContent = origText; }, 1500);
    });
  }

  window.updateMatrixCalculations = updateMatrixCalculations;
  setTimeout(updateMatrixCalculations, 100);
})();

// Variant picker: options (Size, Colour…) with values → live variant table.
// Rows already in the table keep their price, stock, SKU and id when options change.
(function () {
  const body = document.getElementById('skuMatrixBody');
  const groupsEl = document.getElementById('optionGroups');
  const suggestEl = document.getElementById('optionSuggestions');
  const tableWrap = document.getElementById('variantTableWrap');
  const dataEl = document.getElementById('variantPickerData');
  const categorySelect = document.querySelector('select[name="category_id"]');
  if (!body || !groupsEl || !dataEl) return;

  const DATA = JSON.parse(dataEl.textContent || '{}');
  const LIBRARY = DATA.library || {};
  const USAGE = DATA.usage || {};
  const MAX_OPTIONS = 3;
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const norm = (v) => String(v ?? '').trim().toLowerCase();
  const cleanName = (v) => String(v ?? '').replace(/[\[\]]/g, '').trim();

  let options = [];          // [{ name, values: [] }]
  let saved = [];            // rows: { attrs: {name: value}, id, sku, regular_price, sale_price, stock, is_active }
  const removed = new Set(); // variant keys the admin removed by hand

  const keyOf = (attrs) => Object.keys(attrs).map((k) => norm(k) + '=' + norm(attrs[k])).sort().join('|');

  function readRowsFromTable() {
    return Array.from(body.querySelectorAll('tr.sku-row')).map((tr) => {
      const attrs = {};
      tr.querySelectorAll('input[name*="[attributes]"]').forEach((inp) => {
        const m = inp.name.match(/\[attributes\]\[(.*)\]$/);
        if (m) attrs[m[1]] = inp.value;
      });
      const val = (suffix) => tr.querySelector(`input[name$="[${suffix}]"]`)?.value ?? '';
      return {
        attrs, id: val('id'), sku: val('sku'), regular_price: val('regular_price'), sale_price: val('sale_price'),
        stock: val('stock'), is_active: tr.querySelector('input[name$="[is_active]"]')?.checked ?? true,
      };
    });
  }

  function combinations() {
    const filled = options.filter((o) => o.name && o.values.length);
    if (!filled.length) return [];
    return filled.reduce((acc, o) => acc.flatMap((combo) => o.values.map((v) => ({ ...combo, [o.name]: v }))), [{}]);
  }

  const partsOf = (attrs) => Object.keys(attrs).map((k) => norm(k) + '=' + norm(attrs[k]));
  const isSubset = (small, big) => small.every((p) => big.includes(p));

  function findSaved(combo, claimed) {
    const key = keyOf(combo);
    const parts = partsOf(combo);
    // 1) same variant  2) an option was added (row is a subset)  3) an option was removed (combo is a subset)
    return saved.find((r) => !claimed.has(r) && keyOf(r.attrs) === key)
      || saved.find((r) => !claimed.has(r) && isSubset(partsOf(r.attrs), parts))
      || saved.find((r) => !claimed.has(r) && isSubset(parts, partsOf(r.attrs)));
  }

  // A variant removed by hand stays removed, also after adding another option.
  const isRemoved = (combo) => [...removed].some((k) => isSubset(k.split('|'), partsOf(combo)));

  function renderTable() {
    saved = readRowsFromTable().concat(saved.filter((r) => r.detached));
    const claimed = new Set();
    const rows = combinations().filter((c) => !isRemoved(c)).map((combo) => {
      const prev = findSaved(combo, claimed);
      if (prev) claimed.add(prev);
      return { attrs: combo, id: '', sku: '', regular_price: '', sale_price: '', stock: '0', is_active: true, ...(prev ? { ...prev, attrs: combo } : {}) };
    });

    body.innerHTML = rows.map((r, i) => {
      const attrInputs = Object.keys(r.attrs).map((k) => `<input type="hidden" name="sku_matrix[${i}][attributes][${esc(k)}]" value="${esc(r.attrs[k])}" />`).join('');
      const chips = Object.keys(r.attrs).map((k) => `<span class="px-2 py-0.5 rounded-md bg-stone-100 text-stone-700 text-xs font-medium whitespace-nowrap" title="${esc(k)}">${esc(r.attrs[k])}</span>`).join('');
      const input = 'w-full px-2.5 py-1.5 text-sm rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900';
      return `<tr class="sku-row" data-key="${esc(keyOf(r.attrs))}">
        <td class="py-2.5 px-3">${r.id ? `<input type="hidden" name="sku_matrix[${i}][id]" value="${esc(r.id)}" />` : ''}${attrInputs}<div class="flex items-center gap-1 flex-wrap">${chips}</div></td>
        <td class="py-2.5 px-3"><input name="sku_matrix[${i}][sku]" value="${esc(r.sku)}" placeholder="Auto" class="${input} min-w-[170px] font-mono" /></td>
        <td class="py-2.5 px-3"><input name="sku_matrix[${i}][regular_price]" type="number" step="0.01" value="${esc(r.regular_price)}" placeholder="Base" class="sku-regular-price-input ${input}" /></td>
        <td class="py-2.5 px-3"><input name="sku_matrix[${i}][sale_price]" type="number" step="0.01" value="${esc(r.sale_price)}" placeholder="Base" class="sku-sale-price-input ${input}" /></td>
        <td class="py-2.5 px-3"><input name="sku_matrix[${i}][stock]" type="number" min="0" value="${esc(r.stock)}" class="sku-stock-input ${input}" required /></td>
        <td class="py-2.5 px-3 text-center"><input type="checkbox" name="sku_matrix[${i}][is_active]" value="1" ${r.is_active ? 'checked' : ''} class="sku-active-check accent-brand-600 h-4 w-4 cursor-pointer" /></td>
        <td class="py-2.5 px-2 text-center"><button type="button" class="sku-remove-btn h-8 w-8 rounded-lg text-stone-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer" aria-label="Remove variant">&times;</button></td>
      </tr>`;
    }).join('');

    // Rows that no longer match any combination stay remembered, in case the value is added back.
    saved = saved.filter((r) => !claimed.has(r)).map((r) => ({ ...r, detached: true }));
    if (tableWrap) tableWrap.classList.toggle('hidden', rows.length === 0);
    if (typeof window.updateMatrixCalculations === 'function') window.updateMatrixCalculations();
  }

  // ---- suggestions -----------------------------------------------------------
  const categoryUsage = () => USAGE[categorySelect?.value || ''] || [];
  function suggestedValues(name) {
    const out = [];
    const add = (v) => { if (v && !out.some((x) => norm(x) === norm(v))) out.push(v); };
    Object.keys(LIBRARY).forEach((lib) => { if (norm(lib) === norm(name)) LIBRARY[lib].forEach(add); });
    categoryUsage().forEach((u) => { if (norm(u.name) === norm(name)) u.values.forEach(add); });
    return out;
  }
  function suggestedNames() {
    const used = new Set(options.map((o) => norm(o.name)));
    const names = categoryUsage().map((u) => u.name);
    const fallback = names.length ? [] : Object.keys(LIBRARY);
    return [...names, ...fallback].filter((n, i, all) => !used.has(norm(n)) && all.findIndex((x) => norm(x) === norm(n)) === i).slice(0, 4);
  }

  // ---- option groups UI --------------------------------------------------------
  function renderOptions(focusIndex = null) {
    const nameList = Object.keys(LIBRARY).concat(categoryUsage().map((u) => u.name));
    groupsEl.innerHTML = options.map((o, gi) => {
      const chips = o.values.map((v, vi) => `<span class="inline-flex items-center gap-1 pl-2.5 pr-1 py-1 rounded-full bg-stone-100 text-sm text-stone-800">${esc(v)}<button type="button" class="og-value-remove h-5 w-5 rounded-full hover:bg-stone-200 text-stone-500" data-g="${gi}" data-v="${vi}" aria-label="Remove ${esc(v)}">&times;</button></span>`).join('');
      const sugg = suggestedValues(o.name).filter((v) => !o.values.some((x) => norm(x) === norm(v)));
      return `<div class="option-group rounded-xl border border-stone-200 p-4 space-y-3" data-g="${gi}">
        <div class="flex items-center gap-2">
          <input class="og-name flex-1 px-3 py-2 text-sm font-medium rounded-lg border border-stone-200 focus:outline-none focus:border-brand-600 text-stone-900" list="variantOptionNames" value="${esc(o.name)}" placeholder="Option name, e.g. Size or Colour" data-g="${gi}" aria-label="Option name" />
          <button type="button" class="og-remove h-9 px-3 rounded-lg text-sm text-stone-500 hover:text-rose-600 hover:bg-rose-50" data-g="${gi}">Remove</button>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
          ${chips}
          <input class="og-value-input min-w-[180px] flex-1 px-3 py-1.5 text-sm rounded-lg border border-dashed border-stone-300 focus:outline-none focus:border-brand-600 text-stone-900" placeholder="Add a value and press Enter" data-g="${gi}" aria-label="Add value" />
        </div>
        ${sugg.length ? `<div class="flex flex-wrap items-center gap-1.5"><span class="text-xs text-stone-500 mr-1">Suggested</span>${sugg.slice(0, 24).map((v) => `<button type="button" class="og-suggest px-2.5 py-1 rounded-full border border-stone-200 bg-white hover:bg-stone-50 text-xs text-stone-700" data-g="${gi}" data-value="${esc(v)}">+ ${esc(v)}</button>`).join('')}</div>` : ''}
      </div>`;
    }).join('') + `<datalist id="variantOptionNames">${[...new Set(nameList)].map((n) => `<option value="${esc(n)}"></option>`).join('')}</datalist>`;

    const canAdd = options.length < MAX_OPTIONS;
    suggestEl.innerHTML = canAdd ? [
      ...suggestedNames().map((n) => `<button type="button" class="og-add-named px-3 py-1.5 rounded-lg border border-stone-200 bg-white hover:bg-stone-50 text-sm text-stone-700" data-name="${esc(n)}">+ ${esc(n)}</button>`),
      `<button type="button" class="og-add px-3 py-1.5 rounded-lg text-sm font-medium text-brand-700 hover:bg-brand-50">+ ${options.length ? 'Add another option' : 'Add option (size, colour…)'}</button>`,
    ].join('') : '';

    if (focusIndex !== null) groupsEl.querySelector(`.og-value-input[data-g="${focusIndex}"]`)?.focus();
  }

  function addValues(gi, raw, keepFocus = true) {
    const o = options[gi];
    if (!o) return;
    String(raw).split(',').map((v) => v.trim()).filter(Boolean).forEach((v) => {
      if (!o.values.some((x) => norm(x) === norm(v))) o.values.push(v);
      [...removed].forEach((k) => { if (k.split('|').includes(norm(o.name) + '=' + norm(v))) removed.delete(k); });
    });
    renderOptions(keepFocus ? gi : null);
    renderTable();
  }

  groupsEl.addEventListener('click', (e) => {
    const t = e.target.closest('button');
    if (!t) return;
    const gi = Number(t.dataset.g);
    if (t.classList.contains('og-value-remove')) { options[gi].values.splice(Number(t.dataset.v), 1); renderOptions(); renderTable(); }
    else if (t.classList.contains('og-remove')) { options.splice(gi, 1); renderOptions(); renderTable(); }
    else if (t.classList.contains('og-suggest')) { addValues(gi, t.dataset.value); }
  });
  groupsEl.addEventListener('keydown', (e) => {
    if (!e.target.classList.contains('og-value-input')) return;
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      addValues(Number(e.target.dataset.g), e.target.value);
    } else if (e.key === 'Backspace' && !e.target.value) {
      const o = options[Number(e.target.dataset.g)];
      if (o && o.values.length) { o.values.pop(); renderOptions(Number(e.target.dataset.g)); renderTable(); }
    }
  });
  groupsEl.addEventListener('focusout', (e) => {
    if (e.target.classList.contains('og-value-input') && e.target.value.trim()) addValues(Number(e.target.dataset.g), e.target.value, false);
  });
  groupsEl.addEventListener('change', (e) => {
    if (!e.target.classList.contains('og-name')) return;
    const gi = Number(e.target.dataset.g);
    const oldName = options[gi].name;
    const newName = cleanName(e.target.value);
    if (!newName || newName === oldName) { e.target.value = oldName; return; }
    if (options.some((o, i) => i !== gi && norm(o.name) === norm(newName))) { alert('That option already exists.'); e.target.value = oldName; return; }
    // Rename the attribute on existing rows so their prices and stock are kept.
    body.querySelectorAll(`input[name*="[attributes][${CSS.escape(oldName)}]"]`).forEach((inp) => {
      inp.name = inp.name.replace(`[attributes][${oldName}]`, `[attributes][${newName}]`);
    });
    saved.forEach((r) => { if (oldName in r.attrs) { r.attrs[newName] = r.attrs[oldName]; delete r.attrs[oldName]; } });
    [...removed].forEach((k) => { removed.delete(k); removed.add(k.split('|').map((p) => p.startsWith(norm(oldName) + '=') ? norm(newName) + p.slice(norm(oldName).length) : p).sort().join('|')); });
    options[gi].name = newName;
    renderOptions();
    renderTable();
  });
  suggestEl.addEventListener('click', (e) => {
    const t = e.target.closest('button');
    if (!t || options.length >= MAX_OPTIONS) return;
    options.push({ name: t.dataset.name || '', values: [] });
    renderOptions(options.length - 1);
    if (!t.dataset.name) groupsEl.querySelector(`.og-name[data-g="${options.length - 1}"]`)?.focus();
  });
  body.addEventListener('click', (e) => {
    const t = e.target.closest('.sku-remove-btn');
    if (!t) return;
    const tr = t.closest('tr');
    const attrs = {};
    tr.querySelectorAll('input[name*="[attributes]"]').forEach((inp) => { const m = inp.name.match(/\[attributes\]\[(.*)\]$/); if (m) attrs[m[1]] = inp.value; });
    removed.add(keyOf(attrs));
    tr.remove();
    renderTable();
  });
  categorySelect?.addEventListener('change', () => renderOptions());

  // ---- start from the variants already saved on this product ----------------
  readRowsFromTable().forEach((r) => {
    Object.keys(r.attrs).forEach((name) => {
      let o = options.find((x) => norm(x.name) === norm(name));
      if (!o) options.push(o = { name, values: [] });
      if (!o.values.some((v) => norm(v) === norm(r.attrs[name]))) o.values.push(r.attrs[name]);
    });
  });
  renderOptions();
  renderTable();

  window.getVariantOptionValues = () => options.flatMap((o) => o.values);
})();

function deleteProductImage(productId, imageId) {
  if (!confirm('Remove this image?')) return;
  const card = document.getElementById('imgcard-' + imageId);
  const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
  const url = '/admin/products/' + productId + '/images/' + imageId;

  fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: '_method=DELETE&_token=' + encodeURIComponent(token)
  })
  .then(res => {
    if (res.ok) {
      if (card) {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        setTimeout(() => card.remove(), 300);
      }
    } else {
      const form = document.getElementById('delimg' + imageId);
      if (form) form.submit();
      else window.location.reload();
    }
  })
  .catch(() => {
    const form = document.getElementById('delimg' + imageId);
    if (form) form.submit();
    else window.location.reload();
  });
}

// Existing Images Drag & Drop / Button Re-ordering Script
(function() {
  const grid = document.getElementById('productImagesGrid');
  if (!grid) return;

  function updatePositionsAndBadges() {
    const cards = grid.querySelectorAll('.existing-image-card');
    cards.forEach((card, idx) => {
      const posInput = card.querySelector('.image-position-input');
      if (posInput) posInput.value = idx;

      const mainBadge = card.querySelector('.main-badge');
      if (mainBadge) {
        if (idx === 0) mainBadge.classList.remove('hidden');
        else mainBadge.classList.add('hidden');
      }
    });
  }

  grid.addEventListener('click', function(e) {
    const btn = e.target.closest('.move-image-btn');
    if (!btn) return;
    const card = btn.closest('.existing-image-card');
    if (!card) return;
    const dir = btn.dataset.dir;

    if (dir === 'left' && card.previousElementSibling) {
      grid.insertBefore(card, card.previousElementSibling);
    } else if (dir === 'right' && card.nextElementSibling) {
      grid.insertBefore(card.nextElementSibling, card);
    }
    updatePositionsAndBadges();
  });

  // HTML5 Drag and Drop Re-ordering
  let draggedCard = null;

  grid.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.existing-image-card');
    if (!card) return;
    draggedCard = card;
    card.classList.add('opacity-40');
    e.dataTransfer.effectAllowed = 'move';
  });

  grid.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const card = e.target.closest('.existing-image-card');
    if (card && card !== draggedCard) {
      const bounding = card.getBoundingClientRect();
      const offset = e.clientX - bounding.left;
      if (offset > bounding.width / 2) {
        grid.insertBefore(draggedCard, card.nextElementSibling);
      } else {
        grid.insertBefore(draggedCard, card);
      }
      updatePositionsAndBadges();
    }
  });

  grid.addEventListener('dragend', function(e) {
    const card = e.target.closest('.existing-image-card');
    if (card) card.classList.remove('opacity-40');
    draggedCard = null;
    updatePositionsAndBadges();
  });
})();

// Live Drag-and-Drop Image Upload Preview with Auto SEO & Variation Tag Pre-population
(function() {
  const fileInput = document.getElementById('imageFileInput');
  const previewBox = document.getElementById('newImagesPreview');
  if (!fileInput || !previewBox) return;

  function getEnteredVariantOptions() {
    const opts = [];
    return typeof window.getVariantOptionValues === 'function' ? window.getVariantOptionValues() : [];
  }

  fileInput.addEventListener('change', function(e) {
    previewBox.innerHTML = '';
    const files = Array.from(e.target.files || []);
    const productName = document.querySelector('input[name="name"]')?.value?.trim() || 'Product';
    const brandSelect = document.querySelector('select[name="brand_id"]');
    const brandName = brandSelect && brandSelect.selectedIndex > 0 ? brandSelect.options[brandSelect.selectedIndex].text.trim() : '';
    const variantOptions = getEnteredVariantOptions();

    files.forEach((file, idx) => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = function(evt) {
        const autoTag = variantOptions[idx] || '';
        let seoAlt = productName;
        if (autoTag) seoAlt += ` (${autoTag})`;
        if (brandName) seoAlt += ` by ${brandName}`;
        seoAlt += ' — ' + @json(site_name());

        const div = document.createElement('div');
        div.className = 'new-image-card relative flex flex-col items-center gap-1 p-1.5 bg-stone-50 border border-stone-200 rounded-xl shadow-2xs cursor-grab';
        div.innerHTML = `
          <div class="relative w-full aspect-square bg-white rounded-lg overflow-hidden border border-stone-100 flex items-center justify-center">
            <img src="${evt.target.result}" class="max-h-full max-w-full object-contain p-1 pointer-events-none" />
            <span class="new-main-badge absolute top-1 left-1 bg-brand-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs ${idx === 0 ? '' : 'hidden'}">Main</span>
          </div>
          <div class="flex items-center justify-between w-full px-1 py-0.5 bg-stone-100/80 rounded-md border border-stone-200 text-[10px] font-bold text-stone-600">
            <button type="button" class="move-new-img-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="left" title="Move Left">◄</button>
            <span class="text-[9px] text-stone-400 uppercase tracking-tighter">Order</span>
            <button type="button" class="move-new-img-btn hover:text-stone-900 px-1 cursor-pointer font-black" data-dir="right" title="Move Right">►</button>
          </div>
          <div class="w-full mt-0.5">
            <label class="text-[9px] font-bold text-stone-500 block text-center mb-0.5 uppercase tracking-wider">Variation Tag</label>
            <input type="text" name="new_image_colors[${idx}]" value="${autoTag}" placeholder="e.g. Black" class="w-full text-sm px-1.5 py-1 bg-white border border-stone-200 rounded-lg text-center text-stone-800 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
          </div>
          <div class="w-full text-[8.5px] font-semibold text-stone-500 bg-emerald-50/90 p-1 rounded-md border border-emerald-200/60 truncate" title="SEO Alt: ${seoAlt}">
            Alt: <span class="text-stone-700">${seoAlt}</span>
          </div>
        `;
        previewBox.appendChild(div);
        updateNewPreviewBadges();
      };
      reader.readAsDataURL(file);
    });
  });

  previewBox.addEventListener('click', function(e) {
    const btn = e.target.closest('.move-new-img-btn');
    if (!btn) return;
    const card = btn.closest('.new-image-card');
    if (!card) return;
    const dir = btn.dataset.dir;

    if (dir === 'left' && card.previousElementSibling) {
      previewBox.insertBefore(card, card.previousElementSibling);
    } else if (dir === 'right' && card.nextElementSibling) {
      previewBox.insertBefore(card.nextElementSibling, card);
    }
    updateNewPreviewBadges();
  });

  function updateNewPreviewBadges() {
    const cards = previewBox.querySelectorAll('.new-image-card');
    cards.forEach((card, idx) => {
      const badge = card.querySelector('.new-main-badge');
      if (badge) {
        if (idx === 0) badge.classList.remove('hidden');
        else badge.classList.add('hidden');
      }
    });
  }
})();

// Desktop Keyboard Shortcut (Ctrl+S or Cmd+S to save/update)
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    const form = document.getElementById('productForm');
    if (form) {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.click();
      else form.submit();
    }
  }
});
</script>
@endpush
@endsection


