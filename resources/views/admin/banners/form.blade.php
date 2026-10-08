@extends('layouts.admin')
@php
  $editing = $banner->exists;
  $currentPlacement = old('placement', $banner->placement ?? 'hero');
@endphp

@section('title', $editing ? 'Edit banner' : 'New banner')
@section('subtitle', $editing ? ($banner->title ?: 'Banner #' . $banner->id) : 'Upload a banner image, choose where it shows and where it links.')

@section('page-actions')
  <a href="{{ route('admin.banners.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="bannerForm" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Update banner' : 'Save banner' }}
  </button>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data" id="bannerForm">
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

    {{-- Form fields --}}
    <div class="lg:col-span-7 space-y-4">

      {{-- Placement --}}
      <section class="panel p-4 sm:p-5">
        <h2 class="text-[15px] font-semibold text-gray-900">Placement</h2>
        <p class="text-xs text-gray-500 mt-0.5">Choose where this banner appears on the homepage.</p>

        <div class="mt-3 grid sm:grid-cols-2 gap-2.5">
          <label class="relative flex flex-col p-3.5 rounded-xl ring-1 cursor-pointer transition-colors placement-card {{ $currentPlacement === 'hero' ? 'ring-2 ring-gray-900 bg-gray-50' : 'ring-gray-200 hover:bg-gray-50' }}" id="label-placement-hero">
            <span class="flex items-center justify-between gap-2">
              <span class="font-semibold text-[13px] text-gray-900">Carousel slide</span>
              <input type="radio" name="placement" value="hero" class="accent-primary h-4 w-4" @checked($currentPlacement === 'hero') onchange="updatePlacementUI('hero')">
            </span>
            <span class="mt-1 text-xs text-gray-500">Main rotating slider. Best size 1500 &times; 800 px (15:8).</span>
          </label>

          <label class="relative flex flex-col p-3.5 rounded-xl ring-1 cursor-pointer transition-colors placement-card {{ $currentPlacement === 'hero_side' ? 'ring-2 ring-gray-900 bg-gray-50' : 'ring-gray-200 hover:bg-gray-50' }}" id="label-placement-hero_side">
            <span class="flex items-center justify-between gap-2">
              <span class="font-semibold text-[13px] text-gray-900">Promo card</span>
              <input type="radio" name="placement" value="hero_side" class="accent-primary h-4 w-4" @checked($currentPlacement === 'hero_side') onchange="updatePlacementUI('hero_side')">
            </span>
            <span class="mt-1 text-xs text-gray-500">Cards beside the slider. Best size 868 &times; 476 px (1.82:1).</span>
          </label>
        </div>

        <input type="hidden" name="style" id="styleInput" value="{{ old('style', $banner->style ?? 'brand') }}">
      </section>

      {{-- Image --}}
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
          <h2 class="text-[15px] font-semibold text-gray-900">Image</h2>
          <span class="text-xs text-gray-500" id="sizeGuideline">Recommended: 1500 &times; 800 px</span>
        </div>

        @if($banner->image)
          <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-xl">
            <img src="{{ $banner->imageUrl() }}" class="h-14 w-24 rounded-lg object-cover bg-gray-100 shrink-0" alt="Current image" id="currentImageThumb">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-gray-800 truncate">{{ basename($banner->image) }}</p>
              <label class="inline-flex items-center gap-1.5 text-xs text-rose-700 mt-1 cursor-pointer">
                <input type="checkbox" name="remove_image" value="1" class="accent-red-600" id="removeImageCheck" onchange="toggleRemoveImage(this.checked)">
                Remove current image
              </label>
            </div>
          </div>
        @endif

        <div>
          <label class="lbl" for="imageFileInput">Upload image</label>
          <input name="image_file" type="file" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:h-8 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200 cursor-pointer rounded-xl border border-gray-200 p-1" id="imageFileInput" onchange="previewSelectedFile(this)">
          <p class="text-[11px] text-gray-500 mt-1">JPG, PNG or WebP up to 4MB.</p>
        </div>

        <div>
          <label class="lbl" for="imageUrlInput">Or image path / URL</label>
          <input name="image_url" id="imageUrlInput" class="inp" value="{{ old('image_url', (str_starts_with($banner->image ?? '', 'http') || str_starts_with($banner->image ?? '', 'uploads/')) ? $banner->image : '') }}" placeholder="uploads/banners/filename.jpg or https://..." oninput="previewUrlInput(this.value)">
          <p class="text-[11px] text-gray-500 mt-1">A local path like <code class="bg-gray-100 px-1 rounded">uploads/banners/name.jpg</code> or a full https:// link.</p>
        </div>
      </section>

      {{-- Link & title --}}
      <section class="panel p-4 sm:p-5 space-y-3.5">
        <h2 class="text-[15px] font-semibold text-gray-900">Link and title</h2>

        <div>
          <label class="lbl" for="linkUrlInput">Link URL</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
            </span>
            <input name="link_url" id="linkUrlInput" class="inp pl-9" value="{{ old('link_url', $banner->link_url) }}" placeholder="/shop or /product/slug or https://..." oninput="updateLinkPreview(this.value)">
          </div>
          <p class="text-[11px] text-gray-500 mt-1">Where customers go when they tap the banner.</p>
        </div>

        <div>
          <label class="lbl" for="titleInput">Title</label>
          <input name="title" id="titleInput" class="inp" value="{{ old('title', $banner->title) }}" placeholder="e.g. iPhone 16 Pro Max offer">
          <p class="text-[11px] text-gray-500 mt-1">Shown in the admin and used as the image alt text.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-3 items-start">
          <div>
            <label class="lbl" for="positionInput">Position</label>
            <input name="position" id="positionInput" type="number" class="inp" value="{{ old('position', $banner->position ?? 0) }}" min="0" step="1">
            <p class="text-[11px] text-gray-500 mt-1">Lower numbers show first.</p>
          </div>
          <label class="sm:mt-6 flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors">
            <input type="checkbox" name="is_active" value="1" class="accent-primary h-4 w-4" @checked(old('is_active', $banner->is_active ?? true))>
            <span>
              <span class="text-[13px] font-medium text-gray-800 block">Visible on store</span>
              <span class="text-[11px] text-gray-500 block">Untick to hide for now</span>
            </span>
          </label>
        </div>
      </section>

    </div>

    {{-- Live preview --}}
    <div class="lg:col-span-5">
      <section class="panel p-4 sm:p-5 lg:sticky lg:top-20 space-y-3">
        <div class="flex items-center justify-between gap-2">
          <h2 class="text-[15px] font-semibold text-gray-900">Preview</h2>
          <span class="text-[11px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full" id="previewAspectBadge">15:8</span>
        </div>

        <div class="rounded-xl overflow-hidden bg-gray-100 relative" id="previewContainer" style="aspect-ratio: 15/8;">
          <img src="{{ $banner->image ? $banner->imageUrl() : asset('favicon.png') }}" id="livePreviewImg" class="w-full h-full object-cover object-center" alt="Banner Preview">
          <div id="noImagePlaceholder" class="absolute inset-0 bg-gray-100 flex flex-col items-center justify-center p-6 text-center text-gray-500 hidden">
            <p class="text-xs font-medium">No image selected</p>
            <p class="text-[11px] mt-0.5">Upload an image or enter a URL</p>
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 text-xs">
          <span class="text-gray-500 shrink-0">Links to</span>
          <span class="font-mono text-gray-700 truncate min-w-0" id="previewLinkText">{{ $banner->link_url ?: '(default: /shop)' }}</span>
        </div>

        <p class="text-xs text-gray-500 leading-relaxed rounded-xl bg-gray-50 p-3" id="placementGuidelinesText">
          Hero slides display in the main rotating carousel. Recommended size is <strong>1500 &times; 800 px</strong> (Aspect ratio 15:8).
        </p>
      </section>
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
  const BASE = 'relative flex flex-col p-3.5 rounded-xl cursor-pointer transition-colors placement-card ';
  const ON = BASE + 'ring-2 ring-gray-900 bg-gray-50';
  const OFF = BASE + 'ring-1 ring-gray-200 hover:bg-gray-50';

  if (placement === 'hero') {
    cardHero.className = ON;
    cardPromo.className = OFF;
    previewContainer.style.aspectRatio = '15/8';
    previewAspectBadge.textContent = '15:8';
    sizeGuideline.textContent = 'Recommended: 1500 × 800 px';
    guidelinesText.innerHTML = 'Hero slides display in the main rotating carousel. Recommended size is <strong>1500 &times; 800 px</strong> (Aspect ratio 15:8).';
  } else {
    cardPromo.className = ON;
    cardHero.className = OFF;
    previewContainer.style.aspectRatio = '1.82/1';
    previewAspectBadge.textContent = '1.82:1';
    sizeGuideline.textContent = 'Recommended: 868 × 476 px';
    guidelinesText.innerHTML = 'Promo cards sit beside the hero carousel (stacked on desktop, 2-column grid on mobile). Recommended size is <strong>868 &times; 476 px</strong> (Aspect ratio 1.82:1).';
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
