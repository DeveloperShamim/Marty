@extends('layouts.admin')
@section('title', 'Admin Profile & Security')

@section('content')
<div class="max-w-5xl mx-auto space-y-5 sm:space-y-6">

  {{-- Top Header Ribbon --}}
  <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
      <div class="relative group shrink-0">
        @if($user->avatarUrl())
          <img id="headerAvatarImg" src="{{ $user->avatarUrl() }}" class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl object-cover border-2 border-stone-200 shadow-md" alt="{{ $user->name }}">
        @else
          <div id="headerAvatarPlaceholder" class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-emerald-800 text-white font-black text-xl flex items-center justify-center shadow-md">
            {{ strtoupper(substr($user->name ?? 'A', 0, 2)) }}
          </div>
        @endif
        <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-emerald-500 border-2 border-white ring-1 ring-emerald-300"></span>
      </div>

      <div class="min-w-0 space-y-0.5">
        <div class="flex items-center gap-2 flex-wrap">
          <h1 class="text-lg sm:text-2xl font-extrabold text-stone-900 tracking-tight truncate">
            {{ $user->name }}
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
            ● {{ ucfirst(str_replace('_', ' ', $user->role ?? 'Super Admin')) }}
          </span>
        </div>
        <p class="text-xs text-stone-500 font-medium">
          {{ $user->email }} &middot; Member since {{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
      <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 font-extrabold text-xs border border-stone-200 shadow-2xs transition">
        &larr; Dashboard
      </a>
    </div>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold shadow-2xs flex items-center gap-2">
      <span class="text-emerald-700 text-base">✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-extrabold shadow-2xs space-y-1">
      <p class="text-rose-950 font-black">⚠️ Please correct the following errors:</p>
      <ul class="list-disc list-inside space-y-0.5 font-semibold text-rose-800">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Main Grid: 2 Columns on Large Screens --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">

    {{-- Left Column: Account Details & Profile Picture (7 Cols) --}}
    <div class="lg:col-span-7 bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 sm:p-7 shadow-2xs space-y-6">
      <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center font-bold text-lg shrink-0 border border-emerald-100 shadow-2xs">
          👤
        </div>
        <div>
          <h2 class="font-extrabold text-sm sm:text-base text-stone-900 leading-snug">Personal Profile &amp; Photo</h2>
          <p class="text-xs text-stone-500">Update your avatar picture, display name, and contact details.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Profile Picture Uploader with Realtime Preview --}}
        <div class="p-4 rounded-2xl bg-stone-50/80 border border-stone-200/80 space-y-3">
          <label class="block text-xs font-black text-stone-800">Profile Picture / Avatar</label>
          
          <div class="flex items-center gap-4 flex-wrap sm:flex-nowrap">
            {{-- Preview Box --}}
            <div class="relative shrink-0">
              <img id="avatarPreviewImg" src="{{ $user->avatarUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'1.5\'><path d=\'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2\'/><circle cx=\'12\' cy=\'7\' r=\'4\'/></svg>' }}"
                   class="h-20 w-20 rounded-2xl object-cover border-2 border-stone-200 bg-white shadow-xs {{ $user->avatarUrl() ? '' : 'p-3 bg-stone-100' }}"
                   alt="Avatar Preview">
            </div>

            <div class="space-y-2 flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <label for="avatarInput" class="px-3.5 py-2 text-xs font-extrabold rounded-xl bg-white hover:bg-stone-100 text-stone-800 border border-stone-200 shadow-2xs transition cursor-pointer flex items-center gap-1.5 shrink-0">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                  <span>Upload Photo</span>
                </label>
                <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="hidden" onchange="previewAvatar(this)">

                @if($user->avatar)
                  <label class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition cursor-pointer">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500 h-3.5 w-3.5" onchange="handleRemoveAvatar(this)">
                    <span>Remove Photo</span>
                  </label>
                @endif
              </div>
              <p class="text-[11px] text-stone-400">Supported: JPG, PNG, WEBP. Max size: 5MB.</p>
            </div>
          </div>
        </div>

        {{-- Form Fields --}}
        <div class="space-y-4">
          <div class="space-y-1">
            <label for="name" class="block text-xs font-black text-stone-800">Full Display Name <span class="text-rose-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" />
          </div>

          <div class="space-y-1">
            <label for="email" class="block text-xs font-black text-stone-800">Admin Email Address (Login Username) <span class="text-rose-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="phone" class="block text-xs font-black text-stone-800">Phone Number</label>
              <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+880 1700-000000"
                     class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" />
            </div>

            <div class="space-y-1">
              <label for="city" class="block text-xs font-black text-stone-800">City / District</label>
              <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="e.g. Dhaka"
                     class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" />
            </div>
          </div>

          <div class="space-y-1">
            <label for="address" class="block text-xs font-black text-stone-800">Office / Physical Address</label>
            <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="e.g. House 12, Road 4, Banani"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" />
          </div>
        </div>

        <div class="pt-2 border-t border-stone-100">
          <button type="submit" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-2">
            <span>💾</span>
            <span>Save Profile &amp; Photo Changes</span>
          </button>
        </div>
      </form>
    </div>

    {{-- Right Column: Security & Credentials (5 Cols) --}}
    <div class="lg:col-span-5 bg-white rounded-2xl sm:rounded-3xl border border-stone-200 p-5 sm:p-7 shadow-2xs space-y-6 flex flex-col justify-between">
      <div class="space-y-5">
        <div class="flex items-center gap-3 border-b border-stone-100 pb-4">
          <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center font-bold text-lg shrink-0 border border-amber-100 shadow-2xs">
            🔒
          </div>
          <div>
            <h2 class="font-extrabold text-sm sm:text-base text-stone-900 leading-snug">Security &amp; Password</h2>
            <p class="text-xs text-stone-500">Update your secure login password.</p>
          </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
          @csrf
          @method('PUT')

          <div class="space-y-1">
            <label for="current_password" class="block text-xs font-black text-stone-800">Current Password <span class="text-rose-500">*</span></label>
            <div class="relative">
              <input type="password" id="current_password" name="current_password" required placeholder="••••••••"
                     class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" />
              <button type="button" onclick="togglePass('current_password', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs cursor-pointer font-bold">👁️</button>
            </div>
          </div>

          <div class="space-y-1">
            <label for="password" class="block text-xs font-black text-stone-800">New Password (Min 8 chars) <span class="text-rose-500">*</span></label>
            <div class="relative">
              <input type="password" id="password" name="password" required placeholder="••••••••"
                     class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" />
              <button type="button" onclick="togglePass('password', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs cursor-pointer font-bold">👁️</button>
            </div>
          </div>

          <div class="space-y-1">
            <label for="password_confirmation" class="block text-xs font-black text-stone-800">Confirm New Password <span class="text-rose-500">*</span></label>
            <div class="relative">
              <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••"
                     class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs pr-10" />
              <button type="button" onclick="togglePass('password_confirmation', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-700 text-xs cursor-pointer font-bold">👁️</button>
            </div>
          </div>

          <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-stone-900 hover:bg-stone-800 text-white font-extrabold text-xs rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-2">
              <span>🔑</span>
              <span>Update Password Credentials</span>
            </button>
          </div>
        </form>
      </div>

      {{-- Account Security Audit Overview --}}
      <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-2 text-xs">
        <span class="text-[10px] font-black uppercase tracking-wider text-stone-400 block">Security Overview</span>
        <div class="flex items-center justify-between text-stone-700">
          <span>Account ID:</span>
          <span class="font-mono font-bold">#{{ $user->id }}</span>
        </div>
        <div class="flex items-center justify-between text-stone-700">
          <span>Last Updated:</span>
          <span class="font-bold">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/A' }}</span>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const preview = document.getElementById('avatarPreviewImg');
      if (preview) {
        preview.src = e.target.result;
        preview.classList.remove('p-3', 'bg-stone-100');
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function handleRemoveAvatar(checkbox) {
  const preview = document.getElementById('avatarPreviewImg');
  if (!preview) return;
  if (checkbox.checked) {
    preview.style.opacity = '0.3';
  } else {
    preview.style.opacity = '1';
  }
}

function togglePass(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerText = '🙈';
  } else {
    input.type = 'password';
    btn.innerText = '👁️';
  }
}
</script>
@endpush
