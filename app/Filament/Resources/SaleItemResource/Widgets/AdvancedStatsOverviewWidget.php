<?php

namespace App\Filament\Resources\SaleItemResource\Widgets;

use App\Models\ManagementCRM\SaleItem;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Sales', $this->formatNumber(SaleItem::count()))->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total number of sales')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Revenue', $this->formatNumber(SaleItem::sum('total_price')))->icon('heroicon-o-currency-dollar')
                ->description('Total revenue from all sales')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Average Order Value', $this->formatNumber(SaleItem::avg('unit_price')))->icon('heroicon-o-calculator')
                ->description("Average price per unit")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
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
