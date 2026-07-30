@extends('layouts.storefront')
@php $title = 'Create Account'; @endphp

@section('content')
<main class="max-w-md mx-auto px-4 sm:px-6 py-12">
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xl overflow-hidden">
    <!-- Header Tabs -->
    <div class="grid grid-cols-2 text-center font-bold text-sm bg-slate-50 border-b border-slate-200">
      <a href="{{ route('login') }}" class="py-4 text-slate-500 hover:text-slate-900 transition-colors">Login</a>
      <span class="py-4 bg-white text-brand-600 border-b-2 border-brand-600">Register</span>
    </div>

    <div class="p-6 sm:p-8">
      <!-- Google 1-Click Login Button -->
      <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-3 px-4 rounded-xl shadow-sm hover:shadow transition-all text-sm mb-4">
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
        <span>Sign up with Google</span>
      </a>

      <!-- Divider -->
      <div class="relative my-6 text-center text-xs text-slate-400 font-medium">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <span class="relative bg-white px-3 text-slate-400 uppercase tracking-wider text-[11px]">or register with email</span>
      </div>

      <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        @if($errors->any())
          <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-3.5 py-2.5 rounded-xl">
            <ul class="list-disc list-inside space-y-1">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Full Name</label>
          <input name="name" value="{{ old('name') }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="Enter your full name" required />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number <span class="text-slate-400 font-normal lowercase">(optional)</span></label>
          <input name="phone" value="{{ old('phone') }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="01XXXXXXXXX" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="you@example.com" required />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
          <div class="relative">
            <input type="password" id="register_password" name="password" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="At least 8 characters" required />
            <button type="button" onclick="togglePass('register_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" aria-label="Toggle Password Visibility">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password</label>
          <div class="relative">
            <input type="password" id="register_password_confirmation" name="password_confirmation" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="Repeat your password" required />
            <button type="button" onclick="togglePass('register_password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" aria-label="Toggle Password Visibility">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-brand-500/25 transition-all text-sm mt-2">
          Create Account
        </button>
      </form>
    </div>
  </div>
</main>

<script>
  function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
      input.type = 'text';
      btn.innerHTML = `<svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.016 10.016 0 013.122-.463c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-3.32-3.32a3 3 0 11-4.243-4.243M3 3l18 18"/></svg>`;
    } else {
      input.type = 'password';
      btn.innerHTML = `<svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
    }
  }
</script>
@endsection
