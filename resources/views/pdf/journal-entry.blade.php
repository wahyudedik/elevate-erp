<!DOCTYPE html>
<html>

<head>
    <title>Journal Entry #{{ $journalEntry->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .header h2 {
            color: #333;
            margin: 0;
        }

        .details {
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }

        .details p {
            margin: 10px 0;
            color: #444;
        }

        .details strong {
            color: #222;
            width: 120px;
            display: inline-block;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: bold;
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table td {
            padding: 12px;
            border: 1px solid #ddd;
            color: #444;
        }

        .table tr:hover {
            background-color: #f8f8f8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Journal Entry Details</h2>
        </div>

        <div class="details">
            <p><strong>Entry Date:</strong> {{ $journalEntry->entry_date->format('d M Y') }}</p>
            <p><strong>Branch:</strong> {{ $journalEntry->branch->name }}</p>
            <p><strong>Description:</strong> {{ $journalEntry->description }}</p>
        </div>

        <table class="table">
            <tr>
                <th>Account</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>{{ $journalEntry->account->account_name }}</td>
                <td>{{ ucfirst($journalEntry->entry_type) }}</td>
                <td>IDR {{ number_format($journalEntry->amount, 2) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
