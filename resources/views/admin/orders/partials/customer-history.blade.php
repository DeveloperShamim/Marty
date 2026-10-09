{{-- Compact customer delivery history, shown inside the Customer card: one source (BD Courier across all couriers,
     or Steadfast when BD Courier isn't set up), plus this shop's own orders and the blacklist. --}}
@php
  $own = $customerHistory['own'];
  $fromBd = $customerHistory['source'] === 'bdcourier';
  $check = $customerHistory['check'];
  $sf = $customerHistory['steadfast'] ?? null;

  // Normalise both sources to: total, delivered, returned, rate, level, label, reports.
  $h = null;
  if ($fromBd && $check) {
      $h = ['total' => $check->total_parcels, 'delivered' => $check->delivered, 'returned' => $check->cancelled,
            'rate' => $check->total_parcels > 0 ? $check->success_ratio : null, 'level' => $check->riskLevel(), 'label' => $check->riskLabel(),
            'reports' => count($check->reports ?? [])];
  } elseif (! $fromBd && $sf && ! empty($sf['success'])) {
      $h = ['total' => (int) $sf['total'], 'delivered' => (int) $sf['delivered'], 'returned' => (int) $sf['cancelled'],
            'rate' => $sf['rate'], 'level' => $sf['risk_level'], 'label' => $sf['rating_label'], 'reports' => (int) ($sf['fraud'] ?? 0)];
  }
  $tone = match ($h['level'] ?? null) {
      'high'   => ['bg-rose-50 border-rose-200 text-rose-800', 'bg-rose-500'],
      'medium' => ['bg-amber-50 border-amber-200 text-amber-900', 'bg-amber-500'],
      'low'    => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'bg-emerald-500'],
      default  => ['bg-slate-50 border-slate-200 text-slate-700', 'bg-slate-400'],
  };
  $advice = match ($h['level'] ?? null) {
      'low'    => 'Safe to send.',
      'medium' => 'Some returns: call to confirm before sending.',
      'high'   => 'High risk: confirm by phone or ask for the delivery charge in advance.',
      'new'    => 'No courier history: check the address before sending.',
      default  => null,
  };
  $fmtRate = fn ($r) => rtrim(rtrim(number_format((float) $r, 1), '0'), '.');
  $active = $fromBd ? collect($check->couriers ?? [])->where('total', '>', 0) : collect();
@endphp
<div class="border-t border-slate-100 bg-slate-50/60 px-4 sm:px-5 py-3.5 space-y-2.5" id="delivery-history">
  <div class="flex items-start justify-between gap-2">
    <p class="min-w-0 text-xs font-semibold text-slate-700">
      <x-oi name="shield" class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5 text-slate-400" />Customer delivery history
      @if($fromBd || ! empty($sf['configured']))
        <span class="block sm:inline font-normal text-slate-400"><span class="hidden sm:inline">· </span>{{ $fromBd ? 'all couriers via BD Courier' : 'Steadfast' }}</span>
      @endif
    </p>
    @if($fromBd && $customerHistory['configured'])
      <form method="POST" action="{{ route('admin.orders.courier-history', $order) }}"
            @if($check) onsubmit="return confirm('This uses one BD Courier search. The saved result is from {{ $check->checked_at->diffForHumans() }}. Check again?')" @endif>
        @csrf
        <button type="submit" class="shrink-0 whitespace-nowrap px-2.5 py-1 rounded-lg ring-1 ring-slate-200 bg-white hover:bg-slate-50 text-[11px] font-medium text-slate-700 cursor-pointer">{{ $check ? 'Check again' : 'Check now' }}</button>
      </form>
    @elseif(! $fromBd && ! empty($sf['configured']))
      <a href="{{ request()->fullUrlWithQuery(['refresh_courier' => 1]) }}" class="shrink-0 px-2.5 py-1 rounded-lg ring-1 ring-slate-200 bg-white hover:bg-slate-50 text-[11px] font-medium text-slate-700">Refresh</a>
    @else
      <a href="{{ route('admin.integrations.index') }}" class="shrink-0 text-[11px] font-medium text-brand-700 hover:underline" title="Add a BD Courier API token (all couriers) or Steadfast API keys to see each customer's courier delivery history">Connect courier history</a>
    @endif
  </div>

  @if($h)
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
      <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $tone[0] }}">
        <span class="w-1.5 h-1.5 rounded-full {{ $tone[1] }}"></span>{{ $h['label'] }}
      </span>
      <span class="text-sm text-slate-700">
        <b class="font-semibold text-slate-900">{{ $h['total'] }}</b> parcels
        · <b class="font-semibold text-emerald-700">{{ $h['delivered'] }}</b> delivered
        · <b class="font-semibold text-rose-600">{{ $h['returned'] }}</b> returned
      </span>
      @if($h['rate'] !== null)
        <span class="inline-flex items-center gap-2">
          <span class="w-20 h-1.5 rounded-full bg-rose-100 overflow-hidden"><span class="block h-full bg-emerald-500" style="width: {{ min(100, max(0, $h['rate'])) }}%"></span></span>
          <b class="text-sm text-slate-900">{{ $fmtRate($h['rate']) }}%</b>
        </span>
      @endif
    </div>
    @if($advice)<p class="text-xs text-slate-600">{{ $advice }}</p>@endif

    @if($active->isNotEmpty() || $h['reports'] > 0)
      <details class="group">
        <summary class="text-xs font-medium text-brand-700 cursor-pointer list-none inline-flex items-center gap-1">
          <x-oi name="chevron-down" class="w-3.5 h-3.5 -rotate-90 group-open:rotate-0 transition-transform" />
          By courier{{ $h['reports'] > 0 ? ' & ' . $h['reports'] . ' fraud ' . \Illuminate\Support\Str::plural('report', $h['reports']) : '' }}
        </summary>
        <div class="mt-2 space-y-2">
          @if($active->isNotEmpty())
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
              <table class="w-full text-xs">
                <thead><tr class="text-[10px] uppercase tracking-wider text-slate-400 bg-slate-50"><th class="text-left font-semibold py-1.5 px-2">Courier</th><th class="text-right font-semibold py-1.5 px-2">Parcels</th><th class="text-right font-semibold py-1.5 px-2">Delivered</th><th class="text-right font-semibold py-1.5 px-2">Returned</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                  @foreach($active as $c)
                    <tr><td class="py-1.5 px-2 font-semibold text-slate-700">{{ $c['name'] }}</td><td class="py-1.5 px-2 text-right">{{ $c['total'] }}</td><td class="py-1.5 px-2 text-right text-emerald-700">{{ $c['delivered'] }}</td><td class="py-1.5 px-2 text-right text-rose-700">{{ $c['cancelled'] }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
          @if($fromBd && ! empty($check->reports))
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-2.5 space-y-1.5">
              @foreach(array_slice($check->reports, 0, 5) as $r)
                <p class="text-[11px] text-rose-900">
                  @if($r['courier'])<b>{{ $r['courier'] }}</b> · @endif{{ $r['name'] }}@if($r['date']) · {{ \Illuminate\Support\Carbon::parse($r['date'])->format('d M Y') }}@endif
                  @if($r['details'])<span class="block text-rose-800/80">{{ \Illuminate\Support\Str::limit($r['details'], 200) }}</span>@endif
                </p>
              @endforeach
            </div>
          @endif
        </div>
      </details>
    @endif
  @elseif($fromBd)
    <p class="text-xs text-slate-500">Not checked yet. New cash-on-delivery orders are checked automatically; use <b>Check now</b> for this one (uses one search).</p>
  @elseif(! empty($sf['configured']))
    <p class="text-xs text-slate-500">{{ $sf['message'] ?? 'Could not check Steadfast right now.' }} Try <b>Refresh</b>.</p>
  @endif

  <p class="text-xs text-slate-500">
    <b class="text-slate-700">In your shop:</b>
    @if($own['total'] === 0) first order
    @else {{ $own['total'] }} earlier {{ \Illuminate\Support\Str::plural('order', $own['total']) }} · {{ $own['delivered'] }} delivered · {{ $own['returned'] }} returned · {{ $own['cancelled'] }} cancelled
    @endif
    @if($customerHistory['blacklisted'])<b class="text-rose-700 ml-1">· On your blacklist</b>@endif
    @if($fromBd && $check)<span class="text-slate-400">· checked {{ $check->checked_at->diffForHumans() }}</span>@endif
  </p>
</div>
