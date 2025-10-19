<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = Transaction::with('category')
            ->forUser(auth()->id());

        // Apply filters
        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('month') && $request->has('year')) {
            $query->forMonth($request->year, $request->month);
        } elseif (!$request->has('all')) {
            // Default to current month if no specific month/year provided and 'all' not requested
            $query->currentMonth();
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Calculate totals from ALL filtered transactions (before pagination)
        $allFilteredTransactions = $query->get();
        $totalIncome = $allFilteredTransactions->where('type', 'income')->sum('amount');
        $totalExpense = $allFilteredTransactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Pagination - 20 per page
        $perPage = $request->get('per_page', 20);
        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Get categories for filter dropdown
        $categories = Category::forUser(auth()->id())
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'totals' => [
                'total_income' => (float) $totalIncome,
                'total_expense' => (float) $totalExpense,
                'net_balance' => (float) $netBalance,
                'total_count' => $allFilteredTransactions->count()
            ],
            'filters' => $request->only(['type', 'month', 'year', 'category_id', 'start_date', 'end_date', 'all'])
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $categories = Category::forUser(auth()->id())
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return Inertia::render('Transactions/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'category_id' => $request->category_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'transaction_date' => $request->transaction_date,
            ]);

            DB::commit();
            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create transaction.');
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        if ((int)$transaction->user_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $transaction->load('category');

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        if ((int)$transaction->user_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::forUser(auth()->id())
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        $transaction->load('category');

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ((int)$transaction->user_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date|before_or_equal:today',
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($request) {
                    $category = Category::find($value);
                    if (!$category || (int)$category->user_id !== (int)auth()->id()) {
                        $fail('The selected category is invalid.');
                    }
                    if ($category && $category->type !== $request->type) {
                        $fail('The selected category does not match the transaction type.');
                    }
                }
            ]
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction)
    {
        if ((int)$transaction->user_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully');
    }
}
