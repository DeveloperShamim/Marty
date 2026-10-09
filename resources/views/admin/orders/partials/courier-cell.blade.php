{{-- Courier name, tracking code and the last status the courier reported (refreshed nightly by couriers:sync). --}}
@php
  $tracked = in_array(strtolower((string) $order->courier_name), \App\Services\Courier\CourierStatusUpdater::PROVIDERS, true) && $order->courier_tracking_code;
  $trackUrl = $order->courierTrackingUrl();
@endphp
@if($order->isPos())
  <span class="text-[11px] text-gray-400">In-store sale</span>
@elseif(! $order->isDispatchedToCourier())
  <span class="text-[11px] text-gray-400">{{ in_array($order->status, ['cancelled', 'returned'], true) ? '—' : 'Not sent yet' }}</span>
@else
  <div class="min-w-0 space-y-1">
    <p class="flex items-center gap-1.5 text-xs font-semibold text-gray-900">
      {{ $order->courierLabel() }}
      @if($order->courier_tracking_code)
        @if($trackUrl)
          <a href="{{ $trackUrl }}" target="_blank" rel="noopener" class="font-mono font-medium text-[11px] text-gray-500 hover:text-gray-900 underline decoration-dotted underline-offset-2" title="Track on the courier's site">{{ $order->courier_tracking_code }}</a>
        @else
          <span class="font-mono font-medium text-[11px] text-gray-500">{{ $order->courier_tracking_code }}</span>
        @endif
      @endif
    </p>
    @if($order->courier_status)
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $order->courierStatusBadge() }}" @if($order->courier_status_message) title="{{ $order->courier_status_message }}" @endif>
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>{{ \App\Services\Courier\CourierStatusUpdater::label($order->courier_status) }}
      </span>
    @elseif($tracked)
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">Waiting for first check</span>
    @endif
    @if($order->courier_status_message && $order->courier_status && $order->courier_status !== 'delivered')
      <p class="text-[11px] text-gray-500 max-w-[220px] truncate" title="{{ $order->courier_status_message }}">{{ $order->courier_status_message }}</p>
    @endif
    @if($order->courier_synced_at)
      <p class="text-[10.5px] text-gray-400" title="{{ $order->courier_synced_at->timezone(config('app.timezone'))->format('d M Y, g:i A') }}">Checked {{ $order->courier_synced_at->diffForHumans() }}</p>
    @elseif(! $tracked)
      <p class="text-[10.5px] text-gray-400">Tracked by hand</p>
    @endif
  </div>
@endif
