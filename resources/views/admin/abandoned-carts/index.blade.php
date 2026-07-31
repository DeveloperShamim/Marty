@extends('layouts.admin')
@section('title', 'Abandoned Cart Recovery')

@section('content')
<div class="space-y-6 max-w-full">
  <!-- Header Title & Controls -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>🛒</span> Abandoned Cart Recovery
      </h2>
      <p class="text-xs text-gray-500 mt-1">Track uncompleted checkout carts, send WhatsApp/Email reminders, and recover lost revenue.</p>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.abandoned-carts.index') }}" class="flex items-center gap-2 w-full md:w-auto">
      <input type="hidden" name="status" value="{{ $status }}" />
      <div class="relative flex-1 md:w-64">
        <input type="text" name="q" value="{{ $search }}" placeholder="Search customer, phone..." class="inp text-xs pr-8 py-2 w-full" />
        @if($search !== '')
          <a href="{{ route('admin.abandoned-carts.index', ['status' => $status]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
        @endif
      </div>
      <button type="submit" class="btn-primary text-xs px-4 py-2 shrink-0">Search</button>
    </form>
  </div>

  <!-- Status Alerts -->
  @if(session('status'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
      ✓ {{ session('status') }}
    </div>
  @endif

  @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-xs">
      ⚠️ {{ session('error') }}
    </div>
  @endif

  <!-- KPI Metric Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Potential Lost Revenue -->
    <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/80 via-white to-amber-50/30 p-5 shadow-xs">
      <div class="flex items-center justify-between">
        <span class="text-xs font-extrabold text-amber-800 uppercase tracking-wider">Potential Lost Sales</span>
        <span class="h-8 w-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-sm font-bold">💸</span>
      </div>
      <p class="mt-2 text-2xl font-black text-amber-700 font-mono tracking-tight">{{ money($potentialRevenue) }}</p>
      <p class="text-[11px] text-amber-700/80 font-medium mt-1">{{ number_format($abandonedCount + $reminderSentCount) }} unrecovered carts</p>
    </div>

    <!-- Recovered Revenue -->
    <div class="rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/80 via-white to-emerald-50/30 p-5 shadow-xs">
      <div class="flex items-center justify-between">
        <span class="text-xs font-extrabold text-emerald-800 uppercase tracking-wider">Recovered Sales</span>
        <span class="h-8 w-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-sm font-bold">🎉</span>
      </div>
      <p class="mt-2 text-2xl font-black text-emerald-700 font-mono tracking-tight">{{ money($recoveredRevenue) }}</p>
      <p class="text-[11px] text-emerald-600/90 font-bold mt-1">{{ number_format($recoveredCount) }} carts recovered</p>
    </div>

    <!-- Recovery Rate -->
    <div class="rounded-2xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/80 via-white to-indigo-50/30 p-5 shadow-xs">
      <div class="flex items-center justify-between">
        <span class="text-xs font-extrabold text-indigo-800 uppercase tracking-wider">Recovery Rate</span>
        <span class="h-8 w-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-sm font-bold">📈</span>
      </div>
      <p class="mt-2 text-2xl font-black text-indigo-700 tracking-tight">{{ $recoveryRate }}%</p>
      <p class="text-[11px] text-indigo-600/90 font-medium mt-1">Cart recovery success conversion</p>
    </div>

    <!-- Total Tracked Carts -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
      <div class="flex items-center justify-between">
        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Total Tracked Carts</span>
        <span class="h-8 w-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-bold">🛒</span>
      </div>
      <p class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalCartsCount) }}</p>
      <p class="text-[11px] text-slate-400 font-medium mt-1">Total draft carts captured</p>
    </div>
  </div>

  <!-- Status Filter Navigation Tabs -->
  <div class="flex items-center gap-2 border-b border-gray-200 pb-3 overflow-x-auto max-w-full">
    <a href="{{ route('admin.abandoned-carts.index', ['status' => 'all', 'q' => $search]) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $status === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
      All Carts ({{ $totalCartsCount }})
    </a>
    <a href="{{ route('admin.abandoned-carts.index', ['status' => 'abandoned', 'q' => $search]) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $status === 'abandoned' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
      🟠 Abandoned ({{ $abandonedCount }})
    </a>
    <a href="{{ route('admin.abandoned-carts.index', ['status' => 'reminder_sent', 'q' => $search]) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $status === 'reminder_sent' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
      🔵 Reminder Sent ({{ $reminderSentCount }})
    </a>
    <a href="{{ route('admin.abandoned-carts.index', ['status' => 'recovered', 'q' => $search]) }}" class="px-3.5 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $status === 'recovered' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
      🟢 Recovered ({{ $recoveredCount }})
    </a>
  </div>

  <!-- Data Table Container -->
  <div class="card overflow-hidden">
    <div class="overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[750px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3.5 px-4">Customer Details</th>
            <th class="py-3.5 px-4">Cart Items</th>
            <th class="py-3.5 px-3 text-center">Value (৳)</th>
            <th class="py-3.5 px-3 text-center">Status</th>
            <th class="py-3.5 px-3 text-center">Fraud &amp; Trust</th>
            <th class="py-3.5 px-3 text-center">Last Active / Sent</th>
            <th class="py-3.5 px-4 text-right">Recovery Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($carts as $cart)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <!-- Customer Details -->
              <td class="py-4 px-4">
                <div class="space-y-0.5">
                  <p class="font-extrabold text-slate-900 text-xs">
                    {{ $cart->customer_name ?: ($cart->user->name ?? 'Guest Visitor') }}
                  </p>
                  @if($cart->customer_phone)
                    <p class="font-mono text-slate-600 text-[11px]">📞 {{ $cart->customer_phone }}</p>
                  @endif
                  @if($cart->customer_email)
                    <p class="text-slate-400 text-[11px]">✉️ {{ $cart->customer_email }}</p>
                  @endif
                </div>
              </td>

              <!-- Cart Items List -->
              <td class="py-4 px-4">
                @php
                  $items = is_array($cart->cart_data) ? $cart->cart_data : [];
                @endphp
                <div class="space-y-1 max-w-xs">
                  @foreach(array_slice($items, 0, 3) as $item)
                    <div class="flex items-center gap-2 text-[11px]">
                      <span class="font-bold text-slate-800 line-clamp-1">{{ $item['name'] ?? 'Product' }}</span>
                      @if(!empty($item['variant']))
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 rounded">{{ $item['variant'] }}</span>
                      @endif
                      <span class="text-slate-400 font-mono ml-auto">×{{ $item['qty'] ?? 1 }}</span>
                    </div>
                  @endforeach
                  @if(count($items) > 3)
                    <p class="text-[10px] font-bold text-brand-600">+ {{ count($items) - 3 }} more item(s)</p>
                  @endif
                </div>
              </td>

              <!-- Total Value -->
              <td class="py-4 px-3 text-center font-black text-slate-900 font-mono text-sm whitespace-nowrap">
                {{ money($cart->total) }}
              </td>

              <!-- Status Badge -->
              <td class="py-4 px-3 text-center whitespace-nowrap">
                @if($cart->status === 'recovered')
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    🟢 Recovered
                  </span>
                @elseif($cart->status === 'reminder_sent')
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-sky-100 text-sky-800 border border-sky-200">
                    🔵 Reminder Sent
                  </span>
                @else
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                    🟠 Abandoned
                  </span>
                @endif
              </td>

              <!-- Fraud & Trust Badge -->
              <td class="py-4 px-3 text-center whitespace-nowrap">
                @php $fraudBadge = $cart->fraudBadgeInfo(); @endphp
                <span class="px-2.5 py-1 text-[11px] rounded-full {{ $fraudBadge['class'] }}">
                  {{ $fraudBadge['label'] }}
                </span>
              </td>

              <!-- Timestamp -->
              <td class="py-4 px-3 text-center text-slate-500 text-[11px] whitespace-nowrap">
                <p class="font-semibold">{{ $cart->updated_at->diffForHumans() }}</p>
                @if($cart->reminder_sent_at)
                  <p class="text-[10px] text-sky-600 font-medium">Sent: {{ $cart->reminder_sent_at->format('d M, g:i A') }}</p>
                @elseif($cart->recovered_at)
                  <p class="text-[10px] text-emerald-600 font-medium">Recovered: {{ $cart->recovered_at->format('d M, g:i A') }}</p>
                @endif
              </td>

              <!-- Recovery Action Tools -->
              <td class="py-4 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                  @if($cart->isBlacklisted())
                    <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-xl bg-rose-100 text-rose-800 border border-rose-200" title="Phone/Email is on Fraud Blacklist">
                      🚫 Blocked
                    </span>
                  @else
                    <!-- WhatsApp Reminder -->
                    @if($cart->customer_phone)
                      <a href="{{ $cart->whatsAppUrl() }}" target="_blank" rel="noopener" class="px-2.5 py-1 text-[11px] font-extrabold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1 shadow-2xs" title="Send WhatsApp Recovery Message">
                        <span>💬 WhatsApp</span>
                      </a>
                    @endif

                    <!-- Email Reminder -->
                    @if($cart->customer_email && $cart->status !== 'recovered')
                      <form method="POST" action="{{ route('admin.abandoned-carts.send-reminder', $cart) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 text-[11px] font-extrabold rounded-xl bg-sky-600 hover:bg-sky-700 text-white transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Send Email Recovery Link">
                          <span>📧 Email</span>
                        </button>
                      </form>
                    @endif
                  @endif

                  <!-- Copy 1-Click Link -->
                  <button type="button" onclick="navigator.clipboard.writeText('{{ $cart->recoveryUrl() }}'); alert('Recovery URL copied to clipboard!');" class="px-2 py-1 text-[11px] font-bold rounded-xl border border-gray-200 hover:bg-gray-100 text-gray-700 transition cursor-pointer" title="Copy Recovery URL">
                    📋 Copy Link
                  </button>

                  <!-- 1-Click Blacklist Button -->
                  @if($cart->customer_phone && !$cart->isBlacklisted())
                    <form method="POST" action="{{ route('admin.blacklist.store') }}" class="inline">
                      @csrf
                      <input type="hidden" name="type" value="phone" />
                      <input type="hidden" name="value" value="{{ $cart->customer_phone }}" />
                      <input type="hidden" name="reason" value="Blacklisted from Abandoned Cart #{{ $cart->id }}" />
                      <button type="submit" onclick="return confirm('Blacklist phone {{ $cart->customer_phone }}?')" class="px-2 py-1 text-[11px] font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition cursor-pointer" title="Add to Fraud Blacklist">
                        🚫
                      </button>
                    </form>
                  @endif

                  <!-- Mark Recovered -->
                  @if($cart->status !== 'recovered')
                    <form method="POST" action="{{ route('admin.abandoned-carts.mark-recovered', $cart) }}" class="inline">
                      @csrf
                      <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 transition cursor-pointer" title="Mark as Recovered">
                        ✓
                      </button>
                    </form>
                  @endif

                  <!-- Delete -->
                  <form method="POST" action="{{ route('admin.abandoned-carts.destroy', $cart) }}" class="inline" onsubmit="return confirm('Delete this abandoned cart record?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-2 py-1 text-[11px] font-bold rounded-xl text-red-600 hover:bg-red-50 transition cursor-pointer" title="Delete Record">
                      🗑️
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-12 text-slate-400 text-xs">
                No abandoned carts found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($carts->hasPages())
      <div class="p-4 border-t border-gray-100">
        {{ $carts->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
