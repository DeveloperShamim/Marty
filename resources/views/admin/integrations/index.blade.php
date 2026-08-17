@extends('layouts.admin')
@section('title', 'API Integrations & Webhooks')

@section('content')
<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>🔌 API Integrations &amp; Webhooks</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Manage courier dispatch APIs, Google OAuth, marketing tracking pixels, and SMTP email server gateways</p>
    </div>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-extrabold shadow-2xs space-y-1">
      <p class="text-rose-950 font-black">⚠️ Please correct the following integration errors:</p>
      <ul class="list-disc list-inside space-y-0.5 font-semibold text-rose-800">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Navigation Tabs --}}
  <div class="flex items-center gap-2 border-b border-stone-200 pb-3 overflow-x-auto no-scrollbar whitespace-nowrap">
    <button type="button" onclick="switchIntegrationTab('couriers', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-2xs transition shrink-0 cursor-pointer">🚚 Courier APIs</button>
    <button type="button" onclick="switchIntegrationTab('google', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">🔑 Google Login (OAuth)</button>
    <button type="button" onclick="switchIntegrationTab('tracking', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">📊 Marketing &amp; Analytics</button>
    <button type="button" onclick="switchIntegrationTab('mail', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">⚙️ Email Server &amp; OTP</button>
  </div>

  <script>
  function switchIntegrationTab(tabName, btn) {
    document.querySelectorAll('.integration-tab-btn').forEach(b => {
      b.className = 'integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer';
    });
    btn.className = 'integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-2xs transition shrink-0 cursor-pointer';

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
  </script>

  {{-- SECTION 1: COURIER APIS --}}
  <div id="sec-couriers" class="integration-section space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'couriers') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
        <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Courier Dispatch Integrations</span>
        <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-2xs cursor-pointer">Save Courier API Settings</button>
      </div>

      <!-- Steadfast -->
      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
              <span>📦</span> Steadfast Courier API
            </h3>
            <p class="text-xs text-stone-500 mt-0.5">One-click parcel dispatch &amp; automatic tracking via Steadfast portal</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="steadfast_enabled" value="1" class="sr-only peer" @checked(($settings['steadfast_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable Steadfast</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Steadfast API Key</label>
            <input name="steadfast_api_key" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['steadfast_api_key'] ?? '' }}" placeholder="e.g. st_key_xxxxxxxx" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Steadfast Secret Key</label>
            <div class="relative">
              <input id="st_sec" name="steadfast_secret_key" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none pr-10" value="{{ $settings['steadfast_secret_key'] ?? '' }}" placeholder="••••••••••••••••" />
              <button type="button" onclick="togglePass('st_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pathao -->
      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
              <span>🛵</span> Pathao Courier API
            </h3>
            <p class="text-xs text-stone-500 mt-0.5">Automated Pathao parcel booking &amp; Hermes API token integration</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="pathao_enabled" value="1" class="sr-only peer" @checked(($settings['pathao_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable Pathao</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Environment Mode</label>
            <select name="pathao_env" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl">
              <option value="production" @selected(($settings['pathao_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['pathao_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Pathao Client ID</label>
            <input name="pathao_client_id" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['pathao_client_id'] ?? '' }}" placeholder="Client ID" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Pathao Client Secret</label>
            <div class="relative">
              <input id="pt_sec" name="pathao_client_secret" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl pr-10" value="{{ $settings['pathao_client_secret'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Pathao Username (Email)</label>
            <input name="pathao_username" type="text" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['pathao_username'] ?? '' }}" placeholder="merchant@email.com" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Pathao Password</label>
            <div class="relative">
              <input id="pt_pass" name="pathao_password" type="password" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl pr-10" value="{{ $settings['pathao_password'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_pass', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Pathao Store ID (Numeric)</label>
            <input name="pathao_store_id" type="number" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['pathao_store_id'] ?? '' }}" placeholder="e.g. 12345" />
          </div>
        </div>
      </div>

      <!-- RedX -->
      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
              <span>🔴</span> RedX Courier API
            </h3>
            <p class="text-xs text-stone-500 mt-0.5">Automated parcel creation via RedX merchant API token</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="redx_enabled" value="1" class="sr-only peer" @checked(($settings['redx_enabled'] ?? '0') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable RedX</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Environment Mode</label>
            <select name="redx_env" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl">
              <option value="production" @selected(($settings['redx_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['redx_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="text-xs font-extrabold text-stone-800 block mb-1">RedX API Access Token</label>
            <div class="relative">
              <input id="rx_token" name="redx_api_token" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl pr-10" value="{{ $settings['redx_api_token'] ?? '' }}" placeholder="Bearer access token..." />
              <button type="button" onclick="togglePass('rx_token', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION: GOOGLE OAUTH SOCIAL LOGIN --}}
  <div id="sec-google" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'google') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
        <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Google 1-Click Social Auth Credentials</span>
        <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Google OAuth Settings</button>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3">
          <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
            <span>🔑</span> Google OAuth 2.0 Client Credentials
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Enable 1-Click Sign In with Google on checkout, login, and registration pages</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Google Client ID</label>
            <input name="google_client_id" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['google_client_id'] ?? env('GOOGLE_CLIENT_ID', '') }}" placeholder="e.g. 123456789-xxxx.apps.googleusercontent.com" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Google Client Secret</label>
            <div class="relative">
              <input id="gg_sec" name="google_client_secret" type="password" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl pr-10" value="{{ $settings['google_client_secret'] ?? env('GOOGLE_CLIENT_SECRET', '') }}" placeholder="GOCSPX-••••••••••••••••" />
              <button type="button" onclick="togglePass('gg_sec', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
        </div>

        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Google OAuth Redirect URI / Callback URL</label>
          <input name="google_redirect_uri" type="text" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl text-stone-600" value="{{ $settings['google_redirect_uri'] ?? env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')) }}" placeholder="{{ url('/auth/google/callback') }}" />
          <p class="text-[11px] text-stone-400 mt-1">Copy this exact Authorized redirect URI into your Google Cloud Console Web Client Settings.</p>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 2: MARKETING & TRACKING APIS --}}
  <div id="sec-tracking" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'tracking') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
        <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Marketing &amp; Analytics Tracking Pixels</span>
        <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Tracking Settings</button>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="border-b border-stone-100 pb-3">
          <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
            <span>📈</span> Analytics &amp; Conversion Pixels
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Inject tracking snippets cleanly into storefront pages</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Google Tag Manager (GTM ID)</label>
            <input name="tracking_gtm_id" class="w-full text-xs font-mono font-bold uppercase px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['tracking_gtm_id'] ?? '' }}" placeholder="GTM-XXXXXXX" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Google Analytics 4 (GA4 ID)</label>
            <input name="tracking_ga4_id" class="w-full text-xs font-mono font-bold uppercase px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['tracking_ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" />
          </div>
          <div>
            <label class="text-xs font-extrabold text-stone-800 block mb-1">Meta Pixel ID (Facebook)</label>
            <input name="tracking_meta_pixel_id" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['tracking_meta_pixel_id'] ?? '' }}" placeholder="1234567890" />
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 3: EMAIL SERVER & OTP --}}
  <div id="sec-mail" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'mail') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
        <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Email Gateway &amp; Verification OTP</span>
        <button type="submit" class="px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Email Settings</button>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
              <span>📧</span> SMTP Mail Server
            </h3>
            <p class="text-xs text-stone-500 mt-0.5">Configure transactional emails for orders &amp; customer auth</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="otp_enabled" value="1" class="sr-only peer" @checked(($settings['otp_enabled'] ?? '1') === '1') />
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Enable OTP Verification</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">Mailer Driver</label>
            <select name="mail_mailer" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl">
              <option value="smtp" @selected(($settings['mail_mailer'] ?? 'log') === 'smtp')>SMTP Server</option>
              <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log File (Local Testing)</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">SMTP Host</label>
            <input name="mail_host" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" />
          </div>
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">SMTP Port</label>
            <input name="mail_port" type="number" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['mail_port'] ?? '' }}" placeholder="587" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">SMTP Username</label>
            <input name="mail_username" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['mail_username'] ?? '' }}" placeholder="user@domain.com" />
          </div>
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">SMTP Password</label>
            <div class="relative">
              <input id="smtp_pass" name="mail_password" type="password" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl pr-10" placeholder="••••••••••••" />
              <button type="button" onclick="togglePass('smtp_pass', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold">👁️</button>
            </div>
          </div>
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">Encryption</label>
            <select name="mail_encryption" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl">
              <option value="tls" @selected(($settings['mail_encryption'] ?? 'tls') === 'tls')>TLS</option>
              <option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option>
              <option value="none" @selected(($settings['mail_encryption'] ?? '') === 'none')>None</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">From Email Address</label>
            <input name="mail_from_address" type="email" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@yourdomain.com" />
          </div>
          <div>
            <label class="text-xs font-bold text-stone-700 block mb-1">From Name</label>
            <input name="mail_from_name" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="My Store" />
          </div>
        </div>
      </div>
    </form>

    <!-- Test Mail Sender -->
    <form method="POST" action="{{ route('admin.integrations.test-mail') }}" class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
      @csrf
      <h3 class="font-extrabold text-sm text-stone-900 flex items-center gap-2">
        <span>🧪</span> Send Test Email
      </h3>
      <p class="text-xs text-stone-500">Send an instant test email to verify your SMTP server connection.</p>
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <input name="test_email" type="email" class="w-full sm:max-w-sm text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" placeholder="your-email@gmail.com" required />
        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 cursor-pointer">Send Test Email</button>
      </div>
    </form>
  </div>

</div>
@endsection
