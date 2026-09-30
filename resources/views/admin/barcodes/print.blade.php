<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Print Barcode Labels - {{ $storeName }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #f1f5f9;
      color: #000;
    }
    .no-print-bar {
      background: #1e293b;
      color: #fff;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .no-print-bar button {
      background: #10b981;
      color: #fff;
      border: none;
      padding: 8px 18px;
      border-radius: 8px;
      font-weight: bold;
      font-size: 13px;
      cursor: pointer;
    }
    .no-print-bar button:hover {
      background: #059669;
    }

    /* Print container & styles */
    .print-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      padding: 20px;
      margin: auto;
    }

    /* Format 1: Thermal 50mm x 30mm */
    .format-thermal_50x30 .barcode-sticker {
      width: 50mm;
      height: 30mm;
      max-height: 30mm;
      padding: 2mm 3mm;
      background: #fff;
      border: 1px dashed #ccc;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      page-break-inside: avoid;
      overflow: hidden;
    }

    /* Format 2: Thermal 40mm x 25mm */
    .format-thermal_40x25 .barcode-sticker {
      width: 40mm;
      height: 25mm;
      max-height: 25mm;
      padding: 1.5mm 2mm;
      background: #fff;
      border: 1px dashed #ccc;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      page-break-inside: avoid;
      overflow: hidden;
    }

    /* Format 3: A4 3-Column Sheet */
    .format-a4_3col .barcode-sticker {
      width: 63.5mm;
      height: 33.9mm;
      padding: 2.5mm 3mm;
      background: #fff;
      border: 1px dashed #e2e8f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      page-break-inside: avoid;
      overflow: hidden;
    }

    /* Format 4: A4 4-Column Sheet */
    .format-a4_4col .barcode-sticker {
      width: 48.5mm;
      height: 25.4mm;
      padding: 1.5mm 2mm;
      background: #fff;
      border: 1px dashed #e2e8f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      page-break-inside: avoid;
      overflow: hidden;
    }

    .sticker-store {
      font-size: 8px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #111;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }
    .sticker-name {
      font-size: 9px;
      font-weight: 700;
      color: #000;
      line-height: 1.1;
      max-height: 20px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    .sticker-svg-wrap {
      width: 100%;
      display: flex;
      justify-content: center;
    }
    .sticker-svg-wrap svg {
      max-width: 100%;
      height: auto;
    }
    .sticker-footer {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 8px;
      font-weight: 700;
      padding-top: 1px;
    }
    .sticker-price {
      font-size: 10px;
      font-weight: 900;
      color: #000;
    }

    @media print {
      body {
        background: #fff;
      }
      .no-print-bar {
        display: none !important;
      }
      .print-container {
        padding: 0;
        gap: 0;
      }
      .barcode-sticker {
        border: none !important;
      }
      @page {
        margin: 0;
        size: auto;
      }
    }
  </style>
</head>
<body class="format-{{ $sheetFormat }}">

  <div class="no-print-bar">
    <div>
      <strong>Barcode Print Sheet</strong>
      <span style="font-size: 12px; color: #94a3b8; margin-left: 10px;">Total Stickers: {{ count($labels) }} | Format: {{ strtoupper(str_replace('_', ' ', $sheetFormat)) }}</span>
    </div>
    <div style="display: flex; gap: 8px;">
      <button onclick="window.print()">🖨️ Print Labels Now</button>
      <button onclick="window.close()" style="background: #475569;">Close Window</button>
    </div>
  </div>

  <div class="print-container">
    @foreach($labels as $idx => $label)
      <div class="barcode-sticker">
        @if($showStoreName)
          <div class="sticker-store">{{ $storeName }}</div>
        @endif

        <div class="sticker-name">{{ $label['name'] }}</div>

        <div class="sticker-svg-wrap">
          <svg class="barcode-svg" jsbarcode-format="CODE128" jsbarcode-value="{{ $label['barcode'] }}" jsbarcode-textmargin="0" jsbarcode-fontoptions="bold" jsbarcode-width="1.3" jsbarcode-height="24" jsbarcode-fontsize="9" jsbarcode-margin="0"></svg>
        </div>

        <div class="sticker-footer">
          <span style="font-family: monospace;">{{ $label['sku'] }}</span>
          @if($showPrice)
            <span class="sticker-price">৳{{ number_format($label['price'], 0) }}</span>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      JsBarcode(".barcode-svg").init();
      // Optional slight delay before print dialog so SVGs render
      setTimeout(function() {
        // window.print();
      }, 500);
    });
  </script>
</body>
</html>
