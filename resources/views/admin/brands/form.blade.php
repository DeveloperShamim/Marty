@extends('layouts.admin')
@php $editing = $brand->exists; @endphp
@section('title', $editing ? 'Edit brand' : 'New brand')
@section('subtitle', $editing ? $brand->name : 'Add a brand for product filters and the brand page.')

@section('page-actions')
  <a href="{{ route('admin.brands.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="brandForm" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Save changes' : 'Create brand' }}
  </button>
@endsection

@section('content')
<form id="brandForm" method="POST" action="{{ $editing ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" enctype="multipart/form-data" class="max-w-5xl">
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <div class="lg:col-span-8 space-y-4">
      <section class="panel p-4 sm:p-5 space-y-4">
        <h2 class="text-[15px] font-semibold text-gray-900">Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="lbl" for="brand_name">Name <span class="text-rose-500">*</span></label>
            <input id="brand_name" type="text" name="name" class="inp" value="{{ old('name', $brand->name) }}" placeholder="e.g. Khaas Food" required />
          </div>
          <div>
            <label class="lbl" for="brand_slug">URL slug</label>
            <input id="brand_slug" type="text" name="slug" class="inp font-mono" value="{{ old('slug', $brand->slug) }}" placeholder="khaas-food" />
            <p class="text-[11px] text-gray-400 mt-1">Leave blank to create it from the name.</p>
          </div>
          <div>
            <label class="lbl" for="brand_website">Website</label>
            <input id="brand_website" type="url" name="website" class="inp" value="{{ old('website', $brand->website) }}" placeholder="https://www.brandwebsite.com" />
          </div>
          <div>
            <label class="lbl" for="brand_position">Sort order</label>
            <input id="brand_position" type="number" name="position" class="inp tabular-nums" value="{{ old('position', $brand->position ?? 0) }}" />
          </div>
        </div>

        <div>
          <label class="lbl" for="brand_description">About the brand</label>
          <textarea id="brand_description" name="description" rows="3" class="inp" placeholder="A short summary of the brand">{{ old('description', $brand->description) }}</textarea>
        </div>
      </section>

      <section class="panel p-4 sm:p-5 space-y-4">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Search engines</h2>
          <p class="text-xs text-gray-500 mt-0.5">How the brand page appears on Google.</p>
        </div>
        <div>
          <label class="lbl" for="brand_meta_title">Meta title</label>
          <input id="brand_meta_title" type="text" name="meta_title" class="inp" value="{{ old('meta_title', $brand->meta_title) }}" placeholder="Brand products online | Store name" />
        </div>
        <div>
          <label class="lbl" for="brand_meta_description">Meta description</label>
          <textarea id="brand_meta_description" name="meta_description" rows="2" class="inp" placeholder="Short description for search results">{{ old('meta_description', $brand->meta_description) }}</textarea>
        </div>
        <div>
          <label class="lbl" for="brand_meta_keywords">Meta keywords</label>
          <input id="brand_meta_keywords" type="text" name="meta_keywords" class="inp" value="{{ old('meta_keywords', $brand->meta_keywords) }}" placeholder="brand name, authentic goods, bangladesh" />
          <p class="text-[11px] text-gray-400 mt-1">Separate with commas.</p>
        </div>
      </section>
    </div>

    <div class="lg:col-span-4 space-y-4">
      <section class="panel p-4 sm:p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Visibility</h2>
        <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
          <input type="checkbox" name="is_active" value="1" class="h-4 w-4 mt-0.5 rounded cursor-pointer" @checked(old('is_active', $brand->is_active ?? true)) />
          <span>
            <span class="block text-[13px] font-medium text-gray-900">Active</span>
            <span class="block text-xs text-gray-500">Shown in store brand filters and lists.</span>
          </span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
          <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 mt-0.5 rounded cursor-pointer" @checked(old('is_featured', $brand->is_featured ?? false)) />
          <span>
            <span class="block text-[13px] font-medium text-gray-900">Featured brand</span>
            <span class="block text-xs text-gray-500">Shown in the home page brands bar.</span>
          </span>
        </label>
      </section>

      <section class="panel p-4 sm:p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Logo</h2>
        @if($brand->logo)
          <div class="h-20 rounded-xl bg-gray-50 p-2 grid place-items-center">
            <img src="{{ $brand->logoUrl() }}" class="max-h-full max-w-full object-contain" alt="{{ $brand->name }}" />
          </div>
        @endif
        <div>
          <label class="lbl" for="brand_logo">{{ $brand->logo ? 'Replace logo' : 'Upload logo' }}</label>
          <input id="brand_logo" type="file" name="logo_file" accept="image/*" class="w-full text-xs text-gray-600 file:mr-3 file:h-9 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200 cursor-pointer" />
          <p class="text-[11px] text-gray-400 mt-1">Square or transparent PNG/WebP works best.</p>
        </div>
      </section>

      <section class="panel p-4 sm:p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Brand page banner</h2>
        @if($brand->banner)
          <div class="rounded-xl bg-gray-100 aspect-[21/9] overflow-hidden">
            <img src="{{ $brand->bannerUrl() }}" class="w-full h-full object-cover" alt="{{ $brand->name }}" />
          </div>
        @endif
        <div>
          <label class="lbl" for="brand_banner">{{ $brand->banner ? 'Replace banner' : 'Upload banner' }}</label>
          <input id="brand_banner" type="file" name="banner_file" accept="image/*" class="w-full text-xs text-gray-600 file:mr-3 file:h-9 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200 cursor-pointer" />
          <p class="text-[11px] text-gray-400 mt-1">Wide image shown at the top of the brand page.</p>
        </div>
      </section>
    </div>
  </div>

  <div class="mt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 lg:hidden">
    <a href="{{ route('admin.brands.index') }}" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center justify-center">Cancel</a>
    <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center cursor-pointer" style="background: var(--brand-dark);">{{ $editing ? 'Save changes' : 'Create brand' }}</button>
  </div>
</form>
@endsection
