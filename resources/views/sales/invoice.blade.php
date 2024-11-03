<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice for Sale #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .invoice-header {
            border-bottom: 3px solid #3498db;
            padding-bottom: 25px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .invoice-title {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 600;
            background: linear-gradient(45deg, #3498db, #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .customer-details {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .customer-details p {
            margin: 8px 0;
            color: #34495e;
        }

        .invoice-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 25px 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 15px;
            border: 1px solid #e0e0e0;
        }

        .invoice-table th {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }

        .invoice-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .invoice-table tr:hover {
            background-color: #f1f4f6;
            transition: background-color 0.3s ease;
        }

        .total-amount {
            text-align: right;
            font-size: 18px;
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #3498db;
        }

        .total-amount p {
            margin: 10px 0;
            color: #34495e;
        }

        .total-amount p:last-child {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #e0e0e0;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div>
                <h1 class="invoice-title">Invoice for Sale #{{ $sale->id }}</h1>
                <div class="customer-details">
                    <p><strong>Customer:</strong> {{ $sale->customer->name }}</p>
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('d M Y') }}</p>
                    <p><strong>Invoice Number:</strong> INV-{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sale->saleItems ?? [] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>IDR {{ number_format($item->price, 2) }}</td>
                        <td>IDR {{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">No items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="total-amount">
            <p><strong>Subtotal:</strong> IDR {{ number_format($sale->total_amount - ($sale->tax ?? 0), 2) }}</p>
            @if (isset($sale->tax))
                <p><strong>Tax:</strong> IDR {{ number_format($sale->tax, 2) }}</p>
            @endif
            <p><strong>Total Amount:</strong> IDR {{ number_format($sale->total_amount, 2) }}</p>
        </div>
    </div>
</body>

</html>
