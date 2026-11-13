{{-- Homepage section, included in the order set in Admin → Homepage → Sections (App\Support\HomeSections) --}}
{{-- 8. VERIFIED CUSTOMER REVIEWS & SOCIAL PROOF --}}
@if($homeReviews->isNotEmpty())
  <section class="mt-10 sm:mt-14" data-reveal>
    <x-home-heading :title="setting('home_reviews_title', 'Customer Feedback')">
      <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-900 text-[11px] sm:text-xs font-bold shadow-2xs whitespace-nowrap">
        <span class="text-amber-500 tracking-wider">★★★★★</span>
        <span>Real Verified Buyers</span>
      </div>
    </x-home-heading>

    {{-- Review Cards: swipe row on phones, 2 columns on tablets, 3 on desktop --}}
    @php $reviewCols = $homeReviews->count() % 3 === 0 ? 'lg:grid-cols-3' : 'lg:grid-cols-2'; @endphp
    <div class="flex md:grid md:grid-cols-2 {{ $reviewCols }} gap-3.5 sm:gap-5 overflow-x-auto md:overflow-visible snap-x snap-mandatory no-scrollbar pb-3 pt-1 -mx-3 px-3 sm:mx-0 sm:px-0">
      @foreach($homeReviews as $review)
        <div class="w-[85vw] max-w-[340px] sm:w-[360px] md:w-auto md:max-w-none shrink-0 md:shrink min-w-0 snap-center relative overflow-hidden rounded-2xl border border-stone-200/80 bg-white p-4 sm:p-6 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
          <div>
            <div class="flex items-center justify-between gap-2 mb-2.5">
              <div class="flex items-center gap-1 text-amber-400 text-sm">
                @php $rating = (int) ($review->rating ?: 5); @endphp
                @for($i = 1; $i <= 5; $i++)
                  <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ $i <= $rating ? 'text-amber-400 fill-amber-400' : 'text-stone-200 fill-stone-200' }}" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                @endfor
                <span class="text-xs font-extrabold text-stone-800 ml-1">{{ $rating }}.0</span>
              </div>
              <span class="text-[10px] sm:text-[11px] font-medium text-stone-400">{{ $review->created_at?->diffForHumans() ?? 'Verified' }}</span>
            </div>

            <p class="text-stone-700 text-xs sm:text-sm leading-relaxed line-clamp-4">
              “{{ $review->body }}”
            </p>
            @if($review->product)
              <a href="{{ route('product.show', $review->product) }}" class="mt-2.5 inline-flex max-w-full items-center gap-1 text-[11px] font-medium text-stone-500 hover:text-brand-700">
                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="truncate">{{ $review->product->name }}</span>
              </a>
            @endif
          </div>

          <div class="mt-4 pt-3 sm:pt-4 border-t border-stone-100 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
              @php
                $initials = collect(preg_split('/\s+/', trim((string) $review->author_name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
              @endphp
              <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-stone-100 border border-stone-200 text-stone-800 font-extrabold text-[11px] sm:text-xs grid place-items-center shrink-0">
                {{ $initials ?: 'U' }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-bold text-xs sm:text-sm text-stone-900 truncate leading-tight">{{ $review->author_name }}</p>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 whitespace-nowrap">
                  <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                  Verified Buyer
                </span>
              </div>
            </div>

          </div>
        </div>
      @endforeach
    </div>

    {{-- Mobile Swipe Hint --}}
    <div class="flex md:hidden items-center justify-center gap-1.5 mt-2.5 text-stone-400">
      <span class="text-[10px] font-bold tracking-wider uppercase flex items-center gap-1 text-stone-400">
        <span>Swipe for more reviews</span>
        <svg class="w-3 h-3 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </span>
    </div>
  </section>
@endif
