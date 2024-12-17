<?php

namespace App\Filament\Resources\ApplicationsResource\Widgets;

use App\Models\ManagementSDM\Applications;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Applications Overview';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Lamaran', Applications::count())->icon('heroicon-o-document-text')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total lamaran yang diajukan')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Kandidat Diterima', Applications::where('status', 'hired')->count())->icon('heroicon-o-user-group')
                ->description('Kandidat yang berhasil diterima')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Dalam Proses', Applications::whereIn('status', ['applied', 'interviewing', 'offered'])->count())->icon('heroicon-o-clock')
                ->description("Lamaran dalam proses")
                ->descriptionIcon('heroicon-o-arrow-path', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
