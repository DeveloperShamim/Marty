@if($page->sectionOn('video') && $videoId)
  <section class="mt-12 bg-gray-50 py-10 sm:py-14" data-lp-section="video">
    <div class="max-w-3xl mx-auto px-4">
      @if(filled($d['video']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['video']['heading'] }}</h2>@endif
      <div class="mt-6 aspect-video overflow-hidden rounded-2xl bg-black shadow">
        <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0" title="{{ $d['video']['heading'] ?: $page->title }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
    </div>
  </section>
@endif
