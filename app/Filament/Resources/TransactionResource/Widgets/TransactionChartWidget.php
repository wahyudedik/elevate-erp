<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementFinancial\Transaction;

class TransactionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Transaction Chart';

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
        $activeFilter = $this->filter;
    
        $query = Transaction::query();
    
        if ($activeFilter) {
            $query->when($activeFilter === 'today', fn($q) => $q->whereDate('created_at', today()))
                  ->when($activeFilter === 'week', fn($q) => $q->whereBetween('created_at', [now()->subWeek(), now()]))
                  ->when($activeFilter === 'month', fn($q) => $q->whereBetween('created_at', [now()->subMonth(), now()]))
                  ->when($activeFilter === 'year', fn($q) => $q->whereYear('created_at', now()->year));
        }
    
        return [
            'labels' => ['Pending', 'Completed', 'Failed'],
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => [
                        $query->clone()->where('status', 'pending')->count(),
                        $query->clone()->where('status', 'completed')->count(),
                        $query->clone()->where('status', 'failed')->count(),
                    ],
                    'backgroundColor' => ['#FFA500', '#008000', '#FF0000'],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }
}
