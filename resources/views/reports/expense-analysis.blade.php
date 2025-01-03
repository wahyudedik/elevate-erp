<!DOCTYPE html>
<html>
<head>
    <title>Expense Analysis Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
        .summary-box {
            margin: 20px 0;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
        .chart-container {
            margin: 20px 0;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expense Analysis Report</h1>
        <p>{{ $company }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($reportData['period']['start'])->format('d M Y') }} - 
           {{ \Carbon\Carbon::parse($reportData['period']['end'])->format('d M Y') }}</p>
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <p><strong>Total Expenses:</strong> Rp {{ number_format($reportData['total_expenses'], 2) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Expense Category</th>
                <th>Amount (Rp)</th>
                <th>Percentage (%)</th>
                <th>Transactions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['expenses'] as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ number_format($data['amount'], 2) }}</td>
                    <td>{{ number_format($data['percentage'], 2) }}%</td>
                    <td>{{ $data['transactions'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by: {{ $generated_by }}</p>
        <p>Generated at: {{ $generated_at }}</p>
    </div>
</body>
</html>
