@extends('layouts.storefront', ['headerVariant' => 'compact'])
@php
  $title = 'Page not found · ' . site_name();
  $popularCategories = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('position')->take(6)->get();
  $suggestedProducts = \App\Models\Product::where('is_published', true)->with('images', 'category', 'brand', 'variants', 'skus')->inRandomOrder()->take(4)->get();
@endphp

@section('content')
<main class="bg-stone-50/60 pb-16">

  {{-- Message, search and the main ways back in --}}
  <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 sm:pt-14 pb-10 text-center">
    <p class="text-5xl sm:text-6xl font-extrabold font-display tracking-tight text-brand-600 leading-none">404</p>
    <h1 class="mt-4 text-xl sm:text-3xl font-bold font-display text-stone-900">Page not found</h1>
    <p class="mt-2 text-sm sm:text-base text-stone-500 max-w-md mx-auto">The link may be broken or the product has moved. Try a search, or pick up from one of the pages below.</p>

    <form action="{{ route('shop') }}" method="GET" class="mt-6 mx-auto max-w-md flex items-center gap-2 rounded-lg border border-brand-600 bg-white p-1 pl-3.5">
      <label for="nf-search" class="sr-only">Search products</label>
      <input id="nf-search" type="search" name="q" required placeholder="Type your product..." class="min-w-0 flex-1 bg-transparent text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none">
      <button type="submit" class="h-9 px-4 rounded-md bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition cursor-pointer">Search</button>
    </form>

    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
      <a href="{{ route('home') }}" class="h-10 px-5 inline-flex items-center rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition">Back to home</a>
      <a href="{{ route('shop') }}" class="h-10 px-5 inline-flex items-center rounded-xl bg-white text-stone-800 text-sm font-semibold ring-1 ring-stone-200 hover:bg-stone-50 transition">Shop all</a>
      <a href="{{ route('track') }}" class="h-10 px-5 inline-flex items-center rounded-xl bg-white text-stone-800 text-sm font-semibold ring-1 ring-stone-200 hover:bg-stone-50 transition">Track order</a>
    </div>
  </section>

  {{-- Categories, in the homepage tile style --}}
  @if($popularCategories->isNotEmpty())
    <section class="max-w-6xl mx-auto px-4 sm:px-6">
      <h2 class="text-center text-base sm:text-xl font-medium text-stone-900 mb-4 sm:mb-6">Shop by category</h2>
      <div class="grid grid-cols-3 md:grid-cols-6 gap-2.5 sm:gap-4">
        @foreach($popularCategories as $cat)
          <a href="{{ route('shop.category', $cat) }}" class="group flex flex-col items-center rounded-2xl bg-white p-2 sm:p-3 pb-2.5 text-center ring-1 ring-stone-200/80 transition hover:ring-brand-300">
            <span class="block w-full aspect-square overflow-hidden rounded-xl bg-stone-100">
              @if($cat->image)
                <img src="{{ $cat->imageUrl() }}" alt="" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
              @else
                <span class="grid h-full w-full place-items-center text-2xl">{{ $cat->icon ?: strtoupper(mb_substr($cat->name, 0, 1)) }}</span>
              @endif
            </span>
            <span class="mt-1.5 block w-full line-clamp-2 text-xs sm:text-sm font-semibold leading-tight text-stone-800 group-hover:text-brand-600">{{ $cat->name }}</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  {{-- A few products to keep shopping --}}
  @if($suggestedProducts->isNotEmpty())
    <section class="max-w-6xl mx-auto px-4 sm:px-6 mt-12 sm:mt-16">
      <div class="flex items-end justify-between mb-4 sm:mb-6 gap-3">
        <h2 class="text-base sm:text-xl font-medium text-stone-900">You may also like</h2>
        <a href="{{ route('shop') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">View all</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4">
        @foreach($suggestedProducts as $product)
          @include('storefront.partials.product-card', ['product' => $product])
        @endforeach
      </div>
    </section>
  @endif

  <p class="mt-12 text-center text-sm text-stone-500">Still can't find it? <a href="{{ route('contact') }}" class="font-semibold text-stone-800 hover:text-brand-600">Contact us</a></p>
</main>
@endsection
