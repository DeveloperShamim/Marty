@extends('layouts.admin')
@section('title', 'Site Settings')

@section('content')
<div class="settings-page w-full max-w-4xl mx-auto space-y-6">
  {{-- Header & Save All Action --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
    <div>
      <h2 class="font-display text-xl font-bold text-ink flex items-center gap-2">
        <span>⚙️</span> Store Settings &amp; Customization
      </h2>
      <p class="text-xs text-gray-500 mt-1">Configure your brand identity, storefront colors, payments, shipping, and automated emails.</p>
    </div>
    <button type="button" id="saveAllSettings" class="btn-primary shrink-0 px-5 py-2.5 shadow-sm text-sm font-bold cursor-pointer">
      Save All Changes
    </button>
  </div>
  <div id="saveAllFeedback" class="hidden text-sm rounded-xl px-4 py-3 shadow-xs"></div>

  {{-- Organized Category Navigation Tabs --}}
  <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto no-scrollbar">
    <button type="button" onclick="switchSettingsTab('brand', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-xs transition shrink-0 cursor-pointer">🎨 Brand &amp; Theme</button>
    <button type="button" onclick="switchSettingsTab('homepage', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">🏠 Homepage &amp; SEO</button>
    <button type="button" onclick="switchSettingsTab('payments', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">💳 Payments &amp; Delivery</button>
    <button type="button" onclick="switchSettingsTab('system', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">⚙️ Mail &amp; Invoice</button>
    <a href="{{ route('admin.integrations.index') }}" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition shrink-0 cursor-pointer flex items-center gap-1">⚡ API Integrations &rarr;</a>
    <button type="button" onclick="switchSettingsTab('all', this)" class="settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer">📄 View All Sections</button>
  </div>

  <script>
  function switchSettingsTab(tabName, btn) {
    document.querySelectorAll('.settings-tab-btn').forEach(b => {
      b.className = 'settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 transition shrink-0 cursor-pointer';
    });
    btn.className = 'settings-tab-btn px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 text-white shadow-xs transition shrink-0 cursor-pointer';

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
    
    <!-- Section Header & Feedback -->
    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Brand &amp; Identity</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <!-- Sub-Card 1: Brand Identity & Logos -->
    <div class="card p-5 sm:p-6 space-y-5 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>🎨</span> Store Identity &amp; Logos
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Basic store name, taglines, and logo uploads</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Site Name</label><input name="site_name" class="inp" value="{{ $settings['site_name'] ?? '' }}" required /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Tagline</label><input name="tagline" class="inp" value="{{ $settings['tagline'] ?? '' }}" placeholder="Your store slogan" /></div>
      </div>
      <div><label class="lbl font-bold text-xs text-gray-700">Footer Copyright Text</label><textarea name="footer_text" class="inp" rows="2">{{ $settings['footer_text'] ?? '' }}</textarea></div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
        <div>
          <label class="lbl font-bold text-xs text-gray-700">Store Logo</label>
          <div class="flex items-center gap-3 mt-1.5">
            <div class="h-14 w-14 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 border border-gray-200 shadow-xs" data-logo-preview>
              <img src="{{ logo_url() }}" class="h-full w-full object-contain p-1.5" alt="Logo">
            </div>
            <div class="min-w-0 flex-1">
              <input name="logo_file" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="text-xs w-full" />
              <p class="text-[10px] text-gray-400 mt-1">PNG, JPG, SVG or WEBP (Max 2MB)</p>
            </div>
          </div>
        </div>

        <div>
          <label class="lbl font-bold text-xs text-gray-700">Favicon / Site Icon</label>
          <div class="flex items-center gap-3 mt-1.5">
            <div class="h-14 w-14 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 border border-gray-200 shadow-xs">
              <img src="{{ favicon_url() }}" class="h-8 w-8 object-contain" alt="Favicon" data-favicon-preview>
            </div>
            <div class="min-w-0 flex-1">
              <input name="favicon_file" type="file" accept="image/png,image/x-icon,image/svg+xml,image/webp,image/jpeg" class="text-xs w-full" />
              <p class="text-[10px] text-gray-400 mt-1">ICO, PNG, SVG or WEBP (Max 1MB)</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sub-Card 2: Storefront 3-Color Theme Engine -->
    <div class="card p-5 sm:p-6 space-y-5 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
        <div>
          <h3 class="font-bold text-base text-ink flex items-center gap-2">
            <span>✨</span> Storefront 3-Color Theme Engine
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Set 3 core colors (60-30-10 rule) — system matches hover states &amp; soft tints</p>
        </div>

        <button type="button" onclick="setThemeColors('#E8751B', '#353535', '#F8FAFC')" class="px-3 py-1.5 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span>Reset Default Colors</span>
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80 space-y-2">
          <label class="lbl text-xs font-bold text-gray-800">1. Primary Accent (10%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="primaryPicker" value="{{ $settings['theme_primary_color'] ?? '#E8751B' }}" class="h-9 w-12 rounded-lg border border-gray-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('primaryInput').value = this.value" />
            <input type="text" id="primaryInput" name="theme_primary_color" class="inp font-mono text-xs uppercase" value="{{ $settings['theme_primary_color'] ?? '#E8751B' }}" placeholder="#E8751B" oninput="document.getElementById('primaryPicker').value = this.value" />
          </div>
          <p class="text-[10px] text-gray-500">Action buttons, badges, pills</p>
        </div>

        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80 space-y-2">
          <label class="lbl text-xs font-bold text-gray-800">2. Dark Heading (30%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="darkPicker" value="{{ $settings['theme_dark_color'] ?? '#353535' }}" class="h-9 w-12 rounded-lg border border-gray-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('darkInput').value = this.value" />
            <input type="text" id="darkInput" name="theme_dark_color" class="inp font-mono text-xs uppercase" value="{{ $settings['theme_dark_color'] ?? '#353535' }}" placeholder="#353535" oninput="document.getElementById('darkPicker').value = this.value" />
          </div>
          <p class="text-[10px] text-gray-500">Headings, titles, top nav bar</p>
        </div>

        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80 space-y-2">
          <label class="lbl text-xs font-bold text-gray-800">3. Soft Surface (60%)</label>
          <div class="flex items-center gap-2">
            <input type="color" id="surfacePicker" value="{{ $settings['theme_surface_color'] ?? '#F8FAFC' }}" class="h-9 w-12 rounded-lg border border-gray-300 p-0.5 bg-white cursor-pointer shrink-0" onchange="document.getElementById('surfaceInput').value = this.value" />
            <input type="text" id="surfaceInput" name="theme_surface_color" class="inp font-mono text-xs uppercase" value="{{ $settings['theme_surface_color'] ?? '#F8FAFC' }}" placeholder="#F8FAFC" oninput="document.getElementById('surfacePicker').value = this.value" />
          </div>
          <p class="text-[10px] text-gray-500">Canvas &amp; section backgrounds</p>
        </div>
      </div>

      <!-- Presets -->
      <div class="pt-2 border-t border-gray-100 flex flex-wrap gap-2 items-center">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Presets:</span>
        <button type="button" onclick="setThemeColors('#E8751B', '#353535', '#F8FAFC')" class="text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
          <span class="h-3 w-3 rounded-full bg-[#E8751B]"></span> Warm Orange
        </button>
        <button type="button" onclick="setThemeColors('#2563EB', '#0F172A', '#F8FAFC')" class="text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
          <span class="h-3 w-3 rounded-full bg-[#2563EB]"></span> Royal Sapphire
        </button>
        <button type="button" onclick="setThemeColors('#059669', '#111827', '#F4F4F5')" class="text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
          <span class="h-3 w-3 rounded-full bg-[#059669]"></span> Emerald Luxe
        </button>
        <button type="button" onclick="setThemeColors('#DC2626', '#1F2937', '#F9FAFB')" class="text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
          <span class="h-3 w-3 rounded-full bg-[#DC2626]"></span> Ruby Crimson
        </button>
      </div>

      <!-- Hero Banners Shortcut Box -->
      <div class="border-t border-gray-100 pt-3.5 mt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3.5 bg-brand-50/70 rounded-xl border border-brand-200">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 shadow-xs">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
            </div>
            <div>
              <h4 class="font-bold text-xs text-ink">Hero Slider Banners</h4>
              <p class="text-[11px] text-gray-500">Upload slider images, badges, headings, CTA buttons and links</p>
            </div>
          </div>
          <a href="{{ route('admin.banners.index') }}" class="px-3.5 py-2 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-xs whitespace-nowrap text-center">Manage Hero Banners &rarr;</a>
        </div>
      </div>
    </div>

    <!-- Sub-Card 3: Contact & Social Links -->
    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>📞</span> Store Contact Info &amp; Social Links
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Phone, email, address, opening hours, and social media channels</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Contact Phone</label><input name="contact_phone" class="inp" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+8801700000000" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Contact Email</label><input name="contact_email" type="email" class="inp" value="{{ $settings['contact_email'] ?? '' }}" placeholder="support@store.com" /></div>
      </div>
      <div><label class="lbl font-bold text-xs text-gray-700">Physical Address</label><input name="contact_address" class="inp" value="{{ $settings['contact_address'] ?? '' }}" placeholder="House 12, Road 5, Dhaka" /></div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Contact Hours</label><input name="contact_hours" class="inp" value="{{ $settings['contact_hours'] ?? '' }}" placeholder="Sat–Thu, 9am – 9pm" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Contact Page Title</label><input name="contact_title" class="inp" value="{{ $settings['contact_title'] ?? '' }}" placeholder="Get in touch" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Search Placeholder</label><input name="search_placeholder" class="inp" value="{{ $settings['search_placeholder'] ?? '' }}" placeholder="Search Product..." /></div>
      </div>
      <div><label class="lbl font-bold text-xs text-gray-700">Contact Page Subtitle / Intro</label><input name="contact_intro" class="inp" value="{{ $settings['contact_intro'] ?? '' }}" placeholder="We usually reply within a few hours." /></div>

      <div class="pt-3 border-t border-gray-100 space-y-3">
        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Social Media Links</h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div><label class="lbl text-xs font-semibold text-gray-700">Facebook URL</label><input name="facebook_url" class="inp" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/..." /></div>
          <div><label class="lbl text-xs font-semibold text-gray-700">Instagram URL</label><input name="instagram_url" class="inp" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/..." /></div>
          <div><label class="lbl text-xs font-semibold text-gray-700">Twitter URL</label><input name="twitter_url" class="inp" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://twitter.com/..." /></div>
        </div>
      </div>

      <div class="pt-3 border-t border-gray-100 flex justify-end">
        <button type="submit" class="section-save-btn btn-primary px-5 py-2 text-xs font-bold">Save Brand &amp; Contact Settings</button>
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

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Homepage Content</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Homepage Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>📢</span> Top Announcement &amp; Subtitles
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Top promo notification bar and general page subtitles</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Top Banner Promo Text</label><input name="header_promo_text" class="inp" value="{{ $settings['header_promo_text'] ?? '' }}" placeholder="Free shipping on orders over ৳1000!" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Promo Banner Link URL</label><input name="header_promo_link" class="inp" value="{{ $settings['header_promo_link'] ?? '' }}" placeholder="/shop or full URL" /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Shop Page Subtitle</label><input name="shop_subtitle" class="inp" value="{{ $settings['shop_subtitle'] ?? '' }}" placeholder="Explore our full collection" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Delivery ETA Announcement</label><input name="delivery_eta_text" class="inp" value="{{ $settings['delivery_eta_text'] ?? '' }}" placeholder="Estimated delivery in 2–3 days" /></div>
      </div>
    </div>

    <!-- Featured Brands Control Card -->
    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-bold text-base text-ink flex items-center gap-2">
            <span>🏷️</span> Featured Brands Homepage Section
          </h3>
          <p class="text-xs text-gray-500 mt-0.5">Toggle visibility and customize heading/subtitle for the Featured Brands carousel section</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer shrink-0">
          <input type="checkbox" name="show_featured_brands" value="1" @checked(($settings['show_featured_brands'] ?? '1') === '1') class="sr-only peer">
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
          <span class="ml-2.5 text-xs font-extrabold text-gray-700">Section Active</span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="lbl font-bold text-xs text-gray-700">Section Title</label>
          <input name="home_featured_brands_title" class="inp" value="{{ $settings['home_featured_brands_title'] ?? 'Featured Brands' }}" placeholder="Featured Brands" />
        </div>
        <div>
          <label class="lbl font-bold text-xs text-gray-700">Section Subtitle</label>
          <input name="home_featured_brands_subtitle" class="inp" value="{{ $settings['home_featured_brands_subtitle'] ?? 'Shop authentic products directly from leading brands' }}" placeholder="Shop authentic products directly from leading brands" />
        </div>
      </div>
    </div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>🏷️</span> Homepage Section Titles &amp; Labels
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Customize section headings and button call-to-action texts</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Categories Section Title</label><input name="home_categories_title" class="inp" value="{{ $settings['home_categories_title'] ?? '' }}" placeholder="Explore Product Categories" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Hot Deal Section Title</label><input name="home_hot_deal_title" class="inp" value="{{ $settings['home_hot_deal_title'] ?? '' }}" placeholder="Hot Deal of the Week" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Featured Products Title</label><input name="home_featured_title" class="inp" value="{{ $settings['home_featured_title'] ?? '' }}" placeholder="Featured Collection" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Customer Reviews Title</label><input name="home_reviews_title" class="inp" value="{{ $settings['home_reviews_title'] ?? '' }}" placeholder="What Our Customers Say" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">"View More" Button Label</label><input name="home_view_more_label" class="inp" value="{{ $settings['home_view_more_label'] ?? '' }}" placeholder="View More Products" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Default CTA Button Text</label><input name="default_cta_text" class="inp" value="{{ $settings['default_cta_text'] ?? '' }}" placeholder="ADD TO CART" /></div>
      </div>
    </div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>🖼️</span> Hero Banner Fallback Copy
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Shown when no active slider banners are created in the database</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Fallback Badge Text</label><input name="hero_fallback_badge" class="inp" value="{{ $settings['hero_fallback_badge'] ?? '' }}" placeholder="LUXURY COLLECTION 2026" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Fallback Main Title</label><input name="hero_fallback_title" class="inp" value="{{ $settings['hero_fallback_title'] ?? '' }}" placeholder="Premium Shoes &amp; Watches" /></div>
      </div>
      <div><label class="lbl font-bold text-xs text-gray-700">Fallback Subtitle Description</label><input name="hero_fallback_subtitle" class="inp" value="{{ $settings['hero_fallback_subtitle'] ?? '' }}" placeholder="Explore authentic footwear and luxury accessories." /></div>
    </div>
  </form>

  <!-- SEO DEFAULTS FORM -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'seo') }}" class="settings-section-form space-y-6" data-section="seo">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: SEO &amp; Meta Tags</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save SEO Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>🔍</span> Default Meta Tags &amp; Search Optimization
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Global search engine defaults for Google &amp; social sharing</p>
      </div>

      <div><label class="lbl font-bold text-xs text-gray-700">Default Meta Title</label><input name="default_meta_title" class="inp" value="{{ $settings['default_meta_title'] ?? '' }}" placeholder="Online Shopping in Bangladesh" /></div>
      <div><label class="lbl font-bold text-xs text-gray-700">Default Meta Description</label><textarea name="default_meta_description" class="inp" rows="2">{{ $settings['default_meta_description'] ?? '' }}</textarea></div>
      <div><label class="lbl font-bold text-xs text-gray-700">Default Meta Keywords</label><textarea name="default_meta_keywords" class="inp" rows="2">{{ $settings['default_meta_keywords'] ?? '' }}</textarea></div>
    </div>
  </form>

  <!-- TRACKING SCRIPTS FORM -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'tracking') }}" class="settings-section-form space-y-6" data-section="tracking">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Analytics &amp; Facebook Pixel</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Tracking Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>📊</span> Analytics &amp; Ad Tracking Pixels
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Facebook Pixel ID, Google Analytics tracking, and custom header scripts</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Facebook Pixel ID</label><input name="facebook_pixel_id" class="inp" value="{{ $settings['facebook_pixel_id'] ?? '' }}" placeholder="123456789012345" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Google Analytics ID</label><input name="google_analytics_id" class="inp" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" /></div>
      </div>
      <div><label class="lbl font-bold text-xs text-gray-700">Custom Head Scripts (&lt;head&gt;)</label><textarea name="custom_head_scripts" class="inp font-mono text-xs" rows="3" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ $settings['custom_head_scripts'] ?? '' }}</textarea></div>
      <div><label class="lbl font-bold text-xs text-gray-700">Custom Body Scripts (&lt;body&gt;)</label><textarea name="custom_body_scripts" class="inp font-mono text-xs" rows="3" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ $settings['custom_body_scripts'] ?? '' }}</textarea></div>
    </div>
  </form>

  <!-- TAB 3: PAYMENTS & SHIPPING -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'payments') }}" class="settings-section-form space-y-6" data-section="payments">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Mobile Banking &amp; Checkout Payments</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Payments Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-5 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>💳</span> Manual Payment Methods (bKash, Nagad, Rocket, COD)
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Enable or disable checkout payment gateways and provide merchant account numbers</p>
      </div>

      <div class="space-y-3">
        @php
          $payCodOn = ($settings['pay_cod_enabled'] ?? '1') === '1';
          $payBkashOn = ($settings['pay_bkash_enabled'] ?? '1') === '1';
          $payNagadOn = ($settings['pay_nagad_enabled'] ?? '1') === '1';
          $payRocketOn = ($settings['pay_rocket_enabled'] ?? '1') === '1';
        @endphp
        <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50/50 transition">
          <span class="text-sm font-bold text-ink flex items-center gap-2">💵 Cash on Delivery (COD)</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_cod_enabled" value="1" class="peer sr-only" @checked($payCodOn)>
            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50/50 transition">
          <span class="text-sm font-bold text-pink-600 flex items-center gap-2">📱 bKash Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_bkash_enabled" value="1" class="peer sr-only" @checked($payBkashOn)>
            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50/50 transition">
          <span class="text-sm font-bold text-orange-600 flex items-center gap-2">📱 Nagad Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_nagad_enabled" value="1" class="peer sr-only" @checked($payNagadOn)>
            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50/50 transition">
          <span class="text-sm font-bold text-purple-600 flex items-center gap-2">📱 Rocket Merchant Payment</span>
          <span class="relative inline-flex items-center shrink-0">
            <input type="checkbox" name="pay_rocket_enabled" value="1" class="peer sr-only" @checked($payRocketOn)>
            <span class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-brand-600"></span>
            <span class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-gray-100">
        <div><label class="lbl font-bold text-xs text-gray-700">bKash Personal/Merchant No.</label><input name="bkash_number" class="inp" value="{{ $settings['bkash_number'] ?? '' }}" placeholder="01700000000" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Nagad Personal/Merchant No.</label><input name="nagad_number" class="inp" value="{{ $settings['nagad_number'] ?? '' }}" placeholder="01700000000" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Rocket Personal/Merchant No.</label><input name="rocket_number" class="inp" value="{{ $settings['rocket_number'] ?? '' }}" placeholder="01700000000" /></div>
      </div>
    </div>
  </form>

  <!-- SHIPPING FORM -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'shipping') }}" class="settings-section-form space-y-6" data-section="shipping">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Delivery &amp; Shipping Charges</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Shipping Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>🚚</span> Shipping Zones, Tax &amp; Currency
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Delivery fees inside/outside primary zone and tax percentage</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Inside Zone Delivery Fee (Amount)</label><input name="shipping_inside_dhaka" type="number" step="0.01" class="inp" value="{{ $settings['shipping_inside_dhaka'] ?? '' }}" required /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Outside Zone Delivery Fee (Amount)</label><input name="shipping_outside_dhaka" type="number" step="0.01" class="inp" value="{{ $settings['shipping_outside_dhaka'] ?? '' }}" required /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">VAT / Tax Rate (%)</label><input name="tax_percent" type="number" step="0.01" class="inp" value="{{ $settings['tax_percent'] ?? '' }}" required /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Inside Zone Label</label><input name="shipping_inside_label" class="inp" value="{{ $settings['shipping_inside_label'] ?? '' }}" placeholder="Inside Dhaka" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Outside Zone Label</label><input name="shipping_outside_label" class="inp" value="{{ $settings['shipping_outside_label'] ?? '' }}" placeholder="Outside Dhaka" /></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Currency Symbol</label><input name="currency_symbol" class="inp" value="{{ $settings['currency_symbol'] ?? '' }}" placeholder="৳" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Currency ISO Code</label><input name="currency_code" class="inp" value="{{ $settings['currency_code'] ?? '' }}" placeholder="BDT" /></div>
      </div>
    </div>
  </form>

  <!-- TAB 4: MAIL, OTP & INVOICE -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'mail') }}" class="settings-section-form space-y-6" data-section="mail">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: Email Server &amp; OTP</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Email Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-5 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>📧</span> Automated Email &amp; Customer OTP Verification
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Configure SMTP server credentials and customer registration OTP verification</p>
      </div>

      <label class="flex items-center justify-between gap-4 p-4 rounded-xl border border-gray-200 bg-gray-50/50 cursor-pointer">
        <div>
          <span class="block text-sm font-bold text-ink">Require OTP Code Verification</span>
          <span class="block text-xs text-gray-500 mt-0.5">Sends automated OTP code to customer email during signup or login</span>
        </div>
        <input type="checkbox" name="otp_enabled" value="1" class="h-5 w-5 accent-brand-600 rounded cursor-pointer" @checked(($settings['otp_enabled'] ?? '1') === '1')>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="lbl font-bold text-xs text-gray-700">Mailer Engine</label>
          <select name="mail_mailer" class="inp font-semibold">
            <option value="log" @selected(($settings['mail_mailer'] ?? 'log') === 'log')>Log (Writes to laravel.log file)</option>
            <option value="smtp" @selected(($settings['mail_mailer'] ?? '') === 'smtp')>SMTP (Live Email Server)</option>
          </select>
        </div>
        <div><label class="lbl font-bold text-xs text-gray-700">Sender Name</label><input name="mail_from_name" class="inp" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="My Store" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Sender Email Address</label><input name="mail_from_address" type="email" class="inp" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="no-reply@store.com" /></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-3 border-t border-gray-100">
        <div class="sm:col-span-2"><label class="lbl font-bold text-xs text-gray-700">SMTP Host</label><input name="mail_host" class="inp" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.gmail.com" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Port</label><input name="mail_port" class="inp" value="{{ $settings['mail_port'] ?? '' }}" placeholder="587" /></div>
        <div>
          <label class="lbl font-bold text-xs text-gray-700">Encryption</label>
          <select name="mail_encryption" class="inp">
            @foreach(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $val => $lbl)
              <option value="{{ $val }}" @selected(($settings['mail_encryption'] ?? 'tls') === $val)>{{ $lbl }}</option>
            @endforeach
          </select>
        </div>
        <div class="sm:col-span-2"><label class="lbl font-bold text-xs text-gray-700">SMTP Username</label><input name="mail_username" class="inp" value="{{ $settings['mail_username'] ?? '' }}" /></div>
        <div class="sm:col-span-2"><label class="lbl font-bold text-xs text-gray-700">SMTP Password</label><input name="mail_password" type="password" class="inp" placeholder="{{ ($settings['mail_password'] ?? '') ? '•••••• (leave blank to keep)' : '' }}" autocomplete="new-password" /></div>
      </div>
    </div>
  </form>

  <!-- INVOICE SETTINGS FORM -->
  <form method="POST" action="{{ route('admin.settings.update-section', 'invoice') }}" class="settings-section-form space-y-6" data-section="invoice">
    @csrf @method('PUT')

    <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200/80">
      <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Section: PDF Invoice Format</span>
      <button type="submit" class="section-save-btn btn-primary px-4 py-1.5 text-xs font-bold shadow-xs">Save Invoice Section</button>
    </div>
    <div class="section-feedback hidden text-sm rounded-xl px-4 py-2"></div>

    <div class="card p-5 sm:p-6 space-y-4 bg-white rounded-2xl border border-gray-200/80 shadow-xs">
      <div class="border-b border-gray-100 pb-3">
        <h3 class="font-bold text-base text-ink flex items-center gap-2">
          <span>📄</span> Printable Order &amp; Customer Invoice Template
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">Company details, VAT registration number, terms, and order number prefix printed on PDF receipts</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Company Legal Name</label><input name="invoice_company_name" class="inp" value="{{ $settings['invoice_company_name'] ?? '' }}" placeholder="Store Ltd." /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">Order Number Prefix</label><input name="order_number_prefix" class="inp" value="{{ $settings['order_number_prefix'] ?? '' }}" placeholder="ORD-" /></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="lbl font-bold text-xs text-gray-700">Invoice Phone</label><input name="invoice_phone" class="inp" value="{{ $settings['invoice_phone'] ?? '' }}" placeholder="+8801700000000" /></div>
        <div><label class="lbl font-bold text-xs text-gray-700">VAT / TAX Registration Number</label><input name="invoice_vat_number" class="inp" value="{{ $settings['invoice_vat_number'] ?? '' }}" placeholder="BIN-123456789" /></div>
      </div>

      <div><label class="lbl font-bold text-xs text-gray-700">Invoice Address</label><textarea name="invoice_address" class="inp" rows="2">{{ $settings['invoice_address'] ?? '' }}</textarea></div>
      <div><label class="lbl font-bold text-xs text-gray-700">Invoice Terms &amp; Conditions</label><textarea name="invoice_terms" class="inp" rows="2" placeholder="Thank you for shopping with us! Goods sold are non-refundable after 7 days.">{{ $settings['invoice_terms'] ?? '' }}</textarea></div>
    </div>
  </form>
</div>
@endsection
