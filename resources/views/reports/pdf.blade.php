<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #226f54;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #226f54;
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .summary h2 {
            color: #226f54;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .summary-item .value {
            font-size: 24px;
            font-weight: bold;
        }

        .income { color: #87c38f; }
        .expense { color: #da2c38; }
        .balance { color: #226f54; }
        .balance.negative { color: #da2c38; }

        .categories {
            margin-bottom: 30px;
        }

        .categories h3 {
            color: #226f54;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-name {
            font-weight: 500;
        }

        .category-amount {
            font-weight: bold;
        }

        .transactions {
            margin-top: 30px;
        }

        .transactions h3 {
            color: #226f54;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .transactions-table th {
            background-color: #226f54;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        .transactions-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .transactions-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .type-income {
            background-color: #87c38f;
            color: white;
        }

        .type-expense {
            background-color: #da2c38;
            color: white;
        }

        .amount-income {
            color: #87c38f;
            font-weight: bold;
        }

        .amount-expense {
            color: #da2c38;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Expense Report</h1>
    <p>Generated for: {{ $user->name }}</p>
    <p>Period: {{ $start_date }} - {{ $end_date }}</p>
    <p>Generated on: {{ now()->format('M j, Y \a\t g:i A') }}</p>
</div>

<div class="summary">
    <h2>Summary</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Total Income</div>
            <div class="value income">${{ number_format($total_income, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Expenses</div>
            <div class="value expense">${{ number_format($total_expense, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Net Balance</div>
            <div class="value balance {{ $net_balance < 0 ? 'negative' : '' }}">
                ${{ number_format(abs($net_balance), 2) }}
                @if($net_balance < 0) (Loss) @else (Profit) @endif
            </div>
        </div>
    </div>
</div>

@if($income_by_category->count() > 0 || $expense_by_category->count() > 0)
    <div class="categories">
        <h3>Category Breakdown</h3>
        <div class="category-grid">
            @if($income_by_category->count() > 0)
                <div>
                    <h4 style="color: #87c38f; margin-bottom: 10px;">Income Categories</h4>
                    @foreach($income_by_category as $category)
                        <div class="category-item">
                            <span class="category-name">{{ $category['category_name'] }}</span>
                            <span class="category-amount income">${{ number_format($category['total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($expense_by_category->count() > 0)
                <div>
                    <h4 style="color: #da2c38; margin-bottom: 10px;">Expense Categories</h4>
                    @foreach($expense_by_category as $category)
                        <div class="category-item">
                            <span class="category-name">{{ $category['category_name'] }}</span>
                            <span class="category-amount expense">${{ number_format($category['total'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

@if($transactions->count() > 0)
    <div class="transactions">
        <h3>Transaction Details ({{ $transactions->count() }} transactions)</h3>
        <table class="transactions-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}</td>
                    <td>
                        <span class="type-badge type-{{ $transaction->type }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </td>
                    <td>{{ $transaction->category->name }}</td>
                    <td>{{ $transaction->description ?: '-' }}</td>
                    <td style="text-align: right;" class="amount-{{ $transaction->type }}">
                        {{ $transaction->type === 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="footer">
    <p>This report was generated by ExpenseTracker on {{ now()->format('M j, Y \a\t g:i A') }}</p>
</div>
</body>
</html>
