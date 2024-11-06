<?php

namespace App\Filament\Resources\PurchaseItemResource\Widgets;

use App\Models\ManagementStock\PurchaseItem;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', PurchaseItem::count())->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total products purchased')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Quantity', PurchaseItem::sum('quantity'))->icon('heroicon-o-cube')
                ->description('Total items purchased')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Amount', number_format(PurchaseItem::sum('total_price'), 2))->icon('heroicon-o-currency-dollar')
                ->description("Total purchase value")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }
}
