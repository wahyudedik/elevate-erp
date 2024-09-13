<?php

namespace App\Filament\Resources\SalesTransactionResource\Pages;

use App\Filament\Resources\SalesTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesTransactions extends ListRecords
{
    protected static string $resource = SalesTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
