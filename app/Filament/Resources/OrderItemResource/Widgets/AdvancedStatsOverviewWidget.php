<?php

namespace App\Filament\Resources\OrderItemResource\Widgets;

use App\Models\ManagementCRM\OrderItem;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', OrderItem::count())->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total orders processed')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Revenue', OrderItem::sum('total_price'))->icon('heroicon-o-currency-dollar')
                ->description('Total revenue generated')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Average Order Value', OrderItem::avg('unit_price'))->icon('heroicon-o-calculator')
                ->description("Average price per order")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')        ];
    }
}
