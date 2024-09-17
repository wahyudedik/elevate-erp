<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Models\ManagementCRM\Customer;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class CustomerChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Customer Overview';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'info';
    protected static ?string $iconBackgroundColor = 'info';
    protected static ?string $label = 'Monthly users chart';
 
    protected static ?string $badge = 'new';
    protected static ?string $badgeColor = 'success';
    protected static ?string $badgeIcon = 'heroicon-o-check-circle';
    protected static ?string $badgeIconPosition = 'after';
    protected static ?string $badgeSize = 'xs';
 
 
    public ?string $filter = 'today';
 
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
        $customers = Customer::query();

        switch ($this->filter) {
            case 'today':
                $customers->whereDate('created_at', today());
                break;
            case 'week':
                $customers->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $customers->whereMonth('created_at', now()->month);
                break;
            case 'year':
                $customers->whereYear('created_at', now()->year);
                break;
        }

        $customerData = $customers->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $data = array_fill(1, 12, 0);
        foreach ($customerData as $month => $count) {
            $data[$month] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Customers created',
                    'data' => array_values($data),
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
