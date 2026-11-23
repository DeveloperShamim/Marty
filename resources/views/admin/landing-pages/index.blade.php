@extends('layouts.admin')
@section('title', 'Landing pages')
@section('subtitle', 'One-product offer pages for your ads, with their own price and an order form on the page.')

@section('content')
<div class="space-y-4 max-w-5xl">
  {{-- New page: pick the product, the draft starts with Bangla starter text --}}
  <form method="POST" action="{{ route('admin.landing-pages.store') }}" class="panel p-4 sm:p-5 space-y-4" data-lp-new>
    @csrf
    <div>
      <p class="lbl">1. Pick a template</p>
      @include('admin.landing-pages._templates', ['selected' => old('template', 'classic')])
      <p class="text-[11px] text-gray-500 mt-1.5">You can switch template later in the editor and preview each one first. Your text and photos stay.</p>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
    <div class="flex-1 min-w-0">
      <label class="lbl" for="lpNewProduct">2. New landing page for</label>
      <select id="lpNewProduct" name="product_id" class="inp" required>
        <option value="">Choose a product…</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}">{{ $p->name }}{{ $p->is_published ? '' : ' (hidden in shop)' }}</option>
        @endforeach
      </select>
      @error('product_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="pill-btn pill-btn-dark justify-center">
      <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
      Create draft
    </button>
    </div>
  </form>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" data-lp-stats>
    @foreach([
      ['Landing pages', $stats['pages'], $stats['published'] . ' live'],
      ['Views', number_format($stats['views']), 'one per visitor'],
      ['Orders', number_format($stats['orders']), 'not counting cancelled'],
      ['Conversion', $stats['conversion'] . '%', 'orders ÷ views'],
    ] as [$label, $value, $hint])
      <div class="panel p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
        <p class="text-[11px] text-gray-500">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  <form method="GET" class="flex flex-col sm:flex-row gap-2">
    <input name="q" value="{{ $q }}" class="inp flex-1" placeholder="Search title, link or product">
    <select name="status" class="inp sm:w-44">
      <option value="" @selected($status === '')>All</option>
      <option value="published" @selected($status === 'published')>Published</option>
      <option value="draft" @selected($status === 'draft')>Draft</option>
    </select>
    <button class="pill-btn justify-center">Filter</button>
  </form>

  <div class="card overflow-hidden">
    <ul class="divide-y divide-gray-100">
      @forelse($pages as $lp)
        <li class="px-4 py-3 sm:px-5 flex flex-col md:flex-row md:items-center gap-3" data-lp-row="{{ $lp->slug }}">
          <div class="flex items-center gap-3 min-w-0 flex-1">
            <img src="{{ $lp->product?->imageUrl() ?? image_url(null, $lp->title) }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover bg-gray-100">
            <div class="min-w-0">
              <p class="text-[13px] font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
                <span class="truncate">{{ $lp->title }}</span>
                @if($lp->is_published)
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700">Published</span>
                @else
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-600">Draft</span>
                @endif
              </p>
              <p class="text-xs text-gray-500 mt-0.5 truncate">/offer/{{ $lp->slug }} · {{ \App\Models\LandingPage::TEMPLATES[$lp->template()][0] }} · {{ $lp->product?->name ?? 'Product deleted' }} · {{ money($lp->price()) }}</p>
              <p class="text-xs text-gray-600 mt-0.5">
                {{ number_format($lp->views) }} views · <a href="{{ route('admin.orders.index', ['landing' => $lp->id]) }}" class="underline">{{ $lp->orders_count }} {{ \Illuminate\Support\Str::plural('order', $lp->orders_count) }}</a> · {{ $lp->conversionRate($lp->orders_count) }}% conversion
              </p>
            </div>
          </div>
          <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
            <a href="{{ $lp->url() }}" target="_blank" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">{{ $lp->is_published ? 'View' : 'Preview' }}</a>
            <a href="{{ route('admin.landing-pages.edit', $lp) }}" class="h-8 px-3 rounded-full text-white text-xs font-semibold inline-flex items-center" style="background: var(--brand-dark);">Edit</a>
            <form method="POST" action="{{ route('admin.landing-pages.publish', $lp) }}">
              @csrf @method('PATCH')
              <button class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium cursor-pointer">{{ $lp->is_published ? 'Unpublish' : 'Publish' }}</button>
            </form>
            <form method="POST" action="{{ route('admin.landing-pages.duplicate', $lp) }}">
              @csrf
              <button class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium cursor-pointer">Duplicate</button>
            </form>
            <form method="POST" action="{{ route('admin.landing-pages.destroy', $lp) }}" onsubmit="return confirm('Delete this landing page? Its orders stay, but the link stops working.')">
              @csrf @method('DELETE')
              <button class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium cursor-pointer">Delete</button>
            </form>
          </div>
        </li>
      @empty
        <li class="py-10 px-4 text-center">
          <p class="text-sm text-gray-700 font-medium">{{ $q !== '' || $status !== '' ? 'No landing pages match.' : 'No landing pages yet' }}</p>
          <p class="text-xs text-gray-500 mt-1">Pick a product above. The page starts as a draft with Bangla sales text, the product's photos and its price.</p>
        </li>
      @endforelse
    </ul>
  </div>
</div>
@endsection
