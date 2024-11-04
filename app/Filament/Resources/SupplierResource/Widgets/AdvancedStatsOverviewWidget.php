<?php

namespace App\Filament\Resources\SupplierResource\Widgets;

use App\Models\ManagementStock\Supplier;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Suppliers', Supplier::count())->icon('heroicon-o-building-office')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Active suppliers')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Credit Limit', Supplier::sum('credit_limit'))->icon('heroicon-o-currency-dollar')
                ->description('Total credit limit')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Inactive Suppliers', Supplier::where('status', 'inactive')->count())->icon('heroicon-o-building-library')
                ->description("Inactive supplier count")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')        ];
    }
}
