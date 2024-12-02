<?php

namespace App\Filament\Resources\CashFlowResource\Widgets;

use App\Models\ManagementFinancial\CashFlow;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class CashFlowStatWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [

            Stat::make('Arus Kas Operasi', $this->formatNumber(CashFlow::sum('operating_cash_flow')))
                ->icon('heroicon-o-currency-dollar')
                ->progressBarColor('success')
                ->chartColor('success')
                ->description('Total arus kas operasi')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Arus Kas Investasi', $this->formatNumber(CashFlow::sum('investing_cash_flow')))
                ->icon('heroicon-o-building-library')
                ->description('Total arus kas investasi')
                ->descriptionIcon('heroicon-o-arrow-trending-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning'),
            Stat::make('Arus Kas Pendanaan', $this->formatNumber(CashFlow::sum('financing_cash_flow')))
                ->icon('heroicon-o-banknotes')
                ->description('Total arus kas pendanaan')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary'),
            Stat::make('Arus Kas Bersih', $this->formatNumber(CashFlow::sum('net_cash_flow')))
                ->icon('heroicon-o-calculator')
                ->description('Total arus kas bersih')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
        ];
    }

    protected function formatNumber($number)
    {
        $isNegative = $number < 0;
        $number = abs($number);

        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return ($isNegative ? '-' : '') . $formattedNumber . $suffixes[$suffixIndex];
    }
}
