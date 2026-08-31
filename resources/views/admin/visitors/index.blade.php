@extends('layouts.admin')
@section('title', 'Visitor Traffic Analytics')

@section('content')
<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Live Realtime Bar --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>👥</span> Visitor Traffic Analytics
        </h1>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase tracking-wider border border-emerald-200 shrink-0">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          Live Monitor
        </span>
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Track unique storefront visitors, traffic sources, landing pages, and real-time live users.
      </p>
    </div>

    {{-- Actions & Real-Time Pulse Widget --}}
    <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto shrink-0">
      
      {{-- Auto-Refresh Toggle --}}
      <button type="button" id="autoRefreshBtn" onclick="toggleAutoRefresh()" class="px-3.5 py-2 rounded-xl bg-stone-50 hover:bg-stone-100 border border-stone-200 text-stone-700 font-extrabold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
        <span id="refreshIcon" class="text-xs">🔄</span>
        <span id="refreshLabel">Auto-Refresh: Off</span>
      </button>

      {{-- Prune Old Logs Action --}}
      <form method="POST" action="{{ route('admin.visitors.prune') }}" onsubmit="return confirm('Prune visitor logs older than 60 days to save database storage?');" class="inline">
        @csrf
        <input type="hidden" name="days" value="60" />
        <button type="submit" class="px-3.5 py-2 rounded-xl bg-stone-50 hover:bg-amber-50 text-stone-600 hover:text-amber-800 border border-stone-200 font-extrabold text-xs shadow-2xs transition flex items-center gap-1 cursor-pointer" title="Delete visitor logs older than 60 days">
          <span>🧹</span>
          <span>Prune (&gt;60d)</span>
        </button>
      </form>

      {{-- Active Right Now Badge --}}
      <div class="bg-gradient-to-r from-emerald-50 to-white border border-emerald-200/80 px-3.5 py-2 rounded-xl shadow-2xs flex items-center gap-2.5 shrink-0">
        <div class="h-7 w-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black shadow-xs shrink-0">
          ⚡
        </div>
        <div>
          <span class="text-[9px] font-black uppercase tracking-wider text-emerald-800 block leading-tight">Active Now</span>
          <div class="flex items-baseline gap-1">
            <strong class="text-sm sm:text-base font-black font-mono text-stone-900 leading-none">{{ number_format($activeRealtimeCount) }}</strong>
            <span class="text-[10px] text-stone-400 font-medium">online</span>
          </div>
        </div>
      </div>

    </div>
  </div>

  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  {{-- 5 KPI Metric Cards Ribbon --}}
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
    
    {{-- Visitors Today --}}
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider">Visitors Today</span>
        <span class="text-xs">⚡</span>
      </div>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($todayCount) }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Yesterday: <strong class="text-stone-700 font-mono">{{ number_format($yesterdayCount) }}</strong></span>
    </div>

    {{-- This Week --}}
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider">This Week</span>
        <span class="text-xs">📅</span>
      </div>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($thisWeekCount) }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">7-day unique visitors</span>
    </div>

    {{-- This Month --}}
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider">This Month</span>
        <span class="text-xs">📈</span>
      </div>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($thisMonthCount) }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">{{ date('M Y') }} total traffic</span>
    </div>

    {{-- Device Split --}}
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider">Device Split</span>
        <span class="text-xs">📱</span>
      </div>
      <div class="flex items-baseline justify-between font-mono">
        <p class="text-sm sm:text-base font-extrabold text-stone-900">{{ number_format($mobileCount) }} <span class="text-[10px] text-stone-400 font-normal">Mob</span></p>
        <p class="text-xs sm:text-sm font-bold text-stone-500">{{ number_format($desktopCount) }} <span class="text-[10px] text-stone-400 font-normal">Desk</span></p>
      </div>
      @php
        $totalDevices = max(1, $mobileCount + $desktopCount);
        $mobilePct = round(($mobileCount / $totalDevices) * 100);
      @endphp
      <div class="w-full bg-stone-100 h-1.5 rounded-full overflow-hidden flex border border-stone-200/60 mt-1">
        <div class="bg-brand-600 h-full" style="width: {{ $mobilePct }}%"></div>
        <div class="bg-stone-300 h-full flex-1"></div>
      </div>
    </div>

    {{-- All-Time Visitors --}}
    <div class="p-4 rounded-2xl sm:rounded-3xl bg-white border border-stone-200 shadow-2xs space-y-1 col-span-2 sm:col-span-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-black text-stone-400 uppercase tracking-wider">All-Time</span>
        <span class="text-xs">🌐</span>
      </div>
      <p class="text-xl sm:text-2xl font-black text-stone-900 font-mono tracking-tight">{{ number_format($totalCount) }}</p>
      <span class="text-[10px] text-stone-400 font-semibold block">Total recorded logs</span>
    </div>

  </div>

  {{-- Chart & Analytics Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    
    {{-- 14-Day Traffic Trend Chart (2 cols) --}}
    <div class="bg-white p-5 sm:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-4 lg:col-span-2 flex flex-col justify-between">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-stone-100 pb-3.5">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="font-extrabold text-sm sm:text-base text-stone-900 flex items-center gap-2">
              <span>📈</span> 14-Day Traffic Trend
            </h2>
            <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full bg-brand-50 text-brand-800 border border-brand-200">
              Daily Trend
            </span>
          </div>
          <p class="text-xs text-stone-500 mt-0.5">Daily unique storefront visitors</p>
        </div>

        <div class="flex items-center gap-3 text-xs font-bold shrink-0">
          <span class="text-stone-800 font-extrabold bg-stone-100 px-3 py-1 rounded-xl border border-stone-200 text-xs">
            🏆 Peak: {{ number_format($maxTrendCount) }}
          </span>
        </div>
      </div>

      {{-- Chart Canvas Container --}}
      <div class="relative w-full h-48 sm:h-56 md:h-64">
        <canvas id="visitorLineChart"></canvas>
      </div>
    </div>

    {{-- Top Pages & Traffic Sources (1 col) --}}
    <div class="space-y-4">
      
      {{-- Top Visited Landing Pages --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
          <h3 class="text-xs font-black text-stone-900 uppercase tracking-wider flex items-center gap-1.5">
            <span>🔥</span> Top Visited Pages (7d)
          </h3>
          <span class="text-[10px] text-stone-400 font-mono font-bold uppercase">Visits</span>
        </div>
        <div class="space-y-1.5 text-xs">
          @forelse($topPages as $tp)
            <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-stone-50 hover:bg-stone-100 transition border border-stone-200/60">
              <a href="{{ $tp->page_url }}" target="_blank" class="font-mono text-stone-800 hover:text-brand-600 truncate max-w-[200px]" title="{{ $tp->page_url }}">
                {{ $tp->page_url === '/' ? '/ (Home)' : $tp->page_url }}
              </a>
              <span class="px-2.5 py-0.5 rounded-full bg-stone-200 text-stone-800 font-mono font-black text-[11px] shrink-0">
                {{ number_format($tp->visits) }}
              </span>
            </div>
          @empty
            <p class="text-[11px] text-stone-400 text-center py-2 italic">No page data recorded yet.</p>
          @endforelse
        </div>
      </div>

      {{-- Top Traffic Sources --}}
      <div class="bg-white p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
        <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
          <h3 class="text-xs font-black text-stone-900 uppercase tracking-wider flex items-center gap-1.5">
            <span>🌐</span> Traffic Sources (7d)
          </h3>
          <span class="text-[10px] text-stone-400 font-mono font-bold uppercase">Visits</span>
        </div>
        <div class="space-y-1.5 text-xs">
          @forelse($topReferrers as $tr)
            <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-stone-50 hover:bg-stone-100 transition border border-stone-200/60">
              <span class="font-semibold text-stone-800 truncate max-w-[200px]" title="{{ $tr->referer }}">
                {{ $tr->referer }}
              </span>
              <span class="px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-900 font-mono font-black text-[11px] border border-brand-200 shrink-0">
                {{ number_format($tr->visits) }}
              </span>
            </div>
          @empty
            <p class="text-[11px] text-stone-400 text-center py-2 italic">Direct storefront traffic (No external referrers).</p>
          @endforelse
        </div>
      </div>

    </div>

  </div>

  {{-- Chart.js Script --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const ctx = document.getElementById('visitorLineChart');
      if (!ctx) return;
      
      const labels = @json($trendDates->pluck('date'));
      const dataValues = @json($trendDates->pluck('count'));

      new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Daily Visitors',
            data: dataValues,
            borderColor: '#E8751B',
            backgroundColor: 'rgba(232, 117, 27, 0.08)',
            borderWidth: 2.5,
            tension: 0.35,
            pointBackgroundColor: '#E8751B',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#E8751B',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 2.5,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#1c1917',
              titleColor: '#ffffff',
              bodyColor: '#fb923c',
              bodyFont: { weight: 'bold', family: 'monospace' },
              padding: 10,
              cornerRadius: 10,
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
                color: '#78716c',
                font: { size: 10, weight: 'bold' }
              }
            },
            y: {
              beginAtZero: true,
              grid: { color: '#f5f5f4' },
              ticks: {
                color: '#78716c',
                font: { size: 10 },
                precision: 0
              }
            }
          }
        }
      });
    });

    // Live Auto-Refresh Controller (30s)
    let autoRefreshTimer = null;
    let refreshCountdown = 30;
    function toggleAutoRefresh() {
      const btn = document.getElementById('autoRefreshBtn');
      const label = document.getElementById('refreshLabel');
      
      if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
        label.textContent = 'Auto-Refresh: Off';
        btn.classList.remove('bg-emerald-50', 'border-emerald-300', 'text-emerald-800');
      } else {
        refreshCountdown = 30;
        label.textContent = `Auto-Refresh: ${refreshCountdown}s`;
        btn.classList.add('bg-emerald-50', 'border-emerald-300', 'text-emerald-800');
        
        autoRefreshTimer = setInterval(() => {
          refreshCountdown--;
          if (refreshCountdown <= 0) {
            window.location.reload();
          } else {
            label.textContent = `Auto-Refresh: ${refreshCountdown}s`;
          }
        }, 1000);
      }
    }
  </script>

  {{-- Recent Visitor Activity Table Container --}}
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>📋</span> Recent Visitor Activity Logs
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">High-speed background tracking with zero storefront delay.</p>
      </div>

      {{-- Filters & Search Form --}}
      <form method="GET" action="{{ route('admin.visitors.index') }}" class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
        {{-- Device Filter --}}
        <select name="device" onchange="this.form.submit()" class="text-xs font-bold bg-white border border-stone-200 rounded-xl px-3 py-2 text-stone-800 focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer shadow-2xs">
          <option value="">All Devices</option>
          <option value="mobile" {{ request('device') === 'mobile' ? 'selected' : '' }}>📱 Mobile</option>
          <option value="desktop" {{ request('device') === 'desktop' ? 'selected' : '' }}>💻 Desktop</option>
          <option value="tablet" {{ request('device') === 'tablet' ? 'selected' : '' }}>📟 Tablet</option>
        </select>

        <div class="relative flex-1 sm:w-56">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search IP, URL, referrer..." class="w-full pl-8 pr-3 py-2 text-xs font-semibold bg-white border border-stone-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" />
          <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <button type="submit" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition cursor-pointer shadow-2xs">
          Filter
        </button>
      </form>
    </div>

    {{-- Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">IP Address</th>
            <th class="py-3 px-4">Visited Page</th>
            <th class="py-3 px-4">Referrer / Source</th>
            <th class="py-3 px-4">Device &amp; Browser</th>
            <th class="py-3 px-4">Visit Date</th>
            <th class="py-3 px-4 text-right">Time Ago</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($logs as $log)
            <tr class="hover:bg-stone-50/80 transition-colors">
              <td class="py-3.5 px-4 font-mono font-bold text-stone-900 whitespace-nowrap">
                <span class="px-2.5 py-0.5 rounded-md bg-stone-100 text-stone-800 border border-stone-200 text-[11px]">
                  {{ $log->ip_address }}
                </span>
                @if($log->hits > 1)
                  <span class="ml-1 px-1.5 py-0.2 text-[9.5px] font-bold rounded-full bg-brand-50 text-brand-700" title="{{ $log->hits }} page loads">{{ $log->hits }}h</span>
                @endif
              </td>

              <td class="py-3.5 px-4 font-mono text-stone-700 max-w-[200px] truncate" title="{{ $log->page_url }}">
                {{ $log->page_url ?: '/' }}
              </td>

              <td class="py-3.5 px-4 text-stone-600 max-w-[160px] truncate">
                @if($log->referer)
                  <span class="text-brand-700 font-semibold truncate block" title="{{ $log->referer }}">{{ $log->referer }}</span>
                @elseif($log->utm_source)
                  <span class="px-1.5 py-0.5 rounded bg-sky-50 text-sky-800 text-[10px] font-mono font-bold">utm: {{ $log->utm_source }}</span>
                @else
                  <span class="text-stone-400 text-[11px]">Direct</span>
                @endif
              </td>

              <td class="py-3.5 px-4 text-stone-600 whitespace-nowrap">
                @if($log->device_type === 'mobile' || str_contains(strtolower($log->user_agent ?? ''), 'mobile') || str_contains(strtolower($log->user_agent ?? ''), 'android') || str_contains(strtolower($log->user_agent ?? ''), 'iphone'))
                  <span class="px-2 py-0.5 text-[10px] font-black rounded-lg bg-amber-50 text-amber-800 border border-amber-200 mr-1">Mobile</span>
                @elseif($log->device_type === 'tablet')
                  <span class="px-2 py-0.5 text-[10px] font-black rounded-lg bg-purple-50 text-purple-800 border border-purple-200 mr-1">Tablet</span>
                @else
                  <span class="px-2 py-0.5 text-[10px] font-black rounded-lg bg-stone-100 text-stone-700 border border-stone-200 mr-1">Desktop</span>
                @endif
                <span class="text-stone-500 font-medium text-[11px]">{{ $log->browser ?: 'Browser' }}</span>
              </td>

              <td class="py-3.5 px-4 font-semibold text-stone-700 whitespace-nowrap text-[11px]">
                {{ \Illuminate\Support\Carbon::parse($log->visit_date)->format('d M, Y') }}
              </td>

              <td class="py-3.5 px-4 text-right text-stone-400 text-[11px] font-medium whitespace-nowrap">
                {{ $log->updated_at ? $log->updated_at->diffForHumans() : ($log->created_at ? $log->created_at->diffForHumans() : 'N/A') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-12 text-center text-stone-400 text-xs italic bg-stone-50/50">
                👥 No visitor activity matching your query.
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
          $isMobile = $log->device_type === 'mobile' || str_contains(strtolower($log->user_agent ?? ''), 'mobile') || str_contains(strtolower($log->user_agent ?? ''), 'android') || str_contains(strtolower($log->user_agent ?? ''), 'iphone');
        @endphp
        <div class="p-4 space-y-2.5">
          <div class="flex items-center justify-between gap-2">
            <span class="px-2.5 py-0.5 rounded-md bg-stone-100 text-stone-900 border border-stone-200 font-mono font-bold text-xs">
              {{ $log->ip_address }}
            </span>
            <span class="text-[11px] font-medium text-stone-400">
              {{ $log->updated_at ? $log->updated_at->diffForHumans() : '' }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-1.5">
              @if($isMobile)
                <span class="px-2 py-0.5 text-[10px] font-black rounded-lg bg-amber-50 text-amber-800 border border-amber-200">Mobile</span>
              @else
                <span class="px-2 py-0.5 text-[10px] font-black rounded-lg bg-stone-100 text-stone-700 border border-stone-200">Desktop</span>
              @endif
              <span class="text-stone-600 font-semibold">{{ \Illuminate\Support\Carbon::parse($log->visit_date)->format('d M, Y') }}</span>
            </div>
            @if($log->page_url)
              <span class="font-mono text-[11px] text-stone-500 truncate max-w-[140px]">{{ $log->page_url }}</span>
            @endif
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-xs text-stone-400 bg-stone-50">
          No visitor activity matching your query.
        </div>
      @endforelse
    </div>

    @if($logs->hasPages())
      <div class="p-4 border-t border-stone-100 bg-stone-50/40">{{ $logs->links() }}</div>
    @endif
  </div>

</div>
@endsection
