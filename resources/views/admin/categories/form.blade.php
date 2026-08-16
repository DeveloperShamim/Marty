@extends('layouts.admin')
@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit Category' : 'New Category')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
  @csrf
  @if($editing) @method('PUT') @endif

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
    <div>
      <a href="{{ route('admin.categories.index') }}" class="text-xs sm:text-sm text-gray-500 hover:text-primary">&larr; Back to categories</a>
      <h2 class="text-lg sm:text-xl font-bold mt-1 text-slate-900">{{ $editing ? 'Edit Category' : 'New Category' }}</h2>
    </div>
    <div class="flex items-center gap-2 self-start sm:self-auto">
      <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl border border-gray-300 bg-white">Cancel</a>
      <button class="btn-primary py-2 px-5 text-xs sm:text-sm font-bold">Save Category</button>
    </div>
  </div>

  <div class="max-w-2xl space-y-4 sm:space-y-6">
    <div class="card p-4 sm:p-5 space-y-4">
      <h3 class="font-bold text-slate-900 text-sm sm:text-base">Details</h3>
      <div class="grid grid-cols-1 sm:grid-cols-[1fr_100px] gap-3 sm:gap-4">
        <div><label class="lbl">Name</label><input name="name" class="inp" value="{{ old('name', $category->name) }}" required /></div>
        <div><label class="lbl">Icon (emoji)</label><input name="icon" class="inp" value="{{ old('icon', $category->icon) }}" placeholder="👕" /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div><label class="lbl">Slug (blank = auto)</label><input name="slug" class="inp" value="{{ old('slug', $category->slug) }}" /></div>
        <div><label class="lbl">Position</label><input name="position" type="number" class="inp" value="{{ old('position', $category->position ?? 0) }}" /></div>
      </div>
      <div><label class="lbl">Description</label><textarea name="description" class="inp" rows="2">{{ old('description', $category->description) }}</textarea></div>
      <div>
        <label class="lbl">Image</label>
        <div class="flex items-center gap-3 flex-wrap">
          @if($category->image)<img src="{{ $category->imageUrl() }}" class="h-14 w-14 rounded-xl border border-gray-200 object-cover shrink-0" alt="">@endif
          <input name="image_file" type="file" accept="image/*" class="text-xs w-full sm:w-auto" />
        </div>
      </div>
      <div class="space-y-2 pt-3 border-t border-gray-100">
        <label class="flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-700 cursor-pointer"><input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-primary rounded cursor-pointer" @checked(old('is_active', $category->is_active ?? true)) /> Active (visible on storefront)</label>
        <label class="flex items-center gap-2 text-xs sm:text-sm font-medium text-amber-900 cursor-pointer"><input type="checkbox" name="is_featured" value="1" class="h-4 w-4 accent-primary rounded cursor-pointer" @checked(old('is_featured', $category->is_featured ?? false)) /> ★ Featured on Homepage (Displays dedicated category section)</label>
      </div>
    </div>

    <div class="card p-4 sm:p-5 space-y-3">
      <h3 class="font-bold text-slate-900 text-sm sm:text-base">SEO</h3>
      <div><label class="lbl">Meta title</label><input name="meta_title" class="inp" value="{{ old('meta_title', $category->meta_title) }}" /></div>
      <div><label class="lbl">Meta description</label><textarea name="meta_description" class="inp" rows="2">{{ old('meta_description', $category->meta_description) }}</textarea></div>
      <div><label class="lbl">Meta keywords (comma-separated)</label><textarea name="meta_keywords" class="inp" rows="2">{{ old('meta_keywords', $category->meta_keywords) }}</textarea></div>
    </div>
  </div>
</form>
@endsection
