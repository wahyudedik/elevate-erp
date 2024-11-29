<!DOCTYPE html>
<html>
<head>
    <title>Milestone Progress Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 2cm;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .meta-info {
            margin-bottom: 20px;
            font-size: 12px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            font-size: 12px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        .table th { 
            background-color: #f4f4f4; 
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            background: #f8f8f8;
        }
        .status-achieved {
            color: #059669;
            font-weight: bold;
        }
        .status-pending {
            color: #D97706;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Milestone Progress Report</h1>
    </div>

    <div class="company-info">
        <h2>{{ $company }}</h2>
    </div>

    <div class="meta-info">
        <p>Period: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</p>
        <p>Generated: {{ $generated_at }}</p>
        <p>Generated by: {{ $generated_by }}</p>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <h3>Total Milestones</h3>
            <p>{{ $reportData['summary']['total_milestones'] }}</p>
        </div>
        <div class="summary-box">
            <h3>Achievement Rate</h3>
            <p>{{ number_format($reportData['summary']['achievement_rate'], 1) }}%</p>
        </div>
        <div class="summary-box">
            <h3>Achieved Milestones</h3>
            <p>{{ $reportData['summary']['achieved_milestones'] }}</p>
        </div>
        <div class="summary-box">
            <h3>Pending Milestones</h3>
            <p>{{ $reportData['summary']['pending_milestones'] }}</p>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Client</th>
                <th>Milestone</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['milestones'] as $milestone)
                <tr>
                    <td>{{ $milestone->project_name }}</td>
                    <td>{{ $milestone->client_name }}</td>
                    <td>{{ $milestone->milestone_name }}</td>
                    <td>{{ $milestone->milestone_description }}</td>
                    <td>{{ \Carbon\Carbon::parse($milestone->milestone_date)->format('d M Y') }}</td>
                    <td class="status-{{ $milestone->status }}">
                        {{ ucfirst($milestone->status) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <div style="margin-top: 20px; font-size: 10px;">
        <p>Report generated using ERP System</p>
        <p>© {{ date('Y') }} {{ $company }}. All rights reserved.</p>
    </div>
</body>
</html>