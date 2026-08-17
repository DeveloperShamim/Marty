@extends('layouts.admin')
@section('title', 'Admin Profile & Security')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

  {{-- Page Header --}}
  <div class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
      <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white font-black text-xl flex items-center justify-center shadow-md shrink-0">
        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
      </div>
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>👤 My Admin Account Profile</span>
        </h1>
        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">Manage your account email address, phone contact, and security password credentials</p>
      </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>{{ ucfirst(str_replace('_', ' ', $user->role ?? 'Super Admin')) }}</span>
      </span>
    </div>
  </div>

  {{-- Status Alert Messages --}}
  @if(session('status'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold shadow-2xs flex items-center gap-2">
      <span class="text-emerald-600 text-base">✓</span>
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

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Card 1: Update Email & Profile Information --}}
    <div class="bg-white rounded-2xl border border-stone-200 p-4 sm:p-6 shadow-2xs space-y-5">
      <div class="flex items-center gap-3 border-b border-stone-100 pb-3.5">
        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-lg shrink-0">
          ✉️
        </div>
        <div>
          <h2 class="font-extrabold text-base text-stone-900 leading-snug">Account &amp; Contact Details</h2>
          <p class="text-xs text-stone-500">Update your primary admin login email and full name.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label for="name" class="block text-xs font-extrabold text-stone-800 mb-1.5">Full Display Name</label>
          <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-extrabold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs" />
        </div>

        <div>
          <label for="email" class="block text-xs font-extrabold text-stone-800 mb-1.5">Admin Email Address (Login Username)</label>
          <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs" />
          <p class="text-[10px] text-stone-400 mt-1 font-medium">Default Super Admin: <code class="bg-stone-100 px-1.5 py-0.5 rounded text-brand-700 font-mono font-bold">admin@shodeshifood.com</code></p>
        </div>

        <div>
          <label for="phone" class="block text-xs font-extrabold text-stone-800 mb-1.5">Phone Number (Optional)</label>
          <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs" placeholder="+880 1700-000000" />
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-md cursor-pointer">
            💾 Save Profile Changes
          </button>
        </div>
      </form>
    </div>

    {{-- Card 2: Update Security & Password Credentials --}}
    <div class="bg-white rounded-2xl border border-stone-200 p-4 sm:p-6 shadow-2xs space-y-5">
      <div class="flex items-center gap-3 border-b border-stone-100 pb-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-lg shrink-0">
          🔒
        </div>
        <div>
          <h2 class="font-extrabold text-base text-stone-900 leading-snug">Change Password Credentials</h2>
          <p class="text-xs text-stone-500">Update your secure password for super admin access.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label for="current_password" class="block text-xs font-extrabold text-stone-800 mb-1.5">Current Password</label>
          <div class="relative">
            <input type="password" id="current_password" name="current_password" required
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs pr-10" placeholder="••••••••" />
            <button type="button" onclick="togglePassVisibility('current_password', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold" title="Toggle password visibility">👁️</button>
          </div>
        </div>

        <div>
          <label for="password" class="block text-xs font-extrabold text-stone-800 mb-1.5">New Password (Min 8 characters)</label>
          <div class="relative">
            <input type="password" id="password" name="password" required
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs pr-10" placeholder="••••••••" />
            <button type="button" onclick="togglePassVisibility('password', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold" title="Toggle password visibility">👁️</button>
          </div>
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-extrabold text-stone-800 mb-1.5">Confirm New Password</label>
          <div class="relative">
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-bold focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-2xs pr-10" placeholder="••••••••" />
            <button type="button" onclick="togglePassVisibility('password_confirmation', this)" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600 text-xs font-bold" title="Toggle password visibility">👁️</button>
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-3 px-4 bg-stone-900 hover:bg-stone-800 text-white font-extrabold text-xs rounded-xl transition-all shadow-md cursor-pointer">
            🔑 Update Password Credentials
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassVisibility(inputId, btn) {
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
