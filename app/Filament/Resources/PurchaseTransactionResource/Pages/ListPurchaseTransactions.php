<?php

namespace App\Filament\Resources\PurchaseTransactionResource\Pages;

use App\Filament\Resources\PurchaseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseTransactions extends ListRecords
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
