<?php

namespace App\Filament\Resources\OrderProcessingResource\Widgets;

use App\Models\ManagementCRM\OrderProcessing;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', OrderProcessing::count())->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total orders processed')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Sales', 'Rp ' . $this->formatNumber(OrderProcessing::sum('total_amount')))->icon('heroicon-o-currency-dollar')
                ->description('Total revenue generated')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Pending Orders', OrderProcessing::where('status', 'pending')->count())->icon('heroicon-o-clock')
                ->description("Orders awaiting processing")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
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
