@extends('layouts.admin')
@php $editing = $coupon->exists; @endphp
@section('title', $editing ? 'Edit Coupon: ' . $coupon->code : 'Create New Coupon')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="space-y-6">
  @csrf
  @if($editing) @method('PUT') @endif

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <a href="{{ route('admin.coupons.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 mb-1">
        &larr; Back to Coupons List
      </a>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight">
        {{ $editing ? 'Edit Coupon: ' . $coupon->code : '🎟️ Create New Coupon' }}
      </h1>
    </div>
    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">
      {{ $editing ? '💾 Update Coupon' : '✨ Save Promo Code' }}
    </button>
  </div>

  <div class="max-w-3xl space-y-6">

    {{-- Main Coupon Details Card --}}
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <h2 class="text-base font-extrabold text-stone-900 border-b border-stone-100 pb-3 flex items-center gap-2">
        <span>⚙️ Coupon Rules &amp; Discount Configuration</span>
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-extrabold text-stone-800 block mb-1.5">Coupon Code <span class="text-rose-500">*</span></label>
          <input name="code" class="w-full text-sm font-black tracking-wider uppercase px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs font-mono text-brand-700" value="{{ old('code', $coupon->code) }}" required placeholder="e.g. SAVE10, ORGANIC20" />
          <p class="text-[10px] text-stone-400 mt-1">Automatically converted to uppercase. Customers enter this code at checkout.</p>
        </div>

        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Internal Description</label>
          <input name="description" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('description', $coupon->description) }}" placeholder="e.g. Eid Campaign 15% discount for organic honey" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Discount Type</label>
          <select name="type" id="couponType" class="w-full text-xs font-extrabold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs cursor-pointer">
            <option value="percentage" @selected(old('type', $coupon->type)==='percentage')>Percentage Discount (%)</option>
            <option value="fixed" @selected(old('type', $coupon->type)==='fixed')>Fixed Amount Discount (৳)</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-extrabold text-stone-800 block mb-1.5" id="valueLabel">
            {{ old('type', $coupon->type)==='fixed' ? 'Discount Amount (৳)' : 'Discount Percentage (%)' }} <span class="text-rose-500">*</span>
          </label>
          <input name="value" type="number" step="0.01" min="0.01" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('value', $coupon->value) }}" required placeholder="e.g. 10 or 150" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Minimum Order Subtotal (৳)</label>
          <input name="min_order_amount" type="number" step="0.01" min="0" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" placeholder="Leave blank for no min order requirement" />
        </div>

        <div id="maxDiscountWrap">
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Maximum Discount Cap Amount (৳)</label>
          <input name="max_discount" type="number" step="0.01" min="0" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Optional limit for % coupons" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 pt-2 border-t border-stone-100">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Max Total Uses Limit</label>
          <input name="max_uses" type="number" min="1" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Blank = Unlimited" />
          @if($editing)
            <p class="text-[10px] text-stone-400 font-mono mt-1">Used {{ $coupon->used_count }} time(s) so far.</p>
          @endif
        </div>

        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Start Date &amp; Time</label>
          <input name="starts_at" type="datetime-local" class="w-full text-xs font-semibold px-3 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" />
        </div>

        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1.5">Expiration Date &amp; Time</label>
          <input name="expires_at" type="datetime-local" class="w-full text-xs font-semibold px-3 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}" />
        </div>
      </div>

      {{-- Active Toggle --}}
      <div class="pt-4 border-t border-stone-100 bg-stone-50/70 p-4 rounded-xl border border-stone-200">
        <label class="flex items-center gap-2.5 text-xs font-extrabold text-stone-800 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-brand-600 rounded cursor-pointer" @checked(old('is_active', $coupon->is_active ?? true)) /> 
          <span>Active &amp; Ready for Customers (Allow promo code at storefront checkout)</span>
        </label>
      </div>

    </div>

  </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
  var type = document.getElementById('couponType');
  var valueLabel = document.getElementById('valueLabel');
  var maxWrap = document.getElementById('maxDiscountWrap');
  if (!type || !valueLabel || !maxWrap) return;

  function sync() {
    var isFixed = type.value === 'fixed';
    valueLabel.innerHTML = (isFixed ? 'Discount Amount (৳)' : 'Discount Percentage (%)') + ' <span class="text-rose-500">*</span>';
    maxWrap.style.display = isFixed ? 'none' : '';
  }
  type.addEventListener('change', sync);
  sync();
})();
</script>
@endpush
