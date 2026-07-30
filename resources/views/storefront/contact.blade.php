@extends('layouts.storefront')

@section('content')
  <main class="max-w-7xl mx-auto px-4 py-6">
    <nav class="text-sm text-gray-500 mb-4">
      <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a> / Contact
    </nav>
    <div class="max-w-3xl">
      <h1 class="text-2xl font-extrabold mb-1">{{ setting('contact_title', 'Get in touch') }}</h1>
      <p class="text-gray-500 mb-6">{{ setting('contact_intro', '') }}</p>

      <div class="grid sm:grid-cols-2 gap-4">
        @if(setting('contact_address'))
          <div class="bg-white border border-gray-200 rounded-md p-6">
            <h3 class="font-semibold mb-2">Address</h3>
            <p class="text-sm text-gray-600">{{ setting('contact_address') }}</p>
          </div>
        @endif
        @if(setting('contact_phone'))
          <div class="bg-white border border-gray-200 rounded-md p-6">
            <h3 class="font-semibold mb-2">Phone</h3>
            <p class="text-sm text-gray-600">
              <a href="tel:{{ preg_replace('/\s+/', '', (string) setting('contact_phone')) }}" class="hover:text-brand-600">{{ setting('contact_phone') }}</a>
            </p>
          </div>
        @endif
        @if(setting('contact_email'))
          <div class="bg-white border border-gray-200 rounded-md p-6">
            <h3 class="font-semibold mb-2">Email</h3>
            <p class="text-sm text-gray-600">
              <a href="mailto:{{ setting('contact_email') }}" class="hover:text-brand-600">{{ setting('contact_email') }}</a>
            </p>
          </div>
        @endif
        @if(trim((string) setting('contact_hours', '')) !== '')
          <div class="bg-white border border-gray-200 rounded-md p-6">
            <h3 class="font-semibold mb-2">Hours</h3>
            <p class="text-sm text-gray-600">{{ setting('contact_hours') }}</p>
          </div>
        @endif
      </div>

      @if(! setting('contact_address') && ! setting('contact_phone') && ! setting('contact_email') && trim((string) setting('contact_hours', '')) === '')
        <p class="text-sm text-gray-500">Contact details are not configured yet.</p>
      @endif
    </div>
  </main>
@endsection
