<?php

namespace App\Filament\Resources\InventoryTrackingResource\Pages;

use App\Filament\Resources\InventoryTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryTracking extends EditRecord
{
    protected static string $resource = InventoryTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
