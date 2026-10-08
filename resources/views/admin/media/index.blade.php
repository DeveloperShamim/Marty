@extends('layouts.admin')

@section('title', 'Media library')
@section('subtitle', 'Upload, inspect and compress the images used across the store.')

@section('page-actions')
  <button type="button" onclick="document.getElementById('compressionControlPanel').classList.toggle('hidden')"
          class="pill-btn cursor-pointer" title="Adjust WebP compression percentage">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>
    <span>Quality <span id="btnQualityVal">{{ $compressionQuality }}</span>%</span>
  </button>

  <form method="POST" action="{{ route('admin.media.bulk-optimize') }}">
    @csrf
    <input type="hidden" name="quality" class="quality-input-field" value="{{ $compressionQuality }}" />
    <button type="submit" onclick="return confirm('Optimize all website images on disk now with selected compression percentage?')" class="pill-btn cursor-pointer">
      <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
      <span>Optimize all</span>
    </button>
  </form>

  <button type="button" onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    <span>Upload</span>
  </button>
@endsection

@section('content')
<div class="space-y-4">

  {{-- Stats --}}
  <div class="grid grid-cols-3 gap-3">
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Files</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $totalCount }}</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Disk used</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $totalBytesHuman }}</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Unused</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold tabular-nums {{ $unusedCount > 0 ? 'text-amber-700' : 'text-gray-900' }}">{{ $unusedCount }}</p>
    </div>
  </div>

  {{-- Compression quality (toggled from the header) --}}
  <section id="compressionControlPanel" class="hidden panel p-4 sm:p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Compression quality</h2>
        <p class="text-xs text-gray-500 mt-0.5">WebP quality used when optimizing. Lower means smaller files, 100% keeps full quality.</p>
      </div>
      <div class="flex items-center gap-1.5 shrink-0">
        <form method="POST" action="{{ route('admin.media.quality') }}" class="flex items-center gap-2">
          @csrf
          <input type="hidden" name="quality" id="savedQualityInput" value="{{ $compressionQuality }}" />
          <button type="submit" class="h-8 px-3 rounded-full text-white text-xs font-semibold" style="background: var(--brand-dark);">Save as default</button>
        </form>
        <button type="button" onclick="document.getElementById('compressionControlPanel').classList.add('hidden')" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 inline-flex items-center justify-center" aria-label="Close"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      <div class="md:col-span-8 space-y-2">
        <div class="flex items-center justify-between">
          <label for="qualityRangeSlider" class="text-xs font-medium text-gray-700">Target quality</label>
          <span class="text-sm font-semibold text-gray-900 tabular-nums"><span id="qualityPercentDisplay">{{ $compressionQuality }}</span>%</span>
        </div>
        <input type="range" id="qualityRangeSlider" min="10" max="100" step="5" value="{{ $compressionQuality }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-brand-600" />
        <div class="flex justify-between text-[11px] text-gray-400 tabular-nums">
          <span>10%</span>
          <span class="hidden sm:inline">50%</span>
          <span>75% recommended</span>
          <span>100%</span>
        </div>
      </div>

      <div class="md:col-span-4 bg-gray-50 p-3 rounded-xl text-xs space-y-0.5">
        <p id="qualityLevelLabel" class="font-semibold text-emerald-700">Recommended (balanced)</p>
        <p id="qualityLevelDesc" class="text-[11px] text-gray-500">Provides ~60% size reduction with crystal-clear visual quality.</p>
      </div>
    </div>
  </section>

  {{-- Filters --}}
  @php
    $mediaTabs = [
      'all' => ['All', $totalCount],
      'products' => ['Products', null],
      'branding' => ['Branding', null],
      'banners' => ['Banners', null],
      'categories' => ['Categories', null],
      'unused' => ['Unused', $unusedCount],
    ];
  @endphp
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5">
    <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Media type">
      <form method="GET" action="{{ route('admin.media.index') }}" class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
        <input type="hidden" name="type" value="{{ $currentFilter }}" />
        @foreach($mediaTabs as $type => [$label, $count])
          @php $active = $currentFilter === $type; @endphp
          <a href="{{ route('admin.media.index', ['type' => $type, 'search' => $currentSearch]) }}"
             class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
             @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
            {{ $label }}
            @if($count !== null)
              <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $count }}</span>
            @endif
          </a>
        @endforeach
      </form>
    </nav>

    <form method="GET" action="{{ route('admin.media.index') }}" class="flex items-center gap-2 lg:w-80">
      <input type="hidden" name="type" value="{{ $currentFilter }}" />
      <label class="relative flex-1">
        <span class="sr-only">Search images</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Search by file name"
               class="w-full h-10 pl-10 pr-4 rounded-full bg-white shadow-panel border border-transparent text-sm focus:border-gray-200 outline-none" />
      </label>
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
    </form>
  </div>

  {{-- File grid --}}
  <form id="bulkForm" method="POST" action="" class="panel p-3 sm:p-4">
    @csrf
    <input type="hidden" name="quality" class="quality-input-field" value="{{ $compressionQuality }}" />

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <label for="selectAllCheckbox" class="flex items-center gap-2 text-[13px] text-gray-700 cursor-pointer">
        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
               class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer" />
        Select all
      </label>

      <div class="flex items-center gap-1.5">
        <button type="button" id="bulkOptimizeBtn" disabled onclick="submitBulkOptimize()"
                class="h-8 px-3 rounded-full text-xs font-medium bg-gray-100 text-gray-400 cursor-not-allowed transition-colors">
          Optimize selected
        </button>
        <button type="button" id="bulkDeleteBtn" disabled onclick="submitBulkDelete()"
                class="h-8 px-3 rounded-full text-xs font-medium bg-gray-100 text-gray-400 cursor-not-allowed transition-colors">
          Bulk delete
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-2.5 sm:gap-3">
      @forelse($items as $item)
        <div class="relative rounded-2xl bg-gray-50 overflow-hidden flex flex-col">
          <div class="absolute top-2 left-2 z-10">
            <input type="checkbox" name="paths[]" value="{{ $item['relative_path'] }}" onchange="updateBulkBtnState()"
                   aria-label="Select {{ $item['filename'] }}"
                   class="media-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer" />
          </div>
          <span class="absolute top-2 right-2 z-10 text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded-full bg-white/90 text-gray-700">{{ $item['extension'] }}</span>

          <button type="button" onclick="openPreviewModal('{{ $item['url'] }}', '{{ addslashes($item['filename']) }}', '{{ $item['size_human'] }}', '{{ $item['dimensions'] ?? 'N/A' }}', '{{ addslashes($item['used_by'] ?? 'Unused / Not Linked') }}', '{{ addslashes($item['relative_path']) }}', '{{ addslashes($item['alt'] ?? '') }}', '{{ addslashes($item['color'] ?? '') }}')"
                  class="aspect-square w-full p-2 flex items-center justify-center overflow-hidden cursor-zoom-in" title="Inspect &amp; edit details">
            <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="max-h-full max-w-full object-contain" loading="lazy" />
          </button>

          <div class="px-2.5 pt-2 pb-2.5 bg-white m-1 mt-0 rounded-xl flex-1 flex flex-col gap-1.5">
            <div class="min-w-0">
              <p class="text-xs font-medium text-gray-900 truncate" title="{{ $item['filename'] }}">{{ $item['filename'] }}</p>
              <p class="text-[11px] text-gray-500 tabular-nums">{{ $item['size_human'] }} · {{ $item['dimensions'] ?? 'N/A' }}</p>
            </div>
            @if($item['is_used'])
              <span class="self-start max-w-full truncate text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700" title="{{ $item['used_by'] }}">{{ $item['used_by'] }}</span>
            @else
              <span class="self-start text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Unused</span>
            @endif
            <div class="mt-auto pt-1 flex items-center gap-1">
              <button type="button" onclick="copyToClipboard('{{ $item['url'] }}')" title="Copy image URL" aria-label="Copy image URL"
                      class="h-7 w-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
              <button type="button" onclick="triggerSingleOptimize('{{ $item['relative_path'] }}')" title="Compress &amp; optimize" aria-label="Compress and optimize"
                      class="h-7 w-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></button>
              <button type="button" onclick="openPreviewModal('{{ $item['url'] }}', '{{ addslashes($item['filename']) }}', '{{ $item['size_human'] }}', '{{ $item['dimensions'] ?? 'N/A' }}', '{{ addslashes($item['used_by'] ?? 'Unused / Not Linked') }}', '{{ addslashes($item['relative_path']) }}', '{{ addslashes($item['alt'] ?? '') }}', '{{ addslashes($item['color'] ?? '') }}')" title="Inspect &amp; edit details" aria-label="Inspect"
                      class="h-7 w-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
              <button type="button" onclick="confirmSingleDelete('{{ $item['relative_path'] }}', '{{ $item['filename'] }}')" title="Delete image" aria-label="Delete image"
                      class="ml-auto h-7 w-7 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 inline-flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-full py-12 text-center">
          <p class="text-sm font-medium text-gray-700">No media files found</p>
          <p class="text-xs text-gray-500 mt-1">Try another filter or upload a new image.</p>
        </div>
      @endforelse
    </div>
  </form>

</div>

{{-- Single Action Forms --}}
<form id="singleDeleteForm" method="POST" action="{{ route('admin.media.destroy') }}" class="hidden">
  @csrf
  @method('DELETE')
  <input type="hidden" name="relative_path" id="singleDeletePath" />
</form>

<form id="singleOptimizeForm" method="POST" action="{{ route('admin.media.optimize') }}" class="hidden">
  @csrf
  <input type="hidden" name="relative_path" id="singleOptimizePath" />
  <input type="hidden" name="quality" class="quality-input-field" value="{{ $compressionQuality }}" />
</form>

{{-- Upload Image Modal --}}
<div id="uploadModal" class="fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4 hidden select-none">
  <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-4 sm:p-5 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-[15px] font-semibold text-gray-900">Upload image</h3>
      <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 inline-flex items-center justify-center" aria-label="Close"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>

    <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
      @csrf
      <div class="border border-dashed border-gray-300 rounded-xl p-5 text-center bg-gray-50">
        <input type="file" name="image" required accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:h-8 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-200 file:text-gray-800 hover:file:bg-gray-300 cursor-pointer" />
        <p class="text-[11px] text-gray-500 mt-2">PNG, JPG, WebP or SVG up to 5MB.</p>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold" style="background: var(--brand-dark);">Upload</button>
      </div>
    </form>
  </div>
</div>

{{-- Preview Lightbox Modal --}}
<div id="previewModal" class="fixed inset-0 z-50 bg-gray-900/60 flex items-center justify-center p-3 sm:p-4 hidden select-none">
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
      <h3 id="previewTitle" class="text-[15px] font-semibold text-gray-900 truncate">Image details</h3>
      <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="h-8 w-8 shrink-0 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 inline-flex items-center justify-center" aria-label="Close"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
    </div>

    <div class="p-4 overflow-y-auto flex flex-col gap-3">
      <div class="bg-gray-100 rounded-xl p-3 w-full flex items-center justify-center max-h-72">
        <img id="previewImg" src="" alt="Preview" class="max-h-64 max-w-full object-contain" />
      </div>

      <dl class="w-full grid grid-cols-[auto,1fr] gap-x-4 gap-y-1.5 text-xs bg-gray-50 p-3 rounded-xl">
        <dt class="text-gray-500">File size</dt>
        <dd id="previewSize" class="text-gray-900 font-medium text-right tabular-nums"></dd>
        <dt class="text-gray-500">Dimensions</dt>
        <dd id="previewDimensions" class="text-gray-900 font-medium text-right tabular-nums"></dd>
        <dt class="text-gray-500">Used by</dt>
        <dd id="previewUsage" class="text-gray-900 font-medium text-right truncate"></dd>
      </dl>

      {{-- Image Metadata Editor Form --}}
      <form method="POST" action="{{ route('admin.media.metadata') }}" class="w-full flex flex-col gap-3 text-left">
        @csrf
        <input type="hidden" name="relative_path" id="previewMetaRelPath" />

        <div class="flex items-center justify-between gap-2">
          <span class="text-[13px] font-semibold text-gray-900">SEO and variation details</span>
          <button type="submit" class="h-8 px-3 rounded-full text-white text-xs font-semibold cursor-pointer" style="background: var(--brand-dark);">Save details</button>
        </div>

        <div class="grid sm:grid-cols-2 gap-2.5">
          <div>
            <label for="previewMetaAlt" class="lbl">Alt text</label>
            <input type="text" name="alt" id="previewMetaAlt" placeholder="e.g. Running Shoe - Size 42 (Black) — {{ site_name() }}" class="inp" />
          </div>
          <div>
            <label for="previewMetaColor" class="lbl">Variation tag</label>
            <input type="text" name="color" id="previewMetaColor" placeholder="e.g. 500g, 1kg, Glass Jar" class="inp" />
          </div>
        </div>
      </form>
    </div>

    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap justify-between items-center gap-2">
      <div class="flex gap-1.5">
        <button type="button" id="previewOptimizeBtn" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Optimize</button>
        <button type="button" id="previewDeleteBtn" class="h-9 px-3.5 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-[13px] font-medium">Delete</button>
      </div>
      <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Close</button>
    </div>
  </div>
</div>

<script>
  // Slider & Quality Sync Script
  (function() {
    const slider = document.getElementById('qualityRangeSlider');
    const display = document.getElementById('qualityPercentDisplay');
    const savedInput = document.getElementById('savedQualityInput');
    const label = document.getElementById('qualityLevelLabel');
    const desc = document.getElementById('qualityLevelDesc');

    if (!slider) return;

    function updateQualityProfile(val) {
      if (display) display.innerText = val;
      if (savedInput) savedInput.value = val;

      const btnVal = document.getElementById('btnQualityVal');
      if (btnVal) btnVal.innerText = val;

      document.querySelectorAll('.quality-input-field').forEach(input => {
        input.value = val;
      });

      if (val <= 40) {
        if (label) { label.innerText = 'Ultra compact (max savings)'; label.className = 'font-semibold text-amber-700'; }
        if (desc) desc.innerText = 'Provides ~80% size reduction. Ideal for slow mobile connections.';
      } else if (val <= 65) {
        if (label) { label.innerText = 'High compression (fast loading)'; label.className = 'font-semibold text-emerald-700'; }
        if (desc) desc.innerText = 'Provides ~70% size reduction with sharp overall visuals.';
      } else if (val <= 85) {
        if (label) { label.innerText = 'Recommended (balanced)'; label.className = 'font-semibold text-emerald-700'; }
        if (desc) desc.innerText = 'Provides ~60% size reduction with crystal-clear visual quality.';
      } else {
        if (label) { label.innerText = 'High quality (near lossless)'; label.className = 'font-semibold text-sky-700'; }
        if (desc) desc.innerText = 'Provides ~30% size reduction with ultra HD studio clarity.';
      }
    }

    slider.addEventListener('input', function() {
      updateQualityProfile(this.value);
    });

    updateQualityProfile(slider.value);
  })();

  function toggleSelectAll(checkbox) {
    document.querySelectorAll('.media-checkbox').forEach(cb => {
      cb.checked = checkbox.checked;
    });
    updateBulkBtnState();
  }

  function updateBulkBtnState() {
    const checked = document.querySelectorAll('.media-checkbox:checked').length;
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const optimizeBtn = document.getElementById('bulkOptimizeBtn');

    if (checked > 0) {
      deleteBtn.disabled = false;
      deleteBtn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
      deleteBtn.classList.add('bg-rose-50', 'hover:bg-rose-100', 'text-rose-700', 'cursor-pointer');
      deleteBtn.innerText = `Delete (${checked})`;

      optimizeBtn.disabled = false;
      optimizeBtn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
      optimizeBtn.classList.add('bg-gray-900', 'hover:bg-gray-800', 'text-white', 'cursor-pointer');
      optimizeBtn.innerText = `Optimize (${checked})`;
    } else {
      deleteBtn.disabled = true;
      deleteBtn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
      deleteBtn.classList.remove('bg-rose-50', 'hover:bg-rose-100', 'text-rose-700', 'cursor-pointer');
      deleteBtn.innerText = 'Bulk delete';

      optimizeBtn.disabled = true;
      optimizeBtn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
      optimizeBtn.classList.remove('bg-gray-900', 'hover:bg-gray-800', 'text-white', 'cursor-pointer');
      optimizeBtn.innerText = 'Optimize selected';
    }
  }

  function submitBulkDelete() {
    const checked = document.querySelectorAll('.media-checkbox:checked').length;
    if (checked === 0) return;
    if (confirm(`Are you sure you want to permanently delete ${checked} selected media file(s)?`)) {
      const form = document.getElementById('bulkForm');
      form.action = "{{ route('admin.media.bulk-delete') }}";
      form.submit();
    }
  }

  function submitBulkOptimize() {
    const checked = document.querySelectorAll('.media-checkbox:checked').length;
    if (checked === 0) return;
    const quality = document.getElementById('qualityRangeSlider')?.value || 80;
    if (confirm(`Compress & optimize ${checked} selected image(s) at ${quality}% quality now?`)) {
      const form = document.getElementById('bulkForm');
      form.action = "{{ route('admin.media.bulk-optimize') }}";
      form.submit();
    }
  }

  function triggerSingleOptimize(relPath) {
    document.getElementById('singleOptimizePath').value = relPath;
    document.getElementById('singleOptimizeForm').submit();
  }

  function confirmSingleDelete(relPath, filename) {
    if (confirm(`Are you sure you want to delete "${filename}"?`)) {
      document.getElementById('singleDeletePath').value = relPath;
      document.getElementById('singleDeleteForm').submit();
    }
  }

  function copyToClipboard(url) {
    navigator.clipboard.writeText(url).then(() => {
      alert('Image URL copied to clipboard!\n' + url);
    });
  }

  function openPreviewModal(url, filename, size, dimensions, usage, relPath, altText, colorTag) {
    document.getElementById('previewImg').src = url;
    document.getElementById('previewTitle').innerText = filename;
    document.getElementById('previewSize').innerText = size;
    document.getElementById('previewDimensions').innerText = dimensions;
    document.getElementById('previewUsage').innerText = usage;

    document.getElementById('previewMetaRelPath').value = relPath || '';
    document.getElementById('previewMetaAlt').value = altText || '';
    document.getElementById('previewMetaColor').value = colorTag || '';

    document.getElementById('previewDeleteBtn').onclick = function() {
      confirmSingleDelete(relPath, filename);
    };

    document.getElementById('previewOptimizeBtn').onclick = function() {
      triggerSingleOptimize(relPath);
    };

    document.getElementById('previewModal').classList.remove('hidden');
  }
</script>
@endsection
