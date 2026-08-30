@extends('layouts.admin')
@section('title', 'Visitor Traffic Analytics')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-full">

  <!-- Header Title & Realtime Bar -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-gray-200 pb-4">
    <div class="space-y-1">
      <div class="flex items-center gap-2 flex-wrap">
        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
          <span>👥</span> Visitor Traffic Analytics
        </h2>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 shrink-0">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          Live Monitor
        </span>
      </div>
      <p class="text-xs text-slate-500">Track unique storefront visitors, landing page traffic, and real-time activity.</p>
    </div>

    <!-- Actions & Active Right Now Badge -->
    <div class="flex items-center gap-2.5 flex-wrap">
      <!-- Auto-Refresh Toggle -->
      <button type="button" id="autoRefreshBtn" onclick="toggleAutoRefresh()" class="btn-secondary text-xs px-3 py-2 flex items-center gap-1.5 shrink-0 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs">
        <span id="refreshIcon" class="text-xs">🔄</span>
        <span id="refreshLabel">Auto-Refresh: Off</span>
      </button>

      <!-- Prune Old Logs Dropdown/Button -->
      <form method="POST" action="{{ route('admin.visitors.prune') }}" onsubmit="return confirm('Prune logs older than 60 days to save database storage?');" class="inline">
        @csrf
        <input type="hidden" name="days" value="60" />
        <button type="submit" class="btn-secondary text-xs px-3 py-2 flex items-center gap-1 shrink-0 bg-white border border-slate-200 text-slate-600 hover:text-amber-700 hover:bg-amber-50 shadow-2xs" title="Delete visitor logs older than 60 days">
          <span>🧹</span>
          <span>Prune (>60d)</span>
        </button>
      </form>

      <!-- Active Right Now Badge -->
      <div class="bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-2xs flex items-center gap-3 shrink-0">
        <div class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold border border-emerald-100 shrink-0">
          ⚡
        </div>
        <div>
          <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400 block leading-tight">Active Now</span>
          <div class="flex items-baseline gap-1">
            <strong class="text-base font-black font-mono text-slate-900 leading-none">{{ number_format($activeRealtimeCount) }}</strong>
            <span class="text-[10px] text-slate-500 font-medium">online</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if(session('status'))
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <!-- Minimal KPI Stat Cards Grid (2 cols on phone, 5 cols on desktop) -->
  <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
    
    <!-- Visitors Today -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Visitors Today</span>
        <span class="text-slate-400 text-xs sm:text-sm">⚡</span>
      </div>
      <p class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($todayCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-slate-400 mt-1">Yesterday: <strong class="text-slate-700 font-mono">{{ number_format($yesterdayCount) }}</strong></p>
    </div>

    <!-- This Week -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">This Week</span>
        <span class="text-slate-400 text-xs sm:text-sm">📅</span>
      </div>
      <p class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($thisWeekCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-slate-400 mt-1">7-day unique IPs</p>
    </div>

    <!-- This Month -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">This Month</span>
        <span class="text-slate-400 text-xs sm:text-sm">📈</span>
      </div>
      <p class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($thisMonthCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-slate-400 mt-1">{{ date('M Y') }} total</p>
    </div>

    <!-- Mobile vs Desktop -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Device Split</span>
        <span class="text-slate-400 text-xs sm:text-sm">📱</span>
      </div>
      <div class="mt-1.5 sm:mt-2 flex items-baseline justify-between font-mono">
        <p class="text-base sm:text-lg font-extrabold text-slate-900">{{ number_format($mobileCount) }} <span class="text-[10px] text-slate-400 font-normal">Mob</span></p>
        <p class="text-xs sm:text-sm font-bold text-slate-500">{{ number_format($desktopCount) }} <span class="text-[10px] text-slate-400 font-normal">Desk</span></p>
      </div>
      @php
        $totalDevices = max(1, $mobileCount + $desktopCount);
        $mobilePct = round(($mobileCount / $totalDevices) * 100);
      @endphp
      <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2 overflow-hidden flex">
        <div class="bg-brand-600 h-full" style="width: {{ $mobilePct }}%"></div>
        <div class="bg-slate-300 h-full flex-1"></div>
      </div>
    </div>

    <!-- All-Time -->
    <div class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:border-slate-300 transition col-span-2 sm:col-span-1">
      <div class="flex items-center justify-between">
        <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">All-Time</span>
        <span class="text-slate-400 text-xs sm:text-sm">🌐</span>
      </div>
      <p class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">{{ number_format($totalCount) }}</p>
      <p class="text-[10px] sm:text-[11px] text-slate-400 mt-1">Total unique logs</p>
    </div>

  </div>

  <!-- Chart & Top Pages/Referrers Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    
    <!-- 14-Day Traffic Chart (Takes 2 cols on desktop) -->
    <div class="card p-4 sm:p-6 space-y-4 lg:col-span-2 flex flex-col justify-between">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-100 pb-3.5">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
              <span>📈</span> 14-Day Traffic Trend
            </h3>
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-brand-50 text-brand-700 border border-brand-200">
              Daily Trend
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">Daily unique visitor count</p>
        </div>

        <div class="flex items-center gap-3 text-xs font-bold shrink-0">
          <span class="text-slate-700 font-extrabold bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200 text-xs">
            🏆 Peak: {{ number_format($maxTrendCount) }}
          </span>
        </div>
      </div>

      <!-- Chart Canvas Container -->
      <div class="relative w-full h-48 sm:h-56 md:h-64">
        <canvas id="visitorLineChart"></canvas>
      </div>
    </div>

    <!-- Top Pages & Traffic Sources (1 col on desktop) -->
    <div class="space-y-4">
      
      <!-- Top Visited Landing Pages -->
      <div class="card p-4 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <span>🔥</span> Top Visited Pages (7d)
          </h4>
          <span class="text-[10px] text-slate-400 font-mono font-bold">Visits</span>
        </div>
        <div class="space-y-1.5 text-xs">
          @forelse($topPages as $tp)
            <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-slate-50 transition">
              <a href="{{ $tp->page_url }}" target="_blank" class="font-mono text-slate-700 hover:text-brand-600 truncate max-w-[200px]" title="{{ $tp->page_url }}">
                {{ $tp->page_url === '/' ? '/ (Home)' : $tp->page_url }}
              </a>
              <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-800 font-mono font-extrabold text-[11px] shrink-0">
                {{ number_format($tp->visits) }}
              </span>
            </div>
          @empty
            <p class="text-[11px] text-slate-400 text-center py-2">No page data recorded yet.</p>
          @endforelse
        </div>
      </div>

      <!-- Top Traffic Referrers -->
      <div class="card p-4 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
          <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <span>🌐</span> Traffic Sources (7d)
          </h4>
          <span class="text-[10px] text-slate-400 font-mono font-bold">Visits</span>
        </div>
        <div class="space-y-1.5 text-xs">
          @forelse($topReferrers as $tr)
            <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg hover:bg-slate-50 transition">
              <span class="font-semibold text-slate-700 truncate max-w-[200px]" title="{{ $tr->referer }}">
                {{ $tr->referer }}
              </span>
              <span class="px-2 py-0.5 rounded-full bg-brand-50 text-brand-800 font-mono font-extrabold text-[11px] shrink-0">
                {{ number_format($tr->visits) }}
              </span>
            </div>
          @empty
            <p class="text-[11px] text-slate-400 text-center py-2">Direct storefront traffic (No external referrers).</p>
          @endforelse
        </div>
      </div>

    </div>

  </div>

  <!-- Include Chart.js via CDN -->
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
              backgroundColor: '#0f172a',
              titleColor: '#ffffff',
              bodyColor: '#fb923c',
              bodyFont: { weight: 'bold', family: 'monospace' },
              padding: 9,
              cornerRadius: 8,
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
                font: { size: 10, weight: 'bold', family: 'Plus Jakarta Sans' }
              }
            },
            y: {
              beginAtZero: true,
              grid: { color: '#f1f5f9' },
              ticks: {
                color: '#64748b',
                font: { size: 10, family: 'Plus Jakarta Sans' },
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

  <!-- Visitor Logs Activity Card -->
  <div class="card overflow-hidden">
    <div class="p-3.5 sm:p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 flex items-center gap-2">
          <span>📋</span> Recent Visitor Activity Logs
        </h3>
        <p class="text-[11px] sm:text-xs text-slate-500">Fast background tracking with zero storefront delay</p>
      </div>

      <!-- Filters & Search Form -->
      <form method="GET" action="{{ route('admin.visitors.index') }}" class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
        <!-- Device Filter -->
        <select name="device" onchange="this.form.submit()" class="inp text-xs py-2 pr-6">
          <option value="">All Devices</option>
          <option value="mobile" {{ request('device') === 'mobile' ? 'selected' : '' }}>📱 Mobile</option>
          <option value="desktop" {{ request('device') === 'desktop' ? 'selected' : '' }}>💻 Desktop</option>
          <option value="tablet" {{ request('device') === 'tablet' ? 'selected' : '' }}>📟 Tablet</option>
        </select>

        <div class="relative flex-1 sm:w-56">
          <input type="text" name="q" value="{{ $search }}" placeholder="Search IP, URL, referrer..." class="inp text-xs pr-7 py-2 w-full" />
          @if($search !== '')
            <a href="{{ route('admin.visitors.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</a>
          @endif
        </div>
        <button type="submit" class="btn-primary text-xs px-3.5 py-2 shrink-0">Filter</button>
      </form>
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto w-full">
      <table class="w-full text-left text-xs border-collapse min-w-[750px]">
        <thead>
          <tr class="bg-slate-900 text-white uppercase text-[10.5px] font-extrabold tracking-wider whitespace-nowrap">
            <th class="py-3 px-4">IP Address</th>
            <th class="py-3 px-4">Visited Page</th>
            <th class="py-3 px-4">Referrer / Source</th>
            <th class="py-3 px-4">Device & Browser</th>
            <th class="py-3 px-4">Visit Date</th>
            <th class="py-3 px-4 text-right">Time Ago</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          @forelse($logs as $log)
            <tr class="hover:bg-slate-50/80 transition-colors">
              <!-- IP Address -->
              <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200 text-[11px]">
                  {{ $log->ip_address }}
                </span>
                @if($log->hits > 1)
                  <span class="ml-1 px-1.5 py-0.2 text-[9.5px] font-bold rounded-full bg-brand-50 text-brand-700" title="{{ $log->hits }} page loads">{{ $log->hits }}h</span>
                @endif
              </td>

              <!-- Visited Page -->
              <td class="py-3 px-4 font-mono text-slate-700 max-w-[180px] truncate" title="{{ $log->page_url }}">
                {{ $log->page_url ?: '/' }}
              </td>

              <!-- Referrer / Source -->
              <td class="py-3 px-4 text-slate-600 max-w-[150px] truncate">
                @if($log->referer)
                  <span class="text-brand-700 font-semibold truncate block" title="{{ $log->referer }}">{{ $log->referer }}</span>
                @elseif($log->utm_source)
                  <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-mono font-bold">utm: {{ $log->utm_source }}</span>
                @else
                  <span class="text-slate-400 text-[11px]">Direct</span>
                @endif
              </td>

              <!-- Device & Browser -->
              <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                @if($log->device_type === 'mobile' || str_contains(strtolower($log->user_agent ?? ''), 'mobile') || str_contains(strtolower($log->user_agent ?? ''), 'android') || str_contains(strtolower($log->user_agent ?? ''), 'iphone'))
                  <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-50 text-amber-800 border border-amber-200 mr-1">Mobile</span>
                @elseif($log->device_type === 'tablet')
                  <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-purple-50 text-purple-800 border border-purple-200 mr-1">Tablet</span>
                @else
                  <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700 border border-slate-200 mr-1">Desktop</span>
                @endif
                <span class="text-slate-500 font-medium text-[11px]">{{ $log->browser ?: 'Browser' }}</span>
              </td>

              <!-- Visit Date -->
              <td class="py-3 px-4 font-semibold text-slate-700 whitespace-nowrap text-[11px]">
                {{ \Illuminate\Support\Carbon::parse($log->visit_date)->format('d M Y') }}
              </td>

              <!-- Time Ago -->
              <td class="py-3 px-4 text-right text-slate-400 text-[11px] font-medium whitespace-nowrap">
                {{ $log->updated_at ? $log->updated_at->diffForHumans() : ($log->created_at ? $log->created_at->diffForHumans() : 'N/A') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                No visitor activity matching your query.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile Card List View --}}
    <div class="block sm:hidden divide-y divide-slate-100 bg-white">
      @forelse($logs as $log)
        @php
          $isMobile = $log->device_type === 'mobile' || str_contains(strtolower($log->user_agent ?? ''), 'mobile') || str_contains(strtolower($log->user_agent ?? ''), 'android') || str_contains(strtolower($log->user_agent ?? ''), 'iphone');
        @endphp
        <div class="p-3.5 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-900 border border-slate-200 font-mono font-bold text-xs">
              {{ $log->ip_address }}
            </span>
            <span class="text-[11px] font-medium text-slate-400">
              {{ $log->updated_at ? $log->updated_at->diffForHumans() : '' }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-1.5">
              @if($isMobile)
                <span class="px-1.5 py-0.5 text-[10px] font-extrabold rounded bg-amber-50 text-amber-800 border border-amber-200">Mobile</span>
              @else
                <span class="px-1.5 py-0.5 text-[10px] font-extrabold rounded bg-slate-100 text-slate-700 border border-slate-200">Desktop</span>
              @endif
              <span class="text-slate-600 font-semibold">{{ \Illuminate\Support\Carbon::parse($log->visit_date)->format('d M Y') }}</span>
            </div>
            @if($log->page_url)
              <span class="font-mono text-[11px] text-slate-500 truncate max-w-[140px]">{{ $log->page_url }}</span>
            @endif
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-xs text-slate-400">
          No visitor activity matching your query.
        </div>
      @endforelse
    </div>

    @if($logs->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-slate-100">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
