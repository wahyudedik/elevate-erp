<?php

namespace App\Filament\Resources\BalanceSheetResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\BalanceSheetResource;
use App\Filament\Resources\BalanceSheetResource\Widgets\BalanceSheetStatWidget;

class ListBalanceSheets extends ListRecords
{
    protected static string $resource = BalanceSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BalanceSheetStatWidget::class,
        ];
    }
}
