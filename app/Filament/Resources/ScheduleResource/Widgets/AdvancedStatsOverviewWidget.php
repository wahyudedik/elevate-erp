<?php

namespace App\Filament\Resources\ScheduleResource\Widgets;

use App\Models\ManagementSDM\Shift;
use App\Models\ManagementSDM\Employee;
use App\Models\ManagementSDM\Schedule;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Employees', Employee::count())->icon('heroicon-o-users')
                ->description("Registered employees")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary'),
            Stat::make('Total Shifts', Shift::count())->icon('heroicon-o-clock')
                ->description("Active shifts")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
            Stat::make('WFA Schedules', Schedule::where('is_wfa', true)->count())->icon('heroicon-o-home')
                ->description("Work from anywhere schedules")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Banned Schedules', Schedule::where('is_banned', true)->count())->icon('ionicon-ban')
                ->description("Banned schedules")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
