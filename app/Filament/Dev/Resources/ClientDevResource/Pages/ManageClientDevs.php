<?php

namespace App\Filament\Dev\Resources\ClientDevResource\Pages;

use App\Filament\Dev\Resources\ClientDevResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClientDevs extends ManageRecords
{
    protected static string $resource = ClientDevResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
