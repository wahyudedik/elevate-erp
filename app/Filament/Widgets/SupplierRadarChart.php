<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementStock\Supplier;

class SupplierRadarChart extends ChartWidget
{
    protected static ?string $heading = 'Supplier On-Time Delivery Performance';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $suppliers = Supplier::query()
            ->select('supplier_name')
            ->withCount([
                'purchaseTransactions as on_time_rate' => function ($query) {
                    $query->where('status', 'received')
                        ->whereRaw('transaction_date <= DATE_ADD(created_at, INTERVAL 3 DAY)');
                },
                'purchaseTransactions as total_deliveries'
            ])
            ->having('total_deliveries', '>', 0)
            ->limit(5)
            ->get()
            ->map(function ($supplier) {
                $rate = ($supplier->total_deliveries > 0)
                    ? round(($supplier->on_time_rate / $supplier->total_deliveries) * 100, 1)
                    : 0;
                return [
                    'name' => $supplier->supplier_name,
                    'rate' => $rate
                ];
            });

        return [
            'datasets' => [
                [
                    'label' => 'On-Time Delivery Rate (%)',
                    'data' => $suppliers->pluck('rate')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(59, 130, 246)'
                ]
            ],
            'labels' => $suppliers->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'stepSize' => 20
                    ]
                ]
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom'
                ]
            ]
        ];
    }
}
