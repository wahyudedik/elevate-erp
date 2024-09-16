<?php

namespace App\Filament\Resources\CashFlowResource\Widgets;

use App\Models\ManagementFinancial\CashFlow;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class CashFlowChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Cash Flow';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Cash Flow Chart';

    protected static ?string $badge = 'new';
    protected static ?string $badgeColor = 'success';
    protected static ?string $badgeIcon = 'heroicon-o-check-circle';
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
        $dateFilter = match ($this->filter) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->startOfYear(),
            default => now()->subWeek(),
        };

        $cashFlows = CashFlow::where('created_at', '>=', $dateFilter)->get();

        return [
            'datasets' => [
                [
                    'label' => 'Operating Cash Flow',
                    'data' => $cashFlows->pluck('operating_cash_flow')->toArray(),
                    'backgroundColor' => '#4CAF50',
                ],
                [
                    'label' => 'Investing Cash Flow',
                    'data' => $cashFlows->pluck('investing_cash_flow')->toArray(),
                    'backgroundColor' => '#2196F3',
                ],
                [
                    'label' => 'Financing Cash Flow',
                    'data' => $cashFlows->pluck('financing_cash_flow')->toArray(),
                    'backgroundColor' => '#FFC107',
                ],
                [
                    'label' => 'Net Cash Flow',
                    'data' => $cashFlows->pluck('net_cash_flow')->toArray(),
                    'backgroundColor' => '#9C27B0',
                ],
            ],
            'labels' => [
                'Operating Cash Flow',
                'Investing Cash Flow',
                'Financing Cash Flow',
                'Net Cash Flow',
            ],
        ];
    }


    protected function getType(): string
    {
        return 'pie';
    }
}
