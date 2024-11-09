<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\ManagementSDM\Attendance;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Attendances', Attendance::count())->icon('heroicon-o-user-group')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total attendance records')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Present Today', Attendance::where('date', now()->toDateString())
                ->where('status', 'present')->count())->icon('heroicon-o-check-circle')
                ->description('Employees present today')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Late Today', Attendance::where('date', now()->toDateString())
                ->where('status', 'late')->count())->icon('heroicon-o-clock')
                ->description("Employees late today")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning')
        ];
    }
}
