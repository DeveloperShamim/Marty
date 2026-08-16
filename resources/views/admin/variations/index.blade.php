@extends('layouts.admin')
@section('title', 'Product Variations & Attributes')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Product Variations &amp; Attribute Presets</h2>
      <p class="text-xs text-slate-500 mt-1">Manage global variation categories (Weight, Volume, Packaging, Flavor, Size, Color) and standard option presets.</p>
    </div>

    {{-- Add New Attribute Type --}}
    <form method="POST" action="{{ route('admin.variations.types.store') }}" class="flex items-center gap-2">
      @csrf
      <input type="text" name="name" placeholder="New Type (e.g. Grade, Material)" required class="inp text-xs py-2 px-3 bg-white" />
      <button type="submit" class="btn-primary text-xs py-2 px-4 whitespace-nowrap">+ Add Type</button>
    </form>
  </div>

  @if(session('status'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold p-3.5 rounded-xl">
      {{ session('status') }}
    </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($attributeTypes as $type)
      <div class="card p-5 space-y-4 border border-slate-200 bg-white rounded-2xl shadow-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <h3 class="font-extrabold text-slate-900 text-base">{{ $type->name }}</h3>
          </div>
          <form method="POST" action="{{ route('admin.variations.types.destroy', $type) }}" onsubmit="return confirm('Delete attribute type {{ $type->name }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold px-2 py-1 rounded hover:bg-red-50 transition">Delete</button>
          </form>
        </div>

        {{-- Preset Value Badges --}}
        <div>
          <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Preset Option Values</label>
          <div class="flex flex-wrap gap-1.5 min-h-[42px]">
            @forelse($type->values as $val)
              <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 group">
                <span>{{ $val->value }}</span>
                <form method="POST" action="{{ route('admin.variations.values.destroy', $val) }}" class="inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-slate-400 hover:text-red-600 font-bold transition text-xs leading-none">✕</button>
                </form>
              </span>
            @empty
              <span class="text-xs text-slate-400 italic">No preset options yet.</span>
            @endforelse
          </div>
        </div>

        {{-- Quick Add Preset Option Form --}}
        <form method="POST" action="{{ route('admin.variations.values.store', $type) }}" class="pt-2 border-t border-slate-100 flex items-center gap-2">
          @csrf
          <input type="text" name="value" placeholder="Add option (e.g. 500g)..." required class="inp text-xs py-1.5 px-3 bg-slate-50 border-slate-200" />
          <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-lg transition whitespace-nowrap">+ Add</button>
        </form>
      </div>
    @empty
      <div class="col-span-full card p-10 text-center text-slate-400">
        No variation attribute types found. Use the input above to create one.
      </div>
    @endforelse
  </div>
</div>
@endsection
