<?php

namespace App\Filament\Resources\ApplicationsResource\Widgets;

use App\Models\ManagementSDM\Applications;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Applications', Applications::count())->icon('heroicon-o-document-text')
                ->chartColor('primary')
                ->iconPosition('start')
                ->description('Total applications submitted')
                ->descriptionIcon('heroicon-o-document', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('Hired Candidates', Applications::where('status', 'hired')->count())->icon('heroicon-o-user-group')
                ->description('Successfully hired candidates')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('In Progress', Applications::whereIn('status', ['applied', 'interviewing', 'offered'])->count())->icon('heroicon-o-clock')
                ->description("Applications in process")
                ->descriptionIcon('heroicon-o-arrow-path', 'before')
                ->descriptionColor('warning')
                ->iconColor('warning')
        ];
    }
}
