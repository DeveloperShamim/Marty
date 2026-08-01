@extends('layouts.admin')
@section('title', 'Staff Activity Audit Logs')

@section('content')
<div class="space-y-6 max-w-full">
  <!-- Header Title & Controls -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-2xl font-extrabold text-ink tracking-tight flex items-center gap-2.5">
        <span>📜</span> Staff Activity Audit Logs
      </h2>
      <p class="text-xs text-gray-500 mt-1">Audit timeline of actions performed by your employees inside the Admin Panel.</p>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2 w-full md:w-auto">
      <div class="relative flex-1 md:w-72">
        <input type="text" name="q" value="{{ $search }}" placeholder="Search staff name, action, or IP..." class="inp text-xs pr-8 py-2 w-full" />
        @if($search !== '')
          <a href="{{ route('admin.activity-logs.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
        @endif
      </div>
      <button type="submit" class="btn-primary text-xs px-4 py-2 shrink-0">Search</button>
    </form>
  </div>

  <!-- Activity Logs Table Card -->
  <div class="card overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
      <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
        <span>🕒</span> Audit Log Timeline ({{ $logs->total() }} recorded actions)
      </h3>
      <span class="text-xs text-slate-400 font-medium">Automatic system logging</span>
    </div>

    <div class="overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[750px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3.5 px-4">Staff Member</th>
            <th class="py-3.5 px-4">Action</th>
            <th class="py-3.5 px-4">Details &amp; Description</th>
            <th class="py-3.5 px-4 text-center">IP Address</th>
            <th class="py-3.5 px-4 text-right">Timestamp</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($logs as $log)
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="py-4 px-4 whitespace-nowrap">
                <div class="flex items-center gap-2.5">
                  <div class="h-8 w-8 rounded-full bg-slate-800 text-white font-extrabold flex items-center justify-center text-[11px] shrink-0">
                    {{ strtoupper(substr($log->staff_name, 0, 2)) }}
                  </div>
                  <div>
                    <p class="font-extrabold text-slate-900 text-xs leading-tight">{{ $log->staff_name }}</p>
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                      {{ ucfirst(str_replace('_', ' ', $log->staff_role)) }}
                    </span>
                  </div>
                </div>
              </td>

              <td class="py-4 px-4 whitespace-nowrap">
                <span class="inline-block px-2.5 py-1 text-[11px] font-black rounded-lg bg-slate-100 text-slate-800 border border-slate-200">
                  {{ $log->action }}
                </span>
              </td>

              <td class="py-4 px-4 text-slate-600 max-w-md">
                <p class="text-xs font-medium leading-relaxed">{{ $log->description ?: 'No detailed description provided.' }}</p>
              </td>

              <td class="py-4 px-4 text-center whitespace-nowrap font-mono text-slate-600">
                <span class="px-2.5 py-0.5 rounded-md bg-slate-50 text-slate-700 border border-slate-200 text-[11px]">
                  {{ $log->ip_address ?: '127.0.0.1' }}
                </span>
              </td>

              <td class="py-4 px-4 text-right text-slate-500 text-[11px] font-medium whitespace-nowrap">
                <p class="font-bold text-slate-800">{{ $log->created_at ? $log->created_at->format('d M Y, g:i A') : 'N/A' }}</p>
                <p class="text-[10px] text-slate-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</p>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-12 text-slate-400 text-xs">
                No activity logs recorded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($logs->hasPages())
      <div class="p-4 border-t border-slate-100">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
