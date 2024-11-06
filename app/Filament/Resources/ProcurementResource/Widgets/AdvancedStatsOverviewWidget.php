<?php

namespace App\Filament\Resources\ProcurementResource\Widgets;

use App\Models\ManagementStock\Procurement;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Procurements', Procurement::count())->icon('heroicon-o-shopping-cart')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total procurement records')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Cost', number_format(Procurement::sum('total_cost'), 2))->icon('heroicon-o-currency-dollar')
                ->description('Total procurement cost')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Pending Orders', Procurement::where('status', 'ordered')->count())->icon('heroicon-o-clock')
                ->description("Awaiting delivery")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
