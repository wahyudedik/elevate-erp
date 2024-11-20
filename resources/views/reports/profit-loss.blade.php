<!DOCTYPE html>
<html>
<head>
    <title>Profit and Loss Statement</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 12px; }
        .table th { background-color: #f4f4f4; }
        .total-row { font-weight: bold; background-color: #f8f8f8; }
        .profit { color: #1a56db; }
        .loss { color: #dc2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Profit and Loss Statement</h1>
        <p>Period: {{ \Carbon\Carbon::parse($reportData['period']['start'])->format('d M Y') }} - 
           {{ \Carbon\Carbon::parse($reportData['period']['end'])->format('d M Y') }}</p>
    </div>

    <table class="table">
        <tr>
            <th colspan="2">Revenue</th>
        </tr>
        <tr>
            <td>Total Revenue</td>
            <td>Rp {{ number_format($reportData['revenue'], 2) }}</td>
        </tr>
        <tr>
            <th colspan="2">Expenses</th>
        </tr>
        <tr>
            <td>Total Expenses</td>
            <td>Rp {{ number_format($reportData['expenses'], 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Income/Loss</td>
            <td class="{{ $reportData['net_income'] >= 0 ? 'profit' : 'loss' }}">
                Rp {{ number_format($reportData['net_income'], 2) }}
            </td>
        </tr>
    </table>
</body>
</html>
