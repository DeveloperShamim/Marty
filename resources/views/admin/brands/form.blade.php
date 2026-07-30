@extends('layouts.admin')
@php $editing = $brand->exists; @endphp
@section('title', $editing ? 'Edit Brand' : 'New Brand')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data">
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="{{ route('admin.brands.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Back to Brands</a>
      <h2 class="text-xl font-bold mt-1">{{ $editing ? 'Edit Brand: ' . $brand->name : 'New Brand' }}</h2>
    </div>
    <button class="btn-primary">Save Brand</button>
  </div>

  <div class="max-w-3xl space-y-6">
    <div class="card p-5 space-y-4">
      <h3 class="font-semibold text-gray-800">Brand Identity & Details</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="lbl">Brand Name <span class="text-red-500">*</span></label>
          <input name="name" class="inp" value="{{ old('name', $brand->name) }}" placeholder="e.g. Apple, Nike, Samsung" required />
        </div>
        <div>
          <label class="lbl">Slug (blank = auto)</label>
          <input name="slug" class="inp" value="{{ old('slug', $brand->slug) }}" placeholder="e.g. apple" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="lbl">Official Website URL</label>
          <input name="website" type="url" class="inp" value="{{ old('website', $brand->website) }}" placeholder="https://www.brand.com" />
        </div>
        <div>
          <label class="lbl">Display Position Order</label>
          <input name="position" type="number" class="inp" value="{{ old('position', $brand->position ?? 0) }}" />
        </div>
      </div>

      <div>
        <label class="lbl">Brand Description / Story</label>
        <textarea name="description" class="inp" rows="3" placeholder="Brief about the manufacturer or brand...">{{ old('description', $brand->description) }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-gray-100">
        <div>
          <label class="lbl">Brand Logo Image</label>
          <div class="flex items-center gap-4 mt-1">
            @if($brand->logo)
              <img src="{{ $brand->logoUrl() }}" class="h-16 w-16 object-contain rounded-lg border border-gray-200 bg-white p-1" alt="">
            @endif
            <input name="logo_file" type="file" accept="image/*" class="text-sm" />
          </div>
          <p class="text-xs text-gray-400 mt-1">PNG or SVG with transparent background recommended.</p>
        </div>

        <div>
          <label class="lbl">Hero Banner (for Brand Page)</label>
          <div class="flex items-center gap-4 mt-1">
            @if($brand->banner)
              <img src="{{ $brand->bannerUrl() }}" class="h-16 w-28 object-cover rounded-lg border border-gray-200" alt="">
            @endif
            <input name="banner_file" type="file" accept="image/*" class="text-sm" />
          </div>
          <p class="text-xs text-gray-400 mt-1">Wide image displayed at the top of `/brand/{slug}` page.</p>
        </div>
      </div>

      <div class="space-y-2 pt-3 border-t border-gray-100">
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="is_active" value="1" class="accent-primary" @checked(old('is_active', $brand->is_active ?? true)) /> 
          Active (visible on storefront filter and brand list)
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-amber-900">
          <input type="checkbox" name="is_featured" value="1" class="accent-primary" @checked(old('is_featured', $brand->is_featured ?? false)) /> 
          ★ Featured Brand (Display on Homepage featured brand bar)
        </label>
      </div>
    </div>

    <div class="card p-5 space-y-3">
      <h3 class="font-semibold text-gray-800">SEO Settings</h3>
      <div><label class="lbl">Meta Title</label><input name="meta_title" class="inp" value="{{ old('meta_title', $brand->meta_title) }}" placeholder="Buy [Brand] Products Online" /></div>
      <div><label class="lbl">Meta Description</label><textarea name="meta_description" class="inp" rows="2" placeholder="Explore authentic products from [Brand]...">{{ old('meta_description', $brand->meta_description) }}</textarea></div>
      <div><label class="lbl">Meta Keywords</label><textarea name="meta_keywords" class="inp" rows="2" placeholder="brand, electronics, authentic">{{ old('meta_keywords', $brand->meta_keywords) }}</textarea></div>
    </div>
  </div>
</form>
@endsection
