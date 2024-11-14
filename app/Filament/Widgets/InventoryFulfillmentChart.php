<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use App\Models\ManagementStock\InventoryTracking;

class InventoryFulfillmentChart extends ChartWidget
{
    protected static ?string $heading = 'Average Fulfillment Time';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('F');
        });

        $fulfillmentTimes = collect(range(1, 12))->map(function ($month) {
            $startDate = Carbon::create(now()->year, $month, 1)->startOfMonth();
            $endDate = Carbon::create(now()->year, $month, 1)->endOfMonth();

            return InventoryTracking::whereBetween('transaction_date', [$startDate, $endDate])
                ->where('transaction_type', 'deduction')
                ->avg('fulfillment_time') ?? 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Average Days to Fulfill',
                    'data' => $fulfillmentTimes->toArray(),
                    'borderColor' => '#FF6384',
                    'fill' => false,
                ]
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
