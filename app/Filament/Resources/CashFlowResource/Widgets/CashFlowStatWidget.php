<?php

namespace App\Filament\Resources\CashFlowResource\Widgets;

use App\Models\ManagementFinancial\CashFlow;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class CashFlowStatWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Operating Cash Flow', number_format(CashFlow::sum('operating_cash_flow'), 2))
                ->icon('heroicon-o-currency-dollar')
                ->progressBarColor('success')
                ->chartColor('success')
                ->description('Total operating cash flow')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Investing Cash Flow', number_format(CashFlow::sum('investing_cash_flow'), 2))
                ->icon('heroicon-o-building-library')
                ->description('Total investing cash flow')
                ->descriptionIcon('heroicon-o-arrow-trending-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning'),
            Stat::make('Financing Cash Flow', number_format(CashFlow::sum('financing_cash_flow'), 2))
                ->icon('heroicon-o-banknotes')
                ->description('Total financing cash flow')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary'),
            Stat::make('Net Cash Flow', number_format(CashFlow::sum('net_cash_flow'), 2))
                ->icon('heroicon-o-calculator')
                ->description('Total net cash flow')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
        ];
    }
}
