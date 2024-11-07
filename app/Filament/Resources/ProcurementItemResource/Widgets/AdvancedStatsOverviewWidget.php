<?php

namespace App\Filament\Resources\ProcurementItemResource\Widgets;

use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Items', \App\Models\ManagementStock\ProcurementItem::count())->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total procurement items')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Value', number_format(\App\Models\ManagementStock\ProcurementItem::sum('total_price'), 2))->icon('heroicon-o-currency-dollar')
                ->description('Total procurement value')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Average Price', number_format(\App\Models\ManagementStock\ProcurementItem::avg('unit_price'), 2))->icon('heroicon-o-calculator')
                ->description("Average unit price")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')        ];
    }
}
