@extends('layouts.storefront')
@php $title = 'Account Login'; @endphp

@section('content')
<main class="max-w-md mx-auto px-4 sm:px-6 py-12">
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xl overflow-hidden">
    <!-- Header Tabs -->
    <div class="grid grid-cols-2 text-center font-bold text-sm bg-slate-50 border-b border-slate-200">
      <span class="py-4 bg-white text-brand-600 border-b-2 border-brand-600">Login</span>
      <a href="{{ route('register') }}" class="py-4 text-slate-500 hover:text-slate-900 transition-colors">Register</a>
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
        <span>Continue with Google</span>
      </a>

      <!-- Divider -->
      <div class="relative my-6 text-center text-xs text-slate-400 font-medium">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <span class="relative bg-white px-3 text-slate-400 uppercase tracking-wider text-[11px]">or login with email</span>
      </div>

      <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        @if($errors->any())
          <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-3.5 py-2.5 rounded-xl">
            {{ $errors->first() }}
          </div>
        @endif

        @if(session('status'))
          <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs px-3.5 py-2.5 rounded-xl">
            {{ session('status') }}
          </div>
        @endif

        @if(testing_mode())
          <div class="mb-4 overflow-hidden rounded-xl shadow-sm ring-1 ring-teal-600/30 bg-teal-50/50 p-3 text-xs text-slate-700">
            <div class="font-bold text-teal-800 uppercase tracking-wider text-[10px] mb-1">Testing Mode ON</div>
            <p><span class="text-slate-500">Email:</span> <code class="font-mono bg-white px-1 rounded border">{{ config('app.testing_customer_email') }}</code></p>
            <p class="mt-0.5"><span class="text-slate-500">Password:</span> <code class="font-mono bg-white px-1 rounded border">{{ config('app.testing_customer_password') }}</code></p>
          </div>
        @endif

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
          <input type="email" name="email" value="{{ old('email', testing_mode() ? config('app.testing_customer_email') : '') }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="you@example.com" required autofocus />
        </div>

        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:underline">Forgot password?</a>
          </div>
          <div class="relative">
            <input type="password" id="login_password" name="password" value="{{ testing_mode() ? config('app.testing_customer_password') : '' }}" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all" placeholder="••••••••" required />
            <button type="button" onclick="toggleLoginPass('login_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" aria-label="Toggle Password Visibility">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
            <span>Remember me on this device</span>
          </label>
        </div>

        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-brand-500/25 transition-all text-sm">
          Account Login
        </button>
      </form>
    </div>
  </div>
</main>

<script>
  function toggleLoginPass(id, btn) {
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
