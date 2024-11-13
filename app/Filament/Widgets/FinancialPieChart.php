<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\ManagementFinancial\JournalEntry;

class FinancialPieChart extends ChartWidget
{
    protected static ?string $heading = 'Expense Breakdown';

    protected function getData(): array
    {
        $expenseData = JournalEntry::query()
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.account_type', 'expense')
            ->select('accounts.account_name', DB::raw('SUM(journal_entries.amount) as total_amount'))
            ->groupBy('accounts.account_name')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $expenseData->pluck('total_amount')->toArray(),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                    ],
                ],
            ],
            'labels' => $expenseData->pluck('account_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
