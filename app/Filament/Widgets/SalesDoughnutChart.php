<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\CustomerInteraction;

class SalesDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Lead Conversion Rate';
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $totalLeads = CustomerInteraction::distinct('customer_id')->count();
        $convertedCustomers = Customer::where('status', 'active')->count();
        
        $conversionRate = $totalLeads > 0 
            ? round(($convertedCustomers / $totalLeads) * 100, 2)
            : 0;

        return [
            'datasets' => [
                [
                    'value' => $conversionRate,
                    'max' => 100,
                    'min' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'gauge';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'stepSize' => 20,
                    ],
                ],
            ],
            'elements' => [
                'gauge' => [
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                    ],
                ],
            ],
        ];
    }
}
