@extends('layouts.storefront')

@php
  $address = setting('contact_address');
  $phone = setting('contact_phone');
  $email = setting('contact_email');
  $hours = trim((string) setting('contact_hours', ''));

  // Same WhatsApp number rules as the floating chat widget
  $waPhone = preg_replace('/[^0-9]/', '', (string) (setting('whatsapp_number') ?: $phone ?: ''));
  if (str_starts_with($waPhone, '0')) {
      $waPhone = '88' . $waPhone;
  } elseif (!str_starts_with($waPhone, '880') && strlen($waPhone) === 10) {
      $waPhone = '880' . $waPhone;
  }
  $waUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode('Hello ' . site_name() . ', I have an inquiry.') : null;

  $cardClass = 'group flex items-start gap-4 bg-white rounded-2xl border border-stone-200/90 p-5 sm:p-6 transition-all';
  $iconClass = 'grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100';
@endphp

@section('content')
  <main class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-5 sm:py-8">
    <nav class="text-xs sm:text-sm text-stone-500 mb-2 flex items-center gap-1.5">
      <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
      <span class="text-stone-300">/</span>
      <span class="text-stone-800 font-semibold">Contact</span>
    </nav>

    <div class="max-w-4xl">
      <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">{{ setting('contact_title', 'Get in touch') }}</h1>
      @if(setting('contact_intro'))
        <p class="text-sm sm:text-base text-stone-500 mt-2 max-w-2xl leading-relaxed">{{ setting('contact_intro') }}</p>
      @endif

      @if($phone || $waUrl)
        <div class="flex flex-wrap gap-2.5 mt-5">
          @if($phone)
            <a href="tel:{{ preg_replace('/\s+/', '', (string) $phone) }}" class="btn-primary inline-flex items-center gap-2 h-11 px-5 rounded-lg text-sm font-bold">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.3a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.2l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.2-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.7 21 3 14.3 3 6V5z"/></svg>
              Call us
            </a>
          @endif
          @if($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 h-11 px-5 rounded-lg text-sm font-bold bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50 transition-colors">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3 3 3 0 00-.9 2.2 5.2 5.2 0 001.1 2.7 11.8 11.8 0 004.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 001.8-1.3 2.2 2.2 0 00.2-1.3c-.1-.1-.3-.2-.6-.3z"/></svg>
              WhatsApp
            </a>
          @endif
        </div>
      @endif

      <div class="grid sm:grid-cols-2 gap-3 sm:gap-4 mt-6 sm:mt-8">
        @if($phone)
          <a href="tel:{{ preg_replace('/\s+/', '', (string) $phone) }}" class="{{ $cardClass }} hover:border-brand-300/70 hover:shadow-md">
            <span class="{{ $iconClass }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.3a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.2l-2.26 1.13a11 11 0 005.52 5.52l1.13-2.26a1 1 0 011.2-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.7 21 3 14.3 3 6V5z"/></svg>
            </span>
            <span class="min-w-0">
              <span class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Phone</span>
              <span class="block mt-1 font-bold text-stone-900 group-hover:text-brand-600 transition-colors">{{ $phone }}</span>
            </span>
          </a>
        @endif
        @if($email)
          <a href="mailto:{{ $email }}" class="{{ $cardClass }} hover:border-brand-300/70 hover:shadow-md">
            <span class="{{ $iconClass }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <span class="min-w-0">
              <span class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Email</span>
              <span class="block mt-1 font-bold text-stone-900 group-hover:text-brand-600 transition-colors break-all">{{ $email }}</span>
            </span>
          </a>
        @endif
        @if($address)
          <div class="{{ $cardClass }}">
            <span class="{{ $iconClass }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            </span>
            <span class="min-w-0">
              <span class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Address</span>
              <span class="block mt-1 text-sm text-stone-700 leading-relaxed">{{ $address }}</span>
            </span>
          </div>
        @endif
        @if($hours !== '')
          <div class="{{ $cardClass }}">
            <span class="{{ $iconClass }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
            </span>
            <span class="min-w-0">
              <span class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Hours</span>
              <span class="block mt-1 font-bold text-stone-900">{{ $hours }}</span>
            </span>
          </div>
        @endif
      </div>

      @if(! $address && ! $phone && ! $email && $hours === '')
        <p class="text-sm text-stone-500 mt-6">Contact details are not configured yet.</p>
      @endif

      <div class="mt-6 sm:mt-8 rounded-2xl bg-brand-50 ring-1 ring-brand-100 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="font-extrabold text-stone-900">Already placed an order?</h2>
          <p class="text-sm text-stone-600 mt-0.5">Check where your parcel is with your order number or phone.</p>
        </div>
        <a href="{{ route('track') }}" class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg text-sm font-bold bg-white text-stone-900 border border-stone-200 hover:border-brand-300 shrink-0 transition-colors">
          Track your order
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
    </div>
  </main>
@endsection
