@extends('layouts.storefront')

@php
  $title = 'Track Order · ' . site_name();
  $orderNumberInput = old('order_number', request('order_number', request('order_id', request('order', ''))));
  $phoneInput = old('phone', request('phone', ''));

  $waPhone = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: setting('contact_phone') ?: ''));
  if (str_starts_with($waPhone, '0')) {
      $waPhone = '88' . $waPhone;
  } elseif (!str_starts_with($waPhone, '880') && strlen($waPhone) === 10) {
      $waPhone = '880' . $waPhone;
  }
  $orderWaMsg = isset($order) && $order 
      ? 'Hello ' . site_name() . ', I want to inquire about my Order #' . $order->order_number 
      : 'Hello ' . site_name() . ', I need help tracking my order.';
  $orderWaUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($orderWaMsg) : null;
@endphp

@section('content')
<main class="min-h-[75vh] bg-stone-50/60 pb-16">

  {{-- Breadcrumb Bar --}}
  <div class="bg-white border-b border-stone-200/80">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
      <nav class="flex items-center gap-2 text-xs font-semibold text-stone-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-brand-600 transition flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          <span>Home</span>
        </a>
        <span class="text-stone-300">/</span>
        <a href="{{ route('track') }}" class="{{ isset($order) && $order ? 'hover:text-brand-600 transition' : 'text-brand-600 font-bold' }}">Track Order</a>
        @if(isset($order) && $order)
          <span class="text-stone-300">/</span>
          <span class="text-brand-600 font-bold font-mono">#{{ $order->order_number }}</span>
        @endif
      </nav>
    </div>
  </div>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">

    {{-- Hero Banner Section --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-stone-900 via-stone-850 to-stone-950 text-white shadow-2xl border border-stone-800/90 p-6 sm:p-10 mb-8 sm:mb-10">
      {{-- Ambient radial background glows --}}
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 max-w-3xl">
        {{-- Live Status Pill --}}
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-800/90 border border-stone-700/80 text-emerald-400 text-[11px] sm:text-xs font-bold tracking-wide uppercase mb-3 sm:mb-4 shadow-sm">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span>Live Shipment Tracker</span>
        </div>

        <h1 class="text-2xl sm:text-4xl font-extrabold font-display tracking-tight text-white mb-2 sm:mb-3">
          Track Your Order Status
        </h1>
        <p class="text-stone-300 text-xs sm:text-base font-medium leading-relaxed max-w-2xl">
          Enter your <span class="text-white font-bold">Order Number</span> and the <span class="text-white font-bold">Phone Number</span> used at checkout to see real-time dispatch, courier movement, and estimated delivery.
        </p>

        {{-- Quick Feature Highlights --}}
        <div class="flex flex-wrap items-center gap-3 sm:gap-6 mt-5 pt-5 border-t border-stone-800/80 text-xs text-stone-300">
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Real-time Status</span>
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Live Courier Links</span>
          </div>
          <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span>Instant WhatsApp Support</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Interactive Tracking Input Card --}}
    <div class="rounded-3xl bg-white border border-stone-200/90 shadow-xl p-5 sm:p-8 mb-8 sm:mb-12">
      <div class="mb-4">
        <h2 class="text-base sm:text-lg font-bold font-display text-stone-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <span>Find your shipment</span>
        </h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Please provide both details below to authenticate and view your order progress.</p>
      </div>

      @if($errors->any())
        <div class="mb-5 rounded-2xl bg-rose-50 border border-rose-200/90 p-4 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
          <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <div class="flex-1">
            <p class="font-bold text-rose-900">Verification / Search Notice</p>
            <p class="mt-0.5 text-rose-700 leading-relaxed">{{ $errors->first() }}</p>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('track.find') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-end">
        @csrf

        {{-- Order Number Input --}}
        <div class="sm:col-span-5">
          <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">
            Order Number / ID <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
              <span class="font-mono text-sm font-bold">#</span>
            </div>
            <input 
              type="text" 
              name="order_number" 
              value="{{ $orderNumberInput }}" 
              placeholder="e.g. ORD-260912-XXXX" 
              required 
              autocomplete="off"
              class="w-full rounded-2xl border border-stone-200/90 bg-stone-50/50 pl-9 pr-4 py-3 text-xs sm:text-sm font-medium text-stone-900 placeholder:text-stone-400 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all font-mono"
            />
          </div>
        </div>

        {{-- Phone Number Input --}}
        <div class="sm:col-span-4">
          <label class="block text-xs sm:text-sm font-bold text-stone-700 mb-1.5">
            Customer Phone <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <input 
              type="tel" 
              name="phone" 
              value="{{ $phoneInput }}" 
              placeholder="e.g. 017XXXXXXXX" 
              required 
              autocomplete="tel"
              class="w-full rounded-2xl border border-stone-200/90 bg-stone-50/50 pl-10 pr-4 py-3 text-xs sm:text-sm font-medium text-stone-900 placeholder:text-stone-400 focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition-all font-mono"
            />
          </div>
        </div>

        {{-- Track Button --}}
        <div class="sm:col-span-3">
          <button 
            type="submit" 
            class="btn-shine w-full flex items-center justify-center gap-2 rounded-2xl bg-brand-600 hover:bg-brand-700 active:scale-[0.98] text-white font-extrabold py-3 px-6 shadow-md shadow-brand-600/25 transition cursor-pointer text-xs sm:text-sm tracking-wide h-[46px]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span>Track Order</span>
          </button>
        </div>
      </form>

      <div class="mt-3.5 flex items-center justify-between flex-wrap gap-2 text-[11px] sm:text-xs text-stone-500">
        <span class="inline-flex items-center gap-1.5">
          <span class="text-amber-500">💡</span>
          <span>Your Order ID was sent to your phone via SMS right after placing the order.</span>
        </span>
        @if($orderWaUrl)
          <a href="{{ $orderWaUrl }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
            <span>Need help finding order? WhatsApp us</span> &rarr;
          </a>
        @endif
      </div>
    </div>

    {{-- Order Details Section (When order is found) --}}
    @isset($order)
      @if($order)
        @php
          $isCancelled = $order->status === 'cancelled';
          $steps = [
            'pending'    => ['title' => 'Placed', 'desc' => 'Order received in system', 'icon' => 'cart'],
            'confirmed'  => ['title' => 'Confirmed', 'desc' => 'Verified by team', 'icon' => 'check'],
            'processing' => ['title' => 'Processing', 'desc' => 'Packed & quality checked', 'icon' => 'box'],
            'shipped'    => ['title' => 'Dispatched', 'desc' => 'Handed over to courier', 'icon' => 'truck'],
            'delivered'  => ['title' => 'Delivered', 'desc' => 'Successfully received', 'icon' => 'home'],
          ];
          $statusKeys = array_keys($steps);
          $orderIndex = array_search($order->status, $statusKeys, true);
          if ($orderIndex === false) {
              $orderIndex = 0;
          }
          $progressPercent = $isCancelled ? 0 : round(($orderIndex / (count($steps) - 1)) * 100);
        @endphp

        <div class="space-y-6 sm:space-y-8 animate-fade-in">

          {{-- Top Summary Bar Card --}}
          <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft p-5 sm:p-7 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Order ID</span>
                <span class="text-lg sm:text-2xl font-black font-mono text-stone-900 tracking-tight" id="activeOrderNumber">{{ $order->order_number }}</span>
                <button type="button" id="copyOrderBtn" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-stone-600 hover:text-brand-600 bg-stone-100 hover:bg-brand-50 transition cursor-pointer" title="Copy Order Number">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  <span id="copyOrderText">Copy</span>
                </button>
              </div>
              <p class="text-xs sm:text-sm text-stone-500 mt-1 flex items-center gap-2 flex-wrap">
                <span>Placed on <b class="text-stone-700 font-semibold">{{ $order->created_at->format('d M, Y') }}</b> at {{ $order->created_at->format('h:i A') }}</span>
                <span class="text-stone-300">&bull;</span>
                <span>{{ $order->items->sum('quantity') }} {{ Str::plural('item', $order->items->sum('quantity')) }}</span>
                <span class="text-stone-300">&bull;</span>
                <span class="font-extrabold text-brand-600">{{ money($order->total) }}</span>
              </p>
            </div>

            {{-- Badges --}}
            <div class="flex items-center gap-2 flex-wrap">
              {{-- Fulfilment Badge --}}
              @php
                $statusColors = [
                  'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
                  'confirmed'  => 'bg-sky-50 text-sky-700 border-sky-200',
                  'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                  'shipped'    => 'bg-blue-50 text-blue-700 border-blue-200',
                  'delivered'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                  'cancelled'  => 'bg-rose-50 text-rose-700 border-rose-200',
                ];
                $badgeStyle = $statusColors[$order->status] ?? 'bg-stone-100 text-stone-700 border-stone-200';
              @endphp
              <span class="px-3.5 py-1.5 rounded-full border text-xs font-black uppercase tracking-wider {{ $badgeStyle }} shadow-2xs">
                Status: {{ ucfirst($order->status) }}
              </span>

              {{-- Payment Status Badge --}}
              <span class="px-3 py-1.5 rounded-full border text-xs font-bold {{ $order->payment_status === 'verified' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                Payment: {{ ucfirst($order->payment_status) }}
              </span>

              {{-- Payment Method Pill --}}
              <span class="px-3 py-1.5 rounded-full bg-stone-100 text-stone-700 border border-stone-200 text-xs font-semibold">
                {{ $order->paymentMethodLabel() }}
              </span>
            </div>
          </div>

          {{-- Live Courier Tracking Box (If Dispatched) --}}
          @if($order->isDispatchedToCourier())
            <div class="rounded-3xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/90 shadow-soft p-5 sm:p-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100/80 px-2.5 py-0.5 rounded-full border border-emerald-200">Courier Partner</span>
                    <span class="text-base font-extrabold text-stone-900">{{ $order->courierLabel() }}</span>
                  </div>
                  <p class="text-xs sm:text-sm text-stone-600 mt-1">
                    Consignment Tracking Code: 
                    <span class="font-mono font-black text-stone-900 bg-white px-2 py-0.5 rounded-md border border-emerald-300">#{{ $order->courier_tracking_code }}</span>
                    @if($order->courier_sent_at)
                      <span class="text-stone-400 ml-1.5">(Dispatched {{ $order->courier_sent_at->format('d M, Y') }})</span>
                    @endif
                  </p>
                </div>
              </div>

              @if($order->courierTrackingUrl())
                <a 
                  href="{{ $order->courierTrackingUrl() }}" 
                  target="_blank" 
                  rel="noopener noreferrer" 
                  class="btn-shine inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold py-3 px-5 text-xs sm:text-sm shadow-md shadow-emerald-700/20 transition shrink-0"
                >
                  <span>Track on {{ $order->courierLabel() }}</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
              @endif
            </div>
          @endif

          {{-- Interactive Stepper / Visual Progress Timeline --}}
          <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft p-5 sm:p-8">
            <h3 class="text-sm sm:text-base font-extrabold font-display text-stone-900 mb-6 flex items-center justify-between">
              <span>Delivery Journey</span>
              @if(!$isCancelled)
                <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-1 rounded-full border border-brand-100">
                  {{ $progressPercent }}% Complete
                </span>
              @endif
            </h3>

            @if($isCancelled)
              {{-- Cancellation Banner --}}
              <div class="rounded-2xl bg-rose-50 border border-rose-200 p-5 sm:p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h4 class="text-base font-extrabold text-rose-900">This Order Has Been Cancelled</h4>
                <p class="text-xs sm:text-sm text-rose-700 max-w-md mx-auto mt-1 leading-relaxed">
                  If this was done in error or you have any questions regarding your refund or status, please reach out to our customer support.
                </p>
                @if($orderWaUrl)
                  <a href="{{ $orderWaUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition shadow-sm">
                    <span>Chat with Support on WhatsApp</span> &rarr;
                  </a>
                @endif
              </div>
            @else
              {{-- 5-Stage Modern Stepper --}}
              <div class="relative">
                {{-- Desktop Connecting Bar --}}
                <div class="hidden md:block absolute top-6 left-12 right-12 h-1 bg-stone-100 rounded-full z-0">
                  <div class="h-full bg-gradient-to-r from-brand-600 to-emerald-500 rounded-full transition-all duration-700 ease-out" style="width: {{ $progressPercent }}%;"></div>
                </div>

                {{-- Steps Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-2 relative z-10">
                  @foreach($steps as $stepKey => $stepData)
                    @php
                      $stepIdx = $loop->index;
                      $isDone = $stepIdx < $orderIndex;
                      $isCurrent = $stepIdx === $orderIndex;
                      $isPending = $stepIdx > $orderIndex;
                    @endphp

                    <div class="flex md:flex-col items-center md:text-center gap-3.5 md:gap-2.5 p-2 rounded-2xl md:p-0 {{ $isCurrent ? 'bg-brand-50/50 md:bg-transparent border border-brand-200 md:border-0' : '' }}">
                      {{-- Step Milestone Circle --}}
                      <div class="relative flex items-center justify-center shrink-0">
                        @if($isDone)
                          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/25 transition-transform">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                          </div>
                        @elseif($isCurrent)
                          <div class="relative w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-lg shadow-brand-600/30 scale-105 ring-4 ring-brand-100">
                            {{-- Pulsing Ring --}}
                            <span class="animate-ping absolute -inset-0.5 rounded-2xl bg-brand-400 opacity-40 pointer-events-none"></span>

                            @if($stepData['icon'] === 'cart')
                              <svg class="w-5 h-5 sm:w-6 sm:h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            @elseif($stepData['icon'] === 'check')
                              <svg class="w-5 h-5 sm:w-6 sm:h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($stepData['icon'] === 'box')
                              <svg class="w-5 h-5 sm:w-6 sm:h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @elseif($stepData['icon'] === 'truck')
                              <svg class="w-5 h-5 sm:w-6 sm:h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            @else
                              <svg class="w-5 h-5 sm:w-6 sm:h-6 relative z-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            @endif
                          </div>
                        @else
                          <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-stone-100 text-stone-400 flex items-center justify-center border border-stone-200">
                            <span class="text-xs font-black">{{ $loop->iteration }}</span>
                          </div>
                        @endif
                      </div>

                      {{-- Label & Description --}}
                      <div class="flex-1 md:flex-initial">
                        <p class="text-xs sm:text-sm font-extrabold {{ $isCurrent ? 'text-brand-600' : ($isDone ? 'text-stone-900' : 'text-stone-400') }}">
                          {{ $stepData['title'] }}
                        </p>
                        <p class="text-[11px] sm:text-xs text-stone-500 font-medium leading-tight mt-0.5">
                          {{ $stepData['desc'] }}
                        </p>
                        @if($isCurrent)
                          <span class="inline-block mt-1 text-[10px] font-extrabold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">
                            Current Stage
                          </span>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>

          {{-- Two-Column Order Breakdown --}}
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            {{-- LEFT COLUMN: Ordered Items & Pricing Breakdown (7 cols) --}}
            <div class="lg:col-span-7 space-y-6">

              {{-- Ordered Items Card --}}
              <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-stone-100 flex items-center justify-between">
                  <h3 class="font-extrabold font-display text-sm sm:text-base text-stone-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Ordered Items</span>
                  </h3>
                  <span class="text-xs font-bold text-stone-500 bg-stone-100 px-2.5 py-0.5 rounded-full">
                    {{ $order->items->count() }} {{ Str::plural('product', $order->items->count()) }}
                  </span>
                </div>

                <div class="divide-y divide-stone-100">
                  @foreach($order->items as $item)
                    <div class="p-4 sm:p-5 flex items-center gap-3.5 sm:gap-4 hover:bg-stone-50/50 transition">
                      <img 
                        src="{{ $item->imageUrl() }}" 
                        alt="{{ $item->product_name }}" 
                        class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl object-cover bg-stone-100 border border-stone-200/80 shrink-0" 
                        loading="lazy" 
                        decoding="async" 
                      />

                      <div class="flex-1 min-w-0">
                        @if($item->product && $item->product->slug)
                          <a href="{{ route('product.show', $item->product->slug) }}" class="font-bold text-xs sm:text-sm text-stone-900 hover:text-brand-600 transition line-clamp-2">
                            {{ $item->product_name }}
                          </a>
                        @else
                          <p class="font-bold text-xs sm:text-sm text-stone-900 line-clamp-2">
                            {{ $item->product_name }}
                          </p>
                        @endif

                        @if($item->variant)
                          <div class="mt-1">
                            <span class="inline-flex items-center text-[10px] sm:text-xs font-bold bg-stone-100 text-stone-600 px-2 py-0.5 rounded-md border border-stone-200/60">
                              {{ $item->variant }}
                            </span>
                          </div>
                        @endif

                        <p class="text-[11px] sm:text-xs text-stone-500 font-medium mt-1">
                          {{ money($item->unit_price) }} × {{ $item->quantity }}
                        </p>
                      </div>

                      <div class="text-right shrink-0">
                        <span class="text-xs sm:text-sm font-black text-stone-900">
                          {{ money($item->line_total) }}
                        </span>
                      </div>
                    </div>
                  @endforeach
                </div>

                {{-- Financial Summary Breakdown --}}
                <div class="p-5 sm:p-6 bg-stone-50/60 border-t border-stone-100 text-xs sm:text-sm space-y-2.5">
                  <div class="flex items-center justify-between text-stone-600">
                    <span>Items Subtotal</span>
                    <span class="font-semibold text-stone-800">{{ money($order->subtotal) }}</span>
                  </div>

                  @if($order->discount_amount > 0)
                    <div class="flex items-center justify-between text-emerald-700 font-medium">
                      <span class="flex items-center gap-1.5">
                        <span>Coupon Discount</span>
                        @if($order->coupon_code)
                          <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded border border-emerald-200 font-bold">
                            {{ $order->coupon_code }}
                          </span>
                        @endif
                      </span>
                      <span class="font-bold">−{{ money($order->discount_amount) }}</span>
                    </div>
                  @endif

                  <div class="flex items-center justify-between text-stone-600">
                    <span class="flex items-center gap-1">
                      <span>Delivery Charge</span>
                      @if($order->shipping_zone)
                        <span class="text-[10px] text-stone-400 font-normal">({{ shipping_zone_label($order->shipping_zone) }})</span>
                      @endif
                    </span>
                    <span class="font-semibold text-stone-800">{{ money($order->shipping_charge) }}</span>
                  </div>

                  @if($order->tax > 0)
                    <div class="flex items-center justify-between text-stone-600">
                      <span>Govt. Tax</span>
                      <span class="font-semibold text-stone-800">{{ money($order->tax) }}</span>
                    </div>
                  @endif

                  <div class="pt-3 border-t border-stone-200 flex items-center justify-between text-sm sm:text-base">
                    <span class="font-extrabold text-stone-900">Grand Total</span>
                    <span class="font-black font-display text-brand-600 text-base sm:text-xl">
                      {{ money($order->total) }}
                    </span>
                  </div>
                </div>
              </div>

            </div>

            {{-- RIGHT COLUMN: Delivery Info, Payment & Support (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">

              {{-- Delivery Address Card --}}
              <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft p-5 sm:p-6">
                <h3 class="font-extrabold font-display text-xs sm:text-sm text-stone-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                  <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span>Delivery Address</span>
                </h3>

                <div class="space-y-1.5 text-xs sm:text-sm">
                  <p class="font-black text-stone-900 text-sm sm:text-base">{{ $order->customer_name }}</p>
                  
                  <div class="flex items-center gap-2 text-stone-600 pt-0.5">
                    <svg class="w-3.5 h-3.5 text-stone-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="font-mono font-bold text-stone-800">{{ $order->customer_phone }}</span>
                  </div>

                  <p class="text-stone-600 leading-relaxed pt-1">
                    {{ $order->shipping_address }}
                    @if($order->city)
                      <br><span class="text-stone-500">{{ $order->city }} {{ $order->postal_code }}</span>
                    @endif
                  </p>

                  <div class="pt-2">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-stone-700 bg-stone-100 px-2.5 py-1 rounded-lg border border-stone-200">
                      <span>🚚 Zone:</span>
                      <span>{{ shipping_zone_label($order->shipping_zone) }}</span>
                    </span>
                  </div>
                </div>
              </div>

              {{-- Payment Details Card --}}
              <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft p-5 sm:p-6">
                <h3 class="font-extrabold font-display text-xs sm:text-sm text-stone-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                  <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                  <span>Payment Information</span>
                </h3>

                <div class="space-y-2 text-xs sm:text-sm">
                  <div class="flex items-center justify-between">
                    <span class="text-stone-500">Method:</span>
                    <span class="font-extrabold text-stone-900">{{ $order->paymentMethodLabel() }}</span>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-stone-500">Payment Status:</span>
                    <span class="font-bold {{ $order->payment_status === 'verified' ? 'text-emerald-700' : 'text-amber-700' }}">
                      {{ ucfirst($order->payment_status) }}
                    </span>
                  </div>

                  @if($order->isMobileBanking())
                    <div class="pt-2 mt-2 border-t border-stone-100 space-y-1.5 text-xs">
                      @if($order->payment_sender_number)
                        <div class="flex items-center justify-between">
                          <span class="text-stone-500">Sender Number:</span>
                          <span class="font-mono font-bold text-stone-800">{{ $order->payment_sender_number }}</span>
                        </div>
                      @endif

                      @if($order->payment_txn_id)
                        <div class="flex items-center justify-between">
                          <span class="text-stone-500">Transaction ID:</span>
                          <span class="font-mono font-black text-stone-900 bg-stone-100 px-2 py-0.5 rounded border border-stone-200">
                            {{ $order->payment_txn_id }}
                          </span>
                        </div>
                      @endif
                    </div>
                  @endif
                </div>
              </div>

              {{-- Live Assistance Card --}}
              <div class="rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-50/50 border border-emerald-200/90 p-5 sm:p-6">
                <h3 class="font-extrabold font-display text-sm text-stone-900 mb-1.5 flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.585 1.961.947 2.796.947 3.179 0 5.766-2.587 5.767-5.766.001-3.181-2.585-5.767-5.767-5.767zm3.391 8.248c-.146.415-.758.78-1.053.829-.283.048-.648.077-1.04-.049-.24-.078-.553-.191-.951-.362-1.684-.726-2.775-2.457-2.859-2.569-.084-.112-.686-.913-.686-1.741 0-.829.434-1.237.589-1.408.155-.171.339-.214.452-.214.113 0 .226.002.325.007.104.005.244-.04.38.29.146.353.498 1.214.542 1.303.044.089.073.193.014.309-.059.117-.089.19-.176.293-.087.103-.183.23-.262.309-.088.088-.18.184-.077.361.103.177.458.756.983 1.223.676.602 1.246.788 1.423.876.177.088.281.077.386-.044.105-.121.452-.527.573-.707.121-.18.242-.151.407-.089.165.062 1.047.494 1.227.584.18.09.3.134.344.209.044.075.044.436-.102.851z"/></svg>
                  <span>Need Help with Delivery?</span>
                </h3>
                <p class="text-xs text-stone-600 leading-relaxed mb-4">
                  Have questions about your parcel location, delivery time, or need to update your address? Our support team is ready on WhatsApp.
                </p>

                @if($orderWaUrl)
                  <a 
                    href="{{ $orderWaUrl }}" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold py-3 px-4 text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition cursor-pointer"
                  >
                    <span>Inquire on WhatsApp</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                  </a>
                @endif
              </div>

            </div>

          </div>

          {{-- Bottom Actions --}}
          <div class="pt-6 border-t border-stone-200 flex items-center justify-between flex-wrap gap-4">
            <a href="{{ route('track') }}" class="inline-flex items-center gap-2 text-xs sm:text-sm font-extrabold text-stone-600 hover:text-brand-600 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              <span>Track Another Order</span>
            </a>

            <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white text-xs sm:text-sm font-extrabold px-6 py-3 transition shadow-sm">
              <span>Continue Shopping</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
          </div>

        </div>

      @endif
    @else

      {{-- DEFAULT STATE: Helpful Features & Tracking Tips (When no order searched yet) --}}
      <div class="space-y-8 sm:space-y-12">

        {{-- 3 Features Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
          <div class="rounded-3xl bg-white border border-stone-200/90 p-5 sm:p-6 shadow-soft hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="font-extrabold font-display text-sm sm:text-base text-stone-900 mb-1.5">Swift Processing</h3>
            <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
              Every confirmed order is packed and dispatched within 24 hours with trusted delivery couriers.
            </p>
          </div>

          <div class="rounded-3xl bg-white border border-stone-200/90 p-5 sm:p-6 shadow-soft hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="font-extrabold font-display text-sm sm:text-base text-stone-900 mb-1.5">Check on Delivery</h3>
            <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
              Inspect your parcel in front of the delivery rider before complete payment for 100% peace of mind.
            </p>
          </div>

          <div class="rounded-3xl bg-white border border-stone-200/90 p-5 sm:p-6 shadow-soft hover:shadow-md transition">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h3 class="font-extrabold font-display text-sm sm:text-base text-stone-900 mb-1.5">SMS & WhatsApp Alerts</h3>
            <p class="text-xs sm:text-sm text-stone-500 leading-relaxed">
              Automated notifications with your tracking ID keep you updated at every step of your shipment.
            </p>
          </div>
        </div>

        {{-- Tracking FAQ & Assistance Accordion / Cards --}}
        <div class="rounded-3xl bg-white border border-stone-200/90 shadow-soft p-6 sm:p-8">
          <h3 class="font-extrabold font-display text-base sm:text-lg text-stone-900 mb-6 flex items-center gap-2">
            <span class="w-2 h-5 bg-brand-600 rounded-full"></span>
            <span>Frequently Asked Questions</span>
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 text-xs sm:text-sm">
            <div class="p-4 rounded-2xl bg-stone-50/70 border border-stone-200/70">
              <h4 class="font-bold text-stone-900 mb-1 flex items-center gap-1.5">
                <span class="text-brand-600">Q.</span>
                <span>Where can I find my Order ID?</span>
              </h4>
              <p class="text-stone-600 leading-relaxed">
                Your Order ID (format: <code class="font-mono text-stone-800 bg-white px-1.5 py-0.5 rounded border border-stone-200">ORD-XXXXXX-XXXX</code>) is shown on the confirmation screen immediately after checkout and is sent to your phone via SMS.
              </p>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50/70 border border-stone-200/70">
              <h4 class="font-bold text-stone-900 mb-1 flex items-center gap-1.5">
                <span class="text-brand-600">Q.</span>
                <span>How long does delivery take?</span>
              </h4>
              <p class="text-stone-600 leading-relaxed">
                Standard delivery inside Dhaka is usually completed within <strong>24 to 48 hours</strong>. Deliveries outside Dhaka take between <strong>48 to 72 hours</strong> depending on the destination.
              </p>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50/70 border border-stone-200/70">
              <h4 class="font-bold text-stone-900 mb-1 flex items-center gap-1.5">
                <span class="text-brand-600">Q.</span>
                <span>Why does it ask for my phone number?</span>
              </h4>
              <p class="text-stone-600 leading-relaxed">
                For security and customer privacy, our tracking system verifies that your phone number matches the order records before displaying full recipient and delivery details.
              </p>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50/70 border border-stone-200/70">
              <h4 class="font-bold text-stone-900 mb-1 flex items-center gap-1.5">
                <span class="text-brand-600">Q.</span>
                <span>Can I modify my delivery address?</span>
              </h4>
              <p class="text-stone-600 leading-relaxed">
                If your order is still in <strong>Placed</strong> or <strong>Confirmed</strong> status, contact our support team immediately on WhatsApp or phone to update your address.
              </p>
            </div>
          </div>
        </div>

      </div>

    @endisset

  </div>

</main>
@endsection

@push('scripts')
<script>
(function () {
  // 1-Click Copy Order ID
  var copyBtn = document.getElementById('copyOrderBtn');
  var orderEl = document.getElementById('activeOrderNumber');
  var copyText = document.getElementById('copyOrderText');

  if (copyBtn && orderEl) {
    copyBtn.addEventListener('click', function () {
      var num = orderEl.textContent.trim().replace(/^#/, '');
      if (!num) return;

      navigator.clipboard.writeText(num).then(function () {
        if (copyText) copyText.textContent = 'Copied!';
        if (window.showToast) {
          window.showToast('Order ID copied to clipboard!', 'success');
        }
        setTimeout(function () {
          if (copyText) copyText.textContent = 'Copy';
        }, 2000);
      });
    });
  }
})();
</script>
@endpush
