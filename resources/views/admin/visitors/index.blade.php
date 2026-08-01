@extends('layouts.admin')
@section('title', 'Visitor Traffic Analytics')

@section('content')
<div class="space-y-6 max-w-full">

  <!-- Header Title & Realtime Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div class="space-y-1">
      <div class="flex items-center gap-2.5">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
          <span>👥</span> Visitor Traffic Analytics
        </h2>
        <!-- Subtle Live Pulse Badge -->
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          Live Monitor
        </span>
      </div>
      <p class="text-xs text-slate-500">Track unique storefront visitors, daily traffic trends, and IP activity.</p>
    </div>

    <!-- Active Right Now Badge -->
    <div class="bg-white border border-slate-200 px-4 py-2.5 rounded-xl shadow-2xs flex items-center gap-3 shrink-0">
      <div class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold border border-emerald-100">
        ⚡
      </div>
      <div>
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Active Right Now</span>
        <div class="flex items-baseline gap-1.5">
          <strong class="text-lg font-black font-mono text-slate-900">{{ number_format($activeRealtimeCount) }}</strong>
          <span class="text-[11px] text-slate-500">online (last 15m)</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Minimal KPI Stat Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    
    <!-- Visitors Today -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Visitors Today</span>
        <span class="text-slate-400 text-sm">⚡</span>
      </div>
      <p class="mt-2 text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($todayCount) }}</p>
      <p class="text-[11px] text-slate-400 mt-1">Yesterday: <strong class="text-slate-700 font-mono">{{ number_format($yesterdayCount) }}</strong></p>
    </div>

    <!-- This Week -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">This Week</span>
        <span class="text-slate-400 text-sm">📅</span>
      </div>
      <p class="mt-2 text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($thisWeekCount) }}</p>
      <p class="text-[11px] text-slate-400 mt-1">Unique 7-day IPs</p>
    </div>

    <!-- This Month -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">This Month</span>
        <span class="text-slate-400 text-sm">📈</span>
      </div>
      <p class="mt-2 text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($thisMonthCount) }}</p>
      <p class="text-[11px] text-slate-400 mt-1">{{ date('M Y') }} total</p>
    </div>

    <!-- Mobile vs Desktop -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Device Split</span>
        <span class="text-slate-400 text-sm">📱</span>
      </div>
      <div class="mt-2 flex items-baseline justify-between font-mono">
        <p class="text-lg font-extrabold text-slate-900">{{ number_format($mobileCount) }} <span class="text-[10px] text-slate-400 font-normal">Mob</span></p>
        <p class="text-sm font-bold text-slate-500">{{ number_format($desktopCount) }} <span class="text-[10px] text-slate-400 font-normal">Desk</span></p>
      </div>
      @php
        $totalDevices = max(1, $mobileCount + $desktopCount);
        $mobilePct = round(($mobileCount / $totalDevices) * 100);
      @endphp
      <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2 overflow-hidden flex">
        <div class="bg-slate-800 h-full" style="width: {{ $mobilePct }}%"></div>
        <div class="bg-slate-300 h-full flex-1"></div>
      </div>
    </div>

    <!-- All-Time -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">All-Time</span>
        <span class="text-slate-400 text-sm">🌐</span>
      </div>
      <p class="mt-2 text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($totalCount) }}</p>
      <p class="text-[11px] text-slate-400 mt-1">Total tracked logs</p>
    </div>

  </div>

  <!-- Chart.js Modern Line Chart Card -->
  <div class="card p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
      <div>
        <div class="flex items-center gap-2">
          <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
            <span>📈</span> 14-Day Traffic Line Chart
          </h3>
          <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
            Interactive Line Chart
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">Daily unique visitor trajectory and trend line</p>
      </div>

      <div class="flex items-center gap-4 text-xs font-bold shrink-0">
        <div class="flex items-center gap-1.5 text-slate-600">
          <span class="w-3 h-3 rounded-full bg-brand-600"></span>
          <span>Unique Visitors</span>
        </div>
        <span class="text-slate-700 font-extrabold bg-slate-100 px-3 py-1 rounded-xl border border-slate-200">
          🏆 Peak: {{ number_format($maxTrendCount) }} visitors
        </span>
      </div>
    </div>

    <!-- Chart Canvas Container -->
    <div class="relative w-full h-64">
      <canvas id="visitorLineChart"></canvas>
    </div>
  </div>

  <!-- Include Chart.js via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('visitorLineChart').getContext('2d');
      
      const labels = @json($trendDates->pluck('date'));
      const dataValues = @json($trendDates->pluck('count'));

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Daily Visitors',
            data: dataValues,
            borderColor: '#0f766e',
            backgroundColor: 'rgba(15, 118, 110, 0.08)',
            borderWidth: 3,
            tension: 0.35, // Smooth curves
            pointBackgroundColor: '#0f766e',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#0f766e',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#0f172a',
              titleColor: '#ffffff',
              bodyColor: '#2dd4bf',
              bodyFont: { weight: 'bold', family: 'monospace' },
              padding: 12,
              cornerRadius: 12,
              displayColors: false,
              callbacks: {
                label: function(context) {
                  return context.parsed.y + ' unique visitors';
                }
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index',
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: {
                color: '#64748b',
                font: { size: 11, weight: 'bold', family: 'Plus Jakarta Sans' }
              }
            },
            y: {
              beginAtZero: true,
              grid: { color: '#f1f5f9' },
              ticks: {
                color: '#64748b',
                font: { size: 11, family: 'Plus Jakarta Sans' },
                precision: 0
              }
            }
          }
        }
      });
    });
  </script>

  <!-- Minimal Visitor Logs Table -->
  <div class="card overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
          <span>📋</span> Recent Visitor Activity Logs
        </h3>
        <p class="text-xs text-slate-500">Real-time IP logs captured by storefront middleware</p>
      </div>

      <!-- Search Form -->
      <form method="GET" action="{{ route('admin.visitors.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative flex-1 sm:w-64">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search IP or user agent..." class="inp text-xs pr-8 py-2 w-full" />
          @if($search !== '')
            <a href="{{ route('admin.visitors.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>
        <button type="submit" class="btn-primary text-xs px-4 py-2 shrink-0">Search</button>
      </form>
    </div>

    <div class="overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[650px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3.5 px-4 sm:px-5">IP Address</th>
            <th class="py-3.5 px-4 sm:px-5">Visit Date</th>
            <th class="py-3.5 px-4 sm:px-5">User Agent / Device</th>
            <th class="py-3.5 px-4 sm:px-5 text-right">Time Ago</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($logs as $log)
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="py-3.5 px-4 sm:px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200">
                  {{ $log->ip_address }}
                </span>
              </td>
              <td class="py-3.5 px-4 sm:px-5 font-bold text-slate-700 whitespace-nowrap">
                {{ \Illuminate\Support\Carbon::parse($log->visit_date)->format('d M Y') }}
              </td>
              <td class="py-3.5 px-4 sm:px-5 text-slate-600 max-w-sm truncate" title="{{ $log->user_agent }}">
                @if(str_contains(strtolower($log->user_agent), 'mobile') || str_contains(strtolower($log->user_agent), 'android') || str_contains(strtolower($log->user_agent), 'iphone'))
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-50 text-amber-800 border border-amber-200 mr-1.5">Mobile</span>
                @else
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700 border border-slate-200 mr-1.5">Desktop</span>
                @endif
                <span class="text-slate-500 font-mono text-[11px]">{{ $log->user_agent ?: 'Unknown User Agent' }}</span>
              </td>
              <td class="py-3.5 px-4 sm:px-5 text-right text-slate-400 text-[11px] font-medium whitespace-nowrap">
                {{ $log->created_at ? $log->created_at->diffForHumans() : 'N/A' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center py-10 text-slate-400 text-xs">
                No visitor activity matching your query.
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
