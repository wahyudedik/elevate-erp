<?php

namespace App\Filament\Resources\ShiftResource\Widgets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\ManagementSDM\Shift;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Shifts', function () {
                return Shift::count();
            })->icon('heroicon-o-clock')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total shifts created')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Active Branches', function () {
                return Branch::whereHas('shift')->count();
            })->icon('heroicon-o-building-office')
                ->description('Branches with shifts')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Companies', function () {
                return Company::whereHas('shift')->count();
            })->icon('heroicon-o-building-office-2')
                ->description("Companies with shifts")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }
}
