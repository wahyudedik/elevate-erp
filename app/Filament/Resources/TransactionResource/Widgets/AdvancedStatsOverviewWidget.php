<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\ManagementFinancial\Transaction;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    

    protected function getStats(): array
    {
        return [
            Stat::make('Total Transactions', Transaction::count())
                ->icon('heroicon-o-currency-dollar')
                ->description('Total number of transactions')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Completed Transactions', Transaction::where('status', 'completed')->count())
                ->icon('heroicon-o-check-circle')
                ->description('Number of completed transactions')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Amount', Transaction::sum('amount'))
                ->icon('heroicon-o-banknotes')
                ->description('Total transaction amount')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
                // ->formatStateUsing(fn (string $state): string => number_format($state, 2)),
            Stat::make('Average Transaction Amount', Transaction::avg('amount'))
                ->icon('heroicon-o-calculator')
                ->description('Average amount per transaction')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
                // ->formatStateUsing(fn (string $state): string => '$' . number_format((float)$state, 2)),
        ];
    }
}
