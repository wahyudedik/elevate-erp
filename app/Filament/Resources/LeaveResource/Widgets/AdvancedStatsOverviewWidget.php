<?php

namespace App\Filament\Resources\LeaveResource\Widgets;

use App\Models\ManagementSDM\Leave;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Cuti', Leave::count())->icon('heroicon-o-calendar')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Semua pengajuan cuti')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Cuti Tertunda', Leave::where('status', 'pending')->count())->icon('heroicon-o-clock')
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-o-clock', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning'),
            Stat::make('Cuti Disetujui', Leave::where('status', 'approved')->count())->icon('heroicon-o-check-circle')
                ->description("Berhasil disetujui")
                ->descriptionIcon('heroicon-o-check', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
        ];
    }
}
