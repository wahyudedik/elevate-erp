<?php

namespace App\Filament\Resources\CandidateInterviewResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\CandidateInterview;

class CandidateInterviewChartLineWidget extends ChartWidget
{
    protected static ?string $heading = 'Candidate Interview';

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
    
        $query = CandidateInterview::query();
    
        if ($activeFilter) {
            $query->when($activeFilter === 'today', function ($query) {
                return $query->whereDate('created_at', today());
            })->when($activeFilter === 'week', function ($query) {
                return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })->when($activeFilter === 'month', function ($query) {
                return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })->when($activeFilter === 'year', function ($query) {
                return $query->whereYear('created_at', now()->year);
            });
        }
    
        $candidateInterviews = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get();
    
        $labels = $candidateInterviews->pluck('date');
        $data = $candidateInterviews->pluck('count');
    
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Candidate Interview',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
