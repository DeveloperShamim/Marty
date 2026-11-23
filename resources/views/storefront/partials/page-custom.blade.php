{{-- An admin's custom HTML blocks on a store page. Each runs in a sandboxed frame with no access to the
     shop's cookies, session or page, and grows to fit its content (it reports its height to this page). --}}
@foreach($blocks as $ci => $block)
  <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 {{ $spot === 'above' ? 'pt-10 -mb-4' : 'pb-10 -mt-4' }}" data-page-custom-at="{{ $spot }}">
    <iframe class="block w-full border-0" style="height: 0;" title="{{ $heading }}"
            sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox"
            data-lp-custom="{{ $spot }}-{{ $ci }}"
            srcdoc="{{ \App\Models\LandingPage::customDocument($block['html'], $ci) }}"></iframe>
  </div>
@endforeach
