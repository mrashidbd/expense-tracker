<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Report - {{ $start_date }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', Arial, -apple-system, BlinkMacSystemFont, sans-serif;
        }

        @page {
            margin: 48pt;
            size: A4;
        }

        @page :left {
            margin: 48pt;
        }

        @page :right {
            margin: 48pt;
        }

        body {
            font-family: 'DejaVu Sans', Arial, -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #1f2937;
            background-color: #ffffff;
        }

        .container {
            width: 100%;
            font-family: 'DejaVu Sans', Arial, -apple-system, BlinkMacSystemFont, sans-serif;
            padding: 0;
            margin: 0;
        }

        /* Force page break */
        .page-break {
            page-break-before: always;
        }

        /* Header Styles */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .logo-section {
            display: table-cell;
            vertical-align: middle;
        }

        .logo {
            width: auto;
            height: 50px;
            vertical-align: middle;
            margin-right: 15px;
        }

        .logo-placeholder {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-color: #7c3aed;
            color: white;
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            font-size: 18px;
            border-radius: 8px;
            vertical-align: middle;
            margin-right: 15px;
        }

        .app-name {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #7c3aed;
            vertical-align: middle;
        }

        .report-title {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        .report-title h1 {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: #374151;
        }

        .report-period {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 16px;
            color: #6b7280;
        }

        .user-info {
            margin-top: 10px;
        }

        .user-name {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .user-email {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }

        /* Summary Cards */
        .summary-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            table-layout: fixed;
        }

        .summary-card {
            display: table-cell;
            padding: 20px;
            background-color: #f9fafb;
            text-align: center;
            vertical-align: top;
            border: 1px solid #e5e7eb;
        }

        .summary-card + .summary-card {
            border-left: none;
        }

        .summary-card:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .summary-card:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .summary-label {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .summary-amount {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .income-amount {
            color: #10b981;
        }

        .expense-amount {
            color: #ef4444;
        }

        .balance-amount {
            color: #564e97;
        }

        .summary-type {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #9ca3af;
        }

        /* Breakdown Section */
        .breakdown-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .breakdown-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .breakdown-column {
            display: table-cell;
            vertical-align: top;
            padding: 0 50px;
        }

        .breakdown-column:first-child {
            padding-left: 0;
        }

        .breakdown-column:last-child {
            padding-right: 0;
        }

        .category-title {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .category-item {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-name {
            display: table-cell;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        .category-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }

        .income-indicator {
            background-color: #10b981;
        }

        .expense-indicator {
            background-color: #ef4444;
        }

        .category-amount {
            display: table-cell;
            text-align: right;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            vertical-align: middle;
        }

        /* Transactions Table */
        .transactions-section {
            page-break-before: always;
            margin-top: 0;
            padding-top: 0;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .transactions-table thead {
            display: table-header-group;
        }

        .transactions-table tbody {
            display: table-row-group;
        }

        .transactions-table th {
            background-color: #f9fafb;
            padding: 12px;
            text-align: left;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            page-break-inside: avoid;
        }

        .transactions-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .transaction-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .income-type {
            background-color: #d1fae5;
            color: #065f46;
        }

        .expense-type {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .transaction-amount {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-weight: 600;
        }

        .income-transaction {
            color: #10b981;
        }

        .expense-transaction {
            color: #ef4444;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #6b7280;
        }

        .footer p {
            margin-bottom: 5px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                font-family: 'DejaVu Sans', Arial, sans-serif;
            }

            .transactions-table {
                font-size: 12px;
            }

            .transactions-table thead {
                display: table-header-group;
            }

            .transactions-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            @if(file_exists(public_path('logo/logo.png')))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo/logo.png'))) }}" alt="Expense Tracker Logo" class="logo">
            @else
                <div class="logo-placeholder">EexpenseTracker</div>
            @endif
        </div>
        <div class="report-title">
            <h1>Financial Report</h1>
            <p class="report-period">
                {{ $start_date }} - {{ $end_date }}
            </p>
            <div class="user-info">
                <p class="user-name">{{ $user->name }}</p>
                <p class="user-email">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-section">
        <div class="summary-card">
            <div class="summary-label">Total Income</div>
            <div class="summary-amount income-amount">
                Tk.  {{ number_format($total_income, 2) }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Expenses</div>
            <div class="summary-amount expense-amount">
                Tk.  {{ number_format($total_expense, 2) }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Net Balance</div>
            <div class="summary-amount balance-amount">
                Tk.  {{ number_format($net_balance, 2) }}
            </div>
        </div>
    </div>

    <!-- Month's Breakdown -->
    <div class="breakdown-section">
        <h2 class="section-title">Breakdown by Category</h2>
        <div class="breakdown-grid">
            <!-- Income Categories -->
            <div class="breakdown-column">
                <h3 class="category-title">Income Categories</h3>
                @foreach($income_by_category as $category)
                    <div class="category-item">
                        <span class="category-name">
                            <span class="category-indicator income-indicator"></span>
                            {{ $category['category_name'] }}
                        </span>
                        <span class="category-amount">Tk.  {{ number_format($category['total'], 2) }}</span>
                    </div>
                @endforeach
                @if(count($income_by_category) == 0)
                    <div class="category-item">
                        <span class="category-name">No income recorded</span>
                    </div>
                @endif
            </div>

            <!-- Expense Categories -->
            <div class="breakdown-column">
                <h3 class="category-title">Expense Categories</h3>
                @foreach($expense_by_category as $category2)
                    <div class="category-item">
                        <span class="category-name">
                            <span class="category-indicator expense-indicator"></span>
                            {{ $category2['category_name'] }}
                        </span>
                        <span class="category-amount">Tk.  {{ number_format($category2['total'], 2) }}</span>
                    </div>
                @endforeach
                @if(count($expense_by_category) == 0)
                    <div class="category-item">
                        <span class="category-name">No expenses recorded</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="transactions-section">
        <h2 class="section-title">Detail Statement</h2>
        <table class="transactions-table">
            <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 35%;">Description</th>
                <th style="width: 20%;">Category</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->category->name ?? 'Uncategorized' }}</td>
                    <td style="text-align: right;">
                        <span class="transaction-amount {{ $transaction->type == 'income' ? 'income-transaction' : 'expense-transaction' }}">
                            Tk.  {{ number_format($transaction->amount, 2) }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->timezone('+06:00')->format('F d, Y \a\t h:i A') }} (GMT+6:00)</p>
        <p>&copy; {{ date('Y') }} Expense Tracker. All rights reserved.</p>
    </div>
</div>
</body>
</html>
