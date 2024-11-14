<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementCRM\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerLineChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Growth';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $data = Customer::query()
            ->where('company_id', Auth::user()->company_id)
            ->select(DB::raw('COUNT(*) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'))
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#10B981',
                    'fill' => false,
                ]
            ],
            'labels' => $data->pluck('month')->map(function ($month) {
                return Carbon::createFromFormat('Y-m', $month)->format('F Y');
            })->toArray(),
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
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
