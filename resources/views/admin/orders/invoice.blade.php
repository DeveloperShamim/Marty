<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice #{{ (setting('order_number_prefix') && !str_starts_with($order->order_number, setting('order_number_prefix'))) ? setting('order_number_prefix') . $order->order_number : $order->order_number }} - {{ setting('invoice_company_name', site_name()) }}</title>
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
              50: '#fff7ed',
              100: '#ffedd5',
              500: '#f97316',
              600: '#ea580c',
              700: '#c2410c',
              900: '#7c2d12',
            },
            slate: {
              850: '#1e293b',
            }
          }
        }
      }
    };
  </script>
  <style>
    @media print {
      body {
        background: #ffffff !important;
        color: #0f172a !important;
        padding: 0 !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .no-print {
        display: none !important;
      }
      .invoice-card {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      .page-break-inside-avoid {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
      }
      tr {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
      }
      @page {
        size: A4;
        margin: 12mm 15mm;
      }
    }
  </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen py-6 sm:py-10 px-4">

  <!-- Top Action Control Bar (Hidden on Print) -->
  <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white/90 backdrop-blur p-4 rounded-2xl shadow-sm border border-slate-200/80">
    <div class="flex items-center gap-3">
      <button onclick="window.history.back()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
        <span>&larr;</span> Back
      </button>
      <span class="text-slate-300">|</span>
      <span class="text-xs font-extrabold text-slate-700">Order {{ $order->order_number }}</span>
    </div>

    <div class="flex items-center gap-3">
      <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 border border-slate-200 hover:bg-slate-50 rounded-xl transition">
        <span>✏️</span> Manage Order
      </a>
      <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-extrabold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white rounded-xl shadow-sm hover:shadow transition cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        <span>Print Invoice / Download PDF</span>
      </button>
    </div>
  </div>

  <!-- Main Executive Invoice Card -->
  <div class="invoice-card max-w-4xl mx-auto bg-white p-6 sm:p-10 rounded-3xl shadow-xl border border-slate-200/80 space-y-8">
    
    <!-- Top Header Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start border-b border-slate-200 pb-6 gap-6">
      <div class="space-y-1.5">
        @if(has_custom_logo())
          <img src="{{ logo_url() }}" alt="{{ setting('invoice_company_name', site_name()) }}" class="h-12 max-w-[200px] object-contain mb-2">
        @endif
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          {{ setting('invoice_company_name', site_name()) }}
        </h1>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Official Purchase Invoice &amp; Receipt</p>
        
        <div class="pt-2 text-xs text-slate-600 space-y-0.5">
          @if(setting('invoice_vat_number'))
            <p><span class="font-bold text-slate-800">VAT / BIN:</span> <span class="font-mono">{{ setting('invoice_vat_number') }}</span></p>
          @endif
          @if(setting('invoice_phone') || setting('contact_phone'))
            <p><span class="font-bold text-slate-800">Helpline:</span> {{ setting('invoice_phone') ?: setting('contact_phone') }}</p>
          @endif
          @if(setting('contact_email'))
            <p><span class="font-bold text-slate-800">Support Email:</span> {{ setting('contact_email') }}</p>
          @endif
          @if(setting('invoice_address') || setting('contact_address'))
            <p class="max-w-sm text-slate-500 leading-tight mt-1">{{ setting('invoice_address') ?: setting('contact_address') }}</p>
          @endif
        </div>
      </div>

      <!-- Right Header Badge -->
      <div class="text-left sm:text-right space-y-2 shrink-0">
        <div class="inline-block bg-slate-900 text-white px-4 py-1.5 rounded-xl text-xs font-black tracking-wider uppercase shadow-xs">
          INVOICE RECEIPT
        </div>
        <div>
          <p class="text-xl font-extrabold text-orange-600 font-mono">
            #{{ (setting('order_number_prefix') && !str_starts_with($order->order_number, setting('order_number_prefix'))) ? setting('order_number_prefix') . $order->order_number : $order->order_number }}
          </p>
          <p class="text-xs font-semibold text-slate-500 mt-0.5">
            Placed: {{ $order->created_at->format('d M Y, g:i A') }}
          </p>
        </div>

        <div class="flex items-center sm:justify-end gap-2 pt-1 flex-wrap">
          <span class="px-3 py-1 text-[11px] font-extrabold rounded-full border {{ $order->payment_status === 'verified' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
            Payment: {{ strtoupper($order->payment_status) }}
          </span>
          <span class="px-3 py-1 text-[11px] font-extrabold rounded-full bg-slate-100 text-slate-800 border border-slate-200">
            Status: {{ strtoupper($order->status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Customer & Order Information Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
      <!-- Customer Card -->
      <div class="bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-slate-200/80 space-y-2">
        <h3 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200 pb-2 flex items-center gap-1.5">
          <span>👤</span> Customer &amp; Delivery Information
        </h3>
        <div class="space-y-1 pt-1">
          <p class="text-sm font-extrabold text-slate-900">{{ $order->customer_name }}</p>
          <p class="font-medium text-slate-700"><span class="text-slate-400 font-normal">Phone:</span> {{ $order->customer_phone }}</p>
          @if($order->customer_email)
            <p class="font-medium text-slate-700"><span class="text-slate-400 font-normal">Email:</span> {{ $order->customer_email }}</p>
          @endif
          <div class="pt-1.5 border-t border-slate-200/60 mt-2">
            <span class="text-slate-400 font-normal block mb-0.5">Shipping Address:</span>
            <p class="font-semibold text-slate-800 leading-snug">{{ $order->shipping_address }}, {{ $order->city }} {{ $order->postal_code }}</p>
          </div>
        </div>
      </div>

      <!-- Payment & Dispatch Card -->
      <div class="bg-slate-50/80 p-4 sm:p-5 rounded-2xl border border-slate-200/80 space-y-2">
        <h3 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200 pb-2 flex items-center gap-1.5">
          <span>💳</span> Payment &amp; Logistics Details
        </h3>
        <div class="space-y-1.5 pt-1">
          <div class="flex justify-between">
            <span class="text-slate-500">Payment Method:</span>
            <span class="font-bold text-slate-900">{{ $order->paymentMethodLabel() }}</span>
          </div>
          @if($order->isMobileBanking())
            <div class="flex justify-between">
              <span class="text-slate-500">Sender Phone:</span>
              <span class="font-mono font-bold text-slate-800">{{ $order->payment_sender_number ?: 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">TrxID / Ref:</span>
              <span class="font-mono font-bold text-slate-900 bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $order->payment_txn_id ?: 'N/A' }}</span>
            </div>
          @endif
          <div class="flex justify-between">
            <span class="text-slate-500">Delivery Zone:</span>
            <span class="font-bold text-slate-800">{{ shipping_zone_label($order->shipping_zone) }}</span>
          </div>

          @if($order->isDispatchedToCourier())
            <div class="pt-1.5 border-t border-slate-200/60 flex justify-between items-center text-[11px]">
              <span class="text-slate-500">Courier Provider:</span>
              <span class="font-extrabold text-emerald-700">{{ $order->courierLabel() }} (#{{ $order->courier_tracking_code }})</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Order Items List Table (Handles large order lists cleanly) -->
    <div class="space-y-2">
      <div class="flex items-center justify-between border-b border-slate-200 pb-2">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
          <span>🛍️</span> Ordered Items List ({{ $order->items->sum('quantity') }} items)
        </h3>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-slate-200">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-900 text-white uppercase text-[11px] font-extrabold tracking-wider">
              <th class="py-3 px-3 w-10 text-center">#</th>
              <th class="py-3 px-4">Item Details</th>
              <th class="py-3 px-3 text-center">Unit Price</th>
              <th class="py-3 px-3 text-center">Qty</th>
              <th class="py-3 px-4 text-right">Line Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            @foreach($order->items as $index => $item)
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-3 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-3">
                    @if($item->imageUrl())
                      <img src="{{ $item->imageUrl() }}" alt="" class="h-10 w-10 object-cover rounded-lg bg-slate-100 border border-slate-200 shrink-0">
                    @endif
                    <div>
                      <p class="font-bold text-slate-900 text-xs leading-snug">{{ $item->product_name }}</p>
                      @if($item->variant)
                        <span class="inline-block mt-0.5 text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                          {{ $item->variant }}
                        </span>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="py-3.5 px-3 text-center font-medium text-slate-700 font-mono">{{ money($item->unit_price) }}</td>
                <td class="py-3.5 px-3 text-center">
                  <span class="inline-block font-extrabold text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200 text-[11px]">
                    {{ $item->quantity }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 font-mono text-xs">{{ money($item->line_total) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Calculations & Terms Footer Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 pt-2 border-t border-slate-200 page-break-inside-avoid">
      <!-- Left: Staff Notes & Terms -->
      <div class="sm:col-span-7 space-y-3 text-xs">
        @if($order->internal_note)
          <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 space-y-1">
            <p class="font-bold text-[11px] uppercase tracking-wider text-amber-800">📌 Staff Order Note:</p>
            <p class="text-xs leading-relaxed">{{ $order->internal_note }}</p>
          </div>
        @endif

        @if(setting('invoice_terms'))
          <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-1">
            <p class="font-extrabold text-[11px] uppercase tracking-wider text-slate-600">📜 Terms &amp; Exchange Policy:</p>
            <p class="text-[11px] text-slate-600 leading-relaxed whitespace-pre-line">{{ setting('invoice_terms') }}</p>
          </div>
        @else
          <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-1">
            <p class="font-extrabold text-slate-800">Thank you for your business!</p>
            <p class="text-[11px] text-slate-500">If you have any questions regarding your parcel, please contact our support helpline.</p>
          </div>
        @endif
      </div>

      <!-- Right: Subtotal, Shipping, Taxes & Total -->
      <div class="sm:col-span-5 space-y-2 text-xs">
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2">
          <div class="flex justify-between text-slate-600">
            <span>Items Subtotal:</span>
            <span class="font-bold text-slate-900 font-mono">{{ money($order->subtotal) }}</span>
          </div>

          @if($order->discount_amount > 0)
            <div class="flex justify-between text-orange-600 font-semibold">
              <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif:</span>
              <span class="font-bold font-mono">−{{ money($order->discount_amount) }}</span>
            </div>
          @endif

          <div class="flex justify-between text-slate-600">
            <span>Shipping Delivery Fee:</span>
            <span class="font-bold text-slate-900 font-mono">{{ money($order->shipping_charge) }}</span>
          </div>

          @if($order->tax > 0)
            <div class="flex justify-between text-slate-600">
              <span>VAT / Tax:</span>
              <span class="font-bold text-slate-900 font-mono">{{ money($order->tax) }}</span>
            </div>
          @endif

          <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm">
            <span class="font-black uppercase tracking-wider text-slate-900">Total Amount:</span>
            <span class="font-black text-base text-orange-600 font-mono">{{ money($order->total) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Printable Signature Footer -->
    <div class="pt-6 border-t border-dashed border-slate-300 flex justify-between items-end text-[11px] text-slate-400 page-break-inside-avoid">
      <div>
        <div class="h-8 border-b border-slate-300 w-36 mb-1"></div>
        <p class="font-extrabold text-slate-700">{{ setting('invoice_company_name', site_name()) }}</p>
        <p class="text-[10px]">Authorized Signature / Stamp</p>
      </div>

      <div class="text-right space-y-0.5">
        <p class="font-medium text-slate-500">Thank you for shopping with us!</p>
        <p class="text-[10px] text-slate-400">Generated on {{ now()->format('d M Y, g:i A') }}</p>
      </div>
    </div>

  </div>

  <script>
    window.addEventListener('load', function () {
      // Auto-trigger print dialog if not explicitly disabled
      setTimeout(function () {
        if (!window.location.search.includes('noprint')) {
          window.print();
        }
      }, 500);
    });
  </script>

</body>
</html>
