<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Attendance;

class AttendanceLineChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Line Chart';

    protected function getData(): array
    {
        return [

            'labels' => ['Present', 'Absent', 'Late', 'On Leave'],
            'datasets' => [
                [
                    'label' => 'Attendance Status',
                    'data' => [
                        Attendance::where('status', 'present')->count(),
                        Attendance::where('status', 'absent')->count(),
                        Attendance::where('status', 'late')->count(),
                        Attendance::where('status', 'on_leave')->count(),
                    ],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(54, 162, 235, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],

        ];
    }
    protected static ?string $icon = 'heroicon-o-chart-bar';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Refresh data')
                ->action(fn() => $this->refresh())
                ->icon('heroicon-o-refresh'),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
