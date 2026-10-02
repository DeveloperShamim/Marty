{{-- One invoice on paper: the full A4 page, or one half of an A4 sheet ($half). --}}
<article class="inv {{ $half ? 'inv--half' : '' }}">
  <header class="inv-head">
    <div class="inv-from">
      @if(has_custom_logo())
        <img src="{{ logo_url() }}" alt="{{ $inv['store'] }}" class="inv-logo">
      @else
        <div class="inv-store">{{ $inv['store'] }}</div>
      @endif
      <div class="inv-muted">
        @if($inv['address'])<div>{{ $inv['address'] }}</div>@endif
        <div>
          {{ collect([$inv['phone'], $inv['email']])->filter()->implode('  ·  ') }}
        </div>
        @if($inv['vat'])<div>VAT / BIN: {{ $inv['vat'] }}</div>@endif
      </div>
    </div>
    <div class="inv-meta">
      @if($copy)<div class="inv-copy">{{ $copy }}</div>@endif
      <div class="inv-title">Invoice</div>
      <div class="inv-no">#{{ $inv['number'] }}</div>
      <div class="inv-muted">{{ $order->created_at->format('d M Y, g:i A') }}</div>
      <svg class="inv-barcode" jsbarcode-value="{{ $order->order_number }}" role="img" aria-label="Barcode {{ $order->order_number }}"></svg>
    </div>
  </header>

  <section class="inv-parties">
    <div>
      <div class="inv-label">Bill &amp; ship to</div>
      <div class="inv-strong">{{ $order->customer_name }}</div>
      <div>{{ $order->customer_phone }}@if($order->customer_email && ! $half) · {{ $order->customer_email }}@endif</div>
      <div class="inv-addr">{{ $order->shipping_address }}@if($order->city), {{ $order->city }}@endif {{ $order->postal_code }}</div>
    </div>
    <div>
      <div class="inv-label">Payment &amp; delivery</div>
      <div class="inv-kv"><span>Payment</span><b>{{ $order->paymentMethodLabel() }}</b></div>
      <div class="inv-kv"><span>Status</span><b>{{ $inv['paymentStatus'] }}</b></div>
      @if($order->isMobileBanking() && $order->payment_txn_id)
        <div class="inv-kv"><span>TrxID</span><b class="mono">{{ $order->payment_txn_id }}</b></div>
      @endif
      @if($order->shipping_zone)
        <div class="inv-kv"><span>Delivery</span><b>{{ shipping_zone_label($order->shipping_zone) }}</b></div>
      @endif
      @if($order->courier_name)
        <div class="inv-kv"><span>Courier</span><b>{{ $order->courierLabel() }}@if($order->courier_tracking_code) · {{ $order->courier_tracking_code }}@endif</b></div>
      @endif
    </div>
  </section>

  <table class="inv-items">
    <thead>
      <tr>
        <th class="c-n">#</th>
        <th>Item</th>
        <th class="c-r">Price</th>
        <th class="c-c">Qty</th>
        <th class="c-r">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $i => $item)
        <tr class="inv-row">
          <td class="c-n">{{ $i + 1 }}</td>
          <td>
            <div class="inv-item">
              <img src="{{ $item->imageUrl() }}" alt="" class="inv-thumb" onerror="this.remove()">
              <div>
                <div class="inv-strong">{{ $item->product_name }}</div>
                @if($item->variant)<div class="inv-muted">{{ $item->variant }}</div>@endif
              </div>
            </div>
          </td>
          <td class="c-r mono">{{ money($item->unit_price) }}</td>
          <td class="c-c">{{ $item->quantity }}</td>
          <td class="c-r mono">{{ money($item->line_total) }}</td>
        </tr>
      @endforeach
      @if($half)
        {{-- Filled in by the page script when not every item fits on half a page. --}}
        <tr class="inv-more" hidden><td></td><td colspan="4" class="inv-muted"></td></tr>
      @endif
    </tbody>
  </table>

  {{-- Totals and signature stay together, so the signature never ends up alone on a page. --}}
  <div class="inv-end">
  <section class="inv-bottom">
    <div class="inv-notes">
      @if($order->internal_note && ! $half)
        <div class="inv-label">Note</div>
        <p>{{ $order->internal_note }}</p>
      @endif
      @if(setting('invoice_terms'))
        <div class="inv-label">Terms &amp; exchange policy</div>
        <p class="inv-terms">{{ setting('invoice_terms') }}</p>
      @else
        <p class="inv-strong">Thank you for shopping with us!</p>
        <p class="inv-muted">Questions about your order? Call {{ $inv['phone'] ?: 'our helpline' }}.</p>
      @endif
    </div>
    <div class="inv-totals">
      <div class="inv-kv"><span>Subtotal</span><b class="mono">{{ money($order->subtotal) }}</b></div>
      @if($order->discount_amount > 0)
        <div class="inv-kv"><span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span><b class="mono">−{{ money($order->discount_amount) }}</b></div>
      @endif
      <div class="inv-kv"><span>Delivery</span><b class="mono">{{ $order->deliveryDisplay() }}</b></div>
      @if($order->tax > 0)
        <div class="inv-kv"><span>VAT / Tax</span><b class="mono">{{ money($order->tax) }}</b></div>
      @endif
      <div class="inv-kv inv-total"><span>Total</span><b class="mono">{{ money($order->total) }}</b></div>
      @if($inv['due'] > 0)
        <div class="inv-due"><span>Amount due (cash on delivery)</span><b class="mono">{{ money($inv['due']) }}</b></div>
      @elseif($order->payment_status === 'verified')
        <div class="inv-paid">Paid</div>
      @endif
    </div>
  </section>

  <footer class="inv-foot">
    <div>
      <div class="inv-sign"></div>
      Authorised signature
    </div>
    <div class="c-r">Printed {{ now()->format('d M Y, g:i A') }}</div>
  </footer>
  </div>
</article>
