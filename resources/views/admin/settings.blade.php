@extends('layouts.admin')
@section('title', 'Store Settings & Customization')

@section('content')
<div class="settings-page w-full max-w-4xl mx-auto space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>⚙️ Store Settings &amp; Customization</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Configure brand identity, theme colors, payment gateways, delivery fees, and automated emails</p>
    </div>
    <button type="button" id="saveAllSettings" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">
      💾 Save All Changes
    </button>
  </div>

  <div id="saveAllFeedback" class="hidden text-xs sm:text-sm font-extrabold rounded-2xl px-4 py-3 shadow-2xs"></div>

  {{-- Horizontal Scrollable Category Navigation Tabs --}}
  <div class="flex items-center gap-2 border-b border-stone-200 pb-3 overflow-x-auto no-scrollbar whitespace-nowrap">
    <button type="button" onclick="switchSettingsTab('brand', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-2xs transition shrink-0 cursor-pointer">🎨 Brand &amp; Theme</button>
    <button type="button" onclick="switchSettingsTab('homepage', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">🏠 Homepage &amp; SEO</button>
    <button type="button" onclick="switchSettingsTab('payments', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">💳 Payments &amp; Delivery</button>
    <button type="button" onclick="switchSettingsTab('system', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">⚙️ Mail &amp; Invoice</button>
    <a href="{{ route('admin.integrations.index') }}" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200 transition shrink-0 cursor-pointer flex items-center gap-1">⚡ API Integrations &rarr;</a>
    <button type="button" onclick="switchSettingsTab('all', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer">📄 View All Sections</button>
  </div>

  <script>
  function switchSettingsTab(tabName, btn) {
    document.querySelectorAll('.settings-tab-btn').forEach(b => {
      b.className = 'settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-stone-700 hover:bg-stone-100 border border-stone-200 transition shrink-0 cursor-pointer';
    });
    btn.className = 'settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-2xs transition shrink-0 cursor-pointer';

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

  <!-- TAB 1: BRAND & THEME -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'brand') }}" enctype="multipart/form-data" class="settings-section-form space-y-6" data-section="brand">
    @csrf @method('PUT')
    
    <!-- Section Header & Action -->
    <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
      <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Section: Brand &amp; Identity</span>
      <button type="submit" class="section-save-btn px-4 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-2xs">Save Section</button>
    </div>
    <div class="section-feedback hidden text-xs font-bold rounded-xl px-4 py-2"></div>

    <!-- Sub-Card 1: Brand Identity & Logos -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <div class="border-b border-stone-100 pb-3">
        <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
          <span>🎨</span> Store Identity &amp; Logos
        </h3>
        <p class="text-xs text-stone-500 mt-0.5">Basic store name, taglines, and logo uploads</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-extrabold text-stone-800 block mb-1">Site Name <span class="text-rose-500">*</span></label>
          <input name="site_name" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ $settings['site_name'] ?? '' }}" required />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Tagline / Slogan</label>
          <input name="tagline" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ $settings['tagline'] ?? '' }}" placeholder="Your store slogan" />
        </div>
      </div>
      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1">Footer Copyright Text</label>
        <textarea name="footer_text" rows="2" class="w-full text-xs font-medium px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs">{{ $settings['footer_text'] ?? '' }}</textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-3 border-t border-stone-100">
        <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">Store Main Logo</label>
          <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="h-14 w-14 rounded-xl bg-white flex items-center justify-center overflow-hidden shrink-0 border border-stone-200 shadow-2xs" data-logo-preview>
              <img src="{{ logo_url() }}" class="h-full w-full object-contain p-1.5" alt="Logo">
            </div>
            <div class="min-w-0 flex-1 w-full">
              <input name="logo_file" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="text-xs text-stone-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer w-full" />
              <p class="text-[10px] text-stone-400 mt-1">PNG, JPG, SVG or WEBP (Max 2MB)</p>
            </div>
          </div>
        </div>

        <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">Favicon / Browser Icon</label>
          <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="h-14 w-14 rounded-xl bg-white flex items-center justify-center overflow-hidden shrink-0 border border-stone-200 shadow-2xs">
              <img src="{{ favicon_url() }}" class="h-8 w-8 object-contain" alt="Favicon" data-favicon-preview>
            </div>
            <div class="min-w-0 flex-1 w-full">
              <input name="favicon_file" type="file" accept="image/png,image/x-icon,image/svg+xml,image/webp,image/jpeg" class="text-xs text-stone-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer w-full" />
              <p class="text-[10px] text-stone-400 mt-1">ICO, PNG, SVG or WEBP (Max 1MB)</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sub-Card 2: Storefront 3-Color Theme Engine -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-100 pb-3">
        <div>
          <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
            <span>✨</span> Storefront 3-Color Theme Engine
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Set 3 core colors (60-30-10 rule) — system matches hover states &amp; soft tints</p>
        </div>

        <button type="button" onclick="setThemeColors('#16A34A', '#1C1917', '#FAFAF5')" class="w-full sm:w-auto px-3.5 py-1.5 text-xs font-extrabold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span>Reset Default Colors</span>
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-stone-50 p-3.5 rounded-xl border border-stone-200 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">1. Primary Accent (10%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="primaryPicker" value="{{ $settings['theme_primary_color'] ?? '#16A34A' }}" class="h-9 w-12 rounded-lg border border-stone-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('primaryInput').value = this.value" />
            <input type="text" id="primaryInput" name="theme_primary_color" class="w-full text-xs font-mono font-bold uppercase px-3 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none" value="{{ $settings['theme_primary_color'] ?? '#16A34A' }}" placeholder="#16A34A" oninput="document.getElementById('primaryPicker').value = this.value" />
          </div>
          <p class="text-[10px] text-stone-500">Action buttons, badges, pills</p>
        </div>

        <div class="bg-stone-50 p-3.5 rounded-xl border border-stone-200 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">2. Dark Heading (30%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="darkPicker" value="{{ $settings['theme_dark_color'] ?? '#1C1917' }}" class="h-9 w-12 rounded-lg border border-stone-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('darkInput').value = this.value" />
            <input type="text" id="darkInput" name="theme_dark_color" class="w-full text-xs font-mono font-bold uppercase px-3 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none" value="{{ $settings['theme_dark_color'] ?? '#1C1917' }}" placeholder="#1C1917" oninput="document.getElementById('darkPicker').value = this.value" />
          </div>
          <p class="text-[10px] text-stone-500">Headings, titles, top nav bar</p>
        </div>

        <div class="bg-stone-50 p-3.5 rounded-xl border border-stone-200 space-y-2">
          <label class="text-xs font-extrabold text-stone-800 block">3. Soft Surface (60%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="surfacePicker" value="{{ $settings['theme_surface_color'] ?? '#FAFAF5' }}" class="h-9 w-12 rounded-lg border border-stone-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('surfaceInput').value = this.value" />
            <input type="text" id="surfaceInput" name="theme_surface_color" class="w-full text-xs font-mono font-bold uppercase px-3 py-2 bg-white border border-stone-200 rounded-lg focus:outline-none" value="{{ $settings['theme_surface_color'] ?? '#FAFAF5' }}" placeholder="#FAFAF5" oninput="document.getElementById('surfacePicker').value = this.value" />
          </div>
          <p class="text-[10px] text-stone-500">Canvas &amp; section backgrounds</p>
        </div>
      </div>

      <!-- Presets -->
      <div class="pt-3 border-t border-stone-100 space-y-2">
        <span class="text-xs font-extrabold text-stone-500 uppercase tracking-wider block">Theme Presets:</span>
        <div class="flex flex-wrap gap-2 items-center">
          <button type="button" onclick="setThemeColors('#16A34A', '#1C1917', '#FAFAF5')" class="text-xs font-extrabold text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
            <span class="h-3 w-3 rounded-full bg-[#16A34A]"></span> 🌿 Fresh Organic Eco Green
          </button>
          <button type="button" onclick="setThemeColors('#D97706', '#064E3B', '#FFFDF9')" class="text-xs font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
            <span class="h-3 w-3 rounded-full bg-[#D97706]"></span> 🍯 Harvest Gold &amp; Green
          </button>
          <button type="button" onclick="setThemeColors('#059669', '#111827', '#F4F4F5')" class="text-xs font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
            <span class="h-3 w-3 rounded-full bg-[#059669]"></span> 🥦 Emerald Farm Fresh
          </button>
          <button type="button" onclick="setThemeColors('#E8751B', '#353535', '#F8FAFC')" class="text-xs font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
            <span class="h-3 w-3 rounded-full bg-[#E8751B]"></span> 🟠 Warm Sunset Orange
          </button>
          <button type="button" onclick="setThemeColors('#2563EB', '#0F172A', '#F8FAFC')" class="text-xs font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
            <span class="h-3 w-3 rounded-full bg-[#2563EB]"></span> 🔵 Royal Sapphire
          </button>
        </div>
      </div>
    </div>

    <!-- Sub-Card 3: Contact & Social Links -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <div class="border-b border-stone-100 pb-3">
        <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
          <span>📞</span> Store Contact Info &amp; Social Links
        </h3>
        <p class="text-xs text-stone-500 mt-0.5">Phone, email, address, opening hours, and social media channels</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Contact Phone</label>
          <input name="contact_phone" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+8801700000000" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Contact Email</label>
          <input name="contact_email" type="email" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['contact_email'] ?? '' }}" placeholder="support@store.com" />
        </div>
      </div>
      <div>
        <label class="text-xs font-bold text-stone-700 block mb-1">Physical Address</label>
        <input name="contact_address" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['contact_address'] ?? '' }}" placeholder="House 12, Road 5, Dhaka" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Contact Hours</label>
          <input name="contact_hours" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['contact_hours'] ?? '' }}" placeholder="Sat–Thu, 9am – 9pm" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Contact Page Title</label>
          <input name="contact_title" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['contact_title'] ?? '' }}" placeholder="Get in touch" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Search Placeholder</label>
          <input name="search_placeholder" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none" value="{{ $settings['search_placeholder'] ?? '' }}" placeholder="Search Product..." />
        </div>
      </div>

      <div class="pt-3 border-t border-stone-100 space-y-3">
        <h4 class="text-xs font-extrabold text-stone-500 uppercase tracking-wider">Social Media Links</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div><label class="text-xs font-bold text-stone-700 block mb-1">Facebook URL</label><input name="facebook_url" class="w-full text-xs font-bold px-3.5 py-2 bg-white border border-stone-200 rounded-xl" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/..." /></div>
          <div><label class="text-xs font-bold text-stone-700 block mb-1">Instagram URL</label><input name="instagram_url" class="w-full text-xs font-bold px-3.5 py-2 bg-white border border-stone-200 rounded-xl" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/..." /></div>
          <div><label class="text-xs font-bold text-stone-700 block mb-1">Twitter URL</label><input name="twitter_url" class="w-full text-xs font-bold px-3.5 py-2 bg-white border border-stone-200 rounded-xl" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://twitter.com/..." /></div>
        </div>
      </div>

      <div class="pt-3 border-t border-stone-100 flex justify-end">
        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold shadow-md cursor-pointer">Save Brand &amp; Contact Settings</button>
      </div>
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

  <!-- TAB 2: HOMEPAGE & SEO -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'homepage') }}" class="settings-section-form space-y-6" data-section="homepage">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
      <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Section: Homepage Content</span>
      <button type="submit" class="section-save-btn px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Homepage Section</button>
    </div>
    <div class="section-feedback hidden text-xs font-bold rounded-xl px-4 py-2"></div>

    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <div class="border-b border-stone-100 pb-3">
        <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
          <span>📢</span> Top Announcement &amp; Subtitles
        </h3>
        <p class="text-xs text-stone-500 mt-0.5">Top promo notification bar and general page subtitles</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Top Banner Promo Text</label><input name="header_promo_text" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['header_promo_text'] ?? '' }}" placeholder="Free shipping on orders over ৳1000!" /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Promo Banner Link URL</label><input name="header_promo_link" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['header_promo_link'] ?? '' }}" placeholder="/shop or full URL" /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Shop Page Subtitle</label><input name="shop_subtitle" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['shop_subtitle'] ?? '' }}" placeholder="Explore our full collection" /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Delivery ETA Announcement</label><input name="delivery_eta_text" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['delivery_eta_text'] ?? '' }}" placeholder="Estimated delivery in 2–3 days" /></div>
      </div>
    </div>

    <!-- Featured Brands Control Card -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <div class="border-b border-stone-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
            <span>🏷️</span> Featured Brands Homepage Section
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Toggle visibility and customize heading/subtitle for the Featured Brands carousel section</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer shrink-0">
          <input type="checkbox" name="show_featured_brands" value="1" @checked(($settings['show_featured_brands'] ?? '1') === '1') class="sr-only peer">
          <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
          <span class="ml-2.5 text-xs font-extrabold text-stone-800">Section Active</span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Section Title</label>
          <input name="home_featured_brands_title" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['home_featured_brands_title'] ?? 'Featured Brands' }}" placeholder="Featured Brands" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Section Subtitle</label>
          <input name="home_featured_brands_subtitle" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['home_featured_brands_subtitle'] ?? 'Shop authentic products directly from leading brands' }}" placeholder="Shop authentic products directly from leading brands" />
        </div>
      </div>
    </div>

    <!-- Customer Feedback & Reviews Control Card -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <div class="border-b border-stone-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
            <span>💬</span> Customer Feedback Homepage Section
          </h3>
          <p class="text-xs text-stone-500 mt-0.5">Toggle visibility and customize heading/subtitle for verified customer reviews on the homepage</p>
        </div>
        <div class="flex items-center gap-3">
          <a href="{{ route('admin.reviews.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 underline flex items-center gap-1">
            <span>Manage Reviews</span> <span>↗</span>
          </a>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" name="show_home_reviews" value="1" @checked(($settings['show_home_reviews'] ?? '1') === '1') class="sr-only peer">
            <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
            <span class="ml-2.5 text-xs font-extrabold text-stone-800">Section Active</span>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Section Title</label>
          <input name="home_reviews_title" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['home_reviews_title'] ?? 'Customer Feedback' }}" placeholder="Customer Feedback" />
        </div>
        <div>
          <label class="text-xs font-bold text-stone-700 block mb-1">Section Subtitle</label>
          <input name="home_reviews_subtitle" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['home_reviews_subtitle'] ?? 'What our happy customers say about our authentic products and service' }}" placeholder="What our happy customers say about our authentic products and service" />
        </div>
      </div>
    </div>
  </form>

  <!-- TAB 3: PAYMENTS & SHIPPING -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'payments') }}" class="settings-section-form space-y-6" data-section="payments">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
      <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Section: Mobile Banking &amp; Checkout Payments</span>
      <button type="submit" class="section-save-btn px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Payments Section</button>
    </div>
    <div class="section-feedback hidden text-xs font-bold rounded-xl px-4 py-2"></div>

    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-5">
      <div class="border-b border-stone-100 pb-3">
        <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
          <span>💳</span> Manual Payment Methods (bKash, Nagad, Rocket, COD)
        </h3>
        <p class="text-xs text-stone-500 mt-0.5">Enable or disable checkout payment gateways and provide merchant account numbers</p>
      </div>

      <div class="space-y-3">
        @php
          $payCodOn = ($settings['pay_cod_enabled'] ?? '1') === '1';
          $payBkashOn = ($settings['pay_bkash_enabled'] ?? '1') === '1';
          $payNagadOn = ($settings['pay_nagad_enabled'] ?? '1') === '1';
          $payRocketOn = ($settings['pay_rocket_enabled'] ?? '1') === '1';
        @endphp
        <label class="flex items-center justify-between gap-4 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50/50 transition">
          <span class="text-xs font-extrabold text-stone-900 flex items-center gap-2">💵 Cash on Delivery (COD)</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_cod_enabled" value="1" class="peer sr-only" @checked($payCodOn)>
            <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50/50 transition">
          <span class="text-xs font-extrabold text-pink-600 flex items-center gap-2">📱 bKash Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_bkash_enabled" value="1" class="peer sr-only" @checked($payBkashOn)>
            <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50/50 transition">
          <span class="text-xs font-extrabold text-orange-600 flex items-center gap-2">📱 Nagad Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_nagad_enabled" value="1" class="peer sr-only" @checked($payNagadOn)>
            <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50/50 transition">
          <span class="text-xs font-extrabold text-purple-600 flex items-center gap-2">📱 Rocket Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_rocket_enabled" value="1" class="peer sr-only" @checked($payRocketOn)>
            <span class="h-6 w-11 rounded-full bg-stone-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-stone-100">
        <div><label class="text-xs font-bold text-stone-700 block mb-1">bKash Personal/Merchant No.</label><input name="bkash_number" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['bkash_number'] ?? '' }}" placeholder="01700000000" /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Nagad Personal/Merchant No.</label><input name="nagad_number" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['nagad_number'] ?? '' }}" placeholder="01700000000" /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Rocket Personal/Merchant No.</label><input name="rocket_number" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['rocket_number'] ?? '' }}" placeholder="01700000000" /></div>
      </div>
    </div>
  </form>

  <!-- SHIPPING FORM -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'shipping') }}" class="settings-section-form space-y-6" data-section="shipping">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-stone-50 p-4 rounded-xl border border-stone-200">
      <span class="text-xs font-extrabold text-stone-600 uppercase tracking-wider">Section: Delivery &amp; Shipping Charges</span>
      <button type="submit" class="section-save-btn px-4 py-1.5 rounded-xl bg-brand-600 text-white text-xs font-extrabold shadow-2xs">Save Shipping Section</button>
    </div>
    <div class="section-feedback hidden text-xs font-bold rounded-xl px-4 py-2"></div>

    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-4">
      <div class="border-b border-stone-100 pb-3">
        <h3 class="font-extrabold text-base text-stone-900 flex items-center gap-2">
          <span>🚚</span> Shipping Zones, Tax &amp; Currency
        </h3>
        <p class="text-xs text-stone-500 mt-0.5">Delivery fees inside/outside primary zone and tax percentage</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Inside Zone Delivery Fee (৳)</label><input name="shipping_inside_dhaka" type="number" step="0.01" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['shipping_inside_dhaka'] ?? '' }}" required /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Outside Zone Delivery Fee (৳)</label><input name="shipping_outside_dhaka" type="number" step="0.01" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['shipping_outside_dhaka'] ?? '' }}" required /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">VAT / Tax Rate (%)</label><input name="tax_percent" type="number" step="0.01" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['tax_percent'] ?? '' }}" required /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Inside Zone Label</label><input name="shipping_inside_label" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['shipping_inside_label'] ?? '' }}" placeholder="Inside Dhaka" /></div>
        <div><label class="text-xs font-bold text-stone-700 block mb-1">Outside Zone Label</label><input name="shipping_outside_label" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl" value="{{ $settings['shipping_outside_label'] ?? '' }}" placeholder="Outside Dhaka" /></div>
      </div>
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
    saveAllFeedback.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-900', 'bg-rose-50', 'border-rose-200', 'text-rose-900', 'border');
    if (ok) {
      saveAllFeedback.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-900', 'border');
    } else {
      saveAllFeedback.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-900', 'border');
    }
    saveAllFeedback.innerHTML = (ok ? '✓ ' : '⚠️ ') + message;
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
            feedback.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
            feedback.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-900', 'border');
            feedback.innerHTML = '⚠️ ' + errs;
          }
        } else {
          if (feedback) {
            feedback.classList.remove('hidden', 'bg-rose-50', 'border-rose-200', 'text-rose-900');
            feedback.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-900', 'border');
            feedback.innerHTML = '✓ ' + (data.message || 'Section saved successfully.');
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
          feedback.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
          feedback.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-900', 'border');
          feedback.innerHTML = '⚠️ Network error while saving section.';
        }
      });
    });
  });

  // Handle Save All Settings Button
  if (saveAllBtn) {
    saveAllBtn.addEventListener('click', async function () {
      const origText = saveAllBtn.innerText;
      saveAllBtn.disabled = true;
      saveAllBtn.innerText = 'Saving All Sections...';
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
});
</script>
@endpush
