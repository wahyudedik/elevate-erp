<!DOCTYPE html>
<html>
<head>
    <title>Stock Movement Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
        .summary-box {
            margin: 20px 0;
            padding: 15px;
            background: #f8f8f8;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .summary-item {
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
        .type-addition {
            color: green;
        }
        .type-deduction {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Movement Report</h1>
        <div class="company-info">
            <h3>{{ $company }}</h3>
            <p>Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</p>
        </div>
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total Movements:</strong> {{ number_format($reportData['summary']['total_movements']) }}
            </div>
            <div class="summary-item">
                <strong>Stock Additions:</strong> {{ number_format($reportData['summary']['total_additions']) }}
            </div>
            <div class="summary-item">
                <strong>Stock Deductions:</strong> {{ number_format($reportData['summary']['total_deductions']) }}
            </div>
            <div class="summary-item">
                <strong>Net Change:</strong> {{ number_format($reportData['summary']['net_change']) }}
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>SKU</th>
                <th>Before</th>
                <th>After</th>
                <th>Type</th>
                <th>Remarks</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['movements'] as $movement)
                <tr>
                    <td>{{ $movement->item_name }}</td>
                    <td>{{ $movement->sku }}</td>
                    <td>{{ number_format($movement->quantity_before) }}</td>
                    <td>{{ number_format($movement->quantity_after) }}</td>
                    <td class="type-{{ $movement->transaction_type }}">
                        {{ ucfirst($movement->transaction_type) }}
                    </td>
                    <td>{{ $movement->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($movement->transaction_date)->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by: {{ $generated_by }} | Date: {{ $generated_at }}</p>
    </div>
</body>
</html>
