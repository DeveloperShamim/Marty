@extends('layouts.admin')
@section('title', 'Free delivery')
@section('subtitle', 'Free delivery for online payments, big orders or selected products.')

@section('content')
@php
  $reasonRows = [
    'online_payment' => ['Paid online', 'bg-emerald-50 text-emerald-700', '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>'],
    'product'        => ['Free-delivery products', 'bg-sky-50 text-sky-700', '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>'],
    'order_total'    => ['Big orders', 'bg-violet-50 text-violet-700', '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 2h3l2.7 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>'],
  ];
  $totalCost = (float) $stats->sum('cost');
  $totalOrders = (int) $stats->sum('orders');
  $fees = $config['fees'];
@endphp

<div class="space-y-4 max-w-5xl">

  {{-- This month --}}
  <section>
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 px-0.5 mb-2">
      <h2 class="text-[15px] font-semibold text-gray-900">Cost this month</h2>
      <span class="text-xs text-gray-500">Delivered or paid orders since {{ now()->startOfMonth()->format('d M') }}</span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="panel p-3.5 sm:p-4">
        <div class="flex items-center justify-between gap-2">
          <p class="text-xs text-gray-500">Total</p>
          <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
          </span>
        </div>
        <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ money($totalCost) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $totalOrders }} {{ \Illuminate\Support\Str::plural('order', $totalOrders) }}</p>
      </div>
      @foreach($reasonRows as $key => [$label, $cls, $icon])
        @php $row = $stats->get($key); @endphp
        <div class="panel p-3.5 sm:p-4">
          <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-gray-500 leading-tight">{{ $label }}</p>
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl {{ $cls }}">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
            </span>
          </div>
          <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ money((float) ($row->cost ?? 0)) }}</p>
          <p class="text-[11px] text-gray-400 mt-0.5">{{ (int) ($row->orders ?? 0) }} {{ \Illuminate\Support\Str::plural('order', (int) ($row->orders ?? 0)) }}</p>
        </div>
      @endforeach
    </div>
    <p class="mt-2 px-0.5 text-xs text-gray-500 leading-relaxed">
      Normal delivery fee: {{ money($fees['inside_dhaka']) }} inside / {{ money($fees['outside_dhaka']) }} outside Dhaka
      (<a href="{{ route('admin.settings.edit') }}" class="underline hover:text-gray-800">Store settings</a>).
      You still pay the courier on a free-delivery order, so that fee is taken off your profit in Profit &amp; Analytics.
    </p>
  </section>

  {{-- Offer rules --}}
  <form method="POST" action="{{ route('admin.free-delivery.update') }}" class="panel p-4 sm:p-5 flex flex-col gap-4">
    @csrf @method('PUT')

    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Offer rules</h2>
      <p class="text-xs text-gray-500 mt-0.5">Choose when an order ships free.</p>
    </div>

    <div class="rounded-xl bg-gray-50 p-3.5 space-y-3">
      <label class="flex items-start gap-3 cursor-pointer">
        <span class="relative inline-flex items-center shrink-0 mt-0.5">
          <input type="checkbox" name="free_delivery_online" value="1" @checked(old('free_delivery_online', $config['online_enabled'])) class="sr-only peer">
          <span class="w-10 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-4 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></span>
        </span>
        <span>
          <span class="block text-[13px] font-semibold text-gray-900">When the customer pays online</span>
          <span class="block text-xs text-gray-500 mt-0.5">bKash, Nagad or Rocket at checkout. It only counts once you verify the payment. If the money never arrives, use “Switch to cash on delivery” on the order and the delivery charge is added back.</span>
        </span>
      </label>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:pl-[52px]">
        <div>
          <label for="fdMin" class="lbl">Minimum order (৳)</label>
          <input id="fdMin" type="number" name="free_delivery_online_min" min="0" step="1" value="{{ old('free_delivery_online_min', (float) $config['online_min']) }}" class="inp" />
          <p class="text-[11px] text-gray-500 mt-1">After coupon discount. 0 means any amount.</p>
        </div>
        <div>
          <label for="fdZones" class="lbl">Delivery zones</label>
          <select id="fdZones" name="free_delivery_online_zones" class="inp">
            <option value="both" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'both')>Inside &amp; outside Dhaka</option>
            <option value="inside_dhaka" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'inside_dhaka')>Inside Dhaka only</option>
            <option value="outside_dhaka" @selected(old('free_delivery_online_zones', $config['online_zones']) === 'outside_dhaka')>Outside Dhaka only</option>
          </select>
        </div>
      </div>
    </div>

    <div class="rounded-xl bg-gray-50 p-3.5">
      <label for="fdOver" class="block text-[13px] font-semibold text-gray-900">Every order above (৳)</label>
      <p class="text-xs text-gray-500 mt-0.5 mb-2">Any payment method, including cash on delivery. 0 turns this off.</p>
      <input id="fdOver" type="number" name="free_delivery_over_amount" min="0" step="1" value="{{ old('free_delivery_over_amount', (float) $config['over_amount']) }}" class="inp sm:w-64" />
    </div>

    <div class="flex justify-end">
      <button type="submit" class="w-full sm:w-auto h-9 px-4 rounded-full text-white text-[13px] font-semibold" style="background: var(--brand-dark);">Save rules</button>
    </div>
  </form>

  {{-- Free-delivery products --}}
  <section class="panel p-4 sm:p-5 space-y-3">
    <div>
      <h2 class="text-[15px] font-semibold text-gray-900">Free-delivery products <span class="text-gray-400 font-normal tabular-nums">({{ $freeProducts->count() }})</span></h2>
      <p class="text-xs text-gray-500 mt-0.5">If a cart has any of these, the whole order ships free. They show a “Free Delivery” badge in the shop.</p>
    </div>

    <form method="GET" action="{{ route('admin.free-delivery.index') }}" class="flex gap-2">
      <label class="relative flex-1 min-w-0">
        <span class="sr-only">Search products to add</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="q" value="{{ $q }}" placeholder="Search products to add" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
      </label>
      <button class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
    </form>

    @if($q !== '')
      <div class="rounded-xl bg-gray-50 divide-y divide-gray-100">
        @forelse($available as $p)
          <div class="flex items-center gap-3 p-2.5">
            <img src="{{ $p->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover bg-white shrink-0" loading="lazy">
            <div class="min-w-0 flex-1">
              <div class="text-[13px] font-medium text-gray-900 truncate">{{ $p->name }}</div>
              <div class="text-[11px] text-gray-500 tabular-nums">{{ money($p->price) }}</div>
            </div>
            <form method="POST" action="{{ route('admin.free-delivery.toggle', $p) }}">
              @csrf @method('PUT')
              <input type="hidden" name="free_delivery" value="1">
              <button class="h-8 px-3.5 rounded-full text-white text-xs font-semibold" style="background: var(--brand-dark);">Add</button>
            </form>
          </div>
        @empty
          <p class="p-3 text-xs text-gray-500">No other products match “{{ $q }}”.</p>
        @endforelse
      </div>
    @endif

    <div class="divide-y divide-gray-100">
      @forelse($freeProducts as $p)
        <div class="flex items-center gap-3 py-2.5">
          <img src="{{ $p->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover bg-gray-100 shrink-0" loading="lazy">
          <div class="min-w-0 flex-1">
            <a href="{{ route('admin.products.edit', $p) }}" class="block text-[13px] font-medium text-gray-900 truncate hover:underline">{{ $p->name }}</a>
            <div class="text-[11px] text-gray-500 tabular-nums">{{ money($p->price) }} @unless($p->is_published)· <span class="text-amber-700">not published</span>@endunless</div>
          </div>
          <form method="POST" action="{{ route('admin.free-delivery.toggle', $p) }}">
            @csrf @method('PUT')
            <input type="hidden" name="free_delivery" value="0">
            <button class="h-8 px-3 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium">Remove</button>
          </form>
        </div>
      @empty
        <p class="py-6 text-xs text-gray-500 text-center">No free-delivery products yet. Search above to add one, or switch it on in the product's edit page.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection
