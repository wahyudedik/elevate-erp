<?php

namespace App\Filament\Resources\OrderProcessingResource\Pages;

use App\Filament\Resources\OrderProcessingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderProcessing extends EditRecord
{
    protected static string $resource = OrderProcessingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
