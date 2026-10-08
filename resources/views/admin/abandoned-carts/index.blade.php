@extends('layouts.admin')
@section('title', 'Abandoned Cart Recovery')
@section('subtitle', 'Follow up on unfinished checkouts by WhatsApp or email and win back lost sales.')

@if($recoveredCount > 0)
  @section('page-actions')
    <form method="POST" action="{{ route('admin.abandoned-carts.prune-recovered') }}" onsubmit="return confirm('Clean all {{ $recoveredCount }} recovered cart records from the database?')">
      @csrf
      <button type="submit" class="pill-btn cursor-pointer" title="Prune recovered carts">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        Clean recovered ({{ $recoveredCount }})
      </button>
    </form>
  @endsection
@endif

@php
  $statusPill = [
    'recovered'     => ['Recovered', 'bg-emerald-50 text-emerald-700'],
    'reminder_sent' => ['Reminder sent', 'bg-sky-50 text-sky-700'],
    'abandoned'     => ['Abandoned', 'bg-amber-50 text-amber-700'],
  ];
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Stats --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
      ['Lost sales', money($potentialRevenue), number_format($abandonedCount + $reminderSentCount).' not recovered', 'bg-amber-50 text-amber-700', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
      ['Recovered', money($recoveredRevenue), number_format($recoveredCount).' carts recovered', 'bg-emerald-50 text-emerald-700', '<path d="M20 6 9 17l-5-5"/>'],
      ['Recovery rate', $recoveryRate.'%', 'Checkout conversion', 'bg-sky-50 text-sky-700', '<path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/>'],
      ['Total carts', number_format($totalCartsCount), 'Captured checkouts', 'bg-gray-100 text-gray-700', '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 7H6"/>'],
    ] as [$label, $value, $hint, $tone, $icon])
      <div class="panel p-3.5 sm:p-4 min-w-0">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs text-gray-500 truncate">{{ $label }}</span>
          <span class="grid h-8 w-8 place-items-center rounded-xl shrink-0 {{ $tone }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
          </span>
        </div>
        <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums truncate">{{ $value }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  {{-- Status tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Cart status">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach(['all' => ['All carts', $totalCartsCount], 'abandoned' => ['Abandoned', $abandonedCount], 'reminder_sent' => ['Reminder sent', $reminderSentCount], 'recovered' => ['Recovered', $recoveredCount]] as $key => [$label, $count])
        @php $active = $status === $key; @endphp
        <a href="{{ route('admin.abandoned-carts.index', ['status' => $key, 'q' => $search]) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
          <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $count }}</span>
        </a>
      @endforeach
    </div>
  </nav>

  {{-- Cart list --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.abandoned-carts.index') }}" class="p-3 sm:p-4 flex items-center gap-2">
      <input type="hidden" name="status" value="{{ $status }}" />
      <label class="relative flex-1">
        <span class="sr-only">Search carts</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search customer or phone" class="w-full h-10 pl-10 pr-9 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
        @if($search !== '')
          <a href="{{ route('admin.abandoned-carts.index', ['status' => $status]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-700" aria-label="Clear search">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </a>
        @endif
      </label>
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0" style="background: var(--brand-dark);">Search</button>
    </form>

    {{-- Phone cards --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($carts as $cart)
        @php
          $items = is_array($cart->cart_data) ? $cart->cart_data : [];
          [$sLabel, $sTone] = $statusPill[$cart->status] ?? $statusPill['abandoned'];
          $fraudBadge = $cart->fraudBadgeInfo();
        @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2.5">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="font-semibold text-gray-900 text-sm truncate">{{ $cart->customer_name ?: ($cart->user->name ?? 'Guest visitor') }}</p>
              <p class="text-[11px] text-gray-500 mt-0.5">{{ $cart->updated_at->diffForHumans() }} · {{ count($items) }} {{ Str::plural('item', count($items)) }}</p>
            </div>
            <p class="text-[15px] font-semibold text-gray-900 tabular-nums shrink-0">{{ money($cart->total) }}</p>
          </div>

          @if($cart->customer_phone || $cart->customer_email)
            <div class="flex items-center justify-between gap-2">
              <p class="text-[11px] text-gray-500 truncate min-w-0">{{ $cart->customer_email }}</p>
              @if($cart->customer_phone)
                <a href="tel:{{ $cart->customer_phone }}" class="h-8 px-3 rounded-full bg-white ring-1 ring-gray-200 text-gray-800 text-[11px] font-semibold tabular-nums inline-flex items-center gap-1.5 shrink-0">
                  <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                  {{ $cart->customer_phone }}
                </a>
              @endif
            </div>
          @endif

          <div class="space-y-1">
            @foreach(array_slice($items, 0, 2) as $item)
              <div class="flex items-center justify-between gap-2 text-xs">
                <span class="text-gray-800 line-clamp-1 flex-1">{{ $item['name'] ?? 'Product' }}</span>
                @if(!empty($item['variant']))
                  <span class="text-[11px] text-gray-500 shrink-0">{{ $item['variant'] }}</span>
                @endif
                <span class="text-gray-500 tabular-nums shrink-0">&times;{{ $item['qty'] ?? 1 }}</span>
              </div>
            @endforeach
            @if(count($items) > 2)
              <p class="text-[11px] text-gray-500">+ {{ count($items) - 2 }} more</p>
            @endif
          </div>

          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $sTone }}">{{ $sLabel }}</span>
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ ['blacklisted' => 'bg-rose-50 text-rose-700', 'warning' => 'bg-amber-50 text-amber-700'][$fraudBadge['level']] ?? 'bg-gray-100 text-gray-600' }}">{{ trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $fraudBadge['label'])) }}</span>
            @if($cart->reminder_sent_at)
              <span class="text-[11px] text-gray-500 ml-auto">Sent {{ $cart->reminder_sent_at->format('d M, g:i A') }}</span>
            @endif
          </div>

          <div class="flex items-center gap-1.5">
            @if($cart->isBlacklisted())
              <span class="flex-1 h-8 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold inline-flex items-center justify-center">Blocked</span>
            @else
              @if($cart->customer_phone)
                <a href="{{ $cart->whatsAppUrl() }}" target="_blank" rel="noopener" class="flex-1 h-8 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold inline-flex items-center justify-center gap-1.5">
                  <svg class="w-3.5 h-3.5" aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                  WhatsApp
                </a>
              @endif
              @if($cart->customer_email && $cart->status !== 'recovered')
                <form method="POST" action="{{ route('admin.abandoned-carts.send-reminder', $cart) }}" class="flex-1">
                  @csrf
                  <button type="submit" class="w-full h-8 rounded-full text-white text-xs font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer" style="background: var(--brand-dark);">Email</button>
                </form>
              @endif
            @endif
            <button type="button" onclick="navigator.clipboard.writeText('{{ $cart->recoveryUrl() }}'); alert('Recovery URL copied to clipboard!');" class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 inline-flex items-center justify-center shrink-0 cursor-pointer" title="Copy recovery link" aria-label="Copy recovery link">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
            </button>
            @if($cart->status !== 'recovered')
              <form method="POST" action="{{ route('admin.abandoned-carts.mark-recovered', $cart) }}" class="shrink-0">
                @csrf
                <button type="submit" class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 inline-flex items-center justify-center cursor-pointer" title="Mark as recovered" aria-label="Mark as recovered">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </button>
              </form>
            @endif
            <form method="POST" action="{{ route('admin.abandoned-carts.destroy', $cart) }}" class="shrink-0" onsubmit="return confirm('Delete this abandoned cart record?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 inline-flex items-center justify-center cursor-pointer" title="Delete record" aria-label="Delete record">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              </button>
            </form>
          </div>
        </article>
      @empty
        <div class="text-center py-10 text-gray-500 text-sm">No abandoned carts found.</div>
      @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto w-full">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="whitespace-nowrap border-y border-gray-100">
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Items</th>
            <th class="py-3 px-4">Value</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Last active</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($carts as $cart)
            @php
              $items = is_array($cart->cart_data) ? $cart->cart_data : [];
              [$sLabel, $sTone] = $statusPill[$cart->status] ?? $statusPill['abandoned'];
              $fraudBadge = $cart->fraudBadgeInfo();
            @endphp
            <tr class="align-top">
              <td class="py-3 px-4">
                <p class="font-semibold text-gray-900">{{ $cart->customer_name ?: ($cart->user->name ?? 'Guest visitor') }}</p>
                @if($cart->customer_phone)
                  <p class="text-gray-500 text-[11.5px] tabular-nums mt-0.5">{{ $cart->customer_phone }}</p>
                @endif
                @if($cart->customer_email)
                  <p class="text-gray-400 text-[11px]">{{ $cart->customer_email }}</p>
                @endif
              </td>
              <td class="py-3 px-4">
                <div class="space-y-0.5 max-w-[220px] lg:max-w-xs">
                  @foreach(array_slice($items, 0, 2) as $item)
                    <div class="flex items-center gap-1.5 text-xs">
                      <span class="text-gray-800 truncate">{{ $item['name'] ?? 'Product' }}</span>
                      @if(!empty($item['variant']))
                        <span class="text-[11px] text-gray-500 shrink-0">{{ $item['variant'] }}</span>
                      @endif
                      <span class="text-gray-400 tabular-nums ml-auto shrink-0">&times;{{ $item['qty'] ?? 1 }}</span>
                    </div>
                  @endforeach
                  @if(count($items) > 2)
                    <p class="text-[11px] text-gray-500">+ {{ count($items) - 2 }} more</p>
                  @endif
                </div>
              </td>
              <td class="py-3 px-4 font-semibold text-gray-900 tabular-nums whitespace-nowrap">{{ money($cart->total) }}</td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $sTone }}">{{ $sLabel }}</span>
                <span class="mt-1 block w-fit px-2 py-0.5 text-[11px] font-semibold rounded-full {{ ['blacklisted' => 'bg-rose-50 text-rose-700', 'warning' => 'bg-amber-50 text-amber-700'][$fraudBadge['level']] ?? 'bg-gray-100 text-gray-600' }}">{{ trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', $fraudBadge['label'])) }}</span>
              </td>
              <td class="py-3 px-4 text-gray-500 text-xs whitespace-nowrap">
                <p>{{ $cart->updated_at->diffForHumans() }}</p>
                @if($cart->reminder_sent_at)
                  <p class="text-[11px] text-gray-400">Sent {{ $cart->reminder_sent_at->format('d M, g:i A') }}</p>
                @elseif($cart->recovered_at)
                  <p class="text-[11px] text-gray-400">Recovered {{ $cart->recovered_at->format('d M, g:i A') }}</p>
                @endif
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  @if($cart->isBlacklisted())
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-rose-50 text-rose-700" title="Phone or email is on the blacklist">Blocked</span>
                  @else
                    @if($cart->customer_phone)
                      <a href="{{ $cart->whatsAppUrl() }}" target="_blank" rel="noopener" class="h-8 px-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold inline-flex items-center gap-1.5" title="Send WhatsApp recovery message">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        WhatsApp
                      </a>
                    @endif
                    @if($cart->customer_email && $cart->status !== 'recovered')
                      <form method="POST" action="{{ route('admin.abandoned-carts.send-reminder', $cart) }}" class="inline">
                        @csrf
                        <button type="submit" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold cursor-pointer" title="Send email recovery link">Email</button>
                      </form>
                    @endif
                  @endif
                  <button type="button" onclick="navigator.clipboard.writeText('{{ $cart->recoveryUrl() }}'); alert('Recovery URL copied to clipboard!');" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center cursor-pointer" title="Copy recovery link" aria-label="Copy recovery link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
                  </button>
                  @if($cart->customer_phone && !$cart->isBlacklisted())
                    <form method="POST" action="{{ route('admin.blacklist.store') }}" class="inline">
                      @csrf
                      <input type="hidden" name="type" value="phone" />
                      <input type="hidden" name="value" value="{{ $cart->customer_phone }}" />
                      <input type="hidden" name="reason" value="Blacklisted from Abandoned Cart #{{ $cart->id }}" />
                      <button type="submit" onclick="return confirm('Blacklist phone {{ $cart->customer_phone }}?')" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-rose-50 text-gray-600 hover:text-rose-700 inline-flex items-center justify-center cursor-pointer" title="Add to blacklist" aria-label="Add to blacklist">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/></svg>
                      </button>
                    </form>
                  @endif
                  @if($cart->status !== 'recovered')
                    <form method="POST" action="{{ route('admin.abandoned-carts.mark-recovered', $cart) }}" class="inline">
                      @csrf
                      <button type="submit" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center cursor-pointer" title="Mark as recovered" aria-label="Mark as recovered">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                      </button>
                    </form>
                  @endif
                  <form method="POST" action="{{ route('admin.abandoned-carts.destroy', $cart) }}" class="inline" onsubmit="return confirm('Delete this abandoned cart record?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 inline-flex items-center justify-center cursor-pointer" title="Delete record" aria-label="Delete record">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-12 text-gray-500 text-sm">No abandoned carts found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($carts->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $carts->links() }}</div>
    @endif
  </div>

</div>
@endsection
