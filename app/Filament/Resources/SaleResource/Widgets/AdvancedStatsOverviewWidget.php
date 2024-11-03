<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\ManagementCRM\Sale;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Sales', $this->formatNumber(Sale::count()))->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total number of sales')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Revenue', $this->formatNumber(Sale::sum('total_amount')))->icon('heroicon-o-currency-dollar')
                ->description('Total revenue from all sales')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
                // ->money('idr'),
            Stat::make('Completed Sales', $this->formatNumber(Sale::where('status', 'completed')->count()))->icon('heroicon-o-check-circle')
                ->description("Number of completed sales")
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
