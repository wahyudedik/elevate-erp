<?php

namespace App\Filament\Resources\ProjectResource\Widgets;

use App\Models\ManagementProject\Project;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Projects', $this->formatNumber(Project::count()))->icon('heroicon-o-briefcase')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('Active Projects')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Total Budget', $this->formatNumber(Project::sum('budget')))->icon('heroicon-o-currency-dollar')
                ->description('Total Project Budget')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Projects by Status', $this->formatNumber(Project::where('status', 'in_progress')->count()))->icon('heroicon-o-chart-bar')
                ->description("In Progress Projects")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('info')
                ->iconColor('primary')
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
