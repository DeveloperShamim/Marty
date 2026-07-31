@extends('layouts.admin')
@section('title', 'API Integrations')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>🔌</span> API Integrations &amp; Webhooks
      </h2>
      <p class="text-xs text-gray-500 mt-1">Manage external courier APIs, marketing tracking pixels, and email server gateways in one place.</p>
    </div>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
      ✓ {{ session('status') }}
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-xs space-y-1">
      @foreach($errors->all() as $err)
        <p>⚠️ {{ $err }}</p>
      @endforeach
    </div>
  @endif

  {{-- Navigation Tabs --}}
  <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto no-scrollbar">
    <button type="button" onclick="switchIntegrationTab('couriers', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-xs transition shrink-0 cursor-pointer">🚚 Courier APIs</button>
    <button type="button" onclick="switchIntegrationTab('google', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">🔑 Google Login (OAuth)</button>
    <button type="button" onclick="switchIntegrationTab('tracking', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">📊 Marketing &amp; Analytics</button>
    <button type="button" onclick="switchIntegrationTab('mail', this)" class="integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">⚙️ Email Server &amp; OTP</button>
  </div>

  <script>
  function switchIntegrationTab(tabName, btn) {
    document.querySelectorAll('.integration-tab-btn').forEach(b => {
      b.className = 'integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer';
    });
    btn.className = 'integration-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-xs transition shrink-0 cursor-pointer';

    document.querySelectorAll('.integration-section').forEach(sec => {
      if (sec.getAttribute('id') === 'sec-' + tabName) {
        sec.classList.remove('hidden');
      } else {
        sec.classList.add('hidden');
      }
    });
  }
  </script>

  {{-- SECTION 1: COURIER APIS --}}
  <div id="sec-couriers" class="integration-section space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'couriers') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Courier Dispatch Integrations</span>
        <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Courier API Settings</button>
      </div>

      <!-- Steadfast -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="font-bold text-base text-ink flex items-center gap-2">
              <span>📦</span> Steadfast Courier API
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">One-click parcel dispatch &amp; automatic tracking via Steadfast portal</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="steadfast_enabled" value="1" class="sr-only peer" @checked(($settings['steadfast_enabled'] ?? '0') === '1') />
            <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></span>
            <span class="ml-2 text-xs font-bold text-gray-700">Enable Steadfast</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Steadfast API Key</label>
            <input name="steadfast_api_key" type="text" class="inp font-mono text-xs" value="{{ $settings['steadfast_api_key'] ?? '' }}" placeholder="e.g. st_key_xxxxxxxx" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Steadfast Secret Key</label>
            <input name="steadfast_secret_key" type="password" class="inp font-mono text-xs" value="{{ $settings['steadfast_secret_key'] ?? '' }}" placeholder="••••••••••••••••" />
          </div>
        </div>
      </div>

      <!-- Pathao -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="font-bold text-base text-ink flex items-center gap-2">
              <span>🛵</span> Pathao Courier API
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Automated Pathao parcel booking &amp; Hermes API token integration</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="pathao_enabled" value="1" class="sr-only peer" @checked(($settings['pathao_enabled'] ?? '0') === '1') />
            <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></span>
            <span class="ml-2 text-xs font-bold text-gray-700">Enable Pathao</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Environment Mode</label>
            <select name="pathao_env" class="inp text-xs font-bold">
              <option value="production" @selected(($settings['pathao_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['pathao_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Pathao Client ID</label>
            <input name="pathao_client_id" type="text" class="inp font-mono text-xs" value="{{ $settings['pathao_client_id'] ?? '' }}" placeholder="Client ID" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Pathao Client Secret</label>
            <input name="pathao_client_secret" type="password" class="inp font-mono text-xs" value="{{ $settings['pathao_client_secret'] ?? '' }}" placeholder="••••••••" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Pathao Merchant Username (Email)</label>
            <input name="pathao_username" type="text" class="inp text-xs" value="{{ $settings['pathao_username'] ?? '' }}" placeholder="merchant@email.com" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Pathao Merchant Password</label>
            <input name="pathao_password" type="password" class="inp text-xs" value="{{ $settings['pathao_password'] ?? '' }}" placeholder="••••••••" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Pathao Store ID (Numeric)</label>
            <input name="pathao_store_id" type="number" class="inp text-xs font-mono" value="{{ $settings['pathao_store_id'] ?? '' }}" placeholder="e.g. 12345" />
          </div>
        </div>
      </div>

      <!-- RedX -->
      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="font-bold text-base text-ink flex items-center gap-2">
              <span>🔴</span> RedX Courier API
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Automated parcel creation via RedX merchant API token</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="redx_enabled" value="1" class="sr-only peer" @checked(($settings['redx_enabled'] ?? '0') === '1') />
            <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></span>
            <span class="ml-2 text-xs font-bold text-gray-700">Enable RedX</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Environment Mode</label>
            <select name="redx_env" class="inp text-xs font-bold">
              <option value="production" @selected(($settings['redx_env'] ?? 'production') === 'production')>Production Live API</option>
              <option value="sandbox" @selected(($settings['redx_env'] ?? '') === 'sandbox')>Sandbox Test Mode</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="lbl font-bold text-xs text-gray-700">RedX API Access Token</label>
            <input name="redx_api_token" type="password" class="inp font-mono text-xs" value="{{ $settings['redx_api_token'] ?? '' }}" placeholder="Bearer access token..." />
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION: GOOGLE OAUTH SOCIAL LOGIN --}}
  <div id="sec-google" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'google') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Google 1-Click Social Auth Credentials</span>
        <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Google OAuth Settings</button>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="border-b border-gray-100 pb-3">
          <h3 class="font-bold text-base text-ink flex items-center gap-2">
            <span>🔑</span> Google OAuth 2.0 Client Credentials
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Enable 1-Click Sign In with Google on checkout, login, and registration pages</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Google Client ID</label>
            <input name="google_client_id" type="text" class="inp font-mono text-xs" value="{{ $settings['google_client_id'] ?? env('GOOGLE_CLIENT_ID', '') }}" placeholder="e.g. 123456789-xxxx.apps.googleusercontent.com" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Google Client Secret</label>
            <input name="google_client_secret" type="password" class="inp font-mono text-xs" value="{{ $settings['google_client_secret'] ?? env('GOOGLE_CLIENT_SECRET', '') }}" placeholder="GOCSPX-••••••••••••••••" />
          </div>
        </div>

        <div>
          <label class="lbl font-bold text-xs text-gray-700">Google OAuth Redirect URI / Callback URL</label>
          <input name="google_redirect_uri" type="text" class="inp font-mono text-xs" value="{{ $settings['google_redirect_uri'] ?? env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')) }}" placeholder="{{ url('/auth/google/callback') }}" />
          <p class="text-[11px] text-gray-400 mt-1">Copy this exact Authorized redirect URI into your Google Cloud Console Web Client Settings.</p>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 2: MARKETING & TRACKING APIS --}}
  <div id="sec-tracking" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'tracking') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Marketing &amp; Analytics Tracking Pixels</span>
        <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Tracking Settings</button>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="border-b border-gray-100 pb-3">
          <h3 class="font-bold text-base text-ink flex items-center gap-2">
            <span>📈</span> Analytics &amp; Conversion Pixels
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Inject tracking snippets cleanly into storefront pages</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Google Tag Manager (GTM ID)</label>
            <input name="tracking_gtm_id" class="inp font-mono text-xs uppercase" value="{{ $settings['tracking_gtm_id'] ?? '' }}" placeholder="GTM-XXXXXXX" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Google Analytics 4 (GA4 Measurement ID)</label>
            <input name="tracking_ga4_id" class="inp font-mono text-xs uppercase" value="{{ $settings['tracking_ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Meta Pixel ID (Facebook)</label>
            <input name="tracking_meta_pixel_id" class="inp font-mono text-xs" value="{{ $settings['tracking_meta_pixel_id'] ?? '' }}" placeholder="1234567890" />
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- SECTION 3: EMAIL SERVER & OTP --}}
  <div id="sec-mail" class="integration-section hidden space-y-6">
    <form method="POST" action="{{ route('admin.integrations.update', 'mail') }}" class="space-y-6">
      @csrf @method('PUT')

      <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
        <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Email Gateway &amp; Verification OTP</span>
        <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Email Settings</button>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <div>
            <h3 class="font-bold text-base text-ink flex items-center gap-2">
              <span>📧</span> SMTP Mail Server
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Configure transactional emails for orders &amp; customer auth</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="otp_enabled" value="1" class="sr-only peer" @checked(($settings['otp_enabled'] ?? '1') === '1') />
            <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></span>
            <span class="ml-2 text-xs font-bold text-gray-700">Enable OTP Verification</span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Mailer Driver</label>
            <select name="mail_mailer" class="inp text-xs font-bold">
              <option value="smtp" @selected(($settings['mail_mailer'] ?? 'log') === 'smtp')>SMTP Server</option>
              <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log File (Local Testing)</option>
            </select>
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">SMTP Host</label>
            <input name="mail_host" class="inp text-xs" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">SMTP Port</label>
            <input name="mail_port" type="number" class="inp font-mono text-xs" value="{{ $settings['mail_port'] ?? '' }}" placeholder="587" />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">SMTP Username</label>
            <input name="mail_username" class="inp text-xs" value="{{ $settings['mail_username'] ?? '' }}" placeholder="user@domain.com" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">SMTP Password</label>
            <input name="mail_password" type="password" class="inp text-xs" placeholder="••••••••••••" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">Encryption</label>
            <select name="mail_encryption" class="inp text-xs font-bold">
              <option value="tls" @selected(($settings['mail_encryption'] ?? 'tls') === 'tls')>TLS</option>
              <option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option>
              <option value="none" @selected(($settings['mail_encryption'] ?? '') === 'none')>None</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="lbl font-bold text-xs text-gray-700">From Email Address</label>
            <input name="mail_from_address" type="email" class="inp text-xs" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@yourdomain.com" />
          </div>
          <div>
            <label class="lbl font-bold text-xs text-gray-700">From Name</label>
            <input name="mail_from_name" class="inp text-xs" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="My Store" />
          </div>
        </div>
      </div>
    </form>

    <!-- Test Mail Sender -->
    <form method="POST" action="{{ route('admin.integrations.test-mail') }}" class="rounded-2xl border border-gray-200 bg-white p-5 space-y-3 shadow-xs">
      @csrf
      <h4 class="font-bold text-sm text-ink flex items-center gap-2">
        <span>🧪</span> Send Test Email
      </h4>
      <p class="text-xs text-gray-500">Send an instant test email to verify your SMTP server connection.</p>
      <div class="flex items-center gap-3">
        <input name="test_email" type="email" class="inp text-xs max-w-sm" placeholder="your-email@gmail.com" required />
        <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold">Send Test Email</button>
      </div>
    </form>
  </div>
</div>
@endsection
