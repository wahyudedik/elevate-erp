<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use App\Models\ManagementStock\Inventory;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
 
    protected function getStats(): array
    {
        return [
            Stat::make('Total Items', Inventory::count())->icon('heroicon-o-cube')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total inventory items')
                ->descriptionIcon('heroicon-o-cube', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Out of Stock', Inventory::where('status', 'out_of_stock')->count())->icon('heroicon-o-exclamation-triangle')
                ->description('Items needing restock')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning'),
            Stat::make('Total Value', number_format(Inventory::sum('purchase_price'), 2))->icon('heroicon-o-currency-dollar')
                ->description("Inventory value")
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('primary')
                ->iconColor('success')        ];
    }
}
