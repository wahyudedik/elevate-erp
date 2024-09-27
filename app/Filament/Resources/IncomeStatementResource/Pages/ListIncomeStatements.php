<?php

namespace App\Filament\Resources\IncomeStatementResource\Pages;

use App\Filament\Resources\IncomeStatementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomeStatements extends ListRecords
{
    protected static string $resource = IncomeStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }
}
