<?php

namespace App\Filament\Resources\ProjectResorcessResource\Pages;

use App\Filament\Resources\ProjectResorcessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectResorcess extends EditRecord
{
    protected static string $resource = ProjectResorcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
