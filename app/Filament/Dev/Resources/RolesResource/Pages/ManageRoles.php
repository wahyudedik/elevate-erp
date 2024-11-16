<?php

namespace App\Filament\Dev\Resources\RolesResource\Pages;

use App\Filament\Dev\Resources\RolesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRoles extends ManageRecords
{
    protected static string $resource = RolesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
