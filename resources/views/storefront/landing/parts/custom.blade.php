{{-- The admin's own HTML blocks placed at this spot ($at). Each runs in a sandboxed frame with no access to the
     shop's cookies, session or page, and grows to fit its content (it reports its height to this page). --}}
@foreach($customBlocks as $ci => $block)
  @if(($block['at'] ?? '') === $at && trim($block['html'] ?? '') !== '')
    <section class="lp-custom" data-lp-section="custom" data-lp-custom-at="{{ $at }}">
      <iframe class="block w-full border-0" style="height: 0;" title="{{ $page->title }}"
              sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox"
              data-lp-custom="{{ $ci }}"
              srcdoc="{{ \App\Models\LandingPage::customDocument($block['html'], $ci) }}"></iframe>
    </section>
  @endif
@endforeach
