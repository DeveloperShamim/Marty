@extends('layouts.admin')
@section('title', 'Staff Activity Audit Logs')

@section('content')
<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Action Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>📜</span> Staff Activity Audit Logs
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-700 border border-stone-200">
          {{ number_format($totalLogsCount) }} Total Logged Events
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Real-time immutable audit trail of actions, updates, dispatches, and logins across your staff accounts.
      </p>
    </div>

    @if(auth()->user()->isAdmin())
      <button type="button" onclick="document.getElementById('clearLogsModal').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs shadow-2xs transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto shrink-0 cursor-pointer">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span>Clear Audit Logs</span>
      </button>
    @endif
  </div>

  {{-- Status Alerts --}}
  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  {{-- Audit Overview KPI Ribbon --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Total Logged Events</span>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($totalLogsCount) }}</p>
      <span class="text-[10px] text-stone-500 font-semibold block">All-time audit records</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-emerald-800 uppercase tracking-wider block">Actions Today</span>
      <p class="text-xl sm:text-2xl font-black text-emerald-700 font-mono tracking-tight">{{ number_format($todayLogsCount) }}</p>
      <span class="text-[10px] text-emerald-600 font-semibold block">Logged since midnight</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-indigo-900 uppercase tracking-wider block">Active Staff Members</span>
      <p class="text-xl sm:text-2xl font-black text-indigo-800 font-mono tracking-tight">{{ number_format($uniqueStaffCount) }}</p>
      <span class="text-[10px] text-indigo-600 font-semibold block">Employees with recorded logs</span>
    </div>

    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider block">Latest Activity</span>
      <p class="text-xs sm:text-sm font-extrabold text-stone-800 mt-1 truncate">
        {{ $latestLog?->created_at ? $latestLog->created_at->diffForHumans() : 'No logs recorded' }}
      </p>
      <span class="text-[10px] text-stone-400 font-mono block truncate">{{ $latestLog?->staff_name ?: 'None' }}</span>
    </div>
  </div>

  {{-- Audit Timeline Table Container --}}
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>🕒</span> Audit Log Timeline ({{ $logs->total() }})
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Chronological stream of administrative actions and changes.</p>
      </div>

      {{-- Search Form --}}
      <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative flex-1 sm:w-64">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search staff, action, or IP..." class="w-full pl-8 pr-3 py-2 text-xs font-semibold bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
          <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition cursor-pointer shadow-2xs">
          Search
        </button>
      </form>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">Staff Member</th>
            <th class="py-3 px-4">Action</th>
            <th class="py-3 px-4">Audit Details &amp; Description</th>
            <th class="py-3 px-4 text-center">IP Address</th>
            <th class="py-3 px-4 text-right">Timestamp</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($logs as $log)
            @php
              $actionLower = strtolower($log->action);
              $actionColor = 'bg-stone-100 text-stone-800 border-stone-200';
              if (str_contains($actionLower, 'create') || str_contains($actionLower, 'add')) {
                $actionColor = 'bg-emerald-50 text-emerald-900 border-emerald-200';
              } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove') || str_contains($actionLower, 'clear')) {
                $actionColor = 'bg-rose-50 text-rose-900 border-rose-200';
              } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
                $actionColor = 'bg-sky-50 text-sky-900 border-sky-200';
              } elseif (str_contains($actionLower, 'dispatch') || str_contains($actionLower, 'courier')) {
                $actionColor = 'bg-indigo-50 text-indigo-900 border-indigo-200';
              } elseif (str_contains($actionLower, 'suspend') || str_contains($actionLower, 'reject')) {
                $actionColor = 'bg-amber-50 text-amber-900 border-amber-200';
              }
            @endphp
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <div class="h-8 w-8 rounded-xl bg-stone-900 text-white font-black flex items-center justify-center text-[10px] shrink-0 border border-stone-800 shadow-2xs">
                    {{ strtoupper(substr($log->staff_name ?: 'S', 0, 2)) }}
                  </div>
                  <div>
                    <p class="font-extrabold text-stone-900 text-xs leading-tight">{{ $log->staff_name ?: 'System' }}</p>
                    <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block mt-0.5">
                      {{ ucfirst(str_replace('_', ' ', $log->staff_role ?? 'staff')) }}
                    </span>
                  </div>
                </div>
              </td>

              <td class="py-3.5 px-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-1 text-[11px] font-black rounded-lg border shadow-2xs {{ $actionColor }}">
                  {{ $log->action }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-stone-600 max-w-md">
                <p class="text-xs font-medium leading-relaxed">{{ $log->description ?: 'No detailed description provided.' }}</p>
              </td>

              <td class="py-3.5 px-4 text-center whitespace-nowrap font-mono text-stone-600">
                <span class="px-2 py-0.5 rounded-md bg-stone-50 text-stone-700 border border-stone-200 text-[11px]">
                  {{ $log->ip_address ?: '127.0.0.1' }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-right text-stone-500 font-medium whitespace-nowrap text-xs">
                <p class="font-bold text-stone-800">{{ $log->created_at ? $log->created_at->format('d M, Y h:i A') : 'N/A' }}</p>
                <p class="text-[10px] text-stone-400 mt-0.5">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</p>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-stone-400 text-xs italic bg-stone-50/50">
                📜 No activity audit logs recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Cards View (`block md:hidden`) --}}
    <div class="block md:hidden divide-y divide-stone-100 bg-white">
      @forelse($logs as $log)
        @php
          $actionLower = strtolower($log->action);
          $actionColor = 'bg-stone-100 text-stone-800 border-stone-200';
          if (str_contains($actionLower, 'create') || str_contains($actionLower, 'add')) {
            $actionColor = 'bg-emerald-50 text-emerald-900 border-emerald-200';
          } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove') || str_contains($actionLower, 'clear')) {
            $actionColor = 'bg-rose-50 text-rose-900 border-rose-200';
          } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
            $actionColor = 'bg-sky-50 text-sky-900 border-sky-200';
          } elseif (str_contains($actionLower, 'dispatch') || str_contains($actionLower, 'courier')) {
            $actionColor = 'bg-indigo-50 text-indigo-900 border-indigo-200';
          } elseif (str_contains($actionLower, 'suspend') || str_contains($actionLower, 'reject')) {
            $actionColor = 'bg-amber-50 text-amber-900 border-amber-200';
          }
        @endphp
        <div class="p-4 space-y-2.5">
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="h-8 w-8 rounded-xl bg-stone-900 text-white font-black flex items-center justify-center text-[10px] shrink-0 border border-stone-800 shadow-2xs">
                {{ strtoupper(substr($log->staff_name ?: 'S', 0, 2)) }}
              </div>
              <div class="min-w-0">
                <p class="font-extrabold text-stone-900 text-xs truncate">{{ $log->staff_name ?: 'System' }}</p>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                  {{ ucfirst(str_replace('_', ' ', $log->staff_role ?? 'staff')) }}
                </span>
              </div>
            </div>

            <span class="inline-block px-2 py-0.5 text-[10px] font-black rounded-lg border shadow-2xs {{ $actionColor }} shrink-0">
              {{ $log->action }}
            </span>
          </div>

          <div class="p-2.5 bg-stone-50 rounded-xl border border-stone-200/80 text-xs text-stone-700 leading-relaxed font-medium">
            {{ $log->description ?: 'No detailed description provided.' }}
          </div>

          <div class="flex items-center justify-between text-[11px] text-stone-400 font-medium pt-1">
            <span class="font-mono">IP: {{ $log->ip_address ?: '127.0.0.1' }}</span>
            <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400 bg-stone-50">
          No activity logs recorded.
        </div>
      @endforelse
    </div>

    @if($logs->hasPages())
      <div class="p-4 border-t border-stone-100 bg-stone-50/40">{{ $logs->links() }}</div>
    @endif
  </div>

</div>

{{-- Clear Logs Confirmation Modal --}}
@if(auth()->user()->isAdmin())
  <div id="clearLogsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-stone-200 animate-in fade-in zoom-in duration-150">
      <div class="flex items-center gap-3 text-rose-600">
        <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center font-bold text-lg shrink-0">
          ⚠️
        </div>
        <div>
          <h3 class="font-black text-base text-stone-900">Clear All Activity Logs?</h3>
          <p class="text-xs text-stone-400 font-semibold">Super Admin Security Action</p>
        </div>
      </div>

      <p class="text-xs text-stone-600 leading-relaxed">
        Are you sure you want to permanently clear all staff activity audit log records? This action cannot be undone.
      </p>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-stone-100">
        <button type="button" onclick="document.getElementById('clearLogsModal').classList.add('hidden')" class="px-4 py-2.5 text-xs font-bold text-stone-600 hover:text-stone-900 rounded-xl border border-stone-200 bg-stone-50 cursor-pointer">
          Cancel
        </button>
        <form method="POST" action="{{ route('admin.activity-logs.clear') }}">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition cursor-pointer">
            Yes, Clear All Logs
          </button>
        </form>
      </div>
    </div>
  </div>
@endif
@endsection
