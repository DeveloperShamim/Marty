@extends('layouts.storefront', ['headerVariant' => 'compact'])
@php 
  $title = '404 - Page Not Found | ' . site_name();
  $site = site_name();
  $popularCategories = \App\Models\Category::where('is_active', true)->orderBy('position')->take(6)->get();
  $suggestedProducts = \App\Models\Product::where('is_published', true)->inRandomOrder()->take(4)->get();
@endphp

@section('content')
<!-- Hero Section with Glowing Background Elements -->
<section class="relative overflow-hidden bg-slate-900 text-white py-14 sm:py-20">
  <!-- Glowing Ambient Accents -->
  <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-brand-600/15 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-32 right-10 w-96 h-96 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-5 text-center flex flex-col items-center space-y-6">
    
    <!-- Animated Graphic Badge -->
    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-800/90 border border-slate-700/80 text-xs font-black tracking-wide text-amber-400 shadow-inner backdrop-blur-md animate-pulse">
      <span>🛸 ERROR 404 • UNCHARTED TERRITORY</span>
    </div>

    <!-- Big Visual 404 Display -->
    <div class="relative inline-block select-none my-2">
      <span class="font-display font-black text-8xl sm:text-9xl lg:text-[12rem] leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-b from-white via-slate-200 to-slate-500 opacity-90 drop-shadow-2xl">
        404
      </span>
      <div class="absolute inset-0 flex items-center justify-center">
        <span class="text-5xl sm:text-6xl lg:text-7xl animate-bounce drop-shadow-lg">🔍</span>
      </div>
    </div>

    <!-- Title & Explanation -->
    <div class="space-y-2.5 max-w-xl mx-auto text-center">
      <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
        Oops! Page Lost in Transit
      </h1>
      <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-medium">
        The link you followed might be broken, outdated, or the product has been moved to a new category in {{ $site }}.
      </p>
    </div>

    <!-- Search Form -->
    <div class="w-full max-w-lg mx-auto">
      <form action="{{ route('shop') }}" method="GET" class="relative flex items-center w-full">
        <input 
          type="text" 
          name="q" 
          placeholder="Search products, categories, or brands…" 
          class="w-full py-3.5 pl-5 pr-32 rounded-full bg-slate-800/90 border border-slate-700 text-white placeholder-slate-400 text-xs sm:text-sm font-semibold focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 transition shadow-xl" 
          required
        />
        <button 
          type="submit" 
          class="absolute right-1.5 px-4 sm:px-5 py-2 rounded-full bg-brand-600 hover:bg-brand-500 text-white text-xs font-black transition shadow-md hover:scale-105 cursor-pointer flex items-center gap-1.5"
        >
          <span>🔍</span> Search
        </button>
      </form>
    </div>

    <!-- Quick Navigation Action Buttons -->
    <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
      <a href="{{ route('home') }}" class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-full bg-white text-slate-900 font-extrabold text-xs hover:bg-slate-100 transition shadow-lg flex items-center gap-2 group">
        <span>🏠</span> Return to Homepage
      </a>
      <a href="{{ route('shop') }}" class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-extrabold text-xs transition shadow-lg flex items-center gap-2">
        <span>🛍️</span> Browse All Products
      </a>
      <a href="{{ route('track') }}" class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-200 font-extrabold text-xs transition border border-slate-700 flex items-center gap-2">
        <span>📦</span> Track Order
      </a>
    </div>

  </div>
</section>

<!-- Popular Categories Section -->
@if($popularCategories->isNotEmpty())
<section class="py-10 sm:py-12 bg-white border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-5 space-y-6">
    <div class="text-center space-y-1">
      <h3 class="font-display font-extrabold text-lg sm:text-xl text-slate-900">Explore Popular Categories</h3>
      <p class="text-xs text-slate-500">Jump right into top collections from {{ $site }}</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4">
      @foreach($popularCategories as $cat)
        <a href="{{ route('shop.category', $cat) }}" class="group p-4 rounded-2xl border border-slate-100 hover:border-brand-500/40 bg-slate-50/60 hover:bg-white hover:shadow-md transition-all text-center space-y-2 flex flex-col items-center justify-center">
          <img src="{{ $cat->imageUrl() }}" class="w-12 h-12 object-cover rounded-xl bg-white border border-slate-200 group-hover:scale-110 transition-transform" alt="{{ $cat->name }}" />
          <h4 class="font-extrabold text-xs text-slate-800 group-hover:text-brand-600 transition-colors line-clamp-1 w-full text-center">{{ $cat->name }}</h4>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Recommended Products Discovery Section -->
@if($suggestedProducts->isNotEmpty())
<section class="py-10 sm:py-12 bg-slate-50/60">
  <div class="max-w-7xl mx-auto px-4 sm:px-5 space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
      <div>
        <h3 class="font-display font-extrabold text-lg sm:text-xl text-slate-900 flex items-center gap-2">
          <span>✨</span> Recommended For You
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">Popular items you might be interested in</p>
      </div>
      <a href="{{ route('shop') }}" class="text-xs font-extrabold text-brand-600 hover:text-brand-700 flex items-center gap-1">
        <span>View All</span> &rarr;
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
      @foreach($suggestedProducts as $product)
        @include('storefront.partials.product-card', ['product' => $product])
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Need Help Contact Banner -->
<section class="border-t border-slate-200 bg-white py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-5 text-center flex flex-col items-center space-y-3">
    <h3 class="font-display text-xl sm:text-2xl font-extrabold text-slate-900">Still can't find what you're looking for?</h3>
    <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto text-center">
      Our customer support team is available to help you locate items or answer any questions about your order.
    </p>
    <div class="pt-2 flex items-center justify-center gap-3">
      <a href="{{ route('contact') }}" class="px-6 py-3 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs transition shadow-md">
        💬 Contact Support Team
      </a>
    </div>
  </div>
</section>
@endsection
