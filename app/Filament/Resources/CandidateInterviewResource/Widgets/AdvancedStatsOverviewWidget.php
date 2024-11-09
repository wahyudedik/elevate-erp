<?php

namespace App\Filament\Resources\CandidateInterviewResource\Widgets;

use App\Models\ManagementSDM\CandidateInterview;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Interviews', CandidateInterview::count())
                ->icon('heroicon-o-user-group')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('All interviews conducted')
                ->descriptionIcon('heroicon-o-clipboard-document-list', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Passed Interviews', CandidateInterview::where('result', 'passed')->count())
                ->icon('heroicon-o-check-circle')
                ->description('Successful interviews')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Failed Interviews', CandidateInterview::where('result', 'failed')->count())
                ->icon('heroicon-o-x-circle')
                ->description("Unsuccessful interviews")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Pending Interviews', CandidateInterview::where('result', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->description("Awaiting results")
                ->descriptionIcon('heroicon-o-arrow-path', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
