<!DOCTYPE html>
<html>

<head>
    <title>Laporan Neraca</title>
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
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .total {
            font-weight: 600;
            background-color: #f8f9fa;
        }

        .total td {
            border-top: 2px solid #ddd;
        }

        .notes {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notes h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Neraca</h2>
        <p>Periode: {{ $report->report_period_start->format('d M Y') }} -
            {{ $report->report_period_end->format('d M Y') }}</p>
        <p>Cabang: {{ $report->branch->name }}</p>
    </div>

    <table class="table">
        <tr>
            <th colspan="2">Aset</th>
        </tr>
        <tr>
            <td>Total Aset</td>
            <td>{{ number_format($balanceSheet->first()->total_assets, 2) }}</td>
        </tr>

        <tr>
            <th colspan="2">Kewajiban</th>
        </tr>
        <tr>
            <td>Total Kewajiban</td>
            <td>{{ number_format($balanceSheet->first()->total_liabilities, 2) }}</td>
        </tr>

        <tr>
            <th colspan="2">Ekuitas</th>
        </tr>
        <tr>
            <td>Total Ekuitas</td>
            <td>{{ number_format($balanceSheet->first()->total_equity, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Company</th>
                <th>Branch</th>
                <th>Total Assets</th>
                <th>Total Liabilities</th>
                <th>Total Equity</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($balanceSheet as $index => $sheet)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sheet->company->name }}</td>
                    <td>{{ $sheet->branch->name }}</td>
                    <td>{{ number_format($sheet->total_assets, 2) }}</td>
                    <td>{{ number_format($sheet->total_liabilities, 2) }}</td>
                    <td>{{ number_format($sheet->total_equity, 2) }}</td>
                    <td>{{ $sheet->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3">Total</td>
                <td>{{ number_format($balanceSheet->sum('total_assets'), 2) }}</td>
                <td>{{ number_format($balanceSheet->sum('total_liabilities'), 2) }}</td>
                <td>{{ number_format($balanceSheet->sum('total_equity'), 2) }}</td>
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
