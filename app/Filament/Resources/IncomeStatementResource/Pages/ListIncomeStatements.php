<?php

namespace App\Filament\Resources\IncomeStatementResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\IncomeStatementResource;
use App\Filament\Resources\IncomeStatementResource\Widgets\IncomeStatementStatWidget;

class ListIncomeStatements extends ListRecords
{
    protected static string $resource = IncomeStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return[
            IncomeStatementStatWidget::class,
        ];
    }
}
