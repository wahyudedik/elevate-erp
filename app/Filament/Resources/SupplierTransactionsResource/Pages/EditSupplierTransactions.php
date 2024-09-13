<?php

namespace App\Filament\Resources\SupplierTransactionsResource\Pages;

use App\Filament\Resources\SupplierTransactionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplierTransactions extends EditRecord
{
    protected static string $resource = SupplierTransactionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
