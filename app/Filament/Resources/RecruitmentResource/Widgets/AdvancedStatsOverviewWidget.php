<?php

namespace App\Filament\Resources\RecruitmentResource\Widgets;

use App\Models\ManagementSDM\Recruitment;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Recruitments', Recruitment::count())->icon('heroicon-o-briefcase')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Active job postings')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Open Positions', Recruitment::where('status', 'open')->count())->icon('heroicon-o-document-text')
                ->description('Currently accepting applications')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Closing Soon', Recruitment::whereDate('closing_date', '<=', now()->addDays(7))->where('status', 'open')->count())->icon('heroicon-o-clock')
                ->description("Positions closing within 7 days")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')        ];
    }
}
