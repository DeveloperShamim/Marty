@extends('layouts.storefront', ['headerVariant' => 'compact'])

@section('content')
  <section class="bg-white border-b border-slate-100">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
      <nav class="text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
        <span class="mx-2 text-slate-300">/</span>
        <span class="text-ink font-medium">{{ $heading }}</span>
      </nav>
      <h1 class="mt-3 font-display text-3xl font-extrabold text-ink">{{ $heading }}</h1>
    </div>
  </section>
  @include('storefront.partials.page-custom', ['blocks' => $customAbove ?? [], 'spot' => 'above'])
  <section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="rounded-2xl bg-white border border-slate-100 p-6 sm:p-8 text-slate-600 leading-relaxed space-y-4 [&_h2]:text-lg [&_h2]:font-bold [&_h2]:text-ink [&_h2]:pt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1.5 [&_strong]:text-ink" data-store-page>
      @isset($bodyHtml)
        {{ $bodyHtml }}
      @else
        @foreach(preg_split('/\n\n+/', (string) $body) as $para)
          @if(trim($para))<p>{{ $para }}</p>@endif
        @endforeach
      @endisset
    </div>
  </section>
  @include('storefront.partials.page-custom', ['blocks' => $customBelow ?? [], 'spot' => 'below'])
@endsection

@if(!empty($customAbove) || !empty($customBelow))
  @push('scripts')
  <script>
    // Custom HTML frames report their height; only messages from those frames are used
    window.addEventListener('message', function (e) {
      var d = e.data;
      if (!d || typeof d.lpCustomHeight !== 'number') return;
      document.querySelectorAll('iframe[data-lp-custom]').forEach(function (f) {
        if (f.contentWindow === e.source) f.style.height = Math.min(6000, Math.max(20, Math.ceil(d.lpCustomHeight))) + 'px';
      });
    });
  </script>
  @endpush
@endif
