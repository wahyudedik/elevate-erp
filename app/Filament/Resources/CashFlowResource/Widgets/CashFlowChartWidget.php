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
        return [
            'datasets' => [
                [
                    'label' => 'Operating Cash Flow',
                    'data' => CashFlow::where('operating_cash_flow')->where('created_at', '>=', now()->subDays(7))->pluck('operating_cash_flow'),
                ],
                [
                    'label' => 'Investing Cash Flow',
                    'data' => CashFlow::where('investing_cash_flow')->where('created_at', '>=', now()->subDays(7))->pluck('investing_cash_flow'),
                ],
                [
                    'label' => 'Financing Cash Flow',
                    'data' => CashFlow::where('financing_cash_flow')->where('created_at', '>=', now()->subDays(7))->pluck('financing_cash_flow'),
                ],
                [
                    'label' => 'Net Cash Flow',
                    'data' => CashFlow::where('net_cash_flow')->where('created_at', '>=', now()->subDays(7))->pluck('net_cash_flow'),
                ],
            ],
            'labels' => [
                'Operating Cash Flow',
                'Investing Cash Flow',
                'Financing Cash Flow',
                'Net Cash Flow',
            ]
        ];
    }
    
    
    protected function getType(): string
    {
        return 'pie';
    }
}
