@extends('layouts.admin')
@section('title', 'Settings')
@section('subtitle', 'Brand, theme colours, homepage, payments and delivery.')

@section('page-actions')
  <button type="button" id="saveAllSettings" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center gap-1.5 disabled:opacity-60" style="background: var(--brand-dark);">Save all changes</button>
@endsection

@php
  $switchTrack = "relative w-10 h-6 rounded-full bg-gray-200 peer-checked:bg-emerald-600 transition after:content-[''] after:absolute after:top-1 after:left-1 after:w-4 after:h-4 after:rounded-full after:bg-white after:shadow-sm after:transition peer-checked:after:translate-x-4";
  $saveBtn = 'section-save-btn w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center disabled:opacity-60';
  $feedback = 'section-feedback hidden text-xs font-medium rounded-xl px-3.5 py-2';
  $presetBtn = 'h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium text-gray-700 inline-flex items-center gap-1.5 transition-colors';
@endphp

@section('content')
<div class="settings-page w-full max-w-4xl space-y-4">

  <div id="saveAllFeedback" class="hidden text-[13px] font-medium rounded-2xl px-4 py-3"></div>

  {{-- Tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Settings sections">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      <button type="button" onclick="switchSettingsTab('brand', this)" class="settings-tab-btn h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium transition-colors text-white" style="background: var(--brand-dark);">Brand &amp; theme</button>
      <button type="button" onclick="switchSettingsTab('homepage', this)" class="settings-tab-btn h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:bg-gray-100">Homepage &amp; SEO</button>
      <button type="button" onclick="switchSettingsTab('payments', this)" class="settings-tab-btn h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:bg-gray-100">Payments &amp; delivery</button>
      <button type="button" onclick="switchSettingsTab('all', this)" class="settings-tab-btn h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium transition-colors text-gray-600 hover:bg-gray-100">All sections</button>
      <a href="{{ route('admin.integrations.index') }}" class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium text-gray-600 hover:bg-gray-100 inline-flex items-center gap-1">
        Integrations
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
      </a>
    </div>
  </nav>

  <script>
  function switchSettingsTab(tabName, btn) {
    document.querySelectorAll('.settings-tab-btn').forEach(b => {
      b.classList.remove('text-white');
      b.classList.add('text-gray-600', 'hover:bg-gray-100');
      b.style.background = '';
    });
    btn.classList.add('text-white');
    btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
    btn.style.background = 'var(--brand-dark)';

    const forms = document.querySelectorAll('.settings-section-form');
    forms.forEach(f => {
      const sec = f.getAttribute('data-section');
      if (tabName === 'all') {
        f.classList.remove('hidden');
      } else if (tabName === 'brand' && sec === 'brand') {
        f.classList.remove('hidden');
      } else if (tabName === 'homepage' && (sec === 'homepage' || sec === 'seo' || sec === 'tracking')) {
        f.classList.remove('hidden');
      } else if (tabName === 'payments' && (sec === 'payments' || sec === 'shipping')) {
        f.classList.remove('hidden');
      } else if (tabName === 'couriers' && sec === 'couriers') {
        f.classList.remove('hidden');
      } else if (tabName === 'system' && (sec === 'mail' || sec === 'invoice')) {
        f.classList.remove('hidden');
      } else {
        f.classList.add('hidden');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const firstBtn = document.querySelector('.settings-tab-btn');
    if (firstBtn) switchSettingsTab('brand', firstBtn);
  });
  </script>

  {{-- TAB 1: BRAND & THEME --}}
  <form method="POST" action="{{ route('admin.settings.update-section', 'brand') }}" enctype="multipart/form-data" class="settings-section-form space-y-4" data-section="brand">
    @csrf @method('PUT')

    {{-- Store identity --}}
    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Store identity</h2>
        <p class="text-xs text-gray-500 mt-0.5">Store name, tagline and logos.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="lbl">Site name <span class="text-rose-500">*</span></label>
          <input name="site_name" class="inp text-[13px]" value="{{ $settings['site_name'] ?? '' }}" required />
        </div>
        <div>
          <label class="lbl">Tagline</label>
          <input name="tagline" class="inp text-[13px]" value="{{ $settings['tagline'] ?? '' }}" placeholder="Your store slogan" />
        </div>
      </div>
      <div>
        <label class="lbl">Footer copyright text</label>
        <textarea name="footer_text" rows="2" class="inp text-[13px]">{{ $settings['footer_text'] ?? '' }}</textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-2xl bg-gray-50 p-3 flex items-center gap-3">
          <div class="h-12 w-12 rounded-xl bg-white grid place-items-center overflow-hidden shrink-0 ring-1 ring-gray-100" data-logo-preview>
            <img src="{{ logo_url() }}" class="h-full w-full object-contain p-1.5" alt="Logo">
          </div>
          <div class="min-w-0 flex-1">
            <label class="lbl !mb-1">Logo</label>
            <input name="logo_file" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="w-full text-xs text-gray-500 file:mr-2 file:h-7 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-white file:text-gray-800 hover:file:bg-gray-100 cursor-pointer" />
            <p class="text-[11px] text-gray-400 mt-1">PNG, JPG, SVG or WEBP, up to 2 MB</p>
          </div>
        </div>

        <div class="rounded-2xl bg-gray-50 p-3 flex items-center gap-3">
          <div class="h-12 w-12 rounded-xl bg-white grid place-items-center overflow-hidden shrink-0 ring-1 ring-gray-100">
            <img src="{{ favicon_url() }}" class="h-7 w-7 object-contain" alt="Favicon" data-favicon-preview>
          </div>
          <div class="min-w-0 flex-1">
            <label class="lbl !mb-1">Favicon</label>
            <input name="favicon_file" type="file" accept="image/png,image/x-icon,image/svg+xml,image/webp,image/jpeg" class="w-full text-xs text-gray-500 file:mr-2 file:h-7 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-white file:text-gray-800 hover:file:bg-gray-100 cursor-pointer" />
            <p class="text-[11px] text-gray-400 mt-1">ICO, PNG, SVG or WEBP, up to 1 MB</p>
          </div>
        </div>
      </div>
    </section>

    {{-- Theme colours --}}
    <section class="panel p-4 sm:p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-[15px] font-semibold text-gray-900">Theme colours</h2>
          <p class="text-xs text-gray-500 mt-0.5">Three core colours; hover states and tints follow automatically.</p>
        </div>
        <button type="button" onclick="setThemeColors('#8B5A2B', '#2B1D14', '#FAF7F2')" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium text-gray-700 inline-flex items-center gap-1.5 shrink-0">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
          Reset
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach([
          ['primary', 'theme_primary_color', '#16A34A', 'Primary accent', 'Buttons, badges, pills'],
          ['dark', 'theme_dark_color', '#1C1917', 'Dark', 'Headings, titles, top bar'],
          ['surface', 'theme_surface_color', '#FAFAF5', 'Surface', 'Page and section backgrounds'],
        ] as [$cKey, $cName, $cDefault, $cLabel, $cHint])
          <div class="rounded-2xl bg-gray-50 p-3">
            <label class="lbl">{{ $cLabel }}</label>
            <div class="flex items-center gap-2">
              <input type="color" id="{{ $cKey }}Picker" value="{{ $settings[$cName] ?? $cDefault }}" class="h-9 w-11 rounded-lg border border-gray-200 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('{{ $cKey }}Input').value = this.value" />
              <input type="text" id="{{ $cKey }}Input" name="{{ $cName }}" class="inp h-9 py-0 font-mono text-[13px] uppercase" value="{{ $settings[$cName] ?? $cDefault }}" placeholder="{{ $cDefault }}" oninput="document.getElementById('{{ $cKey }}Picker').value = this.value" />
            </div>
            <p class="text-[11px] text-gray-500 mt-1.5">{{ $cHint }}</p>
          </div>
        @endforeach
      </div>

      <div>
        <p class="text-xs text-gray-500 mb-2">Presets</p>
        <div class="flex flex-wrap gap-1.5">
          @foreach([
            ['#8B5A2B', '#2B1D14', '#FAF7F2', 'Leather brown'],
            ['#16A34A', '#1C1917', '#FAFAF5', 'Eco green'],
            ['#D97706', '#064E3B', '#FFFDF9', 'Harvest gold'],
            ['#059669', '#111827', '#F4F4F5', 'Emerald'],
            ['#E8751B', '#353535', '#F8FAFC', 'Sunset orange'],
            ['#2563EB', '#0F172A', '#F8FAFC', 'Sapphire'],
          ] as [$pp, $pd, $ps, $pl])
            <button type="button" onclick="setThemeColors('{{ $pp }}', '{{ $pd }}', '{{ $ps }}')" class="{{ $presetBtn }}">
              <span class="h-3 w-3 rounded-full" style="background: {{ $pp }};"></span>{{ $pl }}
            </button>
          @endforeach
        </div>
      </div>
    </section>

    {{-- Contact & social --}}
    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Contact and social links</h2>
        <p class="text-xs text-gray-500 mt-0.5">Phone, email, address, hours and social channels.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="lbl">Contact phone</label>
          <input name="contact_phone" class="inp text-[13px]" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+8801700000000" />
        </div>
        <div>
          <label class="lbl">WhatsApp number</label>
          <input name="whatsapp_number" class="inp text-[13px]" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="e.g. 01700000000" />
        </div>
        <div>
          <label class="lbl">Contact email</label>
          <input name="contact_email" type="email" class="inp text-[13px]" value="{{ $settings['contact_email'] ?? '' }}" placeholder="support@store.com" />
        </div>
      </div>
      <div>
        <label class="lbl">Address</label>
        <input name="contact_address" class="inp text-[13px]" value="{{ $settings['contact_address'] ?? '' }}" placeholder="House 12, Road 5, Dhaka" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="lbl">Contact hours</label>
          <input name="contact_hours" class="inp text-[13px]" value="{{ $settings['contact_hours'] ?? '' }}" placeholder="Sat–Thu, 9am – 9pm" />
        </div>
        <div>
          <label class="lbl">Contact page title</label>
          <input name="contact_title" class="inp text-[13px]" value="{{ $settings['contact_title'] ?? '' }}" placeholder="Get in touch" />
        </div>
        <div>
          <label class="lbl">Search placeholder</label>
          <input name="search_placeholder" class="inp text-[13px]" value="{{ $settings['search_placeholder'] ?? '' }}" placeholder="Search Product..." />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div><label class="lbl">Facebook URL</label><input name="facebook_url" class="inp text-[13px]" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/..." /></div>
        <div><label class="lbl">Messenger username or link</label><input name="messenger_url" class="inp text-[13px]" value="{{ $settings['messenger_url'] ?? '' }}" placeholder="e.g. solebd or https://m.me/..." /></div>
        <div><label class="lbl">Instagram URL</label><input name="instagram_url" class="inp text-[13px]" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/..." /></div>
        <div><label class="lbl">Twitter URL</label><input name="twitter_url" class="inp text-[13px]" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://twitter.com/..." /></div>
      </div>
    </section>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
      <div class="{{ $feedback }}"></div>
      <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save brand settings</button>
    </div>

    <script>
    function setThemeColors(primary, dark, surface) {
      document.getElementById('primaryPicker').value = primary;
      document.getElementById('primaryInput').value = primary;
      document.getElementById('darkPicker').value = dark;
      document.getElementById('darkInput').value = dark;
      document.getElementById('surfacePicker').value = surface;
      document.getElementById('surfaceInput').value = surface;
    }
    </script>
  </form>

  {{-- TAB 2: HOMEPAGE --}}
  <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}" class="settings-section-form space-y-4" data-section="homepage">
    @csrf @method('PUT')

    <div class="panel px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <p class="text-[13px] text-gray-600">The top headline bar is managed in <span class="font-medium text-gray-900">Marketing → News ticker</span>.</p>
      <a href="{{ route('admin.news-ticker.index') }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-medium text-gray-800 inline-flex items-center self-start sm:self-auto shrink-0">Open news ticker</a>
    </div>

    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Page subtitles</h2>
        <p class="text-xs text-gray-500 mt-0.5">Shop page subtitle and delivery time note.</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl">Shop page subtitle</label><input name="shop_subtitle" class="inp text-[13px]" value="{{ $settings['shop_subtitle'] ?? '' }}" placeholder="Explore our full collection" /></div>
        <div><label class="lbl">Delivery time note</label><input name="delivery_eta_text" class="inp text-[13px]" value="{{ $settings['delivery_eta_text'] ?? '' }}" placeholder="Estimated delivery in 2–3 days" /></div>
      </div>
    </section>

    <section class="panel p-4 sm:p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-[15px] font-semibold text-gray-900">Featured brands section</h2>
          <p class="text-xs text-gray-500 mt-0.5">Show or hide the brands carousel on the homepage.</p>
        </div>
        <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
          <span class="text-xs text-gray-600 hidden sm:inline">Show</span>
          <input type="checkbox" name="show_featured_brands" value="1" @checked(($settings['show_featured_brands'] ?? '1') === '1') class="sr-only peer" aria-label="Show featured brands">
          <span class="{{ $switchTrack }}"></span>
        </label>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="lbl">Title</label>
          <input name="home_featured_brands_title" class="inp text-[13px]" value="{{ $settings['home_featured_brands_title'] ?? 'Featured Brands' }}" placeholder="Featured Brands" />
        </div>
        <div>
          <label class="lbl">Subtitle</label>
          <input name="home_featured_brands_subtitle" class="inp text-[13px]" value="{{ $settings['home_featured_brands_subtitle'] ?? 'Shop authentic products directly from leading brands' }}" placeholder="Shop authentic products directly from leading brands" />
        </div>
      </div>
    </section>

    <section class="panel p-4 sm:p-5 space-y-4">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-[15px] font-semibold text-gray-900">Customer feedback section</h2>
          <p class="text-xs text-gray-500 mt-0.5">Verified customer reviews on the homepage. <a href="{{ route('admin.reviews.index') }}" class="underline hover:text-gray-800">Manage reviews</a></p>
        </div>
        <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
          <span class="text-xs text-gray-600 hidden sm:inline">Show</span>
          <input type="checkbox" name="show_home_reviews" value="1" @checked(($settings['show_home_reviews'] ?? '1') === '1') class="sr-only peer" aria-label="Show customer feedback">
          <span class="{{ $switchTrack }}"></span>
        </label>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="lbl">Title</label>
          <input name="home_reviews_title" class="inp text-[13px]" value="{{ $settings['home_reviews_title'] ?? 'Customer Feedback' }}" placeholder="Customer Feedback" />
        </div>
        <div>
          <label class="lbl">Subtitle</label>
          <input name="home_reviews_subtitle" class="inp text-[13px]" value="{{ $settings['home_reviews_subtitle'] ?? 'What our happy customers say about our authentic products and service' }}" placeholder="What our happy customers say about our authentic products and service" />
        </div>
      </div>
    </section>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
      <div class="{{ $feedback }}"></div>
      <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save homepage settings</button>
    </div>
  </form>

  {{-- TAB 2 (PART 2): SEO --}}
  <form method="POST" action="{{ route('admin.settings.update-section', 'seo') }}" class="settings-section-form space-y-4" data-section="seo">
    @csrf @method('PUT')

    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Search appearance (SEO)</h2>
        <p class="text-xs text-gray-500 mt-0.5">The title and description Google shows for your store.</p>
      </div>

      {{-- Live Google Search Preview --}}
      <div class="rounded-2xl bg-gray-50 p-3 sm:p-4">
        <p class="text-xs text-gray-500 mb-2">Google preview</p>
        <div class="bg-white p-3 rounded-xl space-y-1 min-w-0">
          <p class="text-xs text-gray-600 truncate flex items-center gap-1.5">
            <span class="w-4 h-4 rounded-full bg-gray-100 inline-grid place-items-center text-[9px] font-semibold text-gray-700 shrink-0">M</span>
            <span class="truncate">{{ config('app.url') }}</span>
          </p>
          <p id="globalSeoPreviewTitle" class="text-[15px] text-blue-700 truncate">
            {{ $settings['default_meta_title'] ?? ($settings['site_name'] ?? 'Marty') }}
          </p>
          <p id="globalSeoPreviewDesc" class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
            {{ $settings['default_meta_description'] ?? 'Buy authentic gadgets, watches, and accessories online with fast delivery.' }}
          </p>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label class="lbl">Website title <span class="text-rose-500">*</span></label>
          <span id="titleCharCount" class="text-[11px] text-stone-400 font-mono">0 / 60</span>
        </div>
        <input type="text" id="globalMetaTitleInput" name="default_meta_title" maxlength="180" class="inp text-[13px]" value="{{ $settings['default_meta_title'] ?? '' }}" placeholder="e.g. Marty — Smartwatches, Shoes & Smart Gadgets Store in Bangladesh" />
        <p class="text-[11px] text-gray-500 mt-1">Aim for 50–60 characters. Shown as the headline in Google and in browser tabs.</p>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label class="lbl">Meta description <span class="text-rose-500">*</span></label>
          <span id="descCharCount" class="text-[11px] text-stone-400 font-mono">0 / 160</span>
        </div>
        <textarea id="globalMetaDescInput" name="default_meta_description" rows="3" maxlength="400" class="inp text-[13px]" placeholder="Short description of your store that appears under the title in Google search results...">{{ $settings['default_meta_description'] ?? '' }}</textarea>
        <p class="text-[11px] text-gray-500 mt-1">Aim for 150–160 characters describing what your store offers.</p>
      </div>

      <div>
        <label class="lbl">Meta keywords</label>
        <input type="text" name="default_meta_keywords" maxlength="400" class="inp text-[13px]" value="{{ $settings['default_meta_keywords'] ?? '' }}" placeholder="e.g. smartwatches, apple watch, sneakers, gadgets bd" />
        <p class="text-[11px] text-gray-500 mt-1">Comma-separated.</p>
      </div>
    </section>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
      <div class="{{ $feedback }}"></div>
      <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save SEO settings</button>
    </div>
  </form>

  {{-- TAB 3: PAYMENTS --}}
  <form method="POST" action="{{ route('admin.settings.update-section', 'payments') }}" class="settings-section-form space-y-4" data-section="payments">
    @csrf @method('PUT')

    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Payment methods</h2>
        <p class="text-xs text-gray-500 mt-0.5">Choose what customers can pay with at checkout.</p>
      </div>

      @php
        $payCodOn = ($settings['pay_cod_enabled'] ?? '1') === '1';
        $payBkashOn = ($settings['pay_bkash_enabled'] ?? '1') === '1';
        $payNagadOn = ($settings['pay_nagad_enabled'] ?? '1') === '1';
        $payRocketOn = ($settings['pay_rocket_enabled'] ?? '1') === '1';
        $payRow = 'flex items-center justify-between gap-4 px-3.5 py-3 cursor-pointer hover:bg-gray-50 transition-colors';
      @endphp
      <div class="rounded-2xl ring-1 ring-gray-100 divide-y divide-gray-100 overflow-hidden">
        <label class="{{ $payRow }}">
          <span class="text-[13px] font-medium text-gray-900">Cash on delivery</span>
          <span class="shrink-0 inline-flex">
            <input type="checkbox" name="pay_cod_enabled" value="1" class="peer sr-only" @checked($payCodOn)>
            <span class="{{ $switchTrack }}"></span>
          </span>
        </label>

        <label class="{{ $payRow }} pl-6 sm:pl-8 bg-gray-50/60">
          <span class="min-w-0">
            <span class="text-[13px] font-medium text-gray-900 block">Auto-confirm safe COD orders</span>
            <span class="text-[11px] text-gray-500 block mt-0.5 leading-relaxed">Confirms a cash-on-delivery order straight away when the customer has earlier delivered orders here (and no returns) or a good courier history (80%+ delivered, no fraud reports). New, risky or blacklisted customers stay <b class="font-medium text-gray-700">Pending</b> for you to call. The payment stays pending until delivery.</span>
          </span>
          <span class="shrink-0 inline-flex">
            <input type="checkbox" name="cod_auto_confirm" value="1" class="peer sr-only" @checked(($settings['cod_auto_confirm'] ?? '0') === '1')>
            <span class="{{ $switchTrack }}"></span>
          </span>
        </label>

        @foreach([
          ['pay_bkash_enabled', 'bKash', $payBkashOn, 'bg-pink-500'],
          ['pay_nagad_enabled', 'Nagad', $payNagadOn, 'bg-orange-500'],
          ['pay_rocket_enabled', 'Rocket', $payRocketOn, 'bg-purple-500'],
        ] as [$pName, $pLabel, $pOn, $pDot])
          <label class="{{ $payRow }}">
            <span class="text-[13px] font-medium text-gray-900 inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full {{ $pDot }}"></span>{{ $pLabel }}</span>
            <span class="shrink-0 inline-flex">
              <input type="checkbox" name="{{ $pName }}" value="1" class="peer sr-only" @checked($pOn)>
              <span class="{{ $switchTrack }}"></span>
            </span>
          </label>
        @endforeach
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach(['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket'] as $w => $wName)
          @php $wType = $settings[$w . '_account_type'] ?? 'personal'; @endphp
          <div class="rounded-2xl bg-gray-50 p-3 space-y-3">
            <div>
              <label class="lbl">{{ $wName }} number</label>
              <input name="{{ $w }}_number" class="inp text-[13px]" value="{{ $settings[$w . '_number'] ?? '' }}" placeholder="01700000000" />
            </div>
            <div>
              <label class="lbl">Account type</label>
              <select name="{{ $w }}_account_type" class="inp text-[13px]">
                @foreach(\App\Support\PaymentInstructions::TYPES as $tKey => $tLabel)
                  <option value="{{ $tKey }}" @selected($wType === $tKey)>{{ $tLabel }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endforeach
      </div>
      <p class="text-[11px] text-gray-500">The account type changes the steps customers see at checkout: <b class="font-medium text-gray-700">Personal</b> → “Send Money”, <b class="font-medium text-gray-700">Merchant</b> → “Payment / Merchant Pay” with their mobile number as reference, <b class="font-medium text-gray-700">Agent</b> → “Cash Out”.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
        <div>
          <label class="lbl">Order help hotline</label>
          <input name="order_hotline" class="inp text-[13px]" value="{{ $settings['order_hotline'] ?? '' }}" placeholder="{{ $settings['contact_phone'] ?? '01700000000' }}" />
          <p class="text-[11px] text-gray-500 mt-1">Shown at checkout as “Need help? Call …”. Empty = your contact phone ({{ $settings['contact_phone'] ?? 'not set' }}).</p>
        </div>
        <div>
          <label class="lbl">Order ID prefix</label>
          <input name="order_number_prefix" maxlength="6" class="inp text-[13px] uppercase sm:max-w-[10rem]" value="{{ $settings['order_number_prefix'] ?? 'VB' }}" placeholder="VB" />
          <p class="text-[11px] text-gray-500 mt-1">Letters and numbers, up to 6. New orders look like <b class="font-mono font-medium text-gray-700">{{ \App\Support\OrderNumber::prefix() }}-482913</b> (POS: <b class="font-mono font-medium text-gray-700">{{ \App\Support\OrderNumber::prefix() }}-P-482913</b>). Existing orders keep their numbers.</p>
        </div>
      </div>
    </section>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
      <div class="{{ $feedback }}"></div>
      <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save payment settings</button>
    </div>
  </form>

  {{-- SHIPPING --}}
  <form method="POST" action="{{ route('admin.settings.update-section', 'shipping') }}" class="settings-section-form space-y-4" data-section="shipping">
    @csrf @method('PUT')

    <section class="panel p-4 sm:p-5 space-y-4">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Delivery charges and tax</h2>
        <p class="text-xs text-gray-500 mt-0.5">Delivery fees inside and outside your main zone, and VAT.</p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="lbl">Inside zone fee (৳)</label><input name="shipping_inside_dhaka" type="number" step="0.01" class="inp text-[13px]" value="{{ $settings['shipping_inside_dhaka'] ?? '' }}" required /></div>
        <div><label class="lbl">Outside zone fee (৳)</label><input name="shipping_outside_dhaka" type="number" step="0.01" class="inp text-[13px]" value="{{ $settings['shipping_outside_dhaka'] ?? '' }}" required /></div>
        <div><label class="lbl">VAT / tax rate (%)</label><input name="tax_percent" type="number" step="0.01" class="inp text-[13px]" value="{{ $settings['tax_percent'] ?? '' }}" required /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl">Inside zone label</label><input name="shipping_inside_label" class="inp text-[13px]" value="{{ $settings['shipping_inside_label'] ?? '' }}" placeholder="Inside Dhaka" /></div>
        <div><label class="lbl">Outside zone label</label><input name="shipping_outside_label" class="inp text-[13px]" value="{{ $settings['shipping_outside_label'] ?? '' }}" placeholder="Outside Dhaka" /></div>
      </div>
    </section>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
      <div class="{{ $feedback }}"></div>
      <button type="submit" class="{{ $saveBtn }}" style="background: var(--brand-dark);">Save delivery settings</button>
    </div>
  </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const saveAllBtn = document.getElementById('saveAllSettings');
  const saveAllFeedback = document.getElementById('saveAllFeedback');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  function showAllFeedback(ok, message) {
    if (!saveAllFeedback) return;
    saveAllFeedback.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'bg-rose-50', 'text-rose-800');
    if (ok) {
      saveAllFeedback.classList.add('bg-emerald-50', 'text-emerald-800');
    } else {
      saveAllFeedback.classList.add('bg-rose-50', 'text-rose-800');
    }
    saveAllFeedback.innerHTML = message;
    saveAllFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  // Handle individual section form submission with AJAX
  document.querySelectorAll('.settings-section-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const saveBtn = form.querySelector('.section-save-btn');
      const origText = saveBtn ? saveBtn.innerText : 'Save';
      const feedback = form.querySelector('.section-feedback');

      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';
      }

      const formData = new FormData(form);
      fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
        credentials: 'same-origin',
      })
      .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
      .then(({ ok, data }) => {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerText = origText;
        }
        if (!ok) {
          const errs = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Save failed.');
          if (feedback) {
            feedback.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800');
            feedback.classList.add('bg-rose-50', 'text-rose-800');
            feedback.innerHTML = errs;
          }
        } else {
          if (feedback) {
            feedback.classList.remove('hidden', 'bg-rose-50', 'text-rose-800');
            feedback.classList.add('bg-emerald-50', 'text-emerald-800');
            feedback.innerHTML = (data.message || 'Section saved successfully.');
          }
          showAllFeedback(true, data.message || 'Section saved successfully.');
        }
      })
      .catch(err => {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerText = origText;
        }
        if (feedback) {
          feedback.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800');
          feedback.classList.add('bg-rose-50', 'text-rose-800');
          feedback.innerHTML = 'Network error while saving section.';
        }
      });
    });
  });

  // Handle Save All Settings Button
  if (saveAllBtn) {
    saveAllBtn.addEventListener('click', async function () {
      const origText = saveAllBtn.innerText;
      saveAllBtn.disabled = true;
      saveAllBtn.innerText = 'Saving...';
      showAllFeedback(true, 'Saving all store settings sections...');

      const forms = Array.from(document.querySelectorAll('.settings-section-form'));
      let hasError = false;
      let errorMessages = [];

      for (const form of forms) {
        const formData = new FormData(form);
        try {
          const res = await fetch(form.action, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
            credentials: 'same-origin',
          });
          const data = await res.json();
          if (!res.ok) {
            hasError = true;
            const errs = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Error');
            errorMessages.push(`[${form.getAttribute('data-section') || 'Section'}]: ${errs}`);
          }
        } catch (err) {
          hasError = true;
          errorMessages.push(`[${form.getAttribute('data-section') || 'Section'}]: Network error`);
        }
      }

      saveAllBtn.disabled = false;
      saveAllBtn.innerText = origText;

      if (hasError) {
        showAllFeedback(false, 'Some sections could not be saved:<br>' + errorMessages.join('<br>'));
      } else {
        showAllFeedback(true, 'All store settings sections saved successfully!');
      }
    });
  }
  // Live update SEO Google Search Preview & Character Counters
  const seoTitleInput = document.getElementById('globalMetaTitleInput');
  const seoDescInput = document.getElementById('globalMetaDescInput');
  const seoPreviewTitle = document.getElementById('globalSeoPreviewTitle');
  const seoPreviewDesc = document.getElementById('globalSeoPreviewDesc');
  const titleCharCount = document.getElementById('titleCharCount');
  const descCharCount = document.getElementById('descCharCount');

  function updateSeoPreview() {
    if (seoTitleInput && seoPreviewTitle && titleCharCount) {
      const val = seoTitleInput.value.trim();
      seoPreviewTitle.textContent = val || '{{ $settings['site_name'] ?? 'Marty' }} — Smartwatches &amp; Gadgets';
      titleCharCount.textContent = `${seoTitleInput.value.length} / 60`;
      titleCharCount.className = (seoTitleInput.value.length > 60)
        ? 'text-[11px] text-amber-600 font-mono font-bold'
        : 'text-[11px] text-stone-400 font-mono';
    }
    if (seoDescInput && seoPreviewDesc && descCharCount) {
      const val = seoDescInput.value.trim();
      seoPreviewDesc.textContent = val || 'Buy authentic gadgets, smartwatches, shoes, and accessories online with warranty and fast delivery.';
      descCharCount.textContent = `${seoDescInput.value.length} / 160`;
      descCharCount.className = (seoDescInput.value.length > 160)
        ? 'text-[11px] text-amber-600 font-mono font-bold'
        : 'text-[11px] text-stone-400 font-mono';
    }
  }

  if (seoTitleInput) seoTitleInput.addEventListener('input', updateSeoPreview);
  if (seoDescInput) seoDescInput.addEventListener('input', updateSeoPreview);
  updateSeoPreview();
});
</script>
@endpush
