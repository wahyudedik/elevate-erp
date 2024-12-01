<!DOCTYPE html>
<html>

<head>
    <title>Laporan Arus Kas</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f6fa;
            margin: 40px;
            color: #2d3436;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #6c5ce7, #a55eea);
            border-radius: 15px;
            color: white;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .table th,
        .table td {
            border: none;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
        }

        .table th {
            background: #5c6cfa;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .total {
            font-weight: 600;
            color: #2d3436;
            background-color: #f8f9ff;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            padding: 25px;
        }

        .amount {
            font-family: 'Courier New', monospace;
            color: #2d3436;
        }

        .date {
            color: #636e72;
            font-size: 14px;
        }

        .notes {
            background: #fff8f0;
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
        }

        .flow-item {
            padding: 8px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .flow-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="header"
        style="text-align: center; padding: 40px 0; background: linear-gradient(135deg, #f6f8fd 0%, #ffffff 100%); border-radius: 20px; margin-bottom: 30px;">
        <h2 style="font-size: 36px; margin-bottom: 20px; font-weight: 700; color: #1a237e; letter-spacing: -0.5px;">
            Laporan Arus Kas</h2>
        <div
            style="display: inline-block; background: #ffffff; padding: 15px 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <p style="font-size: 16px; color: #424242; margin: 0 0 8px 0;">
                <span style="color: #666; margin-right: 8px;">Periode:</span>
                {{ $report->report_period_start->format('d M Y') }} - {{ $report->report_period_end->format('d M Y') }}
            </p>
            <p style="font-size: 16px; color: #424242; margin: 0;">
                <span style="color: #666; margin-right: 8px;">Cabang:</span>
                {{ $report->branch->name }}
            </p>
        </div>
    </div>

    <div class="card">
        <table class="table">
            <tr>
                <th>Kategori Arus Kas</th>
                <th>Jumlah</th>
            </tr>
            <tr>
                <td>Arus Kas Operasi</td>
                <td class="amount">{{ number_format($cashFlow->first()->operating_cash_flow, 2) }}</td>
            </tr>
            <tr>
                <td>Arus Kas Investasi</td>
                <td class="amount">{{ number_format($cashFlow->first()->investing_cash_flow, 2) }}</td>
            </tr>
            <tr>
                <td>Arus Kas Pendanaan</td>
                <td class="amount">{{ number_format($cashFlow->first()->financing_cash_flow, 2) }}</td>
            </tr>
            <tr class="total">
                <td>Arus Kas Bersih</td>
                <td class="amount">{{ number_format($cashFlow->first()->net_cash_flow, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori Arus Kas</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cashFlow as $index => $flow)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="flow-item">Arus Kas Operasi</div>
                            <div class="flow-item">Arus Kas Investasi</div>
                            <div class="flow-item">Arus Kas Pendanaan</div>
                            <div class="flow-item total">Arus Kas Bersih</div>
                        </td>
                        <td>
                            <div class="flow-item amount">{{ number_format($flow->operating_cash_flow, 2) }}</div>
                            <div class="flow-item amount">{{ number_format($flow->investing_cash_flow, 2) }}</div>
                            <div class="flow-item amount">{{ number_format($flow->financing_cash_flow, 2) }}</div>
                            <div class="flow-item amount total">{{ number_format($flow->net_cash_flow, 2) }}</div>
                        </td>
                        <td class="date">{{ $flow->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($report->notes)
        <div class="notes">
            <h4 style="color: #2d3436; margin-bottom: 15px; font-weight: 600;">Catatan:</h4>
            <p style="line-height: 1.8; color: #636e72;">{!! $report->notes !!}</p>
        </div>
    @endif
</body>

</html>
