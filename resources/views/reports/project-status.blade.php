<!DOCTYPE html>
<html>
<head>
    <title>Project Status Report</title>
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
        .company {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            color: #444;
        }
        .meta-info {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-box {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        .table th {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-in-progress { background: #dbeafe; color: #1e40af; }
        .status-on-hold { background: #fef9c3; color: #854d0e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .progress-bar {
            width: 100%;
            background-color: #f3f4f6;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $company }}</div>
        <div class="report-title">Project Status Report</div>
        <div class="meta-info">
            Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}<br>
            Generated: {{ $generated_at }} by {{ $generated_by }}
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div class="summary-label">Total Projects</div>
            <div class="summary-value">{{ $reportData['summary']['total_projects'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Active Projects</div>
            <div class="summary-value">{{ $reportData['summary']['active_projects'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Completed Projects</div>
            <div class="summary-value">{{ $reportData['summary']['completed_projects'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">On Hold Projects</div>
            <div class="summary-value">{{ $reportData['summary']['on_hold_projects'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Cancelled Projects</div>
            <div class="summary-value">{{ $reportData['summary']['cancelled_projects'] }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Average Completion</div>
            <div class="summary-value">{{ number_format($reportData['summary']['average_completion'], 1) }}%</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Client</th>
                <th>Manager</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['projects'] as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->client_name }}</td>
                    <td>{{ $project->manager_first_name }} {{ $project->manager_last_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</td>
                    <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</td>
                    <td>
                        <span class="status-badge status-{{ $project->status }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $project->completion_percentage }}%"></div>
                        </div>
                        {{ number_format($project->completion_percentage, 1) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
