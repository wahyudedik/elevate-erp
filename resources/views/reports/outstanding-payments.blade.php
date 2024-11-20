<!DOCTYPE html>
<html>

<head>
    <title>Outstanding Payments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f8f8f8;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Outstanding Payments Report</h1>
        <p>{{ $company }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p>Total Outstanding Amount: Rp {{ number_format($reportData['summary']['total_outstanding'], 2) }}</p>
        <p>Total Pending Transactions: {{ $reportData['summary']['total_transactions'] }}</p>
        <p>Number of Suppliers: {{ $reportData['summary']['suppliers_count'] }}</p>
        <p>Average Outstanding Amount: Rp {{ number_format($reportData['summary']['average_amount'], 2) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Supplier Code</th>
                <th>Transaction Date</th>
                <th>Amount</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['payments'] as $payment)
                <tr>
                    <td>{{ $payment->supplier_name }}</td>
                    <td>{{ $payment->supplier_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('d M Y') }}</td>
                    <td>Rp {{ number_format($payment->total_amount, 2) }}</td>
                    <td>{{ ucfirst($payment->transaction_type) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <p>Generated by: {{ $generated_by }}</p>
        <p>Generated at: {{ $generated_at }}</p>
    </div>
</body>

</html>
