<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');
        $range = $request->input('range', 'this_month');
        $search = trim((string) $request->input('search', ''));

        [$startDate, $endDate, $rangeLabel] = $this->resolveDateRange($range, $request);

        $query = Expense::with(['product', 'creator'])
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        // Summary metrics for the active date range (regardless of current category filter)
        $summaryQuery = Expense::whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        $totalExpenses = (float) (clone $summaryQuery)->sum('amount');
        $marketingTotal = (float) (clone $summaryQuery)->where('category', 'marketing')->sum('amount');
        $sourcingTotal  = (float) (clone $summaryQuery)->where('category', 'sourcing_travel')->sum('amount');
        $packagingTotal = (float) (clone $summaryQuery)->where('category', 'packaging')->sum('amount');
        $operationsTotal = $totalExpenses - ($marketingTotal + $sourcingTotal + $packagingTotal);

        $expenses = $query->orderBy('expense_date', 'desc')->latest('id')->paginate(20)->withQueryString();

        $products = Product::where('is_published', true)->orderBy('name')->get(['id', 'name', 'sku']);
        $categories = Expense::CATEGORIES;
        $defaultUsdRate = (float) Setting::get('pos_usd_rate', 125.00);

        return view('admin.expenses.index', compact(
            'expenses',
            'categories',
            'products',
            'category',
            'range',
            'rangeLabel',
            'startDate',
            'endDate',
            'totalExpenses',
            'marketingTotal',
            'sourcingTotal',
            'packagingTotal',
            'operationsTotal',
            'defaultUsdRate'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'category'           => ['required', 'string', 'in:' . implode(',', array_keys(Expense::CATEGORIES))],
            'amount'             => ['nullable', 'numeric', 'min:0'],
            'currency'           => ['required', 'string', 'in:BDT,USD'],
            'currency_amount'    => ['nullable', 'numeric', 'min:0'],
            'currency_rate'      => ['nullable', 'numeric', 'min:1'],
            'payment_method'     => ['nullable', 'string', 'max:50'],
            'expense_date'       => ['required', 'date'],
            'product_id'         => ['nullable', 'exists:products,id'],
            'receipt_attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $currency = $validated['currency'];
        $amount = (float) ($validated['amount'] ?? 0);
        $currencyAmount = isset($validated['currency_amount']) ? (float)$validated['currency_amount'] : null;
        $currencyRate = isset($validated['currency_rate']) ? (float)$validated['currency_rate'] : 125.00;

        if ($currency === 'USD' && $currencyAmount > 0) {
            $amount = round($currencyAmount * $currencyRate, 2);
        }

        if ($amount <= 0 && $currencyAmount > 0) {
            $amount = round($currencyAmount * $currencyRate, 2);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt_attachment')) {
            $receiptPath = $request->file('receipt_attachment')->store('receipts', 'public');
        }

        Expense::create([
            'title'              => $validated['title'],
            'category'           => $validated['category'],
            'amount'             => $amount,
            'currency'           => $currency,
            'currency_amount'    => $currencyAmount,
            'currency_rate'      => $currencyRate,
            'payment_method'     => $validated['payment_method'] ?? 'cash',
            'expense_date'       => $validated['expense_date'],
            'product_id'         => $validated['product_id'] ?? null,
            'receipt_attachment' => $receiptPath,
            'notes'              => $validated['notes'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense \"{$validated['title']}\" (৳" . number_format($amount, 2) . ") recorded successfully.");
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'category'           => ['required', 'string', 'in:' . implode(',', array_keys(Expense::CATEGORIES))],
            'amount'             => ['nullable', 'numeric', 'min:0'],
            'currency'           => ['required', 'string', 'in:BDT,USD'],
            'currency_amount'    => ['nullable', 'numeric', 'min:0'],
            'currency_rate'      => ['nullable', 'numeric', 'min:1'],
            'payment_method'     => ['nullable', 'string', 'max:50'],
            'expense_date'       => ['required', 'date'],
            'product_id'         => ['nullable', 'exists:products,id'],
            'receipt_attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $currency = $validated['currency'];
        $amount = (float) ($validated['amount'] ?? 0);
        $currencyAmount = isset($validated['currency_amount']) ? (float)$validated['currency_amount'] : null;
        $currencyRate = isset($validated['currency_rate']) ? (float)$validated['currency_rate'] : 125.00;

        if ($currency === 'USD' && $currencyAmount > 0) {
            $amount = round($currencyAmount * $currencyRate, 2);
        }

        $receiptPath = $expense->receipt_attachment;
        if ($request->hasFile('receipt_attachment')) {
            if ($receiptPath && Storage::disk('public')->exists($receiptPath)) {
                Storage::disk('public')->delete($receiptPath);
            }
            $receiptPath = $request->file('receipt_attachment')->store('receipts', 'public');
        }

        $expense->update([
            'title'              => $validated['title'],
            'category'           => $validated['category'],
            'amount'             => $amount,
            'currency'           => $currency,
            'currency_amount'    => $currencyAmount,
            'currency_rate'      => $currencyRate,
            'payment_method'     => $validated['payment_method'] ?? 'cash',
            'expense_date'       => $validated['expense_date'],
            'product_id'         => $validated['product_id'] ?? null,
            'receipt_attachment' => $receiptPath,
            'notes'              => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Expense updated successfully.");
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_attachment && Storage::disk('public')->exists($expense->receipt_attachment)) {
            Storage::disk('public')->delete($expense->receipt_attachment);
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense entry removed.');
    }

    public function exportCsv(Request $request)
    {
        $range = $request->input('range', 'this_month');
        $category = $request->input('category', 'all');

        [$startDate, $endDate] = $this->resolveDateRange($range, $request);

        $query = Expense::with(['product', 'creator'])
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $expenses = $query->orderBy('expense_date', 'asc')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="marty-expenses-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($expenses) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Date', 'Title', 'Category', 'Amount (BDT)', 'Currency', 'Original Amount', 'Dollar Rate', 'Payment Method', 'Target Product', 'Created By', 'Notes']);

            foreach ($expenses as $e) {
                fputcsv($out, [
                    $e->id,
                    $e->expense_date->format('Y-m-d'),
                    $e->title,
                    $e->categoryLabel(),
                    number_format($e->amount, 2, '.', ''),
                    $e->currency,
                    $e->currency_amount ? number_format($e->currency_amount, 2, '.', '') : '',
                    $e->currency_rate ? number_format($e->currency_rate, 2, '.', '') : '',
                    ucfirst($e->payment_method ?? 'Cash'),
                    $e->product?->name ?? 'General Store',
                    $e->creator?->name ?? 'Admin',
                    $e->notes ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function resolveDateRange(string $range, Request $request): array
    {
        $today = Carbon::today();

        return match ($range) {
            'today' => [
                $today->copy()->startOfDay(),
                $today->copy()->endOfDay(),
                'Today'
            ],
            'yesterday' => [
                $today->copy()->subDay()->startOfDay(),
                $today->copy()->subDay()->endOfDay(),
                'Yesterday'
            ],
            'this_week' => [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
                'This Week'
            ],
            'last_week' => [
                $today->copy()->subWeek()->startOfWeek(),
                $today->copy()->subWeek()->endOfWeek(),
                'Last Week'
            ],
            'this_month' => [
                $today->copy()->startOfMonth(),
                $today->copy()->endOfMonth(),
                $today->format('F Y')
            ],
            'last_month' => [
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->subMonth()->endOfMonth(),
                $today->copy()->subMonth()->format('F Y')
            ],
            'custom' => [
                $request->has('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : $today->copy()->startOfMonth(),
                $request->has('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : $today->copy()->endOfDay(),
                'Custom Range'
            ],
            default => [
                $today->copy()->startOfMonth(),
                $today->copy()->endOfMonth(),
                $today->format('F Y')
            ]
        };
    }
}
