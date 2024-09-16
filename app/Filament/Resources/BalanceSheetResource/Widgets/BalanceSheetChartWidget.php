<?php

namespace App\Filament\Resources\BalanceSheetResource\Widgets;

use App\Models\ManagementFinancial\BalanceSheet;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Carbon\Carbon;

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

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $balanceSheets = BalanceSheet::query()
            ->when($this->filter === 'today', fn ($query) => $query->whereDate('created_at', Carbon::today()))
            ->when($this->filter === 'week', fn ($query) => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
            ->when($this->filter === 'month', fn ($query) => $query->whereMonth('created_at', Carbon::now()->month))
            ->when($this->filter === 'year', fn ($query) => $query->whereYear('created_at', Carbon::now()->year))
            ->get();

        $labels = $balanceSheets->pluck('created_at')->map(fn ($date) => $date->format('M d'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Assets',
                    'data' => $balanceSheets->pluck('total_assets')->toArray(),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => 'Total Liabilities',
                    'data' => $balanceSheets->pluck('total_liabilities')->toArray(),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                ],
                [
                    'label' => 'Total Equity',
                    'data' => $balanceSheets->pluck('total_equity')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
