@extends('layouts.admin')
@section('title', 'Product Variations & Attribute Presets')

@section('content')
<div class="space-y-5 max-w-full">

  <!-- Top Header Bar -->
  <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="space-y-1">
      <div class="flex items-center gap-2.5 flex-wrap">
        <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2.5">
          <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 border border-brand-200/80 flex items-center justify-center shrink-0 shadow-2xs">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
          </span>
          <span>Product Variations &amp; Attribute Presets</span>
        </h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-700 text-xs font-bold border border-stone-200 shrink-0">
          {{ $attributeTypes->count() }} Categories &bull; {{ $attributeTypes->sum(fn($t) => $t->values->count()) }} Options
        </span>
      </div>
      <p class="text-xs sm:text-sm text-stone-500">Manage reusable product attribute categories and predefined options for quick catalog creation.</p>
    </div>

    <!-- Create New Attribute Button -->
    <button type="button" onclick="openNewTypeModal()" class="px-4.5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-extrabold text-xs shadow-xs transition-all whitespace-nowrap cursor-pointer flex items-center justify-center gap-1.5 shrink-0 self-start sm:self-auto">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
      <span>New Attribute Category</span>
    </button>
  </div>

  @if(session('status'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-bold shadow-2xs flex items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="text-emerald-600 font-extrabold text-base">✓</span>
        <span>{{ session('status') }}</span>
      </div>
      <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-xs font-bold cursor-pointer">✕</button>
    </div>
  @endif

  <!-- Master-Detail Split Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
    
    <!-- LEFT COLUMN: Master Attribute List (4 / 12) -->
    <div class="lg:col-span-4 space-y-3">
      <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
        
        <!-- List Header & Search -->
        <div class="p-3.5 border-b border-stone-100 bg-stone-50/70 space-y-2.5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-extrabold text-stone-800 uppercase tracking-wider">Attribute Categories</span>
            <span class="px-2 py-0.5 rounded-md bg-stone-200/80 text-stone-700 text-[10px] font-mono font-bold">{{ $attributeTypes->count() }}</span>
          </div>
          <div class="relative">
            <svg class="w-3.5 h-3.5 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
            <input type="text" id="attributeSearchInput" placeholder="Search attributes..." onkeyup="filterAttributeList()" class="w-full text-xs font-semibold pl-8 pr-3 py-2 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" />
          </div>
        </div>

        <!-- Master Items List -->
        <div id="attributeItemsList" class="divide-y divide-stone-100 max-h-[600px] overflow-y-auto">
          @forelse($attributeTypes as $type)
            @php
              $isSelected = optional($selectedType)->id === $type->id;
              $slug = strtolower($type->slug);
              $nameLower = strtolower($type->name);
              $isColor = str_contains($nameLower, 'color') || str_contains($nameLower, 'colour');
            @endphp

            <a href="{{ route('admin.variations.index', ['selected' => $type->id]) }}" 
               class="attribute-list-item flex items-center justify-between p-3.5 transition-all {{ $isSelected ? 'bg-brand-50/80 border-l-4 border-brand-600 text-brand-950 font-bold' : 'hover:bg-stone-50/80 text-stone-700' }}"
               data-name="{{ strtolower($type->name) }}">
              
              <div class="flex items-center gap-3 min-w-0">
                <!-- Icon -->
                <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $isSelected ? 'bg-brand-100 text-brand-700 border border-brand-200' : 'bg-stone-100 text-stone-600 border border-stone-200/80' }}">
                  @if(str_contains($nameLower, 'weight') || str_contains($slug, 'weight'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-12L12 3 3 9m18 6l-9 6-9-6"/></svg>
                  @elseif(str_contains($nameLower, 'size') || str_contains($slug, 'size'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/></svg>
                  @elseif(str_contains($nameLower, 'volume') || str_contains($slug, 'liter') || str_contains($slug, 'ml'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5a3.75 3.75 0 01-7.5 0m7.5 0v2.25a3.75 3.75 0 01-7.5 0v-2.25m-7.5 0a3.75 3.75 0 007.5 0m-7.5 0v2.25a3.75 3.75 0 007.5 0v-2.25"/></svg>
                  @elseif($isColor)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M16.5 13.5L7.5 4.5m9 9l3.75 3.75M7.5 4.5L3.75 8.25m3.75-3.75l5.25 5.25"/></svg>
                  @elseif(str_contains($nameLower, 'pack') || str_contains($nameLower, 'box'))
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                  @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
                  @endif
                </span>

                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="text-sm font-bold truncate">{{ $type->name }}</span>
                    @if($type->is_active)
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    @else
                      <span class="w-1.5 h-1.5 rounded-full bg-stone-300 shrink-0"></span>
                    @endif
                  </div>
                  <span class="text-[10px] text-stone-400 font-mono block">slug: {{ $type->slug }}</span>
                </div>
              </div>

              <!-- Options count -->
              <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $isSelected ? 'bg-brand-200/80 text-brand-900' : 'bg-stone-100 text-stone-600' }}">
                  {{ $type->values->count() }}
                </span>
                <svg class="w-4 h-4 {{ $isSelected ? 'text-brand-600' : 'text-stone-300' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
              </div>
            </a>
          @empty
            <div class="p-8 text-center text-xs text-stone-400">
              No attribute types created yet.
            </div>
          @endforelse
        </div>

        <!-- Add Category Quick Action -->
        <div class="p-3 bg-stone-50 border-t border-stone-100">
          <button type="button" onclick="openNewTypeModal()" class="w-full py-2 px-3 rounded-xl border border-dashed border-stone-300 hover:border-brand-500 hover:bg-brand-50/50 text-stone-600 hover:text-brand-700 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Add New Category</span>
          </button>
        </div>

      </div>
    </div>

    <!-- RIGHT COLUMN: Selected Attribute Detail & Options Manager (8 / 12) -->
    <div class="lg:col-span-8 space-y-4">
      @if($selectedType)
        @php
          $nameLower = strtolower($selectedType->name);
          $slug = strtolower($selectedType->slug);
          $isColor = str_contains($nameLower, 'color') || str_contains($nameLower, 'colour');
        @endphp

        <!-- Selected Header Card -->
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-2xl bg-brand-50 border border-brand-200 text-brand-700 flex items-center justify-center shrink-0 shadow-2xs">
              @if(str_contains($nameLower, 'weight') || str_contains($slug, 'weight'))
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-12L12 3 3 9m18 6l-9 6-9-6"/></svg>
              @elseif(str_contains($nameLower, 'size') || str_contains($slug, 'size'))
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/></svg>
              @elseif(str_contains($nameLower, 'volume') || str_contains($slug, 'liter') || str_contains($slug, 'ml'))
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5a3.75 3.75 0 01-7.5 0m7.5 0v2.25a3.75 3.75 0 01-7.5 0v-2.25m-7.5 0a3.75 3.75 0 007.5 0m-7.5 0v2.25a3.75 3.75 0 007.5 0v-2.25"/></svg>
              @elseif($isColor)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M16.5 13.5L7.5 4.5m9 9l3.75 3.75M7.5 4.5L3.75 8.25m3.75-3.75l5.25 5.25"/></svg>
              @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
              @endif
            </span>

            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-lg font-extrabold text-stone-900">{{ $selectedType->name }}</h2>
                @if($selectedType->is_active)
                  <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Active</span>
                @else
                  <span class="px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 border border-stone-200 text-[10px] font-bold">Inactive</span>
                @endif
              </div>
              <p class="text-xs text-stone-400 font-mono">slug: {{ $selectedType->slug }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button type="button" onclick="openEditTypeModal({{ $selectedType->id }}, '{{ addslashes($selectedType->name) }}', {{ $selectedType->is_active ? 'true' : 'false' }})" class="btn-secondary text-xs px-3 py-2 flex items-center gap-1.5 bg-white border border-stone-200 text-stone-700 hover:bg-stone-50 shadow-2xs cursor-pointer">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
              <span>Rename</span>
            </button>

            <form method="POST" action="{{ route('admin.variations.types.destroy', $selectedType) }}" onsubmit="return confirm('Delete attribute category \'{{ addslashes($selectedType->name) }}\' and all presets?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-secondary text-xs px-3 py-2 flex items-center gap-1.5 bg-white border border-stone-200 text-rose-600 hover:bg-rose-50 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                <span>Delete</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Presets Options Manager Card -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-5 space-y-4">
          <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div>
              <h3 class="text-sm font-extrabold text-stone-900 flex items-center gap-2">
                <span>Configured Option Presets</span>
                <span class="px-2 py-0.5 rounded-full bg-stone-100 text-stone-700 font-mono text-[11px]">
                  {{ $selectedType->values->count() }} total
                </span>
              </h3>
              <p class="text-xs text-stone-400 mt-0.5">Click the ✕ on any option pill to remove it.</p>
            </div>
          </div>

          <!-- Option Chips Grid -->
          <div class="flex flex-wrap gap-2 min-h-[72px] p-3 bg-stone-50/70 rounded-xl border border-stone-100">
            @forelse($selectedType->values as $val)
              @php
                $valLower = strtolower(trim($val->value));
                $colorHex = match($valLower) {
                  'black' => '#09090b',
                  'white' => '#ffffff',
                  'brown' => '#78350f',
                  'natural gold', 'gold' => '#d97706',
                  'red' => '#dc2626',
                  'blue' => '#2563eb',
                  'green' => '#16a34a',
                  'yellow' => '#eab308',
                  'pink' => '#ec4899',
                  'purple' => '#9333ea',
                  'orange' => '#ea580c',
                  'grey', 'gray' => '#6b7280',
                  default => null,
                };
              @endphp

              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-white text-stone-800 border border-stone-200 shadow-2xs hover:border-stone-300 transition-colors group/opt">
                @if($isColor && $colorHex)
                  <span class="w-3.5 h-3.5 rounded-full border border-stone-300 shrink-0 shadow-2xs" style="background-color: {{ $colorHex }}"></span>
                @endif
                <span class="font-mono">{{ $val->value }}</span>
                <form method="POST" action="{{ route('admin.variations.values.destroy', $val) }}" class="inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-stone-300 group-hover/opt:text-rose-600 hover:scale-125 font-bold transition text-xs leading-none cursor-pointer" title="Delete option">✕</button>
                </form>
              </span>
            @empty
              <div class="w-full text-center py-6 text-xs text-stone-400 font-medium italic">
                No preset options added for {{ $selectedType->name }} yet. Use the form below to add options!
              </div>
            @endforelse
          </div>
        </div>

        <!-- Add New Preset Values Card (Single or Comma-separated Bulk) -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-5 space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-stone-700">Add Option Values to {{ $selectedType->name }}</h3>
            <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200/80">Supports Comma Separation</span>
          </div>

          <form method="POST" action="{{ route('admin.variations.values.store', $selectedType) }}" class="space-y-2">
            @csrf
            <div class="flex items-center gap-2">
              <input type="text" name="value" placeholder="e.g. 250g, 500g, 1kg, 2kg, 5kg" required class="flex-1 text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white shadow-2xs" />
              <button type="submit" class="px-5 py-2.5 bg-stone-900 hover:bg-brand-600 active:scale-95 text-white font-extrabold text-xs rounded-xl transition-all cursor-pointer shrink-0 shadow-2xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>+ Add Options</span>
              </button>
            </div>
            <p class="text-[11px] text-stone-400">💡 Separate multiple values with commas (e.g. <span class="font-mono text-stone-600">S, M, L, XL</span> or <span class="font-mono text-stone-600">250g, 500g, 1kg</span>) to add them all in one click.</p>
          </form>
        </div>

      @else
        <!-- Empty State When No Attribute Selected -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs p-12 text-center text-stone-400 space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-stone-100 border border-stone-200 flex items-center justify-center mx-auto text-stone-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
          </div>
          <h3 class="text-base font-extrabold text-stone-800">No Attribute Category Selected</h3>
          <p class="text-xs text-stone-500 max-w-sm mx-auto">Select an attribute category from the left column or create a new one to manage its presets.</p>
        </div>
      @endif
    </div>

  </div>

</div>

<!-- Modal 1: Create New Attribute Type Modal -->
<div id="newTypeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
  <div class="bg-white rounded-2xl border border-stone-200 shadow-2xl max-w-md w-full p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
    <div class="flex items-center justify-between border-b border-stone-100 pb-3">
      <h3 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-2">
        <span>✨</span> Create Attribute Category
      </h3>
      <button type="button" onclick="closeNewTypeModal()" class="text-stone-400 hover:text-stone-700 text-sm font-bold p-1 rounded-lg cursor-pointer">✕</button>
    </div>

    <form method="POST" action="{{ route('admin.variations.types.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-extrabold text-stone-700 uppercase tracking-wider mb-1">Category Name</label>
        <input type="text" name="name" placeholder="e.g. Weight, Size, Color, Flavor, Material" required class="w-full text-sm font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white" />
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
        <button type="button" onclick="closeNewTypeModal()" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50 cursor-pointer">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-xs cursor-pointer">Create Category</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Edit Attribute Type Modal -->
<div id="editTypeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-xs">
  <div class="bg-white rounded-2xl border border-stone-200 shadow-2xl max-w-md w-full p-5 space-y-4 animate-in fade-in zoom-in-95 duration-150">
    <div class="flex items-center justify-between border-b border-stone-100 pb-3">
      <h3 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-2">
        <span>✏️</span> Edit Attribute Category
      </h3>
      <button type="button" onclick="closeEditTypeModal()" class="text-stone-400 hover:text-stone-700 text-sm font-bold p-1 rounded-lg cursor-pointer">✕</button>
    </div>

    <form id="editTypeForm" method="POST" action="" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-xs font-extrabold text-stone-700 uppercase tracking-wider mb-1">Category Name</label>
        <input type="text" id="editTypeName" name="name" required class="w-full text-sm font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white" />
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" id="editTypeActive" name="is_active" value="1" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500 h-4 w-4" />
        <label for="editTypeActive" class="text-xs font-bold text-stone-700">Active (Visible in product catalog creation)</label>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
        <button type="button" onclick="closeEditTypeModal()" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50 cursor-pointer">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-xs cursor-pointer">Save Changes</button>
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
