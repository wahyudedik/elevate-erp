<?php

namespace App\Filament\Dev\Resources\ContactDevResource\Pages;

use App\Filament\Dev\Resources\ContactDevResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContactDevs extends ManageRecords
{
    protected static string $resource = ContactDevResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['count'] = 1;
                    return $data;
                }),
        ];
    }
}
