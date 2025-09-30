<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Set default date range (current month)
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get transactions for the selected period
        $transactions = Transaction::with('category')
            ->where('transactions.user_id', $userId)
            ->dateRange($startDate, $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate summary data
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Group by categories
        $incomeByCategory = $transactions
            ->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_id' => $first->category_id,
                    'category_name' => $first->category->name,
                    'color' => $first->category->color,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_id' => $first->category_id,
                    'category_name' => $first->category->name,
                    'color' => $first->category->color,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        // Daily breakdown for chart
        $dailyData = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate <= $endDateCarbon) {
            $dayTransactions = $transactions->where('transaction_date', $currentDate->format('Y-m-d'));
            $dayIncome = $dayTransactions->where('type', 'income')->sum('amount');
            $dayExpense = $dayTransactions->where('type', 'expense')->sum('amount');

            $dailyData[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('M j'),
                'income' => (float)$dayIncome,
                'expense' => (float)$dayExpense,
                'balance' => (float)($dayIncome - $dayExpense)
            ];

            $currentDate->addDay();
        }

        // Monthly comparison (last 12 months)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $monthTransactions = Transaction::where('transactions.user_id', $userId)
                ->dateRange($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'))
                ->get();

            $monthIncome = $monthTransactions->where('type', 'income')->sum('amount');
            $monthExpense = $monthTransactions->where('type', 'expense')->sum('amount');

            $monthlyData[] = [
                'month' => $monthStart->format('M Y'),
                'income' => (float)$monthIncome,
                'expense' => (float)$monthExpense,
                'balance' => (float)($monthIncome - $monthExpense)
            ];
        }

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_income' => (float)$totalIncome,
                'total_expense' => (float)$totalExpense,
                'net_balance' => (float)$netBalance,
                'transaction_count' => $transactions->count(),
                'period' => Carbon::parse($startDate)->format('M j, Y') . ' - ' . Carbon::parse($endDate)->format('M j, Y')
            ],
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'dailyData' => $dailyData,
            'monthlyData' => $monthlyData,
            'transactions' => $transactions->take(100) // Limit for display
        ]);
    }

    /**
     * Preview report as HTML (for development)
     */
    public function preview(Request $request)
    {
        // Set default date range (current month) if not provided
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $userId = auth()->id();

        // Get data for preview (same logic as exportPdf)
        $transactions = Transaction::with('category')
            ->where('transactions.user_id', $userId)
            ->dateRange($startDate, $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Group by categories
        $incomeByCategory = $transactions
            ->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_name' => $first->category->name,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_name' => $first->category->name,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        $data = [
            'user' => auth()->user(),
            'start_date' => Carbon::parse($startDate)->format('M j, Y'),
            'end_date' => Carbon::parse($endDate)->format('M j, Y'),
            'transactions' => $transactions,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $netBalance,
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory
        ];

        // Return the same view but as HTML instead of PDF
        return view('reports.pdf', $data);
    }

    /**
     * Export report as PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $userId = auth()->id();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Get data for PDF
        $transactions = Transaction::with('category')
            ->where('transactions.user_id', $userId)
            ->dateRange($startDate, $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Group by categories
        $incomeByCategory = $transactions
            ->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_name' => $first->category->name,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $first = $categoryTransactions->first();
                return [
                    'category_name' => $first->category->name,
                    'total' => $categoryTransactions->sum('amount'),
                    'count' => $categoryTransactions->count()
                ];
            })
            ->values();

        $data = [
            'user' => auth()->user(),
            'start_date' => Carbon::parse($startDate)->format('M j, Y'),
            'end_date' => Carbon::parse($endDate)->format('M j, Y'),
            'transactions' => $transactions,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $netBalance,
            'income_by_category' => $incomeByCategory,
            'expense_by_category' => $expenseByCategory
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'dejavu sans',
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => false,
                'dpi' => 150,
                'defaultPaperSize' => 'a4',
                'margin_top' => 48,
                'margin_right' => 48,
                'margin_bottom' => 48,
                'margin_left' => 48,
                'chroot' => public_path(),
                'tempDir' => storage_path('app/temp'),
                'fontDir' => storage_path('fonts'),
                'fontCache' => storage_path('fonts'),
                'isFontSubsettingEnabled' => true,
                'debugKeepTemp' => true,
            ]);

        $filename = 'expense-report-' . $startDate . '-to-' . $endDate . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export report as Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        return \App\Http\Controllers\ExportHelper::exportToExcel(
            $request->start_date,
            $request->end_date,
            auth()->id(),
            'xlsx'
        );
    }

    /**
     * Export report as CSV
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        return \App\Http\Controllers\ExportHelper::exportToExcel(
            $request->start_date,
            $request->end_date,
            auth()->id(),
            'csv'
        );
    }
}
