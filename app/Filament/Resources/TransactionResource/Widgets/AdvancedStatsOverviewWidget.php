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
            Stat::make('Total Transaksi', Transaction::count())
                ->icon('heroicon-o-currency-dollar')
                ->description('Jumlah total transaksi')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Transaksi Selesai', Transaction::where('status', 'completed')->count())
                ->icon('heroicon-o-check-circle')
                ->description('Jumlah transaksi yang selesai')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Nominal', function () {
                return $this->formatNumber(Transaction::sum('amount'));
            })
                ->icon('heroicon-o-banknotes')
                ->description('Total nominal transaksi')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Rata-rata Nominal Transaksi', function () {
                return $this->formatNumber(Transaction::avg('amount'));
            })
                ->icon('heroicon-o-calculator')
                ->description('Rata-rata nominal per transaksi')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
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
