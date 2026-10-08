@extends('layouts.admin')
@section('title', 'Coupons')
@section('subtitle', 'Discount codes customers can use at checkout, with their usage.')

@section('page-actions')
  <a href="{{ route('admin.coupons.create') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    New coupon
  </a>
@endsection

@section('content')
<div class="space-y-4">
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.coupons.index') }}" class="p-3 sm:p-4 flex items-center gap-2">
      <label class="relative flex-1 min-w-0 sm:max-w-md">
        <span class="sr-only">Search coupons</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input name="q" value="{{ $q }}" placeholder="Search coupons" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
      </label>
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
      @if($q)
        <a href="{{ route('admin.coupons.index') }}" class="h-10 px-3 rounded-full text-[13px] font-medium text-gray-600 hover:bg-gray-100 inline-flex items-center shrink-0">Clear</a>
      @endif
    </form>

    {{-- Phone: one card per coupon --}}
    <div class="md:hidden px-3 pb-3 space-y-2.5">
      @forelse($coupons as $coupon)
        @php $isActive = $coupon->isCurrentlyActive(); @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-3">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <span class="inline-flex px-2.5 py-1 rounded-lg bg-white ring-1 ring-dashed ring-gray-300 font-mono font-semibold text-[13px] text-gray-900 uppercase tracking-wide">{{ $coupon->code }}</span>
              @if($coupon->description)
                <p class="text-xs text-gray-500 mt-1.5">{{ $coupon->description }}</p>
              @endif
            </div>
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full shrink-0 {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
          </div>

          <dl class="grid grid-cols-2 gap-x-3 gap-y-2 rounded-xl bg-white p-2.5 text-[13px]">
            <div>
              <dt class="text-[11px] text-gray-500">Discount</dt>
              <dd class="font-semibold text-gray-900">{{ $coupon->valueLabel() }}</dd>
            </div>
            <div>
              <dt class="text-[11px] text-gray-500">Min order</dt>
              <dd class="text-gray-800">{{ $coupon->min_order_amount ? money($coupon->min_order_amount) : 'None' }}</dd>
            </div>
            <div>
              <dt class="text-[11px] text-gray-500">Used</dt>
              <dd class="text-gray-800 tabular-nums">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : ' (no limit)' }}</dd>
            </div>
            <div>
              <dt class="text-[11px] text-gray-500">Valid until</dt>
              <dd class="text-gray-800">{{ $coupon->expires_at?->format('d M Y') ?? 'Never' }}</dd>
            </div>
          </dl>

          <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="flex-1 h-9 rounded-full bg-white ring-1 ring-gray-200 hover:bg-gray-100 text-gray-800 text-xs font-semibold inline-flex items-center justify-center">Edit</a>
            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon \'{{ $coupon->code }}\'?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Delete</button>
            </form>
          </div>
        </article>
      @empty
        <div class="text-center py-12 space-y-2">
          <p class="text-sm text-gray-500">No coupons match your search.</p>
          <a href="{{ route('admin.coupons.create') }}" class="text-[13px] font-medium text-gray-900 underline underline-offset-2">Create a coupon</a>
        </div>
      @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="px-4 py-3">Code</th>
            <th class="px-4 py-3">Discount</th>
            <th class="px-4 py-3">Min order</th>
            <th class="px-4 py-3 text-right">Used</th>
            <th class="px-4 py-3">Valid until</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($coupons as $coupon)
            @php $isActive = $coupon->isCurrentlyActive(); @endphp
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="px-4 py-3">
                <span class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 font-mono font-semibold text-xs text-gray-900 uppercase tracking-wide">{{ $coupon->code }}</span>
                @if($coupon->description)
                  <p class="text-[11px] text-gray-500 mt-1 max-w-xs">{{ $coupon->description }}</p>
                @endif
              </td>
              <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">{{ $coupon->valueLabel() }}</td>
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $coupon->min_order_amount ? money($coupon->min_order_amount) : '—' }}</td>
              <td class="px-4 py-3 text-right text-gray-800 tabular-nums whitespace-nowrap">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : '' }}</td>
              <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $coupon->expires_at?->format('d M Y, h:i A') ?? 'Never' }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('admin.coupons.edit', $coupon) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">Edit</a>
                  <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete coupon \'{{ $coupon->code }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 inline-flex items-center justify-center cursor-pointer" title="Delete" aria-label="Delete coupon {{ $coupon->code }}">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-12 text-center text-gray-500 text-sm">No coupons yet. Use "New coupon" to create one.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($coupons->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $coupons->links() }}</div>
    @endif
  </div>
</div>
@endsection
