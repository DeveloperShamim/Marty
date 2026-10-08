@extends('layouts.admin')
@section('title', 'Reviews')
@section('subtitle', 'Approve or reject customer reviews. Approved reviews update product ratings.')

@php
  $statusPill = [
    'approved' => ['bg-emerald-50 text-emerald-700', 'Approved'],
    'rejected' => ['bg-rose-50 text-rose-700', 'Rejected'],
    'pending'  => ['bg-amber-50 text-amber-700', 'Pending'],
  ];
@endphp

@section('content')
<div class="space-y-4">
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Review status">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
        @php $active = $status === $key; @endphp
        <a href="{{ route('admin.reviews.index', array_filter(['status' => $key, 'q' => $q])) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
          <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts[$key] ?? 0 }}</span>
        </a>
      @endforeach
    </div>
  </nav>

  <div class="card overflow-hidden">
    <form method="GET" class="p-3 sm:p-4 flex items-center gap-2">
      <input type="hidden" name="status" value="{{ $status }}" />
      <label class="relative flex-1 min-w-0 sm:max-w-md">
        <span class="sr-only">Search reviews</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input name="q" value="{{ $q }}" placeholder="Search reviews" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
      </label>
      <button class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
    </form>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="px-4 py-3">Product</th>
            <th class="px-4 py-3">Review</th>
            <th class="px-4 py-3">Rating</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($reviews as $review)
            @php [$pillClass, $pillLabel] = $statusPill[$review->status] ?? $statusPill['pending']; @endphp
            <tr class="hover:bg-gray-50/70 align-top">
              <td class="px-4 py-3">
                @if($review->product)
                  <div class="flex items-center gap-3 min-w-[180px]">
                    <img src="{{ $review->product->imageUrl() }}" class="h-10 w-10 rounded-xl object-cover bg-gray-100 shrink-0" alt="">
                    <div class="min-w-0">
                      <a href="{{ route('admin.products.edit', $review->product) }}" class="font-semibold text-gray-900 hover:underline">{{ $review->product->name }}</a>
                      <p class="text-[11px] text-gray-500">{{ $review->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                  </div>
                @else
                  <span class="text-gray-400">Deleted product</span>
                @endif
              </td>
              <td class="px-4 py-3 max-w-md">
                <p class="font-semibold text-gray-900">{{ $review->author_name }}
                  @if($review->is_verified_purchase)
                    <span class="ml-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sky-50 text-sky-700">Verified</span>
                  @endif
                </p>
                @if($review->author_email)<p class="text-[11px] text-gray-500">{{ $review->author_email }}</p>@endif
                @if($review->title)<p class="mt-1 font-medium text-gray-900">{{ $review->title }}</p>@endif
                <p class="mt-1 text-gray-600 whitespace-pre-line">{{ $review->body }}</p>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="text-amber-500 tracking-tight" aria-label="{{ $review->rating }} out of 5">{{ str_repeat("\u{2605}", $review->rating) }}<span class="text-gray-300">{{ str_repeat("\u{2605}", 5 - $review->rating) }}</span></span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $pillClass }}">{{ $pillLabel }}</span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  @if($review->status !== 'approved')
                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline">@csrf
                      <button class="h-8 px-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">Approve</button>
                    </form>
                  @endif
                  @if($review->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline">@csrf
                      <button class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium">Reject</button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('Delete this review permanently?')">@csrf @method('DELETE')
                    <button class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 inline-flex items-center justify-center" title="Delete" aria-label="Delete review">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500 text-sm">No reviews in this filter.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: one card per review --}}
    <div class="md:hidden px-3 pb-3 space-y-2.5">
      @forelse($reviews as $review)
        @php [$pillClass, $pillLabel] = $statusPill[$review->status] ?? $statusPill['pending']; @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2.5">
          <div class="flex items-start gap-3">
            @if($review->product)
              <img src="{{ $review->product->imageUrl() }}" class="h-10 w-10 rounded-xl object-cover bg-gray-100 shrink-0" alt="">
            @endif
            <div class="min-w-0 flex-1">
              @if($review->product)
                <a href="{{ route('admin.products.edit', $review->product) }}" class="font-semibold text-sm text-gray-900 truncate block">{{ $review->product->name }}</a>
              @else
                <span class="text-sm text-gray-400">Deleted product</span>
              @endif
              <p class="text-[11px] text-gray-500">{{ $review->created_at?->format('d M Y, H:i') }}</p>
            </div>
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full shrink-0 {{ $pillClass }}">{{ $pillLabel }}</span>
          </div>

          <div class="rounded-xl bg-white p-2.5 text-[13px]">
            <div class="flex items-center justify-between gap-2">
              <p class="font-semibold text-gray-900 truncate">{{ $review->author_name }}
                @if($review->is_verified_purchase)
                  <span class="ml-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sky-50 text-sky-700">Verified</span>
                @endif
              </p>
              <span class="text-amber-500 text-xs tracking-tight shrink-0" aria-label="{{ $review->rating }} out of 5">{{ str_repeat("\u{2605}", $review->rating) }}<span class="text-gray-300">{{ str_repeat("\u{2605}", 5 - $review->rating) }}</span></span>
            </div>
            @if($review->author_email)<p class="text-[11px] text-gray-500 truncate">{{ $review->author_email }}</p>@endif
            @if($review->title)<p class="mt-1.5 font-medium text-gray-900">{{ $review->title }}</p>@endif
            <p class="mt-1 text-gray-600 whitespace-pre-line">{{ $review->body }}</p>
          </div>

          <div class="flex items-center gap-1.5">
            @if($review->status !== 'approved')
              <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="flex-1">@csrf
                <button class="w-full h-9 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold">Approve</button>
              </form>
            @endif
            @if($review->status !== 'rejected')
              <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="flex-1">@csrf
                <button class="w-full h-9 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 text-gray-800 text-xs font-semibold">Reject</button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?')">@csrf @method('DELETE')
              <button class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold">Delete</button>
            </form>
          </div>
        </article>
      @empty
        <div class="text-center py-12 text-gray-500 text-sm">No reviews in this filter.</div>
      @endforelse
    </div>

    @if($reviews->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $reviews->links() }}</div>
    @endif
  </div>
</div>
@endsection
