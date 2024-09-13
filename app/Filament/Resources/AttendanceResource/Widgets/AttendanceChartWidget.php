<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\ManagementSDM\Attendance;
use Filament\Widgets\ChartWidget;

class AttendanceChartWidget extends ChartWidget
{

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'r' => [
                    'ticks' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }

    public static function getWidgetIcon(): string
    {
        return 'iconpark-checkin-o';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $query = Attendance::query();

        if ($filter) {
            $query->when($filter === 'today', fn($q) => $q->whereDate('created_at', today()))
                ->when($filter === 'week', fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->when($filter === 'month', fn($q) => $q->whereMonth('created_at', now()->month))
                ->when($filter === 'year', fn($q) => $q->whereYear('created_at', now()->year));
        }

        return [
            'datasets' => [
                [
                    'data' => [
                        $query->where('status', 'present')->count(),
                        $query->where('status', 'absent')->count(),
                        $query->where('status', 'late')->count(),
                        $query->where('status', 'on_leave')->count(),
                    ],
                    'backgroundColor' => [
                        '#4CAF50', // Green for present
                        '#F44336', // Red for absent
                        '#FFC107', // Amber for late
                        '#2196F3', // Blue for on leave
                    ],
                ],
            ],
            'labels' => ['Present', 'Absent', 'Late', 'On Leave'],
        ];
    }


    protected static ?string $heading = 'Attendance Status';

    protected static ?int $sort = 2;
}
