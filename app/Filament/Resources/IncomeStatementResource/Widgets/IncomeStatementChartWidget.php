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

        $totalRevenue = $incomeStatements->pluck('total_revenue')->toArray();
        $totalExpenses = $incomeStatements->pluck('total_expenses')->toArray();
        $netIncome = $incomeStatements->pluck('net_income')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue',
                    'data' => $totalRevenue,
                ],
                [
                    'label' => 'Total Expenses',
                    'data' => $totalExpenses,
                ],
                [
                    'label' => 'Net Income',
                    'data' => $netIncome,
                ],
            ],
            'labels' => $incomeStatements->pluck('created_at')->map(function ($date) {
                return $date->format('Q');
            })->toArray(),
        ];
    }
    protected function getType(): string
    {
        return 'doughnut';
    }
}
