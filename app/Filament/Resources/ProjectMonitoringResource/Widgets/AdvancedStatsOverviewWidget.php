<?php

namespace App\Filament\Resources\ProjectMonitoringResource\Widgets;

use App\Models\ManagementProject\ProjectMonitoring;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Projects', ProjectMonitoring::count())->icon('heroicon-o-clipboard-document-list')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Active project monitorings')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Completion Rate', ProjectMonitoring::avg('completion_percentage') . '%')->icon('heroicon-o-chart-bar')
                ->description('Average completion percentage')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
            Stat::make('At Risk Projects', ProjectMonitoring::where('status', 'at_risk')->count())->icon('heroicon-o-exclamation-triangle')
                ->description("Projects requiring attention")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('danger')
                ->iconColor('warning')
        ];
    }
}
