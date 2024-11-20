<!DOCTYPE html>
<html>
<head>
    <title>Employee Attendance Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-info {
            margin-bottom: 20px;
            font-size: 12px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td,
        .attendance-table th, .attendance-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12px;
        }
        .summary-table th, .attendance-table th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .status-present { color: #059669; }
        .status-late { color: #d97706; }
        .status-absent { color: #dc2626; }
        .status-leave { color: #3b82f6; }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee Attendance Report</h1>
        <div class="company-info">
            <p><strong>Company:</strong> {{ $company }}</p>
            <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($reportData['period']['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($reportData['period']['end'])->format('d M Y') }}</p>
            <p><strong>Generated:</strong> {{ $generated_at }} by {{ $generated_by }}</p>
        </div>
    </div>

    <h2>Attendance Summary</h2>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Present Days</th>
                <th>Late Days</th>
                <th>Absent Days</th>
                <th>Leave Days</th>
                <th>Total Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['summary'] as $employee)
                <tr>
                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                    <td class="status-present">{{ $employee->present_days }}</td>
                    <td class="status-late">{{ $employee->late_days }}</td>
                    <td class="status-absent">{{ $employee->absent_days }}</td>
                    <td class="status-leave">{{ $employee->leave_days }}</td>
                    <td>{{ $employee->total_days }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>Detailed Attendance Records</h2>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Status</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['attendances'] as $attendance)
                <tr>
                    <td>{{ $attendance->first_name }} {{ $attendance->last_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                    <td>{{ $attendance->check_in }}</td>
                    <td>{{ $attendance->check_out }}</td>
                    <td class="status-{{ $attendance->status }}">{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>Generated from {{ config('app.name') }} on {{ $generated_at }}</p>
    </div>
</body>
</html>
