@extends('layouts.admin')
@section('title', 'Variations')
@section('subtitle', 'Reusable options such as size, colour and weight for faster product setup.')

@section('page-actions')
  <button type="button" onclick="openNewTypeModal()" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    New attribute
  </button>
@endsection

@php
  // Small icon per attribute type, picked from its name.
  $typeIcon = function ($type) {
    $n = strtolower($type->name);
    $s = strtolower($type->slug);
    if (str_contains($n, 'weight') || str_contains($s, 'weight')) return '<path d="M12 3v18m9-12L12 3 3 9m18 6-9 6-9-6"/>';
    if (str_contains($n, 'size') || str_contains($s, 'size')) return '<path d="M3 6h18M3 12h18M3 18h18"/>';
    if (str_contains($n, 'volume') || str_contains($s, 'liter') || str_contains($s, 'ml')) return '<path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.7 3h10.6a2 2 0 0 0 1.7-3l-5-9V3"/><path d="M7 15h10"/>';
    if (str_contains($n, 'color') || str_contains($n, 'colour')) return '<circle cx="13.5" cy="6.5" r="1"/><circle cx="17.5" cy="10.5" r="1"/><circle cx="8.5" cy="7.5" r="1"/><circle cx="6.5" cy="12.5" r="1"/><path d="M12 2a10 10 0 0 0 0 20c.9 0 1.6-.7 1.6-1.6 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1 0-.9.7-1.6 1.6-1.6H16a6 6 0 0 0 6-6c0-4.9-4.5-8.6-10-8.6z"/>';
    if (str_contains($n, 'pack') || str_contains($n, 'box')) return '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>';
    return '<path d="M4 9h16M4 15h16M10 3v18M16 3v18"/>';
  };
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Phone: attribute switcher --}}
  <nav class="lg:hidden -mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Attributes">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($attributeTypes as $type)
        @php $isSelected = optional($selectedType)->id === $type->id; @endphp
        <a href="{{ route('admin.variations.index', ['selected' => $type->id]) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $isSelected ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
           @if($isSelected) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $type->name }}
          <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $isSelected ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $type->values->count() }}</span>
        </a>
      @endforeach
    </div>
  </nav>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

    {{-- Attribute list --}}
    <div class="hidden lg:block lg:col-span-4">
      <section class="card overflow-hidden">
        <div class="p-4 space-y-3">
          <div class="flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-gray-900">Attributes</h2>
            <span class="text-xs text-gray-500 tabular-nums">{{ $attributeTypes->count() }} types · {{ $attributeTypes->sum(fn($t) => $t->values->count()) }} options</span>
          </div>
          <label class="relative block">
            <span class="sr-only">Filter attributes</span>
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="attributeSearchInput" placeholder="Filter attributes" onkeyup="filterAttributeList()" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
          </label>
        </div>

        <div id="attributeItemsList" class="px-2 pb-2 space-y-0.5 max-h-[580px] overflow-y-auto">
          @forelse($attributeTypes as $type)
            @php $isSelected = optional($selectedType)->id === $type->id; @endphp
            <a href="{{ route('admin.variations.index', ['selected' => $type->id]) }}"
               class="attribute-list-item flex items-center justify-between gap-3 px-2.5 py-2 rounded-xl transition-colors {{ $isSelected ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-50 text-gray-700' }}"
               data-name="{{ strtolower($type->name) }}">
              <span class="flex items-center gap-3 min-w-0">
                <span class="w-8 h-8 rounded-xl grid place-items-center shrink-0 {{ $isSelected ? 'text-white' : 'bg-gray-100 text-gray-600' }}" @if($isSelected) style="background: var(--brand-dark);" @endif>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $typeIcon($type) !!}</svg>
                </span>
                <span class="min-w-0">
                  <span class="flex items-center gap-1.5">
                    <span class="text-[13px] font-semibold truncate">{{ $type->name }}</span>
                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $type->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}" title="{{ $type->is_active ? 'Active' : 'Inactive' }}"></span>
                  </span>
                  <span class="text-[11px] text-gray-400 font-mono block truncate">{{ $type->slug }}</span>
                </span>
              </span>
              <span class="min-w-[22px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums bg-white text-gray-600 shadow-sm shrink-0">{{ $type->values->count() }}</span>
            </a>
          @empty
            <div class="p-8 text-center text-sm text-gray-500">No attributes yet.</div>
          @endforelse
        </div>
      </section>
    </div>

    {{-- Selected attribute --}}
    <div class="lg:col-span-8 space-y-4">
      @if($selectedType)
        @php
          $nameLower = strtolower($selectedType->name);
          $isColor = str_contains($nameLower, 'color') || str_contains($nameLower, 'colour');
        @endphp

        <section class="panel p-4 sm:p-5 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-9 h-9 rounded-xl bg-gray-100 text-gray-700 grid place-items-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $typeIcon($selectedType) !!}</svg>
              </span>
              <div class="min-w-0">
                <h2 class="text-[15px] font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
                  {{ $selectedType->name }}
                  @if($selectedType->is_active)
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">Active</span>
                  @else
                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[11px] font-semibold">Inactive</span>
                  @endif
                </h2>
                <p class="text-xs text-gray-500 mt-0.5"><span class="font-mono">{{ $selectedType->slug }}</span> · {{ $selectedType->values->count() }} {{ Str::plural('option', $selectedType->values->count()) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              <button type="button" onclick="openEditTypeModal({{ $selectedType->id }}, '{{ addslashes($selectedType->name) }}', {{ $selectedType->is_active ? 'true' : 'false' }})" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                Rename
              </button>
              <form method="POST" action="{{ route('admin.variations.types.destroy', $selectedType) }}" onsubmit="return confirm('Delete attribute category \'{{ addslashes($selectedType->name) }}\' and all presets?')">
                @csrf @method('DELETE')
                <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                  Delete
                </button>
              </form>
            </div>
          </div>

          <div>
            <p class="text-xs text-gray-500 mb-2">
              @if($isColor)Tap a colour dot to set the exact shade customers see (a rainbow dot means none is set yet). Use the reset icon to go back to automatic, and the cross to remove an option.
              @else Use the cross on an option to remove it.
              @endif
            </p>
            <div class="flex flex-wrap gap-1.5 min-h-[56px] p-2.5 bg-gray-50 rounded-xl">
              @forelse($selectedType->values as $val)
                <span class="inline-flex items-center gap-1.5 h-8 pl-3 pr-1.5 rounded-full text-xs bg-white text-gray-800 shadow-sm group/opt">
                  @if($isColor)
                    {{-- Tap the dot to pick the exact shade; it saves straight away. --}}
                    @php $shade = $val->color_hex ?: \App\Support\ColorSwatch::fallback($val->value); @endphp
                    <form method="POST" action="{{ route('admin.variations.values.update', $val) }}" class="inline-flex items-center -ml-1.5">
                      @csrf @method('PATCH')
                      <label class="relative w-5 h-5 rounded-full shrink-0 cursor-pointer ring-1 ring-gray-300 overflow-hidden {{ $shade ? '' : 'bg-[conic-gradient(#f87171,#facc15,#4ade80,#60a5fa,#c084fc,#f87171)]' }}"
                             style="{{ $shade ? 'background-color: ' . $shade : '' }}" title="{{ $val->color_hex ? 'Your colour ' . $val->color_hex . ' (click to change)' : 'Pick the exact colour' }}">
                        <span class="sr-only">Colour for {{ $val->value }}</span>
                        <input type="color" name="color_hex" value="{{ $shade ?: '#888888' }}" class="absolute inset-0 opacity-0 cursor-pointer" onchange="this.form.submit()">
                      </label>
                    </form>
                    @if($val->color_hex)
                      <form method="POST" action="{{ route('admin.variations.values.update', $val) }}" class="inline-flex">
                        @csrf @method('PATCH')
                        <input type="hidden" name="color_hex" value="">
                        <button type="submit" class="text-gray-400 hover:text-gray-700 cursor-pointer" title="Back to the automatic colour" aria-label="Back to the automatic colour">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                      </form>
                    @endif
                  @endif
                  <span class="font-medium">{{ $val->value }}</span>
                  <form method="POST" action="{{ route('admin.variations.values.destroy', $val) }}" class="inline-flex">
                    @csrf @method('DELETE')
                    <button type="submit" class="h-5 w-5 grid place-items-center rounded-full text-gray-400 hover:bg-rose-50 hover:text-rose-600 cursor-pointer" title="Delete option" aria-label="Delete option {{ $val->value }}">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                  </form>
                </span>
              @empty
                <div class="w-full text-center py-4 text-sm text-gray-500">No options for {{ $selectedType->name }} yet. Add some below.</div>
              @endforelse
            </div>
          </div>
        </section>

        <section class="panel p-4 sm:p-5 space-y-3">
          <div>
            <h2 class="text-[15px] font-semibold text-gray-900">Add options</h2>
            <p class="text-xs text-gray-500 mt-0.5">Separate several values with commas, for example <span class="font-mono text-gray-700">S, M, L, XL</span> or <span class="font-mono text-gray-700">250g, 500g, 1kg</span>.</p>
          </div>
          <form method="POST" action="{{ route('admin.variations.values.store', $selectedType) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            @csrf
            <input type="text" name="value" placeholder="e.g. 250g, 500g, 1kg" required aria-label="New options for {{ $selectedType->name }}" class="flex-1 h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none" />
            <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer shrink-0" style="background: var(--brand-dark);">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
              Add options
            </button>
          </form>
        </section>

      @else
        <section class="panel p-10 text-center space-y-2">
          <span class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 grid place-items-center mx-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M4 9h16M4 15h16M10 3v18M16 3v18"/></svg>
          </span>
          <h2 class="text-[15px] font-semibold text-gray-900">No attribute selected</h2>
          <p class="text-xs text-gray-500 max-w-sm mx-auto">Pick an attribute from the list, or create a new one to manage its options.</p>
        </section>
      @endif
    </div>
  </div>
</div>

{{-- New attribute --}}
<div id="newTypeModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-4 bg-gray-900/50">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-[15px] font-semibold text-gray-900">New attribute</h2>
      <button type="button" onclick="closeNewTypeModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 grid place-items-center cursor-pointer" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" action="{{ route('admin.variations.types.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="lbl" for="newTypeName">Name</label>
        <input type="text" id="newTypeName" name="name" placeholder="e.g. Weight, Size, Color, Flavor" required class="inp" />
      </div>
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
        <button type="button" onclick="closeNewTypeModal()" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium cursor-pointer">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold cursor-pointer" style="background: var(--brand-dark);">Create attribute</button>
      </div>
    </form>
  </div>
</div>

{{-- Rename attribute --}}
<div id="editTypeModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-4 bg-gray-900/50">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-[15px] font-semibold text-gray-900">Edit attribute</h2>
      <button type="button" onclick="closeEditTypeModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 grid place-items-center cursor-pointer" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="editTypeForm" method="POST" action="" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="lbl" for="editTypeName">Name</label>
        <input type="text" id="editTypeName" name="name" required class="inp" />
      </div>
      <label for="editTypeActive" class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer">
        <input type="checkbox" id="editTypeActive" name="is_active" value="1" class="h-4 w-4 mt-0.5 rounded" />
        <span>
          <span class="block text-[13px] font-medium text-gray-900">Active</span>
          <span class="block text-xs text-gray-500">Available when you create products.</span>
        </span>
      </label>
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
        <button type="button" onclick="closeEditTypeModal()" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium cursor-pointer">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold cursor-pointer" style="background: var(--brand-dark);">Save changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Filter left list by search input
  function filterAttributeList() {
    const query = document.getElementById('attributeSearchInput').value.toLowerCase().trim();
    const items = document.querySelectorAll('.attribute-list-item');
    
    items.forEach(item => {
      const name = item.getAttribute('data-name') || '';
      if (query === '' || name.includes(query)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }

  // Modals Controller
  function openNewTypeModal() {
    document.getElementById('newTypeModal').classList.remove('hidden');
  }

  function closeNewTypeModal() {
    document.getElementById('newTypeModal').classList.add('hidden');
  }

  function openEditTypeModal(id, name, isActive) {
    const modal = document.getElementById('editTypeModal');
    const form = document.getElementById('editTypeForm');
    const nameInput = document.getElementById('editTypeName');
    const activeCheck = document.getElementById('editTypeActive');

    form.action = `/admin/variations/types/${id}`;
    nameInput.value = name;
    activeCheck.checked = isActive;

    modal.classList.remove('hidden');
  }

  function closeEditTypeModal() {
    document.getElementById('editTypeModal').classList.add('hidden');
  }
</script>
@endsection
