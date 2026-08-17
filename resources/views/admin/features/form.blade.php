@extends('layouts.admin')
@php $editing = $feature->exists; @endphp
@section('title', $editing ? 'Edit Feature: ' . $feature->title : 'Create New Feature')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.features.update', $feature) : route('admin.features.store') }}" class="space-y-6">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <a href="{{ route('admin.features.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 mb-1">
        &larr; Back to Features List
      </a>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight">
        {{ $editing ? 'Edit Feature: ' . $feature->title : '✨ Create New Trust Feature Badge' }}
      </h1>
    </div>
    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">
      {{ $editing ? '💾 Update Feature Badge' : '✨ Save Feature Badge' }}
    </button>
  </div>

  <div class="max-w-2xl space-y-6">

    {{-- Main Feature Card --}}
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <h2 class="text-base font-extrabold text-stone-900 border-b border-stone-100 pb-3 flex items-center gap-2">
        <span>✨ Feature Identity &amp; Icon</span>
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-extrabold text-stone-800 block mb-1.5">Feature Title <span class="text-rose-500">*</span></label>
          <input name="title" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('title', $feature->title) }}" required placeholder="e.g. Free Home Delivery" />
        </div>

        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Display Position Order</label>
          <input name="position" type="number" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('position', $feature->position ?? 0) }}" placeholder="0" />
        </div>
      </div>

      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1.5">Subtitle / Short Description</label>
        <input name="subtitle" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('subtitle', $feature->subtitle) }}" placeholder="e.g. On orders over ৳1,000 across BD" />
      </div>

      <div>
        <label class="text-xs font-extrabold text-stone-800 block mb-1.5">Icon (Emoji, SVG Path, or Image URL)</label>
        <textarea name="icon" rows="3" class="w-full text-xs font-mono font-semibold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="🚚 or M12 3l8 4v5... or https://...">{{ old('icon', $feature->icon) }}</textarea>
        <p class="text-[11px] text-stone-400 mt-1.5">Examples: Emoji (<code>🚚</code>, <code>🛡️</code>, <code>🔁</code>, <code>✨</code>), SVG path (<code>M12 3l8 4v5...</code>), or image URL (<code>https://...</code>)</p>
      </div>

      <div class="pt-3 border-t border-stone-100 bg-stone-50/70 p-4 rounded-xl border border-stone-200">
        <label class="flex items-center gap-2.5 text-xs font-extrabold text-stone-800 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $feature->is_active ?? true)) /> 
          <span>Active &amp; Visible (Show on homepage trust feature strip)</span>
        </label>
      </div>
    </div>

  </div>
</form>
@endsection
