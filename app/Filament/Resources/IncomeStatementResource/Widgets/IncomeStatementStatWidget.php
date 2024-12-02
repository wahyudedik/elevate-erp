<?php

namespace App\Filament\Resources\IncomeStatementResource\Widgets;

use App\Models\ManagementFinancial\IncomeStatement;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class IncomeStatementStatWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [

            Stat::make('Total Pendapatan', function () {
                return '' . $this->formatNumber(IncomeStatement::sum('total_revenue'));
            })
                ->icon('heroicon-o-currency-dollar')
                ->iconColor('success')
                ->description('Total pendapatan dari semua laporan laba rugi')
                ->descriptionIcon('heroicon-o-presentation-chart-line', 'before')
                ->descriptioncolor('success'),

            Stat::make('Total Pengeluaran', function () {
                return '' . $this->formatNumber(IncomeStatement::sum('total_expenses'));
            })
                ->icon('heroicon-o-banknotes')
                ->iconColor('danger')
                ->description('Total pengeluaran dari semua laporan laba rugi')
                ->descriptionIcon('heroicon-o-presentation-chart-line', 'before')
                ->descriptioncolor('danger'),

            Stat::make('Laba Bersih', function () {
                return '' . $this->formatNumber(IncomeStatement::sum('net_income'));
            })
                ->icon('heroicon-o-scale')
                ->iconColor('primary')
                ->description('Total laba bersih dari semua laporan laba rugi')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptioncolor('primary'),
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
