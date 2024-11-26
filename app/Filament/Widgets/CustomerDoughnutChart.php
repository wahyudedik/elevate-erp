<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Support\Facades\Auth;

class CustomerDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Customer Retention Rate';

    protected function getData(): array
    {
        $totalCustomers = Customer::where('company_id', Auth::user()->company_id)->count();

        // Get customers with multiple interactions
        $returningCustomers = CustomerInteraction::where('company_id', Auth::user()->company_id)
            ->select('customer_id')
            ->groupBy('customer_id') 
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0;
        $newCustomerRate = 100 - $retentionRate;

        return [
            'datasets' => [
                [
                    'data' => [$retentionRate, $newCustomerRate],
                    'backgroundColor' => ['#0ea5e9', '#e11d48'],
                ]
            ],
            'labels' => ['Returning Customers', 'New Customers'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
