@extends('layouts.admin')
@section('title', 'Customers')
@section('subtitle', 'Lifetime value, segment tags and quick contact for every buyer.')

@section('page-actions')
  <a href="{{ route('admin.customers.export', request()->query()) }}" class="pill-btn">
    Export CSV
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg></span>
  </a>
  <a href="{{ route('admin.orders.index') }}" class="pill-btn pill-btn-dark">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg></span>
    View orders
  </a>
@endsection

@php
  $tagTone = function ($tag) {
    return match ($tag) {
      'VIP' => 'bg-amber-50 text-amber-800',
      'Wholesale' => 'bg-purple-50 text-purple-800',
      'Loyal' => 'bg-sky-50 text-sky-800',
      'Risk' => 'bg-rose-50 text-rose-700',
      'Influencer' => 'bg-pink-50 text-pink-700',
      'Repeat Buyer' => 'bg-indigo-50 text-indigo-700',
      default => 'bg-emerald-50 text-emerald-700',
    };
  };
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- KPI tiles --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-gray-500">Customers</p>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-gray-100 text-gray-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg></span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($totalCustomersCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Unique buyers</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-gray-500">Lifetime spent</p>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg></span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">{{ money($totalLifetimeRevenue) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Across all orders</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-gray-500">VIP customers</p>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/></svg></span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($vipCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Tagged VIP by admin</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-gray-500">Repeat buyers</p>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-50 text-indigo-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg></span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($repeatCustomersCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">2+ completed orders</p>
    </div>
  </div>

  {{-- Segment tabs --}}
  @php
    $tabs = [
      'all' => 'All customers',
      'vip' => 'VIP',
      'repeat' => 'Repeat buyers',
      'new' => 'First-time buyers',
      'blacklisted' => 'Blacklisted',
    ];
  @endphp
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Customer segments">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach($tabs as $key => $label)
        @php $active = $tab === $key; @endphp
        <a href="{{ route('admin.customers.index', array_merge(request()->query(), ['tab' => $key])) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
        </a>
      @endforeach
    </div>
  </nav>

  {{-- Customer list --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <label class="relative flex-1">
        <span class="sr-only">Search customers</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $term }}" placeholder="Search name, phone or city" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm text-gray-800 placeholder-gray-500 focus:bg-white focus:border-gray-200 outline-none transition" />
      </label>
      <div class="flex flex-wrap min-[400px]:flex-nowrap items-center gap-2">
        <label class="relative flex-1 sm:flex-initial max-[399px]:basis-full">
          <span class="sr-only">Sort by</span>
          <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto h-10 rounded-full bg-gray-100 border border-transparent pl-4 pr-9 text-sm font-medium text-gray-800 appearance-none cursor-pointer focus:bg-white focus:border-gray-200 outline-none">
            <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Recent activity</option>
            <option value="spent_desc" {{ $sort === 'spent_desc' ? 'selected' : '' }}>Highest spent</option>
            <option value="orders_desc" {{ $sort === 'orders_desc' ? 'selected' : '' }}>Most orders</option>
            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
          </select>
          <svg class="w-3.5 h-3.5 text-gray-500 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </label>
        <button type="submit" class="h-10 px-5 rounded-full text-white text-sm font-semibold shrink-0 max-[399px]:flex-1" style="background: var(--brand-dark);">Search</button>
      </div>
    </form>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4 lg:pl-5">Customer</th>
            <th class="py-3 px-4">Contact</th>
            <th class="py-3 px-4">Segment</th>
            <th class="py-3 px-4">Orders</th>
            <th class="py-3 px-4 text-right">Lifetime spent</th>
            <th class="py-3 px-4 text-right">Last order</th>
            <th class="py-3 px-4 lg:pr-5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($customers as $c)
            @php
              $cleanPhone = preg_replace('/[^0-9]/', '', $c->customer_phone);
              if (str_starts_with($cleanPhone, '0')) {
                $waPhone = '88' . $cleanPhone;
              } elseif (str_starts_with($cleanPhone, '880')) {
                $waPhone = $cleanPhone;
              } else {
                $waPhone = '880' . $cleanPhone;
              }
            @endphp
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4 lg:pl-5">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full font-semibold text-xs flex items-center justify-center shrink-0 {{ $c->tag === 'VIP' ? 'bg-amber-50 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ strtoupper(substr($c->customer_name ?: 'C', 0, 2)) }}
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="font-semibold text-gray-900 hover:underline block truncate">
                      {{ $c->customer_name ?: 'Valued Customer' }}
                    </a>
                    @if($c->is_blacklisted)
                      <span class="mt-0.5 inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 text-rose-700">Blacklisted</span>
                    @endif
                  </div>
                </div>
              </td>

              <td class="py-3 px-4">
                <p class="text-gray-800 tabular-nums whitespace-nowrap">{{ $c->customer_phone }}</p>
                @if($c->city)
                  <p class="text-[11px] text-gray-400">{{ $c->city }}</p>
                @endif
              </td>

              <td class="py-3 px-4 whitespace-nowrap">
                <form method="POST" action="{{ route('admin.customers.update-segment-tag', $c->customer_phone) }}" class="inline-block">
                  @csrf
                  <select name="segment_tag" onchange="this.form.submit()" aria-label="Segment tag" class="h-7 text-[11px] font-semibold rounded-full pl-2.5 pr-7 border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gray-200 {{ $tagTone($c->tag) }}">
                    <optgroup label="Custom tag">
                      <option value="VIP" {{ ($c->admin_tag === 'VIP') ? 'selected' : '' }}>VIP customer</option>
                      <option value="Wholesale" {{ ($c->admin_tag === 'Wholesale') ? 'selected' : '' }}>Wholesale / bulk</option>
                      <option value="Loyal" {{ ($c->admin_tag === 'Loyal') ? 'selected' : '' }}>Loyal client</option>
                      <option value="Influencer" {{ ($c->admin_tag === 'Influencer') ? 'selected' : '' }}>Influencer / partner</option>
                      <option value="Risk" {{ ($c->admin_tag === 'Risk') ? 'selected' : '' }}>Return risk</option>
                    </optgroup>
                    <optgroup label="Automatic">
                      <option value="auto" {{ empty($c->admin_tag) ? 'selected' : '' }}>
                        Auto ({{ $c->orders_count >= 2 ? 'Repeat Buyer' : 'New Customer' }})
                      </option>
                    </optgroup>
                  </select>
                </form>
              </td>

              <td class="py-3 px-4 whitespace-nowrap">
                <p class="font-semibold text-gray-900 tabular-nums">{{ number_format($c->orders_count) }}</p>
                @if($c->delivered_count > 0)
                  <p class="text-[11px] text-emerald-700 tabular-nums">{{ $c->delivered_count }} delivered</p>
                @endif
              </td>

              <td class="py-3 px-4 text-right font-semibold text-gray-900 tabular-nums whitespace-nowrap">
                {{ money($c->total_spent) }}
              </td>

              <td class="py-3 px-4 text-right text-gray-500 whitespace-nowrap">
                {{ \Illuminate\Support\Carbon::parse($c->last_order_at)->format('d M, Y') }}
              </td>

              <td class="py-3 px-4 lg:pr-5 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="h-8 w-8 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 inline-flex items-center justify-center" title="Chat with {{ $c->customer_name ?: 'Customer' }} on WhatsApp" aria-label="WhatsApp">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.216 8.216 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.01 4.54-3.68 8.23-8.22 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.15.17-.25.25-.42.08-.17.04-.31-.02-.43s-.56-1.34-.76-1.84c-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.66.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.43 1.03 2.6.12.17 1.78 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6-.07.49-.26 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.12-.23-.19-.48-.31z"/></svg>
                  </a>
                  <a href="tel:{{ $c->customer_phone }}" class="h-8 w-8 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 inline-flex items-center justify-center" title="Call {{ $c->customer_phone }}" aria-label="Call">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                  </a>
                  <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="h-8 pl-3 pr-2 rounded-full text-white text-xs font-semibold inline-flex items-center gap-1" style="background: var(--brand-dark);">
                    Profile
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center text-gray-500 text-sm">No customers match your filter or search.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone: one card per customer --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($customers as $c)
        @php
          $cleanPhone = preg_replace('/[^0-9]/', '', $c->customer_phone);
          if (str_starts_with($cleanPhone, '0')) {
            $waPhone = '88' . $cleanPhone;
          } elseif (str_starts_with($cleanPhone, '880')) {
            $waPhone = $cleanPhone;
          } else {
            $waPhone = '880' . $cleanPhone;
          }
        @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3 space-y-2.5">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-9 h-9 rounded-full font-semibold text-xs flex items-center justify-center shrink-0 {{ $c->tag === 'VIP' ? 'bg-amber-50 text-amber-800' : 'bg-white text-gray-700' }}">
                {{ strtoupper(substr($c->customer_name ?: 'C', 0, 2)) }}
              </div>
              <div class="min-w-0">
                <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="font-semibold text-[13px] text-gray-900 hover:underline block truncate">
                  {{ $c->customer_name ?: 'Valued Customer' }}
                </a>
                <p class="text-[11px] text-gray-500 tabular-nums truncate">{{ $c->customer_phone }}@if($c->city) · {{ $c->city }}@endif</p>
              </div>
            </div>
            <div class="text-right shrink-0">
              <p class="text-[13px] font-semibold text-gray-900 tabular-nums">{{ money($c->total_spent) }}</p>
              <p class="text-[11px] text-gray-500 tabular-nums">{{ $c->orders_count }} orders</p>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            <form method="POST" action="{{ route('admin.customers.update-segment-tag', $c->customer_phone) }}" class="inline-block">
              @csrf
              <select name="segment_tag" onchange="this.form.submit()" aria-label="Segment tag" class="h-8 text-[11px] font-semibold rounded-full pl-3 pr-8 border-0 cursor-pointer {{ $tagTone($c->tag) }}">
                <optgroup label="Custom tag">
                  <option value="VIP" {{ ($c->admin_tag === 'VIP') ? 'selected' : '' }}>VIP</option>
                  <option value="Wholesale" {{ ($c->admin_tag === 'Wholesale') ? 'selected' : '' }}>Wholesale</option>
                  <option value="Loyal" {{ ($c->admin_tag === 'Loyal') ? 'selected' : '' }}>Loyal</option>
                  <option value="Influencer" {{ ($c->admin_tag === 'Influencer') ? 'selected' : '' }}>Influencer</option>
                  <option value="Risk" {{ ($c->admin_tag === 'Risk') ? 'selected' : '' }}>Return risk</option>
                </optgroup>
                <optgroup label="Automatic">
                  <option value="auto" {{ empty($c->admin_tag) ? 'selected' : '' }}>
                    Auto ({{ $c->orders_count >= 2 ? 'Repeat' : 'New' }})
                  </option>
                </optgroup>
              </select>
            </form>
            @if($c->is_blacklisted)
              <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700">Blacklisted</span>
            @endif
          </div>

          <div class="flex items-center gap-1.5">
            <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="h-8 px-3 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold inline-flex items-center">WhatsApp</a>
            <a href="tel:{{ $c->customer_phone }}" class="h-8 px-3 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 text-xs font-semibold inline-flex items-center">Call</a>
            <a href="{{ route('admin.customers.show', $c->customer_phone) }}" class="flex-1 h-8 rounded-full text-white text-xs font-semibold inline-flex items-center justify-center" style="background: var(--brand-dark);">Profile</a>
          </div>
        </article>
      @empty
        <div class="py-12 text-center text-sm text-gray-500">No customers found.</div>
      @endforelse
    </div>

    @if($customers->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $customers->links() }}</div>
    @endif
  </div>

</div>
@endsection
