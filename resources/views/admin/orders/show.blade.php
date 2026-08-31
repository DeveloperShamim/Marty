@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6">
  <!-- Top Navigation & Action Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div class="space-y-1.5">
      <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-slate-500 hover:text-brand-600 inline-flex items-center gap-1">
        <span>&larr;</span> Back to Orders List
      </a>
      <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">
          Order #{{ $order->order_number }}
        </h2>
        <span class="px-2.5 sm:px-3 py-1 text-xs font-extrabold rounded-full {{ $order->statusBadge() }}">
          {{ ucfirst($order->status) }}
        </span>
        <span class="px-2.5 sm:px-3 py-1 text-xs font-extrabold rounded-full {{ $order->paymentBadge() }}">
          Payment: {{ ucfirst($order->payment_status) }}
        </span>
        <span class="px-2.5 sm:px-3 py-1 text-xs font-extrabold rounded-full bg-slate-100 text-slate-700 border border-slate-200" title="Source: {{ $order->utm_source ?? 'Direct' }}">
          Source: {{ $order->utmSourceIcon() }}
        </span>
      </div>
      <p class="text-xs text-slate-400">Placed on {{ $order->created_at->format('d M Y, g:i A') }} ({{ $order->created_at->diffForHumans() }})</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2 flex-wrap shrink-0">
      <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 py-2.5 text-xs font-extrabold bg-amber-500 hover:bg-amber-600 text-white rounded-2xl shadow-sm transition">
        <span>🖨️</span> Print Invoice / PDF
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column (Items, Payment Verification & Financial Totals) -->
    <div class="lg:col-span-2 space-y-6">
      
      <!-- Fraud Risk Warning Card -->
      @if($order->fraud_score !== null)
        @if($order->fraudRiskLevel() === 'high' || $order->fraudRiskLevel() === 'medium')
          @php
            $isHighRisk = $order->fraudRiskLevel() === 'high';
            $cardBg = $isHighRisk ? 'bg-rose-50 border-rose-300 shadow-sm' : 'bg-amber-50 border-amber-300 shadow-sm';
            $headerColor = $isHighRisk ? 'text-rose-900' : 'text-amber-900';
            $btnClass = $isHighRisk ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white';
            $icon = $isHighRisk ? '🔴 HIGH RISK FRAUD WARNING' : '🟡 MEDIUM RISK WARNING';
          @endphp
          <div class="card p-5 border {{ $cardBg }} space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b {{ $isHighRisk ? 'border-rose-200' : 'border-amber-200' }} pb-3">
              <div>
                <h3 class="font-black text-sm {{ $headerColor }} flex items-center gap-2">
                  <span>{{ $icon }}</span>
                </h3>
                <p class="text-xs font-bold {{ $headerColor }} opacity-80 mt-1">Fraud Risk Score: {{ (int) $order->fraud_score }} / 100</p>
              </div>
              <form action="{{ route('admin.blacklist.store') }}" method="POST" class="shrink-0">
                @csrf
                <input type="hidden" name="type" value="phone">
                <input type="hidden" name="value" value="{{ $order->customer_phone }}">
                <input type="hidden" name="reason" value="Blocked from Order #{{ $order->order_number }} - High Fraud Score">
                <button type="submit" class="px-3.5 py-2 text-xs font-black rounded-xl shadow-xs transition {{ $btnClass }} inline-flex items-center gap-1.5 cursor-pointer">
                  <span>🚫</span> Block Phone Number
                </button>
              </form>
            </div>
            
            @if(is_array($order->fraud_flags) && count($order->fraud_flags) > 0)
              <div class="space-y-2">
                <p class="text-xs font-extrabold {{ $headerColor }}">Triggered Fraud Rules:</p>
                <ul class="space-y-1.5 ml-1">
                  @foreach($order->fraud_flags as $flag)
                    <li class="text-xs font-bold {{ $headerColor }} flex items-start gap-1.5">
                      <span class="mt-0.5">⚠️</span> 
                      <span>{{ $flag }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif
          </div>
        @else
          <div class="card p-4 border bg-emerald-50 border-emerald-200 flex items-center justify-between shadow-2xs">
              <h3 class="font-extrabold text-sm text-emerald-800 flex items-center gap-2">
                <span>🟢</span> Low Risk Order
              </h3>
              <div class="text-right">
                <span class="block text-xs font-extrabold text-emerald-700">Score: {{ (int) $order->fraud_score }} / 100</span>
                <span class="block text-[10px] font-medium text-emerald-600">Passed automated fraud checks</span>
              </div>
          </div>
        @endif
      @endif

      <!-- Ordered Items Card -->
      <div class="card overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
            <span>🛍️</span> Ordered Items ({{ $order->items->sum('quantity') }} items)
          </h3>
          <span class="text-xs font-bold text-slate-400 font-mono">Subtotal: {{ money($order->subtotal) }}</span>
        </div>

        <div class="divide-y divide-slate-100">
          @foreach($order->items as $item)
            <div class="p-5 space-y-3">
              <div class="flex items-start sm:items-center gap-4">
                <img src="{{ $item->imageUrl() }}" class="h-16 w-16 object-cover bg-slate-100 rounded-2xl border border-slate-200 shrink-0" alt="{{ $item->product_name }}">
                <div class="flex-1 min-w-0">
                  <p class="font-extrabold text-slate-900 text-sm leading-snug">{{ $item->product_name }}</p>
                  <div class="flex items-center gap-2 mt-1.5 flex-wrap text-xs">
                    <span class="font-mono text-slate-600">{{ money($item->unit_price) }} × {{ $item->quantity }}</span>
                    @if($item->variant)
                      <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                        {{ $item->variant }}
                      </span>
                    @else
                      <span class="px-2 py-0.5 text-[10px] rounded bg-slate-100 text-slate-400">No variation</span>
                    @endif
                    <button type="button" onclick="document.getElementById('edit-variant-{{ $item->id }}').classList.toggle('hidden')" class="text-xs text-brand-600 hover:underline font-bold cursor-pointer inline-flex items-center gap-1 ml-1">
                      ✏️ Edit Variation
                    </button>
                  </div>
                </div>
                <div class="font-black text-slate-900 font-mono text-sm shrink-0">{{ money($item->line_total) }}</div>
              </div>

              <!-- Inline Edit Variation Form -->
              <form id="edit-variant-{{ $item->id }}" method="POST" action="{{ route('admin.orders.items.update-variant', [$order, $item]) }}" class="hidden p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5">
                @csrf
                @method('PATCH')
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-extrabold text-slate-700">Update Item Variation (Size / Color / Specification)</label>
                  <button type="button" onclick="document.getElementById('edit-variant-{{ $item->id }}').classList.add('hidden')" class="text-xs text-slate-400 hover:text-slate-600">✕ Close</button>
                </div>

                @if($item->product && $item->product->variants->isNotEmpty())
                  @php
                    $variantsGrouped = $item->product->variants->groupBy('type');
                  @endphp
                  <div class="text-xs text-slate-500 bg-white p-2.5 rounded-xl border border-slate-200">
                    <span class="font-bold text-slate-700">Available product options:</span>
                    <div class="flex flex-wrap gap-2 mt-1">
                      @foreach($variantsGrouped as $type => $vars)
                        <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-[11px] text-slate-700">
                          <strong>{{ $type }}:</strong> {{ $vars->pluck('value')->join(', ') }}
                        </span>
                      @endforeach
                    </div>
                  </div>
                @endif

                <div class="flex items-center gap-2">
                  <input type="text" name="variant" value="{{ old('variant', $item->variant) }}" placeholder="e.g. Size: L, Color: Black" class="inp text-xs py-2 px-3 flex-1" />
                  <button type="submit" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition cursor-pointer">Save Variation</button>
                </div>
              </form>
            </div>
          @endforeach
        </div>

        <!-- Financial Totals Summary -->
        <div class="p-6 border-t border-slate-200 bg-slate-50/50 space-y-2 text-xs">
          <div class="flex justify-between text-slate-600">
            <span>Subtotal</span>
            <span class="font-bold text-slate-900 font-mono">{{ money($order->subtotal) }}</span>
          </div>

          @if($order->discount_amount > 0)
            <div class="flex justify-between text-brand-600 font-semibold">
              <span>Coupon Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
              <span class="font-bold font-mono">−{{ money($order->discount_amount) }}</span>
            </div>
          @endif

          <div class="flex justify-between text-slate-600">
            <span>Shipping Charge ({{ shipping_zone_label($order->shipping_zone) }})</span>
            <span class="font-bold text-slate-900 font-mono">{{ money($order->shipping_charge) }}</span>
          </div>

          @if($order->tax > 0)
            <div class="flex justify-between text-slate-600">
              <span>VAT / Tax</span>
              <span class="font-bold text-slate-900 font-mono">{{ money($order->tax) }}</span>
            </div>
          @endif

          <div class="flex justify-between items-center text-sm font-black text-slate-900 pt-3 border-t border-slate-200">
            <span>Total Payable Amount</span>
            <span class="text-base text-brand-600 font-mono">{{ money($order->total) }}</span>
          </div>
        </div>
      </div>

      <!-- Payment Verification Card -->
      <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
            <span>💳</span> Payment Statement Verification
          </h3>
          <span class="px-2.5 py-0.5 text-xs font-extrabold rounded-full {{ $order->paymentBadge() }}">
            {{ ucfirst($order->payment_status) }}
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
            <span class="text-slate-400 font-bold block text-[10px] uppercase">Payment Method</span>
            <p class="font-extrabold text-slate-900 text-sm mt-0.5">{{ $order->paymentMethodLabel() }}</p>
          </div>
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
            <span class="text-slate-400 font-bold block text-[10px] uppercase">Expected Amount</span>
            <p class="font-extrabold text-brand-600 font-mono text-sm mt-0.5">{{ money($order->total) }}</p>
          </div>
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
            <span class="text-slate-400 font-bold block text-[10px] uppercase">Sender Phone</span>
            <p class="font-mono font-bold text-slate-800 text-xs mt-0.5">{{ $order->payment_sender_number ?? 'N/A' }}</p>
          </div>
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
            <span class="text-slate-400 font-bold block text-[10px] uppercase">Transaction ID</span>
            <p class="font-mono font-black text-slate-900 text-xs mt-0.5 bg-white px-1.5 py-0.5 rounded border border-slate-200 inline-block">{{ $order->payment_txn_id ?? 'N/A' }}</p>
          </div>
        </div>

        @if($order->payment_status === 'pending')
          <div class="flex items-center gap-3 pt-2">
            <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline">
              @csrf
              <button type="submit" class="px-5 py-2.5 text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                ✓ Verify Payment
              </button>
            </form>
            <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline">
              @csrf
              <button type="submit" class="px-5 py-2.5 text-xs font-extrabold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 rounded-xl transition cursor-pointer">
                ✕ Reject Payment
              </button>
            </form>
          </div>
        @else
          <p class="text-xs text-slate-500 font-medium">
            Payment is currently marked as <b class="{{ $order->payment_status === 'verified' ? 'text-emerald-700' : 'text-rose-700' }}">{{ ucfirst($order->payment_status) }}</b>. Update status in the sidebar if needed.
          </p>
        @endif
      </div>

    </div>

    <!-- Right Column (Order Status Form, Courier, Customer & Fraud Analysis) -->
    <div class="space-y-6">
      
      <!-- Update Order Status Form -->
      <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="card p-5 space-y-4">
        @csrf @method('PATCH')
        <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-2">Update Order Details</h3>
        
        <div>
          <label class="lbl text-xs font-bold text-slate-700">Fulfillment Status</label>
          <select name="status" class="inp text-xs font-bold py-2 mt-1">
            @foreach(\App\Models\Order::STATUSES as $s)
              <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="lbl text-xs font-bold text-slate-700">Payment Status</label>
          <select name="payment_status" class="inp text-xs font-bold py-2 mt-1">
            @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
              <option value="{{ $s }}" @selected($order->payment_status === $s)>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="lbl text-xs font-bold text-slate-700">Internal Staff Note</label>
          <textarea name="internal_note" rows="3" class="inp text-xs mt-1" placeholder="Add private notes for staff…">{{ $order->internal_note }}</textarea>
        </div>

        <button type="submit" class="w-full btn-primary text-xs py-2.5 font-extrabold">Save Order Status</button>
      </form>

      <!-- Courier Integration Module -->
      <div class="card p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="font-extrabold text-sm flex items-center gap-1.5 text-slate-900">
            <span>🚚</span> Courier Integration
          </h3>
          @if($order->isDispatchedToCourier())
            <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Dispatched</span>
          @else
            <span class="px-2.5 py-0.5 text-[11px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-200">Not Dispatched</span>
          @endif
        </div>

        @if($order->isDispatchedToCourier())
          <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-gray-700">Courier Provider:</span>
              <span class="font-extrabold text-emerald-700">{{ $order->courierLabel() }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="font-bold text-gray-700">Consignment Code:</span>
              <span class="font-mono font-bold text-gray-900 bg-white px-2 py-0.5 rounded-lg border border-gray-200">{{ $order->courier_tracking_code }}</span>
            </div>
            @if($order->courier_sent_at)
              <div class="flex items-center justify-between text-[11px] text-gray-500">
                <span>Sent Date:</span>
                <span>{{ $order->courier_sent_at->format('d M Y, g:i A') }}</span>
              </div>
            @endif
            @if($order->courierTrackingUrl())
              <div class="pt-2 border-t border-emerald-200/60 text-center">
                <a href="{{ $order->courierTrackingUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-extrabold text-emerald-800 hover:underline">
                  <span>Track Live on {{ $order->courierLabel() }} Portal &rarr;</span>
                </a>
              </div>
            @endif
          </div>
        @else
          <p class="text-xs text-slate-500">Dispatch this parcel directly to courier API in 1-click:</p>
          
          <div class="space-y-2 pt-1">
            @php $anyConfigured = false; @endphp
            @foreach($couriers as $key => $info)
              @if($info['configured'])
                @php $anyConfigured = true; @endphp
                <form method="POST" action="{{ route('admin.orders.dispatch-courier', [$order, $key]) }}">
                  @csrf
                  <button type="submit" class="w-full py-2.5 px-3 text-xs font-extrabold rounded-2xl border border-slate-200 hover:border-brand-500 bg-white hover:bg-brand-50/50 text-slate-800 hover:text-brand-700 transition flex items-center justify-between cursor-pointer group">
                    <span class="flex items-center gap-2">
                      @if($key === 'steadfast') <span>📦</span>
                      @elseif($key === 'pathao') <span>🛵</span>
                      @else <span>🔴</span>
                      @endif
                      <span>Dispatch via {{ $info['name'] }}</span>
                    </span>
                    <span class="text-xs font-extrabold text-brand-600 group-hover:translate-x-0.5 transition-transform">Send &rarr;</span>
                  </button>
                </form>
              @endif
            @endforeach

            @if(! $anyConfigured)
              <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 space-y-1">
                <p class="font-extrabold">No Courier APIs Configured</p>
                <p class="text-[11px] leading-relaxed">Go to <a href="{{ route('admin.integrations.index') }}" class="underline font-bold text-brand-700">API Integrations</a> to enter your API Keys for Steadfast, Pathao, or RedX.</p>
              </div>
            @endif
          </div>
        @endif
      </div>

      <!-- Customer Address Card (Original Position) -->
      <div class="card p-5 space-y-2 text-xs">
        <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-1.5">
          <span>👤</span> Customer Details
        </h3>
        <div class="space-y-1 pt-1">
          <p class="text-sm font-extrabold text-slate-900">{{ $order->customer_name }}</p>
          <p class="font-mono text-slate-700 font-semibold">📞 {{ $order->customer_phone }}</p>
          @if($order->customer_email)
            <p class="text-slate-500">✉️ {{ $order->customer_email }}</p>
          @endif
          <div class="pt-2 border-t border-slate-100 mt-2">
            <span class="text-slate-400 font-bold block mb-0.5 text-[10px] uppercase">Delivery Address:</span>
            <p class="font-semibold text-slate-800 leading-snug">{{ $order->shipping_address }}, {{ $order->city }} {{ $order->postal_code }}</p>
          </div>
        </div>
      </div>

      <!-- Fraud & Risk Check Card (Placed directly after Customer Information) -->
      <div class="card p-5 space-y-3 border-l-4 {{ $order->fraudRiskLevel() === 'high' ? 'border-l-rose-500 bg-rose-50/30' : ($order->fraudRiskLevel() === 'medium' ? 'border-l-amber-500 bg-amber-50/30' : 'border-l-emerald-500') }}">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="font-extrabold text-sm flex items-center gap-1.5 text-slate-900">
            <span>🛡️</span> Fraud &amp; Risk Check
          </h3>
          <span class="px-2.5 py-0.5 text-xs font-extrabold rounded-full {{ $order->fraudBadgeClass() }}">
            @if($order->fraudRiskLevel() === 'high')
              🔴 High Risk ({{ $order->fraud_score }}%)
            @elseif($order->fraudRiskLevel() === 'medium')
              🟡 Medium Risk ({{ $order->fraud_score }}%)
            @else
              🟢 Low Risk ({{ $order->fraud_score }}%)
            @endif
          </span>
        </div>

        @if(!empty($order->fraud_flags))
          <div class="space-y-1.5 pt-1">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Triggered Risk Indicators:</span>
            @foreach($order->fraud_flags as $flag)
              <div class="p-2 rounded-xl bg-white border border-rose-200 text-xs font-semibold text-rose-800 flex items-start gap-1.5">
                <span class="shrink-0">🚩</span>
                <span>{{ $flag }}</span>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-xs text-emerald-700 font-medium">✓ No fraud risk indicators detected for this order.</p>
        @endif

        <!-- Quick Blacklist Action Buttons -->
        <div class="pt-2 border-t border-gray-100 flex items-center gap-2 flex-wrap text-xs">
          <form method="POST" action="{{ route('admin.blacklist.store') }}" class="inline">
            @csrf
            <input type="hidden" name="type" value="phone" />
            <input type="hidden" name="value" value="{{ $order->customer_phone }}" />
            <input type="hidden" name="reason" value="Blacklisted from Order #{{ $order->order_number }}" />
            <button type="submit" onclick="return confirm('Block phone {{ $order->customer_phone }} from placing future orders?')" class="px-2.5 py-1 text-xs font-extrabold rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-300 transition cursor-pointer">
              🚫 Block Phone
            </button>
          </form>

          @if($order->ip_address)
            <form method="POST" action="{{ route('admin.blacklist.store') }}" class="inline">
              @csrf
              <input type="hidden" name="type" value="ip" />
              <input type="hidden" name="value" value="{{ $order->ip_address }}" />
              <input type="hidden" name="reason" value="Blacklisted IP from Order #{{ $order->order_number }}" />
              <button type="submit" onclick="return confirm('Block IP {{ $order->ip_address }}?')" class="px-2.5 py-1 text-xs font-extrabold rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 transition cursor-pointer">
                🌐 Block IP
              </button>
            </form>
          @endif
        </div>
      </div>

      <!-- Danger Zone Card -->
      <div class="card p-5 border-rose-200 bg-rose-50/40 space-y-2 text-xs">
        <h3 class="font-extrabold text-rose-800">Danger Zone</h3>
        <p class="text-[11px] text-rose-600">Permanently delete this order record from your database.</p>
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Are you SURE you want to permanently delete order {{ $order->order_number }}? This action cannot be undone.')">
          @csrf
          @method('DELETE')
          <button type="submit" class="w-full py-2 px-3 text-xs font-extrabold rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition shadow-xs cursor-pointer">
            Delete Order {{ $order->order_number }}
          </button>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection
