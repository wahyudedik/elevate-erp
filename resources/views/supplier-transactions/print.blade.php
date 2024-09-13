<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Transaction #{{ $supplierTransaction->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .transaction-details { margin-bottom: 20px; }
        .transaction-details table { width: 100%; border-collapse: collapse; }
        .transaction-details th, .transaction-details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .footer { text-align: center; margin-top: 20px; font-size: 0.8em; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Transaction</h1>
    </div>

    <div class="transaction-details">
        <table>
            <tr>
                <th>Transaction ID</th>
                <td>{{ $supplierTransaction->id }}</td>
            </tr>
            <tr>
                <th>Transaction Code</th>
                <td>{{ $supplierTransaction->transaction_code }}</td>
            </tr>
            <tr>
                <th>Supplier</th>
                <td>{{ $supplierTransaction->supplier->supplier_name }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{ $supplierTransaction->transaction_type }}</td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>IDR {{ number_format($supplierTransaction->amount, 2) }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $supplierTransaction->transaction_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <th>Notes</th>
                <td>{{ $supplierTransaction->notes }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
