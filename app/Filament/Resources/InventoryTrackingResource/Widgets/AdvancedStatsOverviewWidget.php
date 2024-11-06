<?php

namespace App\Filament\Resources\InventoryTrackingResource\Widgets;

use App\Models\ManagementStock\InventoryTracking;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Inventory', InventoryTracking::count())->icon('heroicon-o-cube')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total inventory tracked')
                ->descriptionIcon('heroicon-o-chart-bar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Additions', InventoryTracking::where('transaction_type', 'addition')->count())->icon('heroicon-o-plus-circle')
                ->description('Total additions made')
                ->descriptionIcon('heroicon-o-arrow-trending-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Deductions', InventoryTracking::where('transaction_type', 'deduction')->count())->icon('heroicon-o-minus-circle')
                ->description("Total deductions made")
                ->descriptionIcon('heroicon-o-arrow-trending-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')        ];
    }
}
