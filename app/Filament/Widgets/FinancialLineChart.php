<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementFinancial\Transaction;

class FinancialLineChart extends ChartWidget
{
    protected static ?string $heading = 'Cash Flow Status';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $cashFlowData = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as cash_in'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as cash_out')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Cash In',
                    'data' => $cashFlowData->pluck('cash_in'),
                    'borderColor' => '#10B981',
                    'fill' => false,
                ],
                [
                    'label' => 'Cash Out',
                    'data' => $cashFlowData->pluck('cash_out'),
                    'borderColor' => '#EF4444',
                    'fill' => false,
                ],
            ],
            'labels' => $cashFlowData->pluck('date'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "IDR " + value.toLocaleString() }'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}
