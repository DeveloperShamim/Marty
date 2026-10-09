@extends('layouts.admin')
@section('title', 'Flash sale')
@php
  $endsLocal = !empty($endsAt)
    ? \Illuminate\Support\Str::of($endsAt)->replace(' ', 'T')->substr(0, 16)
    : '';
  $hasActiveCountdown = !empty($endsAt) && \Illuminate\Support\Carbon::parse($endsAt)->isFuture();
@endphp
@section('subtitle', 'Homepage flash deals, the countdown clock, display order and stock bars.')

@section('page-actions')
  <a href="{{ route('home') }}" target="_blank" class="pill-btn">
    View store
    <span class="pill-ico"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
  </a>
@endsection

@section('content')
<div class="space-y-4 max-w-full">

  <div id="flashReorderFeedback" class="hidden rounded-xl text-[13px] font-medium px-3.5 py-2.5"></div>

  {{-- Countdown --}}
  <form method="POST" action="{{ route('admin.flash-sale.ends-at') }}" class="panel p-4 sm:p-5 flex flex-col gap-3">
    @csrf
    @method('PUT')

    <div class="flex items-start justify-between gap-3">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Countdown</h2>
        <p class="text-xs text-gray-500 mt-0.5">While this time is in the future, the homepage shows Flash deals with a live clock. Leave it blank or let it pass, and the homepage shows Our picks (products ticked Featured) instead.</p>
      </div>
      <div class="flex items-center gap-1.5 shrink-0">
        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 tabular-nums">{{ $flashProducts->count() }} deals</span>
        @if($hasActiveCountdown)
          <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">Running</span>
        @else
          <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">Not running</span>
        @endif
      </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-end gap-2.5">
      <div class="flex-1 min-w-0">
        <label for="flashSaleEndsAtInput" class="lbl">Ends at</label>
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
          <input type="datetime-local" id="flashSaleEndsAtInput" name="flash_sale_ends_at" class="inp sm:w-72" value="{{ old('flash_sale_ends_at', $endsLocal) }}" />
          <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" onclick="setTimerHours(24)" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium cursor-pointer">+24 hours</button>
            <button type="button" onclick="setTimerHours(72)" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium cursor-pointer">+3 days</button>
            <button type="button" onclick="setTimerHours(168)" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium cursor-pointer">+7 days</button>
            <button type="button" onclick="clearTimer()" class="h-8 px-3 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium cursor-pointer">Clear</button>
          </div>
        </div>
      </div>
      <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold shrink-0 cursor-pointer" style="background: var(--brand-dark);">Save schedule</button>
    </div>
  </form>

  {{-- Active deals --}}
  <section class="panel overflow-hidden">
    <div class="p-4 sm:p-5">
      <h2 class="text-[15px] font-semibold text-gray-900">Active deals <span class="text-gray-400 font-normal tabular-nums">({{ $flashProducts->count() }})</span></h2>
      <p class="text-xs text-gray-500 mt-0.5">Drag the handle to reorder. Set the stock bar % (0–100) shown on the store.</p>
    </div>

    @if($flashProducts->isEmpty())
      <div class="px-4 pb-10 pt-4 text-center text-gray-500 text-sm">
        No products in the flash sale yet. Add deals from the list below.
      </div>
    @else

      {{-- Phone: drag-and-drop cards --}}
      <div id="flashSaleListMobile" class="md:hidden px-3 pb-3 space-y-2.5">
        @foreach($flashProducts as $i => $product)
          <div class="flash-card-item relative rounded-2xl bg-gray-50/80 p-3.5 space-y-3" data-id="{{ $product->id }}">
            <div class="flex items-center gap-2.5">
              <button type="button" class="flash-drag-handle inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-500 cursor-grab active:cursor-grabbing shrink-0" title="Drag to reorder">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
              </button>
              <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-xl bg-white shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                  <span class="flash-pos text-[11px] font-semibold text-gray-500 tabular-nums">#{{ $i + 1 }}</span>
                  <h3 class="font-semibold text-gray-900 text-[13px] leading-tight truncate">{{ $product->name }}</h3>
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5 truncate">{{ $product->category?->name }} · {{ $product->sku }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 rounded-xl bg-white p-2.5 text-xs">
              <div>
                <span class="text-[11px] text-gray-500 block">Deal price</span>
                <span class="font-semibold text-gray-900 tabular-nums">{{ money($product->price) }}</span>
                @if($product->on_sale)
                  <span class="text-[11px] text-gray-400 line-through ml-1 tabular-nums">{{ money($product->regular_price) }}</span>
                @endif
              </div>
              <div>
                <span class="text-[11px] text-gray-500 block">Stock</span>
                <span class="font-medium text-gray-800 tabular-nums">{{ $product->stock_quantity }} left · {{ $product->sold_units }} sold</span>
              </div>
            </div>

            <div class="space-y-1.5">
              <div class="flex items-center justify-between gap-2 text-xs">
                <div>
                  <label class="text-xs font-medium text-gray-700 block">Stock bar</label>
                  <span class="text-[11px] text-gray-500">Auto: {{ $product->calculatedFlashSaleProgress() }}% claimed</span>
                </div>
                <div class="flex items-center gap-1">
                  <input
                    type="number"
                    min="0"
                    max="100"
                    step="1"
                    value="{{ (int) ($product->flash_sale_progress ?? 50) }}"
                    class="flash-progress-input w-16 h-8 border border-gray-200 rounded-lg px-2 text-xs font-medium text-center bg-white focus:outline-none focus:border-gray-400 tabular-nums"
                    data-progress-url="{{ route('admin.flash-sale.progress', $product) }}"
                  />
                  <span class="text-xs text-gray-400">%</span>
                </div>
              </div>
              <div class="h-1.5 w-full rounded-full bg-gray-200 overflow-hidden">
                <div class="flash-progress-bar h-full rounded-full bg-amber-500 transition-all duration-300" style="width: {{ (int) ($product->flash_sale_progress ?? 50) }}%"></div>
              </div>
            </div>

            <div class="flex items-center gap-1.5">
              <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 h-9 rounded-full bg-white hover:bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center justify-center">Edit product</a>
              <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                @csrf @method('DELETE')
                <button type="submit" class="h-9 px-4 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium cursor-pointer">Remove</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Desktop: drag-and-drop table --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-[13px] border-collapse">
          <thead>
            <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
              <th class="py-3 pl-4 pr-1 w-10"><span class="sr-only">Drag</span></th>
              <th class="py-3 px-2 w-10">#</th>
              <th class="py-3 px-4">Product</th>
              <th class="py-3 px-4">Deal price</th>
              <th class="py-3 px-4">Stock</th>
              <th class="py-3 px-4 w-56">Stock bar</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="flashSaleList" class="divide-y divide-gray-100">
            @foreach($flashProducts as $i => $product)
              <tr class="hover:bg-gray-50/70 transition-colors" data-id="{{ $product->id }}">
                <td class="py-3 pl-4 pr-1">
                  <button type="button" class="flash-drag-handle inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 cursor-grab active:cursor-grabbing" title="Drag to reorder">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                  </button>
                </td>
                <td class="py-3 px-2">
                  <span class="flash-pos text-xs font-semibold text-gray-500 tabular-nums">#{{ $i + 1 }}</span>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl bg-gray-100 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                    <div class="min-w-0">
                      <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                      <p class="text-[11px] text-gray-500 mt-0.5">{{ $product->category?->name }} · {{ $product->sku }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="font-semibold text-gray-900 tabular-nums">{{ money($product->price) }}</span>
                  @if($product->on_sale)
                    <span class="block text-[11px] text-gray-400 line-through tabular-nums">{{ money($product->regular_price) }}</span>
                  @endif
                </td>
                <td class="py-3 px-4 whitespace-nowrap tabular-nums">
                  <div class="font-medium text-gray-800">{{ $product->stock_quantity }} left</div>
                  <div class="text-[11px] text-gray-500">{{ $product->sold_units }} sold</div>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-1.5">
                    <input
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      value="{{ (int) ($product->flash_sale_progress ?? 50) }}"
                      class="flash-progress-input w-16 h-8 border border-gray-200 rounded-lg px-2 text-xs font-medium text-center focus:outline-none focus:border-gray-400 tabular-nums"
                      data-progress-url="{{ route('admin.flash-sale.progress', $product) }}"
                    />
                    <span class="text-xs text-gray-400">%</span>
                    <span class="text-[11px] text-gray-500 whitespace-nowrap">Auto {{ $product->calculatedFlashSaleProgress() }}%</span>
                  </div>
                  <div class="mt-1.5 h-1.5 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="flash-progress-bar h-full rounded-full bg-amber-500" style="width: {{ (int) ($product->flash_sale_progress ?? 50) }}%"></div>
                  </div>
                </td>
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="{{ route('admin.products.edit', $product) }}" class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center">Edit</a>
                    <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" class="inline" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="h-8 px-3 rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium cursor-pointer">Remove</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </section>

  {{-- Add products --}}
  <section class="panel overflow-hidden">
    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h2 class="text-[15px] font-semibold text-gray-900">Add products</h2>
        <p class="text-xs text-gray-500 mt-0.5">Published products that are not in the flash sale yet.</p>
      </div>
      <form method="GET" action="{{ route('admin.flash-sale.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <label class="relative flex-1 sm:w-64">
          <span class="sr-only">Search products</span>
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="q" value="{{ $q }}" placeholder="Search name or SKU" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none" />
        </label>
        <button type="submit" class="h-10 px-4 rounded-full text-white text-[13px] font-semibold shrink-0 cursor-pointer" style="background: var(--brand-dark);">Search</button>
      </form>
    </div>

    {{-- Phone: cards --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($available as $product)
        <div class="rounded-2xl bg-gray-50/80 p-3 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-xl bg-white shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
            <div class="min-w-0">
              <h3 class="font-semibold text-gray-900 text-[13px] truncate">{{ $product->name }}</h3>
              <p class="text-[11px] text-gray-500 mt-0.5">
                <span class="font-semibold text-gray-900 tabular-nums">{{ money($product->price) }}</span>
                @if($product->on_sale)<span class="text-gray-400 line-through ml-1 tabular-nums">{{ money($product->regular_price) }}</span>@endif
                · {{ $product->stock_quantity }} in stock
              </p>
            </div>
          </div>

          <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="shrink-0">
            @csrf
            <button type="submit" class="h-8 px-3.5 rounded-full text-white text-xs font-semibold cursor-pointer" style="background: var(--brand-dark);">Add</button>
          </form>
        </div>
      @empty
        <div class="py-8 text-center text-gray-500 text-sm">No products found to add.</div>
      @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="text-gray-500 text-xs font-medium whitespace-nowrap border-y border-gray-100 bg-gray-50/60">
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Price</th>
            <th class="py-3 px-4">Stock</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($available as $product)
            <tr class="hover:bg-gray-50/70 transition-colors">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl bg-gray-100 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $product->category?->name }} · {{ $product->sku }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 whitespace-nowrap tabular-nums">
                <span class="font-semibold text-gray-900">{{ money($product->price) }}</span>
                @if($product->on_sale)<span class="text-[11px] text-gray-400 line-through ml-1">{{ money($product->regular_price) }}</span>@endif
              </td>
              <td class="py-3 px-4 text-gray-700 tabular-nums">{{ $product->stock_quantity }}</td>
              <td class="py-3 px-4 text-right">
                <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="inline">
                  @csrf
                  <button type="submit" class="h-8 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-medium inline-flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add to flash sale
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="py-10 text-center text-gray-500">No products found to add.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($available->hasPages())
      <div class="px-4 py-3 border-t border-gray-100">{{ $available->links() }}</div>
    @endif
  </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
(function () {
  var desktopList = document.getElementById('flashSaleList');
  var mobileList = document.getElementById('flashSaleListMobile');
  var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  var feedback = document.getElementById('flashReorderFeedback');

  function showFeedback(ok, message) {
    if (!feedback) return;
    feedback.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-900', 'bg-rose-50', 'border-rose-200', 'text-rose-700');
    if (ok) {
      feedback.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
    } else {
      feedback.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-700');
    }
    feedback.textContent = message;
  }

  /* ---- Quick Timer Helper Presets ---- */
  window.setTimerHours = function(hours) {
    var now = new Date();
    now.setHours(now.getHours() + hours);
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('flashSaleEndsAtInput').value = now.toISOString().slice(0, 16);
  };

  window.clearTimer = function() {
    document.getElementById('flashSaleEndsAtInput').value = '';
  };

  /* ---- Progress % Sync ---- */
  document.querySelectorAll('.flash-progress-input').forEach(function (input) {
    var timer = null;
    function saveProgress() {
      var val = parseInt(input.value, 10);
      if (Number.isNaN(val)) val = 0;
      val = Math.max(0, Math.min(100, val));
      input.value = val;

      var bar = input.closest('tr, .flash-card-item')?.querySelector('.flash-progress-bar');
      if (bar) bar.style.width = val + '%';

      var body = new FormData();
      body.append('_method', 'PUT');
      body.append('flash_sale_progress', String(val));

      fetch(input.dataset.progressUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
        },
        body: body,
        credentials: 'same-origin',
      })
        .then(function (res) {
          return res.json().then(function (data) {
            if (!res.ok) throw data;
            return data;
          });
        })
        .then(function (data) {
          showFeedback(true, data.message || 'Progress saved.');
        })
        .catch(function () {
          showFeedback(false, 'Could not save progress.');
        });
    }

    input.addEventListener('change', saveProgress);
    input.addEventListener('input', function () {
      var val = parseInt(input.value, 10);
      if (Number.isNaN(val)) return;
      val = Math.max(0, Math.min(100, val));
      var bar = input.closest('tr, .flash-card-item')?.querySelector('.flash-progress-bar');
      if (bar) bar.style.width = val + '%';
      clearTimeout(timer);
      timer = setTimeout(saveProgress, 600);
    });
  });

  /* ---- Drag Re-order (Desktop & Mobile) ---- */
  if (typeof Sortable === 'undefined') return;
  var reorderUrl = @json(route('admin.flash-sale.reorder'));

  function saveOrder(container) {
    var order = Array.from(container.querySelectorAll('[data-id]')).map(function (el) {
      return parseInt(el.getAttribute('data-id'), 10);
    });

    var body = new FormData();
    body.append('_method', 'PUT');
    order.forEach(function (id) { body.append('order[]', id); });

    fetch(reorderUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
      },
      body: body,
      credentials: 'same-origin',
    })
      .then(function (res) {
        return res.json().then(function (data) {
          if (!res.ok) throw data;
          return data;
        });
      })
      .then(function (data) {
        showFeedback(true, data.message || 'Order saved.');
      })
      .catch(function () {
        showFeedback(false, 'Could not save order. Refresh and try again.');
      });
  }

  function renumber(container) {
    container.querySelectorAll('.flash-pos').forEach(function (el, i) {
      el.textContent = '#' + (i + 1);
    });
  }

  if (desktopList) {
    Sortable.create(desktopList, {
      handle: '.flash-drag-handle',
      animation: 150,
      ghostClass: 'opacity-40',
      chosenClass: 'bg-amber-50',
      onEnd: function () {
        renumber(desktopList);
        saveOrder(desktopList);
      },
    });
  }

  if (mobileList) {
    Sortable.create(mobileList, {
      handle: '.flash-drag-handle',
      animation: 150,
      ghostClass: 'opacity-40',
      chosenClass: 'bg-amber-50',
      onEnd: function () {
        renumber(mobileList);
        saveOrder(mobileList);
      },
    });
  }

})();
</script>
@endpush
