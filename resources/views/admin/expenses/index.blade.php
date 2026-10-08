@extends('layouts.admin')
@section('title', 'Expenses')
@section('subtitle', 'Track ad spend, sourcing trips, packaging and store costs.')

@section('page-actions')
  <a href="{{ route('admin.expenses.export', request()->all()) }}" class="pill-btn">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
    Export CSV
  </a>
  <button type="button" onclick="openExpenseModal('marketing')" class="pill-btn cursor-pointer">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
    Log ad spend
  </button>
  <button type="button" onclick="openExpenseModal()" class="pill-btn pill-btn-dark cursor-pointer">
    <span class="pill-ico"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span>
    Add expense
  </button>
@endsection

@section('content')
<div class="space-y-4">

  {{-- Stats --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Total expenses</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-rose-50 text-rose-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">৳{{ number_format($totalExpenses, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">{{ $rangeLabel }}</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Facebook and ads</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-sky-50 text-sky-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">৳{{ number_format($marketingTotal, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5 flex items-center justify-between gap-2">
        <span>≈ ${{ number_format($defaultUsdRate > 0 ? $marketingTotal / $defaultUsdRate : 0, 1) }} USD</span>
        <a href="{{ route('admin.analytics.index') }}" class="font-medium text-gray-600 hover:text-gray-900 hover:underline">View ROAS</a>
      </p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Sourcing and travel</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 17h14M6 17l1.5-6h9L18 17M8 11l1-4h6l1 4"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="16.5" cy="18.5" r="1.5"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">৳{{ number_format($sourcingTotal, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Market visits and fares</p>
    </div>
    <div class="panel p-3.5 sm:p-4">
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500">Packaging</span>
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-violet-50 text-violet-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
        </span>
      </div>
      <p class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 tabular-nums">৳{{ number_format($packagingTotal, 2) }}</p>
      <p class="text-[11px] text-gray-400 mt-0.5">Bags, stickers, tape, boxes</p>
    </div>
  </div>

  {{-- Category tabs --}}
  <nav class="-mx-3 sm:mx-0 px-3 sm:px-0 overflow-x-auto no-scrollbar" aria-label="Expense category">
    <div class="inline-flex items-center gap-1 p-1 rounded-full bg-white shadow-panel whitespace-nowrap">
      @foreach(['all' => 'All'] + $categories as $catKey => $catLabel)
        @php $active = $category === $catKey; @endphp
        <a href="{{ route('admin.expenses.index', array_merge(request()->all(), ['category' => $catKey])) }}"
           class="h-8 sm:h-9 px-3.5 rounded-full text-[13px] font-medium inline-flex items-center transition-colors {{ $active ? 'text-white' : 'text-gray-600 hover:bg-gray-100' }}"
           @if($active) style="background: var(--brand-dark);" aria-current="page" @endif>{{ $catLabel }}</a>
      @endforeach
    </div>
  </nav>

  {{-- Expense list --}}
  <div class="card overflow-hidden">
    <div class="p-3 sm:p-4 flex flex-col sm:flex-row gap-2">
      <form method="GET" action="{{ route('admin.expenses.index') }}" class="relative flex-1">
        <input type="hidden" name="range" value="{{ $range }}">
        <input type="hidden" name="category" value="{{ $category }}">
        <span class="sr-only">Search expenses</span>
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or memo" class="w-full h-10 pl-10 pr-4 rounded-full bg-gray-100 border border-transparent text-sm focus:bg-white focus:border-gray-200 outline-none">
      </form>
      <form method="GET" action="{{ route('admin.expenses.index') }}" id="rangeForm">
        <input type="hidden" name="category" value="{{ $category }}">
        <label class="sr-only" for="expRange">Date range</label>
        <select id="expRange" name="range" onchange="document.getElementById('rangeForm').submit()" class="w-full sm:w-auto h-10 rounded-full bg-gray-100 border border-transparent px-4 text-sm text-gray-800 cursor-pointer">
          <option value="today" @selected($range === 'today')>Today</option>
          <option value="yesterday" @selected($range === 'yesterday')>Yesterday</option>
          <option value="this_week" @selected($range === 'this_week')>This week</option>
          <option value="last_week" @selected($range === 'last_week')>Last week</option>
          <option value="this_month" @selected($range === 'this_month')>This month ({{ now()->format('M') }})</option>
          <option value="last_month" @selected($range === 'last_month')>Last month ({{ now()->subMonth()->format('M') }})</option>
        </select>
      </form>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left text-[13px] border-collapse">
        <thead>
          <tr class="whitespace-nowrap border-y border-gray-100">
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Title</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Payment</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($expenses as $exp)
            <tr>
              <td class="py-3 px-4 text-gray-600 whitespace-nowrap tabular-nums">{{ $exp->expense_date->format('d M Y') }}</td>
              <td class="py-3 px-4 max-w-xs">
                <p class="font-semibold text-gray-900">{{ $exp->title }}</p>
                @if($exp->notes)
                  <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ $exp->notes }}</p>
                @endif
              </td>
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $exp->categoryBadge() }}">{{ $exp->categoryLabel() }}</span>
              </td>
              <td class="py-3 px-4 text-gray-600 max-w-[180px] truncate">
                @if($exp->product)
                  <span class="font-medium text-gray-800" title="{{ $exp->product->name }}">{{ $exp->product->name }}</span>
                @else
                  <span class="text-gray-400">Store wide</span>
                @endif
              </td>
              <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ ucfirst($exp->payment_method ?: 'cash') }}</td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <p class="font-semibold text-gray-900 tabular-nums">৳{{ number_format($exp->amount, 2) }}</p>
                @if($exp->currency === 'USD' && $exp->currency_amount)
                  <p class="text-[11px] text-gray-500 tabular-nums">${{ number_format($exp->currency_amount, 2) }} @ ৳{{ number_format($exp->currency_rate ?: 125, 0) }}</p>
                @endif
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                <div class="inline-flex items-center gap-1.5">
                  @if($exp->receipt_attachment)
                    <button type="button" onclick="previewReceipt('{{ $exp->receiptUrl() }}', '{{ addslashes($exp->title) }}')" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 inline-flex items-center justify-center" title="View receipt" aria-label="View receipt">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                  @endif
                  <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" onsubmit="return confirm('Delete expense \'{{ addslashes($exp->title) }}\'?');" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-8 w-8 rounded-full bg-gray-100 hover:bg-rose-50 text-gray-500 hover:text-rose-700 inline-flex items-center justify-center" title="Delete expense" aria-label="Delete expense">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-12 text-center">
                <p class="text-sm font-medium text-gray-600">No expenses recorded for this period</p>
                <p class="text-xs text-gray-400 mt-1">Use Add expense or Log ad spend above.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Phone cards --}}
    <div class="md:hidden px-3 pb-3 space-y-2">
      @forelse($expenses as $exp)
        <article class="rounded-2xl bg-gray-50/80 p-3.5 space-y-2">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-sm text-gray-900">{{ $exp->title }}</p>
              <p class="text-[11px] text-gray-500 mt-0.5">{{ $exp->expense_date->format('d M Y') }} · {{ ucfirst($exp->payment_method ?: 'cash') }}</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-semibold text-[15px] text-gray-900 tabular-nums">৳{{ number_format($exp->amount, 2) }}</p>
              @if($exp->currency === 'USD' && $exp->currency_amount)
                <p class="text-[11px] text-gray-500 tabular-nums">${{ number_format($exp->currency_amount, 2) }}</p>
              @endif
            </div>
          </div>
          @if($exp->notes)
            <p class="text-xs text-gray-600">{{ $exp->notes }}</p>
          @endif
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
              <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $exp->categoryBadge() }}">{{ $exp->categoryLabel() }}</span>
              @if($exp->product)
                <span class="text-[11px] text-gray-500 truncate">{{ $exp->product->name }}</span>
              @endif
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
              @if($exp->receipt_attachment)
                <button type="button" onclick="previewReceipt('{{ $exp->receiptUrl() }}', '{{ addslashes($exp->title) }}')" class="h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 text-gray-700 inline-flex items-center justify-center" aria-label="View receipt">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              @endif
              <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" onsubmit="return confirm('Delete expense \'{{ addslashes($exp->title) }}\'?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-8 w-8 rounded-full bg-rose-50 text-rose-700 inline-flex items-center justify-center" aria-label="Delete expense">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                </button>
              </form>
            </div>
          </div>
        </article>
      @empty
        <div class="py-10 text-center text-sm text-gray-500">No expenses recorded for this period.</div>
      @endforelse
    </div>

    @if($expenses->hasPages())
      <div class="p-3.5 sm:p-4 border-t border-gray-100">{{ $expenses->links() }}</div>
    @endif
  </div>

</div>

{{-- Add expense modal --}}
<div id="expenseModal" class="fixed inset-0 z-50 bg-gray-950/50 flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl max-h-[90vh] overflow-y-auto">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h3 class="text-[15px] font-semibold text-gray-900" id="expenseModalTitle">Log expense</h3>
        <p class="text-xs text-gray-500 mt-0.5">Ad spend, sourcing trips, packaging or other costs.</p>
      </div>
      <button type="button" onclick="closeExpenseModal()" class="h-8 w-8 grid place-items-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 shrink-0" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3" id="expenseForm">
      @csrf

      <div>
        <label class="lbl" for="expTitle">Title <span class="text-rose-500">*</span></label>
        <input type="text" name="title" id="expTitle" required placeholder="e.g. Facebook boost, Chawkbazar trip" class="w-full h-10 rounded-xl border border-gray-200 px-3.5 text-sm">
      </div>

      <div>
        <label class="lbl" for="expCategory">Category <span class="text-rose-500">*</span></label>
        <select name="category" id="expCategory" onchange="onCategorySelect(this.value)" class="w-full h-10 rounded-xl border border-gray-200 px-3 text-sm bg-white">
          @foreach($categories as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="rounded-xl bg-gray-50 p-3 space-y-2.5">
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <span class="text-xs font-semibold text-gray-700">Amount</span>
          <div class="flex items-center gap-3 text-xs">
            <label class="flex items-center gap-1.5 cursor-pointer text-gray-700">
              <input type="radio" name="currency" value="BDT" checked onchange="toggleCurrency('BDT')">
              BDT
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer text-gray-700">
              <input type="radio" name="currency" value="USD" onchange="toggleCurrency('USD')">
              USD (Meta ads)
            </label>
          </div>
        </div>

        <div id="bdtInputBox">
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">৳</span>
            <input type="number" step="0.01" name="amount" id="expAmountBDT" placeholder="0.00" class="w-full h-10 pl-8 pr-3 rounded-xl border border-gray-200 bg-white text-sm tabular-nums">
          </div>
        </div>

        <div id="usdInputBox" class="hidden space-y-2">
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] text-gray-500 mb-1" for="expAmountUSD">USD amount</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs">$</span>
                <input type="number" step="0.01" name="currency_amount" id="expAmountUSD" oninput="calcUsdToBdt()" placeholder="10.00" class="w-full h-10 pl-7 pr-2 rounded-xl border border-gray-200 bg-white text-sm tabular-nums">
              </div>
            </div>
            <div>
              <label class="block text-[11px] text-gray-500 mb-1" for="expDollarRate">Dollar rate (৳)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs">৳</span>
                <input type="number" step="0.01" name="currency_rate" id="expDollarRate" value="{{ $defaultUsdRate }}" oninput="calcUsdToBdt()" class="w-full h-10 pl-7 pr-2 rounded-xl border border-gray-200 bg-white text-sm tabular-nums">
              </div>
            </div>
          </div>
          <div class="text-xs text-gray-600 bg-white px-3 py-2 rounded-xl flex items-center justify-between">
            <span>Total in BDT</span>
            <span id="usdConvertedDisplay" class="font-semibold text-gray-900 tabular-nums">৳0.00</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="lbl" for="expDate">Date <span class="text-rose-500">*</span></label>
          <input type="date" name="expense_date" id="expDate" required value="{{ date('Y-m-d') }}" class="w-full h-10 rounded-xl border border-gray-200 px-3 text-sm">
        </div>
        <div>
          <label class="lbl" for="expPaymentMethod">Paid with</label>
          <select name="payment_method" id="expPaymentMethod" class="w-full h-10 rounded-xl border border-gray-200 px-3 text-sm bg-white">
            <option value="cash">Cash</option>
            <option value="bkash">bKash</option>
            <option value="nagad">Nagad</option>
            <option value="bank">Bank transfer</option>
            <option value="card">Card</option>
          </select>
        </div>
      </div>

      <div>
        <label class="lbl" for="expProduct">Product (optional)</label>
        <select name="product_id" id="expProduct" class="w-full h-10 rounded-xl border border-gray-200 px-3 text-sm bg-white">
          <option value="">General store expense</option>
          @foreach($products as $prod)
            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
          @endforeach
        </select>
        <p class="text-[11px] text-gray-400 mt-1">Pick a product to measure its ROAS.</p>
      </div>

      <div>
        <label class="lbl">Receipt (optional)</label>
        <input type="file" name="receipt_attachment" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:h-8 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
      </div>

      <div>
        <label class="lbl" for="expNotes">Notes</label>
        <textarea name="notes" id="expNotes" rows="2" placeholder="e.g. CNG fare Farmgate to Chawkbazar, 500 poly mailers" class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-sm"></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-1">
        <button type="button" onclick="closeExpenseModal()" class="h-9 px-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 text-[13px] font-medium">Cancel</button>
        <button type="submit" class="h-9 px-4 rounded-full text-white text-[13px] font-semibold" style="background: var(--brand-dark);">Save expense</button>
      </div>
    </form>
  </div>
</div>

{{-- Receipt preview --}}
<div id="receiptPreviewModal" class="fixed inset-0 z-50 bg-gray-950/70 flex items-center justify-center p-4 hidden" onclick="closeReceiptPreview()">
  <div class="max-w-2xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl p-4 space-y-3" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between gap-3">
      <h4 class="text-sm font-semibold text-gray-900" id="receiptPreviewTitle">Receipt</h4>
      <button type="button" onclick="closeReceiptPreview()" class="h-8 w-8 grid place-items-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600" aria-label="Close">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-gray-50 rounded-xl p-2">
      <img id="receiptPreviewImg" src="" alt="Receipt" class="max-w-full h-auto rounded-lg">
    </div>
  </div>
</div>

@push('scripts')
<script>
  function openExpenseModal(presetCategory = null) {
    const modal = document.getElementById('expenseModal');
    const form = document.getElementById('expenseForm');
    
    if (presetCategory) {
      document.getElementById('expCategory').value = presetCategory;
      if (presetCategory === 'marketing') {
        document.querySelector('input[name="currency"][value="USD"]').checked = true;
        toggleCurrency('USD');
        document.getElementById('expTitle').placeholder = "e.g. Meta Ads Boost - Airpods 2 / Feed Carousel";
      }
    } else {
      document.querySelector('input[name="currency"][value="BDT"]').checked = true;
      toggleCurrency('BDT');
    }

    modal.classList.remove('hidden');
  }

  function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
  }

  function onCategorySelect(val) {
    if (val === 'marketing') {
      document.querySelector('input[name="currency"][value="USD"]').checked = true;
      toggleCurrency('USD');
    }
  }

  function toggleCurrency(curr) {
    const bdtBox = document.getElementById('bdtInputBox');
    const usdBox = document.getElementById('usdInputBox');

    if (curr === 'USD') {
      usdBox.classList.remove('hidden');
      bdtBox.classList.add('hidden');
      calcUsdToBdt();
    } else {
      bdtBox.classList.remove('hidden');
      usdBox.classList.add('hidden');
    }
  }

  function calcUsdToBdt() {
    const usd = parseFloat(document.getElementById('expAmountUSD').value) || 0;
    const rate = parseFloat(document.getElementById('expDollarRate').value) || 125;
    const totalBdt = (usd * rate).toFixed(2);
    document.getElementById('usdConvertedDisplay').innerText = `৳${totalBdt}`;
    document.getElementById('expAmountBDT').value = totalBdt;
  }

  function previewReceipt(url, title) {
    document.getElementById('receiptPreviewImg').src = url;
    document.getElementById('receiptPreviewTitle').innerText = title || 'Voucher';
    document.getElementById('receiptPreviewModal').classList.remove('hidden');
  }

  function closeReceiptPreview() {
    document.getElementById('receiptPreviewModal').classList.add('hidden');
    document.getElementById('receiptPreviewImg').src = '';
  }
</script>
@endpush
@endsection
