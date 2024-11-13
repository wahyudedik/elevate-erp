<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use App\Models\ManagementCRM\Sale;

class SalesBarChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales Growth';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $data = Sale::selectRaw('MONTH(sale_date) as month, SUM(total_amount) as total')
            ->whereYear('sale_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $totals = [];

        foreach ($data as $record) {
            $months[] = Carbon::create()->month($record->month)->format('F');
            $totals[] = $record->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => $totals,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#2196F3',
                    'borderWidth' => 2,
                ]
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString() }'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}
