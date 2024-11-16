<?php

namespace App\Filament\Dev\Resources\CompanyUserResource\Pages;

use App\Filament\Dev\Resources\CompanyUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCompanyUsers extends ManageRecords
{
    protected static string $resource = CompanyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
