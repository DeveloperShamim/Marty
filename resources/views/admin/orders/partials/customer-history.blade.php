{{-- Customer delivery history: all couriers (BD Courier), this shop's own orders, and the blacklist. --}}
@php
  $check = $customerHistory['check'];
  $own = $customerHistory['own'];
  $risk = $check?->riskLevel();
  $tone = match ($risk) {
      'high'   => ['bg-rose-50 border-rose-200 text-rose-800', 'bg-rose-500'],
      'medium' => ['bg-amber-50 border-amber-200 text-amber-900', 'bg-amber-500'],
      'low'    => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'bg-emerald-500'],
      default  => ['bg-slate-50 border-slate-200 text-slate-700', 'bg-slate-400'],
  };
@endphp
<div class="card p-5 border border-slate-200/90 shadow-xs space-y-4">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h3 class="font-bold text-sm text-slate-900">Customer delivery history</h3>
      <p class="text-xs text-slate-500">{{ $order->customer_phone }} across Pathao, Steadfast, RedX, Paperfly and others</p>
    </div>
    <div class="flex items-center gap-2">
      @if($check)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $tone[0] }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $tone[1] }}"></span>{{ $check->riskLabel() }}
        </span>
      @endif
      @if($customerHistory['configured'])
        <form method="POST" action="{{ route('admin.orders.courier-history', $order) }}"
              @if($check) onsubmit="return confirm('This uses one BD Courier search. The saved result is from {{ $check->checked_at->diffForHumans() }}. Check again?')" @endif>
          @csrf
          <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 cursor-pointer">{{ $check ? 'Check again' : 'Check now' }}</button>
        </form>
      @endif
    </div>
  </div>

  @if($check)
    <div class="grid grid-cols-3 gap-2 text-center">
      <div class="rounded-xl bg-slate-50 border border-slate-100 py-2"><p class="text-lg font-bold text-slate-900">{{ $check->total_parcels }}</p><p class="text-[11px] text-slate-500">Parcels</p></div>
      <div class="rounded-xl bg-emerald-50 border border-emerald-100 py-2"><p class="text-lg font-bold text-emerald-700">{{ $check->delivered }}</p><p class="text-[11px] text-slate-500">Delivered</p></div>
      <div class="rounded-xl bg-rose-50 border border-rose-100 py-2"><p class="text-lg font-bold text-rose-700">{{ $check->cancelled }}</p><p class="text-[11px] text-slate-500">Returned / cancelled</p></div>
    </div>
    @if($check->total_parcels > 0)
      <div>
        <div class="flex justify-between text-[11px] text-slate-500 mb-1"><span>Success rate</span><b class="text-slate-800">{{ rtrim(rtrim(number_format($check->success_ratio, 1), '0'), '.') }}%</b></div>
        <div class="h-2 rounded-full bg-rose-100 overflow-hidden"><div class="h-full bg-emerald-500" style="width: {{ min(100, max(0, $check->success_ratio)) }}%"></div></div>
      </div>
    @endif
    @php $active = collect($check->couriers ?? [])->where('total', '>', 0); @endphp
    @if($active->isNotEmpty())
      <table class="w-full text-xs">
        <thead><tr class="text-[10px] uppercase tracking-wider text-slate-400"><th class="text-left font-semibold pb-1">Courier</th><th class="text-right font-semibold pb-1">Parcels</th><th class="text-right font-semibold pb-1">Delivered</th><th class="text-right font-semibold pb-1">Returned</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($active as $c)
            <tr><td class="py-1 font-semibold text-slate-700">{{ $c['name'] }}</td><td class="py-1 text-right">{{ $c['total'] }}</td><td class="py-1 text-right text-emerald-700">{{ $c['delivered'] }}</td><td class="py-1 text-right text-rose-700">{{ $c['cancelled'] }}</td></tr>
          @endforeach
        </tbody>
      </table>
    @endif
    @if(! empty($check->reports))
      <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 space-y-1.5">
        <p class="text-xs font-bold text-rose-800">Fraud reports by other merchants ({{ count($check->reports) }})</p>
        @foreach(array_slice($check->reports, 0, 5) as $r)
          <p class="text-[11px] text-rose-900">
            @if($r['courier'])<b>{{ $r['courier'] }}</b> · @endif{{ $r['name'] }}@if($r['date']) · {{ \Illuminate\Support\Carbon::parse($r['date'])->format('d M Y') }}@endif
            @if($r['details'])<span class="block text-rose-800/80">{{ \Illuminate\Support\Str::limit($r['details'], 200) }}</span>@endif
          </p>
        @endforeach
      </div>
    @endif
    <p class="text-[11px] text-slate-400">Checked {{ $check->checked_at->diffForHumans() }} via BD Courier · saved for {{ \App\Services\Courier\BdCourierService::FRESH_DAYS }} days</p>
  @elseif($customerHistory['configured'])
    <p class="text-xs text-slate-500">Not checked yet. New cash-on-delivery orders are checked automatically; use <b>Check now</b> for this one (uses one search).</p>
  @else
    <p class="text-xs text-slate-500">Add your BD Courier API token in <a href="{{ route('admin.integrations.index') }}" class="underline">Integrations</a> to see this customer's history across all couriers.</p>
  @endif

  <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-600">
    <span><b class="text-slate-800">In your shop:</b>
      @if($own['total'] === 0) first order
      @else {{ $own['total'] }} earlier {{ \Illuminate\Support\Str::plural('order', $own['total']) }} · {{ $own['delivered'] }} delivered · {{ $own['returned'] }} returned · {{ $own['cancelled'] }} cancelled
      @endif
    </span>
    @if($customerHistory['blacklisted'])
      <span class="font-bold text-rose-700">On your blacklist</span>
    @endif
  </div>
</div>
