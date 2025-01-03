<!DOCTYPE html>
<html>

<head>
    <title>Customer Feedback Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .company-info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .metrics-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .metric-box {
            border: 1px solid #ddd;
            padding: 15px;
            width: 23%;
            text-align: center;
            border-radius: 5px;
        }

        .metric-title {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f4f4f4;
        }

        .satisfaction-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
        }

        .high {
            background: #fee2e2;
            color: #991b1b;
        }

        .medium {
            background: #fef9c3;
            color: #854d0e;
        }

        .low {
            background: #dcfce7;
            color: #166534;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Customer Feedback Summary Report</h1>
        <div class="company-info">
            <p><strong>{{ $company }}</strong></p>
            <p>Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</p>
        </div>
    </div>

    <div class="metrics-grid">
        <div class="metric-box">
            <div class="metric-title">Total Feedback</div>
            <div class="metric-value">{{ $reportData['total_feedback'] }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">High Priority</div>
            <div class="metric-value">{{ $reportData['priority_summary']['high_priority'] }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">Medium Priority</div>
            <div class="metric-value">{{ $reportData['priority_summary']['medium_priority'] }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">Low Priority</div>
            <div class="metric-value">{{ $reportData['priority_summary']['low_priority'] }}</div>
        </div>
    </div>

    <h3>Detailed Feedback List</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Description</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['feedback_list'] as $feedback)
                <tr>
                    <td>{{ $feedback->subject }}</td>
                    <td>{{ $feedback->description }}</td>
                    <td>
                        <span class="satisfaction-badge {{ strtolower($feedback->priority) }}">
                            {{ ucfirst($feedback->priority) }}
                        </span>
                    </td>
                    <td>{{ ucfirst($feedback->status) }}</td>
                    <td>{{ \Carbon\Carbon::parse($feedback->created_at)->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by: {{ $generated_by }} | Date: {{ $generated_at }}</p>
    </div>
</body>

</html>
