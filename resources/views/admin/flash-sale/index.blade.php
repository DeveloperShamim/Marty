@extends('layouts.admin')
@section('title', 'Flash Sale Manager')

@section('content')
@php
  $endsLocal = !empty($endsAt)
    ? \Illuminate\Support\Str::of($endsAt)->replace(' ', 'T')->substr(0, 16)
    : '';
@endphp

<div class="space-y-6">

  {{-- Page Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs">
    <div>
      <h1 class="text-xl sm:text-2xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
        <span>⚡ Flash Sale Manager</span>
      </h1>
      <p class="text-xs sm:text-sm text-stone-500 mt-1">Select homepage Flash Sale deals, configure stock progress bars, and set countdown timers</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100 font-extrabold text-xs shadow-2xs transition-all">
      <span>View Live on Storefront &nearr;</span>
    </a>
  </div>

  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <div id="flashReorderFeedback" class="hidden rounded-2xl text-xs sm:text-sm font-extrabold px-4 py-3 border shadow-2xs"></div>

  {{-- Countdown End Time Form --}}
  <form method="POST" action="{{ route('admin.flash-sale.ends-at') }}" class="bg-white p-4 sm:p-6 rounded-2xl border border-stone-200 shadow-2xs space-y-3">
    @csrf
    @method('PUT')
    <div class="flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4">
      <div class="flex-1 min-w-0">
        <label class="text-xs font-extrabold text-stone-800 block mb-1">⏰ Flash Sale Countdown Expiration Date &amp; Time</label>
        <input type="datetime-local" name="flash_sale_ends_at" class="w-full text-xs font-bold px-3.5 py-2.5 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-brand-500 shadow-2xs" value="{{ old('flash_sale_ends_at', $endsLocal) }}" />
        <p class="text-[11px] text-stone-400 mt-1">Controls the live countdown timer on the homepage. Leave blank to hide the countdown clock.</p>
      </div>
      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all shrink-0 cursor-pointer">
        💾 Save Countdown Time
      </button>
    </div>
  </form>

  {{-- Active Flash Sale Products Card --}}
  <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>🔥 Active Flash Sale Products ({{ $flashProducts->count() }})</span>
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Drag card or handle to reorder products. Adjust Stock Progress % (0–100%) for the storefront progress bar.</p>
      </div>
    </div>

    @if($flashProducts->isEmpty())
      <div class="p-8 sm:p-12 text-center text-stone-400 text-xs sm:text-sm font-bold">
        <div class="text-3xl mb-2">⚡</div>
        No products in flash sale yet. Add deals from the available products catalog below!
      </div>
    @else

      {{-- Mobile Drag-and-Drop Cards View (Visible on Small Screens) --}}
      <div id="flashSaleListMobile" class="grid grid-cols-1 gap-3 p-4 md:hidden">
        @foreach($flashProducts as $i => $product)
          <div class="flash-card-item relative bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs space-y-3" data-id="{{ $product->id }}">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <button type="button" class="flash-drag-handle inline-flex h-9 w-9 items-center justify-center rounded-xl border border-stone-200 bg-stone-50 text-stone-600 hover:bg-brand-50 hover:text-brand-700 cursor-grab active:cursor-grabbing shrink-0" title="Drag to reorder">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                </button>
                <img src="{{ $product->imageUrl() }}" class="h-12 w-12 object-cover rounded-xl border border-stone-100 bg-stone-50 shrink-0" alt="{{ $product->name }}">
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="flash-pos inline-flex h-5 px-1.5 items-center justify-center rounded-md bg-amber-100 text-amber-900 font-extrabold text-[10px]">#{{ $i + 1 }}</span>
                    <h3 class="font-extrabold text-stone-900 text-xs leading-tight line-clamp-1">{{ $product->name }}</h3>
                  </div>
                  <p class="text-[11px] text-stone-400 mt-0.5 font-mono">{{ $product->category?->name }} • {{ $product->sku }}</p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs bg-stone-50 p-2.5 rounded-xl border border-stone-200/80">
              <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Flash Price</span>
                <span class="font-extrabold {{ $product->on_sale ? 'text-amber-700' : 'text-stone-900' }} text-sm">{{ money($product->price) }}</span>
                @if($product->on_sale)
                  <span class="text-[10px] text-stone-400 line-through ml-1">{{ money($product->regular_price) }}</span>
                @endif
              </div>
              <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Total Stock</span>
                <span class="font-bold text-stone-800">{{ $product->stock_quantity }} units</span>
              </div>
            </div>

            <div class="space-y-1.5">
              <div class="flex items-center justify-between text-xs">
                <label class="text-[11px] font-extrabold text-stone-700">Stock Progress %</label>
                <div class="flex items-center gap-1">
                  <input
                    type="number"
                    min="0"
                    max="100"
                    step="1"
                    value="{{ (int) ($product->flash_sale_progress ?? 50) }}"
                    class="flash-progress-input w-16 border border-stone-200 rounded-lg px-2 py-1 text-xs font-bold text-center focus:outline-none focus:border-brand-500"
                    data-progress-url="{{ route('admin.flash-sale.progress', $product) }}"
                  />
                  <span class="text-xs font-bold text-stone-400">%</span>
                </div>
              </div>
              <div class="h-2 w-full rounded-full bg-stone-100 overflow-hidden border border-stone-200/60">
                <div class="flash-progress-bar h-full rounded-full bg-amber-500 transition-all duration-300" style="width: {{ (int) ($product->flash_sale_progress ?? 50) }}%"></div>
              </div>
            </div>

            <div class="pt-2 border-t border-stone-100 flex items-center justify-end gap-2">
              <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 text-stone-800 border border-stone-200">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer">
                  Remove
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Desktop Drag-and-Drop Table View (Hidden on Small Screens) --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-stone-50 border-b border-stone-200 font-extrabold text-stone-600 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3.5 w-12 text-center">Drag</th>
              <th class="px-4 py-3.5 w-14 text-center"># Pos</th>
              <th class="px-5 py-3.5">Product Information</th>
              <th class="px-5 py-3.5">Price</th>
              <th class="px-5 py-3.5 text-center">Stock</th>
              <th class="px-5 py-3.5 w-48">Progress %</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="flashSaleList" class="divide-y divide-stone-100 font-medium bg-white">
            @foreach($flashProducts as $i => $product)
              <tr class="hover:bg-stone-50/80 transition-colors" data-id="{{ $product->id }}">
                <td class="px-4 py-3.5 text-center">
                  <button type="button" class="flash-drag-handle inline-flex h-8 w-8 items-center justify-center rounded-lg border border-stone-200 bg-stone-50 text-stone-500 hover:bg-brand-50 hover:text-brand-700 cursor-grab active:cursor-grabbing" title="Drag to reorder">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                  </button>
                </td>
                <td class="px-4 py-3.5 text-center">
                  <span class="flash-pos inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-900 font-extrabold text-xs">#{{ $i + 1 }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl border border-stone-200 bg-stone-50 shrink-0" alt="{{ $product->name }}">
                    <div>
                      <p class="font-extrabold text-stone-900 text-xs">{{ $product->name }}</p>
                      <p class="text-[11px] text-stone-400 font-mono mt-0.5">{{ $product->category?->name }} • {{ $product->sku }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5">
                  <span class="font-extrabold {{ $product->on_sale ? 'text-amber-700' : 'text-stone-900' }}">{{ money($product->price) }}</span>
                  @if($product->on_sale)
                    <span class="text-[11px] text-stone-400 line-through ml-1">{{ money($product->regular_price) }}</span>
                  @endif
                </td>
                <td class="px-5 py-3.5 text-center font-bold text-stone-700">{{ $product->stock_quantity }}</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <input
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      value="{{ (int) ($product->flash_sale_progress ?? 50) }}"
                      class="flash-progress-input w-16 border border-stone-200 rounded-lg px-2 py-1 text-xs font-bold text-center focus:outline-none focus:border-brand-500"
                      data-progress-url="{{ route('admin.flash-sale.progress', $product) }}"
                    />
                    <span class="text-xs font-bold text-stone-400">%</span>
                  </div>
                  <div class="mt-1.5 h-1.5 w-full rounded-full bg-stone-100 overflow-hidden border border-stone-200/60">
                    <div class="flash-progress-bar h-full rounded-full bg-amber-500" style="width: {{ (int) ($product->flash_sale_progress ?? 50) }}%"></div>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                  <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 border border-stone-200">Edit</a>
                  <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" class="inline" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 cursor-pointer">Remove</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  {{-- Add Products to Flash Sale Section --}}
  <div class="bg-white rounded-2xl border border-stone-200 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>➕ Add Products to Flash Sale</span>
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Published products in your store available to be added to Flash Sale.</p>
      </div>

      <form method="GET" action="{{ route('admin.flash-sale.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search product name or SKU..." class="flex-1 sm:w-60 border border-stone-200 rounded-xl px-3.5 py-2 text-xs font-semibold focus:outline-none focus:border-brand-500" />
        <button type="submit" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition">Search</button>
      </form>
    </div>

    {{-- Available Products Mobile Cards View --}}
    <div class="grid grid-cols-1 gap-3 p-4 md:hidden">
      @forelse($available as $product)
        <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-2xs flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-xl border border-stone-100 bg-stone-50 shrink-0" alt="{{ $product->name }}">
            <div class="min-w-0">
              <h3 class="font-extrabold text-stone-900 text-xs truncate">{{ $product->name }}</h3>
              <p class="text-[11px] text-stone-500 font-bold mt-0.5">
                {{ money($product->price) }}
                @if($product->on_sale)<span class="text-[10px] text-stone-400 line-through ml-1">{{ money($product->regular_price) }}</span>@endif
                • {{ $product->stock_quantity }} in stock
              </p>
            </div>
          </div>

          <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="shrink-0">
            @csrf
            <button type="submit" class="px-3 py-2 text-xs rounded-xl bg-amber-500 text-white font-extrabold hover:bg-amber-600 shadow-2xs cursor-pointer">
              + Add
            </button>
          </form>
        </div>
      @empty
        <div class="p-8 text-center text-stone-400 text-xs font-bold">No available products found to add.</div>
      @endforelse
    </div>

    {{-- Available Products Desktop Table View --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-stone-50 border-b border-stone-200 font-extrabold text-stone-600 uppercase tracking-wider">
          <tr>
            <th class="px-5 py-3.5">Product Information</th>
            <th class="px-5 py-3.5">Price</th>
            <th class="px-5 py-3.5 text-center">Stock</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 font-medium bg-white">
          @forelse($available as $product)
            <tr class="hover:bg-stone-50/70 transition-colors">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl border border-stone-200 bg-stone-50 shrink-0" alt="{{ $product->name }}">
                  <div>
                    <p class="font-extrabold text-stone-900 text-xs">{{ $product->name }}</p>
                    <p class="text-[11px] text-stone-400 font-mono mt-0.5">{{ $product->category?->name }} • {{ $product->sku }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5 font-extrabold text-stone-900">
                {{ money($product->price) }}
                @if($product->on_sale)<span class="text-[11px] text-stone-400 line-through ml-1">{{ money($product->regular_price) }}</span>@endif
              </td>
              <td class="px-5 py-3.5 text-center font-bold text-stone-700">{{ $product->stock_quantity }}</td>
              <td class="px-5 py-3.5 text-right">
                <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="inline">
                  @csrf
                  <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-amber-500 text-white font-extrabold hover:bg-amber-600 shadow-2xs cursor-pointer">
                    + Add to Flash Sale
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="px-5 py-10 text-center text-stone-400 font-bold">No available products found to add.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($available->hasPages())
      <div class="p-4 border-t border-stone-100">{{ $available->links() }}</div>
    @endif
  </div>

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
