@extends('layouts.admin')
@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit category' : 'New category')
@section('subtitle', $editing ? $category->name : 'Add a category for the catalog and store menus.')

@section('page-actions')
  <a href="{{ route('admin.categories.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="categoryForm" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Save changes' : 'Create category' }}
  </button>
@endsection

@section('content')
<form id="categoryForm" method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-5xl">
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <div class="lg:col-span-8 space-y-4">
      <section class="panel p-4 sm:p-5 space-y-4">
        <h2 class="text-[15px] font-semibold text-gray-900">Details</h2>

        <div class="grid grid-cols-4 gap-3">
          <div class="col-span-3">
            <label class="lbl" for="cat_name">Name <span class="text-rose-500">*</span></label>
            <input id="cat_name" type="text" name="name" class="inp" value="{{ old('name', $category->name) }}" placeholder="e.g. Organic foods" required />
          </div>
          <div>
            <label class="lbl" for="cat_icon">Icon</label>
            <input id="cat_icon" type="text" name="icon" class="inp text-center" value="{{ old('icon', $category->icon) }}" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="lbl" for="cat_slug">URL slug</label>
            <input id="cat_slug" type="text" name="slug" class="inp font-mono" value="{{ old('slug', $category->slug) }}" placeholder="organic-foods" />
            <p class="text-[11px] text-gray-400 mt-1">Leave blank to create it from the name.</p>
          </div>
          <div>
            <label class="lbl" for="cat_position">Sort order</label>
            <input id="cat_position" type="number" name="position" class="inp tabular-nums" value="{{ old('position', $category->position ?? 0) }}" />
            <p class="text-[11px] text-gray-400 mt-1">Lower numbers show first.</p>
          </div>
        </div>

        <div>
          <label class="lbl" for="cat_description">Description</label>
          <textarea id="cat_description" name="description" rows="3" class="inp" placeholder="A short summary of what is in this category">{{ old('description', $category->description) }}</textarea>
        </div>
      </section>

      <section class="panel p-4 sm:p-5 space-y-4">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Search engines</h2>
          <p class="text-xs text-gray-500 mt-0.5">How this category appears on Google.</p>
        </div>
        <div>
          <label class="lbl" for="cat_meta_title">Meta title</label>
          <input id="cat_meta_title" type="text" name="meta_title" class="inp" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Category title | Store name" />
        </div>
        <div>
          <label class="lbl" for="cat_meta_description">Meta description</label>
          <textarea id="cat_meta_description" name="meta_description" rows="2" class="inp" placeholder="Short description for search results">{{ old('meta_description', $category->meta_description) }}</textarea>
        </div>
        <div>
          <label class="lbl" for="cat_meta_keywords">Meta keywords</label>
          <input id="cat_meta_keywords" type="text" name="meta_keywords" class="inp" value="{{ old('meta_keywords', $category->meta_keywords) }}" placeholder="mustard oil, pure ghee, honey" />
          <p class="text-[11px] text-gray-400 mt-1">Separate with commas.</p>
        </div>
      </section>
    </div>

    <div class="lg:col-span-4 space-y-4">
      <section class="panel p-4 sm:p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Visibility</h2>
        <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
          <input type="checkbox" name="is_active" value="1" class="h-4 w-4 mt-0.5 rounded cursor-pointer" @checked(old('is_active', $category->is_active ?? true)) />
          <span>
            <span class="block text-[13px] font-medium text-gray-900">Active</span>
            <span class="block text-xs text-gray-500">Shown in store menus and filters.</span>
          </span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
          <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 mt-0.5 rounded cursor-pointer" @checked(old('is_featured', $category->is_featured ?? false)) />
          <span>
            <span class="block text-[13px] font-medium text-gray-900">Feature on home page</span>
            <span class="block text-xs text-gray-500">Gets its own product row on the home page.</span>
          </span>
        </label>
      </section>

      <section class="panel p-4 sm:p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Image</h2>
        @if($category->image)
          <div class="rounded-xl overflow-hidden bg-gray-100 aspect-video">
            <img src="{{ $category->imageUrl() }}" class="w-full h-full object-cover" alt="{{ $category->name }}" />
          </div>
        @endif
        <div>
          <label class="lbl" for="cat_image">{{ $category->image ? 'Replace image' : 'Upload image' }}</label>
          <input id="cat_image" type="file" name="image_file" accept="image/*" class="w-full text-xs text-gray-600 file:mr-3 file:h-9 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200 cursor-pointer" />
        </div>
      </section>
    </div>
  </div>

  <div class="mt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 lg:hidden">
    <a href="{{ route('admin.categories.index') }}" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center justify-center">Cancel</a>
    <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center cursor-pointer" style="background: var(--brand-dark);">{{ $editing ? 'Save changes' : 'Create category' }}</button>
  </div>
</form>
@endsection
