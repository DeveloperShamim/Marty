@extends('layouts.admin')
@php
  $editing = $banner->exists;
  $currentPlacement = old('placement', $banner->placement ?? 'hero');
@endphp

@section('title', $editing ? 'Edit Banner' : 'New Banner')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data" id="bannerForm">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Top Navigation & Action --}}
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <a href="{{ route('admin.banners.index') }}" class="w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 flex items-center justify-center transition-colors shadow-sm" aria-label="Back">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      </a>
      <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
          {{ $editing ? 'Edit Banner: ' . ($banner->title ?: 'Banner #' . $banner->id) : 'Create New Banner' }}
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">Upload banner graphic, set target link URL and placement.</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.banners.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
        Cancel
      </a>
      <button type="submit" class="btn-primary text-xs sm:text-sm px-5 py-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ $editing ? 'Update Banner' : 'Save Banner' }}
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- LEFT COLUMN: FORM FIELDS (7 cols) --}}
    <div class="lg:col-span-7 space-y-6">

      {{-- 1. Placement Selection --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <div>
          <label class="lbl text-sm">Banner Placement &amp; Slot</label>
          <p class="text-xs text-gray-400 mb-3">Choose where this banner appears on the homepage.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
          {{-- Option A: Hero Carousel --}}
          <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card {{ $currentPlacement === 'hero' ? 'border-primary bg-primary/5 shadow-sm' : 'border-gray-200 hover:border-gray-300 bg-white' }}" id="label-placement-hero">
            <div class="flex items-center justify-between mb-1.5">
              <span class="font-bold text-sm text-gray-900">Hero Carousel Slide</span>
              <input type="radio" name="placement" value="hero" class="accent-primary h-4 w-4" @checked($currentPlacement === 'hero') onchange="updatePlacementUI('hero')">
            </div>
            <p class="text-xs text-gray-500">Main rotating banner slider (70% width on desktop, full width on mobile).</p>
            <span class="mt-3 inline-block text-[10px] font-bold text-primary tracking-wider uppercase">
              Rec: 1500 &times; 800 px (15:8)
            </span>
          </label>

          {{-- Option B: Promo Card --}}
          <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card {{ $currentPlacement === 'hero_side' ? 'border-amber-500 bg-amber-500/5 shadow-sm' : 'border-gray-200 hover:border-gray-300 bg-white' }}" id="label-placement-hero_side">
            <div class="flex items-center justify-between mb-1.5">
              <span class="font-bold text-sm text-gray-900">Featured Promo Card</span>
              <input type="radio" name="placement" value="hero_side" class="accent-amber-500 h-4 w-4" @checked($currentPlacement === 'hero_side') onchange="updatePlacementUI('hero_side')">
            </div>
            <p class="text-xs text-gray-500">Side promo cards (stacked 30% on desktop, 2-column grid on mobile).</p>
            <span class="mt-3 inline-block text-[10px] font-bold text-amber-600 tracking-wider uppercase">
              Rec: 868 &times; 476 px (1.82:1)
            </span>
          </label>
        </div>

        <input type="hidden" name="style" id="styleInput" value="{{ old('style', $banner->style ?? 'brand') }}">
      </div>

      {{-- 2. Banner Creative / Image --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
          <label class="lbl text-sm mb-0">Banner Image</label>
          <span class="text-xs text-gray-400 font-medium" id="sizeGuideline">Recommended: 1500 &times; 800 px</span>
        </div>

        {{-- Current Image Display --}}
        @if($banner->image)
          <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-200">
            <img src="{{ $banner->imageUrl() }}" class="h-16 w-28 rounded-lg object-cover border border-gray-200 bg-gray-100" alt="Current image" id="currentImageThumb">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-800 truncate">{{ basename($banner->image) }}</p>
              <p class="text-[11px] text-gray-400 mt-0.5">Currently saved banner image</p>
              <label class="inline-flex items-center gap-1.5 text-xs text-red-600 hover:text-red-700 mt-1 cursor-pointer">
                <input type="checkbox" name="remove_image" value="1" class="accent-red-600" id="removeImageCheck" onchange="toggleRemoveImage(this.checked)">
                Remove current image
              </label>
            </div>
          </div>
        @endif

        {{-- File Upload --}}
        <div>
          <label class="lbl text-xs text-gray-700 font-semibold">Upload new image file</label>
          <input name="image_file" type="file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer border border-gray-200 rounded-xl p-1.5 bg-white shadow-2xs" id="imageFileInput" onchange="previewSelectedFile(this)">
          <p class="text-[11px] text-gray-400 mt-1.5">Supports JPG, PNG, WebP up to 4MB.</p>
        </div>

        {{-- Direct URL / Path fallback --}}
        <div>
          <label class="lbl text-xs text-gray-700 font-semibold">&hellip;or enter an image path / URL</label>
          <input name="image_url" id="imageUrlInput" class="inp" value="{{ old('image_url', (str_starts_with($banner->image ?? '', 'http') || str_starts_with($banner->image ?? '', 'uploads/')) ? $banner->image : '') }}" placeholder="uploads/banners/filename.jpg or https://..." oninput="previewUrlInput(this.value)">
          <p class="text-[11px] text-gray-400 mt-1.5">Local path (e.g. <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">uploads/banners/banner_name.jpg</code>) or direct web URL (<code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">https://...</code>).</p>
        </div>
      </div>

      {{-- 3. Banner Title & Target Link URL --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <label class="lbl text-sm">Target Link &amp; Details</label>

        <div>
          <label class="lbl text-xs">Target Link URL</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </span>
            <input name="link_url" id="linkUrlInput" class="inp pl-9" value="{{ old('link_url', $banner->link_url) }}" placeholder="/shop or /product/slug or https://..." oninput="updateLinkPreview(this.value)">
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Where customers will be redirected when clicking this banner.</p>
        </div>

        <div>
          <label class="lbl text-xs">Banner Title / Name (For Admin &amp; Alt Text)</label>
          <input name="title" id="titleInput" class="inp" value="{{ old('title', $banner->title) }}" placeholder="e.g. iPhone 16 Pro Max Flagship Offer">
          <p class="text-[11px] text-gray-400 mt-1.5">Used as label in admin panel and image alt tag for SEO.</p>
        </div>
      </div>

      {{-- 4. Position & Active State --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4 items-center">
          <div>
            <label class="lbl text-xs">Sort Position</label>
            <input name="position" type="number" class="inp" value="{{ old('position', $banner->position ?? 0) }}" min="0" step="1">
            <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first (0, 1, 2...).</p>
          </div>

          <div class="pt-2 sm:pt-0">
            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" name="is_active" value="1" class="accent-primary h-4 w-4" @checked(old('is_active', $banner->is_active ?? true))>
              <div>
                <span class="text-xs font-bold text-gray-800 block">Visible on storefront</span>
                <span class="text-[11px] text-gray-400 block">Uncheck to temporarily hide</span>
              </div>
            </label>
          </div>
        </div>
      </div>

    </div>

    {{-- RIGHT COLUMN: LIVE IMAGE PREVIEW (5 cols) --}}
    <div class="lg:col-span-5 space-y-6">

      <div class="card p-5 sm:p-6 bg-white sticky top-6 shadow-sm border-gray-200 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Image Preview</h3>
          </div>
          <span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md" id="previewAspectBadge">15:8</span>
        </div>

        {{-- Preview Screen Container --}}
        <div class="rounded-2xl overflow-hidden bg-gray-900 border border-gray-800 shadow-sm relative group" id="previewContainer" style="aspect-ratio: 15/8;">
          <img src="{{ $banner->image ? $banner->imageUrl() : asset('uploads/banners/hero_slider_iphone_18_pro.jpg') }}" id="livePreviewImg" class="w-full h-full object-cover object-center transition-all duration-300" alt="Banner Preview">
          
          {{-- Empty state fallback when no image --}}
          <div id="noImagePlaceholder" class="absolute inset-0 bg-neutral-900 flex flex-col items-center justify-center p-6 text-center text-white/50 hidden">
            <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            <p class="text-xs font-semibold">No Image Selected</p>
            <p class="text-[11px] opacity-70 mt-0.5">Upload an image or enter a URL on the left</p>
          </div>
        </div>

        {{-- Target Link Info Box --}}
        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
          <span class="text-gray-500 font-medium">Target Link:</span>
          <span class="font-mono text-gray-700 truncate max-w-[200px]" id="previewLinkText">{{ $banner->link_url ?: '(default: /shop)' }}</span>
        </div>

        <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-100 text-blue-900 space-y-1">
          <p class="text-xs font-bold flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Design Guidelines
          </p>
          <p class="text-[11px] text-blue-800 leading-relaxed" id="placementGuidelinesText">
            Hero slides display in the main rotating carousel. Recommended size is <strong>1500 &times; 800 px</strong> (Aspect ratio 15:8).
          </p>
        </div>

      </div>

    </div>

  </div>
</form>

<script>
function updatePlacementUI(placement) {
  const cardHero = document.getElementById('label-placement-hero');
  const cardPromo = document.getElementById('label-placement-hero_side');
  const previewContainer = document.getElementById('previewContainer');
  const previewAspectBadge = document.getElementById('previewAspectBadge');
  const sizeGuideline = document.getElementById('sizeGuideline');
  const guidelinesText = document.getElementById('placementGuidelinesText');

  if (placement === 'hero') {
    cardHero.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-primary bg-primary/5 shadow-sm';
    cardPromo.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-gray-200 hover:border-gray-300 bg-white';
    previewContainer.style.aspectRatio = '15/8';
    previewAspectBadge.textContent = 'Aspect Ratio: 15/8';
    sizeGuideline.textContent = 'Recommended: 1500 × 800 px';
    guidelinesText.innerHTML = 'Hero slides display in the main rotating carousel. Recommended size is <strong>1500 &times; 800 px</strong> (Aspect ratio 15:8).';
  } else {
    cardPromo.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-amber-500 bg-amber-500/5 shadow-sm';
    cardHero.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-gray-200 hover:border-gray-300 bg-white';
    previewContainer.style.aspectRatio = '1.82/1';
    previewAspectBadge.textContent = 'Aspect Ratio: 1.82/1';
    sizeGuideline.textContent = 'Recommended: 868 × 476 px';
    guidelinesText.innerHTML = 'Featured Promo cards flank the hero carousel (stacked on desktop, 2-column grid on mobile). Recommended size is <strong>868 &times; 476 px</strong> (Aspect ratio 1.82:1).';
  }
}

function updateLinkPreview(val) {
  const previewLinkText = document.getElementById('previewLinkText');
  previewLinkText.textContent = val.trim() ? val.trim() : '(default: /shop)';
}

function previewSelectedFile(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById('livePreviewImg');
      img.src = e.target.result;
      img.classList.remove('hidden');
      document.getElementById('noImagePlaceholder').classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewUrlInput(url) {
  if (url && url.trim()) {
    const fullUrl = (url.startsWith('http') || url.startsWith('/')) ? url : ('/' + url);
    const img = document.getElementById('livePreviewImg');
    img.src = fullUrl;
    img.classList.remove('hidden');
    document.getElementById('noImagePlaceholder').classList.add('hidden');
  }
}

function toggleRemoveImage(checked) {
  const thumb = document.getElementById('currentImageThumb');
  if (thumb) {
    thumb.style.opacity = checked ? '0.3' : '1';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const checkedPlacement = document.querySelector('input[name="placement"]:checked')?.value || 'hero';
  updatePlacementUI(checkedPlacement);
});
</script>
@endsection
