@extends('layouts.admin')
@section('title', 'API Integrations & Webhooks')

@section('content')
@php
  $couriersActive = (($settings['steadfast_enabled'] ?? '0') === '1') || (($settings['pathao_enabled'] ?? '0') === '1') || (($settings['redx_enabled'] ?? '0') === '1');
  $googleActive = !empty($settings['google_client_id'] ?? env('GOOGLE_CLIENT_ID'));
  $trackingActive = !empty($settings['tracking_gtm_id']) || !empty($settings['tracking_ga4_id']) || !empty($settings['tracking_meta_pixel_id']);
  $mailActive = ($settings['mail_mailer'] ?? 'log') === 'smtp';
@endphp

<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Stats Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>🔌</span> API Integrations &amp; Webhooks
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
          Third-Party Connectors
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Configure automated courier parcel dispatch, Google 1-Click login, conversion tracking pixels, and transactional SMTP mail.
      </p>
    </div>

    {{-- Connection Status Pills --}}
    <div class="flex items-center gap-1.5 flex-wrap self-start sm:self-auto shrink-0">
      <span class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ $couriersActive ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-50 text-stone-500 border border-stone-200' }}">
        🚚 Couriers: {{ $couriersActive ? 'Active' : 'Off' }}
      </span>
      <span class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ $googleActive ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-50 text-stone-500 border border-stone-200' }}">
        🔑 Google: {{ $googleActive ? 'Ready' : 'Off' }}
      </span>
      <span class="px-2.5 py-1 rounded-xl text-[11px] font-black {{ $trackingActive ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-50 text-stone-500 border border-stone-200' }}">
        📈 Pixels: {{ $trackingActive ? 'Active' : 'Off' }}
      </span>
    </div>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs space-y-1">
      <p class="text-rose-950 font-black">⚠️ Please correct the following integration errors:</p>
      <ul class="list-disc list-inside space-y-0.5 font-semibold text-rose-800">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Navigation Segment Tabs --}}
  <div class="flex items-center gap-2 border-b border-stone-200 pb-3 overflow-x-auto no-scrollbar whitespace-nowrap">
    <button type="button" onclick="switchIntegrationTab('couriers', this)" class="integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-stone-900 text-white shadow-2xs transition shrink-0 cursor-pointer">
      🚚 Courier APIs
    </button>
    <button type="button" onclick="switchIntegrationTab('google', this)" class="integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">
      🔑 Google Login (OAuth)
    </button>
    <button type="button" onclick="switchIntegrationTab('tracking', this)" class="integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">
      📊 Marketing &amp; Analytics
    </button>
    <button type="button" onclick="switchIntegrationTab('mail', this)" class="integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">
      ⚙️ Email Server &amp; OTP
    </button>
  </div>

  <script>
  function switchIntegrationTab(tabName, btn) {
    document.querySelectorAll('.integration-tab-btn').forEach(b => {
      b.className = 'integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer';
    });
    btn.className = 'integration-tab-btn px-4 py-2 text-xs font-black rounded-xl bg-stone-900 text-white shadow-2xs transition shrink-0 cursor-pointer';

    document.querySelectorAll('.integration-section').forEach(sec => {
      if (sec.getAttribute('id') === 'sec-' + tabName) {
        sec.classList.remove('hidden');
      } else {
        sec.classList.add('hidden');
      }
    });
  }

  function togglePass(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.type === 'password') {
      el.type = 'text';
      btn.innerText = '🙈';
    } else {
      el.type = 'password';
      btn.innerText = '👁️';
    }
  }

  function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
      const orig = btn.innerText;
      btn.innerText = 'Copied!';
      setTimeout(() => { btn.innerText = orig; }, 2000);
    });
  }
  </script>

  {{-- SECTION 1: COURIER APIS --}}
  <div id="sec-couriers" class="integration-section space-y-5 sm:space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'couriers') }}" class="space-y-5">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
        <span class="text-xs font-black text-stone-700 uppercase tracking-wider">Automated Dispatch Gateways</span>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-md cursor-pointer transition">
          💾 Save Courier API Settings
        </button>
      </div>

      {{-- Steadfast Courier Card --}}
      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-lg border border-emerald-100 shadow-2xs shrink-0">
              📦
            </div>
            <div>
              <h3 class="font-extrabold text-sm sm:text-base text-stone-900">Steadfast Courier API</h3>
              <p class="text-xs text-stone-500">1-Click parcel booking and automatic tracking sync with Steadfast</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="steadfast_enabled" value="1" class="sr-only peer" @checked(($settings['steadfast_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable Steadfast</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Steadfast API Key</label>
            <input name="steadfast_api_key" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['steadfast_api_key'] ?? '' }}" placeholder="e.g. st_key_xxxxxxxx" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Steadfast Secret Key</label>
            <div class="relative">
              <input id="st_sec" name="steadfast_secret_key" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" value="{{ $settings['steadfast_secret_key'] ?? '' }}" placeholder="••••••••••••••••" />
              <button type="button" onclick="togglePass('st_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
        </div>
      </div>

      {{-- Pathao Courier Card --}}
      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center font-bold text-lg border border-amber-100 shadow-2xs shrink-0">
              🛵
            </div>
            <div>
              <h3 class="font-extrabold text-sm sm:text-base text-stone-900">Pathao Courier API</h3>
              <p class="text-xs text-stone-500">Automated Pathao parcel booking &amp; Hermes API token integration</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="pathao_enabled" value="1" class="sr-only peer" @checked(($settings['pathao_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable Pathao</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Environment Mode</label>
            <select name="pathao_env" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              <option value="production" @selected(($settings['pathao_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['pathao_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Pathao Client ID</label>
            <input name="pathao_client_id" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['pathao_client_id'] ?? '' }}" placeholder="Client ID" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Pathao Client Secret</label>
            <div class="relative">
              <input id="pt_sec" name="pathao_client_secret" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" value="{{ $settings['pathao_client_secret'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Pathao Username (Email)</label>
            <input name="pathao_username" type="text" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['pathao_username'] ?? '' }}" placeholder="merchant@email.com" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Pathao Password</label>
            <div class="relative">
              <input id="pt_pass" name="pathao_password" type="password" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" value="{{ $settings['pathao_password'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_pass', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Pathao Store ID (Numeric)</label>
            <input name="pathao_store_id" type="number" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['pathao_store_id'] ?? '' }}" placeholder="e.g. 12345" />
          </div>
        </div>
      </div>

      {{-- RedX Courier Card --}}
      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-rose-50 text-rose-800 flex items-center justify-center font-bold text-lg border border-rose-100 shadow-2xs shrink-0">
              🔴
            </div>
            <div>
              <h3 class="font-extrabold text-sm sm:text-base text-stone-900">RedX Courier API</h3>
              <p class="text-xs text-stone-500">Automated parcel creation via RedX merchant API token</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="redx_enabled" value="1" class="sr-only peer" @checked(($settings['redx_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable RedX</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Environment Mode</label>
            <select name="redx_env" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              <option value="production" @selected(($settings['redx_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['redx_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div class="sm:col-span-2 space-y-1">
            <label class="text-xs font-black text-stone-800 block">RedX API Access Token</label>
            <div class="relative">
              <input id="rx_token" name="redx_api_token" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" value="{{ $settings['redx_api_token'] ?? '' }}" placeholder="Bearer access token..." />
              <button type="button" onclick="togglePass('rx_token', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>

  {{-- SECTION 2: GOOGLE OAUTH SOCIAL LOGIN --}}
  <div id="sec-google" class="integration-section hidden space-y-5 sm:space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'google') }}" class="space-y-5">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
        <span class="text-xs font-black text-stone-700 uppercase tracking-wider">Google 1-Click Social Auth Credentials</span>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-md cursor-pointer transition">
          💾 Save Google OAuth Settings
        </button>
      </div>

      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3.5">
          <h3 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-2">
            <span>🔑</span> Google OAuth 2.0 Client Credentials
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Enable 1-Click Sign In with Google on checkout, customer login, and registration pages</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Google Client ID</label>
            <input name="google_client_id" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['google_client_id'] ?? env('GOOGLE_CLIENT_ID', '') }}" placeholder="e.g. 123456789-xxxx.apps.googleusercontent.com" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Google Client Secret</label>
            <div class="relative">
              <input id="gg_sec" name="google_client_secret" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" value="{{ $settings['google_client_secret'] ?? env('GOOGLE_CLIENT_SECRET', '') }}" placeholder="GOCSPX-••••••••••••••••" />
              <button type="button" onclick="togglePass('gg_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
        </div>

        @php
          $redirectUri = $settings['google_redirect_uri'] ?? env('GOOGLE_REDIRECT_URI', url('/auth/google/callback'));
        @endphp
        <div class="space-y-1 pt-1">
          <label class="text-xs font-black text-stone-800 block">Google OAuth Redirect URI / Callback URL</label>
          <div class="flex items-center gap-2">
            <input name="google_redirect_uri" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white text-stone-700 shadow-2xs" value="{{ $redirectUri }}" />
            <button type="button" onclick="copyText('{{ $redirectUri }}', this)" class="px-3.5 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 border border-stone-200 font-extrabold text-xs shadow-2xs transition shrink-0 cursor-pointer">
              Copy URL
            </button>
          </div>
          <p class="text-[11px] text-stone-400">Copy this exact Authorized Redirect URI into your Google Cloud Console Web Client credentials.</p>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 3: MARKETING & TRACKING APIS --}}
  <div id="sec-tracking" class="integration-section hidden space-y-5 sm:space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'tracking') }}" class="space-y-5">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
        <span class="text-xs font-black text-stone-700 uppercase tracking-wider">Marketing &amp; Analytics Tracking Pixels</span>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-md cursor-pointer transition">
          💾 Save Tracking Settings
        </button>
      </div>

      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3.5">
          <h3 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-2">
            <span>📈</span> Analytics &amp; Conversion Pixels
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Inject tracking snippets cleanly into storefront pages without code edits</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Google Tag Manager (GTM ID)</label>
            <input name="tracking_gtm_id" class="w-full text-xs font-mono font-bold uppercase px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['tracking_gtm_id'] ?? '' }}" placeholder="GTM-XXXXXXX" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Google Analytics 4 (GA4 ID)</label>
            <input name="tracking_ga4_id" class="w-full text-xs font-mono font-bold uppercase px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['tracking_ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Meta Pixel ID (Facebook)</label>
            <input name="tracking_meta_pixel_id" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['tracking_meta_pixel_id'] ?? '' }}" placeholder="1234567890" />
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 4: EMAIL SERVER & OTP --}}
  <div id="sec-mail" class="integration-section hidden space-y-5 sm:space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'mail') }}" class="space-y-5">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs">
        <span class="text-xs font-black text-stone-700 uppercase tracking-wider">Email Gateway &amp; Verification OTP</span>
        <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-md cursor-pointer transition">
          💾 Save Email Settings
        </button>
      </div>

      <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3.5">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-sky-50 text-sky-800 flex items-center justify-center font-bold text-lg border border-sky-100 shadow-2xs shrink-0">
              📧
            </div>
            <div>
              <h3 class="font-extrabold text-sm sm:text-base text-stone-900">SMTP Mail Server</h3>
              <p class="text-xs text-stone-500">Configure transactional emails for order notifications &amp; customer auth</p>
            </div>
          </div>

          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="otp_enabled" value="1" class="sr-only peer" @checked(($settings['otp_enabled'] ?? '1') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable OTP Verification</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Mailer Driver</label>
            <select name="mail_mailer" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              <option value="smtp" @selected(($settings['mail_mailer'] ?? 'log') === 'smtp')>SMTP Server</option>
              <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log File (Local Testing)</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">SMTP Host</label>
            <input name="mail_host" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">SMTP Port</label>
            <input name="mail_port" type="number" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['mail_port'] ?? '' }}" placeholder="587" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">SMTP Username</label>
            <input name="mail_username" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['mail_username'] ?? '' }}" placeholder="user@domain.com" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">SMTP Password</label>
            <div class="relative">
              <input id="smtp_pass" name="mail_password" type="password" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" placeholder="••••••••••••" />
              <button type="button" onclick="togglePass('smtp_pass', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs font-bold cursor-pointer">👁️</button>
            </div>
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">Encryption</label>
            <select name="mail_encryption" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs cursor-pointer">
              <option value="tls" @selected(($settings['mail_encryption'] ?? 'tls') === 'tls')>TLS</option>
              <option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option>
              <option value="none" @selected(($settings['mail_encryption'] ?? '') === 'none')>None</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">From Email Address</label>
            <input name="mail_from_address" type="email" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@yourdomain.com" />
          </div>
          <div class="space-y-1">
            <label class="text-xs font-black text-stone-800 block">From Name</label>
            <input name="mail_from_name" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="My Store" />
          </div>
        </div>
      </div>
    </form>

    {{-- Test Mail Sender Container --}}
    <form method="POST" action="{{ route('admin.integrations.test-mail') }}" class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
      @csrf
      <h3 class="font-extrabold text-sm text-stone-900 flex items-center gap-2">
        <span>🧪</span> Send Instant Test Email
      </h3>
      <p class="text-xs text-stone-500">Send an instant test email to verify your SMTP server connection &amp; firewall permissions.</p>
      
      <div class="flex flex-col sm:flex-row items-center gap-3 pt-1">
        <input name="test_email" type="email" class="w-full sm:max-w-sm text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="your-email@gmail.com" required />
        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-white font-extrabold text-xs shadow-md transition cursor-pointer">
          Send Test Email
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
