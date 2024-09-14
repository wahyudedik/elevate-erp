<?php

namespace App\Filament\Resources\BalanceSheetResource\Widgets;

use App\Models\ManagementFinancial\BalanceSheet;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class BalanceSheetStatWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
 
    protected function getStats(): array
    {
        return [
            Stat::make('Total Assets', function () {
                return number_format(BalanceSheet::sum('total_assets'), 2);
            })->icon('heroicon-o-currency-dollar')
                ->progressBarColor('success')
                ->chartColor('success')
                ->description('Total assets in this period')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Liabilities', function () {
                return number_format(BalanceSheet::sum('total_liabilities'), 2);
            })->icon('heroicon-o-scale')
                ->description('Total liabilities in this period')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Equity', function () {
                return number_format(BalanceSheet::sum('total_equity'), 2);
            })->icon('heroicon-o-banknotes')
                ->description("Total equity in this period")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
        ];
    }
}
