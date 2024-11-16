<?php

namespace App\Filament\Dev\Resources\DangerDevResource\Pages;

use App\Filament\Dev\Resources\DangerDevResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDangerDevs extends ManageRecords
{
    protected static string $resource = DangerDevResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
