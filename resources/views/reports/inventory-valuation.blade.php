<!DOCTYPE html>
<html>
<head>
    <title>Inventory Valuation Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 2cm;
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
        .meta-info {
            margin-bottom: 30px;
            font-size: 0.9em;
            color: #666;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 0.9em;
        }
        .summary-box p {
            margin: 0;
            font-size: 1.2em;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Valuation Report</h1>
        <div class="company-info">{{ $company }}</div>
    </div>

    <div class="meta-info">
        <p>Generated on: {{ $generated_at }}</p>
        <p>Generated by: {{ $generated_by }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <h3>Total Items</h3>
            <p>{{ number_format($reportData['summary']['total_items']) }}</p>
        </div>
        <div class="summary-box">
            <h3>Total Quantity</h3>
            <p>{{ number_format($reportData['summary']['total_quantity']) }}</p>
        </div>
        <div class="summary-box">
            <h3>Total Purchase Value</h3>
            <p>Rp {{ number_format($reportData['summary']['total_purchase_value'], 2) }}</p>
        </div>
        <div class="summary-box">
            <h3>Total Potential Value</h3>
            <p>Rp {{ number_format($reportData['summary']['total_potential_value'], 2) }}</p>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Purchase Price</th>
                <th>Total Value</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['inventories'] as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td>Rp {{ number_format($item->purchase_price, 2) }}</td>
                    <td>Rp {{ number_format($item->total_value, 2) }}</td>
                    <td>{{ $item->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
