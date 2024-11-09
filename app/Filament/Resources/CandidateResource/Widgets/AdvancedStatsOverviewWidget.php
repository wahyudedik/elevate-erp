<?php

namespace App\Filament\Resources\CandidateResource\Widgets;

use App\Models\ManagementSDM\Candidate;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Candidates', Candidate::count())->icon('heroicon-o-user')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Total candidates in system')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Hired Candidates', Candidate::where('status', 'hired')->count())->icon('heroicon-o-check-circle')
                ->description('Successfully hired candidates')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('In Process', Candidate::whereIn('status', ['applied', 'interviewing', 'offered'])->count())->icon('heroicon-o-clock')
                ->description("Candidates in recruitment process")
                ->descriptionIcon('heroicon-o-arrow-right', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
