@extends('layouts.storefront')

@php
  $title = 'Checkout';
  $shippingZone = old('shipping_zone', 'inside_dhaka');
  $taxable = max(0, $subtotal - $discount);
@endphp

@section('body_class', 'bg-slate-50 text-ink antialiased')

@section('checkout_header')
  <header class="bg-white border-b border-slate-100">
    <div class="mx-auto max-w-6xl px-3.5 sm:px-6 lg:px-8 h-14 sm:h-16 lg:h-20 flex items-center justify-between">
      <div class="flex items-center gap-2 sm:gap-3">
        @include('partials.brand')
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-slate-500 font-medium shrink-0">
        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-emerald-600 shrink-0" style="color: #10b981 !important;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l8 4v5c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7z"/><path stroke-linecap="round" d="m9 12 2 2 4-4"/></svg>
        <span>Secure Checkout</span>
      </div>
    </div>
  </header>
@endsection

@section('content')
  {{-- Progress Stepper (Responsive) --}}
  <div class="mx-auto max-w-6xl px-3.5 sm:px-6 lg:px-8 pt-4 sm:pt-6 lg:pt-8">
    <div class="bg-white rounded-2xl border border-stone-200/90 shadow-2xs py-2.5 px-3.5 sm:py-3 sm:px-6 overflow-x-auto no-scrollbar">
      <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-6 min-w-max">
        
        {{-- Step 1: Cart (Completed) --}}
        <a href="{{ route('cart.index') }}" class="group flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-extrabold text-stone-600 hover:text-brand-700 transition shrink-0">
          <span class="h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] sm:text-xs flex items-center justify-center font-bold shadow-2xs">
            ✓
          </span>
          <span class="group-hover:underline">Cart</span>
        </a>

        {{-- Divider 1 --}}
        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-stone-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Step 2: Shipping & Delivery (Active) --}}
        <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-black text-brand-700 shrink-0">
          <span class="h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-brand-600 text-white text-[10px] sm:text-xs flex items-center justify-center font-black shadow-xs">
            2
          </span>
          <span>Delivery &amp; Details</span>
        </div>

        {{-- Divider 2 --}}
        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-stone-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Step 3: Payment Confirmation (Upcoming) --}}
        <div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold text-stone-400 shrink-0">
          <span class="h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-stone-100 text-stone-500 border border-stone-200 text-[10px] sm:text-xs flex items-center justify-center font-bold">
            3
          </span>
          <span class="hidden sm:inline">Confirmation</span>
          <span class="sm:hidden">Payment</span>
        </div>

      </div>
    </div>
  </div>

  <section class="mx-auto max-w-6xl px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 pb-28 lg:pb-8">
    @if($errors->any())
      <div class="mb-4 sm:mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm px-4 py-3 shadow-2xs">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="list-disc list-inside mt-1 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 lg:gap-8 items-start">
      
      {{-- Mobile-only TOP: Your order (Items List with cross icons) --}}
      <div class="lg:hidden order-1 rounded-2xl bg-white p-4 border border-slate-200/80 shadow-2xs space-y-3.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
            <h2 class="font-display text-base font-extrabold text-slate-900">Your order</h2>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">({{ $items->sum('qty') }} {{ Str::plural('item', $items->sum('qty')) }})</span>
          </div>
          <button type="button" id="mobileOrderSummaryToggle" class="flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 select-none cursor-pointer bg-brand-50 px-2.5 py-1 rounded-lg transition-colors">
            <span id="mobileOrderSummaryText">Hide details</span>
            <svg id="mobileOrderSummaryChevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
        </div>

        <div id="mobileOrderSummaryBody" class="space-y-3">
          @foreach($items as $item)
            <div class="space-y-2.5 rounded-2xl border border-slate-100 p-3 bg-slate-50/40">
              <div class="flex gap-2.5 items-start">
                <div class="relative shrink-0">
                  <img src="{{ $item->image }}" loading="lazy" decoding="async" class="h-12 w-12 rounded-xl object-cover border border-slate-200/60" alt="{{ $item->name }}" />
                  <span class="absolute -top-1.5 -right-1.5 grid h-4 w-4 place-items-center rounded-full bg-brand-600 text-white text-[9px] font-bold shadow-xs">{{ $item->qty }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1 pr-1">
                      <p class="text-xs font-semibold truncate text-slate-900">{{ $item->name }}</p>
                      <p class="text-[11px] text-slate-500">{{ $item->variant ?: $item->product->unit }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                      <span class="text-xs font-bold text-slate-900">{{ money($item->line_total) }}</span>
                      {{-- Cross Icon: Remove Item from Order --}}
                      <form method="POST" action="{{ route('cart.remove') }}" class="inline-flex">
                        @csrf
                        <input type="hidden" name="key" value="{{ $item->key }}">
                        <button type="submit" class="h-7 w-7 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" title="Remove {{ $item->name }}" aria-label="Remove {{ $item->name }}">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>
                  <div class="mt-2">
                    <form method="POST" action="{{ route('cart.update') }}" class="inline-flex w-max items-center rounded-lg border border-gray-200 bg-white shadow-2xs overflow-hidden h-7">
                      @csrf
                      <input type="hidden" name="key" value="{{ $item->key }}">
                      <button type="submit" name="qty" value="{{ max(0, $item->qty - 1) }}" class="flex-none w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition" aria-label="Decrease quantity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                      </button>
                      <div class="flex-none w-7 h-7 flex items-center justify-center border-x border-gray-200 text-xs font-semibold text-gray-800">{{ $item->qty }}</div>
                      <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="flex-none w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition" aria-label="Increase quantity">
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
      </div>

      {{-- Left Side: Checkout Form (7 Cols on desktop, order-2 on mobile) --}}
      <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" class="order-2 lg:order-1 lg:col-span-7">
        @csrf

        <div class="space-y-3.5 sm:space-y-4">
          {{-- Card 1: Contact (Compact & User-friendly) --}}
          <div class="rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-200/80 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                <h2 class="font-display text-sm sm:text-base font-extrabold text-slate-900">Contact</h2>
              </div>
              <span class="text-[11px] text-slate-400 font-medium">Quick checkout</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5">
              <div>
                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">Full name <span class="text-red-500">*</span></label>
                <input name="customer_name" value="{{ old('customer_name', $user?->name) }}" required placeholder="e.g. Marty Customer" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white" />
              </div>
              
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label for="customerPhone" class="block text-[11px] sm:text-xs font-semibold text-slate-700">Phone <span class="text-red-500">*</span></label>
                  <span id="phoneValidationStatus" class="text-[10px] sm:text-[11px] font-semibold text-slate-400">11 digits (017XXXXXXXX)</span>
                </div>
                <div class="relative">
                  <input type="tel" id="customerPhone" name="customer_phone" value="{{ old('customer_phone', $user?->phone) }}" required placeholder="017XXXXXXXX" maxlength="15" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white pr-8" />
                  <span id="phoneValidIcon" class="absolute right-2.5 top-2.5 text-xs hidden font-bold text-emerald-600">✓</span>
                </div>
                <p id="phoneErrorHint" class="hidden text-[10px] font-semibold text-red-600 mt-0.5">Please enter a valid 11-digit Bangladeshi mobile number.</p>
              </div>

              <div class="sm:col-span-2">
                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">Email <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="email" name="customer_email" value="{{ old('customer_email', $user?->email) }}" placeholder="you@example.com (for order updates & receipt)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white" />
              </div>
            </div>
          </div>

          {{-- Card 2: Delivery address (Compact & User-friendly) --}}
          <div class="rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-200/80 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                <h2 class="font-display text-sm sm:text-base font-extrabold text-slate-900">Delivery address</h2>
              </div>
              <span class="text-[11px] text-slate-400 font-medium">Doorstep parcel delivery</span>
            </div>

            <div class="space-y-2 sm:space-y-2.5">
              <div>
                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">Address <span class="text-red-500">*</span></label>
                <input name="shipping_address" value="{{ old('shipping_address', $user?->address) }}" required placeholder="House, road, area details" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white" />
              </div>

              <div class="grid grid-cols-2 gap-2 sm:gap-2.5">
                <div>
                  <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">City <span class="text-red-500">*</span></label>
                  <input name="city" value="{{ old('city', $user?->city ?? 'Dhaka') }}" required placeholder="e.g. Dhaka" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white" />
                </div>
                <div>
                  <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1">Postal code <span class="text-slate-400 font-normal">(optional)</span></label>
                  <input name="postal_code" value="{{ old('postal_code', $user?->postal_code) }}" placeholder="e.g. 1205" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-300 focus:border-brand-500 bg-white" />
                </div>
              </div>

              <div>
                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1.5">Delivery zone <span class="text-red-500">*</span></label>
                {{-- Hidden native select to preserve standard form submission & JS compatibility --}}
                <select name="shipping_zone" id="shippingZone" class="hidden">
                  <option value="inside_dhaka" data-fee="{{ $shipInside }}" @selected($shippingZone === 'inside_dhaka')>{{ shipping_zone_label('inside_dhaka') }} (+{{ money($shipInside) }})</option>
                  <option value="outside_dhaka" data-fee="{{ $shipOutside }}" @selected($shippingZone === 'outside_dhaka')>{{ shipping_zone_label('outside_dhaka') }} (+{{ money($shipOutside) }})</option>
                </select>

                {{-- Interactive 1-Tap Segmented Control --}}
                <div class="grid grid-cols-2 gap-2">
                  <button type="button" class="zone-pill flex items-center justify-between p-2.5 rounded-xl border transition-all text-left cursor-pointer select-none {{ $shippingZone === 'inside_dhaka' ? 'border-brand-600 bg-brand-50/70 text-brand-900 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}" data-zone="inside_dhaka">
                    <div class="min-w-0">
                      <span class="block text-xs font-extrabold truncate">Inside Dhaka</span>
                      <span class="block text-[11px] text-brand-700 font-semibold">+{{ money($shipInside) }}</span>
                    </div>
                    <span class="zone-check h-4 w-4 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] shrink-0 font-bold {{ $shippingZone === 'inside_dhaka' ? '' : 'hidden' }}">✓</span>
                  </button>
                  <button type="button" class="zone-pill flex items-center justify-between p-2.5 rounded-xl border transition-all text-left cursor-pointer select-none {{ $shippingZone === 'outside_dhaka' ? 'border-brand-600 bg-brand-50/70 text-brand-900 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}" data-zone="outside_dhaka">
                    <div class="min-w-0">
                      <span class="block text-xs font-extrabold truncate">Outside Dhaka</span>
                      <span class="block text-[11px] text-brand-700 font-semibold">+{{ money($shipOutside) }}</span>
                    </div>
                    <span class="zone-check h-4 w-4 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] shrink-0 font-bold {{ $shippingZone === 'outside_dhaka' ? '' : 'hidden' }}">✓</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          {{-- Card 3: Payment Method (Compact & User-friendly) --}}
          @php
            $method = old('payment_method', 'cod');
            $bkash = setting('bkash_number');
            $nagad = setting('nagad_number');
            $rocket = setting('rocket_number');
            $showCod = (string) setting('pay_cod_enabled', '1') === '1';
            $showBkash = (string) setting('pay_bkash_enabled', '1') === '1' && (bool) $bkash;
            $showNagad = (string) setting('pay_nagad_enabled', '1') === '1' && (bool) $nagad;
            $showRocket = (string) setting('pay_rocket_enabled', '1') === '1' && (bool) $rocket;
            $payNumbers = [
              'bkash' => $bkash,
              'nagad' => $nagad,
              'rocket' => $rocket,
            ];
          @endphp
          <div class="rounded-2xl bg-white p-3.5 sm:p-5 border border-slate-200/80 shadow-2xs space-y-2.5 sm:space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                <h2 class="font-display text-sm sm:text-base font-extrabold text-slate-900">Payment method</h2>
              </div>
              <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                100% Secure
              </span>
            </div>

            <div class="space-y-2">
              @if($showCod)
              {{-- Cash On Delivery Row --}}
              <label class="pay-opt relative flex items-center justify-between p-2.5 sm:p-3 rounded-xl border transition-all cursor-pointer select-none {{ $method==='cod' ? 'border-brand-600 bg-brand-50/70 text-brand-950 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                <div class="flex items-center gap-2.5 sm:gap-3">
                  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 text-base shrink-0 font-bold">💵</span>
                  <div>
                    <span class="text-xs sm:text-sm font-bold text-slate-900 block leading-tight">Cash On Delivery</span>
                    <span class="text-[10px] sm:text-[11px] text-slate-500 font-normal">Pay cash upon parcel delivery</span>
                  </div>
                </div>
                <input type="radio" name="payment_method" value="cod" @checked($method==='cod') class="pay-radio hidden" data-manual="0" data-pay-number="" />
                <span class="pay-check grid h-4 w-4 sm:h-5 sm:w-5 place-items-center rounded-full bg-brand-600 text-white text-[10px] sm:text-xs font-bold shrink-0 {{ $method==='cod' ? '' : 'hidden' }}">✓</span>
              </label>
              @endif

              @if($showBkash || $showNagad || $showRocket)
              {{-- Mobile Banking 3-Column Segmented Grid --}}
              <div class="grid grid-cols-3 gap-2">
                @if($showBkash)
                <label class="pay-opt relative flex flex-col items-center justify-center py-2 px-1.5 rounded-xl border transition-all text-center cursor-pointer select-none {{ $method==='bkash' ? 'border-brand-600 bg-brand-50/70 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                  <span class="px-2 py-0.5 rounded-md bg-[#E2136E] text-white font-black text-[10px] leading-tight mb-1 shadow-2xs">bKash</span>
                  <span class="text-[11px] font-bold text-slate-800 block leading-tight">bKash</span>
                  <input type="radio" name="payment_method" value="bkash" @checked($method==='bkash') class="pay-radio hidden" data-manual="1" data-pay-number="{{ $bkash }}" />
                  <span class="pay-check absolute top-1.5 right-1.5 grid h-3.5 w-3.5 place-items-center rounded-full bg-brand-600 text-white text-[9px] font-bold {{ $method==='bkash' ? '' : 'hidden' }}">✓</span>
                </label>
                @endif

                @if($showNagad)
                <label class="pay-opt relative flex flex-col items-center justify-center py-2 px-1.5 rounded-xl border transition-all text-center cursor-pointer select-none {{ $method==='nagad' ? 'border-brand-600 bg-brand-50/70 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                  <span class="px-2 py-0.5 rounded-md bg-[#F7941D] text-white font-black text-[10px] leading-tight mb-1 shadow-2xs">Nagad</span>
                  <span class="text-[11px] font-bold text-slate-800 block leading-tight">Nagad</span>
                  <input type="radio" name="payment_method" value="nagad" @checked($method==='nagad') class="pay-radio hidden" data-manual="1" data-pay-number="{{ $nagad }}" />
                  <span class="pay-check absolute top-1.5 right-1.5 grid h-3.5 w-3.5 place-items-center rounded-full bg-brand-600 text-white text-[9px] font-bold {{ $method==='nagad' ? '' : 'hidden' }}">✓</span>
                </label>
                @endif

                @if($showRocket)
                <label class="pay-opt relative flex flex-col items-center justify-center py-2 px-1.5 rounded-xl border transition-all text-center cursor-pointer select-none {{ $method==='rocket' ? 'border-brand-600 bg-brand-50/70 ring-1 ring-brand-600 font-bold shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                  <span class="px-2 py-0.5 rounded-md bg-[#8C3494] text-white font-black text-[10px] leading-tight mb-1 shadow-2xs">Rocket</span>
                  <span class="text-[11px] font-bold text-slate-800 block leading-tight">Rocket</span>
                  <input type="radio" name="payment_method" value="rocket" @checked($method==='rocket') class="pay-radio hidden" data-manual="1" data-pay-number="{{ $rocket }}" />
                  <span class="pay-check absolute top-1.5 right-1.5 grid h-3.5 w-3.5 place-items-center rounded-full bg-brand-600 text-white text-[9px] font-bold {{ $method==='rocket' ? '' : 'hidden' }}">✓</span>
                </label>
                @endif
              </div>
              @endif
            </div>

            {{-- Manual Payment Instructions and Fields --}}
            <div id="manualFields" class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 {{ $method==='cod' ? 'hidden' : '' }}">
              <div class="flex items-center justify-between gap-2 bg-white p-2 sm:p-2.5 rounded-lg border border-slate-200">
                <div class="min-w-0">
                  <span class="text-[11px] text-slate-500 block leading-tight">Send <b id="manualPayAmount" class="text-brand-700 font-extrabold">{{ money($totals['total']) }}</b> to:</span>
                  <span id="manualPayNumber" class="font-mono font-bold text-slate-900 text-xs sm:text-sm tracking-wider">{{ $payNumbers[$method] ?? "—" }}</span>
                </div>
                <button type="button" id="copyPayNumberBtn" class="flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-700 bg-brand-50 px-2.5 py-1 rounded-md border border-brand-200 transition cursor-pointer select-none shrink-0" title="Copy Number">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  <span id="copyBtnText">Copy</span>
                </button>
              </div>

              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-[10px] sm:text-[11px] font-semibold text-slate-700 mb-0.5">Sender mobile <span class="text-rose-500">*</span></label>
                  <input name="payment_sender_number" value="{{ old('payment_sender_number') }}" placeholder="017XXXXXXXX" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
                <div>
                  <label class="block text-[10px] sm:text-[11px] font-semibold text-slate-700 mb-0.5">TrxID <span class="text-rose-500">*</span></label>
                  <input name="payment_txn_id" value="{{ old('payment_txn_id') }}" placeholder="e.g. 9J82K3L4P" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-mono uppercase focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
              </div>
            </div>
          </div>

          {{-- Desktop Place Order Button on Left Side (Hidden on mobile) --}}
          <div class="hidden lg:block pt-2 space-y-2.5">
            <button type="submit" class="btn-shine w-full rounded-2xl bg-brand-600 text-white font-bold py-3.5 sm:py-4 hover:bg-brand-700 transition shadow-md cursor-pointer text-sm sm:text-base">
              Place Order · <span class="placeTotalText" id="placeTotal">{{ money($totals['total']) }}</span>
            </button>
            <p class="text-center text-[11px] sm:text-xs text-slate-400">By placing your order you agree to our <a href="{{ route('terms') }}" class="underline hover:text-brand-600">Terms</a> &amp; <a href="{{ route('privacy') }}" class="underline hover:text-brand-600">Privacy Policy</a>.</p>
          </div>
        </div>
      </form>

      {{-- Mobile-only BOTTOM: Subtotal, Delivery, Tax, Total Payable, ETA & Coupon (Appears at the bottom on mobile) --}}
      <div class="lg:hidden order-3 space-y-4">
        
        {{-- Card: Subtotal, Delivery, Tax, Total Payable & ETA --}}
        <div class="rounded-2xl bg-white p-4 border border-slate-200/80 shadow-2xs space-y-3">
          <dl class="space-y-2 text-xs sm:text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-800">{{ money($subtotal) }}</dd></div>
            @if($discount > 0)
              <div class="flex justify-between text-brand-600"><dt>Discount ({{ $couponCode }})</dt><dd class="font-semibold sumDiscount">−{{ money($discount) }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd class="font-semibold text-brand-600 sumShipping">{{ money($totals['shipping']) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Tax ({{ rtrim(rtrim(number_format($taxPercent, 2), '0'), '.') }}%)</dt><dd class="font-semibold text-slate-800 sumTax">{{ money($totals['tax']) }}</dd></div>
          </dl>
          <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs sm:text-sm font-bold text-slate-700">Total Payable</span>
            <span class="font-display text-xl font-black text-brand-700 sumTotal">{{ money($totals['total']) }}</span>
          </div>
          @if(trim((string) setting('delivery_eta_text', '')) !== '')
            <div class="mt-2 flex items-center gap-2 rounded-xl bg-brand-50 px-3.5 py-2.5 text-xs text-brand-800">
              <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg> 
              <span>{{ setting('delivery_eta_text') }}</span>
            </div>
          @endif
        </div>

        {{-- Card: Coupon Accordion (Screenshot Style) --}}
        <div class="coupon-card rounded-2xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
          <div class="p-4 sm:p-5">
            <button type="button" class="coupon-toggle-btn w-full flex items-center justify-between text-xs sm:text-sm font-bold text-slate-800 cursor-pointer select-none">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                <span>Have any coupon or gift voucher?</span>
              </div>
              <svg class="coupon-chevron w-4 h-4 text-brand-600 transition-transform duration-200 shrink-0 ml-2 {{ $couponCode ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div class="coupon-drawer pt-3.5 {{ $couponCode ? '' : 'hidden' }}">
              @if($couponCode)
                <div class="flex items-center justify-between gap-3 bg-brand-50 border border-brand-200 rounded-xl px-3.5 py-2.5 text-xs">
                  <div>
                    <p class="font-mono font-bold text-brand-700">{{ $couponCode }}</p>
                    <p class="text-[11px] text-brand-600">You save {{ money($discount) }}</p>
                  </div>
                  <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600 cursor-pointer">Remove</button>
                  </form>
                </div>
              @else
                <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="flex gap-2">
                  @csrf
                  <input name="code" value="{{ old('code') }}" placeholder="Promo code" required class="flex-1 rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-300 uppercase" />
                  <button type="submit" class="rounded-xl bg-brand-600 text-white text-xs font-bold px-4 py-2 hover:bg-brand-700 transition cursor-pointer shrink-0">Apply</button>
                </form>
                @error('coupon')<p class="text-[11px] text-red-600 font-semibold mt-1.5">⚠️ {{ $message }}</p>@enderror
              @endif
            </div>
          </div>
        </div>

        {{-- Mobile Terms & Policies Disclaimer --}}
        <div class="pt-1 pb-1 text-center">
          <p class="text-[11px] text-slate-400">By placing your order you agree to our <a href="{{ route('terms') }}" class="underline hover:text-brand-600">Terms</a> &amp; <a href="{{ route('privacy') }}" class="underline hover:text-brand-600">Privacy Policy</a>.</p>
        </div>

      </div>

      {{-- Desktop Right Side: Order Summary & Coupon (Hidden on mobile, 5 Cols sticky on desktop) --}}
      <aside class="hidden lg:block lg:order-2 lg:col-span-5 space-y-4 sm:space-y-5 lg:sticky lg:top-8">
        
        {{-- Card 1: Your order (Desktop) --}}
        <div class="rounded-2xl bg-white p-4 sm:p-6 border border-slate-200/80 shadow-2xs space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
              <h2 class="font-display text-base font-extrabold text-slate-900">Your order</h2>
              <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">({{ $items->sum('qty') }} {{ Str::plural('item', $items->sum('qty')) }})</span>
            </div>
          </div>

          <div class="space-y-4">
            <div class="space-y-3 sm:space-y-4">
              @foreach($items as $item)
                <div class="space-y-2.5 sm:space-y-3 rounded-2xl border border-slate-100 p-3 sm:p-4 bg-slate-50/40">
                  <div class="flex gap-2.5 sm:gap-3 items-start">
                    <div class="relative shrink-0">
                      <img src="{{ $item->image }}" loading="lazy" decoding="async" class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl object-cover border border-slate-200/60" alt="{{ $item->name }}" />
                      <span class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 grid h-4 w-4 sm:h-5 sm:w-5 place-items-center rounded-full bg-brand-600 text-white text-[9px] sm:text-[10px] font-bold shadow-xs">{{ $item->qty }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1 pr-1">
                          <p class="text-xs sm:text-sm font-semibold truncate text-slate-900">{{ $item->name }}</p>
                          <p class="text-[11px] sm:text-xs text-slate-500">{{ $item->variant ?: $item->product->unit }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                          <span class="text-xs sm:text-sm font-bold text-slate-900">{{ money($item->line_total) }}</span>
                          {{-- Cross Icon: Remove Item from Order --}}
                          <form method="POST" action="{{ route('cart.remove') }}" class="inline-flex">
                            @csrf
                            <input type="hidden" name="key" value="{{ $item->key }}">
                            <button type="submit" class="h-7 w-7 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" title="Remove {{ $item->name }}" aria-label="Remove {{ $item->name }}">
                              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                              </svg>
                            </button>
                          </form>
                        </div>
                      </div>
                      <div class="mt-2 sm:mt-3">
                        <form method="POST" action="{{ route('cart.update') }}" class="inline-flex w-max items-center rounded-lg border border-gray-200 bg-white shadow-2xs overflow-hidden h-7 sm:h-8">
                          @csrf
                          <input type="hidden" name="key" value="{{ $item->key }}">
                          <button type="submit" name="qty" value="{{ max(0, $item->qty - 1) }}" class="flex-none w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition" aria-label="Decrease quantity">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                            </svg>
                          </button>
                          <div class="flex-none w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center border-x border-gray-200 text-xs sm:text-sm font-semibold text-gray-800">{{ $item->qty }}</div>
                          <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="flex-none w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-600 hover:bg-orange-50 hover:text-accent-600 transition" aria-label="Increase quantity">
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

            <dl class="space-y-2 text-xs sm:text-sm border-t border-slate-100 pt-3.5">
              <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-800">{{ money($subtotal) }}</dd></div>
              @if($discount > 0)
                <div class="flex justify-between text-brand-600"><dt>Discount ({{ $couponCode }})</dt><dd class="font-semibold sumDiscount" id="sumDiscount">−{{ money($discount) }}</dd></div>
              @endif
              <div class="flex justify-between"><dt class="text-slate-500">Delivery</dt><dd class="font-semibold text-brand-600 sumShipping" id="sumShipping">{{ money($totals['shipping']) }}</dd></div>
              <div class="flex justify-between"><dt class="text-slate-500">Tax ({{ rtrim(rtrim(number_format($taxPercent, 2), '0'), '.') }}%)</dt><dd class="font-semibold text-slate-800 sumTax" id="sumTax">{{ money($totals['tax']) }}</dd></div>
            </dl>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
              <span class="text-xs sm:text-sm font-bold text-slate-700">Total Payable</span>
              <span class="font-display text-xl sm:text-2xl font-black text-brand-700 sumTotal" id="sumTotal">{{ money($totals['total']) }}</span>
            </div>
            @if(trim((string) setting('delivery_eta_text', '')) !== '')
              <div class="mt-2 flex items-center gap-2 rounded-xl bg-brand-50 px-3.5 py-2.5 text-xs text-brand-800">
                <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg> 
                <span>{{ setting('delivery_eta_text') }}</span>
              </div>
            @endif
          </div>
        </div>

        {{-- Card 2: Coupon Accordion (Desktop) --}}
        <div class="coupon-card rounded-2xl bg-white border border-slate-200/80 shadow-2xs overflow-hidden">
          <div class="p-4 sm:p-5">
            <button type="button" class="coupon-toggle-btn w-full flex items-center justify-between text-xs sm:text-sm font-bold text-slate-800 cursor-pointer select-none">
              <div class="flex items-center gap-2">
                <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
                <span>Have any coupon or gift voucher?</span>
              </div>
              <svg class="coupon-chevron w-4 h-4 text-brand-600 transition-transform duration-200 shrink-0 ml-2 {{ $couponCode ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div class="coupon-drawer pt-3.5 {{ $couponCode ? '' : 'hidden' }}">
              @if($couponCode)
                <div class="flex items-center justify-between gap-3 bg-brand-50 border border-brand-200 rounded-xl px-3.5 py-2.5 text-xs">
                  <div>
                    <p class="font-mono font-bold text-brand-700">{{ $couponCode }}</p>
                    <p class="text-[11px] text-brand-600">You save {{ money($discount) }}</p>
                  </div>
                  <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-red-600 cursor-pointer">Remove</button>
                  </form>
                </div>
              @else
                <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="flex gap-2">
                  @csrf
                  <input name="code" value="{{ old('code') }}" placeholder="Promo code" required class="flex-1 rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-300 uppercase" />
                  <button type="submit" class="rounded-xl bg-brand-600 text-white text-xs font-bold px-4 py-2 hover:bg-brand-700 transition cursor-pointer shrink-0">Apply</button>
                </form>
                @error('coupon')<p class="text-[11px] text-red-600 font-semibold mt-1.5">⚠️ {{ $message }}</p>@enderror
              @endif
            </div>
          </div>
        </div>

      </aside>
    </div>
  </section>

  {{-- Floating "Place Order" Button for Mobile View (No background container below, floating up) --}}
  <div id="stickyMobilePlaceBar" class="lg:hidden fixed bottom-5 sm:bottom-6 left-4 right-4 z-40 pointer-events-none" style="bottom: max(1.25rem, env(safe-area-inset-bottom, 1.25rem));">
    <div class="max-w-md mx-auto pointer-events-auto">
      <button type="button" id="stickyMobilePlaceBtn" class="btn-shine w-full flex items-center justify-center rounded-2xl bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-extrabold py-3.5 px-6 shadow-[0_10px_25px_rgba(234,88,12,0.38)] hover:shadow-[0_12px_30px_rgba(234,88,12,0.45)] transition-all duration-200 cursor-pointer text-sm sm:text-base tracking-wide border border-white/20">
        <span>Place Order · <span id="stickyMobilePlaceTotal" class="sumTotal">{{ money($totals['total']) }}</span></span>
      </button>
    </div>
  </div>
@endsection

@push('scripts')
<script>
(function () {
  var subtotal = {{ $subtotal }};
  var discount = {{ $discount }};
  var taxPct = {{ $taxPercent }};
  var zone = document.getElementById('shippingZone');
  var currencySymbol = @json(currency_symbol());
  var money = function (n) {
    return currencySymbol + ' ' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 0 });
  };

  function recalc() {
    var fee = parseFloat(zone.options[zone.selectedIndex].dataset.fee) || 0;
    var taxable = Math.max(0, subtotal - discount);
    var tax = Math.round(taxable * taxPct / 100);
    var total = taxable + fee + tax;

    document.querySelectorAll('.sumShipping').forEach(function(el) { el.textContent = money(fee); });
    document.querySelectorAll('.sumTax').forEach(function(el) { el.textContent = money(tax); });
    document.querySelectorAll('.sumTotal').forEach(function(el) { el.textContent = money(total); });
    document.querySelectorAll('.placeTotalText').forEach(function(el) { el.textContent = money(total); });

    var sumShipEl = document.getElementById('sumShipping');
    if (sumShipEl) sumShipEl.textContent = money(fee);
    var sumTaxEl = document.getElementById('sumTax');
    if (sumTaxEl) sumTaxEl.textContent = money(tax);
    var sumTotalEl = document.getElementById('sumTotal');
    if (sumTotalEl) sumTotalEl.textContent = money(total);
    var placeTotalEl = document.getElementById('placeTotal');
    if (placeTotalEl) placeTotalEl.textContent = money(total);
    var stickyTotalEl = document.getElementById('stickyMobilePlaceTotal');
    if (stickyTotalEl) stickyTotalEl.textContent = money(total);

    var manualAmt = document.getElementById('manualPayAmount');
    if (manualAmt) manualAmt.textContent = money(total);
  }

  if (zone) zone.addEventListener('change', recalc);

  // Delivery Zone Pill Switcher (1-tap selection)
  document.querySelectorAll('.zone-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      var targetZone = pill.dataset.zone;
      if (!zone || !targetZone) return;

      zone.value = targetZone;
      zone.dispatchEvent(new Event('change'));

      document.querySelectorAll('.zone-pill').forEach(function (p) {
        var isTarget = p.dataset.zone === targetZone;
        if (isTarget) {
          p.classList.add('border-brand-600', 'bg-brand-50/70', 'text-brand-900', 'ring-1', 'ring-brand-600', 'font-bold', 'shadow-2xs');
          p.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
        } else {
          p.classList.remove('border-brand-600', 'bg-brand-50/70', 'text-brand-900', 'ring-1', 'ring-brand-600', 'font-bold', 'shadow-2xs');
          p.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
        }
        var chk = p.querySelector('.zone-check');
        if (chk) chk.classList.toggle('hidden', !isTarget);
      });
    });
  });

  // Payment option selection
  document.querySelectorAll('.pay-opt').forEach(function (opt) {
    opt.addEventListener('click', function () {
      var r = opt.querySelector('.pay-radio');
      if (!r) return;
      r.checked = true;

      document.querySelectorAll('.pay-opt').forEach(function (o) {
        o.classList.remove('border-brand-600', 'bg-brand-50/70', 'ring-1', 'ring-brand-600', 'font-bold', 'shadow-2xs', 'border-2', 'border-brand-500');
        o.classList.add('border-slate-200', 'bg-white');
        var chk = o.querySelector('.pay-check');
        if (chk) chk.classList.add('hidden');
      });

      opt.classList.add('border-brand-600', 'bg-brand-50/70', 'ring-1', 'ring-brand-600', 'font-bold', 'shadow-2xs');
      opt.classList.remove('border-slate-200', 'bg-white');
      var myChk = opt.querySelector('.pay-check');
      if (myChk) myChk.classList.remove('hidden');

      var manualFields = document.getElementById('manualFields');
      if (manualFields) {
        manualFields.classList.toggle('hidden', r.dataset.manual === '0');
      }
      var numEl = document.getElementById('manualPayNumber');
      if (numEl) {
        numEl.textContent = r.dataset.payNumber || "—";
      }
    });
  });

  // Copy Payment Number to Clipboard
  var copyBtn = document.getElementById('copyPayNumberBtn');
  var manualNumEl = document.getElementById('manualPayNumber');
  var copyBtnText = document.getElementById('copyBtnText');
  if (copyBtn && manualNumEl) {
    copyBtn.addEventListener('click', function () {
      var text = manualNumEl.textContent.trim();
      if (text && text !== '—') {
        navigator.clipboard.writeText(text).then(function () {
          if (copyBtnText) copyBtnText.textContent = 'Copied!';
          if (window.showToast) window.showToast('Payment number copied to clipboard!', 'success');
          setTimeout(function () {
            if (copyBtnText) copyBtnText.textContent = 'Copy';
          }, 2000);
        });
      }
    });
  }

  // Coupon Accordion Toggles (Works for both mobile and desktop cards)
  document.querySelectorAll('.coupon-toggle-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = btn.closest('.coupon-card') || btn.parentElement;
      var drawer = card ? card.querySelector('.coupon-drawer') : null;
      var chevron = btn.querySelector('.coupon-chevron');
      if (drawer) {
        var isClosed = drawer.classList.contains('hidden');
        if (isClosed) {
          drawer.classList.remove('hidden');
          if (chevron) chevron.classList.add('rotate-180');
        } else {
          drawer.classList.add('hidden');
          if (chevron) chevron.classList.remove('rotate-180');
        }
      }
    });
  });

  // Mobile Order Summary Collapse Toggle
  var summaryToggle = document.getElementById('mobileOrderSummaryToggle');
  var summaryBody = document.getElementById('mobileOrderSummaryBody');
  var summaryText = document.getElementById('mobileOrderSummaryText');
  var summaryChevron = document.getElementById('mobileOrderSummaryChevron');
  if (summaryToggle && summaryBody) {
    summaryToggle.addEventListener('click', function () {
      var isHidden = summaryBody.classList.contains('hidden');
      if (isHidden) {
        summaryBody.classList.remove('hidden');
        if (summaryText) summaryText.textContent = 'Hide details';
        if (summaryChevron) summaryChevron.classList.remove('rotate-180');
      } else {
        summaryBody.classList.add('hidden');
        if (summaryText) summaryText.textContent = 'Show details';
        if (summaryChevron) summaryChevron.classList.add('rotate-180');
      }
    });
  }

  // Mobile Fixed Bottom "Place Order" Button Trigger
  var stickyPlaceBtn = document.getElementById('stickyMobilePlaceBtn');
  var checkoutForm = document.getElementById('checkoutForm');
  if (stickyPlaceBtn && checkoutForm) {
    stickyPlaceBtn.addEventListener('click', function () {
      if (checkoutForm.reportValidity()) {
        if (typeof checkoutForm.requestSubmit === 'function') {
          checkoutForm.requestSubmit();
        } else {
          checkoutForm.submit();
        }
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
  })();

  // Real-Time Bangladeshi Phone Validation & Submit Guard
  (function () {
    var checkoutForm = document.getElementById('checkoutForm');
    var phoneInput = document.getElementById('customerPhone');
    var phoneStatus = document.getElementById('phoneValidationStatus');
    var phoneIcon = document.getElementById('phoneValidIcon');
    var phoneHint = document.getElementById('phoneErrorHint');

    function isBdPhone(val) {
      if (!val) return false;
      var clean = val.replace(/[\s\-\(\)]/g, '');
      return /^(?:\+?88|0088)?01[3-9]\d{8}$/.test(clean);
    }

    function validatePhone() {
      if (!phoneInput) return true;
      var val = phoneInput.value.trim();
      if (!val) {
        phoneInput.classList.remove('border-emerald-500', 'border-red-400');
        if (phoneIcon) phoneIcon.classList.add('hidden');
        if (phoneHint) phoneHint.classList.add('hidden');
        if (phoneStatus) {
          phoneStatus.textContent = '11 digits (e.g. 017XXXXXXXX)';
          phoneStatus.className = 'text-xs font-semibold text-slate-400';
        }
        return false;
      }

      if (isBdPhone(val)) {
        phoneInput.classList.remove('border-red-400');
        phoneInput.classList.add('border-emerald-500');
        if (phoneIcon) phoneIcon.classList.remove('hidden');
        if (phoneHint) phoneHint.classList.add('hidden');
        if (phoneStatus) {
          phoneStatus.textContent = '✓ Valid BD Mobile';
          phoneStatus.className = 'text-xs font-bold text-emerald-600';
        }
        return true;
      } else {
        phoneInput.classList.remove('border-emerald-500');
        phoneInput.classList.add('border-red-400');
        if (phoneIcon) phoneIcon.classList.add('hidden');
        if (phoneHint) phoneHint.classList.remove('hidden');
        if (phoneStatus) {
          phoneStatus.textContent = '⚠️ Invalid Number';
          phoneStatus.className = 'text-xs font-bold text-red-600';
        }
        return false;
      }
    }

    if (phoneInput) {
      phoneInput.addEventListener('input', validatePhone);
      phoneInput.addEventListener('blur', validatePhone);
      if (phoneInput.value) validatePhone();
    }

    if (checkoutForm) {
      checkoutForm.addEventListener('submit', function (e) {
        if (!validatePhone()) {
          e.preventDefault();
          phoneInput.focus();
          if (window.showToast) {
            window.showToast('Please enter a valid 11-digit Bangladeshi mobile number.', 'error');
          } else {
            alert('Please enter a valid 11-digit Bangladeshi mobile number.');
          }
        }
      });
    }
  })();
})();
</script>
@endpush
