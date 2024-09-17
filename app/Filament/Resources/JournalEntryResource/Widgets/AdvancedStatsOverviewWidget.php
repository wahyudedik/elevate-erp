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
                ->descriptionColor('primary')
                ->iconColor('primary'),

            Stat::make('Total Debit', function () {
                $amount = JournalEntry::where('entry_type', 'debit')->sum('amount');
                return $this->formatNumber($amount);
            })->icon('heroicon-o-arrow-trending-down')
                ->description('Total debit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Total Credit', function () {
                $amount = JournalEntry::where('entry_type', 'credit')->sum('amount');
                return $this->formatNumber($amount);
            })->icon('heroicon-o-arrow-trending-up')
                ->description('Total credit amount')
                ->descriptionIcon('heroicon-o-currency-dollar', 'before')
                ->descriptionColor('danger')
                ->iconColor('danger'),

            Stat::make('Latest Entry Date', function () {
                return JournalEntry::latest('entry_date')->value('entry_date')?->format('Y-m-d') ?? 'No data available';
            })
                ->icon('heroicon-o-calendar')
                ->description('Most recent journal entry date')
                ->descriptionIcon('heroicon-o-clock', 'before')
                ->descriptionColor('primary')
                ->iconColor('primary'),
        ];
    }

    protected function formatNumber($number)
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formattedNumber = number_format($number, $suffixIndex > 0 ? 1 : 0, '.', ',');
        return $formattedNumber . $suffixes[$suffixIndex];
    }
}
