<?php

namespace App\Filament\Resources\ApplicationsResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Applications;

class ApplicationSubmitWidget extends ChartWidget
{
    protected static ?string $heading = 'Application Submit';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }
    
    protected function getData(): array
    {
        $activeFilter = $this->filter;
    
        $query = Applications::query();
    
        if ($activeFilter) {
            $query->when($activeFilter === 'today', function ($query) {
                return $query->whereDate('created_at', today());
            })->when($activeFilter === 'week', function ($query) {
                return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })->when($activeFilter === 'month', function ($query) {
                return $query->whereMonth('created_at', now()->month);
            })->when($activeFilter === 'year', function ($query) {
                return $query->whereYear('created_at', now()->year);
            });
        }
    
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Aplikasi',
                    'data' => [$query->count()],
                ],
            ],
            'labels' => ['Total'],
        ];
    }
    

    protected function getType(): string
    {
        return 'line';
    }
}
