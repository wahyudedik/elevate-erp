<!DOCTYPE html>
<html>

<head>
    <title>Entri Jurnal #{{ $journalEntry->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f4f8;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
            border-bottom: 2px solid #e5e9f0;
            padding-bottom: 25px;
        }

        .header h2 {
            color: #2c3e50;
            margin: 0;
            font-weight: 600;
            font-size: 24px;
        }

        .details {
            margin-bottom: 35px;
            background-color: #f8fafc;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e5e9f0;
        }

        .details p {
            margin: 12px 0;
            color: #4a5568;
            font-size: 15px;
        }

        .details strong {
            color: #2d3748;
            width: 140px;
            display: inline-block;
            font-weight: 600;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            padding: 15px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .table td {
            padding: 15px;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .table tr:hover {
            background-color: #f8fafc;
            transition: background-color 0.2s ease;
        }

        .amount {
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Detail Entri Jurnal</h2>
        </div>

        <div class="details">
            <p><strong>Tanggal Entri:</strong> {{ $journalEntry->entry_date->format('d M Y') }}</p>
            <p><strong>Cabang:</strong> {{ $journalEntry->branch->name }}</p>
            <p><strong>Keterangan:</strong> {{ $journalEntry->description }}</p>
        </div>

        <table class="table">
            <tr>
                <th>Akun</th>
                <th>Jenis</th>
                <th>Jumlah</th>
            </tr>
            <tr>
                <td>{{ $journalEntry->account->account_name }}</td>
                <td>{{ ucfirst($journalEntry->entry_type) }}</td>
                <td class="amount">Rp {{ number_format($journalEntry->amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
