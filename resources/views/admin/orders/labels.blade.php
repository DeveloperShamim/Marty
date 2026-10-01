@php
    $store = setting('invoice_company_name', site_name());
    $storePhone = setting('invoice_phone') ?: setting('contact_phone');
    $sizeUrl = fn ($s) => request()->fullUrlWithQuery(['size' => $s]);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Parcel Labels ({{ $orders->count() }}) - {{ $store }}</title>
  <link rel="icon" href="{{ favicon_url() }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #e5e7eb; color: #111; font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Toolbar (screen only) */
    .bar { position: sticky; top: 0; z-index: 10; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; padding: 12px 16px; background: #fff; border-bottom: 1px solid #e5e7eb; }
    .bar h1 { font-size: 15px; margin: 0; font-weight: 700; }
    .bar p { margin: 2px 0 0; font-size: 12px; color: #6b7280; }
    .bar .grow { flex: 1; min-width: 200px; }
    .seg { display: inline-flex; border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; }
    .seg a { padding: 8px 12px; font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; }
    .seg a.on { background: #0f766e; color: #fff; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 10px; border: 0; background: #0f766e; color: #fff; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn:hover { background: #0b4f4a; }
    .btn[disabled] { opacity: .5; cursor: wait; }
    .empty { max-width: 420px; margin: 80px auto; text-align: center; color: #6b7280; font-size: 14px; }
    .pages { padding: 24px 16px; display: flex; flex-direction: column; align-items: center; gap: 24px; }

    /* Label */
    .label { background: #fff; border: 1.2px solid #111; display: flex; flex-direction: column; overflow: hidden; break-inside: avoid; page-break-inside: avoid; }
    .l-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 3mm; padding: 2.5mm 3mm; border-bottom: 1px solid #111; }
    .l-store { font-weight: 800; font-size: 11pt; line-height: 1.15; }
    .l-small { font-size: 8pt; color: #333; }
    .l-courier { text-align: right; font-size: 8.5pt; font-weight: 700; }
    .l-code { padding: 2mm 3mm 1.5mm; border-bottom: 1px solid #111; text-align: center; }
    .l-code svg { display: block; width: 100%; height: 22mm; }
    .l-num { font: 800 13pt/1.1 ui-monospace, Menlo, Consolas, monospace; letter-spacing: .04em; margin-top: 1mm; }
    .l-to { padding: 2.5mm 3mm; }
    .l-tag { font-size: 7pt; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #555; }
    .l-name { font-size: 13pt; font-weight: 800; line-height: 1.2; margin-top: .5mm; }
    .l-phone { font: 800 14pt/1.2 ui-monospace, Menlo, Consolas, monospace; margin-top: .5mm; }
    .l-addr { font-size: 9.5pt; line-height: 1.3; margin-top: 1mm; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .l-city { font-size: 10pt; font-weight: 700; margin-top: .5mm; }
    .l-items { flex: 1; min-height: 0; overflow: hidden; padding: 2mm 3mm; border-top: 1px dashed #888; font-size: 8.5pt; line-height: 1.4; }
    .l-items div { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .l-foot { display: flex; border-top: 1px solid #111; }
    .l-meta { flex: 1; min-width: 0; padding: 2mm 3mm; font-size: 8.5pt; line-height: 1.35; display: flex; flex-direction: column; justify-content: center; }
    .l-cod { width: 36mm; flex-shrink: 0; border-left: 1px solid #111; padding: 2mm; text-align: center; display: flex; flex-direction: column; justify-content: center; }
    .l-cod.due { background: #111; color: #fff; }
    .l-cod b { display: block; font-size: 14pt; line-height: 1.1; margin-top: .5mm; }

    /* Thermal 100 x 150 mm (4 x 6 in), one label per page */
    .size-thermal .label { width: 100mm; height: 150mm; }
    .size-thermal .l-code svg { height: 26mm; }
    .size-thermal .l-name { font-size: 15pt; }
    .size-thermal .l-phone { font-size: 16pt; }
    .size-thermal .l-addr { font-size: 11pt; -webkit-line-clamp: 4; }
    .size-thermal .l-city { font-size: 11.5pt; }
    /* A4 sheet: 4 labels (2 x 2) per page, cut along the borders */
    .size-a4 .sheet { width: 210mm; height: 297mm; padding: 8mm; background: #fff; display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 4mm; }
    .size-a4 .label { width: 100%; height: 100%; }
    .size-a4 .l-code svg { height: 20mm; }
    .size-a4 .l-items { font-size: 8pt; }

    @media print {
      body { background: #fff; }
      .bar { display: none; }
      .pages { padding: 0; gap: 0; display: block; }
      .size-thermal .label { page-break-after: always; break-after: page; border: 0; }
      .size-thermal .label:last-child, .size-a4 .sheet:last-child { page-break-after: auto; break-after: auto; }
      .size-a4 .sheet { page-break-after: always; break-after: page; }
    }
  </style>
  <style media="print">@page { size: {{ $size === 'a4' ? 'A4' : '100mm 150mm' }}; margin: 0; }</style>
</head>
<body class="size-{{ $size }}">
  <div class="bar">
    <div class="grow">
      <h1>Parcel labels &middot; {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }}</h1>
      <p>Stick one on each parcel. The barcode is the order number, so it scans at Courier Scan.</p>
    </div>
    <div class="seg" role="group" aria-label="Label size">
      <a href="{{ $sizeUrl('thermal') }}" class="{{ $size === 'thermal' ? 'on' : '' }}">4×6 in thermal</a>
      <a href="{{ $sizeUrl('a4') }}" class="{{ $size === 'a4' ? 'on' : '' }}">A4 (4 per page)</a>
    </div>
    @if($orders->isNotEmpty())
      <button type="button" class="btn" id="printBtn" onclick="window.print()" disabled>
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        Print labels
      </button>
      @include('admin.orders.partials.print-tracking', ['type' => 'label', 'format' => $size])
    @endif
  </div>

  @if($orders->isEmpty())
    <div class="empty">No orders to print. Confirm orders first, or open labels from an order's page.</div>
  @else
    <div class="pages">
      @foreach($size === 'a4' ? $orders->chunk(4) : [$orders] as $sheet)
        @if($size === 'a4')<div class="sheet">@endif
        @foreach($sheet as $order)
          @php
            $collect = $order->amountToCollect();
            $itemCount = (int) $order->items->sum('quantity');
          @endphp
          <section class="label">
            <div class="l-head">
              <div>
                <div class="l-store">{{ $store }}</div>
                @if($storePhone)<div class="l-small">{{ $storePhone }}</div>@endif
              </div>
              <div class="l-courier">
                @if($order->courier_name){{ $order->courierLabel() }}@endif
                @if($order->courier_tracking_code)<div class="l-small">ID {{ $order->courier_tracking_code }}</div>@endif
                <div class="l-small">{{ $order->created_at->format('d M Y') }}</div>
              </div>
            </div>

            <div class="l-code">
              <svg class="order-barcode" jsbarcode-value="{{ $order->order_number }}" role="img" aria-label="Barcode {{ $order->order_number }}"></svg>
              <div class="l-num">{{ $order->order_number }}</div>
            </div>

            <div class="l-to">
              <div class="l-tag">Deliver to</div>
              <div class="l-name">{{ $order->customer_name }}</div>
              <div class="l-phone">{{ $order->customer_phone }}</div>
              <div class="l-addr">{{ $order->shipping_address }}</div>
              <div class="l-city">{{ collect([$order->city, $order->postal_code])->filter()->implode(' ') }}@if($order->shipping_zone) &middot; {{ shipping_zone_label($order->shipping_zone) }}@endif</div>
            </div>

            <div class="l-items">
              <div class="l-tag">Contents</div>
              @foreach($order->items->take(5) as $item)
                <div>{{ $item->quantity }} &times; {{ $item->product_name }}@if($item->variant) &middot; {{ $item->variant }}@endif</div>
              @endforeach
              @if($order->items->count() > 5)<div>+ {{ $order->items->count() - 5 }} more</div>@endif
            </div>

            <div class="l-foot">
              <div class="l-meta">
                <b>{{ $itemCount }} {{ Str::plural('item', $itemCount) }}</b>
                <span class="l-small">{{ $order->paymentMethodLabel() }}</span>
              </div>
              <div class="l-cod {{ $collect > 0 ? 'due' : '' }}">
                @if($collect > 0)
                  <span class="l-tag" style="color:#fff">Collect (COD)</span>
                  <b>&#2547;{{ number_format($collect) }}</b>
                @else
                  <span class="l-tag">Prepaid</span>
                  <b>&#2547;0</b>
                @endif
              </div>
            </div>
          </section>
        @endforeach
        @if($size === 'a4')</div>@endif
      @endforeach
    </div>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script>
    (function () {
      var btn = document.getElementById('printBtn');
      try {
        document.querySelectorAll('.order-barcode').forEach(function (svg) {
          // CODE128 with a wide quiet zone, stretched to the label width so guns and phone cameras read it easily.
          JsBarcode(svg, svg.getAttribute('jsbarcode-value'), { format: 'CODE128', width: 2, height: 80, margin: 12, displayValue: false });
          svg.setAttribute('preserveAspectRatio', 'none');
        });
      } catch (e) {
        alert('Barcodes could not be drawn. Check the internet connection and reload.');
      }
      if (btn) btn.disabled = false;
      @if(request()->boolean('print'))
        if (btn) window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
      @endif
    })();
  </script>
</body>
</html>
