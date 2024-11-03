<?php

namespace App\Filament\Resources\ProjectMilestoneResource\Widgets;

use App\Models\ManagementProject\Project;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Milestones', $this->formatNumber(Project::count()))->icon('heroicon-o-flag')
                ->chartColor('success')
                ->iconPosition('start')
                ->description('All project milestones')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),
            Stat::make('Pending Milestones', $this->formatNumber(Project::where('status', 'pending')->count()))->icon('heroicon-o-clock')
                ->description('Milestones in progress')
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('primary')
                ->iconColor('warning'),
            Stat::make('Achieved Milestones', $this->formatNumber(Project::where('status', 'achieved')->count()))->icon('heroicon-o-check-circle')
                ->description("Completed milestones")
                ->descriptionIcon('heroicon-o-chevron-up', 'before')
                ->descriptionColor('success')
                ->iconColor('success')
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
