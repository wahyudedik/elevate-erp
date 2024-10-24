<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\JournalEntryResource;
use App\Filament\Resources\JournalEntryResource\Widgets\AdvancedStatsOverviewWidget;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return
        [
            AdvancedStatsOverviewWidget::class,
        ];
    }
}
