<?php

namespace App\Filament\Resources\FinancialReportResource\Widgets;

use App\Models\ManagementFinancial\FinancialReport;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;


class FinancialReportChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Financial Report';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Financial Report Overview';

    protected static ?string $badge = 'Overview';
    protected static ?string $badgeColor = 'success';
    protected static ?string $badgeIcon = 'heroicon-o-chart-bar';
    protected static ?string $badgeIconPosition = 'after';
    protected static ?string $badgeSize = 'sm';

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All Reports',
            'balance_sheet' => 'Balance Sheet',
            'income_statement' => 'Income Statement',
            'cash_flow' => 'Cash Flow',
        ];
    }
    
    protected function getFilterDefault(): ?string
    {
        return 'all';
    }
    
    protected function getData(): array
    {
        $activeFilter = $this->filter;
    
        $query = FinancialReport::query();
    
        if ($activeFilter !== 'all') {
            $query->where('report_type', $activeFilter);
        }
    
        $data = $query->get();
    
        $reportTypes = ['balance_sheet', 'income_statement', 'cash_flow'];
        $labels = ['Balance Sheet', 'Income Statement', 'Cash Flow'];
        $counts = [];
    
        foreach ($reportTypes as $type) {
            $counts[] = $data->where('report_type', $type)->count();
        }
    
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Financial Reports',
                    'data' => $counts,
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56'],
                    'borderColor' => ['#FF6384', '#36A2EB', '#FFCE56'],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
    

    protected function getType(): string
    {
        return 'line';
    }
}
