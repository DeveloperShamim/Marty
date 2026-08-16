@extends('layouts.admin')
@section('title', 'Fraud Blacklist Management')

@section('content')
<div class="space-y-4 sm:space-y-6">
  <!-- Header Title & Controls -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2">
        <span>🛡️</span> Fraud Blacklist Management
      </h2>
      <p class="text-xs text-gray-500 mt-0.5">Block malicious phone numbers, IP addresses, or emails from placing orders on your store.</p>
    </div>

    <!-- Quick Add Form -->
    <form method="POST" action="{{ route('admin.blacklist.store') }}" class="w-full md:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-50 p-3 sm:p-0 rounded-2xl sm:bg-transparent border sm:border-0 border-slate-200">
      @csrf
      <select name="type" required class="text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 font-bold focus:outline-none focus:ring-2 focus:ring-primary shadow-2xs w-full sm:w-auto">
        <option value="phone">📞 Phone Number</option>
        <option value="ip">🌐 IP Address</option>
        <option value="email">✉️ Email Address</option>
      </select>
      <input type="text" name="value" placeholder="e.g. 01700000000 or 192.168.1.1" required class="inp text-xs py-2 w-full sm:w-48" />
      <input type="text" name="reason" placeholder="Reason (optional)" class="inp text-xs py-2 w-full sm:w-40" />
      <button type="submit" class="btn-primary text-xs px-3.5 py-2 shrink-0 flex items-center justify-center gap-1">
        + Add to Blacklist
      </button>
    </form>
  </div>

  <!-- Status Alert Messages -->
  @if(session('status'))
    <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs flex items-center gap-2">
      <span>✓</span> <span>{{ session('status') }}</span>
    </div>
  @endif

  <!-- KPI Cards Grid -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <div class="card p-3.5 sm:p-4">
      <span class="text-[10px] sm:text-xs font-extrabold text-slate-400 uppercase tracking-wider block">Total Blacklisted</span>
      <p class="mt-1 text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Active blocked entries</p>
    </div>
    <div class="card p-3.5 sm:p-4 border-l-4 border-l-rose-500">
      <span class="text-[10px] sm:text-xs font-extrabold text-slate-400 uppercase tracking-wider block">Blocked Phones</span>
      <p class="mt-1 text-xl sm:text-2xl font-black text-rose-700 font-mono tracking-tight">{{ number_format($phoneCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-rose-600/80 font-medium mt-0.5">Phone numbers</p>
    </div>
    <div class="card p-3.5 sm:p-4 border-l-4 border-l-amber-500">
      <span class="text-[10px] sm:text-xs font-extrabold text-slate-400 uppercase tracking-wider block">Blocked IPs</span>
      <p class="mt-1 text-xl sm:text-2xl font-black text-amber-700 font-mono tracking-tight">{{ number_format($ipCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-amber-600/80 font-medium mt-0.5">IP addresses</p>
    </div>
    <div class="card p-3.5 sm:p-4 border-l-4 border-l-indigo-500">
      <span class="text-[10px] sm:text-xs font-extrabold text-slate-400 uppercase tracking-wider block">Blocked Emails</span>
      <p class="mt-1 text-xl sm:text-2xl font-black text-indigo-700 font-mono tracking-tight">{{ number_format($emailCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-indigo-600/80 font-medium mt-0.5">Email addresses</p>
    </div>
  </div>

  <!-- Type Filter Tabs & Search -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-3">
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
      <a href="{{ route('admin.blacklist.index', ['type' => 'all', 'q' => $search]) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $type === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        All ({{ $totalCount }})
      </a>
      <a href="{{ route('admin.blacklist.index', ['type' => 'phone', 'q' => $search]) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $type === 'phone' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        📞 Phones ({{ $phoneCount }})
      </a>
      <a href="{{ route('admin.blacklist.index', ['type' => 'ip', 'q' => $search]) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $type === 'ip' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        🌐 IPs ({{ $ipCount }})
      </a>
      <a href="{{ route('admin.blacklist.index', ['type' => 'email', 'q' => $search]) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl transition cursor-pointer shrink-0 {{ $type === 'email' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
        ✉️ Emails ({{ $emailCount }})
      </a>
    </div>

    <form method="GET" action="{{ route('admin.blacklist.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
      <input type="hidden" name="type" value="{{ $type }}" />
      <div class="relative flex-1 sm:w-48">
        <input type="text" name="q" value="{{ $search }}" placeholder="Search value, reason..." class="inp text-xs pr-8 py-1.5 w-full" />
        @if($search !== '')
          <a href="{{ route('admin.blacklist.index', ['type' => $type]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
        @endif
      </div>
      <button type="submit" class="btn-secondary text-xs px-3.5 py-1.5 shrink-0">Search</button>
    </form>
  </div>

  <!-- Data Table / Mobile Card Container -->
  <div class="card overflow-hidden">
    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider">
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4">Blocked Value</th>
            <th class="py-3 px-4">Reason / Notes</th>
            <th class="py-3 px-4 text-center">Added Date</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($items as $item)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3.5 px-4">
                @if($item->type === 'phone')
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-rose-100 text-rose-800 border border-rose-200">📞 Phone</span>
                @elseif($item->type === 'ip')
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">🌐 IP</span>
                @else
                  <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">✉️ Email</span>
                @endif
              </td>
              <td class="py-3.5 px-4 font-mono font-black text-slate-900 text-xs">
                {{ $item->value }}
              </td>
              <td class="py-3.5 px-4 text-slate-600">
                {{ $item->reason ?: 'Flagged for suspicious fraud activity' }}
              </td>
              <td class="py-3.5 px-4 text-center text-slate-500 font-medium">
                {{ $item->created_at->format('d M Y, g:i A') }}
              </td>
              <td class="py-3.5 px-4 text-right">
                <form method="POST" action="{{ route('admin.blacklist.destroy', $item) }}" class="inline" onsubmit="return confirm('Remove {{ $item->value }} from blacklist?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl text-red-600 hover:bg-red-50 border border-red-200 transition cursor-pointer">
                    🗑️ Remove
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-10 text-slate-400 text-xs">
                No blacklisted entries found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="block sm:hidden divide-y divide-slate-100 bg-white">
      @forelse($items as $item)
        <div class="p-3.5 space-y-2.5">
          <div class="flex items-center justify-between gap-2">
            @if($item->type === 'phone')
              <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-100 text-rose-800 border border-rose-200">📞 Phone</span>
            @elseif($item->type === 'ip')
              <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">🌐 IP</span>
            @else
              <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">✉️ Email</span>
            @endif

            <span class="text-[10px] font-medium text-slate-400">
              {{ $item->created_at->format('d M Y, g:i A') }}
            </span>
          </div>

          <div class="font-mono font-black text-slate-900 text-sm break-all">
            {{ $item->value }}
          </div>

          @if($item->reason)
            <p class="text-xs text-slate-600 bg-slate-50 p-2 rounded-lg border border-slate-100">
              💬 {{ $item->reason }}
            </p>
          @endif

          <div class="pt-1 flex justify-end">
            <form method="POST" action="{{ route('admin.blacklist.destroy', $item) }}" onsubmit="return confirm('Remove {{ $item->value }} from blacklist?')">
              @csrf @method('DELETE')
              <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer flex items-center gap-1">
                🗑️ Remove Entry
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-xs text-slate-400">
          No blacklisted entries found.
        </div>
      @endforelse
    </div>

    @if($items->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">
        {{ $items->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
