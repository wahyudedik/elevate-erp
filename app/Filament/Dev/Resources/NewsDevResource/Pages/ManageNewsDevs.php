<?php

namespace App\Filament\Dev\Resources\NewsDevResource\Pages;

use App\Filament\Dev\Resources\NewsDevResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsDevs extends ManageRecords
{
    protected static string $resource = NewsDevResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
