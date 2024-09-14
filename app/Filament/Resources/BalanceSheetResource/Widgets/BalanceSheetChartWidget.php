<?php

namespace App\Filament\Resources\BalanceSheetResource\Widgets;

use App\Models\ManagementFinancial\BalanceSheet;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class BalanceSheetChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Balance Sheet';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-scale';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Balance Sheet Overview';
 
    protected static ?string $badge = 'Financial';
    protected static ?string $badgeColor = 'success';
    protected static ?string $badgeIcon = 'heroicon-o-currency-dollar';
    protected static ?string $badgeIconPosition = 'after';
    protected static ?string $badgeSize = 'sm';
 
 
    public ?string $filter = 'current';
 
    protected function getFilters(): ?array
    {
        return [
            'current' => 'Current',
            'last_month' => 'Last Month',
            'last_quarter' => 'Last Quarter',
            'last_year' => 'Last Year',
        ];
    }
 
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Total Assets',
                    'data' => $this->getBalanceSheetData('total_assets'),
                ],
                [
                    'label' => 'Total Liabilities',
                    'data' => $this->getBalanceSheetData('total_liabilities'),
                ],
                [
                    'label' => 'Total Equity',
                    'data' => $this->getBalanceSheetData('total_equity'),
                ],
            ],
            'labels' => $this->getLabels(),
        ];
    }

    protected function getBalanceSheetData(string $column): array
    {
            // Implement logic to fetch data from balance_sheets table
            // based on the selected filter and column
            // Return an array of values
            $query = BalanceSheet::query();

            switch ($this->filter) {
                case 'last_month':
                    $query->whereMonth('created_at', '=', now()->subMonth()->month);
                    break;
                case 'last_quarter':
                    $query->whereBetween('created_at', [now()->subMonths(3), now()]);
                    break;
                case 'last_year':
                    $query->whereYear('created_at', '=', now()->subYear()->year);
                    break;
                default:
                    $query->whereMonth('created_at', '=', now()->month);
                    break;
            }

            return $query->pluck($column)->toArray();
    }

    protected function getLabels(): array
    {
            // Implement logic to generate labels based on the selected filter
            // Return an array of labels
            $labels = [];
            $filter = $this->filter;

            switch ($filter) {
                case 'current':
                    $labels = ['Current'];
                    break;
                case 'last_month':
                    $labels = ['Last Month'];
                    break;
                case 'last_quarter':
                    $labels = ['Last Quarter'];
                    break;
                case 'last_year':
                    $labels = ['Last Year'];
                    break;
                default:
                    $labels = ['Unknown'];
            }

            return $labels;
    }

    protected function getType(): string
    {
        return 'scatter';
    }
}
