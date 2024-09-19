<?php

namespace App\Filament\Resources\LedgerResource\Widgets;

use App\Models\ManagementFinancial\Ledger;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Transactions', function () {
                return $this->formatNumber(Ledger::count());
            })->icon('heroicon-o-document-text')
                ->description('Total number of ledger entries')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),

            Stat::make('Total Debit', function () {
                return $this->formatNumber(Ledger::where('transaction_type', 'debit')->sum('amount'));
            })->icon('heroicon-o-arrow-trending-down')
                ->description('Total debit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Total Credit', function () {
                return $this->formatNumber(Ledger::where('transaction_type', 'credit')->sum('amount'));
            })->icon('heroicon-o-arrow-trending-up')
                ->description('Total credit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),

            Stat::make('Latest Transaction Date', function () {
                return Ledger::latest('transaction_date')->value('transaction_date');
            })->icon('heroicon-o-calendar')
                ->description('Most recent transaction')
                ->descriptionIcon('heroicon-o-clock', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
        ];
    }

    protected function formatNumber($number)
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return $formattedNumber . $suffixes[$suffixIndex];
    }
}
