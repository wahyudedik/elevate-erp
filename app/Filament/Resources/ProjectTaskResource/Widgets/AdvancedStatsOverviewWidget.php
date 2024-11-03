<?php

namespace App\Filament\Resources\ProjectTaskResource\Widgets;

use App\Models\ManagementProject\ProjectTask;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tasks', $this->formatNumber(ProjectTask::count()))->icon('heroicon-o-clipboard-document-list')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('All project tasks')
                ->descriptionIcon('heroicon-o-clipboard', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('In Progress', $this->formatNumber(ProjectTask::where('status', 'in_progress')->count()))->icon('heroicon-o-arrow-path')
                ->description('Tasks in progress')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Overdue Tasks', $this->formatNumber(ProjectTask::where('status', 'overdue')->count()))->icon('heroicon-o-clock')
                ->description("Tasks past due date")
                ->descriptionIcon('heroicon-o-chevron-down', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger')
        ];
    }

    protected function formatNumber($number)
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return $formattedNumber . $suffixes[$suffixIndex];
    }
}
