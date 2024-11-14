<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Employee;

class HrBarChart extends ChartWidget
{
    protected static ?string $heading = 'New Hires vs Exits';

    protected function getData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('F');
        });

        $newHires = collect(range(1, 12))->map(function ($month) {
            return Employee::where('date_of_joining', 'like', now()->format('Y-') . sprintf("%02d", $month) . '%')
                ->count();
        });

        $exits = collect(range(1, 12))->map(function ($month) {
            return Employee::where('status', 'terminated')
                ->orWhere('status', 'resigned')
                ->whereMonth('updated_at', $month)
                ->whereYear('updated_at', now()->year)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'New Hires',
                    'data' => $newHires->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
                [
                    'label' => 'Exits',
                    'data' => $exits->toArray(),
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
