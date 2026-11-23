{{-- Badge and the three-part headline (part 2 in the accent colour) --}}
@if(filled($hero['badge']))
  <span class="mt-5 inline-block rounded-full border px-4 py-1 text-[13px] sm:text-sm font-semibold lp-accent" style="border-color: var(--lp);">{{ $hero['badge'] }}</span>
@endif
<h1 class="mt-4 text-[26px] leading-snug sm:text-4xl sm:leading-tight font-bold text-gray-900">
  {{ $hero['part1'] }}
  @if(filled($hero['part2']))<span class="lp-accent">{{ $hero['part2'] }}</span>@endif
  {{ $hero['part3'] }}
</h1>
