<?php

namespace App\Filament\Resources\ProcurementItemResource\Pages;

use App\Filament\Resources\ProcurementItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcurementItem extends EditRecord
{
    protected static string $resource = ProcurementItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
