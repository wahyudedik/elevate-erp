<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $salesTransaction->id }}</title>
    <style>
        /* Tambahkan CSS untuk styling invoice di sini */
    </style>
</head>

<body>
    <h1>Invoice #{{ $salesTransaction->id }}</h1>
    <p>Date: {{ $salesTransaction->created_at->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @if ($salesTransaction->salesItem->isNotEmpty())
                @foreach ($salesTransaction->salesItem as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>IDR {{ number_format($item->unit_price, 2) }}</td>
                        <td>IDR {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <p>No items found for this transaction.</p>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td>IDR {{ number_format($salesTransaction->salesItem->sum('total_price'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
