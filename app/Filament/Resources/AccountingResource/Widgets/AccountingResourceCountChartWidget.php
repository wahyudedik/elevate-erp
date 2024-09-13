<?php

namespace App\Filament\Resources\AccountingResource\Widgets;

use App\Models\ManagementFinancial\Accounting;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class AccountingResourceCountChartWidget extends ChartWidget
{ 
    // protected static ?string $heading = 'Count of Account';

    protected static ?string $heading = 'Count of Account';
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
        $data = Accounting::query()
            ->selectRaw('account_type, COUNT(*) as count')
            ->groupBy('account_type')
            ->get();

        $labels = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];
        $counts = array_fill(0, 5, 0);

        foreach ($data as $item) {
            $index = array_search(ucfirst($item->account_type), $labels);
            if ($index !== false) {
                $counts[$index] = $item->count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Account Types',
                    'data' => $counts,
                ],
            ],
            'labels' => $labels,
        ];
    }
 
    protected function getType(): string
    {
        return 'line';
    }
}
