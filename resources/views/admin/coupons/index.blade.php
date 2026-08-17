@extends('layouts.admin')
@section('title', 'Discount Coupons')

@section('content')
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>🎟️ Coupon &amp; Discount Management</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Create, manage, inspect usage metrics, and track promotional promo codes</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all">
      <span>+ Add New Coupon</span>
    </a>
  </div>

  {{-- Search & Filters --}}
  <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
    <form method="GET" action="{{ route('admin.coupons.index') }}" class="w-full flex items-center gap-2">
      <input name="q" value="{{ $q }}" placeholder="Search coupon code or description..." class="flex-1 text-xs font-semibold px-3.5 py-2 rounded-xl border border-stone-200 focus:outline-none focus:border-brand-500" />
      <button type="submit" class="px-4 py-2 rounded-xl bg-stone-900 text-white text-xs font-extrabold hover:bg-stone-800 transition">
        Search
      </button>
      @if($q)
        <a href="{{ route('admin.coupons.index') }}" class="px-3 py-2 text-xs font-bold text-stone-500 hover:text-rose-600">Clear</a>
      @endif
    </form>
  </div>

  {{-- Mobile Cards Layout (Visible on Small Screens) --}}
  <div class="grid grid-cols-1 gap-3.5 md:hidden">
    @forelse($coupons as $coupon)
      @php $isActive = $coupon->isCurrentlyActive(); @endphp
      <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-stone-100 border border-dashed border-stone-300 rounded-lg">
              <span class="font-mono font-black text-sm text-brand-700 tracking-wider uppercase">{{ $coupon->code }}</span>
            </div>
            @if($coupon->description)
              <p class="text-xs text-stone-500 font-medium mt-1">{{ $coupon->description }}</p>
            @endif
          </div>

          <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full shrink-0 {{ $isActive ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-500 border border-stone-200' }}">
            {{ $isActive ? '🟢 Active' : '🔴 Inactive' }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs bg-stone-50 p-3 rounded-xl border border-stone-200/80">
          <div>
            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Discount Value</span>
            <span class="font-black text-stone-900 text-sm">{{ $coupon->valueLabel() }}</span>
          </div>

          <div>
            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Min Order</span>
            <span class="font-extrabold text-stone-700">{{ $coupon->min_order_amount ? money($coupon->min_order_amount) : 'None' }}</span>
          </div>

          <div class="mt-1">
            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Usage Uses</span>
            <span class="font-bold text-stone-800 font-mono">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : ' (Unlimited)' }}</span>
          </div>

          <div class="mt-1">
            <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Valid Until</span>
            <span class="font-semibold text-stone-700">{{ $coupon->expires_at?->format('d M Y') ?? 'Never' }}</span>
          </div>
        </div>

        <div class="pt-2 border-t border-stone-100 flex items-center justify-end gap-2">
          <a href="{{ route('admin.coupons.edit', $coupon) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200">
            Edit
          </a>
          <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon \'{{ $coupon->code }}\'?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer">
              Delete
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white p-8 text-center rounded-2xl border border-stone-200">
        <div class="text-3xl mb-2">🎟️</div>
        <p class="text-xs font-bold text-stone-600">No coupons found matching your query.</p>
        <a href="{{ route('admin.coupons.create') }}" class="mt-2 inline-block text-xs font-bold text-brand-600 hover:underline">+ Create First Coupon</a>
      </div>
    @endforelse
  </div>

  {{-- Desktop Table View (Hidden on Small Screens) --}}
  <div class="hidden md:block bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
    <table class="w-full text-left text-xs">
      <thead class="bg-stone-50 border-b border-stone-200 font-extrabold text-stone-600 uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3.5">Coupon Code</th>
          <th class="px-5 py-3.5">Discount</th>
          <th class="px-5 py-3.5">Min Order</th>
          <th class="px-5 py-3.5 text-center">Uses</th>
          <th class="px-5 py-3.5">Valid Until</th>
          <th class="px-5 py-3.5 text-center">Status</th>
          <th class="px-5 py-3.5 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-100 font-medium">
        @forelse($coupons as $coupon)
          @php $isActive = $coupon->isCurrentlyActive(); @endphp
          <tr class="hover:bg-stone-50/70 transition-colors">
            <td class="px-5 py-3.5">
              <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 border border-dashed border-stone-300 rounded-lg">
                <span class="font-mono font-black text-xs text-brand-700 tracking-wider uppercase">{{ $coupon->code }}</span>
              </div>
              @if($coupon->description)
                <p class="text-[11px] text-stone-500 font-medium mt-0.5">{{ $coupon->description }}</p>
              @endif
            </td>
            <td class="px-5 py-3.5 font-extrabold text-stone-900 text-sm">{{ $coupon->valueLabel() }}</td>
            <td class="px-5 py-3.5 text-stone-600 font-bold">{{ $coupon->min_order_amount ? money($coupon->min_order_amount) : '—' }}</td>
            <td class="px-5 py-3.5 text-center font-mono font-bold text-stone-800">
              {{ $coupon->used_count }}{{ $coupon->max_uses ? ' / '.$coupon->max_uses : '' }}
            </td>
            <td class="px-5 py-3.5 text-stone-600 font-semibold">{{ $coupon->expires_at?->format('d M Y, h:i A') ?? 'Never Expires' }}</td>
            <td class="px-5 py-3.5 text-center">
              @if($isActive)
                <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
              @else
                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-stone-100 text-stone-500 border border-stone-200">Inactive</span>
              @endif
            </td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
              <a href="{{ route('admin.coupons.edit', $coupon) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200 transition">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete coupon \'{{ $coupon->code }}\'?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer transition">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-5 py-12 text-center text-stone-400 font-bold">No coupons found. Create your first promotional promo code above!</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="pt-2">
    {{ $coupons->links() }}
  </div>
</div>
@endsection
