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
            Stat::make('Total Shift', function () {
                return Shift::count();
            })->icon('heroicon-o-clock')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total shift yang dibuat')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Cabang Aktif', function () {
                return Branch::whereHas('shift')->count();
            })->icon('heroicon-o-building-office')
                ->description('Cabang dengan shift')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Perusahaan', function () {
                return Company::whereHas('shift')->count();
            })->icon('heroicon-o-building-office-2')
                ->description("Perusahaan dengan shift")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }
}
