<!DOCTYPE html>
<html>
<head>
    <title>Customer Sales Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px;
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
        .summary-stats {
            margin: 20px 0;
            padding: 15px;
            background: #f8f8f8;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        .stat-box {
            float: left;
            width: 30%;
            margin-right: 3%;
            padding: 10px;
            background: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer Sales Report</h1>
        <h3>{{ $company }}</h3>
        <p>Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</p>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <h4>Total Revenue</h4>
            <p>Rp {{ number_format($reportData['total_revenue'], 2) }}</p>
        </div>
        <div class="stat-box">
            <h4>Total Transactions</h4>
            <p>{{ number_format($reportData['total_transactions']) }}</p>
        </div>
        <div class="stat-box">
            <h4>Average Customer Value</h4>
            <p>Rp {{ number_format($reportData['average_customer_value'], 2) }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Purchase Frequency</th>
                <th>Total Spent</th>
                <th>Average Transaction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['customers'] as $customer)
                <tr>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ number_format($customer->purchase_frequency) }}</td>
                    <td>Rp {{ number_format($customer->total_spent, 2) }}</td>
                    <td>Rp {{ number_format($customer->average_transaction, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by: {{ $generated_by }} | Date: {{ $generated_at }}</p>
    </div>
</body>
</html>
