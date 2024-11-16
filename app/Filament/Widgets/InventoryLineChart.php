<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use App\Models\ManagementStock\Inventory;
use App\Models\ManagementStock\InventoryTracking;

class InventoryLineChart extends ChartWidget
{
    protected static ?string $heading = 'Inventory Turnover Rate';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create(null, $month, 1)->format('F');
        });

        $turnoverRates = collect(range(1, 12))->map(function ($month) {
            $startDate = Carbon::create(now()->year, $month, 1)->startOfMonth();
            $endDate = Carbon::create(now()->year, $month, 1)->endOfMonth();

            $totalDeductions = InventoryTracking::where('transaction_type', 'deduction')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('quantity_before');

            $averageInventory = Inventory::query()
                ->whereMonth('created_at', $month)
                ->average('quantity') ?? 0;

            return $averageInventory > 0 ? round(($totalDeductions / $averageInventory), 2) : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Turnover Rate',
                    'data' => $turnoverRates->toArray(),
                    'borderColor' => '#36A2EB',
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
