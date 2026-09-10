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
        <p class="text-xs text-gray-500 mt-0.5">Configure placement, creative image, links, and display options.</p>
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
          <label class="lbl text-sm">Banner Placement &amp; Layout Slot</label>
          <p class="text-xs text-gray-400 mb-3">Choose where this banner appears on the storefront.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
          {{-- Option A: Hero Carousel --}}
          <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card {{ $currentPlacement === 'hero' ? 'border-primary bg-primary/5 shadow-sm' : 'border-gray-200 hover:border-gray-300 bg-white' }}" id="label-placement-hero">
            <div class="flex items-center justify-between mb-1.5">
              <span class="font-bold text-sm text-gray-900">Hero Carousel Slide</span>
              <input type="radio" name="placement" value="hero" class="accent-primary h-4 w-4" @checked($currentPlacement === 'hero') onchange="updatePlacementUI('hero')">
            </div>
            <p class="text-xs text-gray-500">Main rotating banner slider. Takes 70% width on desktop and full width on mobile.</p>
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
            <p class="text-xs text-gray-500">Side promo cards (stacked 30% on desktop, 2-column grid below slider on mobile).</p>
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
          <label class="lbl text-sm mb-0">Banner Creative Image</label>
          <span class="text-xs text-gray-400" id="sizeGuideline">Recommended: 1500 &times; 800 px</span>
        </div>

        {{-- Current Image Display --}}
        @if($banner->image)
          <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-200">
            <img src="{{ $banner->imageUrl() }}" class="h-16 w-28 rounded-lg object-cover border border-gray-200" alt="Current image" id="currentImageThumb">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-800 truncate">{{ basename($banner->image) }}</p>
              <p class="text-[11px] text-gray-400 mt-0.5">Currently saved image on server</p>
              <label class="inline-flex items-center gap-1.5 text-xs text-red-600 hover:text-red-700 mt-1 cursor-pointer">
                <input type="checkbox" name="remove_image" value="1" class="accent-red-600" id="removeImageCheck" onchange="toggleRemoveImage(this.checked)">
                Remove current image
              </label>
            </div>
          </div>
        @endif

        {{-- File Upload --}}
        <div>
          <label class="lbl text-xs text-gray-600">Upload new image file</label>
          <input name="image_file" type="file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer border border-gray-200 rounded-xl p-1 bg-white" id="imageFileInput" onchange="previewSelectedFile(this)">
          <p class="text-[11px] text-gray-400 mt-1">Supports JPG, PNG, WebP up to 4MB.</p>
        </div>

        {{-- Direct URL fallback --}}
        <div>
          <label class="lbl text-xs text-gray-600">&hellip;or enter an image path / URL</label>
          <input name="image_url" id="imageUrlInput" class="inp" value="{{ old('image_url', (str_starts_with($banner->image ?? '', 'http') || str_starts_with($banner->image ?? '', 'uploads/')) ? $banner->image : '') }}" placeholder="uploads/banners/filename.jpg or https://..." oninput="previewUrlInput(this.value)">
          <p class="text-[11px] text-gray-400 mt-1">Example: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">uploads/banners/hero_slider_iphone_18_pro.jpg</code></p>
        </div>
      </div>

      {{-- 3. Banner Texts & Link Details --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <label class="lbl text-sm">Content &amp; Destination</label>

        <div>
          <label class="lbl text-xs">Banner Title</label>
          <input name="title" id="titleInput" class="inp" value="{{ old('title', $banner->title) }}" placeholder="e.g. The New iPhone Era — iPhone 18 Pro Series" oninput="updatePreviewText()">
        </div>

        <div>
          <label class="lbl text-xs">Subtitle / Secondary Text (Optional)</label>
          <textarea name="subtitle" id="subtitleInput" class="inp" rows="2" placeholder="Brief promotional description or offer terms..." oninput="updatePreviewText()">{{ old('subtitle', $banner->subtitle) }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl text-xs">Badge Text (Optional)</label>
            <input name="badge" id="badgeInput" class="inp" value="{{ old('badge', $banner->badge) }}" placeholder="e.g. NEW LAUNCH, HOT DEAL" oninput="updatePreviewText()">
          </div>

          <div>
            <label class="lbl text-xs">Button CTA Text (Optional)</label>
            <input name="button_text" id="buttonTextInput" class="inp" value="{{ old('button_text', $banner->button_text) }}" placeholder="e.g. Pre-order Now, Buy Now" oninput="updatePreviewText()">
          </div>
        </div>

        <div>
          <label class="lbl text-xs">Target Link URL</label>
          <input name="link_url" id="linkUrlInput" class="inp" value="{{ old('link_url', $banner->link_url) }}" placeholder="/shop or /product/slug or https://..." oninput="updatePreviewText()">
          <p class="text-[11px] text-gray-400 mt-1">Where users are navigated when they click on the banner.</p>
        </div>
      </div>

      {{-- 4. Position & Active State --}}
      <div class="card p-5 sm:p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4 items-center">
          <div>
            <label class="lbl text-xs">Sort Position</label>
            <input name="position" type="number" class="inp" value="{{ old('position', $banner->position ?? 0) }}" min="0" step="1">
            <p class="text-[11px] text-gray-400 mt-1">0 is first. Higher numbers appear after.</p>
          </div>

          <div class="pt-2 sm:pt-0">
            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" name="is_active" value="1" class="accent-primary h-4 w-4" @checked(old('is_active', $banner->is_active ?? true))>
              <div>
                <span class="text-xs font-bold text-gray-800 block">Visible on storefront</span>
                <span class="text-[11px] text-gray-400 block">When unchecked, banner is hidden</span>
              </div>
            </label>
          </div>
        </div>
      </div>

    </div>

    {{-- RIGHT COLUMN: LIVE SIMULATION & QUICK PRESETS (5 cols) --}}
    <div class="lg:col-span-5 space-y-6">

      {{-- Live Storefront Simulation Card --}}
      <div class="card p-5 sm:p-6 bg-white sticky top-6 shadow-sm border-gray-200">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Live Preview</h3>
          </div>
          <span class="text-[11px] text-gray-400" id="previewAspectBadge">Aspect Ratio: 15/8</span>
        </div>

        {{-- Preview Screen Container --}}
        <div class="rounded-2xl overflow-hidden bg-gray-900 border border-gray-800 shadow-inner relative group" id="previewContainer" style="aspect-ratio: 15/8;">
          <img src="{{ $banner->image ? $banner->imageUrl() : asset('uploads/banners/hero_slider_iphone_18_pro.jpg') }}" id="livePreviewImg" class="w-full h-full object-cover object-center" alt="Preview">
          
          {{-- Overlay preview --}}
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent p-4 flex flex-col justify-end text-white pointer-events-none" id="livePreviewOverlay" style="display: {{ ($banner->badge || $banner->title) ? 'flex' : 'none' }};">
            <div id="liveBadge" class="w-fit px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary text-white mb-1.5 {{ $banner->badge ? '' : 'hidden' }}">
              {{ $banner->badge }}
            </div>
            <h4 id="liveTitle" class="font-bold text-sm leading-tight text-white drop-shadow-md">
              {{ $banner->title }}
            </h4>
            <p id="liveSubtitle" class="text-[11px] text-white/80 line-clamp-1 mt-0.5 {{ $banner->subtitle ? '' : 'hidden' }}">
              {{ $banner->subtitle }}
            </p>
            <div id="liveButton" class="mt-2 {{ $banner->button_text ? '' : 'hidden' }}">
              <span class="inline-block px-3 py-1 rounded-full bg-blue-600 text-white text-[10px] font-bold">
                {{ $banner->button_text }}
              </span>
            </div>
          </div>
        </div>

        <p class="text-[11px] text-gray-400 mt-2 text-center">
          Simulation of how this banner looks on the storefront.
        </p>

        {{-- Quick Presets Box --}}
        <div class="mt-6 pt-5 border-t border-gray-100">
          <div class="flex items-center gap-1.5 mb-2">
            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Quick Presets</h4>
          </div>
          <p class="text-xs text-gray-500 mb-3">Click to auto-populate banner fields with default templates:</p>

          <div class="space-y-1.5">
            <button type="button" class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all text-xs flex items-center justify-between group" onclick="applyPreset('iphone')">
              <div>
                <span class="font-bold text-gray-800 group-hover:text-primary block">iPhone 18 Pro Era</span>
                <span class="text-[10px] text-gray-400">Hero Slider (1500&times;800)</span>
              </div>
              <span class="text-primary text-xs font-semibold">&larr; Apply</span>
            </button>

            <button type="button" class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all text-xs flex items-center justify-between group" onclick="applyPreset('mac_mini')">
              <div>
                <span class="font-bold text-gray-800 group-hover:text-primary block">Mac Mini M6 Reinvented</span>
                <span class="text-[10px] text-gray-400">Hero Slider (1500&times;800)</span>
              </div>
              <span class="text-primary text-xs font-semibold">&larr; Apply</span>
            </button>

            <button type="button" class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all text-xs flex items-center justify-between group" onclick="applyPreset('samsung')">
              <div>
                <span class="font-bold text-gray-800 group-hover:text-primary block">Samsung Galaxy S26 Ultra</span>
                <span class="text-[10px] text-gray-400">Hero Slider (1500&times;800)</span>
              </div>
              <span class="text-primary text-xs font-semibold">&larr; Apply</span>
            </button>

            <button type="button" class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-amber-500/50 hover:bg-amber-500/5 transition-all text-xs flex items-center justify-between group" onclick="applyPreset('macbook')">
              <div>
                <span class="font-bold text-gray-800 group-hover:text-amber-600 block">MacBook Neo @ 85,999!</span>
                <span class="text-[10px] text-gray-400">Promo Card (868&times;476)</span>
              </div>
              <span class="text-amber-600 text-xs font-semibold">&larr; Apply</span>
            </button>

            <button type="button" class="w-full text-left p-2.5 rounded-xl border border-gray-200 hover:border-amber-500/50 hover:bg-amber-500/5 transition-all text-xs flex items-center justify-between group" onclick="applyPreset('airpods')">
              <div>
                <span class="font-bold text-gray-800 group-hover:text-amber-600 block">Pro Sound AirPods Pro @ 21,999TK</span>
                <span class="text-[10px] text-gray-400">Promo Card (868&times;476)</span>
              </div>
              <span class="text-amber-600 text-xs font-semibold">&larr; Apply</span>
            </button>
          </div>
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

  if (placement === 'hero') {
    cardHero.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-primary bg-primary/5 shadow-sm';
    cardPromo.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-gray-200 hover:border-gray-300 bg-white';
    previewContainer.style.aspectRatio = '15/8';
    previewAspectBadge.textContent = 'Aspect Ratio: 15/8';
    sizeGuideline.textContent = 'Recommended: 1500 × 800 px';
  } else {
    cardPromo.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-amber-500 bg-amber-500/5 shadow-sm';
    cardHero.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all placement-card border-gray-200 hover:border-gray-300 bg-white';
    previewContainer.style.aspectRatio = '1.82/1';
    previewAspectBadge.textContent = 'Aspect Ratio: 1.82/1';
    sizeGuideline.textContent = 'Recommended: 868 × 476 px';
  }
}

function updatePreviewText() {
  const title = document.getElementById('titleInput').value.trim();
  const subtitle = document.getElementById('subtitleInput').value.trim();
  const badge = document.getElementById('badgeInput').value.trim();
  const btn = document.getElementById('buttonTextInput').value.trim();

  const liveTitle = document.getElementById('liveTitle');
  const liveSubtitle = document.getElementById('liveSubtitle');
  const liveBadge = document.getElementById('liveBadge');
  const liveBtn = document.getElementById('liveButton');
  const liveOverlay = document.getElementById('livePreviewOverlay');

  liveTitle.textContent = title;
  liveSubtitle.textContent = subtitle;
  liveSubtitle.classList.toggle('hidden', !subtitle);
  liveBadge.textContent = badge;
  liveBadge.classList.toggle('hidden', !badge);
  if (liveBtn.querySelector('span')) liveBtn.querySelector('span').textContent = btn || 'Shop Now';
  liveBtn.classList.toggle('hidden', !btn);

  liveOverlay.style.display = (title || badge || subtitle || btn) ? 'flex' : 'none';
}

function previewSelectedFile(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('livePreviewImg').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewUrlInput(url) {
  if (url && url.trim()) {
    const fullUrl = (url.startsWith('http') || url.startsWith('/')) ? url : ('/' + url);
    document.getElementById('livePreviewImg').src = fullUrl;
  }
}

function toggleRemoveImage(checked) {
  const thumb = document.getElementById('currentImageThumb');
  if (thumb) {
    thumb.style.opacity = checked ? '0.3' : '1';
  }
}

const PRESETS = {
  iphone: {
    placement: 'hero',
    title: 'The New iPhone Era — iPhone 18 Pro Series & iPhone Duo',
    subtitle: 'Pre-order the most anticipated flagship smartphones with official warranty and EMI facilities.',
    badge: 'NEW LAUNCH',
    button_text: 'Pre-order Now',
    link_url: '/shop',
    image_url: 'uploads/banners/hero_slider_iphone_18_pro.jpg',
    position: 0
  },
  mac_mini: {
    placement: 'hero',
    title: 'The Mini Reinvented — Mac Mini M6 & M5 Pro',
    subtitle: 'Compact power redesigned for extreme performance. Up to 36 months EMI facility available.',
    badge: 'PRE-ORDER ONGOING',
    button_text: 'Pre-order Now',
    link_url: '/shop',
    image_url: 'uploads/banners/hero_slider_mac_mini_m6.jpg',
    position: 1
  },
  samsung: {
    placement: 'hero',
    title: 'Samsung Galaxy S26 Ultra',
    subtitle: 'Next level Galaxy AI, titanium craftsmanship and professional-grade quad camera setup.',
    badge: 'SPECIAL OFFER',
    button_text: 'Explore Galaxy',
    link_url: '/shop',
    image_url: 'uploads/banners/hero_slider_samsung_s26.jpg',
    position: 2
  },
  macbook: {
    placement: 'hero_side',
    title: 'MacBook Neo Only @ 85,999!',
    subtitle: 'Unbelievable lightness, all-day battery life, and vivid Liquid Retina display.',
    badge: 'HOT DEAL',
    button_text: 'Shop Now',
    link_url: '/shop',
    image_url: 'uploads/banners/hero_promo_macbook_neo.jpg',
    position: 0
  },
  airpods: {
    placement: 'hero_side',
    title: 'Pro Sound Only @ 21,999 TK — AirPods Pro (2nd Gen) USB-C',
    subtitle: 'Active Noise Cancellation, Adaptive Audio, and Personalized Spatial Audio.',
    badge: 'PRO SOUND',
    button_text: 'Buy Now',
    link_url: '/product/apple-airpods-pro-2nd-gen-usbc',
    image_url: 'uploads/banners/hero_promo_airpods_pro.png',
    position: 1
  }
};

function applyPreset(key) {
  const p = PRESETS[key];
  if (!p) return;

  const radio = document.querySelector(`input[name="placement"][value="${p.placement}"]`);
  if (radio) {
    radio.checked = true;
    updatePlacementUI(p.placement);
  }

  document.getElementById('titleInput').value = p.title;
  document.getElementById('subtitleInput').value = p.subtitle;
  document.getElementById('badgeInput').value = p.badge;
  document.getElementById('buttonTextInput').value = p.button_text;
  document.getElementById('linkUrlInput').value = p.link_url;
  document.getElementById('imageUrlInput').value = p.image_url;
  previewUrlInput(p.image_url);
  updatePreviewText();
}

document.addEventListener('DOMContentLoaded', () => {
  const checkedPlacement = document.querySelector('input[name="placement"]:checked')?.value || 'hero';
  updatePlacementUI(checkedPlacement);
  updatePreviewText();
});
</script>
@endsection
