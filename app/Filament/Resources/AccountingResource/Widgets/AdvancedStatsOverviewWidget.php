<?php

namespace App\Filament\Resources\AccountingResource\Widgets;

use App\Models\ManagementFinancial\Accounting;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Accounts', Accounting::count() . '/10')->icon('heroicon-o-calculator')
                ->progress(Accounting::count()/10)
                ->progressBarColor('primary')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total number of accounts')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Total Assets', 'IDR' . number_format(Accounting::where('account_type', 'asset')->sum('initial_balance'), 2))->icon('heroicon-o-banknotes')
                ->description('Total value of asset accounts')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Liabilities', 'IDR' . number_format(Accounting::where('account_type', 'liability')->sum('initial_balance'), 2))->icon('heroicon-o-scale')
                ->description("Total value of liability accounts")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
