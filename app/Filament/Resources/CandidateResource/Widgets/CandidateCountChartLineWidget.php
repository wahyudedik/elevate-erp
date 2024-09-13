<?php

namespace App\Filament\Resources\CandidateResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementSDM\Candidate;

class CandidateCountChartLineWidget extends ChartWidget
{
    protected static ?string $heading = 'Candidate Count';

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

        $query = Candidate::query();

        $data = [];
        $labels = [];

        switch ($activeFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                $data = [$query->count()];
                $labels = ['Today'];
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                    $count = $query->whereDate('created_at', $date)->count();
                    $data[] = $count;
                    $labels[] = $date->format('D');
                }
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                $weekNumber = 1;
                while ($startDate <= $endDate) {
                    $endOfWeek = min($startDate->copy()->endOfWeek(), $endDate);
                    $count = $query->whereBetween('created_at', [$startDate, $endOfWeek])->count();
                    $data[] = $count;
                    $labels[] = 'Week ' . $weekNumber;
                    $startDate->addWeek();
                    $weekNumber++;
                }
                break;
            case 'year':
                for ($month = 1; $month <= 12; $month++) {
                    $date = now()->month($month)->startOfMonth();
                    $count = $query->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                    $data[] = $count;
                    $labels[] = $date->format('M');
                }
                break;
            default:
                for ($month = 1; $month <= 12; $month++) {
                    $date = now()->month($month)->startOfMonth();
                    $count = $query->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                    $data[] = $count;
                    $labels[] = $date->format('F');
                }
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Candidate Count',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
