@extends('layouts.admin')
@section('title', 'Profile')
@section('subtitle', 'Your photo, contact details and password.')

@section('page-actions')
  <a href="{{ \App\Support\StaffAccess::home(auth()->user()) }}" class="pill-btn">Back</a>
@endsection

@php
  $eyeIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
@endphp

@section('content')
<div class="max-w-5xl space-y-4">

  {{-- Account summary --}}
  <section class="panel p-4 sm:p-5 flex items-center gap-3.5 min-w-0">
    <div class="relative shrink-0">
      @if($user->avatarUrl())
        <img id="headerAvatarImg" src="{{ $user->avatarUrl() }}" class="h-12 w-12 rounded-2xl object-cover bg-gray-100" alt="{{ $user->name }}">
      @else
        <div id="headerAvatarPlaceholder" class="h-12 w-12 rounded-2xl text-white font-semibold text-base grid place-items-center" style="background: var(--brand-dark);">
          {{ strtoupper(substr($user->name ?? 'A', 0, 2)) }}
        </div>
      @endif
      <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
    </div>
    <div class="min-w-0 flex-1">
      <div class="flex items-center gap-2 flex-wrap">
        <p class="text-[15px] font-semibold text-gray-900 truncate">{{ $user->name }}</p>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'Super Admin')) }}</span>
      </div>
      <p class="text-xs text-gray-500 truncate mt-0.5">{{ $user->email }} &middot; Member since {{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}</p>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

    {{-- Profile details --}}
    <section class="panel p-4 sm:p-5 lg:col-span-7">
      <h2 class="text-[15px] font-semibold text-gray-900">Personal details</h2>
      <p class="text-xs text-gray-500 mt-0.5">Your photo, display name and contact details.</p>

      <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3.5 flex-wrap sm:flex-nowrap">
          <img id="avatarPreviewImg" src="{{ $user->avatarUrl() ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'1.5\'><path d=\'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2\'/><circle cx=\'12\' cy=\'7\' r=\'4\'/></svg>' }}"
               class="h-16 w-16 rounded-2xl object-cover bg-white ring-1 ring-gray-100 shrink-0 {{ $user->avatarUrl() ? '' : 'p-3 bg-stone-100' }}"
               alt="Avatar Preview">
          <div class="space-y-1.5 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <label for="avatarInput" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center gap-1.5 cursor-pointer transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
                Upload photo
              </label>
              <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="hidden" onchange="previewAvatar(this)">

              @if($user->avatar)
                <label class="h-9 px-3.5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-[13px] font-medium inline-flex items-center gap-1.5 cursor-pointer transition-colors">
                  <input type="checkbox" name="remove_avatar" value="1" class="h-3.5 w-3.5 rounded" onchange="handleRemoveAvatar(this)">
                  Remove photo
                </label>
              @endif
            </div>
            <p class="text-[11px] text-gray-400">JPG, PNG or WEBP, up to 5 MB.</p>
          </div>
        </div>

        <div>
          <label for="name" class="lbl">Display name <span class="text-rose-500">*</span></label>
          <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="inp" />
        </div>

        <div>
          <label for="email" class="lbl">Email (used to sign in) <span class="text-rose-500">*</span></label>
          <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="inp" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="phone" class="lbl">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+880 1700-000000" class="inp" />
          </div>
          <div>
            <label for="city" class="lbl">City / district</label>
            <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="e.g. Dhaka" class="inp" />
          </div>
        </div>

        <div>
          <label for="address" class="lbl">Address</label>
          <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="e.g. House 12, Road 4, Banani" class="inp" />
        </div>

        <div class="pt-1 flex justify-end">
          <button type="submit" class="w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5" style="background: var(--brand-dark);">
            Save profile
          </button>
        </div>
      </form>
    </section>

    {{-- Password --}}
    <div class="lg:col-span-5 space-y-4">
      <section class="panel p-4 sm:p-5">
        <h2 class="text-[15px] font-semibold text-gray-900">Password</h2>
        <p class="text-xs text-gray-500 mt-0.5">Use at least 8 characters.</p>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="mt-4 space-y-4">
          @csrf
          @method('PUT')

          @foreach([
            ['current_password', 'Current password'],
            ['password', 'New password'],
            ['password_confirmation', 'Confirm new password'],
          ] as [$fid, $flabel])
            <div>
              <label for="{{ $fid }}" class="lbl">{{ $flabel }} <span class="text-rose-500">*</span></label>
              <div class="relative">
                <input type="password" id="{{ $fid }}" name="{{ $fid }}" required placeholder="••••••••" class="inp pr-11" />
                <button type="button" onclick="togglePass('{{ $fid }}', this)" class="absolute right-1.5 top-1/2 -translate-y-1/2 h-8 w-8 grid place-items-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100" aria-label="Show or hide password">{!! $eyeIcon !!}</button>
              </div>
            </div>
          @endforeach

          <div class="pt-1 flex justify-end">
            <button type="submit" class="w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5" style="background: var(--brand-dark);">
              Update password
            </button>
          </div>
        </form>
      </section>

      <section class="panel p-4 sm:p-5 text-[13px] space-y-2">
        <h2 class="text-[15px] font-semibold text-gray-900">Account</h2>
        <div class="flex items-center justify-between text-gray-600">
          <span>Account ID</span>
          <span class="font-medium text-gray-900 tabular-nums">#{{ $user->id }}</span>
        </div>
        <div class="flex items-center justify-between text-gray-600">
          <span>Last updated</span>
          <span class="font-medium text-gray-900">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/A' }}</span>
        </div>
      </section>
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

const EYE_ICON = @json($eyeIcon);
const EYE_OFF_ICON = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.9 4.2A10 10 0 0 1 12 4c6.5 0 10 7 10 7a17 17 0 0 1-2.2 3.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/></svg>';

function togglePass(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = EYE_OFF_ICON;
  } else {
    input.type = 'password';
    btn.innerHTML = EYE_ICON;
  }
}
</script>
@endpush
