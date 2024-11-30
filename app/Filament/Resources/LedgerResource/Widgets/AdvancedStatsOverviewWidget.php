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
            Stat::make('Total Transaksi', function () {
                return $this->formatNumber(Ledger::count());
            })->icon('heroicon-o-document-text')
                ->description('Jumlah total entri buku besar')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),

            Stat::make('Total Debit', function () {
                return $this->formatNumber(Ledger::where('transaction_type', 'debit')->sum('amount'));
            })->icon('heroicon-o-arrow-trending-down')
                ->description('Jumlah total debit')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Total Kredit', function () {
                return $this->formatNumber(Ledger::where('transaction_type', 'credit')->sum('amount'));
            })->icon('heroicon-o-arrow-trending-up')
                ->description('Jumlah total kredit')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),

            Stat::make('Tanggal Transaksi Terakhir', function () {
                return $this->formatDate(Ledger::latest('transaction_date')->value('transaction_date'));
            })->icon('heroicon-o-calendar')
                ->description('Transaksi terbaru')
                ->descriptionIcon('heroicon-o-clock', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
        ];
    }

    protected function formatDate($date)
    {
        return $date->format('d M Y');
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
