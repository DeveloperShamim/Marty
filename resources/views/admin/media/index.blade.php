@extends('layouts.admin')

@section('title', 'Media Library & Image Optimizer — ShodeshiFood Admin')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>🖼️ Media Library & Image Optimizer</span>
      </h1>
      <p class="text-xs text-stone-500 mt-1">View, upload, inspect, compress/optimize images, copy links, and manage website assets</p>
    </div>

    <div class="flex items-center gap-2.5">
      <form method="POST" action="{{ route('admin.media.bulk-optimize') }}">
        @csrf
        <button type="submit" onclick="return confirm('Optimize all website images on disk now?')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition-all">
          <span>⚡ Optimize All Images</span>
        </button>
      </form>

      <button type="button" onclick="document.getElementById('uploadModal').classList.remove('hidden')"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-all">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span>Upload New Image</span>
      </button>
    </div>
  </div>

  {{-- Stats Bar --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Total Assets</p>
        <p class="text-2xl font-black text-stone-900 mt-0.5">{{ $totalCount }}</p>
      </div>
      <div class="h-10 w-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">📁</div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Storage Disk Usage</p>
        <p class="text-2xl font-black text-stone-900 mt-0.5">{{ $totalBytesHuman }}</p>
      </div>
      <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">💾</div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-xs flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Unused Media Files</p>
        <p class="text-2xl font-black text-stone-900 mt-0.5">{{ $unusedCount }}</p>
      </div>
      <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">🧹</div>
    </div>
  </div>

  {{-- Filters & Search Bar --}}
  <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
    <form method="GET" action="{{ route('admin.media.index') }}" class="flex flex-wrap items-center gap-2">
      <input type="hidden" name="type" value="{{ $currentFilter }}" />

      <a href="{{ route('admin.media.index', ['type' => 'all', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'all' ? 'bg-brand-600 text-white shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
        All Files ({{ $totalCount }})
      </a>

      <a href="{{ route('admin.media.index', ['type' => 'products', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'products' ? 'bg-brand-600 text-white shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
        Products
      </a>

      <a href="{{ route('admin.media.index', ['type' => 'branding', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'branding' ? 'bg-brand-600 text-white shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
        Logos & Branding
      </a>

      <a href="{{ route('admin.media.index', ['type' => 'categories', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'categories' ? 'bg-brand-600 text-white shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
        Categories
      </a>

      <a href="{{ route('admin.media.index', ['type' => 'banners', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'banners' ? 'bg-brand-600 text-white shadow-xs' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
        Hero Banners
      </a>

      <a href="{{ route('admin.media.index', ['type' => 'unused', 'search' => $currentSearch]) }}"
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $currentFilter === 'unused' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
        Unused Files ({{ $unusedCount }})
      </a>
    </form>

    <div class="flex items-center gap-2 w-full md:w-auto">
      <form method="GET" action="{{ route('admin.media.index') }}" class="relative flex-1 md:w-56">
        <input type="hidden" name="type" value="{{ $currentFilter }}" />
        <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Search image..."
               class="w-full pl-9 pr-3 py-2 text-xs font-medium rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
        <svg class="absolute left-3 top-2.5 h-4 w-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </form>

      <button type="button" id="bulkOptimizeBtn" onclick="submitBulkOptimize()" disabled
              class="px-3 py-2 rounded-xl bg-stone-100 text-stone-400 font-bold text-xs cursor-not-allowed transition-all shrink-0">
        ⚡ Optimize Selected
      </button>

      <button type="button" id="bulkDeleteBtn" onclick="submitBulkDelete()" disabled
              class="px-3 py-2 rounded-xl bg-stone-100 text-stone-400 font-bold text-xs cursor-not-allowed transition-all shrink-0">
        Bulk Delete
      </button>
    </div>
  </div>

  {{-- Media Grid --}}
  <form id="bulkForm" method="POST" action="">
    @csrf
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      @forelse($items as $item)
        <div class="group relative bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col">
          {{-- Checkbox --}}
          <div class="absolute top-2 left-2 z-20">
            <input type="checkbox" name="paths[]" value="{{ $item['relative_path'] }}" onchange="updateBulkBtnState()"
                   class="media-checkbox h-4 w-4 rounded border-stone-300 text-brand-600 focus:ring-brand-500 cursor-pointer shadow-sm" />
          </div>

          {{-- Extension Badge --}}
          <div class="absolute top-2 right-2 z-20 flex flex-col items-end gap-1">
            <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-stone-900/80 text-white backdrop-blur-xs">
              {{ $item['extension'] }}
            </span>
          </div>

          {{-- Image Thumbnail Container --}}
          <div class="relative aspect-square w-full bg-stone-50 p-2 flex items-center justify-center overflow-hidden group-hover:bg-stone-100/60 transition-colors">
            <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="max-h-full max-w-full object-contain drop-shadow-xs transition-transform duration-300 group-hover:scale-105" loading="lazy" />

            {{-- Hover Actions Overlay --}}
            <div class="absolute inset-0 bg-stone-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 p-2 backdrop-blur-[2px]">
              <button type="button" onclick="copyToClipboard('{{ $item['url'] }}')" title="Copy Image URL"
                      class="h-8 w-8 rounded-lg bg-white/90 text-stone-700 hover:bg-white flex items-center justify-center text-xs shadow-md transition-all">
                📋
              </button>
              <button type="button" onclick="triggerSingleOptimize('{{ $item['relative_path'] }}')" title="Compress & Optimize Image"
                      class="h-8 w-8 rounded-lg bg-amber-500 text-white hover:bg-amber-600 flex items-center justify-center text-xs shadow-md transition-all">
                ⚡
              </button>
              <button type="button" onclick="openPreviewModal('{{ $item['url'] }}', '{{ $item['filename'] }}', '{{ $item['size_human'] }}', '{{ $item['dimensions'] ?? 'N/A' }}', '{{ $item['used_by'] ?? 'Unused / Not Linked' }}', '{{ $item['relative_path'] }}')" title="Inspect Details"
                      class="h-8 w-8 rounded-lg bg-white/90 text-stone-700 hover:bg-white flex items-center justify-center text-xs shadow-md transition-all">
                🔍
              </button>
              <button type="button" onclick="confirmSingleDelete('{{ $item['relative_path'] }}', '{{ $item['filename'] }}')" title="Delete Image"
                      class="h-8 w-8 rounded-lg bg-rose-600 text-white hover:bg-rose-700 flex items-center justify-center text-xs shadow-md transition-all">
                🗑️
              </button>
            </div>
          </div>

          {{-- Card Details Footer --}}
          <div class="p-3 flex-1 flex flex-col justify-between text-left border-t border-stone-100 bg-white">
            <div>
              <p class="text-xs font-bold text-stone-900 truncate" title="{{ $item['filename'] }}">{{ $item['filename'] }}</p>
              <p class="text-[10px] text-stone-400 mt-0.5 font-mono">{{ $item['size_human'] }} • {{ $item['dimensions'] ?? 'N/A' }}</p>
            </div>

            <div class="mt-2.5">
              @if($item['is_used'])
                <span class="inline-block max-w-full truncate text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200" title="{{ $item['used_by'] }}">
                  ✓ {{ $item['used_by'] }}
                </span>
              @else
                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-stone-100 text-stone-500 border border-stone-200">
                  Unused
                </span>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-stone-200">
          <div class="text-4xl mb-3">🖼️</div>
          <h3 class="text-base font-bold text-stone-900">No media assets found</h3>
          <p class="text-xs text-stone-500 mt-1">Try adjusting your filter or upload a new image above.</p>
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
</form>

{{-- Upload Image Modal --}}
<div id="uploadModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden select-none">
  <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-stone-200 p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-extrabold text-stone-900">Upload New Media Asset</h3>
      <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 text-lg">✕</button>
    </div>

    <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div class="border-2 border-dashed border-stone-300 hover:border-brand-500 rounded-2xl p-6 text-center bg-stone-50 hover:bg-brand-50/20 transition-colors">
        <input type="file" name="image" required accept="image/*" class="w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer" />
        <p class="text-[11px] text-stone-400 mt-2">Supported Formats: PNG, JPG, WEBP, SVG (Max 5MB)</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-bold text-stone-600 hover:bg-stone-100">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md">Upload Image</button>
      </div>
    </form>
  </div>
</div>

{{-- Preview Lightbox Modal --}}
<div id="previewModal" class="fixed inset-0 z-50 bg-stone-900/80 backdrop-blur-xs flex items-center justify-center p-4 hidden select-none">
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-stone-200 overflow-hidden flex flex-col max-h-[90vh]">
    <div class="p-4 border-b border-stone-100 flex items-center justify-between bg-stone-50">
      <h3 id="previewTitle" class="text-sm font-extrabold text-stone-900 truncate">Image Details</h3>
      <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="text-stone-400 hover:text-stone-600 text-lg">✕</button>
    </div>

    <div class="p-6 overflow-y-auto flex flex-col items-center gap-4">
      <div class="bg-stone-100 rounded-xl p-4 w-full flex items-center justify-center max-h-80 border border-stone-200">
        <img id="previewImg" src="" alt="Preview" class="max-h-72 max-w-full object-contain" />
      </div>

      <div class="w-full space-y-2.5 text-xs text-left bg-stone-50 p-4 rounded-xl border border-stone-200">
        <div class="flex justify-between">
          <span class="text-stone-500 font-bold">File Size:</span>
          <span id="previewSize" class="font-mono text-stone-900 font-bold"></span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-500 font-bold">Dimensions:</span>
          <span id="previewDimensions" class="font-mono text-stone-900 font-bold"></span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-500 font-bold">Database Reference:</span>
          <span id="previewUsage" class="font-semibold text-brand-700"></span>
        </div>
      </div>
    </div>

    <div class="p-4 border-t border-stone-100 flex justify-between items-center bg-stone-50 gap-2">
      <div class="flex gap-2">
        <button type="button" id="previewOptimizeBtn" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md">
          ⚡ Compress & Optimize
        </button>
        <button type="button" id="previewDeleteBtn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
          Delete Image
        </button>
      </div>

      <button type="button" onclick="document.getElementById('previewModal').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-stone-200 text-stone-700 font-bold text-xs hover:bg-stone-300">
        Close
      </button>
    </div>
  </div>
</div>

<script>
  function updateBulkBtnState() {
    const checked = document.querySelectorAll('.media-checkbox:checked').length;
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const optimizeBtn = document.getElementById('bulkOptimizeBtn');

    if (checked > 0) {
      deleteBtn.disabled = false;
      deleteBtn.classList.remove('bg-stone-100', 'text-stone-400', 'cursor-not-allowed');
      deleteBtn.classList.add('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'shadow-md', 'cursor-pointer');
      deleteBtn.innerText = `Delete (${checked})`;

      optimizeBtn.disabled = false;
      optimizeBtn.classList.remove('bg-stone-100', 'text-stone-400', 'cursor-not-allowed');
      optimizeBtn.classList.add('bg-amber-500', 'hover:bg-amber-600', 'text-white', 'shadow-md', 'cursor-pointer');
      optimizeBtn.innerText = `⚡ Optimize (${checked})`;
    } else {
      deleteBtn.disabled = true;
      deleteBtn.classList.add('bg-stone-100', 'text-stone-400', 'cursor-not-allowed');
      deleteBtn.classList.remove('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'shadow-md', 'cursor-pointer');
      deleteBtn.innerText = 'Bulk Delete';

      optimizeBtn.disabled = true;
      optimizeBtn.classList.add('bg-stone-100', 'text-stone-400', 'cursor-not-allowed');
      optimizeBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600', 'text-white', 'shadow-md', 'cursor-pointer');
      optimizeBtn.innerText = '⚡ Optimize Selected';
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
    if (confirm(`Compress & optimize ${checked} selected image(s) now?`)) {
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

  function openPreviewModal(url, filename, size, dimensions, usage, relPath) {
    document.getElementById('previewImg').src = url;
    document.getElementById('previewTitle').innerText = filename;
    document.getElementById('previewSize').innerText = size;
    document.getElementById('previewDimensions').innerText = dimensions;
    document.getElementById('previewUsage').innerText = usage;

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
