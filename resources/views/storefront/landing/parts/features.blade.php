@if($page->sectionOn('features') && $features->isNotEmpty())
  <section class="py-10 sm:py-14" style="background: color-mix(in srgb, var(--lp) 6%, var(--lp-base));" data-lp-section="features">
    <div class="max-w-4xl mx-auto px-4">
      @if(filled($d['features']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['features']['heading'] }}</h2>@endif
      <div class="mt-8 space-y-8">
        @foreach($features as $i => $f)
          @php($img = $f['image'] ? image_url($f['image'], $f['title'] ?? '') : ($gallery[$i] ?? null))
          <div class="grid items-center gap-5 sm:grid-cols-2 sm:gap-10">
            @if($img)
              <div class="{{ $i % 2 ? 'sm:order-2' : '' }} aspect-square overflow-hidden rounded-2xl bg-white shadow-sm">
                <img src="{{ $img }}" alt="{{ $f['title'] }}" class="h-full w-full object-cover" loading="lazy">
              </div>
            @endif
            <div class="{{ $img ? '' : 'sm:col-span-2' }}">
              <h3 class="text-lg sm:text-xl font-bold">{{ $f['title'] }}</h3>
              <p class="mt-2 text-[15px] leading-relaxed text-gray-600">{{ $f['text'] }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endif
