<?php

namespace App\Filament\Resources\SalesTransactionResource\Pages;

use App\Filament\Resources\SalesTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesTransaction extends EditRecord
{
    protected static string $resource = SalesTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
