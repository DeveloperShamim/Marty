@extends('layouts.admin')
@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit Category' : 'Create Category')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-5xl">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Header with Breadcrumbs & Action Buttons --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 transition-colors">
        <span>&larr;</span> Back to categories
      </a>
      <h1 class="text-lg sm:text-xl font-extrabold mt-1 text-stone-900 tracking-tight">
        {{ $editing ? 'Edit Category: ' . $category->name : 'Create New Category' }}
      </h1>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto w-full sm:w-auto">
      <a href="{{ route('admin.categories.index') }}" class="flex-1 sm:flex-none text-center px-4 py-2.5 text-xs font-bold rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-700 transition shadow-2xs">
        Cancel
      </a>
      <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition shadow-sm cursor-pointer">
        {{ $editing ? 'Save Changes' : 'Publish Category' }}
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
          Category Information
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
          <div class="sm:col-span-9 space-y-1">
            <label class="block text-xs font-bold text-stone-700">Category Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" class="w-full px-3.5 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl font-bold text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('name', $category->name) }}" placeholder="e.g. Organic Foods, Mustard Oil" required />
          </div>

          <div class="sm:col-span-3 space-y-1">
            <label class="block text-xs font-bold text-stone-700">Icon / Emoji</label>
            <input type="text" name="icon" class="w-full px-3.5 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl font-bold text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition text-center" value="{{ old('icon', $category->icon) }}" placeholder="🍯" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">URL Slug (leave blank to auto-generate)</label>
            <input type="text" name="slug" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl font-mono text-stone-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('slug', $category->slug) }}" placeholder="organic-foods" />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-stone-700">Display Position / Sort Order</label>
            <input type="number" name="position" class="w-full px-3.5 py-2.5 text-sm bg-stone-50 border border-stone-200 rounded-xl font-mono text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('position', $category->position ?? 0) }}" />
          </div>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Category Description</label>
          <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Brief summary of items in this category...">{{ old('description', $category->description) }}</textarea>
        </div>
      </div>

      {{-- SEO Metadata Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3">
          <h3 class="font-black text-stone-900 text-sm sm:text-base">Search Engine Optimization (SEO)</h3>
          <p class="text-xs text-stone-400">Optimize meta tags for Google indexing and search rankings.</p>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Title</label>
          <input type="text" name="meta_title" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Category Title | Store Name" />
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Description</label>
          <textarea name="meta_description" rows="2" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" placeholder="Short description for search engine snippets...">{{ old('meta_description', $category->meta_description) }}</textarea>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-stone-700">Meta Keywords (Comma separated)</label>
          <input type="text" name="meta_keywords" class="w-full px-3.5 py-2.5 text-xs bg-stone-50 border border-stone-200 rounded-xl text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition" value="{{ old('meta_keywords', $category->meta_keywords) }}" placeholder="mustard oil, pure ghee, honey" />
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
            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 mt-0.5 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $category->is_active ?? true)) />
            <div>
              <span class="block text-xs font-black text-stone-900">Active Status</span>
              <span class="block text-[11px] text-stone-500">Visible in storefront menus and filter lists.</span>
            </div>
          </label>

          <label class="flex items-start gap-3 p-3 rounded-xl bg-amber-50/70 border border-amber-200 cursor-pointer hover:bg-amber-50 transition-colors">
            <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 mt-0.5 accent-brand-600 rounded cursor-pointer" @checked(old('is_featured', $category->is_featured ?? false)) />
            <div>
              <span class="block text-xs font-black text-amber-950">★ Featured on Homepage</span>
              <span class="block text-[11px] text-amber-800/90">Displays as a dedicated category showcase row on the home page.</span>
            </div>
          </label>
        </div>
      </div>

      {{-- Thumbnail Image Card --}}
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 shadow-2xs space-y-3">
        <h3 class="font-black text-stone-900 text-sm sm:text-base border-b border-stone-100 pb-2.5">
          Category Image
        </h3>

        @if($category->image)
          <div class="relative rounded-2xl overflow-hidden border border-stone-200 bg-stone-50 aspect-video flex items-center justify-center">
            <img src="{{ $category->imageUrl() }}" class="w-full h-full object-cover" alt="{{ $category->name }}" />
          </div>
        @endif

        <div class="space-y-1.5">
          <label class="block text-xs font-bold text-stone-700">Upload New Photo</label>
          <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-stone-100 file:text-stone-800 hover:file:bg-stone-200 cursor-pointer" />
        </div>
      </div>

    </div>

  </div>
</form>
@endsection
