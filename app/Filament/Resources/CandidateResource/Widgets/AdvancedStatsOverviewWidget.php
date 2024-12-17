<?php

namespace App\Filament\Resources\CandidateResource\Widgets;

use App\Models\ManagementSDM\Candidate;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Candidate Statistics';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Kandidat', Candidate::count())->icon('heroicon-o-user')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total kandidat dalam sistem')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Kandidat Diterima', Candidate::where('status', 'hired')->count())->icon('heroicon-o-check-circle')
                ->description('Kandidat yang berhasil diterima')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Dalam Proses', Candidate::whereIn('status', ['applied', 'interviewing', 'offered'])->count())->icon('heroicon-o-clock')
                ->description("Kandidat dalam proses rekrutmen")
                ->descriptionIcon('heroicon-o-arrow-right', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
