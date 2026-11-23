@extends('layouts.admin')
@php $editing = $page->exists; @endphp
@section('title', $editing ? 'Edit page' : 'New page')
@section('subtitle', $editing ? $page->title : 'A simple page with a title and text, such as About us or FAQ.')

@section('page-actions')
  <a href="{{ route('admin.pages.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="pageForm" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Save page' : 'Create page' }}
  </button>
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" id="pageForm" class="grid grid-cols-1 lg:grid-cols-[1fr_280px] gap-4 max-w-5xl">
  @csrf
  @if($editing) @method('PUT') @endif

  <section class="panel p-4 sm:p-5 space-y-3.5 min-w-0">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl" for="pageTitle">Title <span class="text-rose-500">*</span></label>
        <input name="title" id="pageTitle" class="inp" value="{{ old('title', $page->title) }}" required maxlength="120" placeholder="e.g. About us" />
        @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="lbl" for="pageSlug">Address</label>
        <div class="flex items-center rounded-[10px] ring-1 ring-gray-200 bg-white overflow-hidden">
          <span class="pl-3 text-[13px] text-gray-400 shrink-0">/page/</span>
          <input name="slug" id="pageSlug" class="w-full h-10 px-1 text-[13px] outline-none" value="{{ old('slug', $page->slug) }}" maxlength="120" placeholder="about-us" />
        </div>
        <p class="text-[11px] text-gray-500 mt-1">Leave empty to make it from the title.</p>
        @error('slug')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>
    </div>

    <div>
      <label class="lbl" for="pageBody">Text</label>
      <textarea name="body" id="pageBody" rows="16" class="inp leading-relaxed text-[13px]" maxlength="30000" placeholder="Write your page here.">{{ old('body', $page->body) }}</textarea>
      <p class="text-[11px] text-gray-500 mt-1">Leave an empty line between paragraphs. Start a line with <span class="font-mono">## </span> for a heading or <span class="font-mono">- </span> for a bullet. Wrap words in <span class="font-mono">**two stars**</span> for bold.</p>
      @error('body')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="lbl" for="pageMeta">Search engine description <span class="text-gray-400 font-normal">(optional)</span></label>
      <input name="meta_description" id="pageMeta" class="inp text-[13px]" value="{{ old('meta_description', $page->meta_description) }}" maxlength="300" placeholder="One sentence shown under the page title on Google" />
    </div>

    @php
      $canCustom = auth()->user()?->isAdmin();
      $blocks = array_values(old('custom', $page->custom_html ?? []));
    @endphp
    <div class="pt-2 border-t border-gray-100" id="pageCustom">
      <h2 class="text-[15px] font-semibold text-gray-900 pt-2">Custom HTML <span class="text-gray-400 font-normal text-[13px]">(optional)</span></h2>
      <p class="text-[11px] text-gray-500 mt-0.5">Paste your own HTML, CSS or script, such as a form, a map or a countdown. Each block shows above or below the text in its own sealed frame, so it can't read shoppers' accounts or change the rest of the store.</p>
      @unless($canCustom)<p class="text-[11px] text-amber-700 mt-1">Only admins can change these.</p>@endunless
      <div class="mt-3 space-y-3">
        @for($i = 0; $i < \App\Models\Page::MAX_CUSTOM; $i++)
          <div class="rounded-2xl ring-1 ring-gray-200 p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
              <span class="text-[13px] font-medium text-gray-900">Block {{ $i + 1 }}</span>
              <select name="custom[{{ $i }}][at]" class="h-9 rounded-lg border border-gray-200 bg-white pl-2.5 pr-8 text-[13px] text-gray-800" aria-label="Where block {{ $i + 1 }} shows" @disabled(!$canCustom)>
                @foreach(\App\Models\Page::CUSTOM_SPOTS as $key => $label)
                  <option value="{{ $key }}" @selected(($blocks[$i]['at'] ?? 'below') === $key)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <textarea name="custom[{{ $i }}][html]" rows="5" maxlength="20000" class="inp font-mono text-[12px] leading-relaxed" placeholder="<div>…</div>" aria-label="HTML for block {{ $i + 1 }}" @disabled(!$canCustom)>{{ $blocks[$i]['html'] ?? '' }}</textarea>
            @error("custom.$i.at")<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
            @error("custom.$i.html")<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
          </div>
        @endfor
      </div>
    </div>
  </section>

  <aside class="panel p-4 sm:p-5 space-y-3 self-start">
    <h2 class="text-[15px] font-semibold text-gray-900">Status</h2>
    @foreach([['is_published', 'Published', 'Visible to shoppers'], ['show_in_footer', 'Show in footer', 'Listed under Help in the footer']] as [$f, $l, $h])
      <label class="flex items-center justify-between gap-3 cursor-pointer">
        <span><span class="block text-[13px] font-medium text-gray-900">{{ $l }}</span><span class="block text-[11px] text-gray-500">{{ $h }}</span></span>
        <span class="shrink-0 inline-flex">
          <input type="checkbox" name="{{ $f }}" value="1" class="peer sr-only" @checked(old($f, $page->{$f}))>
          <span class="relative h-6 w-10 rounded-full bg-gray-200 transition-colors peer-checked:bg-gray-900 after:absolute after:top-0.5 after:left-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-4"></span>
        </span>
      </label>
    @endforeach
    <div>
      <label class="lbl" for="pagePosition">Order in footer</label>
      <input name="position" id="pagePosition" type="number" min="0" max="999" class="inp" value="{{ old('position', $page->position ?? 0) }}" />
    </div>
  </aside>
</form>
@endsection
