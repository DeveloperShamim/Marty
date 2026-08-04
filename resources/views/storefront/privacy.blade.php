@extends('layouts.storefront', ['headerVariant' => 'compact'])
@php 
  $title = 'Privacy Policy | ' . site_name();
  $site = site_name();
@endphp

@section('content')
<!-- Hero Header Section -->
<section class="relative overflow-hidden bg-slate-900 text-white py-12 sm:py-16">
  <!-- Background Glow Effects -->
  <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-600/20 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-5">
    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-4">
      <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
      <span class="text-slate-600">/</span>
      <span class="text-slate-200">Legal</span>
      <span class="text-slate-600">/</span>
      <span class="text-emerald-400 font-bold">Privacy Policy</span>
    </nav>

    <div class="max-w-3xl space-y-3">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs font-bold text-emerald-300 backdrop-blur-sm">
        <span>🛡️ Data Protection Guarantee</span>
      </div>
      
      <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
        Privacy Policy
      </h1>
      
      <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-medium">
        At {{ $site }}, your privacy is paramount. Learn how we collect, safeguard, and responsibly manage your personal information when you shop with us.
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
          <span>🔒</span> Encrypted &amp; Secure
        </span>
      </div>
    </div>
  </div>
</section>

<!-- Data Commitment Highlights Bar -->
<section class="border-b border-slate-200 bg-white py-6 shadow-2xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-5">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">🚫</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Zero Data Selling</h4>
          <p class="text-[11px] text-slate-500">We Never Sell Your Data</p>
        </div>
      </div>
      
      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">🔐</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Encrypted Storage</h4>
          <p class="text-[11px] text-slate-500">HTTPS &amp; Secure Storage</p>
        </div>
      </div>

      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">📦</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Delivery Only</h4>
          <p class="text-[11px] text-slate-500">Logistics Partner Sharing</p>
        </div>
      </div>

      <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
        <span class="text-2xl shrink-0">👤</span>
        <div>
          <h4 class="font-extrabold text-xs text-slate-900">Full Control</h4>
          <p class="text-[11px] text-slate-500">Request Removal Anytime</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Main Privacy Policy Content & Index Grid -->
<section class="py-12 bg-slate-50/60">
  <div class="max-w-7xl mx-auto px-4 sm:px-5">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- Left Sidebar: Sticky Table of Contents -->
      <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
        
        <!-- Index Navigation Card -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <span>📌</span> Privacy Topics
            </h3>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Index</span>
          </div>

          <nav class="space-y-1 text-xs font-semibold text-slate-600">
            <a href="#privacy-1" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">01.</span>
                <span>Information We Collect</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-2" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">02.</span>
                <span>How We Use Your Data</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-3" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">03.</span>
                <span>Mobile Banking &amp; Payments</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-4" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">04.</span>
                <span>Logistics &amp; Courier Sharing</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-5" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">05.</span>
                <span>Cookies &amp; Tracking Analytics</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-6" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">06.</span>
                <span>Data Security &amp; Retention</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-7" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">07.</span>
                <span>Your Data Rights &amp; Access</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>

            <a href="#privacy-8" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100 hover:text-emerald-600 transition-colors group">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[10px] font-bold text-slate-400 group-hover:text-emerald-600">08.</span>
                <span>Privacy Contact Channel</span>
              </span>
              <span class="text-slate-300 group-hover:text-emerald-600">&rarr;</span>
            </a>
          </nav>
        </div>

        <!-- Quick Actions & Links -->
        <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm space-y-3">
          <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400">Actions &amp; Terms</h4>

          <button type="button" onclick="window.print()" class="w-full py-2.5 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-xs transition flex items-center justify-center gap-2 cursor-pointer">
            <span>🖨️</span> Print Privacy Policy
          </button>

          <a href="{{ route('terms') }}" class="w-full py-2.5 px-4 rounded-2xl border border-slate-200 hover:border-brand-500 bg-white text-slate-700 hover:text-brand-600 font-extrabold text-xs transition flex items-center justify-between group">
            <span class="flex items-center gap-2">
              <span>📜</span> Terms of Service
            </span>
            <span class="text-slate-400 group-hover:text-brand-600">&rarr;</span>
          </a>
        </div>

      </aside>

      <!-- Right Area: Privacy Articles -->
      <main class="lg:col-span-8 space-y-8">
        
        <!-- Custom Content Override from Settings (If Configured) -->
        @if(!empty($customContent))
          <div class="rounded-3xl border border-emerald-200 bg-emerald-50/40 p-6 sm:p-8 space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-800">
              <span>✨</span> Custom Privacy Statement
            </div>
            <div class="prose prose-slate text-sm leading-relaxed text-slate-700 space-y-4">
              @foreach(preg_split('/\n\n+/', (string) $customContent) as $para)
                @if(trim($para)) <p class="leading-relaxed">{{ $para }}</p> @endif
              @endforeach
            </div>
          </div>
        @endif

        <!-- Standard Privacy Articles -->

        <!-- Section 01 -->
        <article id="privacy-1" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">01</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Information We Collect</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Data Collection</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            When you visit {{ $site }}, register an account, or place an order, we collect specific information needed to process your transaction and deliver your order smoothly:
          </p>

          <div class="grid sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
              <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                <span>👤</span> Customer Contact Data
              </span>
              <ul class="space-y-1 text-slate-600 list-disc list-inside">
                <li>Full Name</li>
                <li>Mobile Phone Number</li>
                <li>Email Address (Optional/Account)</li>
                <li>Delivery Address &amp; District</li>
              </ul>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5">
              <span class="font-extrabold text-slate-900 flex items-center gap-1.5">
                <span>💻</span> Order &amp; System Metadata
              </span>
              <ul class="space-y-1 text-slate-600 list-disc list-inside">
                <li>Ordered Items &amp; Variations</li>
                <li>Transaction IDs (TrxID) for bKash/Nagad</li>
                <li>IP Address &amp; Device User-Agent</li>
                <li>UTM Traffic Referral Source</li>
              </ul>
            </div>
          </div>
        </article>

        <!-- Section 02 -->
        <article id="privacy-2" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">02</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">How We Use Your Information</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Data Purpose</span>
          </div>

          <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
              Your data is strictly used to fulfill legitimate e-commerce operations. Specifically:
            </p>
            <ul class="space-y-2 text-xs list-disc list-inside text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <li><strong>Order Processing:</strong> Confirming orders, verifying payment details, and dispatching parcels.</li>
              <li><strong>Customer Communication:</strong> Sending SMS/email order updates, tracking codes, and delivery notifications.</li>
              <li><strong>Fraud Prevention:</strong> Checking phone numbers and IP addresses against risk rules to prevent fake orders.</li>
              <li><strong>Platform Improvement:</strong> Analyzing store performance and popular product categories.</li>
            </ul>
          </div>
        </article>

        <!-- Section 03 -->
        <article id="privacy-3" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">03</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Mobile Banking &amp; Payment Security</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Payment Protection</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            We prioritize the security of your financial data. Mobile banking transactions (bKash, Nagad, Rocket) are verified using payment Transaction IDs (TrxID) provided by you at checkout.
          </p>

          <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-xs text-emerald-950 space-y-1.5">
            <p class="font-extrabold flex items-center gap-1.5">
              <span>🔒</span> Payment Privacy Guarantee:
            </p>
            <p class="leading-relaxed">
              We do not store bank passwords, PINs, or confidential credentials. Transaction IDs are retained solely to verify order payments and maintain accounting statements.
            </p>
          </div>
        </article>

        <!-- Section 04 -->
        <article id="privacy-4" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">04</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Logistics &amp; Courier Sharing</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Third-Party Logistics</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            To deliver your package to your doorstep, we transmit necessary shipping details to our integrated courier API partners (such as Steadfast, Pathao, or RedX):
          </p>

          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-700 space-y-2">
            <p class="font-extrabold text-slate-900">Data Shared With Courier Providers:</p>
            <ul class="space-y-1 list-disc list-inside text-slate-600">
              <li>Recipient Name &amp; Contact Mobile Number</li>
              <li>Complete Shipping Address &amp; Destination City</li>
              <li>Order Parcel Weight &amp; Payable Cash-on-Delivery (COD) Amount</li>
            </ul>
            <p class="text-[11px] text-slate-500 pt-1 border-t border-slate-200">
              Courier partners are contractually obligated to use this data strictly for parcel delivery and tracking.
            </p>
          </div>
        </article>

        <!-- Section 05 -->
        <article id="privacy-5" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">05</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Cookies &amp; Session Tracking</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Browsing Cookies</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            {{ $site }} uses essential browser cookies and session state to provide standard shopping features (such as maintaining items in your shopping cart, remembering user login, and tracking UTM referral sources).
          </p>
        </article>

        <!-- Section 06 -->
        <article id="privacy-6" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">06</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Data Security &amp; Protection</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Security Measures</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            We implement industry-standard administrative and technical security measures to protect your information against unauthorized access, disclosure, or alteration. All web communications are transmitted via SSL (HTTPS) encryption.
          </p>
        </article>

        <!-- Section 07 -->
        <article id="privacy-7" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">07</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Your Rights &amp; Data Control</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">User Rights</span>
          </div>

          <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
            <p>
              You maintain full ownership of your personal information. As a customer of {{ $site }}, you have the right to:
            </p>
            <ul class="space-y-2 text-xs list-disc list-inside text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <li>Request a copy of the personal information stored in your account.</li>
              <li>Request corrections or updates to inaccurate profile details.</li>
              <li>Request the deletion of your account and personal history (where legally permissible).</li>
            </ul>
          </div>
        </article>

        <!-- Section 08 -->
        <article id="privacy-8" class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm space-y-4 scroll-mt-24">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 font-mono font-bold text-xs">08</span>
              <h2 class="font-display font-extrabold text-lg text-slate-900">Privacy Inquiries &amp; Contact</h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Contact</span>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed">
            If you have questions, concerns, or requests regarding this Privacy Policy or how your data is handled, please reach out to our privacy support team.
          </p>

          <div class="p-4 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <h4 class="font-extrabold text-sm text-white">Privacy Assistance</h4>
              <p class="text-xs text-slate-300">We respond to data inquiries within 24 hours.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <a href="{{ route('contact') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs transition shadow-sm">
                📩 Contact Support Page
              </a>
              <a href="{{ route('terms') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-extrabold text-xs transition">
                📜 Terms of Service
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
    <h3 class="font-display text-2xl font-extrabold text-slate-900">Your privacy and data security are our top priorities</h3>
    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl mx-auto">
      Shop with confidence knowing that your personal details and payment statements are handled with maximum care and protection.
    </p>
    <div class="pt-2 flex items-center justify-center gap-3 flex-wrap">
      <a href="{{ route('shop') }}" class="px-6 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition shadow-md hover:shadow-lg">
        Start Shopping &rarr;
      </a>
      <a href="{{ route('contact') }}" class="px-6 py-3 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-xs transition">
        Contact Customer Desk
      </a>
    </div>
  </div>
</section>
@endsection
