@extends('layouts.admin')
@php $editing = $brand->exists; @endphp
@section('title', $editing ? 'Edit Brand: ' . $brand->name : 'Create Brand')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-5xl">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Header with Breadcrumbs & Action Buttons --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <a href="{{ route('admin.brands.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 transition-colors">
        <span>&larr;</span> Back to brands
      </a>
      <h1 class="text-lg sm:text-xl font-extrabold mt-1 text-stone-900 tracking-tight">
        {{ $editing ? 'Edit Brand: ' . $brand->name : 'Create New Brand' }}
      </h1>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto w-full sm:w-auto">
      <a href="{{ route('admin.brands.index') }}" class="flex-1 sm:flex-none text-center px-4 py-2.5 text-xs font-bold rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-700 transition shadow-2xs">
        Cancel
      </a>
      <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition shadow-sm cursor-pointer">
        {{ $editing ? 'Save Changes' : 'Publish Brand' }}
      </button>
    </div>
  </div>

  {{-- 2-Column Responsive Layout (Mobile: 1-col, Desktop: 12-col) --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
    
    {{-- Main Left Column (8 cols on desktop) --}}
    <div class="lg:col-span-8 space-y-5">
      
      {{-- General Details Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-2xs space-y-4">
        <h3 class="font-black text-stone-900 text-sm sm:text-base border-b border-stone-100 pb-3">
          Brand Information
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">Brand Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" class="w-full px-3.5 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl font-bold text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('name', $brand->name) }}" placeholder="e.g. Khaas Food, Naturals BD" required />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">URL Slug (leave blank to auto-generate)</label>
            <input type="text" name="slug" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl font-mono text-stone-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('slug', $brand->slug) }}" placeholder="khaas-food" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">Official Website URL</label>
            <input type="url" name="website" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('website', $brand->website) }}" placeholder="https://www.brandwebsite.com" />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">Display Position / Sort Order</label>
            <input type="number" name="position" class="w-full px-3.5 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl font-mono text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('position', $brand->position ?? 0) }}" />
          </div>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Brand Story &amp; Description</label>
          <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Brief summary of the brand manufacturer and origins...">{{ old('description', $brand->description) }}</textarea>
        </div>
      </div>

      {{-- SEO Metadata Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3">
          <h3 class="font-black text-stone-900 text-sm sm:text-base">Search Engine Optimization (SEO)</h3>
          <p class="text-xs text-stone-400">Optimize meta tags for Google indexing on `/brand/{slug}` page.</p>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Title</label>
          <input type="text" name="meta_title" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('meta_title', $brand->meta_title) }}" placeholder="Brand Products Online | Store Name" />
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Description</label>
          <textarea name="meta_description" rows="2" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Discover authentic items from this manufacturer with fast home delivery...">{{ old('meta_description', $brand->meta_description) }}</textarea>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Keywords (Comma separated)</label>
          <input type="text" name="meta_keywords" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('meta_keywords', $brand->meta_keywords) }}" placeholder="brand name, authentic goods, bangladesh" />
        </div>
      </div>

    </div>

    {{-- Sidebar Column (4 cols on desktop) --}}
    <div class="lg:col-span-4 space-y-5">
      
      {{-- Visibility & Storefront Settings Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 shadow-2xs space-y-4">
        <h3 class="font-black text-stone-900 text-sm sm:text-base border-b border-stone-100 pb-3">
          Display & Visibility
        </h3>

        <div class="space-y-3">
          <label class="flex items-start gap-3 p-3 rounded-xl bg-stone-50 border border-stone-200 cursor-pointer hover:bg-stone-100 transition-colors">
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 mt-0.5 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $brand->is_active ?? true)) />
            <div>
              <span class="block text-xs font-black text-stone-900">Active Status</span>
              <span class="block text-[11px] text-stone-500">Visible in storefront brand filters and catalog lists.</span>
            </div>
          </label>

          <label class="flex items-start gap-3 p-3 rounded-xl bg-amber-50/70 border border-amber-200 cursor-pointer hover:bg-amber-50 transition-colors">
            <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 mt-0.5 accent-brand-600 rounded cursor-pointer" @checked(old('is_featured', $brand->is_featured ?? false)) />
            <div>
              <span class="block text-xs font-black text-amber-950">★ Featured Brand</span>
              <span class="block text-[11px] text-amber-800/90">Displays prominently in the homepage Featured Brands bar.</span>
            </div>
          </label>
        </div>
      </div>

      {{-- Brand Logo Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 shadow-2xs space-y-3">
        <h3 class="font-black text-stone-900 text-sm sm:text-base border-b border-stone-100 pb-2.5">
          Brand Logo
        </h3>

        @if($brand->logo)
          <div class="h-20 rounded-xl border border-stone-200 bg-stone-50 p-2 flex items-center justify-center">
            <img src="{{ $brand->logoUrl() }}" class="max-h-full max-w-full object-contain" alt="{{ $brand->name }}" />
          </div>
        @endif

        <div class="space-y-1.5">
          <label class="block text-xs font-bold text-stone-700">Upload Logo</label>
          <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-800 hover:file:bg-stone-200 cursor-pointer" />
          <p class="text-[10px] text-stone-400">Square or transparent PNG/WebP recommended.</p>
        </div>
      </div>

      {{-- Brand Hero Banner Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 shadow-2xs space-y-3">
        <h3 class="font-black text-stone-900 text-sm sm:text-base border-b border-stone-100 pb-2.5">
          Storefront Hero Banner
        </h3>

        @if($brand->banner)
          <div class="rounded-xl border border-stone-200 bg-stone-50 aspect-[21/9] overflow-hidden flex items-center justify-center">
            <img src="{{ $brand->bannerUrl() }}" class="w-full h-full object-cover" alt="{{ $brand->name }}" />
          </div>
        @endif

        <div class="space-y-1.5">
          <label class="block text-xs font-bold text-stone-700">Upload Banner Image</label>
          <input type="file" name="banner_file" accept="image/*" class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-800 hover:file:bg-stone-200 cursor-pointer" />
          <p class="text-[10px] text-stone-400">Wide header graphic displayed at `/brand/{slug}` page.</p>
        </div>
      </div>

    </div>

  </div>
</form>
@endsection
