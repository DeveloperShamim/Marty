@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
@php
  $digits = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
  $waPhone = str_starts_with($digits, '880') ? $digits : (str_starts_with($digits, '0') ? '88' . $digits : '880' . $digits);
  $isCod = $order->payment_method === 'cod';
  $qty = $order->items->sum('quantity');
  $steps = ['pending' => 'Placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
  $stepIndex = array_search($order->status, array_keys($steps), true);
  $head = 'flex items-center justify-between gap-2 px-4 sm:px-5 py-3 border-b border-slate-100';
  $title = 'flex items-center gap-2 text-sm font-semibold text-slate-900';
  $initials = collect(preg_split('/\s+/', trim((string) $order->customer_name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
  $menu = 'absolute right-0 z-30 mt-1.5 w-52 rounded-xl border border-slate-200 bg-white p-1 shadow-lg';
  $menuItem = 'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-left cursor-pointer';
@endphp
<style>
  .op details > summary { list-style: none; } .op details > summary::-webkit-details-marker { display: none; }
</style>

<div class="op max-w-6xl mx-auto space-y-4">

  {{-- ================= Header ================= --}}
  <section class="card overflow-hidden">
    <div class="p-4 sm:p-5 space-y-3">
      <div class="flex items-center justify-between gap-2">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900">
          <x-oi name="arrow-left" class="w-3.5 h-3.5" /> All orders
        </a>
        <details class="relative">
          <summary class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 cursor-pointer" title="More actions">
            <x-oi name="more" /><span class="sr-only">More actions</span>
          </summary>
          <div class="{{ $menu }}">
            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Are you SURE you want to permanently delete order {{ $order->order_number }}? This action cannot be undone.')">
              @csrf @method('DELETE')
              <button type="submit" class="{{ $menuItem }} text-rose-600 hover:bg-rose-50"><x-oi name="trash" /> Delete order</button>
            </form>
          </div>
        </details>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div class="min-w-0 space-y-1.5">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 font-mono tracking-tight">#{{ $order->order_number }}</h2>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->statusBadge() }}">{{ ucfirst($order->status) }}</span>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->paymentBadge() }}">Payment: {{ ucfirst($order->payment_status) }}</span>
          </div>
          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5" title="{{ $order->created_at->diffForHumans() }}"><x-oi name="calendar" class="w-3.5 h-3.5" />{{ $order->created_at->format('d M Y, g:i A') }}</span>
            <span class="inline-flex items-center gap-1.5"><x-oi name="{{ $isCod ? 'cash' : 'card' }}" class="w-3.5 h-3.5" />{{ $order->paymentMethodLabel() }}</span>
            <span class="inline-flex items-center gap-1.5" title="Source: {{ $order->utm_source ?? 'Direct' }}"><x-oi name="globe" class="w-3.5 h-3.5" />{{ $order->utm_source ? ucfirst($order->utm_source) : 'Direct' }}</span>
          </div>
        </div>
        <div class="sm:text-right shrink-0">
          <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Total · {{ $qty }} {{ \Illuminate\Support\Str::plural('item', $qty) }}</p>
          <p class="text-2xl font-bold text-slate-900 font-mono leading-tight">{{ money($order->total) }}</p>
        </div>
      </div>

      @if($order->auto_confirmed_reason)
        <p class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-2.5 py-1">
          <x-oi name="zap" class="w-3.5 h-3.5" /> Auto-confirmed: {{ $order->auto_confirmed_reason }}
        </p>
      @endif
    </div>

    {{-- Progress --}}
    <div class="px-4 sm:px-5 pb-4">
      @if($stepIndex !== false)
        <ol class="grid grid-cols-5">
          @foreach($steps as $key => $label)
            @php $i = $loop->index; $done = $i < $stepIndex; $current = $i === $stepIndex; @endphp
            <li class="relative flex flex-col items-center text-center">
              @if(! $loop->first)
                <span class="absolute top-3 right-1/2 w-full h-0.5 -z-0 {{ $i <= $stepIndex ? 'bg-brand-600' : 'bg-slate-200' }}" aria-hidden="true"></span>
              @endif
              <span class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold
                {{ $done ? 'bg-brand-600 text-white' : ($current ? 'bg-white text-brand-700 ring-2 ring-brand-600' : 'bg-white text-slate-400 ring-1 ring-slate-300') }}">
                @if($done)<x-oi name="check" class="w-3.5 h-3.5" />@else{{ $i + 1 }}@endif
              </span>
              <span class="mt-1.5 text-[10px] sm:text-xs {{ $current ? 'font-semibold text-slate-900' : 'text-slate-500' }}">{{ $label }}</span>
            </li>
          @endforeach
        </ol>
      @else
        <div class="flex items-center gap-2 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-3 py-2 text-sm text-rose-700">
          <x-oi name="{{ $order->status === 'returned' ? 'undo' : 'x-circle' }}" />
          <span>This order was <b>{{ $order->status }}</b>.</span>
        </div>
      @endif
    </div>

    {{-- Next step --}}
    @if($order->isAwaitingReview())
      <div class="mx-4 sm:mx-5 mb-4 rounded-xl bg-amber-50 ring-1 ring-amber-200 p-3 sm:p-4 flex flex-col md:flex-row md:items-center gap-3">
        <div class="flex items-start gap-2.5 min-w-0 flex-1">
          <span class="w-8 h-8 shrink-0 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><x-oi name="{{ $isCod ? 'phone' : 'card' }}" /></span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-amber-900">Next step: {{ $isCod ? 'confirm this order' : 'check the payment' }}</p>
            <p class="text-xs text-amber-800 mt-0.5 break-words">
              @if($isCod)
                Check the delivery history below; call if it looks risky, then confirm.
              @else
                Check that <b>{{ money($order->total) }}</b> arrived in {{ $order->paymentMethodLabel() }}@if($order->payment_sender_number) from <b class="font-mono">{{ $order->payment_sender_number }}</b>@endif @if($order->payment_txn_id)(TxID <b class="font-mono">{{ $order->payment_txn_id }}</b>)@endif, then verify.
              @endif
            </p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:flex gap-2 shrink-0">
          <form method="POST" action="{{ route('admin.orders.verify', $order) }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition cursor-pointer whitespace-nowrap"><x-oi name="check" /> {{ $order->acceptLabel() }}</button>
          </form>
          <form method="POST" action="{{ route('admin.orders.reject', $order) }}" onsubmit="return confirm('{{ $isCod ? 'Reject this order?' : 'Reject this payment?' }}')">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 text-sm font-semibold bg-white text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50 rounded-xl transition cursor-pointer whitespace-nowrap"><x-oi name="x" /> {{ $isCod ? 'Reject order' : 'Reject payment' }}</button>
          </form>
        </div>
      </div>
    @endif

    {{-- Print --}}
    <div class="flex flex-wrap items-center gap-2 px-4 sm:px-5 py-3 bg-slate-50/70 border-t border-slate-100">
      {{-- Invoice format is remembered per browser (admin-shell.js) and applied to every invoice link. --}}
      @php $short = ['a4' => 'A4', 'half' => 'Half A4', 'thermal' => '80mm', 'thermal58' => '58mm']; @endphp
      <div class="flex gap-2 w-full sm:w-auto">
        <div class="flex-1 sm:flex-none flex items-stretch rounded-lg ring-1 ring-slate-300 bg-white overflow-hidden min-w-0">
          <a href="{{ route('admin.orders.invoice', ['order' => $order, 'print' => 1]) }}" target="_blank" data-invoice-link data-print-link data-print-warning="{{ $order->printWarning('invoice') }}" class="flex-1 inline-flex items-center justify-center gap-1.5 pl-3 pr-2 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50 whitespace-nowrap">
            <x-oi name="printer" /> Invoice
          </a>
          <label class="shrink-0 flex items-center pr-2 border-l border-slate-200 bg-slate-50 hover:bg-slate-100 cursor-pointer" title="Invoice size">
            <span class="sr-only">Invoice size</span>
            <select id="invoiceFormat" data-invoice-format class="appearance-none bg-transparent border-0 pl-2.5 pr-1 py-2 text-xs font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600 cursor-pointer">
              @foreach(\App\Http\Controllers\Admin\OrderController::INVOICE_FORMATS as $key => $label)
                <option value="{{ $key }}" title="{{ $label }}">{{ $short[$key] ?? $label }}</option>
              @endforeach
            </select>
            <x-oi name="chevron-down" class="!w-3 !h-3 text-slate-400 pointer-events-none" />
          </label>
        </div>
        <a href="{{ route('admin.orders.labels', ['orders' => [$order->order_number], 'print' => 1]) }}" target="_blank" data-print-link data-print-warning="{{ $order->printWarning('label') }}" class="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-slate-800 bg-white ring-1 ring-slate-300 hover:bg-slate-50 rounded-lg whitespace-nowrap">
          <x-oi name="barcode" /> Label
        </a>
      </div>
      @if($order->prints()->exists())
        <div class="w-full sm:w-auto sm:ml-auto text-[11px] text-slate-500 sm:text-right">
          @foreach(['invoice' => 'Invoice', 'label' => 'Label'] as $type => $name)
            @php $p = $order->printsOf($type); @endphp
            @if($p->isNotEmpty())
              <p><span class="font-medium {{ $p->count() > 1 ? 'text-amber-700' : 'text-slate-700' }}">{{ $name }} printed {{ $p->count() }}×</span> · last {{ $p->first()->summary() }}</p>
            @endif
          @endforeach
        </div>
      @endif
    </div>
  </section>

  {{-- On phones the two columns merge (display: contents) and the cards follow the order you work in. --}}
  <div class="flex flex-col gap-4 lg:grid lg:grid-cols-3 lg:items-start">
    <div class="max-lg:contents lg:col-span-2 lg:space-y-4">

      {{-- ================= Customer ================= --}}
      <section class="card max-lg:order-1">
        <div class="{{ $head }}">
          <h3 class="{{ $title }}"><x-oi name="user" class="w-4 h-4 text-slate-400" /> Customer</h3>
          <div class="flex items-center gap-1.5">
            <button type="button" onclick="openEditCustomerModal()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 rounded-lg cursor-pointer"><x-oi name="pencil" class="w-3.5 h-3.5" /> Edit</button>
            <details class="relative">
              <summary class="w-8 h-8 rounded-lg ring-1 ring-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 cursor-pointer" title="Block customer"><x-oi name="more" /><span class="sr-only">More</span></summary>
              <div class="{{ $menu }}">
                <form method="POST" action="{{ route('admin.blacklist.store') }}">
                  @csrf
                  <input type="hidden" name="type" value="phone" />
                  <input type="hidden" name="value" value="{{ $order->customer_phone }}" />
                  <input type="hidden" name="reason" value="Blacklisted from Order #{{ $order->order_number }}" />
                  <button type="submit" onclick="return confirm('Block phone {{ $order->customer_phone }} from placing future orders?')" class="{{ $menuItem }} text-rose-600 hover:bg-rose-50"><x-oi name="ban" /> Block phone</button>
                </form>
                @if($order->ip_address)
                  <form method="POST" action="{{ route('admin.blacklist.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="ip" />
                    <input type="hidden" name="value" value="{{ $order->ip_address }}" />
                    <input type="hidden" name="reason" value="Blacklisted IP from Order #{{ $order->order_number }}" />
                    <button type="submit" onclick="return confirm('Block IP {{ $order->ip_address }}?')" class="{{ $menuItem }} text-amber-700 hover:bg-amber-50"><x-oi name="globe" /> Block IP {{ $order->ip_address }}</button>
                  </form>
                @endif
              </div>
            </details>
          </div>
        </div>

        <div class="p-4 sm:p-5 grid gap-4 md:grid-cols-2">
          <div class="min-w-0 space-y-3">
            <div class="flex items-center gap-3 min-w-0">
              <span class="w-10 h-10 shrink-0 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold">{{ $initials ?: '?' }}</span>
              <div class="min-w-0">
                <p class="font-semibold text-slate-900 break-words">{{ $order->customer_name }}</p>
                @if($order->customer_email)
                  <a href="mailto:{{ $order->customer_email }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-brand-700 break-all"><x-oi name="mail" class="w-3.5 h-3.5" />{{ $order->customer_email }}</a>
                @endif
              </div>
            </div>
            <div class="grid grid-cols-[1fr_auto] sm:flex gap-2">
              <a href="tel:{{ $order->customer_phone }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-700 text-white text-sm font-medium font-mono"><x-oi name="phone" class="w-3.5 h-3.5" /> {{ $order->customer_phone }}</a>
              <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg ring-1 ring-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium"><x-oi name="chat" class="w-3.5 h-3.5" /> WhatsApp</a>
            </div>
          </div>
          <div class="flex items-start gap-3 min-w-0 rounded-xl bg-slate-50 p-3">
            <x-oi name="pin" class="w-4 h-4 text-slate-400 mt-0.5" />
            <div class="min-w-0">
              <p class="text-sm text-slate-800 leading-snug break-words">{{ $order->shipping_address }}, {{ $order->city }} {{ $order->postal_code }}</p>
              <p class="text-xs text-slate-500 mt-1">{{ shipping_zone_label($order->shipping_zone) }}</p>
            </div>
          </div>
        </div>
        @include('admin.orders.partials.customer-history')
      </section>

      {{-- ================= Items, totals & payment ================= --}}
      <section class="card overflow-hidden max-lg:order-2">
        <div class="{{ $head }}">
          <h3 class="{{ $title }}"><x-oi name="bag" class="w-4 h-4 text-slate-400" /> Items ({{ $qty }})</h3>
        </div>
        <ul class="divide-y divide-slate-100">
          @foreach($order->items as $item)
            <li class="px-4 sm:px-5 py-3.5 space-y-3">
              <div class="flex items-start gap-3">
                <img src="{{ $item->imageUrl() }}" class="h-14 w-14 object-cover bg-slate-100 rounded-xl ring-1 ring-slate-200 shrink-0" alt="{{ $item->product_name }}">
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-medium text-slate-900 leading-snug break-words">{{ $item->product_name }}</p>
                    <p class="text-sm font-semibold text-slate-900 font-mono shrink-0">{{ money($item->line_total) }}</p>
                  </div>
                  <div class="flex items-center gap-2 mt-1 flex-wrap text-xs text-slate-500">
                    <span class="font-mono">{{ money($item->unit_price) }} × {{ $item->quantity }}</span>
                    @if($item->variant)
                      <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">{{ $item->variant }}</span>
                    @endif
                    <button type="button" onclick="document.getElementById('edit-variant-{{ $item->id }}').classList.toggle('hidden')" class="inline-flex items-center gap-1 text-brand-700 hover:underline cursor-pointer">
                      <x-oi name="pencil" class="w-3 h-3" /> {{ $item->variant ? 'Edit' : 'Add variation' }}
                    </button>
                  </div>
                </div>
              </div>
              <form id="edit-variant-{{ $item->id }}" method="POST" action="{{ route('admin.orders.items.update-variant', [$order, $item]) }}" class="hidden p-3 bg-slate-50 rounded-xl ring-1 ring-slate-200 space-y-2">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between gap-2">
                  <label for="variant-{{ $item->id }}" class="text-xs font-semibold text-slate-700">Variation (size / colour)</label>
                  <button type="button" onclick="document.getElementById('edit-variant-{{ $item->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer"><x-oi name="x" /><span class="sr-only">Close</span></button>
                </div>
                @if($item->product && $item->product->variants->isNotEmpty())
                  <div class="flex flex-wrap gap-1.5 text-[11px]">
                    @foreach($item->product->variants->groupBy('type') as $type => $vars)
                      <span class="bg-white ring-1 ring-slate-200 px-2 py-0.5 rounded text-slate-600"><b class="text-slate-800">{{ $type }}:</b> {{ $vars->pluck('value')->join(', ') }}</span>
                    @endforeach
                  </div>
                @endif
                <div class="flex flex-col sm:flex-row gap-2">
                  <input id="variant-{{ $item->id }}" type="text" name="variant" value="{{ old('variant', $item->variant) }}" placeholder="e.g. Size: L, Color: Black" class="inp text-sm py-2 px-3 flex-1" />
                  <button type="submit" class="btn-primary py-2 px-4">Save</button>
                </div>
              </form>
            </li>
          @endforeach
        </ul>

        <dl class="px-4 sm:px-5 py-3.5 border-t border-slate-100 space-y-1.5 text-sm">
          <div class="flex justify-between gap-3 text-slate-600"><dt>Subtotal</dt><dd class="font-mono text-slate-900">{{ money($order->subtotal) }}</dd></div>
          @if($order->discount_amount > 0)
            <div class="flex justify-between gap-3 text-emerald-700"><dt>Discount @if($order->coupon_code)<span class="font-mono text-xs">({{ $order->coupon_code }})</span>@endif</dt><dd class="font-mono">−{{ money($order->discount_amount) }}</dd></div>
          @endif
          <div class="flex justify-between gap-3 text-slate-600">
            <dt>Delivery</dt>
            @if($order->hasFreeDelivery())
              <dd class="font-mono"><s class="text-slate-400 mr-1">{{ money($order->shipping_waived) }}</s><span class="text-emerald-700 font-semibold">FREE</span></dd>
            @else
              <dd class="font-mono text-slate-900">{{ money($order->shipping_charge) }}</dd>
            @endif
          </div>
          @if($order->hasFreeDelivery())
            <div class="flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-900">
              <x-oi name="gift" class="w-3.5 h-3.5 mt-0.5" />
              <span><b>Free delivery</b>: {{ \App\Services\FreeDelivery::label($order->free_delivery_reason) ?? 'given at checkout' }}. You pay the courier {{ money($order->shipping_waived) }}; it is subtracted from profit.@if($order->free_delivery_reason === 'online_payment' && $order->payment_status !== 'verified') Only valid once the payment is verified.@endif</span>
            </div>
          @endif
          @if($order->tax > 0)
            <div class="flex justify-between gap-3 text-slate-600"><dt>VAT / Tax</dt><dd class="font-mono text-slate-900">{{ money($order->tax) }}</dd></div>
          @endif
          <div class="flex justify-between items-center gap-3 pt-2 mt-1 border-t border-slate-100">
            <dt class="font-semibold text-slate-900">Total to collect</dt>
            <dd class="text-lg font-bold text-slate-900 font-mono">{{ money($order->total) }}</dd>
          </div>
        </dl>

        {{-- Payment --}}
        <div class="px-4 sm:px-5 py-3.5 border-t border-slate-100 bg-slate-50/60 space-y-3">
          <div class="flex items-center justify-between gap-2">
            <p class="{{ $title }}"><x-oi name="{{ $isCod ? 'cash' : 'card' }}" class="w-4 h-4 text-slate-400" /> Payment</p>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->paymentBadge() }}">{{ ucfirst($order->payment_status) }}</span>
          </div>
          @php
            $showTxn = ! $isCod && ($order->payment_sender_number || $order->payment_txn_id || in_array($order->payment_method, ['bkash', 'nagad', 'rocket'], true));
          @endphp
          @if($isCod)
            <p class="text-sm text-slate-700">Cash on delivery: collect at the door · <b class="font-mono">{{ money($order->total) }}</b></p>
          @else
            <dl class="grid grid-cols-2 {{ $showTxn ? 'sm:grid-cols-4' : '' }} gap-x-4 gap-y-2 text-sm">
              <div class="min-w-0"><dt class="text-[11px] text-slate-400">Method</dt><dd class="font-medium text-slate-900 break-words">{{ $order->paymentMethodLabel() }}</dd></div>
              <div class="min-w-0"><dt class="text-[11px] text-slate-400">Amount</dt><dd class="font-medium text-slate-900 font-mono">{{ money($order->total) }}</dd></div>
              @if($showTxn)
                <div class="min-w-0"><dt class="text-[11px] text-slate-400">Sender phone</dt><dd class="font-medium text-slate-900 font-mono break-all">{{ $order->payment_sender_number ?? 'Not given' }}</dd></div>
                <div class="min-w-0"><dt class="text-[11px] text-slate-400">Transaction ID</dt><dd class="font-medium text-slate-900 font-mono break-all">{{ $order->payment_txn_id ?? 'Not given' }}</dd></div>
              @endif
            </dl>
          @endif
          <p class="text-xs text-slate-500">
            @if($order->isAwaitingReview())
              Waiting for you: use <b>{{ $order->acceptLabel() }}</b> or reject at the top of the page.
            @elseif($isCod && $order->payment_status === 'pending')
              Marked <b>Paid</b> automatically when the order is set to Delivered.
            @else
              Marked <b>{{ ucfirst($order->payment_status) }}</b>. Change it under Update order if needed.
            @endif
          </p>
          @if($order->canSwitchToCod())
            <form method="POST" action="{{ route('admin.orders.switch-to-cod', $order) }}"
                  onsubmit="return confirm('Money not received? The order becomes cash on delivery{{ $order->free_delivery_reason === 'online_payment' ? ' and the ' . money($order->shipping_waived) . ' delivery charge is added back' : '' }}.')">
              @csrf
              <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 rounded-lg transition cursor-pointer">
                <x-oi name="cash" class="w-3.5 h-3.5" /> Payment not received: switch to cash on delivery
              </button>
              @if($order->free_delivery_reason === 'online_payment')
                <span class="block text-xs text-slate-500 mt-1">Free delivery was for paying online, so {{ money($order->shipping_waived) }} delivery is added back.</span>
              @endif
            </form>
          @endif
        </div>
      </section>
    </div>

    <div class="max-lg:contents lg:space-y-4">

      {{-- ================= Update order ================= --}}
      <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="card max-lg:order-3">
        @csrf @method('PATCH')
        <div class="{{ $head }}">
          <h3 class="{{ $title }}"><x-oi name="clipboard" class="w-4 h-4 text-slate-400" /> Update order</h3>
        </div>
        <div class="p-4 sm:p-5 space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label for="orderFulfillmentStatusSelect" class="lbl">Order status</label>
              <select name="status" id="orderFulfillmentStatusSelect" class="inp text-sm py-2">
                @foreach(\App\Models\Order::STATUSES as $s)
                  <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="orderPaymentStatusSelect" class="lbl">Payment</label>
              <select name="payment_status" id="orderPaymentStatusSelect" class="inp text-sm py-2">
                @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                  <option value="{{ $s }}" @selected($order->payment_status === $s)>{{ ucfirst($s) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div>
            <label for="internalNote" class="lbl">Courier &amp; invoice note</label>
            <textarea id="internalNote" name="internal_note" rows="2" class="inp text-sm" placeholder="e.g. Call before delivery">{{ $order->internal_note }}</textarea>
            <p class="text-[11px] text-slate-500 mt-1">Printed on the invoice and sent to the courier. Private notes go in Calls &amp; staff notes.</p>
          </div>
          <button type="submit" class="w-full btn-primary py-2.5">Save changes</button>
        </div>
      </form>

      @include('admin.orders.partials.activity-log')

      {{-- ================= Courier ================= --}}
      <section class="card max-lg:order-5">
        <div class="{{ $head }}">
          <h3 class="{{ $title }}"><x-oi name="truck" class="w-4 h-4 text-slate-400" /> Courier</h3>
          <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->isDispatchedToCourier() ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-600' }}">{{ $order->isDispatchedToCourier() ? 'Sent' : 'Not sent' }}</span>
        </div>
        <div class="p-4 sm:p-5 space-y-3 text-sm">
          @if($order->isDispatchedToCourier())
            <dl class="space-y-2">
              <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Courier</dt><dd class="font-medium text-slate-900 text-right">{{ $order->courierLabel() }}</dd></div>
              <div class="flex items-center justify-between gap-3"><dt class="text-slate-500 shrink-0">Tracking</dt><dd class="font-mono text-slate-900 text-right break-all">{{ $order->courier_tracking_code ?: 'Scan Station' }}</dd></div>
              @if($order->courier_sent_at)
                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Sent</dt><dd class="text-slate-700 text-right">{{ $order->courier_sent_at->format('d M, g:i A') }}</dd></div>
              @endif
            </dl>
            @if($order->courier_tracking_code && in_array($order->courier_name, \App\Services\Courier\CourierStatusUpdater::PROVIDERS, true))
              @php
                $cs = $order->courier_status;
                $csTone = match (true) {
                    $cs === 'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                    in_array($cs, \App\Services\Courier\CourierStatusUpdater::ATTENTION, true) => 'bg-amber-50 text-amber-800 ring-amber-200',
                    default => 'bg-slate-50 text-slate-700 ring-slate-200',
                };
              @endphp
              <div class="pt-3 border-t border-slate-100 space-y-1.5">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-slate-500">Courier says</span>
                  <span class="px-2 py-0.5 rounded-full ring-1 text-xs font-medium {{ $csTone }}">{{ \App\Services\Courier\CourierStatusUpdater::label($cs) ?? 'Not checked yet' }}</span>
                </div>
                @if($order->courier_status_message)<p class="text-xs text-slate-600">{{ $order->courier_status_message }}</p>@endif
                <div class="flex items-center justify-between gap-2 text-xs text-slate-500">
                  <span>{{ $order->courier_synced_at ? 'Checked ' . $order->courier_synced_at->diffForHumans() : 'Updates daily at 9 PM' }}</span>
                  <form method="POST" action="{{ route('admin.orders.courier-status', $order) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 font-medium text-brand-700 hover:underline cursor-pointer"><x-oi name="refresh" class="w-3 h-3" /> Refresh</button>
                  </form>
                </div>
                @if(in_array($cs, ['returning', 'returned', 'cancelled'], true) && $order->status === 'shipped')
                  <p class="text-xs text-amber-900 bg-amber-50 rounded-lg px-2.5 py-2">
                    The parcel is coming back. When it arrives, scan it in at <a href="{{ route('admin.courier-scan.index') }}" class="underline">Courier Scan → Courier IN</a> to restock and record the courier charge.
                  </p>
                @endif
              </div>
            @endif
            @if($order->courierTrackingUrl())
              <a href="{{ $order->courierTrackingUrl() }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg ring-1 ring-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Track on {{ $order->courierLabel() }} <x-oi name="external" class="w-3.5 h-3.5" />
              </a>
            @endif
          @else
            @php $anyConfigured = collect($couriers)->contains('configured', true); @endphp
            @if($anyConfigured)
              <p class="text-xs text-slate-500">Send this parcel in one click:</p>
              <div class="space-y-2">
                @foreach($couriers as $key => $info)
                  @if($info['configured'])
                    <form method="POST" action="{{ route('admin.orders.dispatch-courier', [$order, $key]) }}">
                      @csrf
                      <button type="submit" class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg ring-1 ring-slate-200 hover:ring-brand-600 hover:bg-brand-50/40 text-sm font-medium text-slate-800 transition cursor-pointer group">
                        <span class="inline-flex items-center gap-2"><x-oi name="package" class="w-4 h-4 text-slate-400" /><span>{{ $info['name'] }}</span></span>
                        <span class="text-brand-700 text-xs group-hover:translate-x-0.5 transition-transform">Send →</span>
                      </button>
                    </form>
                  @endif
                @endforeach
              </div>
            @else
              <p class="text-xs text-slate-500">No courier connected. Add Steadfast, Pathao or RedX keys in <a href="{{ route('admin.integrations.index') }}" class="text-brand-700 underline">Integrations</a>.</p>
            @endif
          @endif
        </div>
      </section>

      {{-- ================= Return ================= --}}
      @if($order->status === 'returned' || $order->courier_returned_at)
        <section class="card max-lg:order-6">
          <div class="{{ $head }}">
            <h3 class="{{ $title }}"><x-oi name="undo" class="w-4 h-4 text-slate-400" /> Return</h3>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->return_restocked ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-800 ring-1 ring-amber-200' }}">{{ $order->return_restocked ? 'Restocked' : 'Not restocked' }}</span>
          </div>
          <dl class="p-4 sm:p-5 space-y-2 text-sm">
            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Returned</dt><dd class="text-slate-900 text-right">{{ $order->courier_returned_at ? $order->courier_returned_at->format('d M Y, g:i A') : 'Recorded' }}</dd></div>
            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Delivery charge</dt><dd class="font-medium text-right {{ $order->return_type === 'paid_delivery' ? 'text-emerald-700' : 'text-rose-600' }}">{{ $order->return_type === 'paid_delivery' ? 'Paid by buyer' : 'Not paid (your loss)' }}</dd></div>
            @if($order->courier_loss_amount > 0)
              <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Courier loss</dt><dd class="font-mono text-rose-600">−{{ money($order->courier_loss_amount) }}</dd></div>
            @endif
            @if($order->return_reason)
              <div class="pt-2 border-t border-slate-100"><dt class="text-xs text-slate-500">Reason</dt><dd class="text-slate-800 mt-0.5">“{{ $order->return_reason }}”</dd></div>
            @endif
          </dl>
        </section>
      @endif
    </div>
  </div>
</div>

<!-- Edit Customer Details Modal -->
<div id="editCustomerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-xs">
  <div class="bg-white rounded-3xl shadow-2xl border border-stone-200 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
    <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 bg-stone-50/70">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-base border border-brand-100 shadow-2xs">
          👤
        </div>
        <div>
          <h3 class="font-black text-stone-900 text-sm sm:text-base">Edit Customer Details</h3>
          <p class="text-[11px] text-stone-500 font-medium">Update recipient contact and delivery location</p>
        </div>
      </div>
      <button type="button" onclick="closeEditCustomerModal()" class="w-8 h-8 rounded-xl hover:bg-stone-200/60 flex items-center justify-center text-stone-400 hover:text-stone-700 font-bold transition cursor-pointer">
        ✕
      </button>
    </div>

    <form method="POST" action="{{ route('admin.orders.update-customer', $order) }}" class="p-6 space-y-4">
      @csrf
      @method('PATCH')

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-[11px] font-black text-stone-700 mb-1">Recipient Name <span class="text-rose-500">*</span></label>
          <input type="text" name="customer_name" required value="{{ old('customer_name', $order->customer_name) }}" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="Full name" />
          @error('customer_name')
            <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-[11px] font-black text-stone-700 mb-1">Phone Number <span class="text-rose-500">*</span></label>
          <input type="text" name="customer_phone" required value="{{ old('customer_phone', $order->customer_phone) }}" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="01XXXXXXXXX" />
          @error('customer_phone')
            <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div>
        <label class="block text-[11px] font-black text-stone-700 mb-1">Email Address <span class="text-stone-400 font-normal">(Optional)</span></label>
        <input type="email" name="customer_email" value="{{ old('customer_email', $order->customer_email) }}" class="w-full text-xs font-medium px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="customer@example.com" />
        @error('customer_email')
          <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block text-[11px] font-black text-stone-700 mb-1">Delivery Address <span class="text-rose-500">*</span></label>
        <textarea name="shipping_address" required rows="2" class="w-full text-xs font-semibold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="House, Road, Area, Landmark">{{ old('shipping_address', $order->shipping_address) }}</textarea>
        @error('shipping_address')
          <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        <div>
          <label class="block text-[11px] font-black text-stone-700 mb-1">City / District <span class="text-rose-500">*</span></label>
          <input type="text" name="city" required value="{{ old('city', $order->city) }}" class="w-full text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="e.g. Dhaka, Chittagong" />
          @error('city')
            <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block text-[11px] font-black text-stone-700 mb-1">Postal Code <span class="text-stone-400 font-normal">(Optional)</span></label>
          <input type="text" name="postal_code" value="{{ old('postal_code', $order->postal_code) }}" class="w-full text-xs font-mono font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" placeholder="e.g. 1212" />
          @error('postal_code')
            <p class="text-rose-600 text-[10px] font-bold mt-1">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="flex items-center justify-end gap-2.5 pt-3.5 border-t border-stone-100">
        <button type="button" onclick="closeEditCustomerModal()" class="px-4 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-extrabold text-xs hover:bg-stone-50 transition cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-black text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
          <span>💾</span> Save Customer Details
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditCustomerModal() {
    const m = document.getElementById('editCustomerModal');
    if (m) m.classList.remove('hidden');
  }

  function closeEditCustomerModal() {
    const m = document.getElementById('editCustomerModal');
    if (m) m.classList.add('hidden');
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeEditCustomerModal();
  });
  document.getElementById('editCustomerModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'editCustomerModal') closeEditCustomerModal();
  });

  // Auto-sync payment status when fulfillment status changes
  const fulfillmentSelect = document.getElementById('orderFulfillmentStatusSelect');
  const paymentSelect = document.getElementById('orderPaymentStatusSelect');
  if (fulfillmentSelect && paymentSelect) {
    fulfillmentSelect.addEventListener('change', function() {
      if (this.value === 'returned' || this.value === 'cancelled') {
        paymentSelect.value = 'rejected';
      } else if (this.value === 'delivered') {
        paymentSelect.value = 'verified';
      }
    });
  }
</script>

@if($errors->hasAny(['customer_name', 'customer_phone', 'customer_email', 'shipping_address', 'city', 'postal_code']))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    openEditCustomerModal();
  });
</script>
@endif
@endsection
