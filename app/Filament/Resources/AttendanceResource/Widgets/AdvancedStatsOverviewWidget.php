<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\ManagementSDM\Attendance;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Statistik Kehadiran';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Kehadiran', Attendance::count())->icon('heroicon-o-user-group')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total catatan kehadiran')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Hadir Hari Ini', Attendance::where('date', now()->toDateString())
                ->where('status', 'present')->count())->icon('heroicon-o-check-circle')
                ->description('Karyawan hadir hari ini')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Terlambat Hari Ini', Attendance::where('date', now()->toDateString())
                ->where('status', 'late')->count())->icon('heroicon-o-clock')
                ->description("Karyawan terlambat hari ini")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning')
        ];
    }
}
