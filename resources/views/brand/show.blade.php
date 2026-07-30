@extends('layouts.storefront')

@section('title', $brand->meta_title ?: $brand->name . ' Products')
@section('meta_description', $brand->meta_description ?: $brand->description)
@section('meta_keywords', $brand->meta_keywords)

@section('content')
<main class="max-w-[1440px] mx-auto px-4 sm:px-5 py-6 space-y-6">

  {{-- Brand Hero Header --}}
  <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-xl">
    @if($brand->bannerUrl())
      <div class="absolute inset-0 z-0 opacity-40">
        <img src="{{ $brand->bannerUrl() }}" class="w-full h-full object-cover" alt="">
      </div>
    @endif
    
    <div class="relative z-10 p-6 sm:p-10 flex flex-col md:flex-row items-center md:items-start gap-6">
      <div class="h-24 w-24 sm:h-28 sm:w-28 shrink-0 rounded-2xl bg-white p-3 shadow-lg border border-white/20 flex items-center justify-center">
        <img src="{{ $brand->logoUrl() }}" class="h-full w-full object-contain" alt="{{ $brand->name }}">
      </div>

      <div class="flex-1 text-center md:text-left space-y-2">
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $brand->name }}</h1>
          @if($brand->is_featured)
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-400/20 text-amber-300 border border-amber-400/30">
              ★ Official Featured Brand
            </span>
          @endif
        </div>

        @if($brand->description)
          <p class="text-sm sm:text-base text-slate-300 max-w-3xl leading-relaxed">{{ $brand->description }}</p>
        @endif

        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2 text-xs sm:text-sm text-slate-300">
          <span class="font-medium text-indigo-300">{{ $products->total() }} {{ Str::plural('Product', $products->total()) }} Available</span>
          @if($brand->website)
            <span>&bull;</span>
            <a href="{{ $brand->website }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-300 hover:text-white font-medium underline">
              Official Website &nearr;
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Products Grid --}}
  <div class="space-y-4">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
      <h2 class="text-lg font-bold text-slate-900">Products by {{ $brand->name }}</h2>
      <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="text-xs font-medium text-indigo-600 hover:underline">
        View in Filtered Shop &rarr;
      </a>
    </div>

    @if($products->count() > 0)
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
        @foreach($products as $product)
          @include('storefront.partials.product-card', ['product' => $product])
        @endforeach
      </div>

      <div class="pt-4">
        {{ $products->links() }}
      </div>
    @else
      <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 p-8 space-y-3">
        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
          {{ substr($brand->name, 0, 1) }}
        </div>
        <h3 class="text-lg font-bold text-gray-800">No products available yet</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto">Products for {{ $brand->name }} will be added soon. Browse other brands or shop catalog.</p>
        <a href="{{ route('shop') }}" class="inline-block px-5 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
          Browse All Products
        </a>
      </div>
    @endif
  </div>

</main>
@endsection
