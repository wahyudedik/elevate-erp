<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice for Sale #{{ $sale->id }}</title>
    <style>
        /* Add some basic styling for the invoice */
    </style>
</head>
<body>
    <h1>Invoice for Sale #{{ $sale->id }}</h1>
    <p>Customer: {{ $sale->customer->name }}</p>
    <p>Date: {{ $sale->sale_date->format('d M Y') }}</p>
    <p>Total Amount: IDR {{ number_format($sale->total_amount, 2) }}</p>
    <!-- Add more details as needed -->
</body>
</html>
