@extends('layouts.storefront')
@php $title = 'Order ' . $order->order_number; @endphp

@section('content')
<main class="min-h-[70vh] bg-slate-50/60 py-8 sm:py-12">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="mb-6 flex items-center justify-between">
      <a href="{{ route('account') }}#orders" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-brand-600 hover:text-brand-700 transition">
        <span>←</span> <span>Back to My Orders</span>
      </a>
      <span class="text-xs font-bold text-slate-400">Order Reference: <span class="font-mono text-slate-700 font-extrabold">{{ $order->order_number }}</span></span>
    </div>

    {{-- Order Title Card --}}
    <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-7 mb-6 shadow-2xs">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2.5">
            <span class="w-1.5 h-5 bg-brand-600 rounded-full"></span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 font-mono">{{ $order->order_number }}</h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-500 mt-1">Placed on {{ $order->created_at->format('d M Y \a\t g:i A') }}</p>
        </div>

        @php
          $status = strtolower($order->status);
          $statusStyles = match ($status) {
            'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'confirmed', 'processing', 'shipped' => 'bg-sky-50 text-sky-700 border-sky-200',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-amber-50 text-amber-800 border-amber-200',
          };
        @endphp
        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-extrabold border {{ $statusStyles }} shadow-2xs">
          {{ ucfirst($order->status) }}
        </span>
      </div>
    </div>

    {{-- Items Purchased Table Card --}}
    <div class="rounded-3xl border border-slate-200/90 bg-white shadow-2xs overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h2 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
          <span class="w-1.5 h-4 bg-brand-600 rounded-full"></span>
          <span>Items in this Order</span>
        </h2>
        <span class="text-xs font-bold text-slate-500">{{ $order->items->count() }} {{ Str::plural('Item', $order->items->count()) }}</span>
      </div>

      <div class="divide-y divide-slate-100">
        @foreach($order->items as $item)
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 hover:bg-slate-50/40 transition-colors">
            <div class="flex items-center gap-4 min-w-0 flex-1">
              <img src="{{ $item->imageUrl() }}" class="h-16 w-14 object-cover bg-slate-100 rounded-xl shrink-0 border border-slate-200/80 shadow-2xs" alt="{{ $item->product_name }}" />
              <div class="flex-1 min-w-0">
                @if($item->product && $item->product->is_published)
                  <a href="{{ route('product.show', $item->product) }}" class="font-extrabold text-xs sm:text-sm text-slate-900 hover:text-brand-600 transition truncate block">
                    {{ $item->product_name }}
                  </a>
                @else
                  <p class="font-extrabold text-xs sm:text-sm text-slate-900 truncate">{{ $item->product_name }}</p>
                @endif
                @if($item->variant)
                  <p class="text-xs text-slate-500 mt-0.5">{{ $item->variant }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-0.5">
                  {{ money($item->unit_price) }} × {{ $item->quantity }}
                </p>
              </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
              @if(strtolower($order->status) === 'delivered' && $item->product)
                <div>
                  @if(in_array($item->product_id, $reviewedProductIds ?? []))
                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg">
                      <span>✓</span> Reviewed
                    </span>
                  @else
                    <a href="{{ route('product.show', $item->product) }}#reviews" class="btn-shine inline-flex items-center gap-1 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 px-3.5 py-1.5 rounded-xl shadow-2xs transition-all">
                      <span>★</span> Rate &amp; Review
                    </a>
                  @endif
                </div>
              @endif

              <span class="font-extrabold text-sm text-slate-900 font-mono">{{ money($item->line_total) }}</span>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Order Totals Financial Breakdown --}}
      <div class="p-6 border-t border-slate-100 bg-slate-50/50 text-xs sm:text-sm space-y-2">
        <div class="flex justify-between text-slate-600">
          <span>Subtotal</span>
          <span class="font-mono font-bold">{{ money($order->subtotal) }}</span>
        </div>
        @if($order->discount_amount > 0)
          <div class="flex justify-between text-brand-600 font-bold">
            <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
            <span class="font-mono">−{{ money($order->discount_amount) }}</span>
          </div>
        @endif
        <div class="flex justify-between text-slate-600">
          <span>Shipping Charge</span>
          <span class="font-mono font-bold">{{ money($order->shipping_charge) }}</span>
        </div>
        <div class="flex justify-between text-slate-600">
          <span>Tax</span>
          <span class="font-mono font-bold">{{ money($order->tax) }}</span>
        </div>
        <div class="flex justify-between font-extrabold text-base pt-3 border-t border-slate-200 text-slate-900 mt-2">
          <span>Grand Total</span>
          <span class="text-brand-600 font-mono">{{ money($order->total) }}</span>
        </div>
      </div>
    </div>

    {{-- Delivery and Payment Details Cards --}}
    <div class="grid sm:grid-cols-2 gap-5 text-xs sm:text-sm">
      <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-2">
        <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
          <span>📍</span> <span>Delivery Address</span>
        </h3>
        <p class="font-extrabold text-sm text-slate-900">{{ $order->customer_name }}</p>
        <p class="text-slate-600 leading-relaxed">{{ $order->shipping_address }}, {{ $order->city }}</p>
        <p class="text-slate-500 font-mono mt-1">{{ $order->customer_phone }}</p>
      </div>

      <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-2">
        <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
          <span>💳</span> <span>Payment Method</span>
        </h3>
        <p class="font-extrabold text-sm text-slate-900">{{ $order->paymentMethodLabel() }}</p>
        <div class="flex items-center gap-2 mt-1">
          <span class="text-xs text-slate-500">Payment Status:</span>
          <span class="font-bold text-xs px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 capitalize">
            {{ $order->payment_status }}
          </span>
        </div>
      </div>
    </div>

  </div>
</main>
@endsection
