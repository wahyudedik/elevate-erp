<?php

namespace App\Filament\Resources\IncomeStatementResource\Pages;

use App\Filament\Resources\IncomeStatementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomeStatement extends EditRecord
{
    protected static string $resource = IncomeStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
