@extends('layouts.admin')
@php $editing = $feature->exists; @endphp
@section('title', $editing ? 'Edit feature' : 'New feature')
@section('subtitle', $editing ? $feature->title : 'A trust badge for the homepage strip.')

@section('page-actions')
  <a href="{{ route('admin.features.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="featureForm" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Update feature' : 'Save feature' }}
  </button>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.features.update', $feature) : route('admin.features.store') }}" id="featureForm" class="max-w-2xl">
  @csrf
  @if($editing) @method('PUT') @endif

  <section class="panel p-4 sm:p-5 space-y-3.5">
    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Feature details</h2>
      <p class="text-xs text-gray-500 mt-0.5">Title, short line and icon shown on the homepage.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="sm:col-span-2">
        <label class="lbl" for="featureTitle">Title <span class="text-rose-500">*</span></label>
        <input name="title" id="featureTitle" class="inp" value="{{ old('title', $feature->title) }}" required placeholder="e.g. Free home delivery" />
      </div>
      <div>
        <label class="lbl" for="featurePosition">Position</label>
        <input name="position" id="featurePosition" type="number" class="inp" value="{{ old('position', $feature->position ?? 0) }}" placeholder="0" />
      </div>
    </div>

    <div>
      <label class="lbl" for="featureSubtitle">Subtitle</label>
      <input name="subtitle" id="featureSubtitle" class="inp" value="{{ old('subtitle', $feature->subtitle) }}" placeholder="e.g. On orders over ৳1,000 across BD" />
    </div>

    <div>
      <label class="lbl" for="featureIcon">Icon</label>
      <textarea name="icon" id="featureIcon" rows="3" class="inp font-mono text-xs" placeholder="An emoji, an SVG path like M12 3l8 4v5... or https://...">{{ old('icon', $feature->icon) }}</textarea>
      <p class="text-[11px] text-gray-500 mt-1">Paste an emoji, an SVG path (<code>M12 3l8 4v5...</code>) or an image URL (<code>https://...</code>).</p>
    </div>

    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 text-[13px] font-medium text-gray-800 cursor-pointer">
      <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $feature->is_active ?? true)) />
      <span>Show on the homepage</span>
    </label>
  </section>
</form>
@endsection
