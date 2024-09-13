<?php

namespace App\Filament\Resources\JournalEntryResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementFinancial\JournalEntry;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;

class JournalEntryResourceCountChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Journal Entry';
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
        $query = JournalEntry::query();

        $query->when($this->filter === 'today', function ($query) {
            return $query->whereDate('entry_date', today());
        })->when($this->filter === 'week', function ($query) {
            return $query->whereBetween('entry_date', [now()->startOfWeek(), now()->endOfWeek()]);
        })->when($this->filter === 'month', function ($query) {
            return $query->whereMonth('entry_date', now()->month);
        })->when($this->filter === 'year', function ($query) {
            return $query->whereYear('entry_date', now()->year);
        });

        $journalEntries = $query->get();

        $debitData = $journalEntries->where('entry_type', 'debit')->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->entry_date)->format('M');
        })->map->sum('amount')->toArray();

        $creditData = $journalEntries->where('entry_type', 'credit')->groupBy(function($date) {
            return \Carbon\Carbon::parse($date->entry_date)->format('M');
        })->map->sum('amount')->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return [
            'datasets' => [
                [
                    'label' => 'Debit Entries',
                    'data' => array_values(array_replace(array_fill_keys($labels, 0), $debitData)),
                ],
                [
                    'label' => 'Credit Entries',
                    'data' => array_values(array_replace(array_fill_keys($labels, 0), $creditData)),
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
