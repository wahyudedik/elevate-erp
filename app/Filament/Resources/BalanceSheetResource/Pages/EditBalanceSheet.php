<?php

namespace App\Filament\Resources\BalanceSheetResource\Pages;

use App\Filament\Resources\BalanceSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBalanceSheet extends EditRecord
{
    protected static string $resource = BalanceSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
