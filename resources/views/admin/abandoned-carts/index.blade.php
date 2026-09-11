@extends('layouts.admin')
@section('title', 'Abandoned Cart Recovery')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-full">

  {{-- Header Title & Controls --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-gray-200/80 pb-4">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Abandoned Carts</h1>
        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 border border-gray-200">
          {{ number_format($totalCartsCount) }} tracked
        </span>
      </div>
      <p class="text-xs text-gray-500 mt-1">Track uncompleted checkouts, send WhatsApp/Email recovery messages, and recover lost sales.</p>
    </div>

    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto">
      {{-- Search Form --}}
      <form method="GET" action="{{ route('admin.abandoned-carts.index') }}" class="flex items-center gap-2 flex-1 sm:w-auto">
        <input type="hidden" name="status" value="{{ $status }}" />
        <div class="relative flex-1 sm:w-60">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input type="text" name="q" value="{{ $search }}" placeholder="Search customer, phone..." class="inp pl-8 text-xs py-1.5 w-full" />
          @if($search !== '')
            <a href="{{ route('admin.abandoned-carts.index', ['status' => $status]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>
        <button type="submit" class="btn-primary text-xs px-3.5 py-1.5 shrink-0">Search</button>
      </form>

      {{-- Clean Recovered Carts Action --}}
      @if($recoveredCount > 0)
        <form method="POST" action="{{ route('admin.abandoned-carts.prune-recovered') }}" onsubmit="return confirm('Clean all {{ $recoveredCount }} recovered cart records from the database?')">
          @csrf
          <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100 transition-colors shadow-2xs inline-flex items-center gap-1.5 shrink-0 cursor-pointer" title="Prune Recovered Carts">
            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span>Clean Recovered ({{ $recoveredCount }})</span>
          </button>
        </form>
      @endif
    </div>
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold shadow-2xs flex items-center gap-2">
      <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-2xs flex items-center gap-2">
      <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  {{-- KPI Metric Cards Grid (2-col on mobile, 4-col on desktop) --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    {{-- Potential Lost Sales --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-3.5 sm:p-5 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Lost Sales</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="mt-2 sm:mt-3 space-y-0.5">
        <p class="text-base sm:text-xl lg:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate">{{ money($potentialRevenue) }}</p>
        <p class="text-[10px] sm:text-xs text-gray-500 truncate">{{ number_format($abandonedCount + $reminderSentCount) }} unrecovered</p>
      </div>
    </div>

    {{-- Recovered Revenue --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-3.5 sm:p-5 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Recovered</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="mt-2 sm:mt-3 space-y-0.5">
        <p class="text-base sm:text-xl lg:text-2xl font-bold text-emerald-700 font-mono tracking-tight truncate">{{ money($recoveredRevenue) }}</p>
        <p class="text-[10px] sm:text-xs text-emerald-600/90 font-medium truncate">{{ number_format($recoveredCount) }} carts recovered</p>
      </div>
    </div>

    {{-- Recovery Rate --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-3.5 sm:p-5 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Recovery Rate</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center shrink-0">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
      </div>
      <div class="mt-2 sm:mt-3 space-y-0.5">
        <p class="text-base sm:text-xl lg:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate">{{ $recoveryRate }}%</p>
        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Checkout conversion</p>
      </div>
    </div>

    {{-- Total Tracked Carts --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 p-3.5 sm:p-5 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between">
      <div class="flex items-center justify-between gap-1.5">
        <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider truncate">Total Carts</span>
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-gray-100 text-gray-600 border border-gray-200 flex items-center justify-center shrink-0">
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
      </div>
      <div class="mt-2 sm:mt-3 space-y-0.5">
        <p class="text-base sm:text-xl lg:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate">{{ number_format($totalCartsCount) }}</p>
        <p class="text-[10px] sm:text-xs text-gray-500 truncate">Captured checkouts</p>
      </div>
    </div>
  </div>

  {{-- Status Navigation Tabs --}}
  <div class="relative">
    <div class="flex items-center gap-1.5 sm:gap-2 pb-1 overflow-x-auto no-scrollbar scroll-smooth snap-x max-w-full -mx-3 sm:mx-0 px-3 sm:px-0">
      <a href="{{ route('admin.abandoned-carts.index', ['status' => 'all', 'q' => $search]) }}" class="snap-start px-3 py-1.5 text-xs font-semibold rounded-xl transition-all cursor-pointer shrink-0 whitespace-nowrap {{ $status === 'all' ? 'bg-primary text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200/90' }}">
        All Carts ({{ $totalCartsCount }})
      </a>
      <a href="{{ route('admin.abandoned-carts.index', ['status' => 'abandoned', 'q' => $search]) }}" class="snap-start px-3 py-1.5 text-xs font-semibold rounded-xl transition-all cursor-pointer shrink-0 whitespace-nowrap {{ $status === 'abandoned' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200/90' }}">
        Abandoned ({{ $abandonedCount }})
      </a>
      <a href="{{ route('admin.abandoned-carts.index', ['status' => 'reminder_sent', 'q' => $search]) }}" class="snap-start px-3 py-1.5 text-xs font-semibold rounded-xl transition-all cursor-pointer shrink-0 whitespace-nowrap {{ $status === 'reminder_sent' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200/90' }}">
        Reminder Sent ({{ $reminderSentCount }})
      </a>
      <a href="{{ route('admin.abandoned-carts.index', ['status' => 'recovered', 'q' => $search]) }}" class="snap-start px-3 py-1.5 text-xs font-semibold rounded-xl transition-all cursor-pointer shrink-0 whitespace-nowrap {{ $status === 'recovered' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200/90' }}">
        Recovered ({{ $recoveredCount }})
      </a>
    </div>
  </div>

  {{-- Data Container Card --}}
  <div class="card overflow-hidden">
    {{-- Mobile Cards View (< md screens) --}}
    <div class="block md:hidden divide-y divide-gray-100 bg-white">
      @forelse($carts as $cart)
        @php $items = is_array($cart->cart_data) ? $cart->cart_data : []; @endphp
        <div class="p-3.5 space-y-2.5 hover:bg-gray-50/70 transition-colors">
          {{-- Card Header: Customer & Total Amount --}}
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="font-bold text-gray-900 text-sm">
                {{ $cart->customer_name ?: ($cart->user->name ?? 'Guest Visitor') }}
              </p>
              <p class="text-[11px] text-gray-400 mt-0.5">
                {{ $cart->updated_at->diffForHumans() }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-base font-bold text-gray-900 font-mono block">
                {{ money($cart->total) }}
              </span>
              <span class="text-[10px] font-semibold text-gray-400">
                {{ count($items) }} item(s)
              </span>
            </div>
          </div>

          {{-- Customer Contact Bar --}}
          @if($cart->customer_phone || $cart->customer_email)
            <div class="bg-gray-50 rounded-xl p-2 border border-gray-100 flex items-center justify-between gap-2 text-xs">
              <div class="min-w-0 flex-1 truncate">
                @if($cart->customer_email)
                  <p class="text-gray-600 text-[11px] truncate flex items-center gap-1">
                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ $cart->customer_email }}
                  </p>
                @endif
              </div>
              @if($cart->customer_phone)
                <a href="tel:{{ $cart->customer_phone }}" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 font-mono font-semibold text-xs shadow-2xs hover:bg-gray-100 shrink-0">
                  <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  <span class="text-[11px]">{{ $cart->customer_phone }}</span>
                </a>
              @endif
            </div>
          @endif

          {{-- Cart Items Summary --}}
          <div class="bg-gray-50/60 rounded-xl p-2 border border-gray-100 space-y-1">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 block">Items in Cart:</span>
            @foreach(array_slice($items, 0, 2) as $item)
              <div class="flex items-center justify-between gap-2 text-xs">
                <span class="font-medium text-gray-800 line-clamp-1 flex-1">{{ $item['name'] ?? 'Product' }}</span>
                @if(!empty($item['variant']))
                  <span class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.2 rounded shrink-0">{{ $item['variant'] }}</span>
                @endif
                <span class="text-gray-500 font-mono font-bold shrink-0">&times;{{ $item['qty'] ?? 1 }}</span>
              </div>
            @endforeach
            @if(count($items) > 2)
              <p class="text-[10px] font-semibold text-primary pt-0.5">+ {{ count($items) - 2 }} more item(s)</p>
            @endif
          </div>

          {{-- Badges Row --}}
          <div class="flex items-center gap-1.5 flex-wrap text-xs">
            @if($cart->status === 'recovered')
              <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                Recovered
              </span>
            @elseif($cart->status === 'reminder_sent')
              <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                Reminder Sent
              </span>
            @else
              <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                Abandoned
              </span>
            @endif

            @php $fraudBadge = $cart->fraudBadgeInfo(); @endphp
            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $fraudBadge['class'] }}">
              {{ $fraudBadge['label'] }}
            </span>

            @if($cart->reminder_sent_at)
              <span class="text-[10px] text-blue-600 font-medium ml-auto">
                Sent: {{ $cart->reminder_sent_at->format('d M, g:i A') }}
              </span>
            @endif
          </div>

          {{-- Mobile Action Buttons --}}
          <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-1.5 flex-wrap">
            @if($cart->isBlacklisted())
              <span class="px-3 py-1.5 text-[11px] font-semibold rounded-xl bg-rose-100 text-rose-800 border border-rose-200 w-full text-center">
                Fraud Blocked
              </span>
            @else
              @if($cart->customer_phone)
                <a href="{{ $cart->whatsAppUrl() }}" target="_blank" rel="noopener" class="flex-1 py-1.5 px-2.5 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors inline-flex items-center justify-center gap-1 shadow-2xs">
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                  <span>WhatsApp</span>
                </a>
              @endif

              @if($cart->customer_email && $cart->status !== 'recovered')
                <form method="POST" action="{{ route('admin.abandoned-carts.send-reminder', $cart) }}" class="inline flex-1">
                  @csrf
                  <button type="submit" class="w-full py-1.5 px-2.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors inline-flex items-center justify-center gap-1 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Email</span>
                  </button>
                </form>
              @endif
            @endif

            {{-- Copy Recovery Link --}}
            <button type="button" onclick="navigator.clipboard.writeText('{{ $cart->recoveryUrl() }}'); alert('Recovery URL copied to clipboard!');" class="py-1.5 px-2 text-xs font-semibold rounded-xl border border-gray-200 hover:bg-gray-100 text-gray-700 transition-colors cursor-pointer inline-flex items-center gap-1" title="Copy Recovery URL">
              <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
              <span>Link</span>
            </button>

            {{-- Mark Recovered --}}
            @if($cart->status !== 'recovered')
              <form method="POST" action="{{ route('admin.abandoned-carts.mark-recovered', $cart) }}" class="inline">
                @csrf
                <button type="submit" class="py-1.5 px-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 transition-colors cursor-pointer" title="Mark as Recovered">
                  ✓
                </button>
              </form>
            @endif

            {{-- Delete --}}
            <form method="POST" action="{{ route('admin.abandoned-carts.destroy', $cart) }}" class="inline" onsubmit="return confirm('Delete this abandoned cart record?')">
              @csrf @method('DELETE')
              <button type="submit" class="py-1.5 px-2 text-xs font-semibold rounded-xl text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Delete Record">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="text-center py-12 text-gray-400 text-xs px-4">
          No abandoned carts found.
        </div>
      @endforelse
    </div>

    {{-- Desktop Table View (>= md screens) --}}
    <div class="hidden md:block overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-200 whitespace-nowrap">
            <th class="py-3 px-3 lg:px-4">Customer Details</th>
            <th class="py-3 px-3 lg:px-4">Cart Items</th>
            <th class="py-3 px-3 lg:px-3 text-center">Value</th>
            <th class="py-3 px-3 lg:px-3 text-center">Status</th>
            <th class="py-3 px-3 lg:px-3 text-center">Trust Risk</th>
            <th class="py-3 px-3 lg:px-3 text-center">Last Active</th>
            <th class="py-3 px-3 lg:px-4 text-right">Recovery Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($carts as $cart)
            @php $items = is_array($cart->cart_data) ? $cart->cart_data : []; @endphp
            <tr class="hover:bg-gray-50/70 transition-colors whitespace-nowrap">
              {{-- Customer Details --}}
              <td class="py-3 px-3 lg:px-4">
                <div class="space-y-0.5">
                  <p class="font-bold text-gray-900 text-xs">
                    {{ $cart->customer_name ?: ($cart->user->name ?? 'Guest Visitor') }}
                  </p>
                  @if($cart->customer_phone)
                    <p class="font-mono text-gray-600 text-[11px]">{{ $cart->customer_phone }}</p>
                  @endif
                  @if($cart->customer_email)
                    <p class="text-gray-400 text-[11px]">{{ $cart->customer_email }}</p>
                  @endif
                </div>
              </td>

              {{-- Cart Items List --}}
              <td class="py-3 px-3 lg:px-4">
                <div class="space-y-1 max-w-[200px] lg:max-w-xs">
                  @foreach(array_slice($items, 0, 2) as $item)
                    <div class="flex items-center gap-1.5 text-[11px]">
                      <span class="font-semibold text-gray-800 truncate max-w-[120px]">{{ $item['name'] ?? 'Product' }}</span>
                      @if(!empty($item['variant']))
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1 rounded">{{ $item['variant'] }}</span>
                      @endif
                      <span class="text-gray-400 font-mono ml-auto">&times;{{ $item['qty'] ?? 1 }}</span>
                    </div>
                  @endforeach
                  @if(count($items) > 2)
                    <p class="text-[10px] font-semibold text-primary">+ {{ count($items) - 2 }} more item(s)</p>
                  @endif
                </div>
              </td>

              {{-- Total Value --}}
              <td class="py-3 px-3 lg:px-3 text-center font-bold text-gray-900 font-mono text-xs sm:text-sm whitespace-nowrap">
                {{ money($cart->total) }}
              </td>

              {{-- Status Badge --}}
              <td class="py-3 px-3 lg:px-3 text-center whitespace-nowrap">
                @if($cart->status === 'recovered')
                  <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Recovered
                  </span>
                @elseif($cart->status === 'reminder_sent')
                  <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                    Reminder Sent
                  </span>
                @else
                  <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                    Abandoned
                  </span>
                @endif
              </td>

              {{-- Fraud & Trust Badge --}}
              <td class="py-3 px-3 lg:px-3 text-center whitespace-nowrap">
                @php $fraudBadge = $cart->fraudBadgeInfo(); @endphp
                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $fraudBadge['class'] }}">
                  {{ $fraudBadge['label'] }}
                </span>
              </td>

              {{-- Timestamp --}}
              <td class="py-3 px-3 lg:px-3 text-center text-gray-500 text-[11px] whitespace-nowrap">
                <p class="font-medium">{{ $cart->updated_at->diffForHumans() }}</p>
                @if($cart->reminder_sent_at)
                  <p class="text-[10px] text-blue-600 font-medium">Sent: {{ $cart->reminder_sent_at->format('d M, g:i A') }}</p>
                @elseif($cart->recovered_at)
                  <p class="text-[10px] text-emerald-600 font-medium">Recovered: {{ $cart->recovered_at->format('d M, g:i A') }}</p>
                @endif
              </td>

              {{-- Recovery Action Tools --}}
              <td class="py-3 px-3 lg:px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">

                  @if($cart->isBlacklisted())
                    <span class="px-2.5 py-1 text-[10px] font-semibold rounded-xl bg-rose-100 text-rose-800 border border-rose-200" title="Phone/Email is on Fraud Blacklist">
                      Blocked
                    </span>
                  @else
                    {{-- WhatsApp Reminder --}}
                    @if($cart->customer_phone)
                      <a href="{{ $cart->whatsAppUrl() }}" target="_blank" rel="noopener" class="px-2.5 py-1 text-[11px] font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors inline-flex items-center gap-1 shadow-2xs" title="Send WhatsApp Recovery Message">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp</span>
                      </a>
                    @endif

                    {{-- Email Reminder --}}
                    @if($cart->customer_email && $cart->status !== 'recovered')
                      <form method="POST" action="{{ route('admin.abandoned-carts.send-reminder', $cart) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors inline-flex items-center gap-1 shadow-2xs cursor-pointer" title="Send Email Recovery Link">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                          <span>Email</span>
                        </button>
                      </form>
                    @endif
                  @endif

                  {{-- Copy Link --}}
                  <button type="button" onclick="navigator.clipboard.writeText('{{ $cart->recoveryUrl() }}'); alert('Recovery URL copied to clipboard!');" class="px-2 py-1 text-[11px] font-semibold rounded-xl border border-gray-200 hover:bg-gray-100 text-gray-700 transition-colors cursor-pointer inline-flex items-center gap-1" title="Copy Recovery URL">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <span>Link</span>
                  </button>

                  {{-- Blacklist Phone --}}
                  @if($cart->customer_phone && !$cart->isBlacklisted())
                    <form method="POST" action="{{ route('admin.blacklist.store') }}" class="inline">
                      @csrf
                      <input type="hidden" name="type" value="phone" />
                      <input type="hidden" name="value" value="{{ $cart->customer_phone }}" />
                      <input type="hidden" name="reason" value="Blacklisted from Abandoned Cart #{{ $cart->id }}" />
                      <button type="submit" onclick="return confirm('Blacklist phone {{ $cart->customer_phone }}?')" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer inline-flex items-center justify-center text-xs" title="Add to Fraud Blacklist">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                      </button>
                    </form>
                  @endif

                  {{-- Mark Recovered --}}
                  @if($cart->status !== 'recovered')
                    <form method="POST" action="{{ route('admin.abandoned-carts.mark-recovered', $cart) }}" class="inline">
                      @csrf
                      <button type="submit" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 transition-colors cursor-pointer inline-flex items-center justify-center text-xs" title="Mark as Recovered">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                      </button>
                    </form>
                  @endif

                  {{-- Delete --}}
                  <form method="POST" action="{{ route('admin.abandoned-carts.destroy', $cart) }}" class="inline" onsubmit="return confirm('Delete this abandoned cart record?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-7 h-7 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer inline-flex items-center justify-center text-xs" title="Delete Record">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-12 text-gray-400 text-xs">
                No abandoned carts found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($carts->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">
        {{ $carts->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
