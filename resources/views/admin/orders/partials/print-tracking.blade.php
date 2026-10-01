{{--
  Print history for the invoice / label pages: warns when an order was already printed,
  and records the print when the print dialog opens. Expects $orders, $type ('invoice'|'label'), $format.
--}}
@php
    $already = $orders->map(function ($o) use ($type) {
        $prints = $o->prints->where('type', $type)->sortByDesc('id');

        return $prints->isEmpty() ? null : ['number' => $o->order_number, 'times' => $prints->count(), 'last' => $prints->first()->summary()];
    })->filter()->values();
@endphp
@if($already->isNotEmpty())
  <div class="print-warn" role="alert">
    <b>&#9888; {{ $already->count() === 1 && $orders->count() === 1 ? 'This order was already printed' : $already->count() . ' of these orders were already printed' }}.</b>
    Check it isn't packed twice.
    <ul>
      @foreach($already->take(5) as $a)
        <li><span class="mono">{{ $a['number'] }}</span> &middot; {{ $a['times'] > 1 ? 'printed ' . $a['times'] . '×, last by ' : 'by ' }}{{ $a['last'] }}</li>
      @endforeach
      @if($already->count() > 5)<li>+ {{ $already->count() - 5 }} more</li>@endif
    </ul>
  </div>
@endif
<p class="print-note" id="printNote" hidden></p>
<style>
  .print-warn { width: 100%; margin: 0; padding: 10px 12px; border-radius: 10px; background: #fef3c7; border: 1px solid #fcd34d; color: #78350f; font-size: 12.5px; line-height: 1.45; }
  .print-warn ul { margin: 4px 0 0; padding-left: 18px; }
  .print-note { width: 100%; margin: 0; padding: 8px 12px; border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 12px; }
</style>
<script>
  (function () {
    var payload = {
      orders: @json($orders->pluck('order_number')->values()),
      type: @json($type),
      format: @json($format),
    };
    var busy = false;
    // The browser can't tell Print from Cancel, so opening the print dialog counts as a print.
    window.addEventListener('beforeprint', function () {
      if (busy) return;
      busy = true;
      fetch(@json(route('admin.orders.prints.record')), {
        method: 'POST',
        keepalive: true,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
        body: JSON.stringify(payload),
      }).then(function (r) { return r.ok ? r.json() : null; }).then(function (d) {
        var moved = d && d.moved_to_processing ? d.moved_to_processing.length : 0;
        var note = document.getElementById('printNote');
        if (note && moved) {
          note.textContent = 'Recorded as printed. ' + moved + (moved === 1 ? ' order' : ' orders') + ' moved from Confirmed to Processing (being packed).';
          note.hidden = false;
        }
      }).catch(function () {});
    });
    window.addEventListener('afterprint', function () { setTimeout(function () { busy = false; }, 1000); });
  })();
</script>
