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
            Stat::make('Total Akun', Accounting::count())->icon('heroicon-o-calculator')
                ->chartColor('primary')
                ->iconPosition('end')
                ->description('Jumlah total akun')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Total Aset', $this->formatNumber(Accounting::where('account_type', 'asset')->sum('current_balance')))->icon('heroicon-o-banknotes')
                ->description('Total nilai akun aset')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Kewajiban', $this->formatNumber(Accounting::where('account_type', 'liability')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total nilai akun kewajiban")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Total Ekuitas', $this->formatNumber(Accounting::where('account_type', 'equity')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total nilai akun ekuitas")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Total Pendapatan', $this->formatNumber(Accounting::where('account_type', 'revenue')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total nilai akun pendapatan")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Pengeluaran', $this->formatNumber(Accounting::where('account_type', 'expense')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total nilai akun pengeluaran")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Total Kas', $this->formatNumber(Accounting::where('account_type', 'kas')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total nilai akun kas")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            // Stat::make('Total Piutang', $this->formatNumber(Accounting::where('account_type', 'receivable')->sum('current_balance')))->icon('heroicon-o-scale')
            //     ->description("Total nilai akun piutang")
            //     ->descriptionIcon('heroicon-o-currency-dollar', 'before')
            //     ->descriptionColor('primary')
            //     ->iconColor('primary'),
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
