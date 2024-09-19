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
                return $this->formatNumber(BalanceSheet::sum('total_assets'));
            })->icon('heroicon-o-currency-dollar')
                ->progressBarColor('success')
                ->chartColor('success')
                ->description('Total assets in this period')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Liabilities', function () {
                return $this->formatNumber(BalanceSheet::sum('total_liabilities'));
            })->icon('heroicon-o-scale')
                ->description('Total liabilities in this period')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Equity', function () {
                return $this->formatNumber(BalanceSheet::sum('total_equity'));
            })->icon('heroicon-o-banknotes')
                ->description("Total equity in this period")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
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
