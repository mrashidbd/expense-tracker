<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $userId = auth()->id();
        $now = Carbon::now();

        // Current month summary
        $currentMonthIncome = Transaction::forUser($userId)
            ->income()
            ->currentMonth()
            ->sum('amount');

        $currentMonthExpense = Transaction::forUser($userId)
            ->expense()
            ->currentMonth()
            ->sum('amount');

        $currentMonthBalance = $currentMonthIncome - $currentMonthExpense;

        // Recent transactions
        $recentTransactions = Transaction::with('category')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        // Current month category breakdown for charts
        $incomeByCategory = Transaction::with('category')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'income')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->selectRaw('transactions.category_id, categories.name as category_name, categories.color, SUM(transactions.amount) as total')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.color')
            ->get();

        $expenseByCategory = Transaction::with('category')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->selectRaw('transactions.category_id, categories.name as category_name, categories.color, SUM(transactions.amount) as total')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.color')
            ->get();

        // Yearly data for yearly chart (last 12 months)
        $yearlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthIncome = Transaction::forUser($userId)
                ->income()
                ->forMonth($date->year, $date->month)
                ->sum('amount');

            $monthExpense = Transaction::forUser($userId)
                ->expense()
                ->forMonth($date->year, $date->month)
                ->sum('amount');

            $yearlyData[] = [
                'month' => $date->format('M Y'),
                'income' => (float) $monthIncome,
                'expense' => (float) $monthExpense,
                'balance' => (float) ($monthIncome - $monthExpense)
            ];
        }

        // Categories for quick add transaction
        $categories = Category::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return Inertia::render('Dashboard', [
            'currentMonth' => [
                'income' => (float) $currentMonthIncome,
                'expense' => (float) $currentMonthExpense,
                'balance' => (float) $currentMonthBalance,
                'month' => $now->format('F'),
                'year' => $now->year
            ],
            'recentTransactions' => $recentTransactions,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'yearlyData' => $yearlyData,
            'categories' => $categories
        ]);
    }

    /**
     * Get dashboard data via AJAX
     */
    public function data()
    {
        $userId = auth()->id();
        $now = Carbon::now();

        // Current month summary
        $currentMonthIncome = Transaction::forUser($userId)
            ->income()
            ->currentMonth()
            ->sum('amount');

        $currentMonthExpense = Transaction::forUser($userId)
            ->expense()
            ->currentMonth()
            ->sum('amount');

        $currentMonthBalance = $currentMonthIncome - $currentMonthExpense;

        return response()->json([
            'currentMonth' => [
                'income' => (float) $currentMonthIncome,
                'expense' => (float) $currentMonthExpense,
                'balance' => (float) $currentMonthBalance,
                'month' => $now->format('F'),
                'year' => $now->year
            ]
        ]);
    }
}
