<?php

namespace App\Filament\Resources\SupplierTransactionsResource\Widgets;

use App\Models\ManagementStock\SupplierTransactions;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Transactions', SupplierTransactions::count())
                ->icon('heroicon-o-currency-dollar')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total supplier transactions')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Amount', number_format(SupplierTransactions::sum('amount'), 2))
                ->icon('heroicon-o-banknotes')
                ->description('Total transaction amount')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Latest Transaction', SupplierTransactions::latest()->first()?->transaction_code ?? 'N/A')
                ->icon('heroicon-o-clock')
                ->description('Most recent transaction code')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary')
        ];
    }
}
