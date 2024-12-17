<?php

namespace App\Filament\Resources\RecruitmentResource\Widgets;

use App\Models\ManagementSDM\Recruitment;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Recruitment Statistics';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Rekrutmen', Recruitment::count())->icon('heroicon-o-briefcase')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Lowongan kerja aktif')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Posisi Terbuka', Recruitment::where('status', 'open')->count())->icon('heroicon-o-document-text')
                ->description('Sedang menerima lamaran')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Segera Ditutup', Recruitment::whereDate('closing_date', '<=', now()->addDays(7))->where('status', 'open')->count())->icon('heroicon-o-clock')
                ->description("Posisi yang ditutup dalam 7 hari")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }
}
