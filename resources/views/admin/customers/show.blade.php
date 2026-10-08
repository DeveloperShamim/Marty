@extends('layouts.admin')
@section('title', 'Customer profile')
@section('subtitle', 'Orders, spending and contact details for this customer.')

@section('page-actions')
  <a href="{{ route('admin.customers.index') }}" class="pill-btn">
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    All customers
  </a>
@endsection

@section('content')
@php
  $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
  if (str_starts_with($cleanPhone, '0')) {
    $waPhone = '88' . $cleanPhone;
  } elseif (str_starts_with($cleanPhone, '880')) {
    $waPhone = $cleanPhone;
  } else {
    $waPhone = '880' . $cleanPhone;
  }
@endphp

@php
  $tagTone = match ($tag) {
    'VIP' => 'bg-amber-50 text-amber-800',
    'Wholesale' => 'bg-purple-50 text-purple-800',
    'Loyal' => 'bg-sky-50 text-sky-800',
    'Risk' => 'bg-rose-50 text-rose-700',
    'Influencer' => 'bg-pink-50 text-pink-700',
    'Repeat Buyer' => 'bg-indigo-50 text-indigo-700',
    default => 'bg-emerald-50 text-emerald-700',
  };
@endphp

<div class="space-y-4 max-w-full">

  {{-- Profile --}}
  <section class="panel p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5 min-w-0">
      <div class="w-12 h-12 rounded-full font-semibold text-sm flex items-center justify-center shrink-0 {{ $tag === 'VIP' ? 'bg-amber-50 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
        {{ strtoupper(substr($customer->customer_name ?: 'C', 0, 2)) }}
      </div>

      <div class="min-w-0 space-y-1">
        <div class="flex items-center gap-2 flex-wrap">
          <h2 class="text-[17px] font-semibold text-gray-900 truncate">
            {{ $customer->customer_name ?: 'Valued Customer' }}
          </h2>

          <form method="POST" action="{{ route('admin.customers.update-segment-tag', $phone) }}" class="inline-block">
            @csrf
            <select name="segment_tag" onchange="this.form.submit()" aria-label="Segment tag" class="h-7 text-[11px] font-semibold rounded-full pl-2.5 pr-7 border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gray-200 {{ $tagTone }}" title="Change customer segment tag">
              <optgroup label="Custom tag">
                <option value="VIP" {{ ($adminTag === 'VIP') ? 'selected' : '' }}>VIP customer</option>
                <option value="Wholesale" {{ ($adminTag === 'Wholesale') ? 'selected' : '' }}>Wholesale / bulk</option>
                <option value="Loyal" {{ ($adminTag === 'Loyal') ? 'selected' : '' }}>Loyal client</option>
                <option value="Influencer" {{ ($adminTag === 'Influencer') ? 'selected' : '' }}>Influencer / partner</option>
                <option value="Risk" {{ ($adminTag === 'Risk') ? 'selected' : '' }}>Return risk</option>
              </optgroup>
              <optgroup label="Automatic">
                <option value="auto" {{ empty($adminTag) ? 'selected' : '' }}>
                  Auto ({{ $ordersCount >= 2 ? 'Repeat Buyer' : 'New Customer' }})
                </option>
              </optgroup>
            </select>
          </form>

          @if($isBlacklisted)
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-rose-50 text-rose-700">Blacklisted phone</span>
          @endif
        </div>

        <p class="text-xs text-gray-500 flex items-center gap-x-2 gap-y-0.5 flex-wrap">
          <span class="text-gray-800 tabular-nums">{{ $phone }}</span>
          @if($customer->customer_email)
            <span class="text-gray-300">&middot;</span>
            <span class="break-all">{{ $customer->customer_email }}</span>
          @endif
          @if($customer->city)
            <span class="text-gray-300">&middot;</span>
            <span>{{ $customer->city }}</span>
          @endif
        </p>
      </div>
    </div>

    <div class="grid grid-cols-3 sm:flex items-center gap-2 shrink-0">
      <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="h-9 px-3.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5">
        <svg class="w-3.5 h-3.5 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.216 8.216 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.01 4.54-3.68 8.23-8.22 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.66.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.43 1.03 2.6.12.17 1.78 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6-.07.49-.26 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.31z"/></svg>
        <span>WhatsApp</span>
      </a>

      <a href="tel:{{ $phone }}" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium inline-flex items-center justify-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
        <span>Call</span>
      </a>

      <form method="POST" action="{{ route('admin.customers.toggle-blacklist', $phone) }}" onsubmit="return confirm('{{ $isBlacklisted ? "Unblock phone {$phone}?" : "Block and blacklist phone {$phone} from placing orders?" }}')">
        @csrf
        <button type="submit" class="w-full h-9 px-3.5 rounded-full text-[13px] font-semibold whitespace-nowrap {{ $isBlacklisted ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
          {{ $isBlacklisted ? 'Unblock' : 'Blacklist' }}
        </button>
      </form>
    </div>
  </section>

  {{-- KPI tiles --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Lifetime value</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">{{ money($totalSpent) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Total customer revenue</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Orders</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $ordersCount }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Total orders placed</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Average order</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">{{ money($avgOrderValue) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Per checkout</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <p class="text-xs text-gray-500">Delivery rate</p>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ $successRate }}%</p>
      <p class="text-[11px] text-gray-400 mt-0.5">{{ $deliveredCount }} delivered &middot; {{ $cancelledCount }} cancelled</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

    {{-- Order history --}}
    <section class="card overflow-hidden lg:col-span-8 self-start">
      <div class="p-4 sm:p-5 pb-3 sm:pb-3 flex items-center justify-between gap-3">
        <h2 class="text-[15px] font-semibold text-gray-900">Order history</h2>
        <span class="text-xs text-gray-500 tabular-nums">{{ $ordersCount }} {{ \Illuminate\Support\Str::plural('order', $ordersCount) }}</span>
      </div>

      <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
          <thead>
            <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
              <th class="py-3 px-4 sm:pl-5">Order</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Payment</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Total</th>
              <th class="py-3 px-4 sm:pr-5 text-right"><span class="sr-only">Open</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($orders as $order)
              <tr class="hover:bg-gray-50/70 transition-colors">
                <td class="py-3 px-4 sm:pl-5">
                  <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-gray-900 hover:underline block">{{ $order->order_number }}</a>
                  <span class="text-[11px] text-gray-400">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}</span>
                </td>
                <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ $order->created_at->format('d M, Y') }}</td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $order->paymentBadge() }}">{{ ucfirst($order->payment_status) }}</span>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
                </td>
                <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">{{ money($order->total) }}</td>
                <td class="py-3 px-4 sm:pr-5 text-right whitespace-nowrap">
                  <a href="{{ route('admin.orders.show', $order) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold inline-flex items-center">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="sm:hidden px-3 pb-3 space-y-2">
        @foreach($orders as $order)
          <a href="{{ route('admin.orders.show', $order) }}" class="block rounded-2xl bg-gray-50/80 p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
              <span class="font-semibold text-[13px] text-gray-900">{{ $order->order_number }}</span>
              <span class="text-[13px] font-semibold text-gray-900 tabular-nums">{{ money($order->total) }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $order->paymentBadge() }}">{{ ucfirst($order->payment_status) }}</span>
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
              </div>
              <span class="text-[11px] text-gray-500 whitespace-nowrap">{{ $order->created_at->format('d M, Y') }} · {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}</span>
            </div>
          </a>
        @endforeach
      </div>
    </section>

    <div class="lg:col-span-4 space-y-4">

      {{-- Shipping location --}}
      <section class="panel p-4 sm:p-5">
        <h2 class="text-[15px] font-semibold text-gray-900">Shipping location</h2>
        <p class="mt-2 text-[13px] text-gray-800 leading-relaxed">{{ $customer->shipping_address ?: 'No full address specified' }}</p>
        <dl class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-3 text-[13px]">
          <div>
            <dt class="text-xs text-gray-500">City / district</dt>
            <dd class="font-medium text-gray-900 mt-0.5">{{ $customer->city ?: 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-gray-500">Shipping zone</dt>
            <dd class="font-medium text-gray-900 mt-0.5">{{ ucfirst($customer->shipping_zone ?? 'Standard') }}</dd>
          </div>
        </dl>
      </section>

      {{-- Most purchased --}}
      <section class="panel p-4 sm:p-5">
        <h2 class="text-[15px] font-semibold text-gray-900">Most bought items</h2>
        <div class="mt-3 space-y-2">
          @forelse($topPurchasedItems as $item)
            <div class="flex items-center justify-between gap-2.5">
              <div class="flex items-center gap-2.5 min-w-0">
                @if($item->image)
                  <img src="{{ $item->image }}" class="h-9 w-9 object-cover rounded-xl shrink-0 bg-gray-100" alt="{{ $item->product_name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                @else
                  <div class="h-9 w-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 font-semibold text-xs shrink-0">
                    {{ substr($item->product_name, 0, 1) }}
                  </div>
                @endif
                <div class="min-w-0">
                  <p class="font-medium text-gray-900 text-[13px] truncate">{{ $item->product_name }}</p>
                  <span class="text-[11px] text-gray-500 tabular-nums">{{ money($item->spent) }} total</span>
                </div>
              </div>
              <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-gray-100 text-gray-700 tabular-nums shrink-0">{{ $item->units_bought }}x</span>
            </div>
          @empty
            <p class="text-xs text-gray-500">No purchased items recorded yet.</p>
          @endforelse
        </div>
      </section>

    </div>
  </div>

</div>
@endsection
