<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementProject\Project;

class ProjectBarChart extends ChartWidget
{
    protected static ?string $heading = 'Project Milestone Progress';

    protected function getData(): array
    {
        $projects = Project::with('milestones')
            ->where('status', 'in_progress')
            ->get();

        $datasets = [];
        $labels = [];

        foreach ($projects as $project) {
            $labels[] = $project->name;

            // Calculate milestone completion percentage
            $totalMilestones = $project->milestones->count();
            $achievedMilestones = $project->milestones->where('status', 'achieved')->count();

            $percentage = $totalMilestones > 0
                ? round(($achievedMilestones / $totalMilestones) * 100, 2)
                : 0;

            $datasets[] = $percentage;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Milestone Progress (%)',
                    'data' => $datasets,
                    'backgroundColor' => '#4CAF50',
                    'borderColor' => '#388E3C',
                    'borderWidth' => 1
                ]
            ],
            'labels' => $labels
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
