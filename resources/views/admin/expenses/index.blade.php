@extends('layouts.admin')
@section('title', 'Expense & Ad Spend Manager')

@section('content')
<div class="space-y-6">

  {{-- Top Header & Filters --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs p-5 sm:p-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </span>
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Expense &amp; Ad Spend Manager</h1>
            <p class="text-xs sm:text-sm text-gray-500">Track Facebook boost costs, sourcing runs, packaging materials, and store overhead</p>
          </div>
        </div>
      </div>

      {{-- Action Buttons & Date Range --}}
      <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
        {{-- Date Range Dropdown Form --}}
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex items-center gap-1.5" id="rangeForm">
          <input type="hidden" name="category" value="{{ $category }}">
          <select name="range" onchange="document.getElementById('rangeForm').submit()" class="text-xs font-bold px-3 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 text-gray-800">
            <option value="today" @selected($range === 'today')>Today</option>
            <option value="yesterday" @selected($range === 'yesterday')>Yesterday</option>
            <option value="this_week" @selected($range === 'this_week')>This Week</option>
            <option value="last_week" @selected($range === 'last_week')>Last Week</option>
            <option value="this_month" @selected($range === 'this_month')>This Month ({{ now()->format('M') }})</option>
            <option value="last_month" @selected($range === 'last_month')>Last Month ({{ now()->subMonth()->format('M') }})</option>
          </select>
        </form>

        <a href="{{ route('admin.expenses.export', request()->all()) }}" class="px-3 py-2 text-xs font-bold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 transition-colors flex items-center gap-1.5 shadow-2xs">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Export CSV
        </a>

        {{-- Quick FB Boost Button --}}
        <button type="button" onclick="openExpenseModal('marketing')" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-colors flex items-center gap-1.5 shadow-2xs">
          <span>📣</span>
          Log FB Boost ($)
        </button>

        {{-- General Add Expense Button --}}
        <button type="button" onclick="openExpenseModal()" class="px-4 py-2 text-xs font-bold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition-colors flex items-center gap-1.5 shadow-xs">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          + Add Expense
        </button>
      </div>
    </div>
  </div>

  {{-- Summary KPI Cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    {{-- Total Expenses --}}
    <div class="bg-white p-5 rounded-2xl border border-gray-200/90 shadow-2xs">
      <div class="flex items-center justify-between text-gray-500 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider">Total Expenses</span>
        <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
      </div>
      <div class="text-2xl font-black text-gray-900 tracking-tight">৳{{ number_format($totalExpenses, 2) }}</div>
      <div class="text-xs text-gray-500 mt-1">{{ $rangeLabel }} period</div>
    </div>

    {{-- Facebook / Marketing Ad Spend --}}
    <div class="bg-white p-5 rounded-2xl border border-blue-200/90 shadow-2xs bg-gradient-to-br from-white to-blue-50/20">
      <div class="flex items-center justify-between text-blue-700 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider">Facebook &amp; Ads</span>
        <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700">📣</span>
      </div>
      <div class="text-2xl font-black text-blue-900 tracking-tight">৳{{ number_format($marketingTotal, 2) }}</div>
      <div class="text-xs text-blue-600 mt-1 flex items-center justify-between">
        <span>≈ ${{ number_format($defaultUsdRate > 0 ? $marketingTotal / $defaultUsdRate : 0, 1) }} USD</span>
        <a href="{{ route('admin.analytics.index') }}" class="underline font-bold hover:text-blue-800">View ROAS &rarr;</a>
      </div>
    </div>

    {{-- Sourcing & Travel Trips --}}
    <div class="bg-white p-5 rounded-2xl border border-amber-200/90 shadow-2xs bg-gradient-to-br from-white to-amber-50/20">
      <div class="flex items-center justify-between text-amber-700 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider">Sourcing &amp; Travel</span>
        <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">🚗</span>
      </div>
      <div class="text-2xl font-black text-amber-900 tracking-tight">৳{{ number_format($sourcingTotal, 2) }}</div>
      <div class="text-xs text-amber-600 mt-1">Market visits, fares &amp; sundries</div>
    </div>

    {{-- Packaging Supplies --}}
    <div class="bg-white p-5 rounded-2xl border border-purple-200/90 shadow-2xs bg-gradient-to-br from-white to-purple-50/20">
      <div class="flex items-center justify-between text-purple-700 mb-2">
        <span class="text-xs font-bold uppercase tracking-wider">Packaging Materials</span>
        <span class="p-1.5 rounded-lg bg-purple-100 text-purple-700">📦</span>
      </div>
      <div class="text-2xl font-black text-purple-900 tracking-tight">৳{{ number_format($packagingTotal, 2) }}</div>
      <div class="text-xs text-purple-600 mt-1">Poly bags, stickers, tape &amp; boxes</div>
    </div>

  </div>

  {{-- Category Filter Pills & Search Bar --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    {{-- Category Pills --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
      <a href="{{ route('admin.expenses.index', array_merge(request()->all(), ['category' => 'all'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ $category === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200' }}">
        All Expenses
      </a>
      @foreach($categories as $catKey => $catLabel)
        <a href="{{ route('admin.expenses.index', array_merge(request()->all(), ['category' => $catKey])) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 {{ $category === $catKey ? 'bg-slate-900 text-white shadow-xs' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200' }}">
          {{ $catLabel }}
        </a>
      @endforeach
    </div>

    {{-- Search Input --}}
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="relative w-full sm:w-64 shrink-0">
      <input type="hidden" name="range" value="{{ $range }}">
      <input type="hidden" name="category" value="{{ $category }}">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or memo..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 text-gray-800">
      <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </form>
  </div>

  {{-- Expenses Table --}}
  <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
          <tr>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Title &amp; Notes</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4">Target Product</th>
            <th class="py-3 px-4">Payment</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-center">Receipt</th>
            <th class="py-3 px-4 text-center">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($expenses as $exp)
            <tr class="hover:bg-gray-50/80 transition-colors">
              {{-- Date --}}
              <td class="py-3 px-4 text-gray-600 font-mono whitespace-nowrap">
                {{ $exp->expense_date->format('d M, Y') }}
              </td>

              {{-- Title & Notes --}}
              <td class="py-3 px-4 max-w-xs">
                <div class="font-bold text-gray-900">{{ $exp->title }}</div>
                @if($exp->notes)
                  <div class="text-[11px] text-gray-400 truncate mt-0.5">{{ $exp->notes }}</div>
                @endif
              </td>

              {{-- Category --}}
              <td class="py-3 px-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1 {{ $exp->categoryBadge() }}">
                  <span>{{ $exp->categoryIcon() }}</span>
                  <span>{{ $exp->categoryLabel() }}</span>
                </span>
              </td>

              {{-- Target Product --}}
              <td class="py-3 px-4 text-gray-600 max-w-[180px] truncate">
                @if($exp->product)
                  <span class="font-semibold text-brand-700" title="{{ $exp->product->name }}">{{ $exp->product->name }}</span>
                @else
                  <span class="text-gray-400">&mdash; Store Wide</span>
                @endif
              </td>

              {{-- Payment Method --}}
              <td class="py-3 px-4 text-gray-600 uppercase text-[10px] font-mono font-bold whitespace-nowrap">
                {{ $exp->payment_method ?: 'Cash' }}
              </td>

              {{-- Amount in BDT (+ USD if applicable) --}}
              <td class="py-3 px-4 text-right whitespace-nowrap font-mono">
                <div class="font-black text-sm text-gray-900">৳{{ number_format($exp->amount, 2) }}</div>
                @if($exp->currency === 'USD' && $exp->currency_amount)
                  <div class="text-[10px] text-blue-600 font-semibold">${{ number_format($exp->currency_amount, 2) }} @ ৳{{ number_format($exp->currency_rate ?: 125, 0) }}</div>
                @endif
              </td>

              {{-- Receipt Voucher --}}
              <td class="py-3 px-4 text-center whitespace-nowrap">
                @if($exp->receipt_attachment)
                  <button type="button" onclick="previewReceipt('{{ $exp->receiptUrl() }}', '{{ addslashes($exp->title) }}')" class="p-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors inline-block" title="View receipt">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </button>
                @else
                  <span class="text-gray-300">&mdash;</span>
                @endif
              </td>

              {{-- Action (Delete) --}}
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}" onsubmit="return confirm('Delete expense \'{{ addslashes($exp->title) }}\'?');" class="inline-block">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="p-1 rounded-lg hover:bg-rose-50 text-gray-400 hover:text-rose-600 transition-colors" title="Delete expense">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-12 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="font-bold text-gray-500">No expenses recorded for this period</p>
                <p class="text-xs text-gray-400 mt-1">Click "+ Add Expense" or "Log FB Boost" above to log costs.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($expenses->hasPages())
      <div class="p-4 border-t border-gray-100">
        {{ $expenses->links() }}
      </div>
    @endif
  </div>

</div>

{{-- MODAL: Add / Edit Expense --}}
<div id="expenseModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
  <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
      <div>
        <h3 class="font-extrabold text-gray-900 text-base" id="expenseModalTitle">Log Business Expense</h3>
        <p class="text-xs text-gray-500">Record marketing ad spend, sourcing trips, or packaging costs</p>
      </div>
      <button type="button" onclick="closeExpenseModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
    </div>

    <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="space-y-4" id="expenseForm">
      @csrf

      {{-- Expense Title --}}
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Expense Title / Description <span class="text-rose-500">*</span></label>
        <input type="text" name="title" id="expTitle" required placeholder="e.g. Facebook Boost - Smartwatch Campaign, Chawkbazar Trip" class="w-full text-xs font-bold px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
      </div>

      {{-- Category Selector --}}
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Expense Category <span class="text-rose-500">*</span></label>
        <select name="category" id="expCategory" onchange="onCategorySelect(this.value)" class="w-full text-xs font-bold px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
          @foreach($categories as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Currency Mode Toggle (BDT vs USD for Facebook Ads) --}}
      <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-gray-900">Currency &amp; Amount</label>
          <div class="flex items-center gap-2 text-xs">
            <label class="flex items-center gap-1 cursor-pointer font-bold text-gray-700">
              <input type="radio" name="currency" value="BDT" checked onchange="toggleCurrency('BDT')" class="text-brand-600">
              ৳ BDT
            </label>
            <label class="flex items-center gap-1 cursor-pointer font-bold text-blue-700">
              <input type="radio" name="currency" value="USD" onchange="toggleCurrency('USD')" class="text-blue-600">
              $ USD (Meta Ads)
            </label>
          </div>
        </div>

        {{-- BDT Input Box --}}
        <div id="bdtInputBox">
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-gray-400 text-sm">৳</span>
            <input type="number" step="0.01" name="amount" id="expAmountBDT" placeholder="0.00" class="w-full pl-8 pr-3 py-2 text-sm font-mono font-bold bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-brand-500">
          </div>
        </div>

        {{-- USD Converter Box (Hidden by default, shown when USD is selected) --}}
        <div id="usdInputBox" class="hidden space-y-2">
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-bold text-gray-600 mb-0.5">USD Amount ($)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-blue-600 text-xs">$</span>
                <input type="number" step="0.01" name="currency_amount" id="expAmountUSD" oninput="calcUsdToBdt()" placeholder="10.00" class="w-full pl-7 pr-2 py-1.5 text-xs font-mono font-bold bg-white border border-blue-300 rounded-lg focus:outline-none focus:border-blue-500">
              </div>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Dollar Rate (৳)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-gray-400 text-xs">৳</span>
                <input type="number" step="0.01" name="currency_rate" id="expDollarRate" value="{{ $defaultUsdRate }}" oninput="calcUsdToBdt()" class="w-full pl-7 pr-2 py-1.5 text-xs font-mono font-bold bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500">
              </div>
            </div>
          </div>
          <div class="text-[11px] font-bold text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200 flex items-center justify-between">
            <span>Total Converted BDT:</span>
            <span id="usdConvertedDisplay" class="font-mono font-black text-xs">৳0.00</span>
          </div>
        </div>
      </div>

      {{-- Date & Payment Method --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Date <span class="text-rose-500">*</span></label>
          <input type="date" name="expense_date" id="expDate" required value="{{ date('Y-m-d') }}" class="w-full text-xs font-bold px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1">Payment Method</label>
          <select name="payment_method" id="expPaymentMethod" class="w-full text-xs font-bold px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
            <option value="cash">Cash</option>
            <option value="bkash">bKash</option>
            <option value="nagad">Nagad</option>
            <option value="bank">Bank Transfer</option>
            <option value="card">Card</option>
          </select>
        </div>
      </div>

      {{-- Target Product Attribution (Optional) --}}
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Target Product (Optional)</label>
        <select name="product_id" id="expProduct" class="w-full text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500">
          <option value="">-- General Store Expense (No specific product) --</option>
          @foreach($products as $prod)
            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
          @endforeach
        </select>
        <span class="text-[10px] text-gray-400 block mt-0.5">Select product to measure specific ROAS and campaign profitability</span>
      </div>

      {{-- Receipt Memo Attachment --}}
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Receipt / Memo (Optional)</label>
        <input type="file" name="receipt_attachment" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
      </div>

      {{-- Notes --}}
      <div>
        <label class="block text-xs font-bold text-gray-700 mb-1">Notes / Details</label>
        <textarea name="notes" id="expNotes" rows="2" placeholder="e.g. CNG fare from Farmgate to Chawkbazar, 500 pcs size 12x16 poly mailers..." class="w-full text-xs px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:border-brand-500"></textarea>
      </div>

      {{-- Action Buttons --}}
      <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
        <button type="button" onclick="closeExpenseModal()" class="flex-1 py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors">
          Cancel
        </button>
        <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition-all">
          Save Expense
        </button>
      </div>

    </form>
  </div>
</div>

{{-- MODAL: Lightbox Receipt Viewer --}}
<div id="receiptPreviewModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4 hidden" onclick="closeReceiptPreview()">
  <div class="max-w-2xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl p-4 space-y-3" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between border-b pb-2">
      <h4 class="text-sm font-bold text-gray-800" id="receiptPreviewTitle">Expense Voucher</h4>
      <button type="button" onclick="closeReceiptPreview()" class="text-gray-400 hover:text-gray-600 font-black text-xl">&times;</button>
    </div>
    <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-gray-50 rounded-xl p-2">
      <img id="receiptPreviewImg" src="" alt="Receipt" class="max-w-full h-auto rounded-lg shadow-sm">
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
