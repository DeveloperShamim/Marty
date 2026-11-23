{{-- Classic: one centred column, the original landing page --}}
<header class="max-w-3xl mx-auto px-4 pt-6 sm:pt-8 text-center">
  <div class="flex justify-center">@include('partials.brand', ['size' => 'md'])</div>
  @include('storefront.landing.parts.headline')
</header>
@include('storefront.landing.parts.custom', ['at' => 'hero'])

<section class="max-w-xl mx-auto px-4 mt-6" data-lp-section="gallery">
  @include('storefront.landing.parts.gallery')
  @include('storefront.landing.parts.price')
</section>

@foreach(['video', 'features', 'specs', 'reviews', 'faq', 'contact'] as $part)
  @include('storefront.landing.parts.' . $part)
  @include('storefront.landing.parts.custom', ['at' => $part])
@endforeach

@include('storefront.landing.parts.form')
@include('storefront.landing.parts.custom', ['at' => 'form'])
