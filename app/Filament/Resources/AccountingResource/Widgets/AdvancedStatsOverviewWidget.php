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
            Stat::make('Total Accounts', Accounting::count())->icon('heroicon-o-calculator')
                // ->progress(Accounting::count())
                // ->progressBarColor('primary')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total number of accounts')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Total Assets', $this->formatNumber(Accounting::where('account_type', 'asset')->sum('current_balance')))->icon('heroicon-o-banknotes')
                ->description('Total value of asset accounts')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Liabilities', $this->formatNumber(Accounting::where('account_type', 'liability')->sum('current_balance')))->icon('heroicon-o-scale')
                ->description("Total value of liability accounts")
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),

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
