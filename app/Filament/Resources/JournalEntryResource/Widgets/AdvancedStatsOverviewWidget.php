<?php

namespace App\Filament\Resources\JournalEntryResource\Widgets;

use App\Models\ManagementFinancial\JournalEntry;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Entries', function () {
                return JournalEntry::count();
            })->icon('heroicon-o-document-text')
                ->description('Total journal entries')
                ->descriptionIcon('heroicon-o-information-circle', 'before')
                ->color('primary'),

            Stat::make('Total Debit', function () {
                return JournalEntry::where('entry_type', 'debit')->sum('amount');
            })->icon('heroicon-o-arrow-trending-up')
                ->description('Total debit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->color('success'),

            Stat::make('Total Credit', function () {
                return JournalEntry::where('entry_type', 'credit')->sum('amount');
            })->icon('heroicon-o-arrow-trending-down')
                ->description('Total credit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->color('danger'),

            Stat::make('Latest Entry Date', function () {
                return JournalEntry::latest('entry_date')->value('entry_date');
            })->icon('heroicon-o-calendar')
                // ->description('Most recent journal entry date')
                ->descriptionIcon('heroicon-o-clock', 'before')
                ->color('warning'),
        ];
    }
}
