<?php

namespace App\Filament\Resources\ProjectResorcessResource\Pages;

use App\Filament\Resources\ProjectResorcessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectResorcesses extends ListRecords
{
    protected static string $resource = ProjectResorcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
