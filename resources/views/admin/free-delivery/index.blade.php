@extends('layouts.admin')
@section('title', 'Free Delivery')

@section('content')
@php
  $reasonRows = [
    'online_payment' => ['Paid online', 'bg-emerald-50 border-emerald-200 text-emerald-900'],
    'product'        => ['Free-delivery products', 'bg-sky-50 border-sky-200 text-sky-900'],
    'order_total'    => ['Big orders', 'bg-violet-50 border-violet-200 text-violet-900'],
  ];
  $totalCost = (float) $stats->sum('cost');
  $totalOrders = (int) $stats->sum('orders');
  $fees = $config['fees'];
@endphp

<div class="space-y-5 sm:space-y-6 max-w-5xl">

  {{-- Header --}}
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2"><span>🚚</span> Free Delivery</h1>
    <p class="text-xs text-stone-500 mt-1">
      Give customers free delivery for paying online, for big orders, or on selected products.
      Your normal delivery fee is {{ money($fees['inside_dhaka']) }} inside / {{ money($fees['outside_dhaka']) }} outside Dhaka
      (<a href="{{ route('admin.settings.edit') }}" class="underline">Store Settings</a>). On a free-delivery order you still pay the courier,
      so that fee is subtracted from your profit in <b>Profit &amp; Analytics</b>.
    </p>
  </div>

  {{-- This month --}}
  <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
    <div class="flex items-center justify-between gap-2">
      <h2 class="text-sm font-black text-stone-900">This month's free delivery cost</h2>
      <span class="text-[11px] text-stone-400">Delivered or paid orders since {{ now()->startOfMonth()->format('d M') }}</span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
      <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
        <div class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Total</div>
        <div class="text-lg font-black text-amber-900 font-mono">{{ money($totalCost) }}</div>
        <div class="text-[11px] text-amber-700">{{ $totalOrders }} {{ \Illuminate\Support\Str::plural('order', $totalOrders) }}</div>
      </div>
      @foreach($reasonRows as $key => [$label, $cls])
        @php $row = $stats->get($key); @endphp
        <div class="rounded-xl border p-3 {{ $cls }}">
          <div class="text-[10px] font-bold uppercase tracking-wider opacity-75">{{ $label }}</div>
          <div class="text-lg font-black font-mono">{{ money((float) ($row->cost ?? 0)) }}</div>
          <div class="text-[11px] opacity-75">{{ (int) ($row->orders ?? 0) }} {{ \Illuminate\Support\Str::plural('order', (int) ($row->orders ?? 0)) }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Offer rules --}}
  <form method="POST" action="{{ route('admin.free-delivery.update') }}" class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-5">
    @csrf @method('PUT')

    <div class="space-y-3">
      <label class="flex items-start gap-3 cursor-pointer">
        <span class="relative inline-flex items-center shrink-0 mt-0.5">
          <input type="checkbox" name="free_delivery_online" value="1" @checked(old('free_delivery_online', $config['online_enabled'])) class="sr-only peer">
          <span class="w-11 h-6 bg-stone-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></span>
        </span>
        <span>
          <span class="block text-sm font-extrabold text-stone-900">Free delivery when the customer pays online</span>
          <span class="block text-xs text-stone-500">bKash, Nagad or Rocket at checkout. It only counts once you <b>verify</b> the payment. If the money never arrives, use “Switch to cash on delivery” on the order and the delivery charge is added back.</span>
        </span>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:pl-14">
        <div>
          <label for="fdMin" class="text-xs font-black text-stone-800 block mb-1">Minimum order amount (৳)</label>
          <input id="fdMin" type="number" name="free_delivery_online_min" min="0" step="1" value="{{ old('free_delivery_online_min', (float) $config['online_min']) }}" class="w-full text-sm font-bold px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
          <p class="text-[11px] text-stone-500 mt-1">After coupon discount. 0 = any amount. Stops losing money on small orders.</p>
        </div>
        <div>
          <label for="fdZones" class="text-xs font-black text-stone-800 block mb-1">Delivery zones</label>
          <select id="fdZones" name="free_delivery_online_zones" class="w-full text-sm font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="both" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'both')>Inside &amp; outside Dhaka</option>
            <option value="inside_dhaka" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'inside_dhaka')>Inside Dhaka only</option>
            <option value="outside_dhaka" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'outside_dhaka')>Outside Dhaka only</option>
          </select>
        </div>
      </div>
    </div>

    <div class="border-t border-stone-100 pt-5">
      <label for="fdOver" class="text-sm font-extrabold text-stone-900 block">Free delivery for every order above (৳)</label>
      <p class="text-xs text-stone-500 mb-2">Any payment method, including cash on delivery. Leave 0 to turn this off.</p>
      <input id="fdOver" type="number" name="free_delivery_over_amount" min="0" step="1" value="{{ old('free_delivery_over_amount', (float) $config['over_amount']) }}" class="w-full sm:w-64 text-sm font-bold px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
    </div>

    <div class="flex justify-end">
      <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all">Save Free Delivery</button>
    </div>
  </form>

  {{-- Free-delivery products --}}
  <div class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4">
    <div>
      <h2 class="text-sm font-extrabold text-stone-900">Free-delivery products ({{ $freeProducts->count() }})</h2>
      <p class="text-xs text-stone-500">If a cart contains any of these, the whole order ships free, whatever the payment method. They show a “Free Delivery” badge in the shop.</p>
    </div>

    <form method="GET" action="{{ route('admin.free-delivery.index') }}" class="flex gap-2">
      <input type="search" name="q" value="{{ $q }}" placeholder="Search products to add…" class="flex-1 min-w-0 text-sm px-3.5 py-2.5 bg-stone-50 focus:bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
      <button class="px-4 py-2.5 rounded-xl bg-stone-900 text-white text-xs font-extrabold shrink-0">Search</button>
    </form>

    @if($q !== '')
      <div class="rounded-xl border border-stone-200 divide-y divide-stone-100">
        @forelse($available as $p)
          <div class="flex items-center gap-3 p-2.5">
            <img src="{{ $p->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover bg-stone-100 shrink-0" loading="lazy">
            <div class="min-w-0 flex-1">
              <div class="text-sm font-semibold text-stone-900 truncate">{{ $p->name }}</div>
              <div class="text-[11px] text-stone-500">{{ money($p->price) }}</div>
            </div>
            <form method="POST" action="{{ route('admin.free-delivery.toggle', $p) }}">
              @csrf @method('PUT')
              <input type="hidden" name="free_delivery" value="1">
              <button class="px-3 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-extrabold">+ Add</button>
            </form>
          </div>
        @empty
          <p class="p-3 text-xs text-stone-500">No other products match “{{ $q }}”.</p>
        @endforelse
      </div>
    @endif

    <div class="rounded-xl border border-stone-200 divide-y divide-stone-100">
      @forelse($freeProducts as $p)
        <div class="flex items-center gap-3 p-2.5">
          <img src="{{ $p->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover bg-stone-100 shrink-0" loading="lazy">
          <div class="min-w-0 flex-1">
            <a href="{{ route('admin.products.edit', $p) }}" class="block text-sm font-semibold text-stone-900 truncate hover:underline">{{ $p->name }}</a>
            <div class="text-[11px] text-stone-500">{{ money($p->price) }} @unless($p->is_published)· <span class="text-amber-700">not published</span>@endunless</div>
          </div>
          <form method="POST" action="{{ route('admin.free-delivery.toggle', $p) }}">
            @csrf @method('PUT')
            <input type="hidden" name="free_delivery" value="0">
            <button class="px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-extrabold">Remove</button>
          </form>
        </div>
      @empty
        <p class="p-4 text-xs text-stone-500 text-center">No free-delivery products yet. Search above to add one, or switch it on in the product's edit page.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection
