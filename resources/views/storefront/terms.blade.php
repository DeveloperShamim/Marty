@extends('layouts.storefront', ['headerVariant' => 'compact'])
@php 
  $title = 'Terms of Service | ' . site_name();
  $site = site_name();
@endphp

@section('content')
<!-- Hero Header Section -->
<section class="relative overflow-hidden bg-slate-900 text-white py-12 sm:py-16">
  <!-- Subtle Background Glow Effects -->
  <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-600/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-5">
    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-4">
      <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
      <span class="text-slate-600">/</span>
      <span class="text-slate-200">Legal</span>
      <span class="text-slate-600">/</span>
      <span class="text-brand-400 font-bold">Terms of Service</span>
    </nav>

    <div class="max-w-3xl space-y-3">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs font-bold text-brand-300 backdrop-blur-sm">
        <span>📜 Official User Agreement</span>
      </div>
      
      <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
        Terms of Service
      </h1>
      
      <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-medium">
        Welcome to {{ $site }}. Please read these terms carefully before exploring our catalog, registering an account, or placing an order.
      </p>

      <!-- Key Metadata Badges -->
      <div class="pt-3 flex flex-wrap items-center gap-3 text-xs text-slate-400">
        <span class="flex items-center gap-1.5 bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-700/50">
          <span>📅</span> Updated: <strong>August 2026</strong>
        </span>
        <span class="flex items-center gap-1.5 bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-700/50">
          <span>⏱️</span> Read Time: <strong>~3 Minutes</strong>
        </span>
        <span class="flex items-center gap-1.5 bg-emerald-950/80 text-emerald-300 px-3 py-1.5 rounded-xl border border-emerald-800/60 font-bold">
          <span>✓</span> Status: Active Policy
        </span>
      </div>
    </div>
  </div>
</section>

<!-- Trust Highlights Bar -->
<section class="border-b border-slate-200 bg-white py-6 shadow-2xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-5">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">🛍️</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Genuine Products</h4>
          <p class="text-[11px] text-slate-500">100% Authentic Catalog</p>
        </div>
      </div>
      
      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">💳</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Verified Payment</h4>
          <p class="text-[11px] text-slate-500">bKash, Nagad, COD & More</p>
        </div>
      </div>

      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">🚚</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Tracked Shipping</h4>
          <p class="text-[11px] text-slate-500">Courier Doorstep Delivery</p>
        </div>
      </div>

      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">🛡️</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Buyer Protection</h4>
          <p class="text-[11px] text-slate-500">7-Day Support Guarantee</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Main Terms Content & Navigation Grid -->
<section class="py-12 bg-slate-50/60">
  <div class="max-w-7xl mx-auto px-4 sm:px-5">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- Left Sidebar: Sticky Table of Contents & Quick Action Box -->
      <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
        
        <!-- Table of Contents Card -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <span>📌</span> Quick Navigation
            </h3>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Index</span>
          </div>

          <nav class="space-y-1 text-xs font-semibold text-slate-600">
            <a href="#section-1" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">01.</span>
                <span>Acceptance & Eligibility</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-2" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">02.</span>
                <span>Account & Order Placement</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-3" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">03.</span>
                <span>Pricing & Payment Verification</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-4" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">04.</span>
                <span>Shipping & Delivery Terms</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-5" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">05.</span>
                <span>Returns, Refunds & Exchanges</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-6" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">06.</span>
                <span>Fraud & Risk Blacklist Policy</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-7" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">07.</span>
                <span>Intellectual Property & Content</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>

            <a href="#section-8" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-brand-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-brand-600">08.</span>
                <span>Customer Support & Contact</span>
              </span>
              <span class="text-slate-300 group-hover:text-brand-600">&rarr;</span>
            </a>
          </nav>
        </div>

        <!-- Print & Related Policies Box -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-3">
          <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400">Actions & Related Policies</h4>

          <button type="button" onclick="window.print()" class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-xs transition flex items-center justify-center gap-2 cursor-pointer">
            <span>🖨️</span> Print Terms Document
          </button>

          <a href="{{ route('privacy') }}" class="w-full py-2.5 px-4 rounded-2xl border border-slate-200 hover:border-brand-500 bg-white text-slate-700 hover:text-brand-600 font-extrabold text-xs transition flex items-center justify-between group">
            <span class="flex items-center gap-2">
              <span>🔒</span> Privacy Policy
            </span>
            <span class="text-slate-400 group-hover:text-brand-600">&rarr;</span>
          </a>
        </div>

      </aside>

      <!-- Right Area: Detailed Terms Articles -->
      <main class="lg:col-span-8 space-y-8">
        
        <!-- Custom Content Override from Settings (If Configured) -->
        @if(!empty($customContent))
          <div class="rounded-3xl border border-brand-200 bg-brand-50/40 p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-brand-700">
              <span>✨</span> Custom Policy Addendum
            </div>
            <div class="prose prose-slate text-sm leading-relaxed text-slate-700 space-y-4">
              @foreach(preg_split('/\n\n+/', (string) $customContent) as $para)
                @if(trim($para)) <p class="leading-relaxed">{{ $para }}</p> @endif
              @endforeach
            </div>
          </div>
        @endif

        <!-- Standard Terms Sections -->

        <!-- Section 01 -->
        <article id="section-1" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">01</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Acceptance &amp; Eligibility</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">General Scope</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            By accessing, browsing, or making purchases on <strong>{{ $site }}</strong>, you acknowledge that you have read, understood, and agreed to be legally bound by these Terms of Service. If you do not agree with any part of these terms, you must refrain from placing orders or utilizing our storefront services.
          </p>

          <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-extrabold flex items-center gap-1.5">
              <span>⚠️</span> Important Note:
            </p>
            <p class="leading-relaxed">
              Users must be at least 18 years of age or possess legal parental/guardian consent to make online financial transactions on {{ $site }}.
            </p>
          </div>
        </article>

        <!-- Section 02 -->
        <article id="section-2" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">02</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Account Registration &amp; Order Accuracy</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Ordering Rules</span>
          </div>

          <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
              When placing an order or registering an account, you agree to provide true, accurate, current, and complete contact details including your full name, valid phone number, and physical shipping address.
            </p>
            <ul class="space-y-2 text-xs list-disc list-inside text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <li>Orders placed with invalid or uncontactable mobile numbers may be subject to instant cancellation.</li>
              <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
              <li>{{ $site }} reserves the right to decline any order suspected of fraudulent intent.</li>
            </ul>
          </div>
        </article>

        <!-- Section 03 -->
        <article id="section-3" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">03</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Pricing &amp; Mobile Banking Verification</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Payments</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            All prices displayed on {{ $site }} are listed in Bangladeshi Taka (BDT) inclusive of applicable taxes, unless explicitly stated otherwise.
          </p>

          <div class="grid sm:grid-cols-2 gap-4 pt-1 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
              <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                <span>📱</span> bKash / Nagad / Rocket Payments
              </span>
              <p class="text-slate-600 leading-snug">
                For mobile wallet transfers, customers must submit a valid sender phone number and unique Transaction ID (TrxID). Duplicate or fabricated TrxIDs will be automatically rejected.
              </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
              <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                <span>💵</span> Cash on Delivery (COD)
              </span>
              <p class="text-slate-600 leading-snug">
                COD orders are confirmed upon phone verification. Payable amount must be handed over in full to the courier agent upon parcel receipt.
              </p>
            </div>
          </div>
        </article>

        <!-- Section 04 -->
        <article id="section-4" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">04</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Shipping &amp; Delivery Timelines</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Logistics</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            Parcels are dispatched through trusted courier partners (including Steadfast, Pathao, or RedX). Estimated delivery windows are as follows:
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-1">
              <span class="font-extrabold text-emerald-900 block">🏙️ Inside Dhaka City</span>
              <p class="text-emerald-800">Standard Delivery: 24 to 48 Hours</p>
              <span class="text-[10px] text-emerald-700 font-bold block mt-1">Shipping Charge: Calculated at checkout</span>
            </div>

            <div class="p-4 rounded-2xl bg-brand-50/70 border border-brand-200/80 space-y-1">
              <span class="font-extrabold text-brand-900 block">🏞️ Outside Dhaka (Suburbs &amp; Districts)</span>
              <p class="text-brand-800">Standard Delivery: 3 to 5 Business Days</p>
              <span class="text-[10px] text-brand-700 font-bold block mt-1">Courier tracking code provided via SMS/Email</span>
            </div>
          </div>
        </article>

        <!-- Section 05 -->
        <article id="section-5" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">05</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Returns, Refunds &amp; Exchanges</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Customer Rights</span>
          </div>

          <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
              We strive to deliver flawless products. If you receive a damaged, defective, or incorrect item, you are entitled to a replacement or refund under the following conditions:
            </p>
            <ul class="space-y-2 text-xs list-disc list-inside text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <li><strong>Notice Period:</strong> Issues must be reported within 7 days of receiving the parcel.</li>
              <li><strong>Condition:</strong> Items must be returned unused in their original packaging with tags intact.</li>
              <li><strong>Unboxing Video:</strong> Recording an unboxing video upon parcel delivery is recommended for faster claim processing.</li>
            </ul>
          </div>
        </article>

        <!-- Section 06 -->
        <article id="section-6" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-rose-50 text-rose-600 font-mono font-bold text-xs">06</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Fraud Prevention &amp; Blacklist Policy</h2>
            </div>
            <span class="text-xs text-rose-500 font-extrabold">Security Enforcement</span>
          </div>

          <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
              To protect honest shoppers and prevent fraud, {{ $site }} employs zero-API automated risk detection and blacklist management.
            </p>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-2">
              <p class="font-extrabold flex items-center gap-1.5">
                <span>🚫</span> Zero Tolerance Actions:
              </p>
              <ul class="space-y-1 list-disc list-inside text-rose-800">
                <li>Submitting false Transaction IDs or fake payment receipts.</li>
                <li>Repeatedly rejecting COD parcels at doorstep without valid cause.</li>
                <li>Prank orders or automated spam submissions.</li>
              </ul>
              <p class="text-[11px] text-rose-700 pt-1 border-t border-rose-200">
                Offending phone numbers or IP addresses will be blacklisted from placing future orders across our network.
              </p>
            </div>
          </div>
        </article>

        <!-- Section 07 -->
        <article id="section-7" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">07</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Intellectual Property &amp; Copyright</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Ownership</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            All content published on {{ $site }}—including logos, imagery, product designs, banners, text typography, and software scripts—is the exclusive intellectual property of {{ $site }}. Unauthorized copying or commercial reproduction is strictly prohibited.
          </p>
        </article>

        <!-- Section 08 -->
        <article id="section-8" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-brand-50 text-brand-600 font-mono font-bold text-xs">08</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Customer Support &amp; Assistance</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Help Center</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            If you have questions regarding these terms, require assistance with an existing order, or wish to submit feedback, our support team is ready to assist you.
          </p>

          <div class="p-4 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <h4 class="font-extrabold text-sm text-white">Need Quick Support?</h4>
              <p class="text-xs text-slate-300">Our customer team responds within 24 hours.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <a href="{{ route('contact') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs transition shadow-sm">
                📩 Contact Support Page
              </a>
              <a href="{{ route('track') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-extrabold text-xs transition">
                📦 Track Order
              </a>
            </div>
          </div>
        </article>

      </main>

    </div>
  </div>
</section>

<!-- Footer Banner CTA -->
<section class="border-t border-slate-200 bg-white py-10">
  <div class="mx-auto max-w-4xl px-4 text-center space-y-3">
    <h3 class="font-display text-2xl font-extrabold text-slate-900">Have questions about your order or our policies?</h3>
    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl mx-auto">
      Our dedicated support team is available to assist you with any questions regarding product availability, payments, or delivery.
    </p>
    <div class="pt-2 flex items-center justify-center gap-3 flex-wrap">
      <a href="{{ route('contact') }}" class="px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs transition shadow-md hover:shadow-lg">
        Contact Us
      </a>
      <a href="{{ route('shop') }}" class="px-6 py-3 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-xs transition">
        Browse Products &rarr;
      </a>
    </div>
  </div>
</section>
@endsection
