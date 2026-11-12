@extends('layouts.admin')
@section('title', 'Pages')
@section('subtitle', 'About us, FAQ, shipping, returns and any other page your store needs.')

@section('page-actions')
  <a href="{{ route('admin.pages.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    New page
  </a>
@endsection

@section('content')
<div class="space-y-4 max-w-4xl">
  <div class="card overflow-hidden">
    <ul class="divide-y divide-gray-100">
      @forelse($pages as $page)
        <li class="px-4 py-3 sm:px-5 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4" data-page-row="{{ $page->slug }}">
          <div class="min-w-0 flex-1">
            <p class="text-[13px] font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
              {{ $page->title }}
              @if($page->is_published)
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Published</span>
              @else
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Draft</span>
              @endif
              @if($page->is_published && $page->show_in_footer)
                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-gray-100 text-gray-600">In footer</span>
              @endif
            </p>
            <p class="text-xs text-gray-500 mt-0.5 truncate">/page/{{ $page->slug }} · {{ \Illuminate\Support\Str::limit(strip_tags((string) $page->body), 90) ?: 'No text yet' }}</p>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            @if($page->is_published)
              <a href="{{ $page->url() }}" target="_blank" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">View</a>
            @endif
            <a href="{{ route('admin.pages.edit', $page) }}" class="h-8 px-3 rounded-full text-white text-xs font-semibold inline-flex items-center" style="background: var(--brand-dark);">Edit</a>
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete the page \'{{ addslashes($page->title) }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium cursor-pointer">Delete</button>
            </form>
          </div>
        </li>
      @empty
        <li class="py-10 px-4 text-center">
          <p class="text-sm text-gray-700 font-medium">No pages yet</p>
          <p class="text-xs text-gray-500 mt-1">Most stores add About us, FAQ, Shipping &amp; delivery and Returns &amp; refunds.</p>
          <a href="{{ route('admin.pages.create') }}" class="mt-3 inline-flex h-9 px-4 rounded-full text-white text-xs font-semibold items-center" style="background: var(--brand-dark);">Add the first page</a>
        </li>
      @endforelse
    </ul>
  </div>
  <p class="text-xs text-gray-500 px-1">Terms &amp; returns and the Privacy policy are edited in <a href="{{ route('admin.settings.edit') }}#all" class="underline">Settings</a>. To put a page in the top menu, add a “Custom link” to <span class="font-mono">/page/…</span> in <a href="{{ route('admin.header-menu.index') }}" class="underline">Menu bar</a>.</p>
</div>
@endsection
