@extends('layouts.admin')
@section('title', 'Admin Account Profile & Security')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-stone-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2.5">
        <span>👤</span> My Admin Profile & Credentials
      </h2>
      <p class="text-xs text-stone-500 mt-1">Manage your Super Admin account email address, password credentials, and security profile.</p>
    </div>

    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
        {{ ucfirst(str_replace('_', ' ', $user->role ?? 'Admin')) }}
      </span>
    </div>
  </div>

  <!-- Status Alert Messages -->
  @if(session('status'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs flex items-center gap-2">
      <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>✓ {{ session('status') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-xs">
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Card 1: Update Email & Profile Information -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-xs space-y-5">
      <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-base shrink-0">
          ✉️
        </div>
        <div>
          <h3 class="font-extrabold text-base text-stone-900 leading-snug">Account & Email Details</h3>
          <p class="text-[11px] text-stone-500">Update your primary admin login email and display name.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label for="name" class="block text-xs font-bold text-stone-700 mb-1">Full Name</label>
          <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
        </div>

        <div>
          <label for="email" class="block text-xs font-bold text-stone-700 mb-1">Admin Email Address (Login Email)</label>
          <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" />
          <p class="text-[10px] text-stone-400 mt-1">Default Super Admin Email: <code class="bg-stone-100 px-1 py-0.5 rounded text-brand-600 font-mono">admin@solebd.com</code></p>
        </div>

        <div>
          <label for="phone" class="block text-xs font-bold text-stone-700 mb-1">Phone Number (Optional)</label>
          <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" placeholder="+880 1700-000000" />
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl transition-colors shadow-xs cursor-pointer">
            Save Profile & Email Changes
          </button>
        </div>
      </form>
    </div>

    <!-- Card 2: Update Security & Password Credentials -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-xs space-y-5">
      <div class="flex items-center gap-3 border-b border-stone-100 pb-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base shrink-0">
          🔒
        </div>
        <div>
          <h3 class="font-extrabold text-base text-stone-900 leading-snug">Change Password</h3>
          <p class="text-[11px] text-stone-500">Update your secure login password credential.</p>
        </div>
      </div>

      <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
          <label for="current_password" class="block text-xs font-bold text-stone-700 mb-1">Current Password</label>
          <input type="password" id="current_password" name="current_password" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" placeholder="••••••••" />
        </div>

        <div>
          <label for="password" class="block text-xs font-bold text-stone-700 mb-1">New Password (Min 8 characters)</label>
          <input type="password" id="password" name="password" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" placeholder="••••••••" />
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-bold text-stone-700 mb-1">Confirm New Password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required
                 class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-stone-900 text-xs font-medium focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" placeholder="••••••••" />
        </div>

        <div class="pt-2">
          <button type="submit" class="w-full py-2.5 px-4 bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs rounded-xl transition-colors shadow-xs cursor-pointer">
            Update Password Credentials
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
