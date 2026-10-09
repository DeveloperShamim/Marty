@extends('layouts.admin')
@section('title', 'Activity logs')
@section('subtitle', 'Every action, update, dispatch and login made by your staff.')

@if(auth()->user()->isAdmin())
  @section('page-actions')
    <button type="button" onclick="document.getElementById('clearLogsModal').classList.remove('hidden')" class="pill-btn cursor-pointer text-rose-700">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>
      Clear logs
    </button>
  @endsection
@endif

@php
  $actionTone = function ($action) {
    $a = strtolower($action);
    if (str_contains($a, 'create') || str_contains($a, 'add')) return 'bg-emerald-50 text-emerald-700';
    if (str_contains($a, 'delete') || str_contains($a, 'remove') || str_contains($a, 'clear')) return 'bg-rose-50 text-rose-700';
    if (str_contains($a, 'update') || str_contains($a, 'edit')) return 'bg-sky-50 text-sky-700';
    if (str_contains($a, 'dispatch') || str_contains($a, 'courier')) return 'bg-violet-50 text-violet-700';
    if (str_contains($a, 'suspend') || str_contains($a, 'reject')) return 'bg-amber-50 text-amber-700';
    return 'bg-gray-100 text-gray-700';
  };
@endphp

@section('content')
<div class="space-y-4 max-w-full">

  {{-- Stats --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="panel p-3.5 sm:p-4">
      <span class="text-xs text-gray-500">Total events</span>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($totalLogsCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">All time</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <span class="text-xs text-gray-500">Today</span>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($todayLogsCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Since midnight</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <span class="text-xs text-gray-500">Active staff</span>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">{{ number_format($uniqueStaffCount) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">With recorded actions</p>
    </div>
    <div class="panel p-3.5 sm:p-4 min-w-0">
      <span class="text-xs text-gray-500">Latest activity</span>
      <p class="mt-1 text-sm sm:text-[15px] font-semibold text-gray-900 truncate">{{ $latestLog?->created_at ? $latestLog->created_at->diffForHumans() : 'No logs yet' }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $latestLog?->staff_name ?: 'None' }}</p>
    </div>
  </div>

  {{-- Log list --}}
  <div class="card overflow-hidden">
    <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="px-1 sm:px-0">
        <h2 class="text-[15px] font-semibold text-gray-900">Timeline <span class="text-gray-400 font-normal tabular-nums">{{ $logs->total() }}</span></h2>
        <p class="text-xs text-gray-500 mt-0.5">Newest actions first.</p>
      </div>
      <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <label class="relative flex-1 sm:w-72">
          <span class="sr-only">Search logs</span>
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="q" value="{{ $search }}" placeholder="Search staff, action or IP" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
        </label>
        <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0 cursor-pointer" style="background: var(--brand-dark);">Search</button>
      </form>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="whitespace-nowrap border-y border-gray-100">
            <th class="py-3 px-4">Staff</th>
            <th class="py-3 px-4">Action</th>
            <th class="py-3 px-4">Details</th>
            <th class="py-3 px-4">IP address</th>
            <th class="py-3 px-4 text-right">Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($logs as $log)
            <tr>
              <td class="py-3 px-4 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <span class="h-8 w-8 rounded-full bg-gray-100 text-gray-700 font-semibold grid place-items-center text-[11px] shrink-0">{{ strtoupper(substr($log->staff_name ?: 'S', 0, 2)) }}</span>
                  <div>
                    <p class="font-semibold text-gray-900 leading-tight">{{ $log->staff_name ?: 'System' }}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ ucfirst(str_replace('_', ' ', $log->staff_role ?? 'staff')) }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $actionTone($log->action) }}">{{ $log->action }}</span>
              </td>
              <td class="py-3 px-4 text-gray-600 max-w-md">{{ $log->description ?: 'No detailed description provided.' }}</td>
              <td class="py-3 px-4 whitespace-nowrap font-mono text-xs text-gray-600">{{ $log->ip_address ?: '127.0.0.1' }}</td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <p class="text-gray-800">{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : 'N/A' }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</p>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500 text-sm">No activity recorded yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone cards --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($logs as $log)
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2">
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="h-8 w-8 rounded-full bg-white text-gray-700 font-semibold grid place-items-center text-[11px] shrink-0">{{ strtoupper(substr($log->staff_name ?: 'S', 0, 2)) }}</span>
              <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-[13px] truncate max-[399px]:whitespace-normal max-[399px]:leading-snug">{{ $log->staff_name ?: 'System' }}</p>
                <p class="text-[11px] text-gray-500">{{ ucfirst(str_replace('_', ' ', $log->staff_role ?? 'staff')) }}</p>
              </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $actionTone($log->action) }}">{{ $log->action }}</span>
          </div>
          <p class="text-[13px] text-gray-700 leading-relaxed">{{ $log->description ?: 'No detailed description provided.' }}</p>
          <div class="flex items-center justify-between text-[11px] text-gray-400">
            <span class="font-mono">IP {{ $log->ip_address ?: '127.0.0.1' }}</span>
            <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
          </div>
        </article>
      @empty
        <div class="py-10 text-center text-sm text-gray-500">No activity recorded yet.</div>
      @endforelse
    </div>

    @if($logs->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $logs->links() }}</div>
    @endif
  </div>

</div>

{{-- Clear logs confirmation --}}
@if(auth()->user()->isAdmin())
  <div id="clearLogsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4 hidden">
    <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl space-y-3">
      <div class="flex items-center gap-3">
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-rose-50 text-rose-700 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4M12 17h.01"/></svg>
        </span>
        <h3 class="text-[15px] font-semibold text-gray-900">Clear all activity logs?</h3>
      </div>
      <p class="text-[13px] text-gray-600 leading-relaxed">All staff activity records will be deleted permanently. This cannot be undone.</p>
      <div class="flex items-center justify-end gap-2 pt-1">
        <button type="button" onclick="document.getElementById('clearLogsModal').classList.add('hidden')" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium cursor-pointer">Cancel</button>
        <form method="POST" action="{{ route('admin.activity-logs.clear') }}">
          @csrf
          @method('DELETE')
          <button type="submit" class="h-9 px-4 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-[13px] font-semibold cursor-pointer">Clear all logs</button>
        </form>
      </div>
    </div>
  </div>
@endif
@endsection
