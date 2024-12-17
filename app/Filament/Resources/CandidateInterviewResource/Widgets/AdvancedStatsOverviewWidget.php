<?php

namespace App\Filament\Resources\CandidateInterviewResource\Widgets;

use App\Models\ManagementSDM\CandidateInterview;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Interview Statistics';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Wawancara', CandidateInterview::count())
                ->icon('heroicon-o-user-group')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Semua wawancara yang dilakukan')
                ->descriptionIcon('heroicon-o-clipboard-document-list', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Wawancara Lulus', CandidateInterview::where('result', 'passed')->count())
                ->icon('heroicon-o-check-circle')
                ->description('Wawancara berhasil')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Wawancara Gagal', CandidateInterview::where('result', 'failed')->count())
                ->icon('heroicon-o-x-circle')
                ->description("Wawancara tidak berhasil")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),
            Stat::make('Wawancara Tertunda', CandidateInterview::where('result', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->description("Menunggu hasil")
                ->descriptionIcon('heroicon-o-arrow-path', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
