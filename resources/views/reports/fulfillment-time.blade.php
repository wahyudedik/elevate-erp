<!DOCTYPE html>
<html>
<head>
    <title>Fulfillment Time Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 2cm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .company {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            color: #444;
        }
        .meta-info {
            margin: 10px 0;
            color: #666;
            font-size: 12px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f8f8f8;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        .table th {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $company }}</div>
        <div class="report-title">Fulfillment Time Report</div>
        <div class="meta-info">
            Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}<br>
            Generated: {{ $generated_at }} by {{ $generated_by }}
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div class="summary-label">Average Fulfillment Time</div>
            <div class="summary-value">{{ number_format($reportData['summary']['average_time'], 1) }} hours</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Orders Under 24h</div>
            <div class="summary-value">{{ number_format($reportData['summary']['orders_under_24h']) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Orders Over 48h</div>
            <div class="summary-value">{{ number_format($reportData['summary']['orders_over_48h']) }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Fulfillment Date</th>
                <th>Time Taken (hours)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['orders'] as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->fulfillment_date)->format('d M Y H:i') }}</td>
                    <td>{{ number_format($order->fulfillment_hours, 1) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
