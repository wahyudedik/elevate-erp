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
            Stat::make('Total Karyawan', Employee::count())->icon('heroicon-o-users')
                ->description("Karyawan terdaftar")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('primary'),
            Stat::make('Total Shift', Shift::count())->icon('heroicon-o-clock')
                ->description("Shift aktif")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('warning'),
            Stat::make('Jadwal WFA', Schedule::where('is_wfa', true)->count())->icon('heroicon-o-home')
                ->description("Jadwal kerja dari mana saja")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('success'),
            Stat::make('Jadwal Diblokir', Schedule::where('is_banned', true)->count())->icon('ionicon-ban')
                ->description("Jadwal yang diblokir")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
