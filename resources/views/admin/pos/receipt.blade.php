<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt - {{ $order->order_number }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
      font-size: 12px;
      line-height: 1.3;
      background: #f8fafc;
      color: #000;
    }
    .no-print-bar {
      background: #0f172a;
      color: #fff;
      padding: 10px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .no-print-bar button {
      background: #10b981;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
    .receipt-container {
      width: 78mm;
      max-width: 100%;
      margin: 20px auto;
      background: #fff;
      padding: 12px 10px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    .store-name {
      font-size: 16px;
      font-weight: 900;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .store-info {
      font-size: 11px;
      color: #333;
      margin-top: 2px;
    }
    .divider {
      border-top: 1px dashed #000;
      margin: 8px 0;
    }
    .double-divider {
      border-top: 2px solid #000;
      margin: 8px 0;
    }
    .order-meta {
      font-size: 11px;
      margin-bottom: 6px;
    }
    .order-meta div {
      display: flex;
      justify-content: space-between;
    }
    table.items-table {
      width: 100%;
      border-collapse: collapse;
      margin: 6px 0;
    }
    table.items-table th {
      border-bottom: 1px dashed #000;
      padding: 4px 0;
      font-size: 11px;
    }
    table.items-table td {
      padding: 4px 0;
      vertical-align: top;
      font-size: 11px;
    }
    .item-name {
      font-weight: bold;
    }
    .item-variant {
      font-size: 10px;
      color: #555;
    }
    .totals-area {
      font-size: 12px;
    }
    .totals-area div {
      display: flex;
      justify-content: space-between;
      padding: 2px 0;
    }
    .grand-total {
      font-size: 14px;
      font-weight: 900;
      padding-top: 4px;
    }
    .footer-note {
      text-align: center;
      font-size: 10px;
      margin-top: 12px;
      color: #444;
    }
    .barcode-area {
      text-align: center;
      margin-top: 10px;
    }
    .barcode-area svg {
      max-width: 100%;
      height: 35px;
    }

    @media print {
      body {
        background: #fff;
      }
      .no-print-bar {
        display: none !important;
      }
      .receipt-container {
        width: 100%;
        margin: 0;
        padding: 4px 0;
        box-shadow: none;
      }
      @page {
        margin: 0;
        size: 80mm auto;
      }
    }
  </style>
</head>
<body>

  <div class="no-print-bar">
    <span>Thermal Receipt: {{ $order->order_number }}</span>
    <div style="display: flex; gap: 8px;">
      <button onclick="window.print()">🖨️ Print Receipt</button>
      <button onclick="window.close()" style="background: #475569;">Close</button>
    </div>
  </div>

  <div class="receipt-container">
    <div class="text-center">
      <div class="store-name">{{ $storeName }}</div>
      <div class="store-info">{{ $storeAddress }}</div>
      <div class="store-info">Hotline: {{ $storePhone }}</div>
    </div>

    <div class="divider"></div>

    <div class="order-meta">
      <div><span>Order:</span> <strong class="bold">{{ $order->order_number }}</strong></div>
      <div><span>Date:</span> <span>{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
      <div><span>Cashier:</span> <span>{{ $order->user?->name ?? 'POS Staff' }}</span></div>
      <div><span>Customer:</span> <span>{{ $order->customer_name }} ({{ $order->customer_phone }})</span></div>
    </div>

    <div class="divider"></div>

    <table class="items-table">
      <thead>
        <tr>
          <th style="text-align: left;">Item</th>
          <th style="text-align: center; width: 16%;">Qty</th>
          <th style="text-align: right; width: 22%;">Rate</th>
          <th style="text-align: right; width: 26%;">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $it)
          <tr>
            <td>
              <div class="item-name">{{ $it->product_name }}</div>
              @if($it->variant)
                <div class="item-variant">[{{ $it->variant }}]</div>
              @endif
            </td>
            <td style="text-align: center;">{{ $it->quantity }}</td>
            <td style="text-align: right;">{{ number_format($it->unit_price, 0) }}</td>
            <td style="text-align: right;">{{ number_format($it->line_total, 0) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals-area">
      <div><span>Subtotal:</span> <span>৳{{ number_format($order->subtotal, 2) }}</span></div>
      @if($order->discount_amount > 0)
        <div><span>Discount:</span> <span>-৳{{ number_format($order->discount_amount, 2) }}</span></div>
      @endif
      @if($order->shipping_charge > 0)
        <div><span>Delivery / Shipping:</span> <span>৳{{ number_format($order->shipping_charge, 2) }}</span></div>
      @endif
      <div class="double-divider"></div>
      <div class="grand-total">
        <span>NET PAYABLE:</span>
        <span>৳{{ number_format($order->total, 2) }}</span>
      </div>
      <div>
        <span>Payment Method:</span>
        <strong class="bold">{{ strtoupper($order->payment_method) }}</strong>
      </div>
      @if($order->payment_method === 'cash')
        <div><span>Cash Tendered:</span> <span>৳{{ number_format($order->pos_cash_tendered ?: $order->total, 2) }}</span></div>
        <div><span>Change Returned:</span> <span class="bold">৳{{ number_format($order->pos_change_amount ?: 0, 2) }}</span></div>
      @endif
    </div>

    <div class="divider"></div>

    <div class="barcode-area">
      <svg id="receiptBarcode" jsbarcode-format="CODE128" jsbarcode-value="{{ $order->order_number }}" jsbarcode-textmargin="0" jsbarcode-fontoptions="bold" jsbarcode-width="1.3" jsbarcode-height="28" jsbarcode-fontsize="10"></svg>
    </div>

    <div class="footer-note">
      <p>Thank you for shopping with us!</p>
      <p>Please keep this receipt for warranty or exchange.</p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      JsBarcode("#receiptBarcode").init();
      setTimeout(function() {
        window.print();
      }, 350);
    });
  </script>
</body>
</html>
