@extends('layouts.admin')
@php $editing = $coupon->exists; @endphp
@section('title', $editing ? 'Edit coupon' : 'New coupon')
@section('subtitle', $editing ? 'Code ' . $coupon->code . ' · used ' . $coupon->used_count . ' ' . Str::plural('time', $coupon->used_count) : 'Create a discount code for checkout.')

@section('page-actions')
  <a href="{{ route('admin.coupons.index') }}" class="pill-btn">Cancel</a>
  <button type="submit" form="couponForm" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
    {{ $editing ? 'Save changes' : 'Create coupon' }}
  </button>
@endsection

@section('content')
<form id="couponForm" method="POST" action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="max-w-3xl space-y-4">
  @csrf
  @if($editing) @method('PUT') @endif

  <section class="panel p-4 sm:p-5 space-y-4">
    <h2 class="text-[15px] font-semibold text-gray-900">Code</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl" for="coupon_code">Coupon code <span class="text-rose-500">*</span></label>
        <input id="coupon_code" name="code" class="inp font-mono uppercase tracking-wide" value="{{ old('code', $coupon->code) }}" required placeholder="SAVE10" />
        <p class="text-[11px] text-gray-400 mt-1">Saved in capitals. Customers type it at checkout.</p>
      </div>
      <div>
        <label class="lbl" for="coupon_description">Note for staff</label>
        <input id="coupon_description" name="description" class="inp" value="{{ old('description', $coupon->description) }}" placeholder="e.g. Eid campaign, 15% off honey" />
      </div>
    </div>
  </section>

  <section class="panel p-4 sm:p-5 space-y-4">
    <h2 class="text-[15px] font-semibold text-gray-900">Discount</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="lbl" for="couponType">Type</label>
        <select name="type" id="couponType" class="inp cursor-pointer">
          <option value="percentage" @selected(old('type', $coupon->type)==='percentage')>Percentage Discount (%)</option>
          <option value="fixed" @selected(old('type', $coupon->type)==='fixed')>Fixed Amount Discount (৳)</option>
        </select>
      </div>
      <div>
        <label class="lbl" for="coupon_value" id="valueLabel">
          {{ old('type', $coupon->type)==='fixed' ? 'Discount amount (৳)' : 'Discount percentage (%)' }} <span class="text-rose-500">*</span>
        </label>
        <input id="coupon_value" name="value" type="number" step="0.01" min="0.01" class="inp tabular-nums" value="{{ old('value', $coupon->value) }}" required placeholder="e.g. 10 or 150" />
      </div>
      <div>
        <label class="lbl" for="coupon_min">Minimum order (৳)</label>
        <input id="coupon_min" name="min_order_amount" type="number" step="0.01" min="0" class="inp tabular-nums" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" placeholder="No minimum" />
      </div>
      <div id="maxDiscountWrap">
        <label class="lbl" for="coupon_max_discount">Maximum discount (৳)</label>
        <input id="coupon_max_discount" name="max_discount" type="number" step="0.01" min="0" class="inp tabular-nums" value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Optional cap for % coupons" />
      </div>
    </div>
  </section>

  <section class="panel p-4 sm:p-5 space-y-4">
    <h2 class="text-[15px] font-semibold text-gray-900">Limits and dates</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="lbl" for="coupon_max_uses">Total uses allowed</label>
        <input id="coupon_max_uses" name="max_uses" type="number" min="1" class="inp tabular-nums" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="No limit" />
        @if($editing)
          <p class="text-[11px] text-gray-400 mt-1">Used {{ $coupon->used_count }} time(s) so far.</p>
        @endif
      </div>
      <div>
        <label class="lbl" for="coupon_starts">Starts</label>
        <input id="coupon_starts" name="starts_at" type="datetime-local" class="inp" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" />
      </div>
      <div>
        <label class="lbl" for="coupon_expires">Expires</label>
        <input id="coupon_expires" name="expires_at" type="datetime-local" class="inp" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}" />
      </div>
    </div>

    <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
      <input type="checkbox" name="is_active" value="1" class="h-4 w-4 mt-0.5 rounded cursor-pointer" @checked(old('is_active', $coupon->is_active ?? true)) />
      <span>
        <span class="block text-[13px] font-medium text-gray-900">Active</span>
        <span class="block text-xs text-gray-500">Customers can use this code at checkout.</span>
      </span>
    </label>
  </section>

  <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 lg:hidden">
    <a href="{{ route('admin.coupons.index') }}" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center justify-center">Cancel</a>
    <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center cursor-pointer" style="background: var(--brand-dark);">{{ $editing ? 'Save changes' : 'Create coupon' }}</button>
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
    valueLabel.innerHTML = (isFixed ? 'Discount amount (৳)' : 'Discount percentage (%)') + ' <span class="text-rose-500">*</span>';
    maxWrap.style.display = isFixed ? 'none' : '';
  }
  type.addEventListener('change', sync);
  sync();
})();
</script>
@endpush
