<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ledger #{{ $ledger->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
        }

        .document-title {
            font-size: 20px;
            margin-top: 10px;
            color: #64748b;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #64748b;
            margin-bottom: 5px;
        }

        .value {
            font-size: 16px;
        }

        .amount {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .transactions-table th {
            background: #f1f5f9;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .transactions-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .transactions-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .summary-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }

        .debit {
            color: #166534;
        }

        .credit {
            color: #991b1b;
        }

        .balance {
            color: #1e40af;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">{{ $ledger->company->name }}</div>
        <div class="document-title">Buku Besar #{{ $ledger->id }}</div>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Cabang</div>
                <div class="value">{{ $ledger->branch->name }}</div>
            </div>
            <div class="info-item">
                <div class="label">Akun</div>
                <div class="value">{{ $ledger->account->account_name }}</div>
            </div>
        </div>
    </div>

    <table class="transactions-table">
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Catatan</th>
                <th style="text-align: right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $transaction->status }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                    <td>{{ $transaction->notes }}</td>
                    <td style="text-align: right">Rp {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Pending</div>
                <div class="summary-value pending">Rp {{ number_format($totalPending, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Completed</div>
                <div class="summary-value completed">Rp {{ number_format($totalCompleted, 2, ',', '.') }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Failed</div>
                <div class="summary-value failed">Rp {{ number_format($totalFailed, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->format('d F Y H:i:s') }}
        <br>
        {{ $ledger->company->name }} - {{ $ledger->branch->name }}
    </div>
</body>

</html>
