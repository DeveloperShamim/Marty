@extends('layouts.admin')
@php $editing = $brand->exists; @endphp
@section('title', $editing ? 'Edit Brand: ' . $brand->name : 'Create New Brand')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-6">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <a href="{{ route('admin.brands.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 mb-1">
        &larr; Back to Brands List
      </a>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight">
        {{ $editing ? 'Edit Brand: ' . $brand->name : '➕ Add New Brand' }}
      </h1>
    </div>
    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">
      {{ $editing ? '💾 Update Brand Details' : '✨ Create Brand' }}
    </button>
  </div>

  <div class="max-w-4xl space-y-6">

    {{-- Main Brand Identity Card --}}
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <h2 class="text-base font-extrabold text-stone-900 border-b border-stone-100 pb-3 flex items-center gap-2">
        <span>🏷️ Brand Identity &amp; Details</span>
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-extrabold text-stone-800 block mb-1.5">Brand Name <span class="text-rose-500">*</span></label>
          <input name="name" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('name', $brand->name) }}" placeholder="e.g. Khaas Food, Naturals BD" required />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Slug (URL Keyword)</label>
          <input name="slug" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs font-mono text-stone-600" value="{{ old('slug', $brand->slug) }}" placeholder="e.g. khaas-food (auto-generated if empty)" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Official Website URL</label>
          <input name="website" type="url" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('website', $brand->website) }}" placeholder="https://www.brand.com" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Display Order Position</label>
          <input name="position" type="number" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('position', $brand->position ?? 0) }}" placeholder="0" />
        </div>
      </div>

      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1.5">Brand Description / Story</label>
        <textarea name="description" rows="3" class="w-full text-xs font-medium px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="Brief manufacturer background or brand philosophy...">{{ old('description', $brand->description) }}</textarea>
      </div>

      {{-- Images Upload Section --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-stone-100">
        {{-- Brand Logo --}}
        <div class="bg-stone-50 p-4 rounded-xl border border-stone-200/80 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">Brand Logo Image</label>
          <div class="flex flex-col sm:flex-row items-center gap-3">
            @if($brand->logo)
              <img src="{{ $brand->logoUrl() }}" class="h-14 w-14 object-contain rounded-xl border border-stone-200 bg-white p-1 shrink-0 shadow-2xs" alt="{{ $brand->name }}">
            @endif
            <input name="logo_file" type="file" accept="image/*" class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-extrabold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer" />
          </div>
          <p class="text-[10px] text-stone-400">PNG or WebP with transparent background recommended.</p>
        </div>

        {{-- Brand Banner --}}
        <div class="bg-stone-50 p-4 rounded-xl border border-stone-200/80 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">Hero Banner Image (Storefront Page)</label>
          <div class="flex flex-col sm:flex-row items-center gap-3">
            @if($brand->banner)
              <img src="{{ $brand->bannerUrl() }}" class="h-14 w-28 object-cover rounded-xl border border-stone-200 shrink-0 shadow-2xs" alt="{{ $brand->name }}">
            @endif
            <input name="banner_file" type="file" accept="image/*" class="w-full text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-extrabold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer" />
          </div>
          <p class="text-[10px] text-stone-400">Wide banner header displayed at the top of `/brand/{slug}` page.</p>
        </div>
      </div>

      {{-- Visibility Switches --}}
      <div class="space-y-2.5 pt-4 border-t border-stone-100 bg-stone-50/70 p-4 rounded-xl border border-stone-200">
        <label class="flex items-center gap-2.5 text-xs font-bold text-stone-800 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $brand->is_active ?? true)) /> 
          <span>Active (Visible on storefront brand filter &amp; catalog)</span>
        </label>
        <label class="flex items-center gap-2.5 text-xs font-extrabold text-amber-900 cursor-pointer">
          <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 accent-amber-600 rounded cursor-pointer" @checked(old('is_featured', $brand->is_featured ?? false)) /> 
          <span>★ Featured Brand (Display prominently on Homepage featured brand bar)</span>
        </label>
      </div>
    </div>

    {{-- SEO Settings Card --}}
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <h2 class="text-base font-extrabold text-stone-900 border-b border-stone-100 pb-3">🔍 Search Engine Optimization (SEO)</h2>
      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1">Meta Title Tag</label>
        <input name="meta_title" class="w-full text-xs font-medium px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('meta_title', $brand->meta_title) }}" placeholder="e.g. Buy Authentic Khaas Food Products Online in Bangladesh" />
      </div>
      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1">Meta Description</label>
        <textarea name="meta_description" rows="2" class="w-full text-xs font-medium px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="Explore 100% pure organic food items from Khaas Food with home delivery...">{{ old('meta_description', $brand->meta_description) }}</textarea>
      </div>
      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1">Meta Keywords</label>
        <textarea name="meta_keywords" rows="2" class="w-full text-xs font-medium px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" placeholder="khaas food, pure ghee, organic honey, shodeshifood">{{ old('meta_keywords', $brand->meta_keywords) }}</textarea>
      </div>
    </div>

  </div>
</form>
@endsection
