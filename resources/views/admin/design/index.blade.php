@extends('layouts.admin')
@section('title', 'Homepage Design & Theme Palette')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
  
  {{-- Header --}}
  <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-5">
    <div>
      <h1 class="text-2xl font-black text-gray-900 tracking-tight">Homepage Design &amp; Theme Palette</h1>
      <p class="text-sm text-gray-500 mt-1">Maintain a cohesive, professional 3-to-4 color appearance across your storefront following web design standards.</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('home') }}" target="_blank" class="px-4 py-2 text-xs font-bold rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition inline-flex items-center gap-1.5 shadow-xs">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span>Preview Storefront</span>
      </a>
      <a href="{{ route('admin.banners.create') }}" class="btn-primary flex items-center gap-1.5 shadow-sm">
        <span>+ Add Hero Slide</span>
      </a>
    </div>
  </div>

  @if(session('status'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->has('logo'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-bold flex items-center gap-2">
      <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>{{ $errors->first('logo') }}</span>
    </div>
  @endif

  {{-- Section 0: Auto-Detect Brand Colors from Logo --}}
  <div class="card p-5 bg-gradient-to-r from-amber-500/10 via-brand-500/10 to-amber-500/10 border border-brand-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="h-12 w-12 rounded-xl bg-white p-1.5 shadow-xs border border-gray-200 flex items-center justify-center shrink-0">
        <img src="{{ logo_url() }}" alt="Current Logo" class="max-h-full max-w-full object-contain">
      </div>
      <div>
        <h3 class="text-sm font-extrabold text-gray-900 flex items-center gap-1.5">
          <span>⚡ Auto-Detect Website Colors from Logo</span>
        </h3>
        <p class="text-xs text-gray-500">Automatically analyze your logo image and extract its dominant accent color for your entire website design.</p>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.homepage-design.extract-logo-color') }}" class="shrink-0">
      @csrf
      <button type="submit" class="btn-primary text-xs px-4 py-2.5 shadow-sm inline-flex items-center gap-1.5">
        <svg class="w-4 h-4 text-amber-300 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Auto-Detect From Logo</span>
      </button>
    </form>
  </div>

  {{-- Section 1: Professional 3 to 4 Color System --}}
  <div class="card p-6 shadow-sm border border-gray-200/80 rounded-2xl bg-white space-y-6">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
      <div>
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
          <span>Professional 3 to 4 Color System (60-30-10 Rule)</span>
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">Top web design experts limit a site to 3-4 cohesive colors: 60% Neutral White/Light Canvas, 30% Dark Heading &amp; Text, and 10% Primary Brand Action Color.</p>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.homepage-design.update-theme') }}" class="space-y-6">
      @csrf
      @method('PUT')

      <div class="grid md:grid-cols-2 gap-6">
        
        {{-- Color Selection --}}
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-bold text-gray-800 mb-1.5">1. Primary Brand Action Color (10%)</label>
            <p class="text-xs text-gray-400 mb-2">Used for Add to Cart buttons, active tabs, header cart badges, and primary action links.</p>
            <div class="flex items-center gap-3">
              <input type="color" id="primaryColorPicker" value="{{ old('brand_primary_color', $brandPrimaryColor) }}" class="h-12 w-16 rounded-xl border border-gray-300 p-1 cursor-pointer bg-white" onchange="document.getElementById('primaryHexInput').value = this.value; updatePreview(this.value);" />
              <input type="text" id="primaryHexInput" name="brand_primary_color" value="{{ old('brand_primary_color', $brandPrimaryColor) }}" class="form-input flex-1 font-mono uppercase text-sm font-bold text-gray-800" placeholder="#E8751B" oninput="document.getElementById('primaryColorPicker').value = this.value; updatePreview(this.value);" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-800 mb-1.5">2. Dark Heading &amp; Text Color (30%)</label>
            <p class="text-xs text-gray-400 mb-2">Used for clear headings, body text, and structural headers.</p>
            <input type="text" readonly value="#1E293B (Slate Dark Ink)" class="form-input w-full font-mono text-sm bg-gray-50 text-gray-600 border-gray-200 cursor-not-allowed" />
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-800 mb-1.5">3. Canvas &amp; Card Background (60%)</label>
            <p class="text-xs text-gray-400 mb-2">Clean neutral white (#FFFFFF) for high contrast and readability.</p>
            <input type="text" readonly value="#FFFFFF (Pure White) / #F8FAFC (Soft Slate)" class="form-input w-full font-mono text-sm bg-gray-50 text-gray-600 border-gray-200 cursor-not-allowed" />
          </div>

          {{-- Curated 3-4 Color Designer Presets --}}
          <div class="space-y-2 pt-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Curated 4-Color Designer Themes</span>
            <div class="grid grid-cols-2 gap-2">
              <button type="button" onclick="setPreset('#E8751B')" class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 text-xs font-bold hover:bg-gray-50 transition text-left">
                <span class="truncate">Warm Amber</span>
                <span class="flex items-center gap-1 shrink-0">
                  <span class="h-3.5 w-3.5 rounded-full bg-[#E8751B]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#1E293B]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#F8FAFC] border"></span>
                </span>
              </button>
              <button type="button" onclick="setPreset('#2563EB')" class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 text-xs font-bold hover:bg-gray-50 transition text-left">
                <span class="truncate">Royal Blue</span>
                <span class="flex items-center gap-1 shrink-0">
                  <span class="h-3.5 w-3.5 rounded-full bg-[#2563EB]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#0F172A]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#F8FAFC] border"></span>
                </span>
              </button>
              <button type="button" onclick="setPreset('#059669')" class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 text-xs font-bold hover:bg-gray-50 transition text-left">
                <span class="truncate">Emerald Luxe</span>
                <span class="flex items-center gap-1 shrink-0">
                  <span class="h-3.5 w-3.5 rounded-full bg-[#059669]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#111827]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#F8FAFC] border"></span>
                </span>
              </button>
              <button type="button" onclick="setPreset('#DC2626')" class="flex items-center justify-between p-2.5 rounded-xl border border-gray-200 text-xs font-bold hover:bg-gray-50 transition text-left">
                <span class="truncate">Ruby Crimson</span>
                <span class="flex items-center gap-1 shrink-0">
                  <span class="h-3.5 w-3.5 rounded-full bg-[#DC2626]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#1F2937]"></span>
                  <span class="h-3.5 w-3.5 rounded-full bg-[#F8FAFC] border"></span>
                </span>
              </button>
            </div>
          </div>
        </div>

        {{-- Live Storefront UI Component Preview --}}
        <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-5 space-y-4 flex flex-col justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block mb-3">Live Storefront 3-Color Preview</span>
            
            <div class="bg-white p-4 rounded-xl border border-gray-200 space-y-3 shadow-xs">
              <div class="flex items-center justify-between">
                <span id="previewBadge" class="text-xs font-bold text-white px-2.5 py-1 rounded-full uppercase" style="background-color: {{ $brandPrimaryColor }};">⚡ SPECIAL DISCOUNT</span>
                <span id="previewPrice" class="text-base font-extrabold" style="color: {{ $brandPrimaryColor }};">$120.00</span>
              </div>

              <p class="text-xs text-gray-600 font-medium">Sample Storefront Product Card layout with high-contrast text and primary CTA button.</p>

              <button type="button" id="previewButton" class="w-full py-2.5 px-4 text-xs font-bold text-white rounded-xl shadow-xs transition" style="background-color: {{ $brandPrimaryColor }};">
                + Add to Cart
              </button>
            </div>
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium leading-relaxed">
            <span class="font-bold">💡 Design Tip:</span> By restricting the storefront color palette to 3-4 balanced colors, call-to-action buttons stand out immediately without visually cluttering the shopping experience.
          </div>
        </div>
      </div>

      <div class="flex justify-end pt-2 border-t border-gray-100">
        <button type="submit" class="btn-primary shadow-md">
          Save Brand Colors
        </button>
      </div>
    </form>
  </div>

  {{-- Section 2: Hero Banners & Slider Management --}}
  <div class="card p-6 shadow-sm border border-gray-200/80 rounded-2xl bg-white space-y-6">
    <div class="flex flex-wrap items-center justify-between border-b border-gray-100 pb-4 gap-3">
      <div>
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
          <span>Homepage Hero Banners &amp; Slider</span>
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">Control slides displayed in the main hero banner section on the storefront homepage.</p>
      </div>
      <a href="{{ route('admin.banners.create') }}" class="btn-primary text-xs px-3.5 py-2">+ Add Slide</a>
    </div>

    @if($heroBanners->isEmpty())
      <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl">
        <p class="text-sm font-semibold text-gray-500">No hero banners created yet.</p>
        <p class="text-xs text-gray-400 mt-1">Create your first hero banner slide to showcase your products on the homepage.</p>
        <a href="{{ route('admin.banners.create') }}" class="btn-primary text-xs mt-4 inline-flex">+ Create Hero Banner</a>
      </div>
    @else
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($heroBanners as $banner)
          <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-xs hover:shadow-md transition">
            <div class="h-36 bg-gray-900 relative">
              @if($banner->image)
                <img src="{{ $banner->imageUrl() }}" class="h-full w-full object-cover opacity-80" alt="{{ $banner->title }}">
              @else
                <div class="h-full w-full bg-gradient-to-br from-brand-600 to-amber-600 opacity-80"></div>
              @endif
              <div class="absolute inset-0 p-3 flex flex-col justify-end bg-gradient-to-t from-black/70 to-transparent">
                @if($banner->badge)<span class="w-fit bg-brand-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md mb-1">{{ $banner->badge }}</span>@endif
                <p class="text-white font-bold text-sm line-clamp-1">{{ $banner->title ?: 'Untitled Slide' }}</p>
                @if($banner->subtitle)<p class="text-white/75 text-xs line-clamp-1">{{ $banner->subtitle }}</p>@endif
              </div>
              <span class="absolute top-2.5 right-2.5 text-[10px] font-bold px-2.5 py-0.5 rounded-full {{ $banner->is_active ? 'bg-emerald-500 text-white' : 'bg-gray-600 text-white' }}">
                {{ $banner->is_active ? 'Visible' : 'Hidden' }}
              </span>
            </div>

            <div class="p-3.5 flex items-center justify-between gap-2 text-xs bg-gray-50/50 border-t border-gray-100">
              <span class="text-gray-500 font-mono">Pos #{{ $banner->position }}</span>
              <div class="flex items-center gap-1.5">
                <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $banner->is_active ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    {{ $banner->is_active ? 'Hide' : 'Show' }}
                  </button>
                </form>
                <a href="{{ route('admin.banners.edit', $banner) }}" class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-bold border border-gray-200 hover:bg-gray-200 transition">Edit</a>
                <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete this hero slide?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 font-bold border border-rose-200 hover:bg-rose-100 transition">Del</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>

<script>
function updatePreview(hex) {
  const badge = document.getElementById('previewBadge');
  const price = document.getElementById('previewPrice');
  const button = document.getElementById('previewButton');
  if (badge) badge.style.backgroundColor = hex;
  if (price) price.style.color = hex;
  if (button) button.style.backgroundColor = hex;
}

function setPreset(hex) {
  document.getElementById('primaryColorPicker').value = hex;
  document.getElementById('primaryHexInput').value = hex;
  updatePreview(hex);
}
</script>
@endsection
