@extends('layouts.admin')
@section('title', 'Customer Profile: ' . ($customer->customer_name ?: $phone))

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

<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Back link & Top Profile Card --}}
  <div class="space-y-3">
    <a href="{{ route('admin.customers.index') }}" class="text-xs font-bold text-stone-500 hover:text-brand-600 inline-flex items-center gap-1 transition-colors">
      <span>&larr;</span> Back to Customers
    </a>

    <div class="bg-white p-5 sm:p-7 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6">
      <div class="flex items-center gap-4 min-w-0">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl font-black text-base sm:text-xl flex items-center justify-center shrink-0 border {{ $tag === 'VIP' ? 'bg-amber-100 text-amber-900 border-amber-300 shadow-sm' : 'bg-stone-100 text-stone-800 border-stone-200' }}">
          {{ strtoupper(substr($customer->customer_name ?: 'C', 0, 2)) }}
        </div>

        <div class="min-w-0 space-y-1.5">
          <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-lg sm:text-2xl font-extrabold text-stone-900 tracking-tight truncate">
              {{ $customer->customer_name ?: 'Valued Customer' }}
            </h1>

            {{-- Segment Tag Badge with Admin Dropdown Form --}}
            <form method="POST" action="{{ route('admin.customers.update-segment-tag', $phone) }}" class="inline-block">
              @csrf
              <select name="segment_tag" onchange="this.form.submit()" class="text-xs font-black rounded-lg px-2.5 py-1 border transition-all cursor-pointer shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $tag === 'VIP' ? 'bg-amber-100 text-amber-950 border-amber-300' : ($tag === 'Wholesale' ? 'bg-purple-100 text-purple-950 border-purple-300' : ($tag === 'Loyal' ? 'bg-sky-100 text-sky-950 border-sky-300' : ($tag === 'Risk' ? 'bg-rose-100 text-rose-950 border-rose-300' : ($tag === 'Influencer' ? 'bg-pink-100 text-pink-950 border-pink-300' : ($tag === 'Repeat Buyer' ? 'bg-indigo-50 text-indigo-900 border-indigo-200' : 'bg-emerald-50 text-emerald-900 border-emerald-200'))))) }}" title="Click to change customer segment tag">
                <optgroup label="Custom Tag (Admin Selected)">
                  <option value="VIP" {{ ($adminTag === 'VIP') ? 'selected' : '' }}>🥇 VIP Customer</option>
                  <option value="Wholesale" {{ ($adminTag === 'Wholesale') ? 'selected' : '' }}>📦 Wholesale / Bulk</option>
                  <option value="Loyal" {{ ($adminTag === 'Loyal') ? 'selected' : '' }}>🌟 Loyal Client</option>
                  <option value="Influencer" {{ ($adminTag === 'Influencer') ? 'selected' : '' }}>🎬 Influencer / Partner</option>
                  <option value="Risk" {{ ($adminTag === 'Risk') ? 'selected' : '' }}>⚠️ Return Risk</option>
                </optgroup>
                <optgroup label="Automatic Default">
                  <option value="auto" {{ empty($adminTag) ? 'selected' : '' }}>
                    Auto ({{ $ordersCount >= 2 ? 'Repeat Buyer' : 'New Customer' }})
                  </option>
                </optgroup>
              </select>
            </form>

            @if($isBlacklisted)
              <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                🚫 Blacklisted Phone
              </span>
            @endif
          </div>

          <div class="flex items-center gap-3 text-xs text-stone-500 font-medium flex-wrap">
            <span class="font-mono font-bold text-stone-800">📞 {{ $phone }}</span>
            @if($customer->customer_email)
              <span>&middot;</span>
              <span>✉️ {{ $customer->customer_email }}</span>
            @endif
            @if($customer->city)
              <span>&middot;</span>
              <span>📍 {{ $customer->city }}</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto shrink-0 w-full sm:w-auto">
        <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
          <svg class="w-4 h-4 fill-current text-white" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.216 8.216 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.01 4.54-3.68 8.23-8.22 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.66.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.43 1.03 2.6.12.17 1.78 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6-.07.49-.26 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.31z"/></svg>
          <span>WhatsApp</span>
        </a>

        <a href="tel:{{ $phone }}" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          <span>Call</span>
        </a>

        <form method="POST" action="{{ route('admin.customers.toggle-blacklist', $phone) }}" class="flex-1 sm:flex-none" onsubmit="return confirm('{{ $isBlacklisted ? "Unblock phone {$phone}?" : "Block and blacklist phone {$phone} from placing orders?" }}')">
          @csrf
          <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-extrabold transition cursor-pointer {{ $isBlacklisted ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100' }}">
            {{ $isBlacklisted ? '✓ Unblock Phone' : '🚫 Blacklist Phone' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- 4 Metric Cards Ribbon --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
    <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Total Lifetime Value</span>
      <p class="text-xl sm:text-2xl font-black text-emerald-700 font-mono tracking-tight">{{ money($totalSpent) }}</p>
      <span class="text-[10px] text-stone-500 font-semibold block">Total customer revenue</span>
    </div>

    <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Total Purchases</span>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ $ordersCount }}</p>
      <span class="text-[10px] text-stone-500 font-semibold block">Total orders placed</span>
    </div>

    <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Average Order Value</span>
      <p class="text-xl sm:text-2xl font-black text-indigo-700 font-mono tracking-tight">{{ money($avgOrderValue) }}</p>
      <span class="text-[10px] text-stone-500 font-semibold block">Average per checkout</span>
    </div>

    <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Fulfillment Rate</span>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ $successRate }}%</p>
      <span class="text-[10px] text-emerald-700 font-bold block">{{ $deliveredCount }} delivered &middot; {{ $cancelledCount }} cancelled</span>
    </div>
  </div>

  {{-- 2-Column Responsive Layout --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
    
    {{-- Order History Table (8 cols on desktop) --}}
    <div class="lg:col-span-8 space-y-4">
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-stone-100 flex items-center justify-between">
          <h2 class="font-black text-stone-900 text-sm sm:text-base flex items-center gap-2">
            <span>📦</span> Order History ({{ $ordersCount }})
          </h2>
          <span class="text-xs text-stone-400">All chronologically placed orders</span>
        </div>

        {{-- Desktop Order Table --}}
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
                <th class="py-3 px-4">Order #</th>
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4 text-center">Payment</th>
                <th class="py-3 px-4 text-center">Fulfillment</th>
                <th class="py-3 px-4 text-right">Total</th>
                <th class="py-3 px-4 text-right">Invoice</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 bg-white">
              @foreach($orders as $order)
                <tr class="hover:bg-stone-50/80 transition-colors">
                  <td class="py-3.5 px-4">
                    <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-brand-700 hover:underline block">
                      {{ $order->order_number }}
                    </a>
                    <span class="text-[10px] text-stone-400">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}</span>
                  </td>
                  <td class="py-3.5 px-4 text-stone-500 font-medium whitespace-nowrap">
                    {{ $order->created_at->format('d M, Y') }}
                  </td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                      {{ ucfirst($order->payment_status) }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                      {{ ucfirst($order->status) }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right font-black text-stone-900 font-mono text-xs sm:text-sm">
                    {{ money($order->total) }}
                  </td>
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition shadow-2xs">
                      View &rarr;
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Mobile Order Cards --}}
        <div class="block sm:hidden divide-y divide-stone-100 bg-white">
          @foreach($orders as $order)
            <div class="p-4 space-y-2.5">
              <div class="flex items-center justify-between">
                <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-xs text-brand-700 hover:underline">
                  {{ $order->order_number }}
                </a>
                <span class="text-[10px] text-stone-400 font-medium">{{ $order->created_at->format('d M, Y') }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-xs text-stone-500 font-medium">{{ $order->items->count() }} items purchased</span>
                <span class="text-sm font-black text-stone-900 font-mono">{{ money($order->total) }}</span>
              </div>

              <div class="flex items-center justify-between pt-2 border-t border-stone-100 text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->paymentBadge() }}">
                    {{ ucfirst($order->payment_status) }}
                  </span>
                  <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $order->statusBadge() }}">
                    {{ ucfirst($order->status) }}
                  </span>
                </div>
                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-brand-600 hover:underline">
                  Order Details &rarr;
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Sidebar Insights (4 cols on desktop) --}}
    <div class="lg:col-span-4 space-y-5">
      
      {{-- Primary Delivery Address Card --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
        <h3 class="font-black text-stone-900 text-xs sm:text-sm uppercase tracking-wider border-b border-stone-100 pb-2.5 flex items-center gap-2">
          <span>📍</span> Primary Shipping Location
        </h3>

        <div class="space-y-2 text-xs">
          <div>
            <span class="text-[10px] font-bold text-stone-400 uppercase block">Delivery Address</span>
            <p class="font-medium text-stone-900 mt-0.5 leading-relaxed">{{ $customer->shipping_address ?: 'No full address specified' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-stone-100">
            <div>
              <span class="text-[10px] font-bold text-stone-400 uppercase block">City / District</span>
              <p class="font-bold text-stone-800">{{ $customer->city ?: 'N/A' }}</p>
            </div>
            <div>
              <span class="text-[10px] font-bold text-stone-400 uppercase block">Shipping Zone</span>
              <p class="font-bold text-stone-800">{{ ucfirst($customer->shipping_zone ?? 'Standard') }}</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Most Purchased Products Card --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
        <h3 class="font-black text-stone-900 text-xs sm:text-sm uppercase tracking-wider border-b border-stone-100 pb-2.5 flex items-center gap-2">
          <span>⭐</span> Favorite Items Bought
        </h3>

        <div class="space-y-2.5">
          @forelse($topPurchasedItems as $item)
            <div class="flex items-center justify-between gap-2.5 p-2 rounded-xl bg-stone-50 border border-stone-200/80">
              <div class="flex items-center gap-2.5 min-w-0">
                @if($item->image)
                  <img src="{{ $item->image }}" class="h-8 w-8 object-cover rounded-lg border border-stone-200 shrink-0 bg-white" alt="{{ $item->product_name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                @else
                  <div class="h-8 w-8 rounded-lg bg-stone-200 flex items-center justify-center text-stone-500 font-bold text-xs shrink-0">
                    {{ substr($item->product_name, 0, 1) }}
                  </div>
                @endif
                <div class="min-w-0">
                  <p class="font-extrabold text-stone-900 text-xs truncate">{{ $item->product_name }}</p>
                  <span class="text-[10px] text-stone-400 font-mono">{{ money($item->spent) }} total</span>
                </div>
              </div>

              <span class="px-2 py-0.5 text-[10px] font-black rounded-md bg-stone-200 text-stone-800 font-mono shrink-0">
                {{ $item->units_bought }}x
              </span>
            </div>
          @empty
            <p class="text-xs text-stone-400 italic">No purchase item breakdown recorded.</p>
          @endforelse
        </div>
      </div>

    </div>

  </div>

</div>
@endsection
