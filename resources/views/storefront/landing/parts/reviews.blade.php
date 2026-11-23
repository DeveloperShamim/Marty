@if($page->sectionOn('reviews') && $reviewImages->isNotEmpty())
  <section class="bg-gray-50 py-10 sm:py-14" data-lp-section="reviews">
    <div class="max-w-5xl mx-auto px-4">
      @if(filled($d['reviews']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['reviews']['heading'] }}</h2>@endif
      @if(filled($d['reviews']['subtitle'] ?? null))<p class="mt-1 text-center text-sm text-gray-500">{{ $d['reviews']['subtitle'] }}</p>@endif
      <div class="mt-6 flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2">
        @foreach($reviewImages as $img)
          <img src="{{ image_url($img, 'review') }}" alt="কাস্টমার রিভিউ" class="h-72 w-auto shrink-0 snap-center rounded-xl border border-gray-200 bg-white object-contain" loading="lazy">
        @endforeach
      </div>
    </div>
  </section>
@endif
