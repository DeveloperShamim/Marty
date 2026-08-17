@extends('layouts.admin')
@section('title', 'Product Variations & Attribute Presets')

@section('content')
<div class="space-y-6">

  {{-- Page Header & New Attribute Type Bar --}}
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>🌿 Product Variations &amp; Attribute Presets</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Manage global variation categories (Weight, Volume, Packaging, Flavor, Size, Color) and option presets</p>
    </div>

    {{-- Add New Attribute Type Form --}}
    <form method="POST" action="{{ route('admin.variations.types.store') }}" class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto shrink-0">
      @csrf
      <input type="text" name="name" placeholder="New Attribute (e.g. Grade, Material)" required class="w-full sm:w-60 text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" />
      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all whitespace-nowrap cursor-pointer">
        + Add Attribute Type
      </button>
    </form>
  </div>

  @if(session('status'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  {{-- Attribute Categories Grid --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($attributeTypes as $type)
      <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-2xs space-y-4 flex flex-col justify-between">
        <div class="space-y-4">
          <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div class="flex items-center gap-2 min-w-0">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
              <h3 class="font-extrabold text-stone-900 text-base truncate">{{ $type->name }}</h3>
              <span class="px-2 py-0.5 rounded-md bg-stone-100 text-stone-600 font-extrabold text-[10px] shrink-0">
                {{ $type->values->count() }}
              </span>
            </div>
            <form method="POST" action="{{ route('admin.variations.types.destroy', $type) }}" onsubmit="return confirm('Delete attribute category \'{{ addslashes($type->name) }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer">Delete</button>
            </form>
          </div>

          {{-- Preset Value Badges --}}
          <div>
            <label class="text-[10px] font-black uppercase tracking-wider text-stone-400 block mb-2 font-mono">Preset Option Values</label>
            <div class="flex flex-wrap gap-1.5 min-h-[42px]">
              @forelse($type->values as $val)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold bg-emerald-50 text-emerald-900 border border-emerald-200/90 group shadow-2xs">
                  <span>{{ $val->value }}</span>
                  <form method="POST" action="{{ route('admin.variations.values.destroy', $val) }}" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-emerald-500 hover:text-rose-600 font-bold transition text-xs leading-none cursor-pointer" title="Remove preset">✕</button>
                  </form>
                </span>
              @empty
                <span class="text-xs text-stone-400 italic">No preset options added yet.</span>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Quick Add Preset Option Form --}}
        <form method="POST" action="{{ route('admin.variations.values.store', $type) }}" class="pt-3 border-t border-stone-100 flex items-center gap-2">
          @csrf
          <input type="text" name="value" placeholder="Add option (e.g. 500g)..." required class="flex-1 text-xs font-bold px-3 py-2 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500" />
          <button type="submit" class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0">+ Add</button>
        </form>
      </div>
    @empty
      <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-stone-200 text-stone-400 font-bold">
        <div class="text-3xl mb-2">🌿</div>
        No variation attribute types found. Use the input form above to create your first attribute category!
      </div>
    @endforelse
  </div>

</div>
@endsection
