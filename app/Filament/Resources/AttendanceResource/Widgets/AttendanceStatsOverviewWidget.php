<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\ManagementSDM\Shift;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementSDM\Attendance;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AttendanceStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Shifts', Shift::count())
                ->icon('heroicon-o-clock')
                ->iconBackgroundColor('success')
                ->chartColor('success')
                ->iconPosition('end')
                ->description('Total shifts created')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Schedules', Schedule::count())
                ->icon('heroicon-o-calendar')
                ->description('Total schedules created')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Total Attendances', Attendance::count())
                ->icon('heroicon-o-user-group')
                ->description("Total attendance records")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary')
        ];
    }
}
