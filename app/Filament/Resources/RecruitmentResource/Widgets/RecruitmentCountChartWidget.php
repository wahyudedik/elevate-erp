<?php

namespace App\Filament\Resources\RecruitmentResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementSDM\Recruitment;

class RecruitmentCountChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Recruitment Count Chart';

    protected function getData(): array
    {
        $recruitments = Recruitment::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $datasets = [];
        foreach ($recruitments as $recruitment) {
            $datasets[] = [
                'x' => $recruitment->month,
                'y' => $recruitment->year,
                'r' => $recruitment->count * 5, // Multiply by 5 to make bubbles more visible
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Recruitment Count',
                    'data' => $datasets,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    public function filterQuery($query, $data)
    {
        return $query->when($data['filter'] === 'today', function ($query) {
            return $query->whereDate('created_at', today());
        })->when($data['filter'] === 'week', function ($query) {
            return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        })->when($data['filter'] === 'month', function ($query) {
            return $query->whereMonth('created_at', now()->month);
        })->when($data['filter'] === 'year', function ($query) {
            return $query->whereYear('created_at', now()->year);
        });
    }


    protected function getType(): string
    {
        return 'bubble';
    }
}
