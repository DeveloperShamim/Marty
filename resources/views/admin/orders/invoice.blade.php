<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice #{{ $order->order_number }} - {{ site_name() }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'],
          },
          colors: {
            brand: {
              500: '#FC8933',
              600: '#E8751B',
            }
          }
        }
      }
    };
  </script>
  <style>
    body {
      font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif;
      background-color: #f3f4f6;
      color: #1f2937;
      margin: 0;
      padding: 0;
    }
    @media print {
      body {
        background-color: #ffffff !important;
        color: #000000 !important;
      }
      .no-print {
        display: none !important;
      }
      .invoice-container {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      @page {
        size: A4;
        margin: 15mm;
      }
    }
  </style>
</head>
<body class="py-8 px-4 sm:px-6">

  <!-- Top Action Bar (Hidden during Print) -->
  <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
    <div class="flex items-center gap-3">
      <button onclick="window.history.back()" class="text-sm text-gray-500 hover:text-gray-800 font-medium">
        &larr; Back
      </button>
      <span class="text-gray-300">|</span>
      <span class="text-sm font-semibold text-gray-700">Order {{ $order->order_number }}</span>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium text-sm rounded-lg shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print / Save PDF
      </button>
    </div>
  </div>

  <!-- Invoice Main Container -->
  <div class="invoice-container max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-md border border-gray-200">
    
    <!-- Invoice Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start border-b border-gray-200 pb-8 gap-6">
      <div>
        @if(has_custom_logo())
          <img src="{{ logo_url() }}" alt="{{ site_name() }}" class="h-12 w-auto mb-3 object-contain">
        @else
          <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ site_name() }}</h1>
        @endif
        <p class="text-xs text-gray-500 mt-1">E-Commerce Invoice & Packing Slip</p>
        @if(setting('contact_phone'))
          <p class="text-xs text-gray-500">Phone: {{ setting('contact_phone') }}</p>
        @endif
        @if(setting('contact_email'))
          <p class="text-xs text-gray-500">Email: {{ setting('contact_email') }}</p>
        @endif
      </div>

      <div class="text-left sm:text-right">
        <h2 class="text-3xl font-extrabold tracking-wider text-gray-900 uppercase">INVOICE</h2>
        <p class="text-sm font-semibold text-amber-600 mt-1">#{{ $order->order_number }}</p>
        <p class="text-xs text-gray-500 mt-1"><b>Date:</b> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        <div class="mt-2 inline-flex items-center gap-2">
          <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border border-gray-300 uppercase">
            Payment: {{ ucfirst($order->payment_status) }}
          </span>
          <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border border-gray-300 uppercase">
            Order: {{ ucfirst($order->status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Bill To / Ship To Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 my-8 text-sm">
      <div class="bg-gray-50 p-5 rounded-xl border border-gray-100">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Customer & Delivery Address</h3>
        <p class="font-bold text-gray-900 text-base mb-1">{{ $order->customer_name }}</p>
        <p class="text-gray-700"><b>Phone:</b> {{ $order->customer_phone }}</p>
        @if($order->customer_email)
          <p class="text-gray-700"><b>Email:</b> {{ $order->customer_email }}</p>
        @endif
        <p class="text-gray-700 mt-2">
          <b>Address:</b> {{ $order->shipping_address }}, {{ $order->city }} {{ $order->postal_code }}
        </p>
      </div>

      <div class="bg-gray-50 p-5 rounded-xl border border-gray-100">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Payment Details</h3>
        <p class="text-gray-700"><b>Payment Method:</b> {{ $order->paymentMethodLabel() }}</p>
        @if($order->isMobileBanking())
          <p class="text-gray-700 mt-1"><b>Sender Phone:</b> {{ $order->payment_sender_number ?: 'N/A' }}</p>
          <p class="text-gray-700 mt-1"><b>Transaction ID:</b> {{ $order->payment_txn_id ?: 'N/A' }}</p>
        @endif
        <p class="text-gray-700 mt-1"><b>Shipping Zone:</b> {{ shipping_zone_label($order->shipping_zone) }}</p>
        <p class="text-gray-700 mt-1"><b>Fulfillment Status:</b> {{ ucfirst($order->status) }}</p>
      </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto my-8">
      <table class="w-full text-left text-sm border-collapse">
        <thead>
          <tr class="border-b-2 border-gray-800 text-gray-800 uppercase text-xs tracking-wider">
            <th class="py-3 px-2 font-bold">#</th>
            <th class="py-3 px-2 font-bold">Item Description</th>
            <th class="py-3 px-2 font-bold text-center">Unit Price</th>
            <th class="py-3 px-2 font-bold text-center">Qty</th>
            <th class="py-3 px-2 font-bold text-right">Line Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @foreach($order->items as $index => $item)
            <tr>
              <td class="py-4 px-2 text-gray-500 text-xs">{{ $index + 1 }}</td>
              <td class="py-4 px-2">
                <p class="font-bold text-gray-900">{{ $item->product_name }}</p>
                @if($item->variant)
                  <p class="text-xs text-gray-500">Variant: {{ $item->variant }}</p>
                @endif
              </td>
              <td class="py-4 px-2 text-center text-gray-700">{{ money($item->unit_price) }}</td>
              <td class="py-4 px-2 text-center font-bold text-gray-900">{{ $item->quantity }}</td>
              <td class="py-4 px-2 text-right font-bold text-gray-900">{{ money($item->line_total) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <!-- Totals & Notes Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4 border-t border-gray-200">
      <div class="flex-1 text-xs text-gray-500 space-y-2">
        @if($order->internal_note)
          <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg text-amber-900">
            <p class="font-bold">Staff Note:</p>
            <p>{{ $order->internal_note }}</p>
          </div>
        @endif
        <p class="font-semibold text-gray-700">Thank you for your order!</p>
        <p>If you have any questions about this invoice, please contact support.</p>
      </div>

      <div class="w-full sm:w-72 space-y-2 text-sm">
        <div class="flex justify-between text-gray-600">
          <span>Subtotal</span>
          <span class="font-semibold text-gray-900">{{ money($order->subtotal) }}</span>
        </div>
        @if($order->discount_amount > 0)
          <div class="flex justify-between text-amber-600">
            <span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
            <span class="font-semibold">−{{ money($order->discount_amount) }}</span>
          </div>
        @endif
        <div class="flex justify-between text-gray-600">
          <span>Shipping Charge</span>
          <span class="font-semibold text-gray-900">{{ money($order->shipping_charge) }}</span>
        </div>
        @if($order->tax > 0)
          <div class="flex justify-between text-gray-600">
            <span>Tax</span>
            <span class="font-semibold text-gray-900">{{ money($order->tax) }}</span>
          </div>
        @endif
        <div class="flex justify-between text-base font-extrabold border-t-2 border-gray-900 pt-3 text-gray-900">
          <span>Total Payable</span>
          <span>{{ money($order->total) }}</span>
        </div>
      </div>
    </div>

    <!-- Footer Signature Line -->
    <div class="mt-16 pt-8 border-t border-dashed border-gray-300 flex justify-between items-end text-xs text-gray-400">
      <div>
        <p class="font-semibold text-gray-600">{{ site_name() }}</p>
        <p>Authorized Signature / Stamp</p>
      </div>
      <div>
        <p>Generated automatically on {{ now()->format('d M Y, h:i A') }}</p>
      </div>
    </div>

  </div>

  <script>
    window.addEventListener('load', function () {
      // Small timeout to allow fonts and CSS to render before print dialog
      setTimeout(function () {
        if (!window.location.search.includes('noprint')) {
          window.print();
        }
      }, 600);
    });
  </script>

</body>
</html>
