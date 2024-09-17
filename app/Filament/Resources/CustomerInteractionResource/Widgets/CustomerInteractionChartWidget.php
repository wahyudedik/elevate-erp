<?php

namespace App\Filament\Resources\CustomerInteractionResource\Widgets;

use App\Models\ManagementCRM\CustomerInteraction;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class CustomerInteractionChartWidget extends AdvancedChartWidget
{
    protected static ?string $heading = 'Customer Interaction';
    protected static string $color = 'info';
    protected static ?string $icon = 'heroicon-o-chart-bar';
    protected static ?string $iconColor = 'success';
    protected static ?string $iconBackgroundColor = 'success';
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
        $query = CustomerInteraction::query();

        switch ($this->filter) {
            case 'today':
                $query->whereDate('interaction_date', today());
                break;
            case 'week':
                $query->whereBetween('interaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('interaction_date', now()->month);
                break;
            case 'year':
                $query->whereYear('interaction_date', now()->year);
                break;
        }

        $interactions = $query->get()->groupBy(function ($item) {
            return $item->interaction_date->format('M');
        });

        $data = collect(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'])->map(function ($month) use ($interactions) {
            return $interactions->has($month) ? $interactions[$month]->count() : 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Customer Interactions',
                    'data' => $data->values()->toArray(),
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
