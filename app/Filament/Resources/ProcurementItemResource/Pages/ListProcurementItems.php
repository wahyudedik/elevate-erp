<?php

namespace App\Filament\Resources\ProcurementItemResource\Pages;

use App\Filament\Resources\ProcurementItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcurementItems extends ListRecords
{
    protected static string $resource = ProcurementItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
