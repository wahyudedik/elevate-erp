<?php

namespace App\Filament\Resources\BalanceSheetResource\Pages;

use App\Filament\Resources\BalanceSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBalanceSheets extends ListRecords
{
    protected static string $resource = BalanceSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
