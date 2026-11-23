@if($page->sectionOn('faq') && $faqs->isNotEmpty())
  <section class="py-10 sm:py-14" style="background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));" data-lp-section="faq">
    <div class="max-w-2xl mx-auto px-4">
      @if(filled($d['faq']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['faq']['heading'] }}</h2>@endif
      @if(filled($d['faq']['subtitle'] ?? null))<p class="mt-1 text-center text-sm text-gray-500">{{ $d['faq']['subtitle'] }}</p>@endif
      <div class="mt-6 space-y-3">
        @foreach($faqs as $f)
          <details class="group rounded-xl border border-gray-200 bg-white px-4 py-3">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-semibold">
              <span>{{ $f['q'] }}</span>
              <span class="lp-plus lp-accent text-xl leading-none transition-transform">+</span>
            </summary>
            <p class="mt-2 text-[15px] leading-relaxed text-gray-600">{{ $f['a'] }}</p>
          </details>
        @endforeach
      </div>
    </div>
  </section>
@endif
