{{-- The one heading used by every homepage section.
     Default: title on the left over a thin line, with "View all items" (when an href is given) and anything passed in on the right.
     centered: the title in the middle with a short orange underline (Featured Categories, Top Selling Products). --}}
@props(['title', 'href' => null, 'linkLabel' => null, 'centered' => false])
@if($centered)
  <div {{ $attributes->merge(['class' => 'gb-heading-center']) }} data-home-heading>
    <h2>{{ $title }}</h2>
  </div>
@else
  <div {{ $attributes->merge(['class' => 'gb-heading-row']) }} data-home-heading>
    <h2>{{ $title }}</h2>
    @if($href || trim($slot) !== '')
      <div class="flex items-center gap-2 shrink-0">
        {{ $slot }}
        @if($href)
          <a href="{{ $href }}" class="gb-view-all" data-see-all>{{ $linkLabel ?? setting('home_view_more_label', 'View all items') }}</a>
        @endif
      </div>
    @endif
  </div>
@endif
