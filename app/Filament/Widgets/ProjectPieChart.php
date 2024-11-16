<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementProject\ProjectTask;

class ProjectPieChart extends ChartWidget
{
    protected static ?string $heading = 'Task Completion Rate';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $taskStats = ProjectTask::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $taskStats->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#10B981', // completed - green
                        '#3B82F6', // in_progress - blue
                        '#EF4444', // overdue - red
                        '#F59E0B', // pending - yellow
                    ],
                ],
            ],
            'labels' => $taskStats->pluck('status')->map(function ($status) {
                return ucfirst(str_replace('_', ' ', $status));
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
