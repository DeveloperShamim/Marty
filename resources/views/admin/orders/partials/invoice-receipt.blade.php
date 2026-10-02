{{-- Thermal roll receipt. Each receipt is its own named page, sized to its height by the page script. --}}
<div class="rcpt" data-receipt style="page: rcpt{{ $index }}">
  <div class="center">
    @if(has_custom_logo())
      <img src="{{ logo_url() }}" alt="{{ $inv['store'] }}" class="rcpt-logo"><br>
    @else
      <div class="rcpt-store">{{ $inv['store'] }}</div>
    @endif
    @if($inv['address'])<div class="rcpt-small">{{ $inv['address'] }}</div>@endif
    @if($inv['phone'])<div class="rcpt-small">{{ $inv['phone'] }}</div>@endif
    @if($inv['vat'])<div class="rcpt-small">VAT / BIN: {{ $inv['vat'] }}</div>@endif
  </div>
  <hr>
  <div class="rcpt-row"><b>Invoice</b><b>#{{ $inv['number'] }}</b></div>
  <div class="rcpt-row rcpt-small"><span>Date</span><span>{{ $order->created_at->format('d/m/Y g:i A') }}</span></div>
  <hr>
  <div><b>{{ $order->customer_name }}</b></div>
  <div>{{ $order->customer_phone }}</div>
  @if($order->shipping_address)<div class="rcpt-small">{{ $order->shipping_address }}@if($order->city), {{ $order->city }}@endif</div>@endif
  <hr>
  @foreach($order->items as $item)
    <div class="rcpt-item">
      <img src="{{ $item->imageUrl() }}" alt="" class="rcpt-thumb" onerror="this.remove()">
      <div class="rcpt-item-body">
        <div class="name">{{ $item->product_name }}@if($item->variant) ({{ $item->variant }})@endif</div>
        <div class="rcpt-row"><span>{{ $item->quantity }} &times; {{ money($item->unit_price) }}</span><span>{{ money($item->line_total) }}</span></div>
      </div>
    </div>
  @endforeach
  <hr>
  <div class="rcpt-row"><span>Subtotal ({{ $order->items->sum('quantity') }} {{ Str::plural('item', (int) $order->items->sum('quantity')) }})</span><span>{{ money($order->subtotal) }}</span></div>
  @if($order->discount_amount > 0)
    <div class="rcpt-row"><span>Discount</span><span>−{{ money($order->discount_amount) }}</span></div>
  @endif
  <div class="rcpt-row"><span>Delivery</span><span>{{ $order->deliveryDisplay() }}</span></div>
  @if($order->tax > 0)
    <div class="rcpt-row"><span>VAT / Tax</span><span>{{ money($order->tax) }}</span></div>
  @endif
  <hr>
  <div class="rcpt-row rcpt-total"><span>TOTAL</span><span>{{ money($order->total) }}</span></div>
  @if($order->order_type === 'pos' && $order->pos_cash_tendered > 0)
    <div class="rcpt-row"><span>Cash received</span><span>{{ money($order->pos_cash_tendered) }}</span></div>
    <div class="rcpt-row"><span>Change</span><span>{{ money($order->pos_change_amount) }}</span></div>
  @endif
  <div class="rcpt-row rcpt-small"><span>{{ $order->paymentMethodLabel() }}</span><span>{{ $inv['paymentStatus'] }}</span></div>
  @if($inv['due'] > 0)
    <div class="rcpt-row rcpt-due"><span>DUE (COD)</span><span>{{ money($inv['due']) }}</span></div>
  @endif
  <svg class="rcpt-barcode" jsbarcode-value="{{ $order->order_number }}" role="img" aria-label="Barcode {{ $order->order_number }}"></svg>
  <hr>
  <div class="center">
    @if(setting('invoice_terms'))
      <div class="rcpt-terms">{{ setting('invoice_terms') }}</div>
    @else
      <b>Thank you for shopping with us!</b>
    @endif
    <div class="rcpt-small" style="margin-top:1.5mm">Printed {{ now()->format('d/m/Y g:i A') }}</div>
  </div>
</div>
