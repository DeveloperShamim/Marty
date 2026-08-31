@extends('layouts.admin')
@section('title', 'Flash Sale Manager')

@section('content')
@php
  $endsLocal = !empty($endsAt)
    ? \Illuminate\Support\Str::of($endsAt)->replace(' ', 'T')->substr(0, 16)
    : '';
  $hasActiveCountdown = !empty($endsAt) && \Illuminate\Support\Carbon::parse($endsAt)->isFuture();
@endphp

<div class="space-y-5 sm:space-y-6 max-w-full">

  {{-- Header & Stats Ribbon --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs">
    <div>
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-base sm:text-xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
          <span>⚡</span> Flash Sale Manager
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-900 border border-amber-200">
          {{ $flashProducts->count() }} Deals Active
        </span>
        @if($hasActiveCountdown)
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
            ● Timer Running
          </span>
        @else
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-100 text-stone-500 border border-stone-200">
            ○ Timer Inactive
          </span>
        @endif
      </div>
      <p class="text-xs text-stone-500 mt-1">
        Configure homepage flash deals, live expiration countdown timer, product display sequence, and stock progress bars.
      </p>
    </div>

    <a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white hover:bg-stone-50 text-stone-800 border border-stone-200 font-extrabold text-xs shadow-2xs transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto shrink-0 group">
      <span>View Live on Storefront</span>
      <span class="text-stone-400 group-hover:text-emerald-700 transition-transform group-hover:translate-x-0.5">↗</span>
    </a>
  </div>

  @if(session('status'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-extrabold px-4 py-3 shadow-2xs flex items-center gap-2">
      <span>✓</span>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <div id="flashReorderFeedback" class="hidden rounded-2xl text-xs sm:text-sm font-extrabold px-4 py-3 border shadow-2xs"></div>

  {{-- Countdown End Time & Schedule Form Card --}}
  <form method="POST" action="{{ route('admin.flash-sale.ends-at') }}" class="bg-white p-4 sm:p-5 lg:p-6 rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs space-y-3">
    @csrf
    @method('PUT')
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 sm:gap-4">
      <div class="flex-1 min-w-0 space-y-1.5">
        <label class="text-xs font-black text-stone-800 flex items-center gap-1.5">
          <span>⏰</span> Flash Sale Countdown Expiration Date &amp; Time
        </label>
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
          <input type="datetime-local" id="flashSaleEndsAtInput" name="flash_sale_ends_at" class="w-full sm:w-80 text-xs font-bold px-3.5 py-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-2xs" value="{{ old('flash_sale_ends_at', $endsLocal) }}" />
          
          <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" onclick="setTimerHours(24)" class="px-2.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition shadow-2xs cursor-pointer">+24 Hours</button>
            <button type="button" onclick="setTimerHours(72)" class="px-2.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition shadow-2xs cursor-pointer">+3 Days</button>
            <button type="button" onclick="setTimerHours(168)" class="px-2.5 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 text-[11px] font-bold transition shadow-2xs cursor-pointer">+7 Days</button>
            <button type="button" onclick="clearTimer()" class="px-2.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold transition shadow-2xs cursor-pointer">Clear</button>
          </div>
        </div>
        <p class="text-[11px] text-stone-400">Controls the live clock on the homepage flash sale row. Leave blank to hide the clock.</p>
      </div>

      <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md transition-all shrink-0 cursor-pointer">
        Save Schedule
      </button>
    </div>
  </form>

  {{-- Active Flash Sale Deals Container --}}
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden space-y-0">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>🔥</span> Active Flash Deals ({{ $flashProducts->count() }})
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Drag card or handle to reorder products. Adjust Stock Progress % (0–100%) for the storefront progress bar.</p>
      </div>
    </div>

    @if($flashProducts->isEmpty())
      <div class="p-8 sm:p-12 text-center text-stone-400 text-xs sm:text-sm font-bold bg-stone-50/40">
        <div class="text-3xl mb-2">⚡</div>
        No products in flash sale yet. Add deals from the available catalog below!
      </div>
    @else

      {{-- Mobile Drag-and-Drop Cards View (`block md:hidden`) --}}
      <div id="flashSaleListMobile" class="grid grid-cols-1 gap-3 p-4 md:hidden divide-y-0">
        @foreach($flashProducts as $i => $product)
          <div class="flash-card-item relative bg-white p-4 rounded-2xl border border-stone-200 shadow-2xs space-y-3" data-id="{{ $product->id }}">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <button type="button" class="flash-drag-handle inline-flex h-9 w-9 items-center justify-center rounded-xl border border-stone-200 bg-stone-50 text-stone-600 hover:bg-brand-50 hover:text-brand-700 cursor-grab active:cursor-grabbing shrink-0" title="Drag to reorder">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                </button>
                <img src="{{ $product->imageUrl() }}" class="h-12 w-12 object-cover rounded-xl border border-stone-100 bg-stone-50 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="flash-pos inline-flex h-5 px-1.5 items-center justify-center rounded-md bg-amber-100 text-amber-900 font-black text-[10px]">#{{ $i + 1 }}</span>
                    <h3 class="font-extrabold text-stone-900 text-xs leading-tight line-clamp-1">{{ $product->name }}</h3>
                  </div>
                  <p class="text-[11px] text-stone-400 mt-0.5 font-mono">{{ $product->category?->name }} • {{ $product->sku }}</p>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs bg-stone-50 p-2.5 rounded-xl border border-stone-200/80">
              <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Flash Price</span>
                <span class="font-black text-emerald-700 text-sm font-mono">{{ money($product->price) }}</span>
                @if($product->on_sale)
                  <span class="text-[10px] text-stone-400 line-through ml-1 font-mono">{{ money($product->regular_price) }}</span>
                @endif
              </div>
              <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Total Stock</span>
                <span class="font-bold text-stone-800 font-mono">{{ $product->stock_quantity }} units</span>
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
                    class="flash-progress-input w-16 border border-stone-200 rounded-lg px-2 py-1 text-xs font-bold text-center focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono"
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
              <a href="{{ route('admin.products.edit', $product) }}" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition shadow-2xs">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition cursor-pointer">
                  Remove
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Desktop Drag-and-Drop Table View (`hidden md:block`) --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
              <th class="px-4 py-3.5 w-12 text-center">Drag</th>
              <th class="px-4 py-3.5 w-14 text-center"># Pos</th>
              <th class="px-5 py-3.5">Product Information</th>
              <th class="px-5 py-3.5">Deal Price</th>
              <th class="px-5 py-3.5 text-center">Stock</th>
              <th class="px-5 py-3.5 w-48">Stock Progress %</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="flashSaleList" class="divide-y divide-stone-100 bg-white">
            @foreach($flashProducts as $i => $product)
              <tr class="hover:bg-stone-50/80 transition-colors" data-id="{{ $product->id }}">
                <td class="px-4 py-3.5 text-center">
                  <button type="button" class="flash-drag-handle inline-flex h-8 w-8 items-center justify-center rounded-lg border border-stone-200 bg-stone-50 text-stone-500 hover:bg-stone-100 hover:text-stone-800 cursor-grab active:cursor-grabbing shadow-2xs" title="Drag to reorder">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                  </button>
                </td>
                <td class="px-4 py-3.5 text-center">
                  <span class="flash-pos inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-900 font-black text-xs">#{{ $i + 1 }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-3">
                    <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl border border-stone-200 bg-stone-50 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                    <div>
                      <p class="font-extrabold text-stone-900 text-xs">{{ $product->name }}</p>
                      <p class="text-[11px] text-stone-400 font-mono mt-0.5">{{ $product->category?->name }} • {{ $product->sku }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5">
                  <span class="font-black text-emerald-700 font-mono text-sm">{{ money($product->price) }}</span>
                  @if($product->on_sale)
                    <span class="text-[11px] text-stone-400 line-through ml-1 font-mono">{{ money($product->regular_price) }}</span>
                  @endif
                </td>
                <td class="px-5 py-3.5 text-center font-black text-stone-800 font-mono">{{ $product->stock_quantity }}</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <input
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      value="{{ (int) ($product->flash_sale_progress ?? 50) }}"
                      class="flash-progress-input w-16 border border-stone-200 rounded-lg px-2 py-1 text-xs font-bold text-center focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono"
                      data-progress-url="{{ route('admin.flash-sale.progress', $product) }}"
                    />
                    <span class="text-xs font-bold text-stone-400">%</span>
                  </div>
                  <div class="mt-1.5 h-1.5 w-full rounded-full bg-stone-100 overflow-hidden border border-stone-200/60">
                    <div class="flash-progress-bar h-full rounded-full bg-amber-500" style="width: {{ (int) ($product->flash_sale_progress ?? 50) }}%"></div>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                  <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 border border-stone-200 transition shadow-2xs">Edit</a>
                  <form method="POST" action="{{ route('admin.flash-sale.remove', $product) }}" class="inline" onsubmit="return confirm('Remove {{ addslashes($product->name) }} from Flash Sale?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition cursor-pointer">Remove</button>
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
  <div class="bg-white rounded-2xl sm:rounded-3xl border border-stone-200 shadow-2xs overflow-hidden">
    <div class="p-4 sm:p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-stone-50/50">
      <div>
        <h2 class="font-extrabold text-stone-900 text-sm sm:text-base flex items-center gap-2">
          <span>➕</span> Add Products to Flash Sale
        </h2>
        <p class="text-xs text-stone-500 mt-0.5">Published products in your store available to be added to Flash Sale.</p>
      </div>

      <form method="GET" action="{{ route('admin.flash-sale.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search product name or SKU..." class="flex-1 sm:w-64 border border-stone-200 rounded-xl px-3.5 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white" />
        <button type="submit" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-extrabold text-xs hover:bg-stone-800 transition cursor-pointer shadow-2xs">Search</button>
      </form>
    </div>

    {{-- Available Products Mobile Cards View (`block md:hidden`) --}}
    <div class="grid grid-cols-1 gap-3 p-4 md:hidden">
      @forelse($available as $product)
        <div class="bg-white p-3.5 rounded-2xl border border-stone-200 shadow-2xs flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <img src="{{ $product->imageUrl() }}" class="h-11 w-11 object-cover rounded-xl border border-stone-100 bg-stone-50 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
            <div class="min-w-0">
              <h3 class="font-extrabold text-stone-900 text-xs truncate">{{ $product->name }}</h3>
              <p class="text-[11px] text-stone-500 font-bold mt-0.5">
                <span class="font-mono text-stone-900 font-black">{{ money($product->price) }}</span>
                @if($product->on_sale)<span class="text-[10px] text-stone-400 line-through ml-1 font-mono">{{ money($product->regular_price) }}</span>@endif
                • {{ $product->stock_quantity }} in stock
              </p>
            </div>
          </div>

          <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="shrink-0">
            @csrf
            <button type="submit" class="px-3.5 py-2 text-xs rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold shadow-2xs transition cursor-pointer">
              + Add
            </button>
          </form>
        </div>
      @empty
        <div class="p-8 text-center text-stone-400 text-xs font-bold">No available products found to add.</div>
      @endforelse
    </div>

    {{-- Available Products Desktop Table View (`hidden md:block`) --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="bg-stone-100/90 text-stone-700 font-black border-b border-stone-200 uppercase text-[11px] tracking-wider whitespace-nowrap">
            <th class="px-5 py-3.5">Product Information</th>
            <th class="px-5 py-3.5">Price</th>
            <th class="px-5 py-3.5 text-center">Stock</th>
            <th class="px-5 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 bg-white">
          @forelse($available as $product)
            <tr class="hover:bg-stone-50/70 transition-colors">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <img src="{{ $product->imageUrl() }}" class="h-10 w-10 object-cover rounded-xl border border-stone-200 bg-stone-50 shrink-0" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23a8a29e\' stroke-width=\'2\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\'/><circle cx=\'9\' cy=\'9\' r=\'2\'/></svg>';">
                  <div>
                    <p class="font-extrabold text-stone-900 text-xs">{{ $product->name }}</p>
                    <p class="text-[11px] text-stone-400 font-mono mt-0.5">{{ $product->category?->name }} • {{ $product->sku }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5 font-black text-stone-900 font-mono text-sm">
                {{ money($product->price) }}
                @if($product->on_sale)<span class="text-[11px] text-stone-400 line-through ml-1">{{ money($product->regular_price) }}</span>@endif
              </td>
              <td class="px-5 py-3.5 text-center font-bold text-stone-700 font-mono">{{ $product->stock_quantity }}</td>
              <td class="px-5 py-3.5 text-right">
                <form method="POST" action="{{ route('admin.flash-sale.add', $product) }}" class="inline">
                  @csrf
                  <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold shadow-2xs transition cursor-pointer">
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
      <div class="p-4 border-t border-stone-100 bg-stone-50/40">{{ $available->links() }}</div>
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
