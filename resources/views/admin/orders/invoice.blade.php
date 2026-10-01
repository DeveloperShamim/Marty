@php
    $prefix = (string) setting('order_number_prefix');
    $invFor = fn ($order) => [
        'store'   => setting('invoice_company_name', site_name()),
        'number'  => $prefix !== '' && ! str_starts_with($order->order_number, $prefix) ? $prefix . $order->order_number : $order->order_number,
        'phone'   => setting('invoice_phone') ?: setting('contact_phone'),
        'email'   => setting('contact_email'),
        'address' => setting('invoice_address') ?: setting('contact_address'),
        'vat'     => setting('invoice_vat_number'),
        'due'     => $order->amountToCollect(),
        'paymentStatus' => match (true) {
            $order->payment_status === 'verified' => 'Paid',
            $order->payment_status === 'rejected' => 'Payment rejected',
            $order->payment_method === 'cod'      => 'Pay on delivery',
            default                               => 'Awaiting verification',
        },
    ];
    $single = $orders->count() === 1;
    $order = $orders->first();
    $inv = $invFor($order);
    $formatUrl = fn ($f) => $single
        ? route('admin.orders.invoice', ['order' => $order, 'format' => $f])
        : route('admin.orders.invoices', ['orders' => $orders->pluck('order_number')->all(), 'format' => $f]);
    $sheets = match ($format) {
        'half'  => $single ? 1 : (int) ceil($orders->count() / 2),
        'a4'    => $orders->count(),
        default => null,
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>{{ $single ? 'Invoice #' . $inv['number'] : $orders->count() . ' invoices' }} - {{ $inv['store'] }}</title>
  <link rel="icon" href="{{ favicon_url() }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; }
    body { background: #e5e7eb; color: #111827; font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .mono { font-family: ui-monospace, Menlo, Consolas, monospace; font-variant-numeric: tabular-nums; }

    /* ---------- Toolbar (screen only) ---------- */
    .bar { position: sticky; top: 0; z-index: 10; display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; padding: 12px 16px; background: #fff; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
    .bar a { color: inherit; }
    .bar .back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 10px; border: 1px solid #e5e7eb; text-decoration: none; font-weight: 600; color: #374151; }
    .bar .back:hover { background: #f9fafb; }
    .bar .grow { flex: 1; min-width: 160px; }
    .bar .grow b { display: block; font-size: 14px; }
    .bar .grow span { color: #6b7280; font-size: 12px; }
    .seg { display: inline-flex; flex-wrap: wrap; border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; }
    .seg a { padding: 8px 12px; font-weight: 600; color: #374151; text-decoration: none; border-right: 1px solid #e5e7eb; }
    .seg a:last-child { border-right: 0; }
    .seg a:hover { background: #f3f4f6; }
    .seg a.on { background: #0f766e; color: #fff; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 10px; border: 0; background: #0f766e; color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn:hover { background: #0b4f4a; }
    .hint { width: 100%; margin: 0; padding: 8px 12px; border-radius: 10px; background: #fffbeb; color: #92400e; font-size: 12px; }
    .stage { padding: 24px 16px 48px; display: flex; flex-direction: column; align-items: center; gap: 24px; overflow-x: auto; }

    /* ---------- Paper invoice (A4 and half page) ---------- */
    .paper { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06); }
    .inv { display: flex; flex-direction: column; gap: 6mm; padding: 14mm 15mm; font-size: 9.5pt; line-height: 1.45; color: #111827; }
    .inv-head { display: flex; justify-content: space-between; gap: 8mm; padding-bottom: 5mm; border-bottom: 2px solid #111827; }
    .inv-logo { max-height: 14mm; max-width: 55mm; object-fit: contain; display: block; margin-bottom: 2mm; }
    .inv-store { font-size: 17pt; font-weight: 800; letter-spacing: -.01em; margin-bottom: 1.5mm; }
    .inv-muted { color: #6b7280; font-size: 8.5pt; }
    .inv-meta { text-align: right; flex-shrink: 0; }
    .inv-title { font-size: 20pt; font-weight: 800; letter-spacing: .02em; text-transform: uppercase; line-height: 1; color: #0f766e; }
    .inv-no { font-weight: 700; font-size: 11pt; margin-top: 1.5mm; }
    .inv-copy { display: inline-block; margin-bottom: 1.5mm; padding: .6mm 2.2mm; border: 1px solid #111827; border-radius: 99px; font-size: 7pt; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .inv-barcode { display: block; margin: 2mm 0 0 auto; height: 11mm; width: 48mm; }
    .inv-parties { display: grid; grid-template-columns: 1.2fr 1fr; gap: 8mm; }
    .inv-label { font-size: 7.5pt; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #6b7280; margin-bottom: 1mm; }
    .inv-strong { font-weight: 700; }
    .inv-parties .inv-strong { font-size: 11pt; }
    .inv-addr { margin-top: .5mm; }
    .inv-kv { display: flex; justify-content: space-between; gap: 4mm; }
    .inv-kv span { color: #6b7280; }
    .inv-kv b { font-weight: 600; text-align: right; }
    .inv-items { width: 100%; border-collapse: collapse; }
    .inv-items th { text-align: left; font-size: 7.5pt; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; padding: 0 2mm 2mm; border-bottom: 1px solid #d1d5db; }
    .inv-items td { padding: 2.2mm 2mm; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
    .inv-items tr { break-inside: avoid; page-break-inside: avoid; }
    .inv-items th:first-child, .inv-items td:first-child { padding-left: 0; }
    .inv-items th:last-child, .inv-items td:last-child { padding-right: 0; }
    .c-n { width: 7mm; color: #9ca3af; }
    .c-c { text-align: center; width: 12mm; }
    .c-r { text-align: right; white-space: nowrap; }
    .inv-item { display: flex; align-items: center; gap: 3mm; }
    .inv-thumb { width: 10mm; height: 10mm; object-fit: cover; border-radius: 1.5mm; border: 1px solid #e5e7eb; flex-shrink: 0; }
    .inv-bottom { display: grid; grid-template-columns: 1fr 72mm; gap: 10mm; break-inside: avoid; page-break-inside: avoid; }
    .inv-notes p { margin: 0 0 3mm; }
    .inv-terms { white-space: pre-line; color: #4b5563; font-size: 8.5pt; }
    .inv-totals { display: flex; flex-direction: column; gap: 1.2mm; }
    .inv-total { margin-top: 1mm; padding-top: 2mm; border-top: 2px solid #111827; font-size: 12pt; }
    .inv-total span, .inv-total b { color: #111827; font-weight: 800; }
    .inv-due { margin-top: 1.5mm; padding: 2mm 3mm; border-radius: 1.5mm; background: #111827; color: #fff; display: flex; justify-content: space-between; align-items: center; gap: 3mm; font-size: 8.5pt; }
    .inv-due b { font-size: 12pt; }
    .inv-paid { margin: 2mm 0 0 auto; padding: 1mm 4mm; border: 2px solid #0f766e; border-radius: 1.5mm; color: #0f766e; font-weight: 800; font-size: 11pt; letter-spacing: .15em; text-transform: uppercase; transform: rotate(-4deg); }
    .inv-end { flex: 1; display: flex; flex-direction: column; gap: 6mm; break-inside: avoid; page-break-inside: avoid; }
    .inv--half .inv-end { gap: 3mm; }
    .inv-foot { margin-top: auto; padding-top: 4mm; border-top: 1px dashed #d1d5db; display: flex; justify-content: space-between; align-items: flex-end; color: #6b7280; font-size: 8pt; }
    .inv-sign { width: 45mm; height: 9mm; border-bottom: 1px solid #9ca3af; margin-bottom: 1mm; }

    .fmt-a4 .paper { width: 210mm; min-height: 297mm; }
    .fmt-a4 .inv { min-height: 297mm; }

    /* Half page: two copies (customer + office) on one A4, cut along the dashed line */
    .fmt-half .paper { width: 210mm; height: 297mm; display: flex; flex-direction: column; }
    .inv--half { height: 148.5mm; overflow: hidden; padding: 7mm 10mm 6mm; gap: 3mm; font-size: 8pt; line-height: 1.35; }
    .inv--half + .inv--half { border-top: 1px dashed #9ca3af; position: relative; }
    .inv--half + .inv--half::before { content: '\2702'; position: absolute; left: 6mm; top: -2.6mm; font-size: 9pt; line-height: 1; background: #fff; padding: 0 1mm; color: #6b7280; }
    .inv--half .inv-head { padding-bottom: 2.5mm; }
    .inv--half .inv-logo { max-height: 9mm; margin-bottom: 1mm; }
    .inv--half .inv-store { font-size: 13pt; margin-bottom: .5mm; }
    .inv--half .inv-muted { font-size: 7pt; }
    .inv--half .inv-title { font-size: 14pt; }
    .inv--half .inv-no { font-size: 9pt; margin-top: .5mm; }
    .inv--half .inv-barcode { height: 8mm; width: 40mm; margin-top: 1mm; }
    .inv--half .inv-parties { gap: 6mm; }
    .inv--half .inv-parties .inv-strong { font-size: 9pt; }
    .inv--half .inv-label { font-size: 6.5pt; margin-bottom: .3mm; }
    .inv--half .inv-items th { font-size: 6.5pt; padding-bottom: 1mm; }
    .inv--half .inv-items td { padding: 1mm 2mm; }
    .inv--half .inv-item > div { display: flex; gap: 2mm; min-width: 0; }
    .inv--half .inv-item .inv-strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 95mm; }
    .inv--half .inv-item .inv-muted { white-space: nowrap; }
    .inv--half .inv-bottom { grid-template-columns: 1fr 62mm; gap: 6mm; }
    .inv--half .inv-terms { font-size: 7pt; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
    .inv--half .inv-total { font-size: 10pt; padding-top: 1mm; }
    .inv--half .inv-due { padding: 1.2mm 2.5mm; font-size: 7.5pt; }
    .inv--half .inv-due b { font-size: 10pt; }
    .inv--half .inv-paid { font-size: 9pt; }
    /* Half page: barcode beside the invoice number, signature under the notes, to leave room for items. */
    .inv--half { position: relative; }
    .inv--half .inv-meta { display: grid; grid-template-areas: "bar copy" "bar title" "bar no" "bar date"; column-gap: 4mm; align-items: center; }
    .inv--half .inv-copy { grid-area: copy; justify-self: end; margin-bottom: .8mm; }
    .inv--half .inv-title { grid-area: title; }
    .inv--half .inv-no { grid-area: no; }
    .inv--half .inv-meta > .inv-muted { grid-area: date; }
    .inv--half .inv-barcode { grid-area: bar; margin: 0; }
    .inv--half .inv-notes { padding-bottom: 11mm; }
    .inv--half .inv-terms { -webkit-line-clamp: 3; }
    .inv--half .inv-foot { position: absolute; left: 10mm; bottom: 6mm; right: 76mm; padding-top: 0; border-top: 0; font-size: 7pt; }
    .inv--half .inv-sign { height: 5mm; width: 38mm; }

    /* ---------- Thermal receipt (80mm / 58mm roll) ---------- */
    .rcpt { background: #fff; color: #000; padding: 4mm 3.5mm 6mm; font-size: 8.5pt; line-height: 1.35; font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; }
    .fmt-thermal .rcpt { width: 80mm; }
    .fmt-thermal58 .rcpt { width: 58mm; padding: 3mm 2mm 5mm; font-size: 7.5pt; }
    .rcpt .center { text-align: center; }
    .rcpt-logo { max-height: 14mm; max-width: 80%; object-fit: contain; filter: grayscale(1) contrast(1.4); }
    .rcpt-store { font-size: 12pt; font-weight: 800; }
    .fmt-thermal58 .rcpt-store { font-size: 10pt; }
    .rcpt hr { border: 0; border-top: 1px dashed #000; margin: 2.5mm 0; }
    .rcpt-row { display: flex; justify-content: space-between; gap: 2mm; }
    .rcpt-row > :last-child { text-align: right; white-space: nowrap; }
    .rcpt-item { margin-bottom: 1.5mm; }
    .rcpt-item .name { font-weight: 600; }
    .rcpt-total { font-size: 11pt; font-weight: 800; }
    .fmt-thermal58 .rcpt-total { font-size: 9.5pt; }
    .rcpt-due { margin-top: 1.5mm; padding: 1.5mm 2mm; border: 1.5px solid #000; font-weight: 800; }
    .rcpt-barcode { display: block; width: 100%; height: 12mm; margin-top: 2mm; }
    .rcpt-small { font-size: 7pt; }
    .rcpt-terms { white-space: pre-line; font-size: 7pt; }

    @media print {
      body { background: #fff; }
      .bar { display: none; }
      .stage { padding: 0; display: block; overflow: visible; }
      .paper { box-shadow: none; break-after: page; page-break-after: always; }
      .rcpt { break-after: page; page-break-after: always; }
      .paper:last-child, .rcpt:last-child { break-after: auto; page-break-after: auto; }
      .fmt-a4 .paper { min-height: 0; }
      .fmt-a4 .inv { min-height: 274mm; } /* signature at the foot of the page; longer orders flow onto more pages */
    }
  </style>
  @if($format === 'a4')
    <style media="print">@page { size: A4; margin: 11mm 0; } .fmt-a4 .inv { padding: 1mm 14mm; }</style>
  @elseif($format === 'half')
    <style media="print">@page { size: A4; margin: 0; }</style>
  @else
    {{-- Receipt height is measured after rendering so the roll is cut right after the receipt. --}}
    <style media="print" id="receiptPage">@page { size: {{ $format === 'thermal58' ? '58mm' : '80mm' }} 200mm; margin: 0; }</style>
    <style media="print" id="receiptPages"></style>
  @endif
</head>
<body class="fmt-{{ $format }}">
  <div class="bar">
    @if($single)
      <a href="{{ route('admin.orders.show', $order) }}" class="back">&larr; Order</a>
      <div class="grow">
        <b>Invoice #{{ $inv['number'] }}</b>
        <span>{{ $order->customer_name }} &middot; {{ money($order->total) }}</span>
      </div>
    @else
      <a href="{{ route('admin.orders.index') }}" class="back">&larr; Orders</a>
      <div class="grow">
        <b>{{ $orders->count() }} invoices</b>
        <span>{{ $sheets ? $sheets . ' A4 ' . Str::plural('sheet', $sheets) . ($format === 'half' ? ', two orders per sheet' : ' (more if an order is long)') : 'One receipt per order' }}</span>
      </div>
    @endif
    <nav class="seg" aria-label="Invoice format">
      @foreach($formats as $key => $label)
        <a href="{{ $formatUrl($key) }}" class="{{ $format === $key ? 'on' : '' }}" data-format="{{ $key }}" @if($format === $key) aria-current="page" @endif>{{ $label }}</a>
      @endforeach
    </nav>
    <button type="button" class="btn" onclick="window.print()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
      Print
    </button>
    <p class="hint" id="halfHint" hidden></p>
    @include('admin.orders.partials.print-tracking', ['type' => 'invoice'])
  </div>

  <div class="stage">
    @if($format === 'a4')
      @foreach($orders as $order)
        <div class="paper">
          @include('admin.orders.partials.invoice-sheet', ['inv' => $invFor($order), 'half' => false, 'copy' => null])
        </div>
      @endforeach
    @elseif($format === 'half' && $single)
      <div class="paper">
        @include('admin.orders.partials.invoice-sheet', ['inv' => $invFor($order), 'half' => true, 'copy' => 'Customer copy'])
        @include('admin.orders.partials.invoice-sheet', ['inv' => $invFor($order), 'half' => true, 'copy' => 'Office copy'])
      </div>
    @elseif($format === 'half')
      {{-- Two different orders per A4 sheet. --}}
      @foreach($orders->chunk(2) as $pair)
        <div class="paper">
          @foreach($pair as $order)
            @include('admin.orders.partials.invoice-sheet', ['inv' => $invFor($order), 'half' => true, 'copy' => null])
          @endforeach
        </div>
      @endforeach
    @else
      @foreach($orders->values() as $index => $order)
        @include('admin.orders.partials.invoice-receipt', ['inv' => $invFor($order)])
      @endforeach
    @endif
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script>
    (function () {
      document.querySelectorAll('[jsbarcode-value]').forEach(function (svg) {
        try {
          JsBarcode(svg, svg.getAttribute('jsbarcode-value'), { format: 'CODE128', width: 2, height: 60, margin: 6, displayValue: false });
          svg.setAttribute('preserveAspectRatio', 'none');
        } catch (e) {}
      });

      // Remember the chosen format for the print buttons on the order pages.
      try { localStorage.setItem('admin.invoice.format', @json($format)); } catch (e) {}

      // Thermal: size the printed page to the receipt so the roll isn't fed further than needed.
      // Each receipt prints on its own named page (rcpt0, rcpt1, ...) sized to that receipt.
      var receiptPages = document.getElementById('receiptPages');
      function fitReceipt() {
        if (!receiptPages) return;
        var width = @json($format === 'thermal58' ? '58mm' : '80mm');
        receiptPages.textContent = Array.prototype.map.call(document.querySelectorAll('[data-receipt]'), function (r, i) {
          var mm = Math.ceil(r.getBoundingClientRect().height * 25.4 / 96) + 2;
          return '@page rcpt' + i + ' { size: ' + width + ' ' + mm + 'mm; margin: 0; }';
        }).join('\n');
      }

      // Half page: each copy is exactly half an A4. List as many items as fit and keep the totals visible.
      function fitHalves() {
        var hidden = 0, cut = 0;
        document.querySelectorAll('.inv--half').forEach(function (copy) {
          var rows = Array.prototype.slice.call(copy.querySelectorAll('.inv-row'));
          var more = copy.querySelector('.inv-more');
          rows.forEach(function (r) { r.hidden = false; });
          if (more) more.hidden = true;
          var shown = rows.length;
          while (copy.scrollHeight > copy.clientHeight + 1 && shown > 1) {
            rows[--shown].hidden = true;
            if (more) {
              var n = rows.length - shown;
              more.hidden = false;
              more.cells[1].textContent = '+ ' + n + ' more ' + (n === 1 ? 'item' : 'items') + ' (included in the total)';
            }
          }
          hidden = Math.max(hidden, rows.length - shown);
          if (rows.length - shown > 0) cut++;
        });
        var hint = document.getElementById('halfHint');
        if (hint) {
          hint.hidden = hidden === 0;
          hint.textContent = @json($single)
            ? 'Half page has room for ' + (document.querySelectorAll('.inv--half .inv-row').length / 2 - hidden) +
              ' of this order\'s items; the rest are summarised (the total includes everything). Use Full page to list them all.'
            : cut + ' of these invoices have more items than fit on half a page; those items are summarised (totals include everything). Use Full page to list them all.';
        }
      }

      var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
      window.addEventListener('load', function () {
        ready.then(function () {
          fitHalves();
          fitReceipt();
          @if(request()->boolean('print'))
            setTimeout(function () { window.print(); }, 250);
          @endif
        });
      });
      window.addEventListener('beforeprint', function () { fitHalves(); fitReceipt(); });
    })();
  </script>
</body>
</html>
