<?php

namespace App\Filament\Resources\IncomeStatementResource\Widgets;

use App\Models\ManagementFinancial\IncomeStatement;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class IncomeStatementChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Income Statement';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-currency-dollar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Financial Performance';

    protected static ?string $badge = 'Overview';
    protected static ?string $badgeColor = 'success';
    protected static ?string $badgeIcon = 'heroicon-o-chart-bar';
    protected static ?string $badgeIconPosition = 'after';
    protected static ?string $badgeSize = 'sm';


    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'year' => 'This year',
            'quarter' => 'This quarter',
            'month' => 'This month',
        ];
    }

    protected function getData(): array
    {
        $incomeStatements = IncomeStatement::all();

        return [
            'datasets' => $this->getDatasets($incomeStatements),
            'labels' => ['Revenue', 'Expenses', 'Net Income'],
            'colors' => ['#4CAF50', '#F44336', '#2196F3'],
        ];
    }

    private function getDatasets($incomeStatements): array
    {
        return [
            $this->createDataset('Total Revenue', $incomeStatements->pluck('total_revenue'), 'rgba(75, 192, 192, 0.2)', 'rgba(75, 192, 192, 1)'),
            $this->createDataset('Total Expenses', $incomeStatements->pluck('total_expenses'), 'rgba(255, 99, 132, 0.2)', 'rgba(255, 99, 132, 1)'),
            $this->createDataset('Net Income', $incomeStatements->pluck('net_income'), 'rgba(54, 162, 235, 0.2)', 'rgba(54, 162, 235, 1)'),
        ];
    }

    private function createDataset(string $label, $data, string $backgroundColor, string $borderColor): array
    {
        return [
            'label' => $label,
            'data' => $data->toArray(),
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
    protected function getType(): string
    {
        return 'doughnut';
    }
}
