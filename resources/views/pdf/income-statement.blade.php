<!DOCTYPE html>
<html>

<head>
    <title>Laba Rugi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .header p {
            color: #666;
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #2c3e50;
            color: #fff;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .table tbody tr:hover {
            background-color: #f5f6f7;
        }

        .total {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .total td {
            border-top: 2px solid #2c3e50;
        }

        .notes {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notes h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .notes p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Laba Rugi</h2>
        <p>Periode: {{ $report->report_period_start->format('d M Y') }} -
            {{ $report->report_period_end->format('d M Y') }}</p>
        <p>Cabang: {{ $report->branch->name }}</p>
    </div>

    <table class="table">
        <tr>
            <th>Keterangan</th>
            <th>Jumlah</th>
        </tr>
        <tr>
            <td>Total Pendapatan</td>
            <td>{{ number_format($incomeStatement->first()->total_revenue, 2) }}</td>
        </tr>
        <tr>
            <td>Total Pengeluaran</td>
            <td>({{ number_format($incomeStatement->first()->total_expenses, 2) }})</td>
        </tr>
        <tr class="total">
            <td>Laba Bersih</td>
            <td>{{ number_format($incomeStatement->first()->net_income, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Branch</th>
                <th>Total Revenue</th>
                <th>Total Expenses</th>
                <th>Net Income</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incomeStatement as $index => $statement)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $statement->branch->name }}</td>
                    <td>{{ number_format($statement->total_revenue, 2) }}</td>
                    <td>({{ number_format($statement->total_expenses, 2) }})</td>
                    <td class="total">{{ number_format($statement->net_income, 2) }}</td>
                    <td>{{ $statement->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">Total</td>
                <td>{{ number_format($incomeStatement->sum('total_revenue'), 2) }}</td>
                <td>({{ number_format($incomeStatement->sum('total_expenses'), 2) }})</td>
                <td>{{ number_format($incomeStatement->sum('net_income'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if ($report->notes)
        <div class="notes">
            <h4>Catatan:</h4>
            <p>{!! $report->notes !!}</p>
        </div>
    @endif
</body>

</html>
