{{-- "Invoice 2×" / "Label" chips showing what has already been printed for an order. --}}
@foreach(['invoice' => 'Invoice', 'label' => 'Label'] as $type => $name)
  @php $prints = $order->printsOf($type); @endphp
  @if($prints->isNotEmpty())
    <span class="inline-flex items-center gap-0.5 px-1.5 py-px rounded-md text-[10px] font-semibold {{ $prints->count() > 1 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}"
          title="{{ $name }} printed {{ $prints->count() }}× · last: {{ $prints->first()->summary() }}">
      <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
      {{ $name }}@if($prints->count() > 1) {{ $prints->count() }}×@endif
    </span>
  @endif
@endforeach
