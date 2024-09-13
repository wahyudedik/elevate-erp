<?php

namespace App\Filament\Resources\LedgerResource\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ManagementFinancial\Ledger;

class LedgerChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ledger Chart';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    public ?string $filter = 'year';

    protected function getData(): array
    {
        $query = Ledger::query();

        $query->when($this->filter === 'today', function ($query) {
            $query->whereDate('created_at', today());
        })->when($this->filter === 'week', function ($query) {
            $query->whereBetween('created_at', [now()->subWeek(), now()]);
        })->when($this->filter === 'month', function ($query) {
            $query->whereBetween('created_at', [now()->subMonth(), now()]);
        })->when($this->filter === 'year', function ($query) {
            $query->whereYear('created_at', now()->year);
        });

        return [
            'labels' => ['Debit', 'Credit'],
            'datasets' => [
                [
                    'data' => [
                        $query->clone()->where('transaction_type', 'debit')->sum('amount'),
                        $query->clone()->where('transaction_type', 'credit')->sum('amount'),
                    ],
                    'backgroundColor' => ['#FF6384', '#36A2EB'],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }
}
