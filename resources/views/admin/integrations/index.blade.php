@extends('layouts.admin')
@section('title', 'Integrations')
@section('subtitle', 'Courier APIs, Google sign-in, tracking pixels and email.')

@section('content')
@php
  $couriersActive = (($settings['steadfast_enabled'] ?? '0') === '1') || (($settings['pathao_enabled'] ?? '0') === '1') || (($settings['redx_enabled'] ?? '0') === '1');
  $googleActive = !empty($settings['google_client_id'] ?? config('services.google.client_id'));
  $trackingActive = !empty($settings['tracking_gtm_id']) || !empty($settings['tracking_ga4_id']) || !empty($settings['tracking_meta_pixel_id']) || !empty($settings['tracking_tiktok_pixel_id']) || !empty($settings['tracking_google_ads_id']);
  $mailActive = ($settings['mail_mailer'] ?? 'log') === 'smtp';

  $eyeIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  $eyeBtn = 'absolute right-1.5 top-1/2 -translate-y-1/2 h-8 w-8 grid place-items-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100';
  $switchTrack = "relative w-10 h-6 rounded-full bg-gray-200 peer-checked:bg-emerald-600 transition after:content-[''] after:absolute after:top-1 after:left-1 after:w-4 after:h-4 after:rounded-full after:bg-white after:shadow-sm after:transition peer-checked:after:translate-x-4";
  $saveBtn = 'w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5';
  $tabs = [
    'couriers' => ['Courier APIs', $couriersActive],
    'google'   => ['Google login', $googleActive],
    'tracking' => ['Tracking', $trackingActive],
    'mail'     => ['Email & OTP', $mailActive],
  ];
@endphp

<div class="space-y-4 max-w-full">

  {{-- Tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Integration sections">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($tabs as $key => [$label, $on])
        <button type="button" onclick="switchIntegrationTab('{{ $key }}', this)"
                class="integration-tab-btn h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $loop->first ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
                @if($loop->first) style="background: var(--brand-dark);" @endif>
          {{ $label }}
          <span class="h-1.5 w-1.5 rounded-full {{ $on ? 'bg-emerald-500' : 'bg-gray-300' }}" title="{{ $on ? 'Active' : 'Off' }}"></span>
        </button>
      @endforeach
    </div>
  </nav>

  <script>
  function switchIntegrationTab(tabName, btn) {
    document.querySelectorAll('.integration-tab-btn').forEach(b => {
      b.classList.remove('text-white');
      b.classList.add('text-gray-600', 'hover:bg-gray-100');
      b.style.background = '';
    });
    btn.classList.add('text-white');
    btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
    btn.style.background = 'var(--brand-dark)';

    document.querySelectorAll('.integration-section').forEach(sec => {
      if (sec.getAttribute('id') === 'sec-' + tabName) {
        sec.classList.remove('hidden');
      } else {
        sec.classList.add('hidden');
      }
    });
  }

  const EYE_ICON = @json($eyeIcon);
  const EYE_OFF_ICON = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.9 4.2A10 10 0 0 1 12 4c6.5 0 10 7 10 7a17 17 0 0 1-2.2 3.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/></svg>';

  function togglePass(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.type === 'password') {
      el.type = 'text';
      btn.innerHTML = EYE_OFF_ICON;
    } else {
      el.type = 'password';
      btn.innerHTML = EYE_ICON;
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
  <div id="sec-couriers" class="integration-section space-y-4">
    <form method="POST" action="{{ route('admin.integrations.update', 'couriers') }}" class="space-y-4">
      @csrf @method('PUT')

      {{-- Steadfast --}}
      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">Steadfast</h2>
            <p class="text-xs text-gray-500 mt-0.5">Book parcels in one click and sync tracking with Steadfast.</p>
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs text-gray-600 hidden sm:inline">Enabled</span>
            <input type="checkbox" name="steadfast_enabled" value="1" class="sr-only peer" @checked(($settings['steadfast_enabled'] ?? '0') === '1') aria-label="Enable Steadfast" />
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl">API key</label>
            <input name="steadfast_api_key" type="text" class="inp font-mono text-[13px]" value="{{ $settings['steadfast_api_key'] ?? '' }}" placeholder="e.g. st_key_xxxxxxxx" />
          </div>
          <div>
            <label class="lbl">Secret key</label>
            <div class="relative">
              <input id="st_sec" name="steadfast_secret_key" type="password" class="inp font-mono text-[13px] pr-11" value="{{ $settings['steadfast_secret_key'] ?? '' }}" placeholder="••••••••••••••••" />
              <button type="button" onclick="togglePass('st_sec', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
        </div>
      </section>

      {{-- Pathao --}}
      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">Pathao</h2>
            <p class="text-xs text-gray-500 mt-0.5">Book Pathao parcels automatically with your merchant API.</p>
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs text-gray-600 hidden sm:inline">Enabled</span>
            <input type="checkbox" name="pathao_enabled" value="1" class="sr-only peer" @checked(($settings['pathao_enabled'] ?? '0') === '1') aria-label="Enable Pathao" />
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl">Environment</label>
            <select name="pathao_env" class="inp text-[13px] cursor-pointer">
              <option value="production" @selected(($settings['pathao_env'] ?? 'production') === 'production')>Production (live)</option>
              <option value="sandbox" @selected(($settings['pathao_env'] ?? '') === 'sandbox')>Sandbox (test)</option>
            </select>
          </div>
          <div>
            <label class="lbl">Client ID</label>
            <input name="pathao_client_id" type="text" class="inp font-mono text-[13px]" value="{{ $settings['pathao_client_id'] ?? '' }}" placeholder="Client ID" />
          </div>
          <div>
            <label class="lbl">Client secret</label>
            <div class="relative">
              <input id="pt_sec" name="pathao_client_secret" type="password" class="inp font-mono text-[13px] pr-11" value="{{ $settings['pathao_client_secret'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_sec', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
          <div>
            <label class="lbl">Username (email)</label>
            <input name="pathao_username" type="text" class="inp text-[13px]" value="{{ $settings['pathao_username'] ?? '' }}" placeholder="merchant@email.com" />
          </div>
          <div>
            <label class="lbl">Password</label>
            <div class="relative">
              <input id="pt_pass" name="pathao_password" type="password" class="inp text-[13px] pr-11" value="{{ $settings['pathao_password'] ?? '' }}" placeholder="••••••••" />
              <button type="button" onclick="togglePass('pt_pass', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
          <div>
            <label class="lbl">Store ID</label>
            <input name="pathao_store_id" type="number" class="inp font-mono text-[13px]" value="{{ $settings['pathao_store_id'] ?? '' }}" placeholder="e.g. 12345" />
          </div>
        </div>
      </section>

      {{-- RedX --}}
      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">RedX</h2>
            <p class="text-xs text-gray-500 mt-0.5">Create parcels with your RedX merchant API token.</p>
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs text-gray-600 hidden sm:inline">Enabled</span>
            <input type="checkbox" name="redx_enabled" value="1" class="sr-only peer" @checked(($settings['redx_enabled'] ?? '0') === '1') aria-label="Enable RedX" />
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl">Environment</label>
            <select name="redx_env" class="inp text-[13px] cursor-pointer">
              <option value="production" @selected(($settings['redx_env'] ?? 'production') === 'production')>Production (live)</option>
              <option value="sandbox" @selected(($settings['redx_env'] ?? '') === 'sandbox')>Sandbox (test)</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="lbl">API access token</label>
            <div class="relative">
              <input id="rx_token" name="redx_api_token" type="password" class="inp font-mono text-[13px] pr-11" value="{{ $settings['redx_api_token'] ?? '' }}" placeholder="Bearer access token..." />
              <button type="button" onclick="togglePass('rx_token', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
        </div>
      </section>

      {{-- Automatic delivery status updates --}}
      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">Automatic delivery updates</h2>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Booked parcels are checked with the courier once a day at 9 PM. Delivered parcels become <b class="font-medium text-gray-700">Delivered</b> (cash-on-delivery marked paid); returns, holds and part deliveries are listed on Courier Scan.</p>
            @if($lastSync)
              <p class="text-[11px] text-gray-400 mt-1">Last check {{ \Illuminate\Support\Carbon::parse($lastSync['at'])->diffForHumans() }}: {{ $lastSync['checked'] }} checked, {{ $lastSync['delivered'] }} delivered, {{ $lastSync['attention'] }} need attention, {{ $lastSync['failed'] }} failed.</p>
            @endif
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <input type="hidden" name="courier_auto_sync" value="0">
            <span class="text-xs text-gray-600 hidden sm:inline">Auto-update</span>
            <input type="checkbox" name="courier_auto_sync" value="1" class="peer sr-only" @checked(($settings['courier_auto_sync'] ?? '1') !== '0') aria-label="Auto-update">
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>

        <div class="rounded-2xl bg-gray-50 p-3 sm:p-4 text-xs text-gray-600 space-y-2">
          <p class="font-medium text-gray-800">Instant updates (optional)</p>
          <p>Paste these links into the webhook / callback URL setting of each courier's merchant panel. Keep them private; they contain your secret key.</p>
          @foreach($webhooks as $provider => $url)
            <div class="flex items-center gap-2">
              <span class="w-16 sm:w-20 shrink-0 font-medium text-gray-700 capitalize">{{ $provider === 'redx' ? 'RedX' : $provider }}</span>
              <input type="text" readonly value="{{ $url }}" class="flex-1 min-w-0 h-8 text-[11px] font-mono px-3 bg-white border border-gray-200 rounded-full" onclick="this.select()">
              <button type="button" class="h-8 px-3 rounded-full bg-white hover:bg-gray-100 border border-gray-200 text-xs font-medium text-gray-700 shrink-0" onclick="navigator.clipboard.writeText(this.previousElementSibling.value).then(() => { this.textContent = 'Copied'; setTimeout(() => this.textContent = 'Copy', 1500); })">Copy</button>
            </div>
          @endforeach
          <p class="text-gray-500 break-words">Without webhooks the daily check still works. It needs the server's cron job: <code class="font-mono text-[11px] bg-white px-1 rounded break-all">* * * * * cd {{ base_path() }} &amp;&amp; php artisan schedule:run</code></p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-1.5 items-end">
          <div>
            <label class="lbl" for="redx_default_area_id">RedX default area ID</label>
            <input id="redx_default_area_id" name="redx_default_area_id" type="number" min="1" class="inp font-mono text-[13px]" value="{{ $settings['redx_default_area_id'] ?? '' }}" placeholder="e.g. 1" />
          </div>
          <p class="sm:col-span-2 text-[11px] text-gray-500 sm:pb-2.5">Used for RedX bookings when no delivery area matches the customer's address. Leave empty to be asked to fix the address instead.</p>
        </div>
      </section>

      {{-- Customer delivery history (BD Courier) --}}
      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">Customer delivery history (BD Courier)</h2>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">Shows a customer's delivered and returned parcels across Pathao, Steadfast, RedX, Paperfly and others, plus fraud reports from other merchants. Get the API token at <a href="https://bdcourier.com" target="_blank" rel="noopener" class="underline">bdcourier.com</a>.</p>
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <input type="hidden" name="bdcourier_auto_check" value="0">
            <span class="text-xs text-gray-600 hidden sm:inline">Check new COD orders</span>
            <input type="checkbox" name="bdcourier_auto_check" value="1" class="peer sr-only" @checked(($settings['bdcourier_auto_check'] ?? '1') !== '0') aria-label="Check new COD orders">
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>
        <div class="space-y-2">
          <div class="hidden sm:grid grid-cols-12 gap-2 px-1 text-xs text-gray-500">
            <span class="col-span-3">Name</span><span class="col-span-4">API token</span><span class="col-span-2">Searches / day</span><span class="col-span-2">Used today</span><span></span>
          </div>
          <div id="bdcKeys" class="space-y-2">
            @foreach(($bdKeys ?: [['id' => '', 'label' => '', 'token' => '', 'limit' => null, 'used' => 0, 'blocked' => null]]) as $i => $k)
              <div class="bdc-key grid grid-cols-12 gap-2 items-center rounded-2xl sm:rounded-none bg-gray-50 sm:bg-transparent p-2.5 sm:p-0">
                <input type="hidden" name="bdcourier_keys[{{ $i }}][id]" value="{{ $k['id'] }}">
                <input name="bdcourier_keys[{{ $i }}][label]" value="{{ $k['label'] }}" placeholder="e.g. Main account" maxlength="60" class="inp col-span-12 sm:col-span-3 h-9 py-0 text-[13px]" aria-label="Key name">
                <input name="bdcourier_keys[{{ $i }}][token]" value="{{ $k['token'] }}" type="password" autocomplete="off" placeholder="Paste API token" class="inp col-span-12 sm:col-span-4 h-9 py-0 font-mono text-[13px]" aria-label="API token">
                <input name="bdcourier_keys[{{ $i }}][limit]" value="{{ $k['limit'] }}" type="number" min="0" placeholder="No limit" class="inp col-span-5 sm:col-span-2 h-9 py-0 font-mono text-[13px]" aria-label="Searches per day">
                <span class="col-span-5 sm:col-span-2 text-xs {{ $k['blocked'] ? 'text-rose-700' : 'text-gray-600' }}" @if($k['blocked']) title="{{ $k['blocked'] }}" @endif>
                  @if($k['id'] !== '')
                    <b class="font-semibold">{{ $k['used'] }}</b>{{ $k['limit'] !== null ? ' / ' . $k['limit'] : '' }}
                    @if($k['blocked']) · paused today @elseif($k['limit'] !== null && $k['used'] >= $k['limit']) · limit reached @endif
                  @else — @endif
                </span>
                <div class="col-span-2 sm:col-span-1 flex justify-end gap-1">
                  @if($k['id'] !== '')
                    <button type="button" class="bdc-plan h-8 px-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium text-gray-700" data-key="{{ $k['id'] }}" title="Check connection and searches left">Check</button>
                  @endif
                  <button type="button" class="bdc-remove h-8 w-8 shrink-0 rounded-full text-gray-400 hover:text-rose-600 hover:bg-rose-50" aria-label="Remove key">&times;</button>
                </div>
              </div>
            @endforeach
          </div>
          <button type="button" id="bdcAddKey" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium text-gray-800">+ Add another key</button>
        </div>
        <p id="bdcPlanResult" class="text-xs text-gray-600" hidden></p>
        <p class="text-[11px] text-gray-500 leading-relaxed">Keys are used <b class="font-medium text-gray-700">top to bottom</b>: the first key until it reaches its searches per day, then the next. A key BD Courier refuses (out of searches, expired or wrong) is paused until tomorrow. Leave "Searches / day" empty for no limit. Each phone number is looked up once and reused for 7 days; only cash-on-delivery web orders are checked automatically, and customers you've delivered to twice are skipped. Save, then use <b class="font-medium text-gray-700">Check</b> to see a key's plan and searches left.</p>
      </section>
      <script>
        (function () {
          var list = document.getElementById('bdcKeys');
          function renumber() {
            list.querySelectorAll('.bdc-key').forEach(function (row, i) {
              row.querySelectorAll('[name^="bdcourier_keys["]').forEach(function (el) {
                el.name = el.name.replace(/^bdcourier_keys\[\d+\]/, 'bdcourier_keys[' + i + ']');
              });
            });
          }
          document.getElementById('bdcAddKey').addEventListener('click', function () {
            var rows = list.querySelectorAll('.bdc-key');
            if (rows.length >= 10) return;
            var row = rows[rows.length - 1].cloneNode(true);
            row.querySelectorAll('input').forEach(function (el) { el.value = ''; });
            var used = row.querySelector('span'); if (used) { used.textContent = '—'; used.className = 'col-span-5 sm:col-span-2 text-xs text-gray-600'; used.removeAttribute('title'); }
            var check = row.querySelector('.bdc-plan'); if (check) check.remove();
            list.appendChild(row);
            renumber();
            row.querySelector('input:not([type=hidden])').focus();
          });
          list.addEventListener('click', function (e) {
            var rm = e.target.closest('.bdc-remove');
            if (rm) {
              var rows = list.querySelectorAll('.bdc-key');
              var row = rm.closest('.bdc-key');
              if (rows.length > 1) row.remove(); else row.querySelectorAll('input').forEach(function (el) { el.value = ''; });
              renumber();
              return;
            }
            var btn = e.target.closest('.bdc-plan');
            if (!btn) return;
            var out = document.getElementById('bdcPlanResult');
            out.hidden = false; out.className = 'text-xs text-gray-600'; out.textContent = 'Checking…';
            fetch(@json(route('admin.integrations.bdcourier-plan')) + '?key=' + encodeURIComponent(btn.getAttribute('data-key')), { headers: { 'Accept': 'application/json' } })
              .then(function (r) { return r.json(); })
              .then(function (d) {
                var name = btn.closest('.bdc-key').querySelector('[name$="[label]"]').value || 'Key';
                out.className = 'text-xs font-semibold ' + (d.success ? 'text-emerald-700' : 'text-rose-700');
                out.textContent = name + ': ' + (d.success
                  ? 'connected · ' + d.plan + ' plan · ' + d.remaining + ' searches left' + (d.renews ? ' · renews ' + d.renews : '')
                  : 'not connected: ' + d.message);
              })
              .catch(function () { out.className = 'text-xs font-semibold text-rose-700'; out.textContent = 'Could not check right now.'; });
          });
        })();
      </script>

      <div class="flex justify-end">
        <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save courier settings</button>
      </div>
    </form>
  </div>

  {{-- SECTION 2: GOOGLE OAUTH SOCIAL LOGIN --}}
  <div id="sec-google" class="integration-section hidden space-y-4">
    <form method="POST" action="{{ route('admin.integrations.update', 'google') }}" class="space-y-4">
      @csrf @method('PUT')

      <section class="panel p-4 sm:p-5 space-y-4">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900">Google sign-in</h2>
          <p class="text-xs text-gray-500 mt-0.5">Let customers sign in with Google on checkout, login and registration.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl">Client ID</label>
            <input name="google_client_id" type="text" class="inp font-mono text-[13px]" value="{{ $settings['google_client_id'] ?? env('GOOGLE_CLIENT_ID', '') }}" placeholder="e.g. 123456789-xxxx.apps.googleusercontent.com" />
          </div>
          <div>
            <label class="lbl">Client secret</label>
            <div class="relative">
              <input id="gg_sec" name="google_client_secret" type="password" class="inp font-mono text-[13px] pr-11" value="{{ $settings['google_client_secret'] ?? env('GOOGLE_CLIENT_SECRET', '') }}" placeholder="GOCSPX-••••••••••••••••" />
              <button type="button" onclick="togglePass('gg_sec', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
        </div>

        @php
          $redirectUri = $settings['google_redirect_uri'] ?? (config('services.google.redirect') ?: url('/auth/google/callback'));
        @endphp
        <div>
          <label class="lbl">Redirect URI (callback URL)</label>
          <div class="flex items-center gap-2">
            <input name="google_redirect_uri" type="text" class="inp font-mono text-[13px] min-w-0" value="{{ $redirectUri }}" />
            <button type="button" onclick="copyText('{{ $redirectUri }}', this)" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium shrink-0">Copy</button>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Add this exact URL as an authorized redirect URI in your Google Cloud Console web client.</p>
        </div>
      </section>

      <div class="flex justify-end">
        <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save Google settings</button>
      </div>
    </form>
  </div>

  {{-- SECTION 3: MARKETING & TRACKING APIS --}}
  <div id="sec-tracking" class="integration-section hidden space-y-4">
    <form method="POST" action="{{ route('admin.integrations.update', 'tracking') }}" class="space-y-4">
      @csrf @method('PUT')

      @php
        $hasCapiToken = trim((string) ($settings['tracking_meta_capi_token'] ?? '')) !== '';
        $hasGa4Secret = trim((string) ($settings['tracking_ga4_api_secret'] ?? '')) !== '';
        $chip = fn (bool $on, string $label) => '<span class="shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-full ' . ($on ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500') . '">' . e($label) . '</span>';
      @endphp

      {{-- Google: Analytics 4, Tag Manager, Ads, Search Console --}}
      <section class="panel p-4 sm:p-5 space-y-4" data-tracking-card="google">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900 flex flex-wrap items-center gap-x-2 gap-y-1">Google <button type="button" data-guide-open="google" class="inline-flex items-center gap-1 h-6 pl-1.5 pr-2 rounded-full bg-gray-100 hover:bg-gray-200 text-[11px] font-medium text-gray-700 align-middle" aria-label="Setup guide"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.3"/><path d="M12 17h.01"/></svg>Guide</button></h2>
            <p class="text-xs text-gray-500 mt-0.5">Analytics 4, Tag Manager, Google Ads conversions and Search Console.</p>
          </div>
          {!! $chip(! empty($settings['tracking_ga4_id']), ! empty($settings['tracking_ga4_id']) ? 'GA4 on' : 'GA4 off') !!}
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl">GA4 Measurement ID</label>
            <input name="tracking_ga4_id" class="inp font-mono text-[13px] uppercase" value="{{ $settings['tracking_ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" />
            <p class="text-[11px] text-gray-400 mt-1.5">Google Analytics → Admin → Data streams → Measurement ID.</p>
          </div>
          <div>
            <label class="lbl">GA4 API secret <span class="font-normal text-gray-400">(server-side purchases)</span></label>
            <div class="relative">
              <input id="ga4_secret" name="tracking_ga4_api_secret" type="password" autocomplete="off" class="inp font-mono text-[13px] pr-11" placeholder="{{ $hasGa4Secret ? 'Saved. Type to replace' : 'API secret' }}" />
              <button type="button" onclick="togglePass('ga4_secret', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Data streams → Measurement Protocol API secrets.
              @if($hasGa4Secret)<label class="inline-flex items-center gap-1 ml-1 text-rose-600 cursor-pointer"><input type="checkbox" name="tracking_ga4_api_secret_clear" value="1" class="rounded"> Remove</label>@endif
            </p>
          </div>
          <div>
            <label class="lbl">Tag Manager container ID</label>
            <input name="tracking_gtm_id" class="inp font-mono text-[13px] uppercase" value="{{ $settings['tracking_gtm_id'] ?? '' }}" placeholder="GTM-XXXXXXX" />
            <p class="text-[11px] text-gray-400 mt-1.5">Optional, if you manage tags in Google Tag Manager.</p>
          </div>
          <div>
            <label class="lbl">Search Console tag</label>
            <input name="google_site_verification" class="inp font-mono text-[13px]" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Verification code" />
          </div>
          <div>
            <label class="lbl">Google Ads conversion ID</label>
            <input name="tracking_google_ads_id" class="inp font-mono text-[13px] uppercase" value="{{ $settings['tracking_google_ads_id'] ?? '' }}" placeholder="AW-123456789" />
          </div>
          <div>
            <label class="lbl">Google Ads purchase label</label>
            <input name="tracking_google_ads_label" class="inp font-mono text-[13px]" value="{{ $settings['tracking_google_ads_label'] ?? '' }}" placeholder="AbC-D_efG-h123" />
            <p class="text-[11px] text-gray-400 mt-1.5">Counts a conversion when a customer places an order.</p>
          </div>
        </div>
      </section>

      {{-- Meta: Pixel, domain verification, Conversions API --}}
      <section class="panel p-4 sm:p-5 space-y-4" data-tracking-card="meta">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900 flex flex-wrap items-center gap-x-2 gap-y-1">Facebook Pixel &amp; Conversions API <button type="button" data-guide-open="meta" class="inline-flex items-center gap-1 h-6 pl-1.5 pr-2 rounded-full bg-gray-100 hover:bg-gray-200 text-[11px] font-medium text-gray-700 align-middle" aria-label="Setup guide"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.3"/><path d="M12 17h.01"/></svg>Guide</button></h2>
            <p class="text-xs text-gray-500 mt-0.5">Browser pixel plus server-side purchases, counted once.</p>
          </div>
          {!! $chip(($settings['tracking_meta_capi_enabled'] ?? '0') === '1' && $hasCapiToken, ($settings['tracking_meta_capi_enabled'] ?? '0') === '1' && $hasCapiToken ? 'CAPI on' : 'CAPI off') !!}
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl">Meta Pixel / Dataset ID</label>
            <input name="tracking_meta_pixel_id" class="inp font-mono text-[13px]" value="{{ $settings['tracking_meta_pixel_id'] ?? '' }}" placeholder="1234567890" inputmode="numeric" />
            <p class="text-[11px] text-gray-400 mt-1.5">Meta Events Manager → Data sources.</p>
          </div>
          <div>
            <label class="lbl">Domain verification</label>
            <input name="tracking_meta_domain_verification" class="inp font-mono text-[13px]" value="{{ $settings['tracking_meta_domain_verification'] ?? '' }}" placeholder="Code or &lt;meta ... /&gt; tag" />
            <p class="text-[11px] text-gray-400 mt-1.5">Business settings → Brand safety → Domains.</p>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-3.5 sm:p-4 space-y-4">
          <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="tracking_meta_capi_enabled" value="1" class="peer sr-only" @checked(($settings['tracking_meta_capi_enabled'] ?? '0') === '1') />
            <span class="{{ $switchTrack }} shrink-0 mt-0.5"></span>
            <span class="min-w-0">
              <span class="block text-[13px] font-semibold text-gray-900">Conversions API (server-side)</span>
              <span class="block text-xs text-gray-500 mt-0.5">Sends each order from your server to Meta with the customer's hashed phone and email, so sales hidden by ad-blockers and iPhone privacy still count.</span>
            </span>
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="lbl">Access token</label>
              <div class="relative">
                <input id="capi_token" name="tracking_meta_capi_token" type="password" autocomplete="off" class="inp font-mono text-[13px] pr-11" placeholder="{{ $hasCapiToken ? 'Saved. Type to replace' : 'EAAG...' }}" />
                <button type="button" onclick="togglePass('capi_token', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
              </div>
              <p class="text-[11px] text-gray-400 mt-1.5">Events Manager → Settings → Conversions API → Generate access token.
                @if($hasCapiToken)<label class="inline-flex items-center gap-1 ml-1 text-rose-600 cursor-pointer"><input type="checkbox" name="tracking_meta_capi_token_clear" value="1" class="rounded"> Remove</label>@endif
              </p>
            </div>
            <div>
              <label class="lbl">Test event code <span class="font-normal text-gray-400">(optional)</span></label>
              <input name="tracking_meta_capi_test_code" class="inp font-mono text-[13px]" value="{{ $settings['tracking_meta_capi_test_code'] ?? '' }}" placeholder="TEST12345" />
              <p class="text-[11px] text-gray-400 mt-1.5">From Events Manager → Test events. Leave blank when live.</p>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <button type="button" id="testMetaBtn" class="h-9 px-4 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-gray-800 text-[13px] font-medium shrink-0">Test Meta connection</button>
            <p id="testMetaResult" class="text-xs text-gray-500" hidden></p>
          </div>
          <p class="text-[11px] text-gray-400">Save first, then test. The test uses the saved pixel, token and test code.</p>
        </div>
      </section>

      {{-- TikTok --}}
      <section class="panel p-4 sm:p-5 space-y-4" data-tracking-card="tiktok">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900 flex flex-wrap items-center gap-x-2 gap-y-1">TikTok Pixel <button type="button" data-guide-open="tiktok" class="inline-flex items-center gap-1 h-6 pl-1.5 pr-2 rounded-full bg-gray-100 hover:bg-gray-200 text-[11px] font-medium text-gray-700 align-middle" aria-label="Setup guide"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.3"/><path d="M12 17h.01"/></svg>Guide</button></h2>
          <p class="text-xs text-gray-500 mt-0.5">Tracks TikTok Ads views, add to cart, checkout and purchases.</p>
        </div>
        <div class="sm:max-w-sm">
          <label class="lbl">TikTok Pixel ID</label>
          <input name="tracking_tiktok_pixel_id" class="inp font-mono text-[13px] uppercase" value="{{ $settings['tracking_tiktok_pixel_id'] ?? '' }}" placeholder="CXXXXXXXXXXXXXXX" />
          <p class="text-[11px] text-gray-400 mt-1.5">TikTok Ads Manager → Assets → Events.</p>
        </div>
      </section>

      {{-- Custom scripts --}}
      <section class="panel p-4 sm:p-5 space-y-4" data-tracking-card="custom">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900 flex flex-wrap items-center gap-x-2 gap-y-1">Custom scripts <button type="button" data-guide-open="custom" class="inline-flex items-center gap-1 h-6 pl-1.5 pr-2 rounded-full bg-gray-100 hover:bg-gray-200 text-[11px] font-medium text-gray-700 align-middle" aria-label="Setup guide"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.3"/><path d="M12 17h.01"/></svg>Guide</button></h2>
          <p class="text-xs text-gray-500 mt-0.5">Paste other tracking codes (Microsoft Clarity, Hotjar, Snapchat, Pinterest…). They run on every storefront page.</p>
        </div>
        <div>
          <label class="lbl">Inside &lt;head&gt;</label>
          <textarea name="tracking_custom_head" rows="4" class="inp font-mono text-[12px] leading-relaxed" placeholder="&lt;!-- &lt;script&gt; or &lt;meta&gt; tags --&gt;" spellcheck="false">{{ $settings['tracking_custom_head'] ?? '' }}</textarea>
        </div>
        <div>
          <label class="lbl">Right after &lt;body&gt;</label>
          <textarea name="tracking_custom_body" rows="4" class="inp font-mono text-[12px] leading-relaxed" placeholder="&lt;!-- &lt;noscript&gt; or tracking tags --&gt;" spellcheck="false">{{ $settings['tracking_custom_body'] ?? '' }}</textarea>
          <p class="text-[11px] text-gray-400 mt-1.5">Only paste code from a service you trust. It runs for every visitor.</p>
        </div>
      </section>

      <script>
        (function () {
          var btn = document.getElementById('testMetaBtn'), out = document.getElementById('testMetaResult');
          if (!btn) return;
          btn.addEventListener('click', function () {
            out.hidden = false; out.className = 'text-xs text-gray-600'; out.textContent = 'Sending a test event…';
            fetch(@json(route('admin.integrations.test-meta')), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) } })
              .then(function (r) { return r.json(); })
              .then(function (d) { out.className = 'text-xs font-semibold ' + (d.ok ? 'text-emerald-700' : 'text-rose-700'); out.textContent = d.message; })
              .catch(function () { out.className = 'text-xs font-semibold text-rose-700'; out.textContent = 'Could not reach the server.'; });
          });
        })();
      </script>

      <div class="flex justify-end">
        <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save tracking settings</button>
      </div>
    </form>
    @include('admin.integrations.partials.tracking-guides')
  </div>

  {{-- SECTION 4: EMAIL SERVER & OTP --}}
  <div id="sec-mail" class="integration-section hidden space-y-4">
    <form method="POST" action="{{ route('admin.integrations.update', 'mail') }}" class="space-y-4">
      @csrf @method('PUT')

      <section class="panel p-4 sm:p-5 space-y-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 class="text-[15px] font-semibold text-gray-900">Email server (SMTP)</h2>
            <p class="text-xs text-gray-500 mt-0.5">Sends order notifications and customer sign-in codes.</p>
          </div>
          <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
            <span class="text-xs text-gray-600">OTP verification</span>
            <input type="checkbox" name="otp_enabled" value="1" class="sr-only peer" @checked(($settings['otp_enabled'] ?? '1') === '1') />
            <span class="{{ $switchTrack }}"></span>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="lbl">Mailer</label>
            <select name="mail_mailer" class="inp text-[13px] cursor-pointer">
              <option value="smtp" @selected(($settings['mail_mailer'] ?? 'log') === 'smtp')>SMTP server</option>
              <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log file (local testing)</option>
            </select>
          </div>
          <div>
            <label class="lbl">SMTP host</label>
            <input name="mail_host" class="inp text-[13px]" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" />
          </div>
          <div>
            <label class="lbl">SMTP port</label>
            <input name="mail_port" type="number" class="inp font-mono text-[13px]" value="{{ $settings['mail_port'] ?? '' }}" placeholder="587" />
          </div>
          <div>
            <label class="lbl">SMTP username</label>
            <input name="mail_username" class="inp text-[13px]" value="{{ $settings['mail_username'] ?? '' }}" placeholder="user@domain.com" />
          </div>
          <div>
            <label class="lbl">SMTP password</label>
            <div class="relative">
              <input id="smtp_pass" name="mail_password" type="password" class="inp text-[13px] pr-11" placeholder="••••••••••••" />
              <button type="button" onclick="togglePass('smtp_pass', this)" class="{{ $eyeBtn }}" aria-label="Show or hide">{!! $eyeIcon !!}</button>
            </div>
          </div>
          <div>
            <label class="lbl">Encryption</label>
            <select name="mail_encryption" class="inp text-[13px] cursor-pointer">
              <option value="tls" @selected(($settings['mail_encryption'] ?? 'tls') === 'tls')>TLS</option>
              <option value="ssl" @selected(($settings['mail_encryption'] ?? '') === 'ssl')>SSL</option>
              <option value="none" @selected(($settings['mail_encryption'] ?? '') === 'none')>None</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="lbl">From address</label>
            <input name="mail_from_address" type="email" class="inp text-[13px]" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@yourdomain.com" />
          </div>
          <div>
            <label class="lbl">From name</label>
            <input name="mail_from_name" class="inp text-[13px]" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="My Store" />
          </div>
        </div>
      </section>

      <div class="flex justify-end">
        <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save email settings</button>
      </div>
    </form>

    {{-- Test email --}}
    <form method="POST" action="{{ route('admin.integrations.test-mail') }}" class="panel p-4 sm:p-5 space-y-3">
      @csrf
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Send a test email</h2>
        <p class="text-xs text-gray-500 mt-0.5">Check that your SMTP server connection works.</p>
      </div>
      <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
        <input name="test_email" type="email" class="inp text-[13px] sm:max-w-sm" placeholder="your-email@gmail.com" required />
        <button type="submit" class="h-9 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium shrink-0">Send test email</button>
      </div>
    </form>
  </div>

</div>
@endsection
