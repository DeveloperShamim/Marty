@extends('layouts.admin')
@section('title', 'Blacklist')
@section('subtitle', 'Block phone numbers, IP addresses or emails from placing orders.')

@php
  $typeMeta = [
    'phone' => ['Phone', 'bg-rose-50 text-rose-700'],
    'ip'    => ['IP', 'bg-amber-50 text-amber-700'],
    'email' => ['Email', 'bg-sky-50 text-sky-700'],
  ];
@endphp

@section('content')
<div class="space-y-4">

  {{-- Stats --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach([
      ['Total blocked', $totalCount, 'Active entries', 'bg-gray-100 text-gray-700', '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
      ['Phones', $phoneCount, 'Phone numbers', 'bg-rose-50 text-rose-700', '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>'],
      ['IP addresses', $ipCount, 'Networks', 'bg-amber-50 text-amber-700', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>'],
      ['Emails', $emailCount, 'Email addresses', 'bg-sky-50 text-sky-700', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>'],
    ] as [$label, $value, $hint, $tone, $icon])
      <div class="panel p-3.5 sm:p-4">
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs text-gray-500">{{ $label }}</span>
          <span class="grid h-8 w-8 place-items-center rounded-xl {{ $tone }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
          </span>
        </div>
        <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($value) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $hint }}</p>
      </div>
    @endforeach
  </div>

  {{-- Add entry --}}
  <section class="panel p-4 sm:p-5">
    <h2 class="text-[15px] font-semibold text-gray-900">Add to blacklist</h2>
    <p class="text-xs text-gray-500 mt-0.5">Orders from this value will be blocked at checkout.</p>
    <form method="POST" action="{{ route('admin.blacklist.store') }}" class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[auto_1fr_1fr_auto] gap-2">
      @csrf
      <select name="type" required class="h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm text-gray-800 focus:bg-white focus:border-gray-200 outline-none">
        <option value="phone">Phone number</option>
        <option value="ip">IP address</option>
        <option value="email">Email address</option>
      </select>
      <input type="text" name="value" placeholder="e.g. 01700000000 or 192.168.1.1" required class="h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm focus:bg-white focus:border-gray-200 outline-none" />
      <input type="text" name="reason" placeholder="Reason (optional)" class="h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm focus:bg-white focus:border-gray-200 outline-none" />
      <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold inline-flex items-center justify-center gap-1.5" style="background: var(--brand-dark);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Add entry
      </button>
    </form>
  </section>

  {{-- Tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Entry type">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach(['all' => ['All', $totalCount], 'phone' => ['Phones', $phoneCount], 'ip' => ['IPs', $ipCount], 'email' => ['Emails', $emailCount]] as $key => [$label, $count])
        @php $active = $type === $key; @endphp
        <a href="{{ route('admin.blacklist.index', ['type' => $key, 'q' => $search]) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center gap-1.5 transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>
          {{ $label }}
          <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-semibold leading-5 text-center tabular-nums {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $count }}</span>
        </a>
      @endforeach
    </div>
  </nav>

  {{-- List --}}
  <div class="card overflow-hidden">
    <form method="GET" action="{{ route('admin.blacklist.index') }}" class="p-3 sm:p-4 flex items-center gap-2">
      <input type="hidden" name="type" value="{{ $type }}" />
      <label class="relative flex-1">
        <span class="sr-only">Search blacklist</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search value or reason" class="w-full h-10 pl-10 pr-9 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
        @if($search !== '')
          <a href="{{ route('admin.blacklist.index', ['type' => $type]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-700" aria-label="Clear search">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </a>
        @endif
      </label>
      <button type="submit" class="h-10 px-4 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium shrink-0">Search</button>
    </form>

    {{-- Desktop table --}}
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4">Value</th>
            <th class="py-3 px-4">Reason</th>
            <th class="py-3 px-4">Added</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($items as $item)
            @php [$tLabel, $tTone] = $typeMeta[$item->type] ?? $typeMeta['email']; @endphp
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tTone }}">{{ $tLabel }}</span></td>
              <td class="py-3 px-4 font-mono font-medium text-gray-900">{{ $item->value }}</td>
              <td class="py-3 px-4 text-gray-600">{{ $item->reason ?: 'Flagged for suspicious fraud activity' }}</td>
              <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ $item->created_at->format('d M Y, g:i A') }}</td>
              <td class="py-3 px-4 text-right">
                <form method="POST" action="{{ route('admin.blacklist.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove {{ $item->value }} from blacklist?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Remove</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center py-10 text-gray-500 text-sm">No blacklisted entries found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone cards --}}
    <div class="sm:hidden px-3 pb-3 space-y-2">
      @forelse($items as $item)
        @php [$tLabel, $tTone] = $typeMeta[$item->type] ?? $typeMeta['email']; @endphp
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tTone }}">{{ $tLabel }}</span>
            <span class="text-[11px] text-gray-500">{{ $item->created_at->format('d M Y, g:i A') }}</span>
          </div>
          <p class="font-mono font-medium text-sm text-gray-900 break-all">{{ $item->value }}</p>
          @if($item->reason)
            <p class="text-xs text-gray-600">{{ $item->reason }}</p>
          @endif
          <div class="flex justify-end">
            <form method="POST" action="{{ route('admin.blacklist.destroy', $item) }}" onsubmit="return confirm('Remove {{ $item->value }} from blacklist?')">
              @csrf @method('DELETE')
              <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold cursor-pointer">Remove</button>
            </form>
          </div>
        </article>
      @empty
        <div class="py-10 text-center text-sm text-gray-500">No blacklisted entries found.</div>
      @endforelse
    </div>

    @if($items->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $items->links() }}</div>
    @endif
  </div>
</div>
@endsection
