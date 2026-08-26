@extends('layouts.storefront')

@php
  $title = 'Checkout';
  $shippingZone = old('shipping_zone', 'inside_dhaka');
  $taxable = max(0, $subtotal - $discount);
@endphp

@section('body_class', 'bg-slate-50 text-ink antialiased')

@section('checkout_header')
  <header class="bg-white border-b border-slate-100">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-16 lg:h-20 flex items-center justify-between">
      @include('partials.brand')
      <div class="flex items-center gap-2 text-sm text-slate-500"><svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7z"/><path stroke-linecap="round" d="m9 12 2 2 4-4"/></svg> Secure Checkout</div>
    </div>
  </header>
@endsection

@section('content')
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 pt-8">
    <ol class="flex items-center gap-3 text-sm font-medium">
      <li class="flex items-center gap-2 text-brand-700"><span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-white text-xs">1</span> <a href="{{ route('cart.index') }}" class="hover:underline">Cart</a></li>
      <li class="h-px w-8 bg-brand-300"></li>
      <li class="flex items-center gap-2 text-brand-700"><span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-white text-xs">2</span> Delivery</li>
      <li class="h-px w-8 bg-slate-200"></li>
      <li class="flex items-center gap-2 text-slate-400"><span class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-slate-500 text-xs">3</span> Payment</li>
    </ol>
  </div>

  <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
    @if($errors->any())
      <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="list-disc list-inside mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="lg:grid lg:grid-cols-[1fr_380px] lg:gap-8 lg:items-start">
      <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" class="space-y-6">
        @csrf

        <div class="rounded-2xl bg-white p-6 border border-slate-100 shadow-2xs">
          <h2 class="font-display text-lg font-extrabold text-stone-900">Contact Information</h2>
          <div class="mt-4 grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-sm font-semibold text-stone-800 mb-1.5">Full Name <span class="text-red-500">*</span></label>
              <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', $user?->name) }}" required placeholder="e.g. Rahim Ahmed" class="w-full rounded-xl border @error('customer_name') border-red-400 bg-red-50/20 @else border-slate-200 @enderror px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
              @error('customer_name')<p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-sm font-semibold text-stone-800 mb-1.5">Mobile Phone <span class="text-red-500">*</span></label>
              <div class="relative">
                <input type="tel" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $user?->phone) }}" required placeholder="01XXXXXXXXX" inputmode="numeric" maxlength="14" pattern="^(?:\+?88)?01[3-9]\d{8}$" class="w-full rounded-xl border @error('customer_phone') border-red-400 bg-red-50/20 @else border-slate-200 @enderror px-4 py-3 text-sm font-mono tracking-wide focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
              </div>
              <p class="text-[11px] text-stone-500 mt-1">Must be an 11-digit mobile number (e.g. 017XXXXXXXX)</p>
              @error('customer_phone')<p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>
            
            <div class="sm:col-span-2">
              <label class="block text-sm font-semibold text-stone-800 mb-1.5">Email Address <span class="text-stone-400 font-normal text-xs">(optional)</span></label>
              <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', $user?->email) }}" placeholder="you@example.com" class="w-full rounded-xl border @error('customer_email') border-red-400 bg-red-50/20 @else border-slate-200 @enderror px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" />
              @error('customer_email')<p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Delivery address</h2>
          <div class="mt-4 grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Address</label><input name="shipping_address" value="{{ old('shipping_address', $user?->address) }}" required placeholder="House, road, area" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div><label class="block text-sm font-medium mb-1.5">City</label><input name="city" value="{{ old('city', $user?->city) }}" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div><label class="block text-sm font-medium mb-1.5">Postal code</label><input name="postal_code" value="{{ old('postal_code', $user?->postal_code) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" /></div>
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium mb-1.5">Delivery zone</label>
              <select name="shipping_zone" id="shippingZone" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300">
                <option value="inside_dhaka" data-fee="{{ $shipInside }}" @selected($shippingZone === 'inside_dhaka')>{{ shipping_zone_label('inside_dhaka') }} (+{{ money($shipInside) }})</option>
                <option value="outside_dhaka" data-fee="{{ $shipOutside }}" @selected($shippingZone === 'outside_dhaka')>{{ shipping_zone_label('outside_dhaka') }} (+{{ money($shipOutside) }})</option>
              </select>
            </div>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100 shadow-2xs">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <span class="w-1.5 h-6 bg-brand-500 rounded-full inline-block"></span>
              <h2 class="font-display text-lg font-bold text-stone-900">Payment method</h2>
            </div>
            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-full uppercase tracking-wider">🔒 Secure</span>
          </div>

          @php
            $method = old('payment_method', 'cod');
            $bkash = setting('bkash_number');
            $bkashType = setting('bkash_type', 'personal');
            $nagad = setting('nagad_number');
            $nagadType = setting('nagad_type', 'personal');
            $rocket = setting('rocket_number');
            $rocketType = setting('rocket_type', 'personal');
            $showCod = (string) setting('pay_cod_enabled', '1') === '1';
            $showBkash = (string) setting('pay_bkash_enabled', '1') === '1' && (bool) $bkash;
            $showNagad = (string) setting('pay_nagad_enabled', '1') === '1' && (bool) $nagad;
            $showRocket = (string) setting('pay_rocket_enabled', '1') === '1' && (bool) $rocket;
            $payNumbers = [
              'bkash' => $bkash,
              'nagad' => $nagad,
              'rocket' => $rocket,
            ];
            $payTypes = [
              'bkash' => $bkashType,
              'nagad' => $nagadType,
              'rocket' => $rocketType,
            ];
            $payNames = [
              'bkash' => 'bKash',
              'nagad' => 'Nagad',
              'rocket' => 'Rocket',
            ];
          @endphp

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            {{-- 1. Cash on Delivery (COD) --}}
            @if($showCod)
            <label class="pay-opt group relative flex items-center justify-between gap-3 rounded-xl border-2 p-3 sm:p-3.5 cursor-pointer transition-all duration-200 {{ $method==='cod' ? 'border-brand-500 bg-brand-50/50 shadow-xs' : 'border-stone-300 hover:border-stone-400 bg-white hover:bg-stone-50/50' }}">
              <input type="radio" name="payment_method" value="cod" @checked($method==='cod') class="pay-radio sr-only" data-manual="0" data-pay-number="" data-pay-type="cod" data-pay-name="Cash On Delivery" />
              
              <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center p-1 overflow-hidden shrink-0 shadow-2xs">
                  @if(payment_icon_url('cod'))
                    <img src="{{ payment_icon_url('cod') }}" class="h-full w-full object-contain" alt="COD">
                  @else
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path stroke-linecap="round" d="M6 12h.01M18 12h.01"/></svg>
                  @endif
                </div>

                <div class="min-w-0">
                  <span class="block text-sm font-semibold text-stone-900 group-hover:text-brand-600 transition-colors">Cash On Delivery</span>
                </div>
              </div>

              {{-- Checkmark indicator --}}
              <div class="pay-check shrink-0 {{ $method==='cod' ? 'flex' : 'hidden' }} h-5 w-5 rounded-full bg-brand-500 text-white items-center justify-center shadow-xs">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
            </label>
            @endif

            {{-- 2. bKash --}}
            @if($showBkash)
            <label class="pay-opt group relative flex items-center justify-between gap-3 rounded-xl border-2 p-3 sm:p-3.5 cursor-pointer transition-all duration-200 {{ $method==='bkash' ? 'border-brand-500 bg-brand-50/50 shadow-xs' : 'border-stone-300 hover:border-stone-400 bg-white hover:bg-stone-50/50' }}">
              <input type="radio" name="payment_method" value="bkash" @checked($method==='bkash') class="pay-radio sr-only" data-manual="1" data-pay-number="{{ $bkash }}" data-pay-type="{{ $bkashType }}" data-pay-name="bKash" />

              <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg bg-[#E2136E] text-white flex items-center justify-center p-1.5 overflow-hidden shrink-0 shadow-2xs font-extrabold text-xs">
                  @if(payment_icon_url('bkash'))
                    <img src="{{ payment_icon_url('bkash') }}" class="h-full w-full object-contain" alt="bKash">
                  @else
                    <svg class="h-6 w-6 text-white" viewBox="0 0 32 32" fill="currentColor">
                      <path d="M19.9 3.5l-9.8 11.2 9.1 2.3-4.8 11.5 12.1-13.4-9.3-2.1z"/>
                    </svg>
                  @endif
                </div>

                <div class="min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="block text-sm font-semibold text-stone-900 group-hover:text-brand-600 transition-colors">Bkash</span>
                    <span class="rounded-full bg-pink-50 text-pink-700 text-[10px] font-bold px-1.5 py-0.2">{{ $bkashType === 'merchant' ? 'Payment' : 'Send Money' }}</span>
                    @if($freeShippingOnOnline ?? false)
                      <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5">Free Delivery</span>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Checkmark indicator --}}
              <div class="pay-check shrink-0 {{ $method==='bkash' ? 'flex' : 'hidden' }} h-5 w-5 rounded-full bg-brand-500 text-white items-center justify-center shadow-xs">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
            </label>
            @endif

            {{-- 3. Nagad --}}
            @if($showNagad)
            <label class="pay-opt group relative flex items-center justify-between gap-3 rounded-xl border-2 p-3 sm:p-3.5 cursor-pointer transition-all duration-200 {{ $method==='nagad' ? 'border-brand-500 bg-brand-50/50 shadow-xs' : 'border-stone-300 hover:border-stone-400 bg-white hover:bg-stone-50/50' }}">
              <input type="radio" name="payment_method" value="nagad" @checked($method==='nagad') class="pay-radio sr-only" data-manual="1" data-pay-number="{{ $nagad }}" data-pay-type="{{ $nagadType }}" data-pay-name="Nagad" />

              <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg bg-[#F7941D] text-white flex items-center justify-center p-1.5 overflow-hidden shrink-0 shadow-2xs font-extrabold text-xs">
                  @if(payment_icon_url('nagad'))
                    <img src="{{ payment_icon_url('nagad') }}" class="h-full w-full object-contain" alt="Nagad">
                  @else
                    <span class="font-bold text-[10px] tracking-tighter">NAGAD</span>
                  @endif
                </div>

                <div class="min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="block text-sm font-semibold text-stone-900 group-hover:text-brand-600 transition-colors">Nagad</span>
                    <span class="rounded-full bg-orange-50 text-orange-700 text-[10px] font-bold px-1.5 py-0.2">{{ $nagadType === 'merchant' ? 'Payment' : 'Send Money' }}</span>
                    @if($freeShippingOnOnline ?? false)
                      <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5">Free Delivery</span>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Checkmark indicator --}}
              <div class="pay-check shrink-0 {{ $method==='nagad' ? 'flex' : 'hidden' }} h-5 w-5 rounded-full bg-brand-500 text-white items-center justify-center shadow-xs">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
            </label>
            @endif

            {{-- 4. Rocket --}}
            @if($showRocket)
            <label class="pay-opt group relative flex items-center justify-between gap-3 rounded-xl border-2 p-3 sm:p-3.5 cursor-pointer transition-all duration-200 {{ $method==='rocket' ? 'border-brand-500 bg-brand-50/50 shadow-xs' : 'border-stone-300 hover:border-stone-400 bg-white hover:bg-stone-50/50' }}">
              <input type="radio" name="payment_method" value="rocket" @checked($method==='rocket') class="pay-radio sr-only" data-manual="1" data-pay-number="{{ $rocket }}" data-pay-type="{{ $rocketType }}" data-pay-name="Rocket" />

              <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg bg-[#8C3494] text-white flex items-center justify-center p-1.5 overflow-hidden shrink-0 shadow-2xs font-extrabold text-xs">
                  @if(payment_icon_url('rocket'))
                    <img src="{{ payment_icon_url('rocket') }}" class="h-full w-full object-contain" alt="Rocket">
                  @else
                    <span class="font-bold text-[10px] tracking-tighter">ROCKET</span>
                  @endif
                </div>

                <div class="min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="block text-sm font-semibold text-stone-900 group-hover:text-brand-600 transition-colors">Rocket</span>
                    <span class="rounded-full bg-purple-50 text-purple-700 text-[10px] font-bold px-1.5 py-0.2">{{ $rocketType === 'merchant' ? 'Payment' : 'Send Money' }}</span>
                    @if($freeShippingOnOnline ?? false)
                      <span class="rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5">Free Delivery</span>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Checkmark indicator --}}
              <div class="pay-check shrink-0 {{ $method==='rocket' ? 'flex' : 'hidden' }} h-5 w-5 rounded-full bg-brand-500 text-white items-center justify-center shadow-xs">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
            </label>
            @endif
          </div>

          {{-- Manual Payment Instruction & Input Box --}}
          @php
            $currentType = $payTypes[$method] ?? 'personal';
            $currentName = $payNames[$method] ?? 'Mobile Banking';
            $currentNumber = $payNumbers[$method] ?? '';
          @endphp
          <div id="manualFields" class="mt-5 rounded-2xl bg-stone-50/80 p-4 sm:p-5 border border-stone-200/90 space-y-4 {{ $method==='cod' ? 'hidden' : '' }}">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-stone-200 shadow-2xs">
              <div class="flex items-center gap-2.5 flex-wrap">
                <span id="manualTypeBadge" class="rounded-lg {{ $currentType === 'merchant' ? 'bg-purple-100 text-purple-800' : 'bg-brand-100 text-brand-800' }} text-[11px] font-bold px-2.5 py-1 uppercase tracking-wide">
                  {{ $currentType === 'merchant' ? 'Merchant Payment' : 'Personal Send Money' }}
                </span>
                <span class="text-xs text-stone-500 font-medium"><span id="manualMethodName">{{ $currentName }}</span> Number:</span>
                <span id="manualPayNumber" class="text-sm font-bold font-mono text-stone-900">{{ $currentNumber ?: '—' }}</span>
              </div>
              <button type="button" id="copyNumberBtn" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-stone-200 bg-stone-50 hover:bg-stone-100 text-stone-700 transition cursor-pointer shrink-0">
                <svg class="h-3.5 w-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span id="copyBtnText">Copy Number</span>
              </button>
            </div>

            <!-- Step by Step instructions -->
            <div class="text-xs text-stone-600 space-y-2 bg-brand-50/40 p-3.5 rounded-xl border border-brand-100/80">
              <p class="font-bold text-stone-800 flex items-center gap-1.5">
                <span>📋</span> How to pay:
              </p>
              <div id="personalSteps" class="{{ $currentType === 'merchant' ? 'hidden' : 'space-y-1' }}">
                <p>1. Open your <b class="text-stone-900 method-label">{{ $currentName }}</b> app and tap <b>"Send Money"</b>.</p>
                <p>2. Enter Number: <b class="number-label font-mono text-stone-900">{{ $currentNumber }}</b> &amp; Amount: <b class="amount-label text-stone-900">{{ money($totals['total']) }}</b>.</p>
                <p>3. Enter your PIN to complete the transaction.</p>
                <p>4. Enter your Sender Number &amp; Transaction ID (TrxID) below to place order.</p>
              </div>
              <div id="merchantSteps" class="{{ $currentType === 'merchant' ? 'space-y-1' : 'hidden' }}">
                <p>1. Open your <b class="text-stone-900 method-label">{{ $currentName }}</b> app and tap <b>"Make Payment"</b> (or Payment).</p>
                <p>2. Enter Merchant Number: <b class="number-label font-mono text-stone-900">{{ $currentNumber }}</b> &amp; Amount: <b class="amount-label text-stone-900">{{ money($totals['total']) }}</b>.</p>
                <p>3. Enter Reference: (Your Phone Number) and PIN to complete payment.</p>
                <p>4. Enter your Sender Number &amp; Transaction ID (TrxID) below to place order.</p>
              </div>
            </div>
            
            <div class="grid sm:grid-cols-2 gap-3 pt-1">
              <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">Your Sender Mobile Number <span class="text-red-500">*</span></label>
                <input name="payment_sender_number" value="{{ old('payment_sender_number') }}" placeholder="01XXXXXXXXX" class="w-full rounded-xl border @error('payment_sender_number') border-red-400 bg-red-50/20 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500" />
                @error('payment_sender_number')<p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-xs font-bold text-stone-700 mb-1">Transaction ID (TrxID) <span class="text-red-500">*</span></label>
                <input name="payment_txn_id" value="{{ old('payment_txn_id') }}" placeholder="e.g. 9J4K2L8X" class="w-full rounded-xl border @error('payment_txn_id') border-red-400 bg-red-50/20 @else border-slate-200 @enderror bg-white px-3.5 py-2.5 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-brand-500" />
                @error('payment_txn_id')<p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>@enderror
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-2.5 pt-2">
          <button type="submit" id="submitOrderBtn" class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-6 transition-colors shadow-xs text-base flex items-center justify-center gap-2 cursor-pointer">
            <span>Place Order</span>
            <span>·</span>
            <span id="placeTotal">{{ money($totals['total']) }}</span>
          </button>

          <p class="text-center text-xs text-slate-400">By placing your order you agree to our <a href="{{ route('terms') }}" class="underline hover:text-brand-600">Terms</a> &amp; <a href="{{ route('privacy') }}" class="underline hover:text-brand-600">Privacy Policy</a>.</p>
        </div>
      </form>

      <aside class="mt-8 lg:mt-0 space-y-4">
        <div class="rounded-2xl bg-white p-6 border border-slate-100">
          <h2 class="font-display text-lg font-extrabold">Coupon code</h2>
          @if($couponCode)
            <div class="mt-3 flex items-center justify-between gap-3 bg-brand-50 border border-brand-200 rounded-xl px-4 py-3">
              <div>
                <p class="font-mono font-bold text-brand-700">{{ $couponCode }}</p>
                <p class="text-xs text-brand-600">You save {{ money($discount) }}</p>
              </div>
              <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600">Remove</button>
              </form>
            </div>
          @else
            <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="mt-3 flex gap-2">
              @csrf
              <input name="code" value="{{ old('code') }}" placeholder="Promo code" required class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-300" />
              <button type="submit" class="rounded-xl bg-brand-600 text-white text-sm font-semibold px-4 hover:bg-brand-700 transition">Apply</button>
            </form>
            @error('coupon')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
          @endif
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-100 lg:sticky lg:top-8">
          <h2 class="font-display text-lg font-extrabold">Your order</h2>
          <div class="mt-5 space-y-4">
            @foreach($items as $item)
              <div class="space-y-3 rounded-2xl border border-slate-100 p-4">
                <div class="flex gap-3 items-start">
                  <div class="relative"><img src="{{ $item->image }}" loading="lazy" decoding="async" class="h-14 w-14 rounded-xl object-cover" alt="{{ $item->name }}" /><span class="absolute -top-2 -right-2 grid h-5 w-5 place-items-center rounded-full bg-brand-600 text-white text-[10px] font-bold">{{ $item->qty }}</span></div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <p class="text-sm font-semibold truncate">{{ $item->name }}</p>
                        <p class="text-xs text-slate-500">{{ $item->variant ?: $item->product->unit }}</p>
                      </div>
                      <span class="text-sm font-bold">{{ money($item->line_total) }}</span>
                    </div>
                    <div class="mt-3">
                      <form method="POST" action="{{ route('cart.update') }}" class="inline-flex w-max items-center rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden h-8">
                        @csrf
                        <input type="hidden" name="key" value="{{ $item->key }}">
                        <button type="submit" name="qty" value="{{ max(0, $item->qty - 1) }}" class="flex-none w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                          </svg>
                        </button>
                        <div class="flex-none w-8 h-8 flex items-center justify-center border-x border-gray-200 font-semibold text-gray-800">{{ $item->qty }}</div>
                        <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="flex-none w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <dl class="mt-5 space-y-2.5 text-sm border-t border-slate-100 pt-5">
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold">{{ money($subtotal) }}</dd></div>
            @if($discount > 0)
              <div class="flex justify-between text-brand-600"><dt>Discount ({{ $couponCode }})</dt><dd class="font-semibold" id="sumDiscount">−{{ money($discount) }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd class="font-semibold text-brand-600" id="sumShipping">{{ money($totals['shipping']) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Tax ({{ rtrim(rtrim(number_format($taxPercent, 2), '0'), '.') }}%)</dt><dd class="font-semibold" id="sumTax">{{ money($totals['tax']) }}</dd></div>
          </dl>
          <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between"><span class="font-semibold">Total</span><span class="font-display text-2xl font-extrabold text-brand-700" id="sumTotal">{{ money($totals['total']) }}</span></div>
          @if(trim((string) setting('delivery_eta_text', '')) !== '')
            <div class="mt-5 flex items-center gap-2.5 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-800"><svg class="h-5 w-5 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg> {{ setting('delivery_eta_text') }}</div>
          @endif
        </div>
      </aside>
    </div>
  </section>
@endsection

@push('scripts')
<script>
(function () {
  var subtotal = {{ $subtotal }};
  var discount = {{ $discount }};
  var taxPct = {{ $taxPercent }};
  var freeShippingOnOnline = @json($freeShippingOnOnline ?? false);
  var zone = document.getElementById('shippingZone');
  var currencySymbol = @json(currency_symbol());
  var money = function (n) {
    return currencySymbol + ' ' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  };

  function recalc() {
    var zoneFee = parseFloat(zone.options[zone.selectedIndex].dataset.fee) || 0;
    var selectedRadio = document.querySelector('.pay-radio:checked');
    var isOnline = selectedRadio && selectedRadio.value !== 'cod';
    var isFreeShipping = freeShippingOnOnline && isOnline;
    var fee = isFreeShipping ? 0 : zoneFee;

    var taxable = Math.max(0, subtotal - discount);
    var tax = Math.round(taxable * taxPct / 100);
    var total = taxable + fee + tax;

    var sumShippingEl = document.getElementById('sumShipping');
    if (sumShippingEl) {
      if (isFreeShipping) {
        sumShippingEl.innerHTML = '<span class="text-emerald-600 font-bold">FREE (৳0)</span> <span class="text-xs text-stone-400 line-through">' + money(zoneFee) + '</span>';
      } else {
        sumShippingEl.textContent = money(fee);
      }
    }

    document.getElementById('sumTax').textContent = money(tax);
    document.getElementById('sumTotal').textContent = money(total);
    document.getElementById('placeTotal').textContent = money(total);

    var manualAmt = document.getElementById('manualPayAmount');
    if (manualAmt) manualAmt.textContent = money(total);
  }

  if (zone) zone.addEventListener('change', recalc);

  document.querySelectorAll('.pay-radio').forEach(function (r) {
    r.addEventListener('change', function () {
      document.querySelectorAll('.pay-opt').forEach(function (o) {
        o.classList.remove('border-brand-500', 'bg-brand-50/50', 'shadow-xs');
        o.classList.add('border-stone-300');
        var chk = o.querySelector('.pay-check');
        if (chk) {
          chk.classList.remove('flex');
          chk.classList.add('hidden');
        }
      });
      var label = r.closest('.pay-opt');
      label.classList.add('border-brand-500', 'bg-brand-50/50', 'shadow-xs');
      label.classList.remove('border-stone-300');
      var myChk = label.querySelector('.pay-check');
      if (myChk) {
        myChk.classList.remove('hidden');
        myChk.classList.add('flex');
      }

      var isManual = r.dataset.manual === '1';
      document.getElementById('manualFields').classList.toggle('hidden', !isManual);

      if (isManual) {
        var num = r.dataset.payNumber || '';
        var pType = r.dataset.payType || 'personal';
        var pName = r.dataset.payName || 'Payment';

        var numEl = document.getElementById('manualPayNumber');
        if (numEl) numEl.textContent = num || "—";

        var nameEl = document.getElementById('manualMethodName');
        if (nameEl) nameEl.textContent = pName;

        document.querySelectorAll('.method-label').forEach(function (el) {
          el.textContent = pName;
        });
        document.querySelectorAll('.number-label').forEach(function (el) {
          el.textContent = num;
        });

        var badgeEl = document.getElementById('manualTypeBadge');
        var personalSteps = document.getElementById('personalSteps');
        var merchantSteps = document.getElementById('merchantSteps');

        if (pType === 'merchant') {
          if (badgeEl) {
            badgeEl.textContent = 'Merchant Payment';
            badgeEl.className = 'rounded-lg bg-purple-100 text-purple-800 text-[11px] font-bold px-2.5 py-1 uppercase tracking-wide';
          }
          if (personalSteps) personalSteps.classList.add('hidden');
          if (merchantSteps) merchantSteps.classList.remove('hidden');
        } else {
          if (badgeEl) {
            badgeEl.textContent = 'Personal Send Money';
            badgeEl.className = 'rounded-lg bg-brand-100 text-brand-800 text-[11px] font-bold px-2.5 py-1 uppercase tracking-wide';
          }
          if (personalSteps) personalSteps.classList.remove('hidden');
          if (merchantSteps) merchantSteps.classList.add('hidden');
        }
      }

      recalc();
    });
  });

  // Copy number button handler
  var copyBtn = document.getElementById('copyNumberBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      var selectedRadio = document.querySelector('.pay-radio:checked');
      var num = selectedRadio ? selectedRadio.dataset.payNumber : '';
      if (!num) return;

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(num);
      } else {
        var tempInput = document.createElement('input');
        tempInput.value = num;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
      }

      var btnText = document.getElementById('copyBtnText');
      if (btnText) {
        var orig = btnText.textContent;
        btnText.textContent = 'Copied!';
        setTimeout(function () {
          btnText.textContent = orig;
        }, 2000);
      }
      if (window.showToast) {
        window.showToast('Number ' + num + ' copied to clipboard!', 'success');
      }
    });
  }

  recalc();

  // Auto-paste and auto-apply coupon code from URL or sessionStorage
  const urlParams = new URLSearchParams(window.location.search);
  let autoCoupon = urlParams.get('coupon');
  if (!autoCoupon) {
    try {
      autoCoupon = sessionStorage.getItem('auto_apply_coupon');
    } catch(e) {}
  }
  if (autoCoupon) {
    try {
      sessionStorage.removeItem('auto_apply_coupon');
    } catch(e) {}
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput && !codeInput.value) {
      codeInput.value = autoCoupon;
      const couponForm = codeInput.closest('form');
      if (couponForm) {
        if (window.showToast) {
          window.showToast('Coupon ' + autoCoupon + ' applied!', 'success');
        }
        setTimeout(function() {
          couponForm.submit();
        }, 300);
      }
    }
  }
  // Optimized ultra-fast guest contact sync (zero speed impact)
  (function () {
    const nameInput = document.querySelector('input[name="customer_name"]');
    const phoneInput = document.querySelector('input[name="customer_phone"]');
    const emailInput = document.querySelector('input[name="customer_email"]');
    const zoneSelect = document.getElementById('shippingZone');
    let lastPayload = '';

    function syncContact() {
      const name = nameInput ? nameInput.value.trim() : '';
      const phone = phoneInput ? phoneInput.value.trim() : '';
      const email = emailInput ? emailInput.value.trim() : '';
      const zone = zoneSelect ? zoneSelect.value : 'inside_dhaka';

      if (!phone && !name && !email) return;

      const payload = JSON.stringify({
        customer_name: name,
        customer_phone: phone,
        customer_email: email,
        shipping_zone: zone
      });

      if (payload === lastPayload) return;
      lastPayload = payload;

      fetch('{{ route("checkout.sync-contact") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: payload,
        keepalive: true
      }).catch(function () {});
    }

    [nameInput, phoneInput, emailInput].forEach(function (inp) {
      if (!inp) return;
      inp.addEventListener('change', syncContact);
      inp.addEventListener('blur', syncContact);
    });

    // Real-time phone sanitizer: only allows numbers and leading +
    if (phoneInput) {
      phoneInput.addEventListener('input', function() {
        var val = this.value;
        var hasPlus = val.startsWith('+');
        var digits = val.replace(/\D/g, '');
        if (hasPlus) {
          this.value = '+' + digits.slice(0, 13);
        } else {
          this.value = digits.slice(0, 11);
        }
      });
    }

    // Client-side checkout form validation before submit
    var form = document.getElementById('checkoutForm');
    if (form && phoneInput) {
      form.addEventListener('submit', function(e) {
        var phoneVal = phoneInput.value.replace(/\D/g, '');
        var bdPhoneRegex = /^(?:88)?01[3-9]\d{8}$/;
        
        if (!bdPhoneRegex.test(phoneVal)) {
          e.preventDefault();
          alert('Please enter a valid 11-digit mobile number starting with 01 (e.g. 017XXXXXXXX).');
          phoneInput.focus();
          phoneInput.classList.add('border-red-500', 'bg-red-50/20');
          return false;
        }

        if (emailInput && emailInput.value.trim() !== '') {
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(emailInput.value.trim())) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            emailInput.focus();
            emailInput.classList.add('border-red-500', 'bg-red-50/20');
            return false;
          }
        }
      });
    }
  })();
})();
</script>
@endpush
