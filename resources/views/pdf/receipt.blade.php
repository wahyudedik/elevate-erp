<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Kwitansi #{{ $transaction->transaction_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .receipt-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .company-info {
            margin-bottom: 30px;
            color: #666;
            font-size: 15px;
        }

        .receipt-details {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .receipt-details td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .receipt-details td:first-child {
            font-weight: 600;
            color: #2c3e50;
        }

        .amount-box {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            font-weight: 600;
            font-size: 18px;
            color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .terbilang {
            background-color: #fff;
            padding: 15px;
            border-radius: 6px;
            color: #666;
            font-style: italic;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff;
            border-left: 4px solid #2c3e50;
            border-radius: 4px;
        }

        .footer {
            margin-top: 60px;
            text-align: right;
            color: #666;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            width: 200px;
            margin-left: auto;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="receipt-title">Kwitansi</div>
        <div class="company-info">
            {{ $transaction->company->name }}<br>
            {{ $transaction->branch->name }}<br>
            {!! $transaction->branch->address !!}
        </div>
    </div>

    <table class="receipt-details">
        <tr>
            <td width="150">No. Kwitansi</td>
            <td>: {{ $transaction->transaction_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ $transaction->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>: {{ ucfirst($transaction->status) }}</td>
        </tr>
    </table>

    <div class="amount-box">
        Jumlah: Rp {{ number_format($transaction->amount, 2, ',', '.') }}
    </div>

    <div class="terbilang">
        Terbilang: {{ ucwords(\App\Helpers\Terbilang::make($transaction->amount)) }} Rupiah
    </div>

    @if ($transaction->notes)
        <div class="notes">
            <strong>Catatan:</strong><br>
            {{ $transaction->notes }}
        </div>
    @endif

    <div class="footer">
        {{ $transaction->branch->city }}, {{ $transaction->created_at->format('d/m/Y') }}
        <div class="signature-line">
            Petugas
        </div>
    </div>
</body>

</html>
