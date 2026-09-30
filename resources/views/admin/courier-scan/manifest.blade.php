<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courier Pickup Manifest - {{ $date }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 12px; color: #111; background: #f8fafc; }
    .no-print { background: #0f172a; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; }
    .no-print button { background: #10b981; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .sheet { max-width: 210mm; margin: 20px auto; background: #fff; padding: 15mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 15px; }
    .title { font-size: 18px; font-weight: 900; text-transform: uppercase; }
    .store { font-size: 13px; font-weight: 700; color: #475569; margin-top: 3px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
    td { border: 1px solid #e2e8f0; padding: 6px 8px; font-size: 11px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .signatures { display: flex; justify-content: space-between; margin-top: 50px; padding-top: 20px; }
    .sign-box { width: 45%; border-top: 1px solid #000; padding-top: 8px; text-align: center; font-size: 11px; font-weight: 700; }
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .sheet { box-shadow: none; margin: 0; padding: 8mm; max-width: 100%; }
      @page { margin: 10mm; size: A4 portrait; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <strong>Courier Pickup Manifest Sheet</strong>
      <span style="color: #94a3b8; margin-left: 10px;">Date: {{ $date }} | Total Parcels: {{ $orders->count() }}</span>
    </div>
    <div style="display: flex; gap: 8px;">
      <button onclick="window.print()">🖨️ Print Manifest</button>
      <button onclick="window.close()" style="background: #475569;">Close</button>
    </div>
  </div>

  <div class="sheet">
    <div class="header">
      <div>
        <div class="title">Courier Handover Manifest</div>
        <div class="store">{{ $storeName }} &middot; Phone: {{ $storePhone }}</div>
      </div>
      <div style="text-align: right;">
        <div><strong>Date:</strong> {{ date('d M Y', strtotime($date)) }}</div>
        <div><strong>Courier:</strong> {{ strtoupper($courier) }}</div>
        <div><strong>Total Parcels:</strong> {{ $orders->count() }}</div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width: 5%;">#</th>
          <th style="width: 18%;">Order Number</th>
          <th style="width: 25%;">Customer</th>
          <th style="width: 22%;">Address / City</th>
          <th style="width: 15%; text-align: right;">COD Amount</th>
          <th style="width: 15%; text-align: center;">Rider Check</th>
        </tr>
      </thead>
      <tbody>
        @php $grandTotal = 0; @endphp
        @forelse($orders as $i => $ord)
          @php $grandTotal += $ord->total; @endphp
          <tr>
            <td>{{ $i + 1 }}</td>
            <td style="font-family: monospace; font-weight: bold;">{{ $ord->order_number }}</td>
            <td>
              <strong>{{ $ord->customer_name }}</strong><br>
              <span style="color: #64748b;">{{ $ord->customer_phone }}</span>
            </td>
            <td>{{ $ord->shipping_address }}, {{ $ord->city }}</td>
            <td class="text-right" style="font-weight: bold;">৳{{ number_format($ord->total, 2) }}</td>
            <td class="text-center" style="font-size: 16px;">[ &nbsp; ]</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">No dispatched orders recorded for this date.</td>
          </tr>
        @endforelse
      </tbody>
      @if($orders->isNotEmpty())
        <tfoot>
          <tr style="background: #f8fafc; font-weight: bold;">
            <td colspan="4" class="text-right">TOTAL COD RECEIVABLE:</td>
            <td class="text-right">৳{{ number_format($grandTotal, 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      @endif
    </table>

    <div class="signatures">
      <div class="sign-box">
        Warehouse Dispatcher Signature &amp; Date
      </div>
      <div class="sign-box">
        Courier Rider Signature, Name &amp; Phone
      </div>
    </div>
  </div>

</body>
</html>
